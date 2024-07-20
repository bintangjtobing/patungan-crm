<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function show($record)
    {
        $product = Product::where('uuid', $record)->firstOrFail();
        return view('filament.user.resources.subscription-resource.pages.order', ['product' => $product]);
    }
}