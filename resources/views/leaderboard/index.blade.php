@extends('layouts.app')

@section('title', 'Bảng Xếp Hạng Toàn Cầu | OceanQuiz')

@section('content')
<div class="container" style="max-width: 1000px;">
    <div class="library-heading">
        <div>
            <span class="zone-depth-pill">THI ĐUA TOÀN CẦU</span>
            <h1>Bảng xếp hạng đại dương</h1>
            <p>Xếp hạng theo tổng điểm từ lượt làm đầu tiên của mỗi đề.</p>
        </div>
        <a href="{{ route('quiz.index') }}" class="btn btn-primary">Thi đua điểm số</a>
    </div>

    <div class="bento-card">
        <div class="leaderboard-table-wrap" tabindex="0" role="region" aria-label="Bảng xếp hạng nhà thám hiểm">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Hạng</th>
                        <th>Tên nhà thám hiểm</th>
                        <th>Số lượt hoàn thành</th>
                        <th style="text-align: right;">Tổng điểm</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaders as $idx => $leader)
                        @php($rank = $leaders->firstItem() + $idx)
                        <tr style="{{ auth()->id() === $leader->id ? 'background: rgba(22, 104, 196, 0.07);' : '' }}">
                            <td style="font-family: 'JetBrains Mono', monospace; font-weight: 700; color: var(--blue);">
                                #{{ $rank }}
                            </td>
                            <td>
                                <div style="font-weight: 600;">
                                    {{ $leader->name }}
                                    @if(auth()->id() === $leader->id)
                                        <span class="zone-depth-pill" style="font-size: 0.68rem; padding: 0.12rem 0.5rem; margin-left: 0.4rem;">BẠN</span>
                                    @endif
                                </div>
                            </td>
                            <td style="color: var(--ink-soft);">{{ $leader->quizzes_completed }} lượt</td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace; font-weight: 700; color: var(--blue-deep); font-size: 1.05rem;">
                                {{ number_format($leader->total_score) }} điểm
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" style="color: var(--ink-soft);">Chưa có nhà thám hiểm nào trong bảng xếp hạng.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $leaders->onEachSide(1)->links('pagination.ocean') }}
    </div>
</div>
@endsection
