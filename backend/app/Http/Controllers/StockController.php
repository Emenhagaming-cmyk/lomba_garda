<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::with('supplier:id,name')
            ->where('business_id', $this->businessId($request->user()));

        if ($request->filled('status') && in_array($request->status, ['ok', 'low', 'out'])) {
            $query->when($request->status === 'low', fn ($q) => $q->where(function ($q) {
                $q->whereColumn('stock', '<=', 'min_stock')->where('stock', '>', 0);
            }))
                ->when($request->status === 'out', fn ($q) => $q->where('stock', 0))
                ->when($request->status === 'ok', fn ($q) => $q->whereColumn('stock', '>', 'min_stock'));
        }

        $stock = $query->orderByRaw('stock <= min_stock DESC')->orderBy('name')->get();

        return response()->json([
            'data' => [
                'stock' => $stock->map(fn (Product $p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'category' => $p->category,
                    'stock' => $p->stock,
                    'min_stock' => $p->min_stock,
                    'status' => $p->stock === 0 ? 'out' : ($p->stock <= $p->min_stock ? 'low' : 'ok'),
                    'supplier' => $p->supplier?->name,
                    'last_updated' => $p->stockMovements()->latest()->value('created_at'),
                ]),
            ],
        ]);
    }

    public function movements(Request $request): JsonResponse
    {
        $query = StockMovement::with('product:id,name')
            ->whereHas('product', fn ($q) => $q->where('business_id', $this->businessId($request->user())))
            ->latest();

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        return response()->json([
            'data' => [
                'movements' => $query->limit(50)->get()->map(fn (StockMovement $m) => [
                    'id' => $m->id,
                    'product' => $m->product?->name,
                    'type' => $m->type,
                    'quantity' => $m->quantity,
                    'reference' => $this->referenceLabel($m),
                    'note' => $m->note,
                    'created_at' => $m->created_at->toIso8601String(),
                ]),
            ],
        ]);
    }

    public function adjust(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer'],
            'type' => ['sometimes', 'string', 'in:adjustment,production'],
            'note' => ['sometimes', 'nullable', 'string', 'max:255'],
        ]);

        $product = Product::findOrFail($validated['product_id']);
        abort_unless($product->business_id === $this->businessId($request->user()), 404);

        $type = $validated['type'] ?? StockMovement::TYPE_ADJUSTMENT;
        $isIn = $validated['quantity'] >= 0;

        if ($isIn) {
            $product->increment('stock', $validated['quantity']);
        } else {
            $newStock = $product->stock + $validated['quantity'];

            if ($newStock < 0) {
                return response()->json([
                    'error' => [
                        'code' => 'STOCK_NEGATIVE',
                        'message' => 'Stok tidak boleh negatif.',
                    ],
                ], 422);
            }

            $product->decrement('stock', abs($validated['quantity']));
        }

        StockMovement::create([
            'product_id' => $product->id,
            'type' => $type,
            'quantity' => abs($validated['quantity']),
            'note' => $validated['note'] ?? null,
        ]);

        return response()->json([
            'data' => [
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'stock' => $product->fresh()->stock,
                ],
            ],
        ], 201);
    }

    private function referenceLabel(StockMovement $movement): string
    {
        return match ($movement->reference_type) {
            'App\Models\Sale' => 'Penjualan',
            'App\Models\Purchase' => 'Pembelian',
            default => ucfirst($movement->type),
        };
    }
}
