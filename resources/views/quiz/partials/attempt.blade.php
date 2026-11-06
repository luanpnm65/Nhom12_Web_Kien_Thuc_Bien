<details class="attempt-collapse">
    <summary>
        <div class="attempt-title-block">
            <h3>{{ $attempt->quizSet->title ?? 'Bộ đề không còn khả dụng' }}</h3>
            <div class="attempt-meta">
                <time datetime="{{ $attempt->created_at->toIso8601String() }}">{{ $attempt->created_at->copy()->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') }} (VN)</time>
                <span>{{ $attempt->quizSet->ocean_zone ?? 'Biển sâu' }}</span>
                <span>{{ intdiv($attempt->duration_seconds, 60) }} phút {{ $attempt->duration_seconds % 60 }} giây</span>
            </div>
        </div>
        <div class="attempt-result">
            <strong>{{ number_format($attempt->score) }} điểm lượt làm</strong>
            <span>+{{ number_format($attempt->ranking_points ?? 0) }} điểm xếp hạng</span>
            <span>Đúng {{ $attempt->correct_count }}/{{ $attempt->total_questions }} câu ▾</span>
        </div>
    </summary>
    <div class="attempt-breakdown">
        @forelse($attempt->answers as $ans)
            @php
                $q = $ans->question;
                $options = $q ? $q->options : [];
                $userChoiceText = ($ans->selected_option_index !== null && isset($options[$ans->selected_option_index]))
                    ? $options[$ans->selected_option_index]
                    : 'Hết giờ, không chọn';
                $correctChoiceText = ($q && isset($options[$q->correct_option_index]))
                    ? $options[$q->correct_option_index]
                    : 'Không còn dữ liệu';
            @endphp
            <div class="breakdown-item {{ $ans->is_correct ? '' : 'wrong' }}">
                <div class="answer-heading">
                    <strong>{{ $loop->iteration }}. {{ $q->question_text ?? 'Câu hỏi không còn khả dụng' }}</strong>
                    <span class="answer-points {{ $ans->is_correct ? 'correct' : 'incorrect' }}">{{ $ans->is_correct ? 'Đúng' : 'Sai' }} · {{ $ans->points_earned }} điểm · {{ number_format($ans->time_taken, 1) }}s</span>
                </div>
                <p>Bạn chọn: <strong class="{{ $ans->is_correct ? 'answer-correct' : 'answer-incorrect' }}">{{ $userChoiceText }}</strong></p>
                <p>Đáp án đúng: <strong class="answer-correct">{{ $correctChoiceText }}</strong></p>
                @if($q && $q->explanation)
                    <p class="answer-explanation">Giải thích: {{ $q->explanation }}</p>
                @endif
            </div>
        @empty
            <p class="attempt-no-answers">Lượt làm này chưa có dữ liệu chi tiết từng câu.</p>
        @endforelse
        @if($attempt->quizSet && $attempt->quizSet->is_active)
            <a class="btn btn-outline attempt-retry" href="{{ route('quiz.play', ['quiz_set_id' => $attempt->quiz_set_id]) }}">Làm lại bài này →</a>
        @else
            <p class="attempt-no-answers">Bộ đề đã gỡ xuất bản. Bạn vẫn có thể xem kết quả đã làm.</p>
        @endif
    </div>
</details>
