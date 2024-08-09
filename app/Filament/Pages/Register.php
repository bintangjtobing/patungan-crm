<?php

namespace App\Filament\Pages;

use App\Models\Product;
use Filament\Forms\Form;
use App\Models\Transaction;
use Filament\Pages\Auth\Register;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Wizard;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Auth\VerifyEmail;
use Filament\Forms\Components\BaseFileUpload;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use League\Flysystem\UnableToCheckFileExistence;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

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

            // Add transaction data
            Transaction::create([
                'user_id' => $user->id,
                'product_uuid' => $data['product_uuid'],
                'harga' => $data['harga'],
                'bukti_transaksi' => $data['bukti_transaksi'],
                'status' => 0,
                'jenis_transaksi' => 1,
            ]);

            $this->callHook('afterRegister');

            // Send the registration success notification
            $user->notify(new \App\Notifications\UserRegisteredNotification($user));

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
            ->image()
            ->label('Bukti Transaksi')
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
            ->reactive();
    }
}
