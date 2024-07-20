<?php

namespace App\Filament\Pages;

use Filament\Forms\Form;
use Filament\Pages\Auth\Register;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\HtmlString;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Notifications\Auth\VerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Select;

class Registration extends Register
{
    protected ?string $maxWidth = '2xl';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Kontak')
                        ->schema([
                            $this->getNameFormComponent(),
                            $this->getEmailFormComponent(),
                            $this->getNoHpFormComponent(),
                            $this->getPasswordFormComponent(),
                            $this->getPasswordConfirmationFormComponent(),
                        ]),
                    Wizard\Step::make('Berlangganan')
                        ->schema([
                            $this->getProductFormComponent(),
                            $this->getHargaProductFormComponent(),
                        ]),
                    Wizard\Step::make('Pembayaran')
                        ->schema([
                            $this->getFileUploadFormComponent(),
                        ]),
                ])->submitAction(new HtmlString(view('components.button', [
                    'type' => 'submit',
                    'size' => 'sm',
                    'wireSubmit' => 'register',
                    'slot' => 'Register',
                ])->render()))
            ]);
    }

    protected function getFormActions(): array
    {
        return [];
    }

    protected function handleRegistration(array $data): Model
    {
        return $this->wrapInDatabaseTransaction(function () use ($data) {
            $this->callHook('beforeValidate');

            $data = $this->form->getState();

            $this->callHook('afterValidate');

            $data = $this->mutateFormDataBeforeRegister($data);

            $this->callHook('beforeRegister');

            $user = $this->getUserModel()::create($data);

            $this->form->model($user)->saveRelationships();

            // Menambahkan data ke tabel Transaction
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'product_uuid' => $data['product_uuid'],
                'harga' => $data['harga'],
                'bukti_transaksi' => $data['bukti_transaksi'],
                'status' => 0, // atau status default lainnya
                'jenis_transaksi' => 1, // atau jenis transaksi yang sesuai
            ]);

            $this->callHook('afterRegister');

            return $user;
        });
    }

    protected function getNameFormComponent(): Component
    {
        return TextInput::make('name')
            ->label('Name')
            ->required();
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Email')
            ->required()
            ->email();
    }

    protected function getNoHpFormComponent(): Component
    {
        return TextInput::make('no_hp')
            ->label('No HP')
            ->required()
            ->tel();
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label('Password')
            ->required()
            ->password();
    }

    protected function getPasswordConfirmationFormComponent(): Component
    {
        return TextInput::make('password_confirmation')
            ->label('Confirm Password')
            ->required()
            ->password();
    }

    protected function getProductFormComponent(): Component
    {
        return Select::make('product_uuid')
            ->label('Produk')
            ->options(Product::all()->pluck('nama', 'uuid'))
            ->required()
            ->searchable()
            ->reactive() // Make it reactive
            ->afterStateUpdated(function ($state, callable $set) {
                $product = Product::where('uuid', $state)->first();
                if ($product) {
                    $set('harga', $product->harga_jual);
                } else {
                    $set('harga', null); // Reset harga if no product is found
                }
            });
    }

    protected function getHargaProductFormComponent(): Component
    {
        return TextInput::make('harga')
            ->label('Harga')
            ->required()
            ->maxLength(255)
            ->default(function ($get) {
                $product = Product::where('uuid', $get('product_uuid'))->first();
                if ($product) {
                    return $product->harga_jual;
                }
                return null;
            })
            ->numeric()
            ->extraInputAttributes(['readonly' => true])
            ->reactive(); // Make it reactive
    }

    protected function getFileUploadFormComponent(): Component
    {
        return FileUpload::make('bukti_transaksi')
            ->label('Bukti Transaksi')
            ->required();
    }
}
