<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quiz_set_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quiz_attempt_id')->nullable()->unique()->constrained()->cascadeOnDelete();
            $table->string('phase');
            $table->unsignedInteger('question_index')->default(0);
            $table->unsignedBigInteger('quiz_started_ms')->nullable();
            $table->unsignedBigInteger('deadline_ms')->nullable()->index();
            $table->json('answers');
            $table->timestamps();
            $table->index(['user_id', 'quiz_set_id', 'quiz_attempt_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_runs');
    }
};
