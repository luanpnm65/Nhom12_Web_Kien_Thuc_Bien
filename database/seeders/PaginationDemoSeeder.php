<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\QuizSet;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PaginationDemoSeeder extends Seeder
{
    private const ACCOUNT = 'admin@ocean.io';

    private const TOPIC = 'Mẫu phân trang';

    private const STOPS = [
        ['Tầng Ánh Sáng (0m - 200m)', '100 m'],
        ['Tầng Chạng Vạng (200m - 1,000m)', '600 m'],
        ['Tầng Nửa Đêm (1,000m - 4,000m)', '2.500 m'],
        ['Tầng Đáy Thẳm (4,000m - 6,000m)', '5.000 m'],
        ['Tầng Hố Thẳm (6,000m - 11,000m)', '8.000 m'],
    ];

    public function run(): void
    {
        $connection = DB::connection();
        $isLocalDatabase = $connection->getDriverName() === 'sqlite'
            || in_array($connection->getConfig('host'), ['127.0.0.1', 'localhost', '::1'], true);

        if (! app()->environment('local') || ! $isLocalDatabase) {
            throw new RuntimeException('Pagination demo data requires a local environment and database.');
        }

        $connection->transaction(function () {
            $user = User::where('email', self::ACCOUNT)->lockForUpdate()->firstOrFail();
            $article = Article::firstOrCreate(
                ['title' => self::TOPIC.' — Nhật ký robot lặn', 'source_name' => 'Dữ liệu kiểm thử'],
                ['content' => <<<'PASSAGE'
                    Trong chuyến thám hiểm giả lập, robot lặn Hải Âu dừng ở năm trạm. Trạm Tầng Ánh Sáng ở độ sâu 100 m, Tầng Chạng Vạng ở 600 m, Tầng Nửa Đêm ở 2.500 m, Tầng Đáy Thẳm ở 5.000 m và Tầng Hố Thẳm ở 8.000 m.

                    Tại mỗi trạm, robot ghi lại nhiệt độ, chụp ảnh và lưu cả hai vào nhật ký. Nếu mất tín hiệu, robot dừng khảo sát và quay về tàu. Nhóm điều khiển đối chiếu dữ liệu sau khi thu hồi robot.
                    PASSAGE],
            );
            $start = now()->subMinutes(20);
            $pointsPerCorrectAnswer = 750; // Trả lời đúng trong 10 giây với giới hạn 20 giây.

            for ($number = 1; $number <= 20; $number++) {
                [$zone, $depth] = self::STOPS[($number - 1) % count(self::STOPS)];
                $set = $article->quizSets()->firstOrCreate(
                    ['source' => QuizSet::PAGINATION_DEMO_SOURCE, 'title' => sprintf('%s %02d — %s', self::TOPIC, $number, $zone)],
                    ['topic' => self::TOPIC, 'ocean_zone' => $zone, 'reading_passage' => $article->content, 'is_active' => true, 'created_by' => $user->id],
                );

                if ($set->attempts()->whereBelongsTo($user)->exists()) {
                    continue;
                }

                $questionData = $this->questionsFor($zone, $depth);
                $correctCount = $number % (count($questionData) + 1);
                $attempt = $set->attempts()->make([
                    'user_id' => $user->id,
                    'score' => $correctCount * $pointsPerCorrectAnswer,
                    'ranking_points' => 0,
                    'correct_count' => $correctCount,
                    'total_questions' => count($questionData),
                    'duration_seconds' => 120 + $number,
                ]);
                $attempt->created_at = $attempt->updated_at = $start->copy()->addMinutes($number);
                $attempt->save();

                foreach ($questionData as $index => [$text, $options, $correctIndex, $explanation]) {
                    $question = $set->questions()->firstOrCreate(['question_text' => $text], [
                        'options' => $options,
                        'correct_option_index' => $correctIndex,
                        'explanation' => $explanation,
                        'time_limit' => 20,
                    ]);
                    $isCorrect = $index < $correctCount;
                    $attempt->answers()->create([
                        'question_id' => $question->id,
                        'selected_option_index' => $isCorrect ? $question->correct_option_index : ($question->correct_option_index + 1) % 4,
                        'is_correct' => $isCorrect,
                        'time_taken' => 10,
                        'points_earned' => $isCorrect ? $pointsPerCorrectAnswer : 0,
                    ]);
                }
            }
        });
    }

    private function questionsFor(string $zone, string $depth): array
    {
        $depths = array_column(self::STOPS, 1);
        $otherDepths = array_values(array_diff($depths, [$depth]));

        return [
            ["Robot dừng ở độ sâu bao nhiêu tại trạm $zone?", [$depth, ...array_slice($otherDepths, 0, 3)], 0, "Độ sâu của trạm này là $depth."],
            ['Robot lặn trong chuyến thám hiểm có tên gì?', ['Mòng Biển', 'Hải Âu', 'Chim Cánh Cụt', 'Bồ Nông'], 1, 'Robot có tên là Hải Âu.'],
            ['Robot đo thông số nào tại mỗi trạm?', ['Tốc độ gió', 'Lượng mưa', 'Nhiệt độ', 'Độ ẩm không khí'], 2, 'Robot ghi lại nhiệt độ tại mỗi trạm.'],
            ['Khi mất tín hiệu, robot làm gì?', ['Lặn sâu thêm', 'Đổi sang trạm khác', 'Tiếp tục chụp ảnh', 'Dừng khảo sát và quay về tàu'], 3, 'Robot dừng khảo sát và quay về tàu khi mất tín hiệu.'],
            ['Nhóm điều khiển đối chiếu dữ liệu khi nào?', ['Sau khi thu hồi robot', 'Trước khi robot lặn', 'Tại trạm đầu tiên', 'Khi robot mất tín hiệu'], 0, 'Nhóm điều khiển đối chiếu dữ liệu sau khi thu hồi robot.'],
        ];
    }
}
