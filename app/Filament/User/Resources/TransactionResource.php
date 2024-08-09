<?php

namespace App\Filament\User\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\User\Resources\TransactionResource\Pages;
use App\Filament\User\Resources\TransactionResource\RelationManagers;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Transaction History';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        $user = Auth::user(); // Get the authenticated user
        $userId = $user->id; // Assuming 'id' is the user ID field in your
        return $table
            ->modifyQueryUsing(function (Builder $query) use ($userId) {
                $query->where('user_id', $userId)
                    ->where("status", 1)
                    ->orderBy('created_at', 'desc'); // or any other column you want to sort by
            })
            ->columns([
                Tables\Columns\TextColumn::make('product.nama')
                    ->label('Product'),
                Tables\Columns\TextColumn::make('product.harga_jual')
                    ->label('Harga')
                    ->money('IDR')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_waktu_transaksi_selesai')
                    ->label('Tanggal Pesanan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('status'),
                Tables\Columns\TextColumn::make('modified_transaction_date')
                    ->label('Langganan Berakhir')
                    ->getStateUsing(function ($record) {
                        return $record->modified_transaction_date;
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('unduh')
                    ->url(fn($record): string => route('pdf.dwonload', ['id' => $record->id])),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
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
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            // 'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
