<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function show($record)
    {
        // Cari product berdasarkan UUID
        $product = Product::where('uuid', $record)->firstOrFail();

        // Kembalikan view dengan data product sebagai record
        return view('filament.user.resources.subscription-resource.pages.order', ['product' => $product]);
    }
}
