<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KredentialCustomerResource\Pages;
use App\Filament\Resources\KredentialCustomerResource\RelationManagers;
use App\Models\KredentialCustomer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\User;
use App\Models\Product;

class KredentialCustomerResource extends Resource
{
    protected static ?string $model = KredentialCustomer::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Customer')
                    ->options(User::where('type', 1)->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('product_uuid')
                    ->label('Product')
                    ->options(Product::all()->pluck('nama', 'uuid'))
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('pin')
                    ->maxLength(255),
                Forms\Components\TextInput::make('email_akses')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('profil_akes')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('email_akses')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pin')
                    ->searchable(),
                Tables\Columns\TextColumn::make('profil_akes')
                    ->searchable(),
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
            'index' => Pages\ListKredentialCustomers::route('/'),
            'create' => Pages\CreateKredentialCustomer::route('/create'),
            'edit' => Pages\EditKredentialCustomer::route('/{record}/edit'),
        ];
    }
}
