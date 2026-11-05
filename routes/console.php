<?php

use App\Services\TimedQuizRuns;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('quiz:advance-runs', function (TimedQuizRuns $runs) {
    $runs->advanceDue();
})->purpose('Advance and finish timed quiz runs that have reached their deadlines');

Schedule::command('quiz:advance-runs')->everyFiveSeconds()->withoutOverlapping(1);
