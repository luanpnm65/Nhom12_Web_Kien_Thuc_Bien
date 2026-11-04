<?php

namespace Tests\Feature;

use App\Models\AiGenerationLog;
use App\Models\Article;
use App\Models\Question;
use App\Models\QuizAttempt;
use App\Models\QuizSet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class QuestionGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_du_lieu_moi_va_quan_he_nhat_ky(): void
    {
        $this->seed();

        $this->assertDatabaseCount('articles', 2);
        $this->assertDatabaseCount('quiz_sets', 2);
        $this->assertDatabaseCount('questions', 10);
        $this->assertDatabaseCount('ai_generation_logs', 0);

        $this->assertSame(
            0,
            QuizSet::whereNull('article_id')->count()
        );

        $set = QuizSet::firstOrFail();
        $article = $set->article;
        $admin = User::where('email', 'admin@ocean.io')->firstOrFail();

        $this->assertInstanceOf(Article::class, $article);
        $this->assertSame($set->reading_passage, $article->content);
        $this->assertSame('manual', $set->source);

        $log = AiGenerationLog::create([
            'user_id' => $admin->id,
            'article_id' => $article->id,
            'quiz_set_id' => $set->id,
            'model' => 'kiem-thu-c01',
            'status' => 'pending',
        ]);

        $this->assertTrue($log->user->is($admin));
        $this->assertTrue($log->article->is($article));
        $this->assertTrue($log->quizSet->is($set));
    }

    public function test_nang_cap_giu_nguyen_du_lieu_cu(): void
    {
        $migration = require database_path(
            'migrations/2026_11_03_000001_add_question_generation.php'
        );

        // Mô phỏng cấu trúc trước C01 trong database kiểm thử.
        $migration->down();

        $admin = User::factory()->create([
            'role' => 'admin',
            'total_score' => 3456,
            'quizzes_completed' => 1,
        ]);

        $oldSet = [
            'title' => 'Bộ đề cũ',
            'topic' => 'Biển',
            'ocean_zone' => 'Tầng ánh sáng',
            'reading_passage' => 'Nội dung bài đọc cũ.',
            'source_url' => 'https://example.com/bai-doc',
            'created_by' => $admin->id,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $publishedId = DB::table('quiz_sets')->insertGetId(
            $oldSet + ['is_active' => true]
        );

        $draftId = DB::table('quiz_sets')->insertGetId(
            $oldSet + ['is_active' => false]
        );

        $question = Question::create([
            'quiz_set_id' => $publishedId,
            'question_text' => 'Câu hỏi cũ?',
            'options' => ['A', 'B', 'C', 'D'],
            'correct_option_index' => 1,
            'explanation' => 'Giải thích cũ.',
            'time_limit' => 20,
        ]);

        $attempt = QuizAttempt::create([
            'user_id' => $admin->id,
            'quiz_set_id' => $publishedId,
            'score' => 999,
            'correct_count' => 1,
            'total_questions' => 1,
            'duration_seconds' => 10,
        ]);

        $questionBefore = $question->getAttributes();
        $attemptBefore = $attempt->getAttributes();

        $migration->up();

        $published = QuizSet::findOrFail($publishedId);
        $draft = QuizSet::findOrFail($draftId);

        $this->assertTrue($published->is_active);
        $this->assertFalse($draft->is_active);
        $this->assertDatabaseCount('articles', 2);

        foreach ([$published, $draft] as $set) {
            $this->assertNotNull($set->article);
            $this->assertSame(
                'Nội dung bài đọc cũ.',
                $set->article->content
            );
            $this->assertSame(
                'https://example.com/bai-doc',
                $set->article->source_url
            );
        }

        $this->assertEquals(
            $questionBefore,
            $question->fresh()->getAttributes()
        );

        $this->assertEquals(
            $attemptBefore,
            $attempt->fresh()->getAttributes()
        );

        $this->assertSame(3456, $admin->fresh()->total_score);
        $this->assertSame(1, $admin->fresh()->quizzes_completed);
    }
}