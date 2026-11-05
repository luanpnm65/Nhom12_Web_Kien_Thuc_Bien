<?php

namespace App\Services;

use App\Models\Question;
use App\Models\QuizAttempt;
use App\Models\QuizRun;
use App\Models\QuizSet;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class TimedQuizRuns
{
    private const READING_MS = 180_000;

    private const FEEDBACK_MS = 2_200;

    public function open(User $user, int $quizSetId): QuizRun
    {
        return DB::transaction(function () use ($user, $quizSetId) {
            // Use the same account lock for opening, answering and ranking a run.
            User::lockForUpdate()->findOrFail($user->id);
            $existingRun = QuizRun::where('user_id', $user->id)->where('quiz_set_id', $quizSetId)
                ->whereNull('quiz_attempt_id')->lockForUpdate()->first();

            if ($existingRun) {
                return $existingRun;
            }

            $quizSet = QuizSet::where('is_active', true)->has('questions')->lockForUpdate()->findOrFail($quizSetId);

            return QuizRun::create([
                'user_id' => $user->id,
                'quiz_set_id' => $quizSet->id,
                'phase' => 'reading',
                'deadline_ms' => now()->getTimestampMs() + self::READING_MS,
                'answers' => [],
            ]);
        }, 3);
    }

    public function state(QuizRun $run, bool $startEarly = false, ?int $questionId = null, ?int $selectedOption = null): array
    {
        return DB::transaction(function () use ($run, $startEarly, $questionId, $selectedOption) {
            $user = User::lockForUpdate()->findOrFail($run->user_id);
            $run = QuizRun::lockForUpdate()->findOrFail($run->id);

            if ($run->quiz_attempt_id !== null) {
                return $this->snapshot($run, null, $run->quizAttempt->total_questions, $run->quizAttempt);
            }

            $questions = $run->quizSet->questions()->orderBy('id')->get();
            $now = now()->getTimestampMs();
            $this->advance($run, $user, $questions, $now);

            if ($startEarly && $run->phase === 'reading') {
                $this->beginQuestions($run, $questions[0], $now);
            }

            if ($questionId !== null && $run->phase === 'question') {
                $question = $questions[$run->question_index];
                if ($question->id === $questionId) {
                    $this->recordAnswer($run, $question, $selectedOption, $now);
                }
            }

            if ($run->isDirty()) {
                $run->save();
            }

            $currentQuestion = in_array($run->phase, ['question', 'feedback'], true)
                ? $questions[$run->question_index]
                : null;

            return $this->snapshot($run, $currentQuestion, $questions->count(), $run->quizAttempt);
        }, 3);
    }

    public function advanceDue(?int $userId = null): void
    {
        QuizRun::where('deadline_ms', '<=', now()->getTimestampMs())
            ->when($userId !== null, fn ($query) => $query->where('user_id', $userId))
            ->select('id', 'user_id')
            ->chunkById(100, function ($runs) {
                foreach ($runs as $run) {
                    try {
                        $this->state($run);
                    } catch (ModelNotFoundException) {
                        // An account or run may be removed between the scan and the lock.
                    }
                }
            });
    }

    private function advance(QuizRun $run, User $user, Collection $questions, int $now): void
    {
        while ($run->deadline_ms !== null && $run->deadline_ms <= $now) {
            // Anchor each transition to the elapsed deadline so time keeps moving while away.
            $deadline = $run->deadline_ms;
            if ($run->phase === 'reading') {
                $this->beginQuestions($run, $questions[0], $deadline);
            } elseif ($run->phase === 'question') {
                $this->recordAnswer($run, $questions[$run->question_index], null, $deadline);
            } elseif ($run->question_index + 1 < $questions->count()) {
                $run->question_index++;
                $this->beginQuestion($run, $questions[$run->question_index], $deadline);
            } else {
                $this->finish($run, $user, $questions->count(), $deadline);
            }
        }
    }

    private function beginQuestions(QuizRun $run, Question $question, int $startedAt): void
    {
        $run->quiz_started_ms = $startedAt;
        $this->beginQuestion($run, $question, $startedAt);
    }

    private function beginQuestion(QuizRun $run, Question $question, int $startedAt): void
    {
        $run->phase = 'question';
        $run->deadline_ms = $startedAt + $this->timeLimit($question) * 1000;
    }

    private function timeLimit(Question $question): int
    {
        return $question->time_limit ?: 20;
    }

    private function recordAnswer(QuizRun $run, Question $question, ?int $selectedOption, int $answeredAt): void
    {
        $timeLimit = $this->timeLimit($question);
        $elapsed = round(max(0, $timeLimit - ($run->deadline_ms - $answeredAt) / 1000), 2);
        $isCorrect = $selectedOption !== null && $selectedOption === $question->correct_option_index;
        $points = $isCorrect ? (int) round(1000 * (1 - $elapsed / $timeLimit * 0.5)) : 0;

        $answers = $run->answers;
        $answers[] = [
            'question_id' => $question->id,
            'selected_option_index' => $selectedOption,
            'is_correct' => $isCorrect,
            'time_taken' => $elapsed,
            'points_earned' => $points,
        ];
        $run->answers = $answers;
        $run->phase = 'feedback';
        $run->deadline_ms = $answeredAt + self::FEEDBACK_MS;
    }

    private function finish(QuizRun $run, User $user, int $totalQuestions, int $finishedAt): void
    {
        $attempts = $user->quizAttempts();
        $score = array_sum(array_column($run->answers, 'points_earned'));
        $isRankedQuiz = $run->quizSet->source !== QuizSet::PAGINATION_DEMO_SOURCE;
        $hasPreviousAttempt = $attempts->where('quiz_set_id', $run->quiz_set_id)->exists();
        $rankingPoints = $isRankedQuiz && ! $hasPreviousAttempt ? $score : 0;

        $attempt = $user->quizAttempts()->create([
            'quiz_set_id' => $run->quiz_set_id,
            'score' => $score,
            'ranking_points' => $rankingPoints,
            'correct_count' => count(array_filter(array_column($run->answers, 'is_correct'))),
            'total_questions' => $totalQuestions,
            'duration_seconds' => max(1, (int) round(($finishedAt - $run->quiz_started_ms) / 1000)),
        ]);
        $attempt->answers()->createMany($run->answers);

        if ($isRankedQuiz) {
            $user->update([
                'total_score' => $user->total_score + $rankingPoints,
                'quizzes_completed' => $user->quizzes_completed + 1,
            ]);
        }

        $run->quizAttempt()->associate($attempt);
        $run->phase = 'complete';
        $run->deadline_ms = null;
        $run->answers = [];
    }

    private function snapshot(QuizRun $run, ?Question $question, int $totalQuestions, ?QuizAttempt $attempt): array
    {
        $feedback = null;
        if ($run->phase === 'feedback') {
            $feedback = $run->answers[$run->question_index];
            $feedback['correct_option_index'] = $question->correct_option_index;
        }

        return [
            'id' => $run->id,
            'phase' => $run->phase,
            'server_now' => now()->getTimestampMs(),
            'deadline' => $run->deadline_ms,
            'total_questions' => $totalQuestions,
            'score' => $attempt?->score ?? array_sum(array_column($run->answers, 'points_earned')),
            'correct_count' => $attempt?->correct_count ?? count(array_filter(array_column($run->answers, 'is_correct'))),
            'ranking_points' => $attempt?->ranking_points,
            'question' => $question ? [
                'id' => $question->id,
                'number' => $run->question_index + 1,
                'text' => $question->question_text,
                'options' => $question->options,
                'time_limit' => $this->timeLimit($question),
            ] : null,
            'feedback' => $feedback,
        ];
    }
}
