<?php

namespace App\Filament\User\Resources;

use App\Filament\User\Resources\ProductsResource\Pages;
use App\Filament\User\Resources\ProductsResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Actions\Action;

class ProductsResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('harga_jual')
                    ->label('harga')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\ImageColumn::make('url_image')
                    ->defaultImageUrl(url('https://res.cloudinary.com/du0tz73ma/image/upload/v1700278579/default-profile_y2huqf.jpg')),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('order')
                    ->label('order')
                    ->url(fn ($record): string => route('filament.user.resources.products.order', ['record' => $record->uuid])) // Ensure this line is correct
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
            'index' => Pages\ListProducts::route('/'),
            'order' => Pages\OrderProduct::route('/{record}/order'),
        ];
    }
}
