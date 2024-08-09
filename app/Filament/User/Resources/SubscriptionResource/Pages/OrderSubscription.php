<?php

namespace App\Filament\User\Resources\SubscriptionResource\Pages;

use App\Models\Product;
use Filament\Resources\Pages\EditRecord;
use App\Filament\User\Resources\SubscriptionResource;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Illuminate\Contracts\Support\Htmlable;
use App\Models\Transaction;

class OrderSubscription extends EditRecord
{
    use InteractsWithRecord;

    protected static string $resource = SubscriptionResource::class;

    // public $product;

    protected static string $view = 'filament.user.resources.subscription-resource.pages.order';
    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }
    public function getTitle(): string|Htmlable
    {
        return 'Order produk';
    }

}
