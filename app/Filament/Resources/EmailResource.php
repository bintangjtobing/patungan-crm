<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Models\Email;
use Filament\Forms\Form;
use App\Mail\GenericEmail;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Mail;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\RichEditor;
use Filament\Tables\Actions\CreateAction;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\EmailResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\EmailResource\RelationManagers;

class EmailResource extends Resource
{
    protected static ?string $model = Email::class;

    protected static ?string $navigationGroup = 'System management';

    protected static ?int $navigationSort = 3;

    protected static bool $canCreateAnother = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('Subject')
                    ->columnSpanFull(),
                Forms\Components\RichEditor::make('Object')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $query->orderBy('created_at', 'desc');
            })
            ->columns([
                Tables\Columns\TextColumn::make('subject'),
                TextColumn::make('user.username')
                    ->label('User')
                    ->getStateUsing(function ($record) {
                        $user = $record->user->first();
                        return $user ? ($user->username ?: $user->name) : 'N/A';
                    }),
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('body')
                    ->getStateUsing(function ($record) {
                        $text = strip_tags($record->body); // Hapus tag HTML
                        return Str::limit($text, 50); // Batasi panjang teks
                    }),
            ])
            ->headerActions([
                Action::make('sendEmail')
                ->label('Send Email')
                ->form([
                    Select::make('user')
                        ->label('Send to')
                        ->searchable()
                        ->options(
                            User::all()->mapWithKeys(function ($user) {
                                // Menggunakan username atau name sebagai fallback
                                $label = $user->username ?? $user->name;
                                return [$user->id => $label];
                            })->toArray()
                        )
                        ->required(),
                    Select::make('type')
                        ->label('Type')
                        ->options([
                            'rimender' => 'Rimender',
                            'welcome' => 'Welcome',
                        ])
                        ->required(),
                    TextInput::make('subject')
                        ->default('Pengingat Pembayaran Invoice #[Nomor Invoice]')
                        ->required(),
                    Hidden::make('nameApp')
                        ->default('patunganYuk'),
                    RichEditor::make('body')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $user = User::find($data['user']);
                    $email = Email::create([
                        'user_id' => $user->id, // Menggunakan ID user
                        'type' => $data['type'], // Mengambil type dari data
                        'subject' => $data['subject'],
                        'body' => $data['body'],
                        'status' => 'pending',
                        'tanggal_waktu_terkirim' => Carbon::now(),
                    ]);

                    try {
                        Mail::to($user->email)
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
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
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

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmails::route('/'),
            // 'create' => Pages\CreateEmail::route('/create'),
            'edit' => Pages\EditEmail::route('/{record}/edit'),
        ];
    }
}
