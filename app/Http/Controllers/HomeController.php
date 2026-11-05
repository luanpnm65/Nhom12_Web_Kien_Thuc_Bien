<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Services\TimedQuizRuns;

class HomeController extends Controller
{
    public function welcome()
    {
        return view('welcome');
    }

    public function leaderboard(TimedQuizRuns $runs)
    {
        $runs->advanceDue();
        $leaders = User::orderByDesc('total_score')
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return view('leaderboard.index', compact('leaders'));
    }
}
