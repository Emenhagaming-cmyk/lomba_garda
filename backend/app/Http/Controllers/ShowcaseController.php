<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ShowcaseController extends Controller
{
    public function index(): JsonResponse
    {
        $products = Product::query()
            ->with('business:id,name,type,address')
            ->where('is_showcased', true)
            ->where('is_active', true)
            ->whereHas('business')
            ->orderByDesc('updated_at')
            ->limit(12)
            ->get()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'category' => $product->category,
                'description' => $product->description,
                'sell_price' => $product->sell_price,
                'unit' => $product->unit,
                'business' => [
                    'id' => $product->business->id,
                    'name' => $product->business->name,
                    'type' => $product->business->type,
                    'address' => $product->business->address,
                ],
            ]);

        return response()->json(['data' => ['products' => $products]]);
    }
}
