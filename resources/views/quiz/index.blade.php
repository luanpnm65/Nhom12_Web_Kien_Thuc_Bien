@extends('layouts.app')
@section('title', 'Chọn bài làm | OceanQuiz')
@section('content')
<div class="container library-page">
    <header class="library-heading">
        <div>
            <span class="zone-depth-pill">KHÁM PHÁ KIẾN THỨC</span>
            <h1>Chọn chuyến thám hiểm của bạn</h1>
            <p>Tìm chủ đề yêu thích, chọn bộ đề và bắt đầu đọc hiểu trước khi trả lời câu hỏi.</p>
        </div>
        <a class="btn btn-outline" href="{{ route('quiz.history') }}">Lịch sử làm bài</a>
    </header>

    <form class="bento-card library-filters" method="get" action="{{ route('quiz.index') }}" aria-label="Tìm và lọc bộ đề">
        <div class="filter-field filter-search">
            <label for="search">Tên bài hoặc chủ đề</label>
            <input class="form-control" type="search" id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Ví dụ: Cá voi, Rãnh Mariana…" maxlength="200">
        </div>
        <div class="filter-field">
            <label for="status">Trạng thái làm bài</label>
            <select class="form-control" id="status" name="status">
                <option value="">Tất cả bài</option>
                <option value="not_started" @selected(($filters['status'] ?? '') === 'not_started')>Chưa làm</option>
                <option value="completed" @selected(($filters['status'] ?? '') === 'completed')>Đã làm</option>
            </select>
        </div>
        <div class="filter-actions">
            <button class="btn btn-primary" type="submit">Tìm bài</button>
            <a class="btn btn-outline" href="{{ route('quiz.index') }}">Xóa lọc</a>
        </div>
    </form>
    @if($errors->any())
        <div class="library-error" role="alert">{{ $errors->first() }}</div>
    @endif

    <div class="library-results-heading">
        <h2>{{ $quizSets->total() }} bộ đề phù hợp</h2>
        <span>Mới nhất trước · 10 bài / trang</span>
    </div>
    <div class="quiz-catalog-grid">
        @forelse($quizSets as $set)
            <article class="bento-card quiz-catalog-card">
                <div class="catalog-card-meta">
                    <span class="zone-depth-pill">{{ $set->ocean_zone }}</span>
                    <span class="completion-badge {{ $set->user_attempts_count ? 'completed' : '' }}">{{ $set->user_attempts_count ? 'Đã làm' : 'Chưa làm' }}</span>
                </div>
                <h3>{{ $set->title }}</h3>
                <p class="catalog-topic">Chủ đề: {{ $set->topic }}</p>
                <p class="catalog-excerpt">{{ \Illuminate\Support\Str::limit($set->reading_passage, 180) }}</p>
                <div class="catalog-card-footer">
                    <span>{{ $set->questions_count }} câu hỏi · Đọc hiểu 3 phút</span>
                    <a class="btn btn-primary" href="{{ route('quiz.play', ['quiz_set_id' => $set->id]) }}" aria-label="{{ $set->user_attempts_count ? 'Làm lại' : 'Chọn làm bài' }}: {{ $set->title }}">{{ $set->user_attempts_count ? 'Làm lại →' : 'Chọn làm bài →' }}</a>
                </div>
            </article>
        @empty
            <div class="bento-card library-empty">
                <h2>Chưa có bộ đề phù hợp</h2>
                <p>Thử đổi từ khóa hoặc xóa bộ lọc. Các bộ đề sẽ xuất hiện sau khi được xuất bản.</p>
                <a class="btn btn-outline" href="{{ route('quiz.index') }}">Xem tất cả bộ đề</a>
            </div>
        @endforelse
    </div>
    {{ $quizSets->onEachSide(1)->links('pagination.ocean') }}
</div>
@endsection
