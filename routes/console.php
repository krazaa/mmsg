<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('installments:send-upcoming-reminders')
    ->dailyAt('09:00')
    ->timezone('Asia/Karachi')
    ->withoutOverlapping();

Schedule::command('email-campaigns:dispatch')
    ->everyFiveMinutes()
    ->withoutOverlapping();
