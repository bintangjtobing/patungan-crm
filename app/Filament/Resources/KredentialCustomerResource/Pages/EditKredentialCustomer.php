<?php

namespace App\Filament\Resources\KredentialCustomerResource\Pages;

use App\Filament\Resources\KredentialCustomerResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditKredentialCustomer extends EditRecord
{
    protected static string $resource = KredentialCustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Credential on customer Updated.')
            ->body('The Credential on customer details have been successfully updated.');
    }
}