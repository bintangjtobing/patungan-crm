<?php

namespace App\Filament\User\Resources;

use App\Filament\User\Resources\SubscriptionResource\Pages;
use App\Filament\User\Resources\SubscriptionResource\RelationManagers;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Notifications\StatusUpdatedNotification;
use Filament\Tables\Actions\Action;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $title = 'List available product';
    protected static ?string $navigationLabel = 'Subscription';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function(Builder $query) {
                $query->where('user_id', Auth::user()->id)
                ->where('status' , 1)
                ->whereIn('id', function ($subQuery) {
                    $subQuery->selectRaw('MAX(id)')
                        ->from('transactions')
                        ->groupBy('product_uuid');
                });
            })
            ->columns([
                Tables\Columns\TextColumn::make('product.nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('harga')
                    ->money('IDR')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('modified_transaction_date')
                    ->label('Langganan Berakhir')
                    ->getStateUsing(function ($record) {
                        return $record->modified_transaction_date;
                    })
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('order')
                    ->label('Perpanjang')
                    ->url(fn ($record): string => route('filament.user.resources.subscriptions.order', ['record' => $record->id]))
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
            'index' => Pages\ListSubscriptions::route('/'),
            'create' => Pages\CreateSubscription::route('/create'),
            // 'edit' => Pages\EditSubscription::route('/{record}/edit'),
            'order' => Pages\OrderSubscription::route('/{record}/order')
        ];
    }
}
