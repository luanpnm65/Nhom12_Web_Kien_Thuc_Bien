<?php

namespace Tests\Feature;

use App\Models\QuizSet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizRankingTest extends TestCase
{
    use RefreshDatabase;

    private function quiz(array $attributes = []): QuizSet
    {
        $quizSet = QuizSet::create($attributes + [
            'title' => 'Đề tính điểm',
            'topic' => 'Đại dương',
            'reading_passage' => 'Tư liệu đọc thử.',
        ]);
        $quizSet->questions()->create([
            'question_text' => 'Câu hỏi thử',
            'options' => ['Đúng', 'Sai', 'Sai', 'Sai'],
            'correct_option_index' => 0,
            'time_limit' => 20,
        ]);

        return $quizSet;
    }

    private function submit(QuizSet $quizSet, int $selected = 0, int $timeTaken = 2)
    {
        $run = $this->followingRedirects()->get(route('quiz.play', ['quiz_set_id' => $quizSet->id]))
            ->assertOk()->viewData('runState');
        $this->postJson(route('quiz.run.start', ['run' => $run['id']]))->assertOk();
        $this->travel($timeTaken)->seconds();
        $this->postJson(route('quiz.run.answer', ['run' => $run['id']]), [
            'question_id' => $quizSet->questions->first()->id,
            'selected_option_index' => $selected,
        ])->assertOk();
        $this->travel(2200)->milliseconds();

        return $this->getJson(route('quiz.run.state', ['run' => $run['id']]))
            ->assertOk()->assertJsonPath('phase', 'complete');
    }

    public function test_each_user_earns_rank_points_only_on_their_first_submission_for_each_quiz(): void
    {
        $this->freezeSecond();
        $quizSet = $this->quiz();
        $otherQuizSet = $this->quiz();
        $user = User::factory()->create(['total_score' => 100]);
        $otherUser = User::factory()->create();

        $this->actingAs($user);
        $this->submit($quizSet)->assertJsonPath('score', 950)->assertJsonPath('ranking_points', 950);
        $this->submit($quizSet, timeTaken: 0)->assertJsonPath('score', 1000)->assertJsonPath('ranking_points', 0);
        $this->submit($otherQuizSet, selected: 1)->assertJsonPath('ranking_points', 0);
        $this->submit($otherQuizSet)->assertJsonPath('score', 950)->assertJsonPath('ranking_points', 0);

        $this->assertSame(1050, $user->fresh()->total_score);
        $this->assertSame(4, $user->fresh()->quizzes_completed);
        $this->assertDatabaseHas('quiz_attempts', ['user_id' => $user->id, 'score' => 1000, 'ranking_points' => 0]);
        $this->assertDatabaseCount('quiz_attempt_answers', 4);
        $this->get(route('quiz.history'))->assertOk()->assertSee('1,000 điểm lượt làm')->assertSee('+0 điểm xếp hạng');

        $this->actingAs($otherUser);
        $this->submit($quizSet)->assertJsonPath('ranking_points', 950);
        $this->assertSame(950, $otherUser->fresh()->total_score);
    }

    public function test_demo_attempts_and_retakes_cannot_raise_a_users_rank_when_scores_are_tied(): void
    {
        $this->freezeSecond();
        $first = User::factory()->create(['total_score' => 0]);
        $second = User::factory()->create(['total_score' => 0]);
        $demoQuiz = $this->quiz(['source' => QuizSet::PAGINATION_DEMO_SOURCE]);
        $regularQuiz = $this->quiz();

        $this->actingAs($second);
        $this->submit($regularQuiz, selected: 1);
        $this->submit($regularQuiz)->assertJsonPath('ranking_points', 0);
        $this->submit($demoQuiz)->assertJsonPath('ranking_points', 0);
        $this->assertSame(2, $second->fresh()->quizzes_completed);

        $this->get(route('leaderboard'))->assertOk()->assertViewHas('leaders', fn ($leaders) => $leaders->pluck('id')->all() === [$first->id, $second->id]);
        $this->get(route('dashboard'))->assertOk()->assertViewHas('globalRank', 2);
    }

    public function test_migration_removes_only_legacy_repeat_points_and_preserves_attempt_scores(): void
    {
        $migration = require database_path('migrations/2026_11_05_000001_track_quiz_ranking_points.php');
        $migration->down();

        $quizSet = $this->quiz();
        $user = User::factory()->create(['total_score' => 2430, 'quizzes_completed' => 3]);
        foreach ([750, 680] as $score) {
            $user->quizAttempts()->create(['quiz_set_id' => $quizSet->id, 'score' => $score]);
        }

        $migration->up();
        $migration->up();

        $this->assertSame(1750, $user->fresh()->total_score);
        $this->assertSame(3, $user->fresh()->quizzes_completed);
        $this->assertSame([750, 680], $user->quizAttempts()->orderBy('id')->pluck('score')->all());
        $this->assertSame([750, 0], $user->quizAttempts()->orderBy('id')->pluck('ranking_points')->all());

        $migration->down();
        $this->assertSame(2430, $user->fresh()->total_score);
        $migration->up();
    }
}
