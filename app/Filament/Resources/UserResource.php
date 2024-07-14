<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Filament\Forms\Components\BaseFileUpload;
use Illuminate\Support\Facades\Log;
use Filament\Tables\Filters\SelectFilter;
use League\Flysystem\UnableToCheckFileExistence;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('username')
                ->maxLength(255),
            TextInput::make('name')
                ->required()
                ->maxLength(255),
            TextInput::make('alamat')
                ->maxLength(255),
            TextInput::make('email')
                ->email()
                ->required()
                ->maxLength(255),
            Radio::make('type')
                ->label('Type')
                ->options([
                    0 => 'User',
                    1 => 'Customer',
                ])
                ->required(),
            TextInput::make('no_hp')
                ->maxLength(255),
            TextInput::make('password')
                ->password()
                ->required(),
            FileUpload::make('profile_picture')
                ->image()
                ->saveUploadedFileUsing(function (BaseFileUpload $component, TemporaryUploadedFile $file) : ?string {
                    try {
                        if(!$file->exists()) {
                            return null;
                        }
                    } catch (UnableToCheckFileExistence $exception) {
                        return null;
                    }

                    return Cloudinary::upload($file->getRealPath())->getSecurePath();
                })
                ->reactive(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('profile_picture')
                    ->label('Profile')
                    ->defaultImageUrl(url('https://res.cloudinary.com/du0tz73ma/image/upload/v1700278579/default-profile_y2huqf.jpg')),
                Tables\Columns\TextColumn::make('username')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('alamat')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->formatStateUsing(function ($state) {
                        return $state === 0 ? 'User' : 'Customer';
                    }),
                Tables\Columns\TextColumn::make('no_hp')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal bergabung')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        0 => 'User',
                        1 => 'Customer',
                    ])
                    ->placeholder('Select Type')
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
