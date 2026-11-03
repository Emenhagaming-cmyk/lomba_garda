<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Customer::where('business_id', $this->businessId($request->user()));

        if ($request->filled('search')) {
            $query->where(fn ($q) => $q
                ->where('name', 'like', '%'.$request->search.'%')
                ->orWhere('phone', 'like', '%'.$request->search.'%'));
        }

        $customers = $query->withCount('sales')->get();

        $spending = Sale::where('business_id', $this->businessId($request->user()))
            ->selectRaw('customer_id, SUM(total) as spent')
            ->whereNotNull('customer_id')
            ->groupBy('customer_id')
            ->pluck('spent', 'customer_id');

        return response()->json([
            'data' => [
                'customers' => $customers->map(fn (Customer $customer) => $this->serialize($customer, $spending->get($customer->id))),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'address' => ['sometimes', 'nullable', 'string', 'max:255'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:500'],
        ]);

        $customer = new Customer($validated);
        $customer->business_id = $this->businessId($request->user());
        $customer->save();

        return response()->json(['data' => ['customer' => $this->serialize($customer)]], 201);
    }

    public function show(Request $request, Customer $customer): JsonResponse
    {
        abort_unless($customer->business_id === $this->businessId($request->user()), 404);

        $sales = $customer->sales()
            ->with('items:id,sale_id,product_id,quantity,unit_price,line_total', 'items.product:id,name')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $lastSale = $customer->sales()->latest()->with('items.product:id,name,stock,min_stock')->first();
        $lastOrder = [];

        if ($lastSale) {
            $lastOrder = $lastSale->items->map(function ($item): array {
                return [
                    'product_id' => $item->product_id,
                    'name' => $item->product?->name,
                    'quantity' => $item->quantity,
                    'stock' => $item->product?->stock ?? 0,
                    'min_stock' => $item->product?->min_stock ?? 0,
                    'sell_price' => $item->unit_price,
                ];
            })->values();
        }

        $frequent = SaleItem::selectRaw('product_id, SUM(quantity) as total_qty, MAX(sale_items.created_at) as last_bought')
            ->whereHas('sale', function ($query) use ($customer): void {
                $query->where('customer_id', $customer->id);
            })
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->orderByDesc('last_bought')
            ->limit(5)
            ->get()
            ->map(function ($row): array {
                $product = Product::select('id', 'name', 'stock', 'min_stock', 'sell_price')->find($row->product_id);

                if ($product === null) {
                    return null;
                }

                return [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'stock' => $product->stock,
                    'min_stock' => $product->min_stock,
                    'sell_price' => $product->sell_price,
                    'total_qty' => (int) $row->total_qty,
                ];
            })
            ->filter(fn ($item) => $item !== null)
            ->values();

        return response()->json([
            'data' => [
                'customer' => $this->serialize($customer, $customer->sales()->sum('total')),
                'metrics' => [
                    'total_spending' => $customer->sales()->sum('total'),
                    'order_count' => $customer->sales()->count(),
                    'average_order' => (int) round($customer->sales()->count() ? $customer->sales()->sum('total') / $customer->sales()->count() : 0),
                    'last_purchase' => $customer->sales()->latest()->value('created_at'),
                    'segment' => $this->segment($customer),
                ],
                'repeat' => [
                    'last_order' => $lastOrder,
                    'frequent' => $frequent,
                ],
                'history' => $sales->map(fn (Sale $sale) => [
                    'id' => $sale->id,
                    'invoice_no' => $sale->invoice_no,
                    'total' => $sale->total,
                    'payment_method' => $sale->payment_method,
                    'created_at' => $sale->created_at->toIso8601String(),
                    'items' => $sale->items->map(fn ($item) => [
                        'product' => $item->product?->name,
                        'quantity' => $item->quantity,
                        'line_total' => $item->line_total,
                    ]),
                ]),
            ],
        ]);
    }

    private function serialize(Customer $customer, ?int $spending = null): array
    {
        return [
            'id' => $customer->id,
            'name' => $customer->name,
            'phone' => $customer->phone,
            'email' => $customer->email,
            'address' => $customer->address,
            'notes' => $customer->notes,
            'total_spending' => $spending,
            'order_count' => $customer->sales_count ?? $customer->sales()->count(),
            'segment' => $this->segment($customer),
        ];
    }

    private function segment(Customer $customer): string
    {
        return $customer->segment();
    }
}
