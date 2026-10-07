<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::with('supplier:id,name')
            ->where('business_id', $this->businessId($request->user()));

        if ($request->boolean('low')) {
            $query->lowStock();
        }

        if ($request->filled('search')) {
            $query->where(fn ($q) => $q
                ->where('name', 'like', '%'.$request->search.'%')
                ->orWhere('sku', 'like', '%'.$request->search.'%'));
        }

        $products = $query->orderBy('name')->get();

        return response()->json([
            'data' => [
                'products' => $products->map(fn (Product $p) => $this->serialize($p)),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['sometimes', 'nullable', 'string', 'max:100', Rule::unique('products', 'sku')],
            'category' => ['sometimes', 'nullable', 'string', 'max:100'],
            'variant' => ['sometimes', 'nullable', 'string', 'max:100'],
            'unit' => ['sometimes', 'string', 'max:20'],
            'buy_price' => ['required', 'integer', 'min:0'],
            'sell_price' => ['required', 'integer', 'min:0'],
            'hpp' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'min_stock' => ['sometimes', 'integer', 'min:0'],
            'supplier_id' => ['sometimes', 'nullable', 'integer', 'exists:suppliers,id'],
            'initial_stock' => ['sometimes', 'integer', 'min:0'],
        ]);

        $product = new Product($validated);
        $product->business_id = $this->businessId($request->user());
        $product->hpp = $validated['hpp'] ?? $validated['buy_price'];
        $product->stock = 0;
        $product->save();

        if (($validated['initial_stock'] ?? 0) > 0) {
            $product->increment('stock', $validated['initial_stock']);
            StockMovement::create([
                'product_id' => $product->id,
                'type' => StockMovement::TYPE_IN,
                'quantity' => $validated['initial_stock'],
                'note' => 'Stok awal',
            ]);
        }

        return response()->json(['data' => ['product' => $this->serialize($product->fresh())]], 201);
    }

    public function show(Request $request, Product $product): JsonResponse
    {
        abort_unless($product->business_id === $this->businessId($request->user()), 404);

        return response()->json(['data' => ['product' => $this->serialize(
            $product->load(['supplier:id,name', 'stockMovements' => fn ($q) => $q->latest()->limit(10)])
        )]]);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        abort_unless($product->business_id === $this->businessId($request->user()), 404);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'sku' => ['sometimes', 'nullable', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($product->id)],
            'category' => ['sometimes', 'nullable', 'string', 'max:100'],
            'variant' => ['sometimes', 'nullable', 'string', 'max:100'],
            'unit' => ['sometimes', 'string', 'max:20'],
            'buy_price' => ['sometimes', 'integer', 'min:0'],
            'sell_price' => ['sometimes', 'integer', 'min:0'],
            'hpp' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'min_stock' => ['sometimes', 'integer', 'min:0'],
            'supplier_id' => ['sometimes', 'nullable', 'integer', 'exists:suppliers,id'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $product->fill($validated);
        $product->save();

        return response()->json(['data' => ['product' => $this->serialize($product)]]);
    }

    public function destroy(Request $request, Product $product): JsonResponse
    {
        abort_unless($product->business_id === $this->businessId($request->user()), 404);

        $product->update(['is_active' => false]);

        return response()->json(['data' => null]);
    }

    private function serialize(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'category' => $product->category,
            'variant' => $product->variant,
            'unit' => $product->unit,
            'buy_price' => $product->buy_price,
            'sell_price' => $product->sell_price,
            'hpp' => $product->hpp,
            'min_stock' => $product->min_stock,
            'stock' => $product->stock,
            'supplier' => $product->supplier?->name,
            'supplier_id' => $product->supplier_id,
            'is_active' => $product->is_active,
            'stock_movements' => $product->relationLoaded('stockMovements')
                ? $product->stockMovements->map(fn (StockMovement $m) => [
                    'id' => $m->id,
                    'type' => $m->type,
                    'quantity' => $m->quantity,
                    'note' => $m->note,
                    'created_at' => $m->created_at->toIso8601String(),
                ])
                : null,
        ];
    }
}
