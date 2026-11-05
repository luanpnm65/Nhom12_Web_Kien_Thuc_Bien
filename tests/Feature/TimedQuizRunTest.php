<?php

namespace Tests\Feature;

use App\Models\QuizAttempt;
use App\Models\QuizSet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class TimedQuizRunTest extends TestCase
{
    use RefreshDatabase;

    private function quiz(int $questionCount = 3): QuizSet
    {
        $set = QuizSet::create([
            'title' => 'Đề thám hiểm có giới hạn giờ',
            'topic' => 'Đại dương',
            'reading_passage' => 'Tư liệu cho lượt thi.',
        ]);
        for ($number = 1; $number <= $questionCount; $number++) {
            $set->questions()->create([
                'question_text' => 'Nội dung riêng của câu '.$number,
                'options' => ['Đáp án đúng', 'Đáp án sai', 'Phương án C', 'Phương án D'],
                'correct_option_index' => 0,
                'time_limit' => 20,
            ]);
        }

        return $set->load('questions');
    }

    private function openQuiz(QuizSet $set): array
    {
        return $this->followingRedirects()->get(route('quiz.play', ['quiz_set_id' => $set->id]))
            ->assertOk()->viewData('runState');
    }

    private function actionUrl(string $action, array $run): string
    {
        return route('quiz.run.'.$action, ['run' => $run['id']]);
    }

    private function answer(array $run, int $questionId, int $selectedOption, array $extra = []): TestResponse
    {
        return $this->postJson($this->actionUrl('answer', $run), [
            'question_id' => $questionId,
            'selected_option_index' => $selectedOption,
        ] + $extra);
    }

    public function test_refresh_keeps_reading_and_question_deadlines_and_locks_the_first_accepted_answer(): void
    {
        $this->freezeSecond();
        $this->actingAs(User::factory()->create());
        $set = $this->quiz();
        [$first, $second] = $set->questions;
        $foreign = $this->quiz(1)->questions->first();

        $run = $this->openQuiz($set);
        $this->assertSame('reading', $run['phase']);
        $this->assertSame(180_000, $run['deadline'] - $run['server_now']);
        $this->assertNull($run['question']);

        $this->travel(73)->seconds();
        $resumed = $this->openQuiz($set);
        $this->assertSame($run['id'], $resumed['id']);
        $this->assertSame($run['deadline'], $resumed['deadline']);
        $this->assertSame(107_000, $resumed['deadline'] - $resumed['server_now']);
        $this->get(route('quiz.run', ['run' => $run['id']]))->assertOk()->assertDontSee($first->question_text);

        $start = $this->postJson($this->actionUrl('start', $run))->assertOk()
            ->assertJsonPath('phase', 'question')->assertJsonPath('question.id', $first->id)
            ->assertJsonPath('question.number', 1)->assertJsonMissingPath('question.correct_option_index')
            ->assertJsonPath('feedback', null)->json();
        $this->travel(4)->seconds();
        $question = $this->get(route('quiz.run', ['run' => $run['id']]))->assertOk()
            ->assertDontSee($second->question_text)->viewData('runState');
        $this->assertSame($start['deadline'], $question['deadline']);
        $this->assertSame(16_000, $question['deadline'] - $question['server_now']);

        $this->answer($run, $foreign->id, 0)->assertOk()->assertJsonPath('phase', 'question');
        $this->answer($run, $first->id, 4)->assertUnprocessable();
        $this->answer($run, $first->id, 1, ['time_taken' => 0, 'points_earned' => 1000])
            ->assertOk()->assertJsonPath('phase', 'feedback')->assertJsonPath('score', 0)
            ->assertJsonPath('feedback.selected_option_index', 1)->assertJsonPath('feedback.correct_option_index', 0)
            ->assertJsonPath('feedback.is_correct', false);
        $this->assertEquals(4, $this->getJson($this->actionUrl('state', $run))->json('feedback.time_taken'));

        $this->answer($run, $first->id, 0)->assertOk()->assertJsonPath('feedback.selected_option_index', 1);
        $resumed = $this->openQuiz($set);
        $this->assertSame($run['id'], $resumed['id']);
        $this->assertSame(1, $resumed['feedback']['selected_option_index']);

        $this->travel(2200)->milliseconds();
        $this->answer($run, $first->id, 0)->assertOk()
            ->assertJsonPath('phase', 'question')->assertJsonPath('question.id', $second->id)
            ->assertJsonPath('score', 0);
        $this->assertDatabaseCount('quiz_attempts', 0);
    }

    public function test_absence_advances_through_reading_and_unanswered_questions_without_resetting_the_clock(): void
    {
        $this->freezeSecond();
        $this->actingAs(User::factory()->create());
        $set = $this->quiz();
        $run = $this->openQuiz($set);

        // Reading, two questions with feedback, then five seconds of question three.
        $this->travel(229_400)->milliseconds();
        $resumed = $this->openQuiz($set);
        $this->assertSame($run['id'], $resumed['id']);
        $this->assertSame('question', $resumed['phase']);
        $this->assertSame($set->questions[2]->id, $resumed['question']['id']);
        $this->assertSame(15_000, $resumed['deadline'] - $resumed['server_now']);

        $this->travel(15)->seconds();
        $this->answer($run, $set->questions[2]->id, 0)->assertOk()
            ->assertJsonPath('phase', 'feedback')->assertJsonPath('feedback.selected_option_index', null);
        $this->travel(2200)->milliseconds();
        $this->getJson($this->actionUrl('state', $run))->assertOk()
            ->assertJsonPath('phase', 'complete')->assertJsonPath('ranking_points', 0);

        $attempt = QuizAttempt::sole();
        $this->assertSame(3, $attempt->total_questions);
        $this->assertSame([null, null, null], $attempt->answers()->orderBy('id')->pluck('selected_option_index')->all());
    }

    public function test_scheduled_advancement_finalizes_an_abandoned_run_once_and_preserves_its_score(): void
    {
        $this->freezeSecond();
        $user = User::factory()->create(['total_score' => 100]);
        $this->actingAs($user);
        $set = $this->quiz(2);
        $run = $this->openQuiz($set);
        $this->postJson($this->actionUrl('start', $run))->assertOk();
        $this->travel(2)->seconds();
        $this->answer($run, $set->questions[0]->id, 0)->assertOk()->assertJsonPath('score', 950);

        $this->travel(1)->minute();
        $this->artisan('quiz:advance-runs')->assertSuccessful();
        $this->artisan('quiz:advance-runs')->assertSuccessful();
        $this->assertSame(1050, $user->fresh()->total_score);
        $this->assertSame(1, $user->fresh()->quizzes_completed);
        $this->assertDatabaseCount('quiz_attempts', 1);
        $this->assertDatabaseCount('quiz_attempt_answers', 2);
        $this->getJson($this->actionUrl('state', $run))->assertOk()
            ->assertJsonPath('phase', 'complete')->assertJsonPath('ranking_points', 950)
            ->assertJsonPath('correct_count', 1)->assertJsonPath('deadline', null);
        $this->answer($run, $set->questions[1]->id, 0)->assertOk()->assertJsonPath('score', 950);
        $this->assertSame(1050, $user->fresh()->total_score);

        $retry = $this->openQuiz($set);
        $this->assertNotSame($run['id'], $retry['id']);
        $this->assertSame('reading', $retry['phase']);
        $this->get(route('quiz.run', ['run' => $run['id']]))->assertOk()
            ->assertViewHas('runState', fn ($state) => $state['id'] === $run['id'] && $state['phase'] === 'complete');
    }

    public function test_run_endpoints_are_private_and_the_legacy_batch_submission_cannot_award_points(): void
    {
        $set = $this->quiz(1);
        $this->getJson(route('quiz.run.state', ['run' => 1]))->assertUnauthorized();
        $owner = User::factory()->create();
        $this->actingAs($owner);
        $run = $this->openQuiz($set);

        $other = User::factory()->create();
        $this->actingAs($other);
        $this->get(route('quiz.run', ['run' => $run['id']]))->assertNotFound();
        $this->getJson($this->actionUrl('state', $run))->assertNotFound();
        $this->postJson($this->actionUrl('start', $run))->assertNotFound();
        $this->answer($run, $set->questions[0]->id, 0)->assertNotFound();

        $this->postJson('/quiz/submit', [
            'quiz_set_id' => $set->id,
            'duration_seconds' => 1,
            'answers' => [['question_id' => $set->questions[0]->id, 'selected_option_index' => 0, 'time_taken' => 0]],
        ])->assertNotFound();
        $this->assertSame(0, $other->fresh()->total_score);
        $this->assertDatabaseCount('quiz_attempts', 0);
    }

    public function test_unpublishing_a_quiz_preserves_questions_needed_by_an_active_run(): void
    {
        $this->freezeSecond();
        $set = $this->quiz(5);
        $player = User::factory()->create();
        $this->actingAs($player);
        $run = $this->openQuiz($set);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->post(route('admin.question-sets.unpublish', $set))->assertRedirect();
        $this->patchJson(route('admin.question-sets.update', $set), [
            'title' => 'Tiêu đề đã sửa',
            'ocean_zone' => 'Đại dương',
            'questions' => $set->questions->map(fn ($question) => [
                'question_text' => $question->question_text,
                'options' => $question->options,
                'correct_option_index' => $question->correct_option_index,
                'explanation' => 'Giải thích hợp lệ.',
            ])->all(),
        ])->assertUnprocessable();
        $this->deleteJson(route('admin.question-sets.destroy', $set))->assertUnprocessable();

        $this->actingAs($player);
        $this->assertSame($run['id'], $this->openQuiz($set)['id']);
        $this->postJson($this->actionUrl('start', $run))->assertOk()
            ->assertJsonPath('question.id', $set->questions[0]->id);
    }
}
