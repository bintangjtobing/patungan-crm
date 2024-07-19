<?php

namespace App\Filament\Pages;

use Filament\Forms\Form;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Button;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SimplePage;

class Payments extends SimplePage
{
    protected ?string $maxWidth = '2xl';

    protected function getNameFormComponent(): Component
    {
        return TextInput::make('name')
            ->label(__('filament-panels::pages/auth/register.form.name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Mengambil komponen formulir default
                $this->getNameFormComponent(),
                // $this->getEmailFormComponent(),
                // $this->getAddresFormComponent(),
                // $this->getNoHpFormComponent(),
                // $this->getProductFormComponent(),
                // $this->getHargaProductFormComponent(),
                // $this->getPasswordFormComponent(),
                // $this->getPasswordConfirmationFormComponent(),
                // $this->getTypeUserFormComponent()
            ]);
    }

    // protected function getGithubFormComponent(): Component
    // {
    //     return TextInput::make('github')
    //         ->prefix('https://github.com/')
    //         ->label(__('GitHub'))
    //         ->maxLength(255);
    // }

    // protected function getTwitterFormComponent(): Component
    // {
    //     return TextInput::make('twitter')
    //         ->prefix('https://x.com/')
    //         ->label(__('Twitter (X)'))
    //         ->maxLength(255);
    // }
}
