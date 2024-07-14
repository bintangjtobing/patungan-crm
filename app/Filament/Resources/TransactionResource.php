<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Product;
use Illuminate\Support\Carbon;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use League\Flysystem\UnableToCheckFileExistence;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\Select;
use Filament\Tables\Filters\SelectFilter;
use App\Models\User;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Radio::make('jenis_transaksi')
                    ->label('Jenis transaksi')
                    ->options([
                        0 => 'Pembelian',
                        1 => 'Penjualan'
                    ])
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state == 0) {
                            $set('user_id', auth()->id());
                        } else {
                            $set('user_id', null);
                        }
                    }),

                Forms\Components\Select::make('user_id')
                    ->label('Customer')
                    ->options(function (callable $get) {
                        if ($get('jenis_transaksi') == 1) {
                            return User::where('type', 1)->pluck('name', 'id');
                        }
                        return User::pluck('name', 'id');
                    })
                    ->searchable()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        if ($get('jenis_transaksi') == 1) {
                            $set('user_id', auth()->id());
                        }
                    }),
                // ->disabled(function ($get) {
                //     return $get('jenis_transaksi') == 0; // Disable jika jenis_transaksi adalah Pembelian (0)
                // }),


                Forms\Components\Select::make('product_uuid')
                    ->label('Produk')
                    ->options(Product::all()->pluck('nama', 'uuid'))
                    ->required()
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $product = Product::where('uuid', $state)->first();
                        if ($product) {
                            if ($get('jenis_transaksi') == 0) {
                                $jumlah = $get('jumlah', 1); // Ambil nilai jumlah, jika tidak ada gunakan default 1
                                $harga = $product->harga_beli * $jumlah;
                                $set('harga', $harga);
                            } else {
                                $jumlah = $get('jumlah', 1); // Ambil nilai jumlah, jika tidak ada gunakan default 1
                                $harga = $product->harga_jual * $jumlah;
                                $set('harga', $harga);
                            }
                        } else {
                            $set('harga', null); // Set harga menjadi null jika produk tidak ditemukan
                        }
                    }),

                Forms\Components\TextInput::make('jumlah')
                    ->required()
                    ->maxLength(255)->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $product = Product::where('uuid', $get('product_uuid'))->first();
                        if ($product) {
                            if ($get('jenis_transaksi') == 0) {
                                $harga = $product->harga_beli * $state; // Hitung harga berdasarkan jumlah
                                $set('harga', $harga);
                            } else {
                                $harga = $product->harga_jual * $state; // Hitung harga berdasarkan jumlah
                                $set('harga', $harga);
                            }
                        }
                    }),

                Forms\Components\TextInput::make('harga')
                    ->required()
                    ->maxLength(255)
                    ->default(function ($get) {
                        $product = Product::where('uuid', $get('product_uuid'))->first();
                        if ($product) {
                            if ($get('jenis_transaksi') == 0) {
                                $jumlah = $get('jumlah', 1);
                                return $product->harga_beli * $jumlah;
                            } else {
                                $jumlah = $get('jumlah', 1);
                                return $product->harga_jual * $jumlah;
                            }
                        }
                        return null;
                    })
                    ->extraInputAttributes(['readonly' => true]), // Menambahkan atribut readonly,



                Forms\Components\Select::make('status')
                    ->required()
                    ->options([
                        0 => 'Pending',
                        1 => 'Selesai',
                        2 => 'Batal'
                    ])
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state == 1) {
                            $set('tanggal_waktu_transaksi_selesai', Carbon::now()->setTimezone('Asia/Jakarta')->toDateTimeString());
                        } else {
                            $set('tanggal_waktu_transaksi_selesai', null);
                        }
                    }),

                Forms\Components\DateTimePicker::make('tanggal_waktu_transaksi_selesai')
                    ->reactive(),

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
                    ->reactive(),

                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('jenis_transaksi')
                    ->label('Jenis Transaksi')
                    ->formatStateUsing(function ($state) {
                        return $state === 0
                            ? '<span style="color: red;">Pembelian</span>'
                            : '<span style="color: green;">Penjualan</span>';
                    })
                    ->html(),
                Tables\Columns\TextColumn::make('jumlah')
                    ->searchable(),
                Tables\Columns\TextColumn::make('harga')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(function ($state) {
                        return $state === 0 ? 'Pending' : ($state === 1 ? 'Selesai' : 'Batal');
                    })
                    ->searchable(),
                Tables\Columns\ImageColumn::make('bukti_transaksi')
                    ->defaultImageUrl(url('https://res.cloudinary.com/du0tz73ma/image/upload/v1700279273/building_z7thy7.png')),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('jenis_transaksi')
                    ->label('Jenis transaski')
                    ->options([
                        0 => 'Pembelian',
                        1 => 'Penjualan'
                    ]),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        0 => 'Pending',
                        1 => 'Selesai',
                        2 => 'Batal',
                    ])
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
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
