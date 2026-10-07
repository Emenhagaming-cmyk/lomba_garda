<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Sale::with('customer:id,name')
            ->where('business_id', $this->businessId($request->user()));

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        $sales = $query->latest()->paginate(15);

        return response()->json([
            'data' => [
                'sales' => $sales->items(),
                'pagination' => [
                    'total' => $sales->total(),
                    'per_page' => $sales->perPage(),
                    'current_page' => $sales->currentPage(),
                    'last_page' => $sales->lastPage(),
                ],
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => ['sometimes', 'nullable', 'integer', 'exists:customers,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'payment_method' => ['sometimes', 'string', 'in:cash,qris,transfer,edc,other'],
            'discount' => ['sometimes', 'integer', 'min:0'],
            'note' => ['sometimes', 'nullable', 'string', 'max:500'],
        ]);

        $businessId = $this->businessId($request->user());

        if ($businessId === null) {
            return response()->json([
                'error' => [
                    'code' => 'BUSINESS_REQUIRED',
                    'message' => 'Lengkapi profil bisnis sebelum mencatat transaksi.',
                ],
            ], 409);
        }

        $discount = $validated['discount'] ?? 0;

        try {
            $sale = DB::transaction(function () use ($request, $validated, $businessId, $discount): Sale {
                $products = Product::whereKey(array_column($validated['items'], 'product_id'))
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                foreach ($validated['items'] as $item) {
                    $product = $products->get($item['product_id']);

                    if ($product === null || $product->stock < $item['quantity']) {
                        $name = $product?->name ?? 'produk';
                        $stock = $product?->stock ?? 0;

                        throw ValidationException::withMessages([
                            'items' => "Stok '{$name}' tidak mencukupi (sisa {$stock}).",
                        ]);
                    }
                }

                $sale = Sale::create([
                    'business_id' => $businessId,
                    'user_id' => $request->user()->id,
                    'customer_id' => $validated['customer_id'] ?? null,
                    'invoice_no' => 'INV-'.str_pad((string) (Sale::max('id') + 1), 6, '0', STR_PAD_LEFT),
                    'payment_method' => $validated['payment_method'] ?? 'cash',
                    'discount' => $discount,
                    'total' => 0,
                    'note' => $validated['note'] ?? null,
                ]);

                $total = 0;

                foreach ($validated['items'] as $item) {
                    $product = $products->get($item['product_id']);
                    $quantity = $item['quantity'];
                    $lineTotal = $product->sell_price * $quantity;

                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'unit_price' => $product->sell_price,
                        'cost_price' => $product->hpp,
                        'discount' => 0,
                        'line_total' => $lineTotal,
                    ]);

                    $product->decrement('stock', $quantity);

                    StockMovement::create([
                        'product_id' => $product->id,
                        'type' => StockMovement::TYPE_OUT,
                        'quantity' => $quantity,
                        'reference_type' => Sale::class,
                        'reference_id' => $sale->id,
                        'note' => $sale->invoice_no,
                    ]);

                    $total += $lineTotal;
                }

                $sale->update(['total' => max(0, $total - $discount)]);

                return $sale;
            });
        } catch (ValidationException $exception) {
            throw $exception;
        }

        return response()->json([
            'data' => ['sale' => $this->showPayload($sale)],
        ], 201);
    }

    public function show(Request $request, Sale $sale): JsonResponse
    {
        abort_unless($sale->business_id === $this->businessId($request->user()), 404);

        return response()->json(['data' => ['sale' => $this->showPayload($sale)]]);
    }

    private function showPayload(Sale $sale): array
    {
        $sale->load('customer:id,name', 'items:id,sale_id,product_id,quantity,unit_price,line_total', 'items.product:id,name');

        return [
            'id' => $sale->id,
            'invoice_no' => $sale->invoice_no,
            'customer' => $sale->customer?->name ?? 'Walk-in',
            'customer_id' => $sale->customer_id,
            'payment_method' => $sale->payment_method,
            'discount' => $sale->discount,
            'total' => $sale->total,
            'note' => $sale->note,
            'created_at' => $sale->created_at->toIso8601String(),
            'items' => $sale->items->map(fn (SaleItem $item) => [
                'product_id' => $item->product_id,
                'product' => $item->product?->name,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'line_total' => $item->line_total,
            ]),
        ];
    }
}
