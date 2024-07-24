<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentsResource\Pages;
use App\Filament\Resources\PaymentsResource\RelationManagers;
use App\Models\ListPayments;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentsResource extends Resource
{
    protected static ?string $model = ListPayments::class;
    protected static ?string $navigationGroup = 'Transaction';
    protected static ?int $navigationSort = 2;
    // protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        $status = 0;
        return $table
            ->modifyQueryUsing(function (Builder $query) use ($status) {
                return $query->where('status', $status)
                    ->orderBy('created_at', 'desc');;
            })
            ->columns([
                Tables\Columns\TextColumn::make('jenis_transaksi')
                    ->label('Jenis Transaksi')
                    ->formatStateUsing(function ($state) {
                        return $state === 0
                            ? '<span style="color: red;">Pembelian</span>'
                            : '<span style="color: green;">Penjualan</span>';
                    })
                    ->html(),
                    Tables\Columns\TextColumn::make('user.username')
                    ->label('User')
                    ->getStateUsing(fn ($record) => $record->user->username ? $record->user->username : $record->user->name),
                Tables\Columns\TextColumn::make('product.nama')->label('Product'),
                Tables\Columns\TextColumn::make('jumlah')
                    ->numeric()
                    ->searchable(),
                Tables\Columns\TextColumn::make('harga')
                    ->money('IDR')
                    ->searchable(),
                Tables\Columns\SelectColumn::make('status')
                    ->label('Status')
                    ->options([
                        0 => 'Pending',
                        1 => 'Selesai',
                        2 => 'Batal',
                    ]),
                Tables\Columns\ImageColumn::make('bukti_transaksi')
                    ->defaultImageUrl(url('https://res.cloudinary.com/du0tz73ma/image/upload/v1700279273/building_z7thy7.png')),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal transaksi')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayments::route('/create'),
            'edit' => Pages\EditPayments::route('/{record}/edit'),
        ];
    }
}
