<?php

namespace App\Http\Controllers;

use App\Models\Product;

class CatalogController extends Controller
{
    public function index()
    {
        return view('home', [
            'products'  => Product::listed()->with('variants')->latest('id')->get(),
            'discovery' => Product::where('is_set', true)->where('is_active', true)->with('variants')->first(),
        ]);
    }
}
