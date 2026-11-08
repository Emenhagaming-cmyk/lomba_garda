<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Shop::query()
            ->with(['items:id,shop_id,name,price,unit'])
            ->active();

        if ($request->filled('search')) {
            $search = '%'.$request->string('search')->trim().'%';
            $query->where(function ($sub) use ($search) {
                $sub->where('name', 'like', $search)
                    ->orWhere('city', 'like', $search)
                    ->orWhere('address', 'like', $search);
            });
        }

        if ($request->boolean('open')) {
            $query->open();
        }

        if ($request->boolean('shipping')) {
            $query->where('supports_shipping', true);
        }

        if ($request->boolean('cod')) {
            $query->where('supports_cod', true);
        }

        $shops = $query->orderBy('distance_km')->limit(12)->get();

        $temporarilyClosed = Shop::query()
            ->active()
            ->whereNotNull('reopens_on')
            ->orderBy('distance_km')
            ->first();

        return response()->json([
            'data' => [
                'shops' => $shops->map(fn (Shop $shop) => $this->serializeShop($shop)),
                'temporarily_closed' => $temporarilyClosed ? $this->serializeShop($temporarilyClosed) : null,
            ],
        ]);
    }

    private function serializeShop(Shop $shop): array
    {
        return [
            'id' => $shop->id,
            'name' => $shop->name,
            'city' => $shop->city,
            'address' => $shop->address,
            'phone' => $shop->phone,
            'open_hours' => $shop->open_hours,
            'status' => $shop->status,
            'rating' => $shop->rating,
            'reviews_count' => $shop->reviews_count,
            'distance_km' => $shop->distance_km,
            'map_x' => $shop->map_x,
            'map_y' => $shop->map_y,
            'supports_shipping' => $shop->supports_shipping,
            'supports_cod' => $shop->supports_cod,
            'reopens_on' => $shop->reopens_on?->toDateString(),
            'items' => $shop->relationLoaded('items')
                ? $shop->items->map(fn ($item) => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'price' => $item->price,
                    'unit' => $item->unit,
                ])
                : [],
        ];
    }
}
