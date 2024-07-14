<?php

namespace App\Filament\Resources\KredentialCustomerResource\Pages;

use App\Filament\Resources\KredentialCustomerResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewKredentialCustomer extends ViewRecord
{
    protected static string $resource = KredentialCustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}