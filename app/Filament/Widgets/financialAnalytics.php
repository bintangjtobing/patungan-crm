<?php

namespace App\Filament\Widgets;

use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use App\Models\Transaction;

class FinancialAnalytics extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'financialAnalytics';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'financial Analytics';

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        // Fetch data from the transactions
        $transactions = Transaction::selectRaw('
            DATE_FORMAT(created_at, "%Y-%m") as month,
            SUM(CASE WHEN jenis_transaksi = 1 THEN harga ELSE 0 END) as total_income,
            SUM(CASE WHEN jenis_transaksi = 0 THEN harga ELSE 0 END) as total_outcome
        ')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Initialize arrays for chart data
        $incomeData = [];
        $outcomeData = [];
        $profitData = [];
        $months = [];

        // Process each transaction and populate chart data arrays
        foreach ($transactions as $transaction) {
            $incomeData[] = $transaction->total_income;
            $outcomeData[] = $transaction->total_outcome;
            $profitData[] = $transaction->total_income - $transaction->total_outcome; // Calculate profit
            $months[] = $transaction->month;
        }

        // Return chart options
        return [
            'chart' => [
                'type' => 'bar',
                'height' => 300,
            ],
            'series' => [
                [
                    'name' => 'Income',
                    'data' => $incomeData,
                ],
                [
                    'name' => 'Outcome',
                    'data' => $outcomeData,
                ],
                [
                    'name' => 'Profit',
                    'data' => $profitData,
                ],
            ],
            'xaxis' => [
                'categories' => $months, // Months as categories
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'yaxis' => [
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'colors' => ['#f59e0b', '#2dd4bf', '#f472b6'], // Colors for each series
            'plotOptions' => [
                'bar' => [
                    'borderRadius' => 3,
                    'horizontal' => false,
                    'dataLabels' => [
                        'position' => 'top', // Show data labels on top of the bars
                    ],
                ],
            ],
            'dataLabels' => [
                'enabled' => true,
                'offsetY' => -20,
                'style' => [
                    'fontSize' => '12px',
                    'colors' => ['#304758']
                ],
            ],
            'legend' => [
                'position' => 'top',
            ],
        ];
    }
}
