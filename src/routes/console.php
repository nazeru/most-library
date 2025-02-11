<?php

use App\Enums\BookRentalStatus;
use App\Models\BookRental;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    $now = now();

    $overdueRentals = BookRental::whereIn('status', [BookRentalStatus::ACTIVE])
        ->where('due_date', '<', $now)
        ->get();

    foreach ($overdueRentals as $rental) {
        $rental->update(['status' => BookRentalStatus::OVERDUE]);
    }

    info("Обновлены статусы просроченных аренд: " . $overdueRentals->count());
})->everyMinute();