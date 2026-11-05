<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('quiz_attempts', 'ranking_points')) {
            Schema::table('quiz_attempts', function (Blueprint $table) {
                $table->unsignedInteger('ranking_points')->nullable();
            });
        }

        DB::transaction(function () {
            DB::table('quiz_attempts')->whereNull('ranking_points')
                ->whereIn('quiz_set_id', $this->demoQuizIds())
                ->update(['ranking_points' => 0]);

            $firstAttemptIds = DB::table('quiz_attempts')
                ->selectRaw('MIN(id) AS id')
                ->groupBy('user_id', 'quiz_set_id');

            DB::table('quiz_attempts')->whereNull('ranking_points')
                ->whereIn('id', DB::query()->fromSub($firstAttemptIds, 'first_attempts')->select('id'))
                ->update(['ranking_points' => DB::raw('score')]);

            $deductions = DB::table('quiz_attempts')->whereNull('ranking_points')
                ->selectRaw('user_id, -SUM(score) AS adjustment')->where('score', '>', 0);
            $this->adjustUserScores($deductions);

            DB::table('quiz_attempts')->whereNull('ranking_points')->update(['ranking_points' => 0]);
        });
    }

    public function down(): void
    {
        DB::transaction(function () {
            // Restore the previous scoring rule for every regular attempt.
            $credits = DB::table('quiz_attempts')->whereNotIn('quiz_set_id', $this->demoQuizIds())
                ->whereColumn('score', '>', 'ranking_points')
                ->selectRaw('user_id, SUM(score - ranking_points) AS adjustment');
            $this->adjustUserScores($credits);
            DB::table('quiz_attempts')->update(['ranking_points' => null]);
        });

        Schema::table('quiz_attempts', fn (Blueprint $table) => $table->dropColumn('ranking_points'));
    }

    private function demoQuizIds(): Builder
    {
        return DB::table('quiz_sets')->select('id')->where('source', 'pagination_demo');
    }

    private function adjustUserScores(Builder $adjustments): void
    {
        $adjustments->groupBy('user_id')->chunkById(100, function ($users) {
            foreach ($users as $user) {
                $account = DB::table('users')->where('id', $user->user_id);
                $totalScore = (int) $account->lockForUpdate()->value('total_score');
                $account->update(['total_score' => max(0, $totalScore + (int) $user->adjustment)]);
            }
        }, 'user_id');
    }
};
