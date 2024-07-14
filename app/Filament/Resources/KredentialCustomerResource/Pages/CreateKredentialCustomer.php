<?php

namespace App\Filament\Resources\KredentialCustomerResource\Pages;

use App\Filament\Resources\KredentialCustomerResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateKredentialCustomer extends CreateRecord
{
    protected static string $resource = KredentialCustomerResource::class;

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Credential on customer Created.')
            ->body('The new Credential on customer has been successfully created.');
    }
}