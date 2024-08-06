<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Cusomers', User::query()->where('type', 1)->count())
                ->description('All Cusomer from database')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success'),
            Stat::make('Products', Product::query()->count())
                ->description('All products from database')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('danger'),
            Stat::make('Transaction', Transaction::query()->count())
                ->description('All transactions')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Income', function () {
                $currentMonth = Carbon::now()->month;
                $currentYear = Carbon::now()->year;

                $totalIncome = Transaction::query()
                    ->where('jenis_transaksi', 1)
                    ->whereMonth('created_at', $currentMonth)
                    ->whereYear('created_at', $currentYear)
                    ->sum('harga');

                return 'IDR ' . number_format($totalIncome, 0, ',', '.');
            })
                ->description('Income for this month')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

        ];
    }
}
