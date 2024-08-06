<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Models\Email;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\ListPayments;
use App\Models\Rekening; // Tambahkan ini
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\RichEditor;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\PaymentsResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Mail\GenericEmail;
use App\Filament\Resources\PaymentsResource\RelationManagers;
use Filament\Forms\Components\Hidden;

class PaymentsResource extends Resource
{
    protected static ?string $model = ListPayments::class;
    protected static ?string $navigationGroup = 'Transaction';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Form schema
            ]);
    }

    public static function table(Table $table): Table
    {
        $status = 0;

        return $table
            ->modifyQueryUsing(function (Builder $query) use ($status) {
                return $query->where('status', $status)
                    ->orderBy('created_at', 'desc');
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
                Tables\Columns\TextColumn::make('modified_transaction_date')
                    ->label('Langganan Berakhir')
                    ->getStateUsing(function ($record) {
                        return $record->modified_transaction_date;
                    }),
                Tables\Columns\ImageColumn::make('bukti_transaksi')
                    ->defaultImageUrl(url('https://res.cloudinary.com/du0tz73ma/image/upload/v1700279273/building_z7thy7.png')),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal transaksi')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Table filters
            ])
            ->actions([
                Action::make('sendEmail')
                    ->label('Kirim pengingat')
                    ->form(function (ListPayments $record) {
                        $rekening = Rekening::where('is_active', 1)->first(); // Dapatkan data rekening pertama, atau bisa dimodifikasi sesuai kebutuhan

                        $textBody = '<p>Halo [Nama Pelanggan],</p>

                            <p>Kami harap Anda dalam keadaan baik.</p>

                            <p>Kami ingin mengingatkan Anda bahwa pembayaran untuk [Nama Produk] sebesar [Jumlah Pembayaran] jatuh tempo pada [Tanggal Jatuh Tempo]. Hingga saat ini, kami belum menerima pembayaran dari Anda.</p>

                            <p>Detail pembayaran:</p>

                            <ul>
                                <li>Nama Produk: [Nama Produk]</li>
                                <li>Jumlah Pembayaran: [Jumlah Pembayaran]</li>
                                <li>Tanggal Jatuh Tempo: [Tanggal Jatuh Tempo]</li>
                            </ul>
                            <p>Anda dapat melakukan pembayaran melalui [Metode Pembayaran] ke rekening berikut:</p>

                            <ul>
                                <li>Bank: [Nama Bank]</li>
                                <li>Nomor Rekening: [Nomor Rekening]</li>
                                <li>Atas Nama: [Nama Pemilik Rekening]</li>
                            </ul>

                            <p>Jika Anda telah melakukan pembayaran, abaikan email ini dan kami mohon maaf atas ketidaknyamanannya. Jika ada pertanyaan atau membutuhkan bantuan lebih lanjut, jangan ragu untuk menghubungi kami di [Nomor Telepon] atau balas email ini.</p>

                            <p>Terima kasih atas perhatian dan kerjasamanya.</p>

                            <p>Salam hangat,</p>

                            <table>
                                <tr>
                                    <td>[Nama Anda]</td>
                                </tr>
                                <tr>
                                    <td>[Nama Perusahaan]</td>
                                </tr>
                                <tr>
                                    <td>[Nomor Telepon]</td>
                                </tr>
                                <tr>
                                    <td>[Email]</td>
                                </tr>
                            </table>
                        ';

                        return [
                            TextInput::make('subject')
                                ->default('Pengingat Pembayaran produk ' . $record->product->nama)
                                ->required(),
                            Hidden::make('nameApp')->default('patunganYuk'),
                            RichEditor::make('body')
                                ->default(
                                    str_replace(
                                        ['[Nama Pelanggan]', '[Nama Produk]', '[Jumlah Pembayaran]', '[Tanggal Jatuh Tempo]', '[Metode Pembayaran]', '[Nama Bank]', '[Nomor Rekening]', '[Nama Pemilik Rekening]', '[Nomor Telepon]', '[Nama Anda]', '[Nama Perusahaan]', '[Email]'],
                                        [
                                            $record->user->name,
                                            $record->product->nama,
                                            $record->product->harga_jual,
                                            $record->modified_transaction_date,
                                            'Transfer Bank',
                                            $rekening->bank ?? 'Bank Tidak Diketahui', // Nama Bank dari model Rekening
                                            $rekening->no_rek ?? 'Nomor Rekening Tidak Diketahui', // Nomor Rekening dari model Rekening
                                            $rekening->name ?? 'Nama Pemilik Tidak Diketahui', // Nama Pemilik Rekening dari model Rekening
                                            '081234567890',
                                            'Bintang Tobing',
                                            'PatunganYuk',
                                            'patunganYuk@gmail.com',
                                        ],
                                        $textBody
                                    )
                                )
                                ->required(),
                        ];
                    })
                    ->action(function (array $data, ListPayments $record) {
                        $email = Email::create([
                            'user_id' => $record->user->id,
                            'type' => 'reminder',
                            'subject' => $data['subject'],
                            'body' => $data['body'],
                            'status' => 'pending',
                            'tanggal_waktu_terkirim' => Carbon::now(),
                        ]);

                        try {
                            Mail::to($record->user->email)
                                ->send(new GenericEmail($data['subject'], $data['body'], $data['nameApp']));

                            $email->update(['status' => 'success', 'tanggal_waktu_terkirim' => Carbon::now()]);

                            Notification::make()
                                ->title('Email sent successfully!')
                                ->success()
                                ->send();
                        } catch (\Throwable $th) {
                            $email->update(['status' => 'gagal']);

                            Notification::make()
                                ->title('Failed to send email!')
                                ->danger()
                                ->send();
                        }
                    }),
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
            // Relation managers
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            // 'create' => Pages\CreatePayments::route('/create'),
            // 'edit' => Pages\EditPayments::route('/{record}/edit'),
        ];
    }
}
