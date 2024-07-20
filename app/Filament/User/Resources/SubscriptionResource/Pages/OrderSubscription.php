<?php

namespace App\Filament\User\Resources\SubscriptionResource\Pages;

use App\Filament\User\Resources\SubscriptionResource;
use Filament\Resources\Pages\EditRecord;
use App\Models\Product;

class OrderSubscription extends EditRecord
{
    protected static string $resource = SubscriptionResource::class;

    public $product;

    protected static string $view = 'filament.user.resources.subscription-resource.pages.order';
}