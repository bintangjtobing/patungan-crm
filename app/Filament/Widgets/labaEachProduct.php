<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\Transaction;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class LabaEachProduct extends BaseWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Transaction::select('product_uuid', DB::raw('
                    COALESCE(SUM(CASE WHEN jenis_transaksi = 1 THEN harga ELSE 0 END), 0) -
                    COALESCE(SUM(CASE WHEN jenis_transaksi = 0 THEN harga ELSE 0 END), 0) as total_laba
                '))
                ->groupBy('product_uuid')
                ->orderBy('total_laba', 'desc')
            )
            ->defaultPaginationPageOption(5)
            ->columns([
                Tables\Columns\TextColumn::make('product.nama')
                    ->label('Product Name')
                    ->getStateUsing(function ($record) {
                        $product = Product::find($record->product_uuid);
                        return $product ? $product->nama : null;
                    }),
                Tables\Columns\TextColumn::make('total_laba')
                    ->label('Total Laba')
                    ->money('IDR')
                    ->formatStateUsing(function ($state) {
                        $color = $state >= 0 ? 'green' : 'red';
                        return '<span style="color: ' . $color . ';">IDR ' . number_format($state, 0, ',', '.') . '</span>';
                    })
                    ->html(),
            ]);
    }

    public function getTableRecordKey(Model $record): string
    {
        return $record->product_uuid;
    }
}
