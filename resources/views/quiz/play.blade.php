@extends('layouts.app')

@section('title', 'Đấu Trường Quiz: ' . $quizSet->title)

@section('content')
<div class="container" id="quizRun"
    data-state-url="{{ route('quiz.run.state', ['run' => $runState['id']]) }}"
    data-start-url="{{ route('quiz.run.start', ['run' => $runState['id']]) }}"
    data-answer-url="{{ route('quiz.run.answer', ['run' => $runState['id']]) }}">

    <div class="quiz-play-header">
        <a class="btn btn-outline" href="{{ route('quiz.index') }}">Chọn bộ đề khác</a>
    </div>

    <p id="quizConnectionStatus" role="status" hidden></p>
    <noscript><p class="library-error">Hãy bật JavaScript để xem và tiếp tục lượt làm bài.</p></noscript>

    <!-- BƯỚC 1: ĐỌC HIỂU TƯ LIỆU -->
    <div id="readingPhaseContainer" class="reading-journal-box" style="display: none;">
        <div class="phase-topline">
            <div>
                <span class="zone-depth-pill">BƯỚC 1 / 2 · ĐỌC TƯ LIỆU</span>
                <span class="phase-zone">{{ $quizSet->ocean_zone }}</span>
            </div>
            <div class="timer-value"><span id="readingTimeText">--:--</span></div>
        </div>

        <div class="timer-progress-wrapper">
            <div id="readingProgressBar" class="timer-progress-bar"></div>
        </div>

        <h1 style="font-size: 2rem; margin-top: 1.2rem;">{{ $quizSet->title }}</h1>

        <article class="journal-passage">{{ $quizSet->reading_passage }}</article>

        <div class="phase-footer">
            <p class="phase-note">
                Gồm <strong>{{ $runState['total_questions'] }} câu hỏi tính điểm tốc độ</strong> dựa trên bài đọc này. Đồng hồ vẫn chạy khi bạn rời trang.
            </p>
            <button type="button" id="startQuizEarlyBtn" class="btn btn-primary" style="padding: 0.85rem 1.6rem;">
                Bắt đầu trả lời
            </button>
        </div>
    </div>

    <!-- BƯỚC 2: GIAO DIỆN THI -->
    <div id="kahootPhaseContainer" class="kahoot-arena" style="display: none;">
        <div class="kahoot-topbar">
            <div class="topbar-cell">
                <span class="topbar-label">Câu hỏi</span>
                <strong><span id="qCurrentIndex">1</span> / {{ $runState['total_questions'] }}</strong>
            </div>

            <div class="topbar-cell timer-value">
                <span class="topbar-label">Thời gian</span>
                <strong><span id="questionTimerDisplay">--.-</span>s</strong>
            </div>

            <div class="topbar-cell score-value">
                <span class="topbar-label">Điểm</span>
                <strong><span id="liveScoreDisplay">0</span></strong>
            </div>
        </div>

        <div class="timer-progress-wrapper" style="margin: 0;">
            <div id="questionProgressBar" class="timer-progress-bar"></div>
        </div>

        <div class="kahoot-question-banner" id="kahootQuestionText">
            Đang tải câu hỏi…
        </div>

        <div id="instantFeedbackBanner" role="status" style="display: none; padding: 1rem 1.5rem; text-align: center;"></div>

        <div class="kahoot-options-grid">
            <button type="button" class="kahoot-btn opt-0" disabled>
                <span class="kahoot-shape">▲</span>
                <span id="optText0">Đáp án A</span>
            </button>
            <button type="button" class="kahoot-btn opt-1" disabled>
                <span class="kahoot-shape">◆</span>
                <span id="optText1">Đáp án B</span>
            </button>
            <button type="button" class="kahoot-btn opt-2" disabled>
                <span class="kahoot-shape">●</span>
                <span id="optText2">Đáp án C</span>
            </button>
            <button type="button" class="kahoot-btn opt-3" disabled>
                <span class="kahoot-shape">■</span>
                <span id="optText3">Đáp án D</span>
            </button>
        </div>
    </div>

    <!-- TỔNG KẾT -->
    <div id="quizCompletionBox" class="reading-journal-box" style="display: none; text-align: center;">
        <span class="zone-depth-pill">HOÀN THÀNH</span>
        <h2 style="font-size: 2rem; margin: 1rem 0 0.5rem;">Hoàn thành chuyến thám hiểm</h2>
        <p id="quizSaveStatus" role="status" style="color: var(--ink-soft); margin-bottom: 1.5rem;">Đang lưu kết quả…</p>

        <div class="stat-grid-2" style="max-width: 480px; margin: 1.5rem auto;">
            <div class="stat-tile">
                <div style="font-size: 0.74rem; color: var(--ink-soft); text-transform: uppercase; letter-spacing: 0.08em;">Điểm đạt được</div>
                <div class="value" id="finalScoreValue">0</div>
            </div>
            <div class="stat-tile">
                <div style="font-size: 0.74rem; color: var(--ink-soft); text-transform: uppercase; letter-spacing: 0.08em;">Số câu đúng</div>
                <div class="value" id="finalAccuracyValue">0/0</div>
            </div>
        </div>

        <div class="play-actions">
            <a href="{{ route('quiz.history') }}" class="btn btn-primary">Xem chi tiết đúng/sai</a>
            <a href="{{ route('leaderboard') }}" class="btn btn-outline">Bảng xếp hạng</a>
        </div>
    </div>

</div>

<script type="application/json" id="quizRunState">@json($runState)</script>
<script src="{{ asset('js/quiz-play.js') }}" defer></script>
@endsection
