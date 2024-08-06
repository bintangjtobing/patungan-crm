<?php
namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Transaction;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class TopSelings extends BaseWidget
{

    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Transaction::where('jenis_transaksi', 1)->select('product_uuid', DB::raw('SUM(harga) as total_harga'))
                    ->groupBy('product_uuid')
                    ->orderBy('total_harga', 'desc')
            )
            ->defaultPaginationPageOption(5)
            ->columns([
                Tables\Columns\TextColumn::make('product.nama')
                    ->label('Product Name')
                    // ->sortable()
                    ->getStateUsing(function ($record) {
                        return $record->product ? $record->product->nama : null;
                    }),
                Tables\Columns\TextColumn::make('total_harga')
                    ->label('Total Harga')
                    ->money('IDR'),
                    // ->sortable(),
            ]);
    }

    public function getTableRecordKey(Model $record): string
    {
        return $record->product_uuid;
    }
}
