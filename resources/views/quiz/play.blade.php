@extends('layouts.app')

@section('title', 'Đấu Trường Quiz: ' . $quizSet->title)

@section('content')
<div class="container" id="quizRun"
    data-state-url="{{ route('quiz.run.state', ['run' => $runState['id']]) }}"
    data-start-url="{{ route('quiz.run.start', ['run' => $runState['id']]) }}"
    data-answer-url="{{ route('quiz.run.answer', ['run' => $runState['id']]) }}">
    <a class="btn btn-outline" href="{{ route('quiz.index') }}">← Chọn bộ đề khác</a>
    <p id="quizConnectionStatus" role="status" hidden></p>
    <noscript><p class="library-error">Hãy bật JavaScript để xem và tiếp tục lượt làm bài.</p></noscript>

    <!-- BƯỚC 1: GIAI ĐOẠN ĐỌC HIỂU TƯ LIỆU (3 PHÚT) -->
    <div id="readingPhaseContainer" class="reading-journal-box" style="display: none;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div>
                <span class="zone-depth-pill">BƯỚC 1 / 2 • ĐỌC TƯ LIỆU KHOA HỌC</span>
                <span style="margin-left: 0.65rem; font-size: 0.85rem; color: var(--text-muted);">🌊 {{ $quizSet->ocean_zone }}</span>
            </div>
            <div style="font-family: 'JetBrains Mono', monospace; font-size: 1.25rem; font-weight: 700; color: var(--cyan-glow);">
                ⏳ <span id="readingTimeText">--:--</span>
            </div>
        </div>

        <div class="timer-progress-wrapper">
            <div id="readingProgressBar" class="timer-progress-bar"></div>
        </div>

        <h1 style="font-size: 2rem; margin-top: 1.2rem;">{{ $quizSet->title }}</h1>

        <article class="journal-passage">{{ $quizSet->reading_passage }}</article>

        <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(255,255,255,0.12); padding-top: 1.5rem; margin-top: 1.5rem;">
            <div style="font-size: 0.88rem; color: var(--text-muted);">
                📌 Gồm <strong>{{ $runState['total_questions'] }} câu hỏi tính điểm tốc độ</strong> dựa trên bài đọc này. Đồng hồ vẫn chạy khi bạn rời trang.
            </div>
            <button type="button" id="startQuizEarlyBtn" class="btn btn-primary" style="font-size: 1.05rem; padding: 0.9rem 1.8rem;">
                ⚡ Bắt Đầu Trả Lời Sớm →
            </button>
        </div>
    </div>

    <!-- BƯỚC 2: GIAO DIỆN THI KAHOOT -->
    <div id="kahootPhaseContainer" class="kahoot-arena" style="display: none;">

        <div style="display: flex; justify-content: space-between; align-items: center; background: rgba(12, 25, 48, 0.85); padding: 1rem 1.5rem; border-radius: 16px; border: 1px solid var(--border-glass);">
            <div style="font-weight: 700;">
                Câu hỏi <span id="qCurrentIndex" style="color: var(--cyan-glow);">1</span> / {{ $runState['total_questions'] }}
            </div>

            <div style="font-family: 'JetBrains Mono', monospace; font-size: 1.45rem; font-weight: 800; color: #fbbf24;">
                ⏱️ <span id="questionTimerDisplay">--.-</span>s
            </div>

            <div style="font-family: 'JetBrains Mono', monospace; font-size: 1.2rem; font-weight: 800; color: #4ade80;">
                🏆 <span id="liveScoreDisplay">0</span> điểm
            </div>
        </div>

        <div class="timer-progress-wrapper" style="margin: 0;">
            <div id="questionProgressBar" class="timer-progress-bar"></div>
        </div>

        <div class="kahoot-question-banner" id="kahootQuestionText">
            Đang tải câu hỏi...
        </div>

        <div id="instantFeedbackBanner" role="status" style="display: none; padding: 1rem 1.5rem; border-radius: 14px; text-align: center; font-weight: 700; font-size: 1.15rem;"></div>

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

    <!-- MÀN HÌNH TỔNG KẾT ĐIỂM -->
    <div id="quizCompletionBox" class="reading-journal-box" style="display: none; text-align: center;">
        <div style="font-size: 4rem; margin-bottom: 0.5rem;">🏆</div>
        <h2 style="font-size: 2.2rem;">Hoàn Thành Chuyến Thám Hiểm!</h2>
        <p id="quizSaveStatus" role="status" style="color: var(--text-muted); margin-bottom: 1.5rem;">Đang lưu kết quả…</p>

        <div class="stat-grid-2" style="max-width: 480px; margin: 1.5rem auto;">
            <div class="stat-tile">
                <div style="font-size: 0.8rem; color: var(--text-muted);">ĐIỂM ĐẠT ĐƯỢC</div>
                <div class="value" id="finalScoreValue">0</div>
            </div>
            <div class="stat-tile">
                <div style="font-size: 0.8rem; color: var(--text-muted);">SỐ CÂU ĐÚNG</div>
                <div class="value" id="finalAccuracyValue">0/0</div>
            </div>
        </div>

        <div style="display: flex; justify-content: center; gap: 1rem; margin-top: 2rem;">
            <a href="{{ route('quiz.history') }}" class="btn btn-primary">📊 Xem Chi Tiết Đúng/Sai</a>
            <a href="{{ route('leaderboard') }}" class="btn btn-outline">🌍 Bảng Xếp Hạng</a>
        </div>
    </div>

</div>

<script type="application/json" id="quizRunState">@json($runState)</script>
<script src="{{ asset('js/quiz-play.js') }}" defer></script>
@endsection
