<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ──────────────────────────────────────────────
// Scheduled: Cek stok rendah harian
// Waktu diambil dari config/stockcheck.php → .env STOCK_CHECK_TIME
// ──────────────────────────────────────────────
Schedule::command('stock:check-low')
    ->dailyAt(config('stockcheck.check_time'))
    ->timezone('Asia/Jakarta')
    ->appendOutputTo(storage_path('logs/stock-check.log'));
