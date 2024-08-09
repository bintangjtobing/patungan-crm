<?php

namespace App\Filament\User\Resources\ListPaymentsResource\Pages;

use App\Filament\User\Resources\ListPaymentsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListListPayments extends ListRecords
{
    protected static string $resource = ListPaymentsResource::class;
    protected static ?string $title = 'List payments';

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
