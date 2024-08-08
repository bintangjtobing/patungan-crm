<?php
namespace App\Filament\User\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Transaction;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\BaseFileUpload;
use League\Flysystem\UnableToCheckFileExistence;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use App\Filament\User\Resources\ListPaymentsResource\Pages;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use App\Filament\User\Resources\ListPaymentsResource\RelationManagers;
use Illuminate\Validation\Rule;

class ListPaymentsResource extends Resource
{
    protected static ?string $model = Transaction::class;
    protected static ?string $navigationLabel = 'Payments';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('bukti_transaksi')
                    ->image()
                    ->saveUploadedFileUsing(function (BaseFileUpload $component, TemporaryUploadedFile $file): ?string {
                        try {
                            if (!$file->exists()) {
                                return null;
                            }
                        } catch (UnableToCheckFileExistence $exception) {
                            return null;
                        }

                        return Cloudinary::upload($file->getRealPath())->getSecurePath();
                    })
                    ->columnSpan('full')
                    ->reactive()
                    ->rules(['nullable', 'file', Rule::unique('transactions', 'bukti_transaksi')->where(function ($query) {
                        return $query->where('user_id', Auth::user()->id);
                    })])
                    ->helperText(function ($state) {
                        return $state ? 'Anda sudah mengunggah bukti transaksi.' : null;
                    })
                    ->required(fn ($state) => !$state),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                return $query->where('user_id', Auth::user()->id)
                    ->where('status', 0)
                    ->orderBy('created_at', 'desc');
            })
            ->columns([
                Tables\Columns\TextColumn::make('product.nama'),
                Tables\Columns\TextColumn::make('harga')
                    ->money('IDR'),
                    Tables\Columns\SelectColumn::make('status')
                    ->label('Status')
                    ->disabled()
                    ->options([
                        0 => 'Pending',
                        1 => 'Selesai',
                        2 => 'Batal',
                    ]),
                Tables\Columns\ImageColumn::make('bukti_transaksi')
                    ->defaultImageUrl(url('https://res.cloudinary.com/du0tz73ma/image/upload/v1700279273/building_z7thy7.png')),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('unggah bukti'),
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
            'index' => Pages\ListListPayments::route('/'),
            'create' => Pages\CreateListPayments::route('/create'),
            'edit' => Pages\EditListPayments::route('/{record}/edit'),
        ];
    }
}
