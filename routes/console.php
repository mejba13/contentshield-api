<?php

use App\Jobs\ProcessScheduledMonitoringJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
|
| ContentShield AI scheduled monitoring tasks.
|
*/

// Hourly monitoring (Agency plan)
Schedule::job(new ProcessScheduledMonitoringJob('hourly'))
    ->hourly()
    ->name('monitoring:hourly')
    ->withoutOverlapping();

// Daily monitoring (Pro plan)
Schedule::job(new ProcessScheduledMonitoringJob('daily'))
    ->dailyAt('02:00')
    ->name('monitoring:daily')
    ->withoutOverlapping();

// Weekly monitoring (Starter plan)
Schedule::job(new ProcessScheduledMonitoringJob('weekly'))
    ->weeklyOn(1, '03:00') // Mondays at 3 AM
    ->name('monitoring:weekly')
    ->withoutOverlapping();

// Clean up old scan logs (keep 90 days)
Schedule::command('model:prune', ['--model' => 'App\Models\ScanLog'])
    ->daily()
    ->name('cleanup:scan-logs');

// Check for expired licenses
Schedule::command('licenses:check-expiry')
    ->dailyAt('00:00')
    ->name('licenses:check-expiry');
