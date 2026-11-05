@extends('layouts.app')

@section('title', 'Bảng Xếp Hạng Toàn Cầu | AbyssalMind')

@section('content')
<div class="container" style="max-width: 960px;">
    <div class="bento-card">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.75rem;">
            <div>
                <h1 style="font-size: 1.85rem;">Bảng Xếp Hạng Đại Dương Toàn Cầu</h1>
                <p style="color: var(--text-muted); font-size: 0.92rem;">Xếp hạng theo tổng điểm từ lượt làm đầu tiên của mỗi đề.</p>
            </div>
            <a href="{{ route('quiz.index') }}" class="btn btn-primary">Thi Đua Điểm Số Ngay</a>
        </div>

        <div class="leaderboard-table-wrap" tabindex="0" role="region" aria-label="Bảng xếp hạng nhà thám hiểm">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Hạng</th>
                    <th>Tên Nhà Thám Hiểm</th>
                    <th>Số Lượt Đã Hoàn Thành</th>
                    <th style="text-align: right;">Tổng Điểm</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leaders as $idx => $leader)
                    @php($rank = $leaders->firstItem() + $idx)
                    <tr style="{{ auth()->id() === $leader->id ? 'background: rgba(0, 242, 254, 0.12);' : '' }}">
                        <td style="font-family: 'JetBrains Mono', monospace; font-weight: 700;">
                            #{{ $rank }}
                        </td>
                        <td>
                            <div style="font-weight: 700;">
                                {{ $leader->name }}
                                @if(auth()->id() === $leader->id)
                                    <span class="zone-depth-pill" style="font-size:0.7rem; padding:0.15rem 0.5rem; margin-left:0.4rem;">BẠN</span>
                                @endif
                            </div>
                        </td>
                        <td>{{ $leader->quizzes_completed }} lượt thi</td>
                        <td style="text-align: right; font-family: 'JetBrains Mono', monospace; font-weight: 800; color: var(--cyan-glow); font-size: 1.1rem;">
                            {{ number_format($leader->total_score) }} điểm
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4">Chưa có nhà thám hiểm nào trong bảng xếp hạng.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        {{ $leaders->onEachSide(1)->links('pagination.ocean') }}
    </div>
</div>
@endsection
