<?php

namespace App\Filament\User\Resources\SubscriptionResource\Pages;

use App\Filament\User\Resources\SubscriptionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSubscriptions extends ListRecords
{
    protected static string $resource = SubscriptionResource::class;
    protected static ?string $title = 'List subscriptions';

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }

     public function getBreadcrumbs(): array
    {
        return [
            route('filament.user.resources.subscriptions.index') => 'Subscription',
            'current' => 'List',
        ];
    }
}
