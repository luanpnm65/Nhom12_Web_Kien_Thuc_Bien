@extends('layouts.app')

@section('title', 'Bảng Điều Khiển Cá Nhân | AbyssalMind')

@section('content')
<div class="container">
    <div class="bento-dashboard-grid">

        <!-- CỘT TRÁI: HỒ SƠ CÁ NHÂN, THỐNG KÊ, BẮT ĐẦU QUIZ & ÔN TẬP CÂU SAI -->
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            <div class="bento-card">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.25rem;">
                    <div style="width: 64px; height: 64px; border-radius: 16px; background: linear-gradient(135deg, #00f2fe, #0284c7); display:flex; align-items:center; justify-content:center; font-size: 2rem; color:#021024; font-weight: 800;">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div>
                        <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; color: var(--cyan-glow); font-weight: 700;">
                            {{ $user->isAdmin() ? '👑 Đô Đốc Hải Quân (Admin)' : '🤿 Nhà Thám Hiểm Đại Dương' }}
                        </span>
                        <h2 style="font-size: 1.4rem;">{{ $user->name }}</h2>
                        <div style="font-size: 0.88rem; color: var(--text-muted);">{{ $user->email }}</div>
                    </div>
                </div>

                <div class="stat-grid-2">
                    <div class="stat-tile">
                        <div style="font-size: 0.78rem; color: var(--text-muted);">TỔNG ĐIỂM TÍCH LŨY</div>
                        <div class="value">{{ number_format($user->total_score) }}</div>
                    </div>
                    <div class="stat-tile">
                        <div style="font-size: 0.78rem; color: var(--text-muted);">HẠNG TOÀN CẦU</div>
                        <div class="value">#{{ $globalRank }}</div>
                    </div>
                </div>

                <!-- Nút Bắt đầu làm Quiz -->
                <a href="{{ route('quiz.random') }}" class="btn btn-primary" style="width: 100%; padding: 1.05rem; font-size: 1.08rem; margin-bottom: 0.9rem;">
                    🚀 Bắt đầu làm đề ngẫu nhiên
                </a>

                <!-- Nút Ôn tập các câu trả lời sai -->
                <a href="{{ route('quiz.mistakes') }}" class="btn btn-warning" style="width: 100%; padding: 0.9rem;">
                    🎯 Ôn Tập Câu Sai ({{ $mistakeCount }} câu trong 5 lần gần nhất)
                </a>
            </div>

            <div class="bento-card" style="background: rgba(14, 165, 233, 0.08);">
                <h3 style="font-size: 1.05rem; margin-bottom: 0.5rem;">⚡ Cơ Chế Tính Điểm Tốc Độ Kahoot</h3>
                <p style="font-size: 0.88rem; color: var(--text-muted);">
                    Mỗi lượt thi gồm <strong>Bước 1: Đọc hiểu văn bản (3 phút)</strong> và <strong>Bước 2: Phản xạ trắc nghiệm Kahoot</strong>. Trả lời đúng càng nhanh, bạn càng nhận được tới <strong>1.000 điểm</strong> mỗi câu. Chỉ điểm ở lượt làm đầu tiên của mỗi đề được tính vào bảng xếp hạng.
                </p>
            </div>
        </div>

        <!-- CỘT PHẢI: LỊCH SỬ 5 LẦN LÀM BÀI GẦN NHẤT -->
        <div class="bento-card">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
                <div>
                    <h2 style="font-size: 1.45rem;">📜 Lịch Sử 5 Lần Chinh Phục Gần Nhất</h2>
                    <p style="font-size: 0.88rem; color: var(--text-muted);">Bấm vào từng thẻ bên dưới để xem chi tiết đúng/sai của từng câu hỏi</p>
                </div>
                <span class="zone-depth-pill">Tổng số bài đã làm: {{ $user->quizzes_completed }}</span>
            </div>

            @forelse($recentAttempts as $attempt)
                @include('quiz.partials.attempt', ['attempt' => $attempt])
            @empty
                <div style="text-align: center; padding: 3rem 1rem; color: var(--text-muted);">
                    <div style="font-size: 3rem; margin-bottom: 0.75rem;">🌊</div>
                    <p>Bạn chưa thực hiện bài thi khám phá đại dương nào.</p>
                    <a href="{{ route('quiz.index') }}" class="btn btn-primary" style="margin-top: 1rem;">Làm Bài Thi Đầu Tiên Ngay</a>
                </div>
            @endforelse
            <a href="{{ route('quiz.history') }}" class="btn btn-outline history-all-link">Xem toàn bộ lịch sử →</a>
        </div>

    </div>
</div>
@endsection
