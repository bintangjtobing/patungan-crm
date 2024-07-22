<?php

namespace App\Filament\User\Resources\SubscriptionResource\Pages;

use Filament\Resources\Pages\Page;
use App\Filament\User\Resources\SubscriptionResource;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;

class Order extends Page
{
    use InteractsWithRecord;

    protected static string $resource = SubscriptionResource::class;

    protected static string $view = 'filament.user.resources.subscription-resource.pages.order';


    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }
}

