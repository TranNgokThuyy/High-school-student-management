<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Sao lưu dữ liệu tự động mỗi ngày lúc 01:30 (cần cron chạy `php artisan schedule:run` mỗi phút)
Schedule::command('sao-luu:tao')->dailyAt('01:30');
