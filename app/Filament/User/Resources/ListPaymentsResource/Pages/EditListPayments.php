<?php

namespace App\Filament\User\Resources\ListPaymentsResource\Pages;

use App\Filament\User\Resources\ListPaymentsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditListPayments extends EditRecord
{
    protected static string $resource = ListPaymentsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
