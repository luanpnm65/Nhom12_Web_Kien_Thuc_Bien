<?php

namespace Tests\Feature;

use App\Models\AiGenerationLog;
use App\Models\Article;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use App\Models\QuizSet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class QuizBrowsingHistoryTest extends TestCase
{
    use RefreshDatabase;

    private function quiz(string $title, array $attributes = [], int $questionCount = 1): QuizSet
    {
        $set = QuizSet::create(array_merge([
            'title' => $title,
            'topic' => 'Sinh vật biển',
            'ocean_zone' => 'Tầng ánh sáng',
            'reading_passage' => 'Tư liệu đọc về đại dương.',
            'is_active' => true,
        ], $attributes));

        for ($number = 1; $number <= $questionCount; $number++) {
            $set->questions()->create([
                'question_text' => 'Câu hỏi '.$number.' của '.$title,
                'options' => ['Lựa chọn sai', 'Đáp án đúng', 'Lựa chọn C', 'Lựa chọn D'],
                'correct_option_index' => 1,
                'explanation' => 'Giải thích của '.$title,
                'time_limit' => 20,
            ]);
        }

        return $set;
    }

    private function attempt(User $user, QuizSet $set, bool $withAnswer = false): QuizAttempt
    {
        $attempt = QuizAttempt::create([
            'user_id' => $user->id,
            'quiz_set_id' => $set->id,
            'score' => 0,
            'correct_count' => 0,
            'total_questions' => 1,
            'duration_seconds' => 42,
        ]);

        if ($withAnswer) {
            QuizAttemptAnswer::create([
                'quiz_attempt_id' => $attempt->id,
                'question_id' => $set->questions->first()->id,
                'selected_option_index' => 0,
                'is_correct' => false,
                'time_taken' => 3.5,
                'points_earned' => 0,
            ]);
        }

        return $attempt;
    }

    public function test_catalog_history_and_quiz_play_require_login(): void
    {
        foreach (['/quiz', '/quiz/history', '/quiz/random', '/quiz/play?quiz_set_id=1'] as $path) {
            $this->get($path)->assertRedirect('/login');
        }
    }

    public function test_dashboard_starts_a_random_playable_published_quiz(): void
    {
        $user = User::factory()->create()->refresh();
        $this->quiz('Đề nháp', ['is_active' => false], 5);
        $this->quiz('Đề kiểm thử phân trang', ['source' => QuizSet::PAGINATION_DEMO_SOURCE], 5);
        $this->quiz('Đề chưa có câu hỏi', [], 0);

        $this->actingAs($user)->get('/dashboard')->assertOk()
            ->assertSee('Bắt đầu làm đề ngẫu nhiên')->assertSee(route('quiz.random'));
        $this->get(route('quiz.random'))->assertRedirect('/quiz')->assertSessionHasErrors();
        $this->get('/quiz')->assertOk()
            ->assertSee('Hiện chưa có bộ đề phù hợp để bắt đầu ngẫu nhiên.')
            ->assertSee('Đề kiểm thử phân trang');

        $available = $this->quiz('Đề sẵn sàng');
        $playUrl = route('quiz.play', ['quiz_set_id' => $available->id]);
        $this->get(route('quiz.random'))->assertRedirect($playUrl);
        $this->followingRedirects()->get($playUrl)->assertOk()->assertViewHas('quizSet', fn ($set) => $set->is($available));
        $this->assertDatabaseCount('quiz_attempts', 0);
    }

    public function test_catalog_lists_only_playable_sets_and_play_requires_an_explicit_selection(): void
    {
        $user = User::factory()->create();
        $first = $this->quiz('Đề được chọn');
        $second = $this->quiz('Đề khác');
        $draft = $this->quiz('Đề nháp riêng', ['is_active' => false]);
        $empty = $this->quiz('Đề không có câu hỏi', [], 0);

        $this->actingAs($user)->get('/quiz')->assertOk()
            ->assertSee($first->title)->assertSee($second->title)
            ->assertDontSee($draft->title)->assertDontSee($empty->title);
        $this->get('/quiz/play')->assertRedirect('/quiz');
        $this->followingRedirects()->get('/quiz/play?quiz_set_id='.$first->id)->assertOk()
            ->assertViewHas('quizSet', fn ($set) => $set->id === $first->id)
            ->assertSee($first->reading_passage)->assertDontSee($second->title);

        foreach ([$draft->id, $empty->id, 999999] as $id) {
            $this->get('/quiz/play?quiz_set_id='.$id)->assertNotFound();
        }
        $this->getJson('/quiz/play?quiz_set_id=invalid')->assertUnprocessable();
        $this->assertDatabaseCount('quiz_attempts', 0);
    }

    public function test_catalog_combines_title_or_topic_and_personal_completion_filters(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $completed = $this->quiz('Cá voi đã làm');
        $notStarted = $this->quiz('Khám phá mới', ['topic' => 'Cá voi']);
        $this->quiz('Chủ đề san hô', ['topic' => 'San hô']);
        $this->attempt($user, $completed);
        $this->attempt($other, $notStarted);
        $query = ['search' => 'Cá voi'];

        $this->actingAs($user)->get('/quiz?'.http_build_query($query + ['status' => 'not_started']))
            ->assertOk()->assertViewHas('quizSets', fn ($sets) => $sets->pluck('id')->all() === [$notStarted->id]);
        $this->get('/quiz?'.http_build_query($query + ['status' => 'completed']))
            ->assertOk()->assertViewHas('quizSets', fn ($sets) => $sets->pluck('id')->all() === [$completed->id]);
    }

    public function test_catalog_paginates_ten_matches_in_stable_order_and_preserves_filters(): void
    {
        $user = User::factory()->create();
        $sets = collect(range(1, 12))->map(fn ($i) => $this->quiz('Bộ lọc cá voi '.$i));
        $query = ['search' => 'cá voi', 'status' => 'not_started'];
        $this->actingAs($user);
        $page = $this->get('/quiz?'.http_build_query($query))->assertOk()->viewData('quizSets');
        $this->assertInstanceOf(LengthAwarePaginator::class, $page);
        $this->assertSame(10, $page->perPage());
        $this->assertSame(12, $page->total());
        $this->assertSame($sets->reverse()->take(10)->pluck('id')->values()->all(), $page->pluck('id')->all());
        parse_str(parse_url($page->nextPageUrl(), PHP_URL_QUERY), $nextQuery);
        $this->assertEquals($query + ['page' => '2'], $nextQuery);
        $this->get('/quiz?'.http_build_query($nextQuery))->assertOk()
            ->assertViewHas('quizSets', fn ($items) => $items->pluck('id')->all() === [$sets[1]->id, $sets[0]->id]);
    }

    public function test_empty_catalog_and_empty_history_render_successfully(): void
    {
        $this->actingAs(User::factory()->create());
        $this->get('/quiz')->assertOk()->assertViewHas('quizSets', fn ($sets) => $sets->total() === 0);
        $this->get('/quiz/history')->assertOk()->assertViewHas('attempts', fn ($attempts) => $attempts->total() === 0);
        $this->get('/quiz/play')->assertRedirect('/quiz');
    }

    public function test_full_history_is_paginated_private_and_keeps_unpublished_answer_details(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $privateSet = $this->quiz('Bài riêng của người khác');
        $this->attempt($other, $privateSet, true);
        $old = $this->quiz('Bài cũ đã gỡ xuất bản', ['is_active' => false]);
        $oldAttempt = $this->attempt($user, $old, true);
        $active = $this->quiz('Bài đã hoàn thành');
        foreach (range(1, 10) as $i) {
            $this->attempt($user, $active, true);
        }

        $response = $this->actingAs($user)->get('/quiz/history')->assertOk()
            ->assertDontSee($privateSet->title);
        $page = $response->viewData('attempts');
        $this->assertSame(10, $page->perPage());
        $this->assertSame(11, $page->total());
        $this->assertCount(10, $page);
        $this->get('/quiz/history?page=2')->assertOk()
            ->assertViewHas('attempts', fn ($items) => $items->pluck('id')->all() === [$oldAttempt->id])
            ->assertSee($old->title)->assertSee('Lựa chọn sai')->assertSee('Đáp án đúng')
            ->assertSee('Giải thích của '.$old->title)->assertDontSee($privateSet->title);
    }

    public function test_history_search_is_restricted_to_current_user(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $match = $this->quiz('Nhật ký cá voi');
        $otherTopic = $this->quiz('Nhật ký san hô');
        $expected = $this->attempt($user, $match, true);
        $this->attempt($other, $match);
        $this->attempt($user, $otherTopic);

        $this->actingAs($user)->get('/quiz/history?'.http_build_query([
            'search' => 'cá voi',
        ]))->assertOk()->assertViewHas('attempts', fn ($items) => $items->pluck('id')->all() === [$expected->id]);
    }

    public function test_attempt_cards_are_collapsed_by_default_in_history_and_dashboard(): void
    {
        $user = User::factory()->create()->refresh();
        $set = $this->quiz('Lịch sử cần mở thủ công');
        $this->attempt($user, $set, true);
        $this->attempt($user, $set, true);

        $this->actingAs($user);
        foreach (['/quiz/history', '/dashboard'] as $path) {
            $page = $this->get($path)->assertOk()->assertSee($set->title);
            $this->assertSame(2, preg_match_all('/<details\b[^>]*class="attempt-collapse"[^>]*>/', $page->getContent(), $cards));
            foreach ($cards[0] as $card) {
                $this->assertDoesNotMatchRegularExpression('/\sopen(?:[\s=>])/', $card);
            }
        }
    }

    public function test_selected_quiz_completion_is_recorded_in_history(): void
    {
        $this->freezeSecond();
        $user = User::factory()->create();
        $set = $this->quiz('Bài đã chọn để nộp');
        $question = $set->questions->first();
        $run = $this->actingAs($user)->followingRedirects()->get('/quiz/play?quiz_set_id='.$set->id)
            ->assertOk()->viewData('runState');
        $this->postJson(route('quiz.run.start', ['run' => $run['id']]))->assertOk();
        $this->travel(2)->seconds();
        $this->postJson(route('quiz.run.answer', ['run' => $run['id']]), [
            'question_id' => $question->id,
            'selected_option_index' => 1,
        ])->assertOk();
        $this->travel(2200)->milliseconds();
        $this->getJson(route('quiz.run.state', ['run' => $run['id']]))->assertOk()
            ->assertJsonPath('phase', 'complete')->assertJsonPath('correct_count', 1);
        $this->assertDatabaseHas('quiz_attempts', ['user_id' => $user->id, 'quiz_set_id' => $set->id, 'score' => 950]);
        $this->get('/quiz/history')->assertOk()->assertSee($set->title)->assertSee('Giải thích của '.$set->title);
    }

    public function test_saved_articles_admin_sets_and_ai_logs_have_ten_rows_per_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        foreach (range(1, 12) as $i) {
            $article = Article::create(['title' => 'Thư viện bài '.$i, 'content' => str_repeat('Nội dung đại dương. ', 10)]);
            $set = $this->quiz('Đề quản trị '.$i, ['article_id' => $article->id, 'created_by' => $admin->id]);
            AiGenerationLog::create([
                'user_id' => $admin->id, 'article_id' => $article->id, 'quiz_set_id' => $set->id,
                'model' => 'test-model', 'status' => 'success',
            ]);
        }
        $this->actingAs($admin);
        foreach ([
            ['/admin/articles', 'articles', 'page'],
            ['/admin', 'quizSets', 'sets_page'],
            ['/admin/ai', 'logs', 'page'],
        ] as [$path, $key, $pageName]) {
            $first = $this->get($path)->assertOk()->viewData($key);
            $this->assertInstanceOf(LengthAwarePaginator::class, $first);
            $this->assertSame(10, $first->perPage());
            $this->assertSame(12, $first->total());
            $this->assertCount(10, $first);
            $second = $this->get($path.'?'.$pageName.'=2')->assertOk()->viewData($key);
            $this->assertCount(2, $second);
            $this->assertEmpty(array_intersect($first->pluck('id')->all(), $second->pluck('id')->all()));
        }
    }

    public function test_leaderboard_page_two_keeps_global_ranks_and_includes_users_beyond_fifty(): void
    {
        foreach (range(1, 51) as $i) {
            User::factory()->create(['name' => 'Nhà thám hiểm '.$i, 'total_score' => 52000 - $i * 1000]);
        }
        $page = $this->get('/leaderboard?page=2')->assertOk();
        $page->assertSee('#11')->assertDontSee('🥇')->assertDontSee('🥈')->assertDontSee('🥉');
        $leaders = $page->viewData('leaders');
        $this->assertSame(10, $leaders->perPage());
        $this->assertSame(51, $leaders->total());
        $this->get('/leaderboard?page=6')->assertOk()->assertSee('#51')->assertSee('Nhà thám hiểm 51');
    }
}
