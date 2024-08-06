<?php

namespace App\Filament\User\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class expands extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Outcom', function () {
                $currentMonth = Carbon::now()->month;
                $currentYear = Carbon::now()->year;

                $totalIncome = Transaction::query()
                    ->where('jenis_transaksi', 1)
                    ->where('user_id', Auth::user()->id)
                    ->whereMonth('created_at', $currentMonth)
                    ->whereYear('created_at', $currentYear)
                    ->sum('harga');

                return 'IDR ' . number_format($totalIncome, 0, ',', '.');
            })
                ->description('Outcome for this month')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('danger'),
        ];
    }
}
