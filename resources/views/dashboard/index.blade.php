@extends('layouts.app')

@section('title', 'Bảng Điều Khiển Cá Nhân | AbyssalMind')

@section('content')
<div class="container">
    <div class="library-heading">
        <div>
            <span class="zone-depth-pill">TỔNG QUAN CÁ NHÂN</span>
            <h1>Bảng điều khiển</h1>
            <p>Điểm số, thứ hạng và những lượt làm bài gần nhất của bạn.</p>
        </div>
        <a href="{{ route('quiz.index') }}" class="btn btn-primary">Chọn bài tiếp theo</a>
    </div>

    <div class="bento-dashboard-grid">
        <!-- CỘT TRÁI: HỒ SƠ, THỐNG KÊ, HÀNH ĐỘNG -->
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            <div class="bento-card">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.25rem;">
                    <div style="width: 62px; height: 62px; border-radius: 20px; background: linear-gradient(140deg, #2d8fdd, #0b3a68); display: flex; align-items: center; justify-content: center; font-size: 1.7rem; color: #ffffff; font-weight: 700; box-shadow: 0 10px 24px rgba(22,104,196,0.25);">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div style="min-width: 0;">
                        <span style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.12em; color: var(--ink-soft); font-weight: 600;">
                            {{ $user->isAdmin() ? 'Quản trị viên' : 'Nhà thám hiểm' }}
                        </span>
                        <h2 style="font-size: 1.35rem; overflow-wrap: anywhere;">{{ $user->name }}</h2>
                        <div style="font-size: 0.86rem; color: var(--ink-soft); overflow-wrap: anywhere;">{{ $user->email }}</div>
                    </div>
                </div>

                <div class="stat-grid-2">
                    <div class="stat-tile">
                        <div style="font-size: 0.72rem; color: var(--ink-soft); text-transform: uppercase; letter-spacing: 0.08em;">Tổng điểm</div>
                        <div class="value">{{ number_format($user->total_score) }}</div>
                    </div>
                    <div class="stat-tile">
                        <div style="font-size: 0.72rem; color: var(--ink-soft); text-transform: uppercase; letter-spacing: 0.08em;">Hạng toàn cầu</div>
                        <div class="value">#{{ $globalRank }}</div>
                    </div>
                </div>

                <a href="{{ route('quiz.random') }}" class="btn btn-primary" style="width: 100%; padding: 0.95rem; font-size: 1rem; margin-bottom: 0.75rem;">
                    Bắt đầu đề ngẫu nhiên
                </a>

                <a href="{{ route('quiz.mistakes') }}" class="btn btn-warning" style="width: 100%; padding: 0.85rem;">
                    Ôn tập câu sai ({{ $mistakeCount }} câu)
                </a>
            </div>

            <div class="bento-card" style="background: rgba(255, 255, 255, 0.6);">
                <h3 style="font-size: 1rem; margin-bottom: 0.5rem;">Cách tính điểm</h3>
                <p style="font-size: 0.87rem; color: var(--ink-soft);">
                    Mỗi lượt gồm <strong>đọc hiểu 3 phút</strong> và <strong>phản xạ trắc nghiệm</strong>. Trả lời đúng càng nhanh, bạn nhận tối đa <strong>1.000 điểm</strong> mỗi câu. Chỉ lượt làm đầu tiên của mỗi đề được tính vào bảng xếp hạng.
                </p>
            </div>
        </div>

        <!-- CỘT PHẢI: LỊCH SỬ GẦN NHẤT -->
        <div class="bento-card">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
                <div>
                    <h2 style="font-size: 1.35rem;">5 lượt làm gần nhất</h2>
                    <p style="font-size: 0.87rem; color: var(--ink-soft);">Mở từng lượt để xem chi tiết đúng/sai của mỗi câu hỏi.</p>
                </div>
                <span class="zone-depth-pill">Tổng số bài đã làm: {{ $user->quizzes_completed }}</span>
            </div>

            @forelse($recentAttempts as $attempt)
                @include('quiz.partials.attempt', ['attempt' => $attempt])
            @empty
                <div style="text-align: center; padding: 3rem 1rem; color: var(--ink-soft);">
                    <p>Bạn chưa thực hiện bài thi khám phá đại dương nào.</p>
                    <a href="{{ route('quiz.index') }}" class="btn btn-primary" style="margin-top: 1rem;">Làm bài thi đầu tiên</a>
                </div>
            @endforelse
            <a href="{{ route('quiz.history') }}" class="btn btn-outline history-all-link">Xem toàn bộ lịch sử</a>
        </div>
    </div>
</div>
@endsection
