<?php

namespace App\Livewire;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Livewire\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\BaseFileUpload;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use League\Flysystem\UnableToCheckFileExistence;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class Register extends Component implements HasForms
{

    use InteractsWithForms;

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('username')
                ->maxLength(255)
                ->extraAttributes(['class' => 'border border-gray-300 rounded']), // Add Tailwind classes
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

    public function render()
    {
        return view('livewire.register');
    }
}
