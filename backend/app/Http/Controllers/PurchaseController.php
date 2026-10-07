<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $purchases = Purchase::with('supplier:id,name', 'items:id,purchase_id,product_id,quantity')
            ->where('business_id', $this->businessId($request->user()))
            ->withCount('items')
            ->latest()
            ->paginate(15);

        return response()->json([
            'data' => [
                'purchases' => $purchases->items(),
                'pagination' => [
                    'total' => $purchases->total(),
                    'per_page' => $purchases->perPage(),
                    'current_page' => $purchases->currentPage(),
                    'last_page' => $purchases->lastPage(),
                ],
            ],
        ]);
    }

    public function suppliers(Request $request): JsonResponse
    {
        $suppliers = Supplier::where('business_id', $this->businessId($request->user()))
            ->orderBy('name')
            ->get(['id', 'name', 'lead_time_days', 'contact_person']);

        return response()->json(['data' => ['suppliers' => $suppliers]]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'order_date' => ['sometimes', 'date'],
            'status' => ['sometimes', 'string', 'in:draft,ordered,received'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['sometimes', 'integer', 'min:0'],
            'note' => ['sometimes', 'nullable', 'string', 'max:500'],
        ]);

        $businessId = $this->businessId($request->user());

        $purchase = DB::transaction(function () use ($validated, $businessId): Purchase {
            $products = Product::whereKey(array_column($validated['items'], 'product_id'))->get()->keyBy('id');
            $total = 0;

            $purchase = Purchase::create([
                'business_id' => $businessId,
                'supplier_id' => $validated['supplier_id'],
                'po_number' => 'PO-'.str_pad((string) (Purchase::max('id') + 1), 4, '0', STR_PAD_LEFT),
                'status' => $validated['status'] ?? Purchase::STATUS_ORDERED,
                'order_date' => $validated['order_date'] ?? Carbon::today()->toDateString(),
                'received_at' => null,
                'total' => 0,
                'note' => $validated['note'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $product = $products->get($item['product_id']);
                $unitCost = $item['unit_cost'] ?? $product?->buy_price ?? 0;
                $lineTotal = $unitCost * $item['quantity'];

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $unitCost,
                    'line_total' => $lineTotal,
                ]);

                $total += $lineTotal;
            }

            $purchase->update(['total' => $total]);

            if ($purchase->status === Purchase::STATUS_RECEIVED) {
                $this->applyReceive($purchase);
            }

            return $purchase;
        });

        return response()->json([
            'data' => ['purchase' => $this->showPayload($purchase->fresh())],
        ], 201);
    }

    public function receive(Request $request, Purchase $purchase): JsonResponse
    {
        abort_unless($purchase->business_id === $this->businessId($request->user()), 404);

        if ($purchase->status === Purchase::STATUS_RECEIVED) {
            return response()->json(['data' => ['purchase' => $this->showPayload($purchase)]]);
        }

        DB::transaction(function () use ($purchase): void {
            $this->applyReceive($purchase);
        });

        return response()->json(['data' => ['purchase' => $this->showPayload($purchase->fresh())]]);
    }

    private function applyReceive(Purchase $purchase): void
    {
        foreach ($purchase->items as $item) {
            $item->product()->increment('stock', $item->quantity);

            StockMovement::create([
                'product_id' => $item->product_id,
                'type' => StockMovement::TYPE_IN,
                'quantity' => $item->quantity,
                'reference_type' => Purchase::class,
                'reference_id' => $purchase->id,
                'note' => 'Penerimaan PO '.$purchase->po_number,
            ]);
        }

        $purchase->update([
            'status' => Purchase::STATUS_RECEIVED,
            'received_at' => Carbon::now(),
        ]);
    }

    private function showPayload(Purchase $purchase): array
    {
        $purchase->load('supplier:id,name', 'items:id,purchase_id,product_id,quantity,unit_cost,line_total', 'items.product:id,name');

        return [
            'id' => $purchase->id,
            'po_number' => $purchase->po_number,
            'supplier' => $purchase->supplier?->name,
            'supplier_id' => $purchase->supplier_id,
            'status' => $purchase->status,
            'order_date' => $purchase->order_date->toDateString(),
            'received_at' => $purchase->received_at?->toIso8601String(),
            'total' => $purchase->total,
            'note' => $purchase->note,
            'items' => $purchase->items->map(fn (PurchaseItem $item) => [
                'product_id' => $item->product_id,
                'product' => $item->product?->name,
                'quantity' => $item->quantity,
                'unit_cost' => $item->unit_cost,
                'line_total' => $item->line_total,
            ]),
        ];
    }
}
