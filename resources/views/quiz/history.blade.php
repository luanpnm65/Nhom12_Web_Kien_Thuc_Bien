@extends('layouts.app')
@section('title', 'Lịch sử làm bài | OceanQuiz')
@section('content')
<div class="container library-page history-page">
    <header class="library-heading">
        <div>
            <span class="zone-depth-pill">HÀNH TRÌNH CỦA BẠN</span>
            <h1>Lịch sử làm bài</h1>
            <p>Xem điểm, thời gian và đáp án từng câu trong tất cả các lượt làm bài của bạn.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('quiz.index') }}">Chọn bài tiếp theo →</a>
    </header>
    <form class="bento-card library-filters history-filters" method="get" action="{{ route('quiz.history') }}" aria-label="Tìm trong lịch sử làm bài">
        <div class="filter-field filter-search">
            <label for="search">Tên bài hoặc chủ đề</label>
            <input class="form-control" type="search" id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Tìm bài đã làm…" maxlength="200">
        </div>
        <div class="filter-actions">
            <button class="btn btn-primary" type="submit">Tìm lịch sử</button>
            <a class="btn btn-outline" href="{{ route('quiz.history') }}">Xóa lọc</a>
        </div>
    </form>
    @if($errors->any())
        <div class="library-error" role="alert">{{ $errors->first() }}</div>
    @endif
    <div class="library-results-heading">
        <h2>{{ $attempts->total() }} lượt làm bài</h2>
        <span>Mới nhất trước · Bấm vào lượt làm để xem đáp án</span>
    </div>
    <section aria-label="Các lượt làm bài">
        @forelse($attempts as $attempt)
            @include('quiz.partials.attempt', ['attempt' => $attempt])
        @empty
            <div class="bento-card library-empty">
                <h2>{{ request()->filled('search') ? 'Không tìm thấy lượt làm phù hợp' : 'Bạn chưa có lượt làm bài nào' }}</h2>
                <p>{{ request()->filled('search') ? 'Thử đổi từ khóa hoặc xóa bộ lọc để xem lại lịch sử.' : 'Chọn một bộ đề để bắt đầu chuyến thám hiểm đầu tiên.' }}</p>
                <a class="btn btn-outline" href="{{ route('quiz.index') }}">Khám phá bộ đề</a>
            </div>
        @endforelse
    </section>
    {{ $attempts->onEachSide(1)->links('pagination.ocean') }}
</div>
@endsection
