<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizRun extends Model
{
    protected $fillable = ['user_id', 'quiz_set_id', 'phase', 'deadline_ms', 'answers'];

    protected function casts(): array
    {
        return [
            'question_index' => 'integer',
            'quiz_started_ms' => 'integer',
            'deadline_ms' => 'integer',
            'answers' => 'array',
        ];
    }

    public function quizSet()
    {
        return $this->belongsTo(QuizSet::class);
    }

    public function quizAttempt()
    {
        return $this->belongsTo(QuizAttempt::class);
    }
}
