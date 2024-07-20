<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function show($record)
    {
        return view('filament.user.resources.subscription-resource.pages.order', ['record' => $record]);
    }
}
