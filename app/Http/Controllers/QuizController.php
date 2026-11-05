<?php

// [Thành viên 2 - Contributor A] app/Http/Controllers/QuizController.php

namespace App\Http\Controllers;

use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use App\Models\QuizRun;
use App\Models\QuizSet;
use App\Services\TimedQuizRuns;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class QuizController extends Controller
{
    public function index(Request $request, TimedQuizRuns $runs)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:200'],
            'ocean_zone' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['completed', 'not_started'])],
        ]);
        $userId = $request->user()->id;
        $runs->advanceDue($userId);
        $availableSets = QuizSet::where('is_active', true)->has('questions');
        $zones = (clone $availableSets)->select('ocean_zone')->distinct()->orderBy('ocean_zone')->pluck('ocean_zone');
        $quizSets = $availableSets
            ->withCount('questions')
            ->withCount(['attempts as user_attempts_count' => fn ($query) => $query->where('user_id', $userId)])
            ->when(trim($filters['search'] ?? ''), function ($query, $search) {
                $query->where(fn ($query) => $query->where('title', 'like', '%'.$search.'%')->orWhere('topic', 'like', '%'.$search.'%'));
            })
            ->when($filters['ocean_zone'] ?? null, fn ($query, $zone) => $query->where('ocean_zone', $zone))
            ->when(($filters['status'] ?? '') === 'completed', fn ($query) => $query->whereHas('attempts', fn ($query) => $query->where('user_id', $userId)))
            ->when(($filters['status'] ?? '') === 'not_started', fn ($query) => $query->whereDoesntHave('attempts', fn ($query) => $query->where('user_id', $userId)))
            ->latest('id')->paginate(10)->withQueryString();

        return view('quiz.index', compact('quizSets', 'zones', 'filters'));
    }

    public function history(Request $request, TimedQuizRuns $runs)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:200'],
            'ocean_zone' => ['nullable', 'string', 'max:255'],
        ]);
        $userId = $request->user()->id;
        $runs->advanceDue($userId);
        $zones = QuizSet::whereHas('attempts', fn ($query) => $query->where('user_id', $userId))
            ->select('ocean_zone')->distinct()->orderBy('ocean_zone')->pluck('ocean_zone');
        $attempts = QuizAttempt::with(['quizSet', 'answers.question'])
            ->where('user_id', $userId)
            ->when(trim($filters['search'] ?? ''), function ($query, $search) {
                $query->whereHas('quizSet', fn ($query) => $query->where(fn ($query) => $query->where('title', 'like', '%'.$search.'%')->orWhere('topic', 'like', '%'.$search.'%')));
            })
            ->when($filters['ocean_zone'] ?? null, fn ($query, $zone) => $query->whereHas('quizSet', fn ($query) => $query->where('ocean_zone', $zone)))
            ->latest('id')->paginate(10)->withQueryString();

        return view('quiz.history', compact('attempts', 'zones', 'filters'));
    }

    public function random()
    {
        $quizSetId = QuizSet::where('is_active', true)
            ->where('source', '!=', QuizSet::PAGINATION_DEMO_SOURCE)
            ->has('questions')
            ->inRandomOrder()
            ->value('id');

        if ($quizSetId === null) {
            return to_route('quiz.index')->withErrors('Hiện chưa có bộ đề phù hợp để bắt đầu ngẫu nhiên.');
        }

        return to_route('quiz.play', ['quiz_set_id' => $quizSetId]);
    }

    public function play(Request $request, TimedQuizRuns $runs)
    {
        $data = $request->validate(['quiz_set_id' => ['nullable', 'integer', 'min:1']]);
        if (empty($data['quiz_set_id'])) {
            return to_route('quiz.index');
        }

        return to_route('quiz.run', $runs->open($request->user(), (int) $data['quiz_set_id']));
    }

    public function showRun(Request $request, QuizRun $run, TimedQuizRuns $runs)
    {
        $this->checkRunOwner($request, $run);

        return response()->view('quiz.play', [
            'quizSet' => $run->quizSet,
            'runState' => $runs->state($run),
        ])->header('Cache-Control', 'no-store');
    }

    public function runState(Request $request, QuizRun $run, TimedQuizRuns $runs)
    {
        $this->checkRunOwner($request, $run);

        return response()->json($runs->state($run))->header('Cache-Control', 'no-store');
    }

    public function startRun(Request $request, QuizRun $run, TimedQuizRuns $runs)
    {
        $this->checkRunOwner($request, $run);

        return response()->json($runs->state($run, startEarly: true));
    }

    public function answerRun(Request $request, QuizRun $run, TimedQuizRuns $runs)
    {
        $this->checkRunOwner($request, $run);
        $data = $request->validate([
            'question_id' => ['required', 'integer', 'min:1'],
            'selected_option_index' => ['required', 'integer', 'min:0', 'max:3'],
        ]);

        return response()->json($runs->state($run,
            questionId: (int) $data['question_id'],
            selectedOption: (int) $data['selected_option_index'],
        ));
    }

    private function checkRunOwner(Request $request, QuizRun $run): void
    {
        abort_unless($run->user_id === $request->user()->id, 404);
    }

    /**
     * Tính năng Review Mistakes: Tự động gom các câu trả lời sai trong 5 lần làm bài gần nhất
     */
    public function mistakes(TimedQuizRuns $runs)
    {
        $user = Auth::user();
        $runs->advanceDue($user->id);

        // Lấy ID của 5 lần làm bài gần nhất
        $lastFiveAttemptIds = QuizAttempt::where('user_id', $user->id)
            ->whereHas('quizSet', fn ($query) => $query->where('source', '!=', QuizSet::PAGINATION_DEMO_SOURCE))
            ->latest()
            ->take(5)
            ->pluck('id');

        // Lấy tất cả câu trả lời sai trong 5 lần đó, kèm thông tin Question và QuizSet
        $wrongAnswers = QuizAttemptAnswer::with(['question.quizSet', 'attempt'])
            ->whereIn('quiz_attempt_id', $lastFiveAttemptIds)
            ->where('is_correct', false)
            ->latest()
            ->get()
            ->unique('question_id')
            ->values();

        return view('quiz.mistakes', compact('wrongAnswers'));
    }
}
