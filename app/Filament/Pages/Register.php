<?php

namespace App\Filament\Pages;

use Filament\Forms\Form;
use Filament\Pages\Auth\Register;
use Filament\Forms\Components\Wizard;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Notifications\Auth\VerifyEmail;
use Illuminate\Database\Eloquent\Model;

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
                ])->submitAction(new HtmlString(Blade::render(<<<BLADE
                    <x-filament::button
                        type="submit"
                        size="sm"
                        wire:submit="register"
                    >
                        Register
                    </x-filament::button>
                    BLADE))),
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
}

