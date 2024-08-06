<?php

namespace App\Filament\User\Resources\ProductsResource\Pages;

use App\Filament\User\Resources\ProductsResource;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;

class OrderProduct extends Page
{
    use InteractsWithRecord;
    protected static string $resource = ProductsResource::class;

    protected static string $view = 'filament.user.resources.products-resource.pages.order-product';

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }
}
