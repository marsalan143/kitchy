<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index()
    {
        $items = MenuItem::where('status', true)
            ->select('uuid', 'name', 'category', 'unit_type', 'price_per_unit', 'image')
            ->get();
        
        return response()->json($items);
    }

    public function categories()
    {
        $categories = MenuItem::where('status', true)
            ->distinct()
            ->pluck('category');
        
        return response()->json($categories);
    }
}
