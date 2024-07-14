<?php

namespace App\Filament\Resources\KredentialCustomerResource\Pages;

use App\Filament\Resources\KredentialCustomerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKredentialCustomer extends EditRecord
{
    protected static string $resource = KredentialCustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
