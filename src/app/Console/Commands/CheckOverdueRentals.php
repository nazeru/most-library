<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BookRental;
use App\Enums\BookRentalStatus;

class CheckOverdueRentals extends Command
{
    protected $signature = 'rentals:check-overdue';
    protected $description = 'Check for overdue rentals and update their status';

    public function handle()
    {
        $now = now();

        $overdueRentals = BookRental::whereIn('status', [BookRentalStatus::ACTIVE])
            ->where('due_date', '<', $now)
            ->get();

        foreach ($overdueRentals as $rental) {
            $rental->update(['status' => BookRentalStatus::OVERDUE]);
        }

        $this->info("Обновлены статусы просроченных аренд: " . $overdueRentals->count());
    }
}
