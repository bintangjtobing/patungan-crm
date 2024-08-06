<?php

namespace App\Filament\User\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\Transaction;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class expenditureOfEachProduct extends BaseWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Transaction::where('jenis_transaksi', 1)
                ->where('user_id', auth()->user()->id)
                ->select('product_uuid', DB::raw('SUM(harga) as total_pengeluaran'))
                ->groupBy('product_uuid')
                ->orderBy('total_pengeluaran', 'desc')
            )
            ->defaultPaginationPageOption(5)
            ->columns([
                Tables\Columns\TextColumn::make('product.nama')
                    ->label('Product Name')
                    ->getStateUsing(function ($record) {
                        $product = Product::find($record->product_uuid);
                        return $product ? $product->nama : null;
                    }),
                Tables\Columns\TextColumn::make('total_pengeluaran')
                    ->label('Total pengeluaran')
                    ->money('IDR')
                    ->formatStateUsing(function ($state) {
                        $color = 'red';
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
