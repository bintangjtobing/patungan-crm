<?php

namespace App\Filament\User\Resources\SubscriptionResource\Pages;

use App\Filament\User\Resources\SubscriptionResource;
use Filament\Resources\Pages\Page;

class Order extends Page
{
    protected static string $resource = SubscriptionResource::class;

    protected static string $view = 'filament.user.resources.subscription-resource.pages.order';

    public $record;

    public function mount($record): void
    {
        $this->record = $record;
    }
}

