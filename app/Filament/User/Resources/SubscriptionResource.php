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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Notifications\StatusUpdatedNotification;
use Filament\Tables\Actions\Action;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $title = 'List available product';
    protected static ?string $navigationLabel = 'Products';
    protected static ?string $slug = 'list-available-products';


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
            // ->modifyQueryUsing(function (Builder $query) use ($userId) {
            //     $query->where('user_id', $userId);
            // })
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('harga_jual')
                    ->money('IDR')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('order')
                    ->label('Order')
                    ->successRedirectUrl(route('filament.user.resources.list-available-products.index'))
                    ->url(fn ($record): string => route('filament.user.resources.list-available-products.order', $record->uuid))
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
            'edit' => Pages\EditSubscription::route('/{record}/edit'),
            'order' => Pages\OrderSubscription::route('/{record}/order')
        ];
    }
}
