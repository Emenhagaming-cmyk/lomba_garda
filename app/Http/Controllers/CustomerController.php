<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Sale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

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
        $orders = $customer->sales()->count();

        if ($orders === 0) {
            return 'new';
        }

        $spending = $customer->sales()->sum('total');
        $lastPurchase = $customer->sales()->latest()->value('created_at');
        $daysSince = $lastPurchase ? Carbon::parse($lastPurchase)->diffInDays() : null;

        if ($spending >= 1_500_000) {
            return 'vip';
        }

        if ($daysSince !== null && $daysSince > 90) {
            return 'inactive';
        }

        if ($daysSince !== null && $daysSince > 45) {
            return 'at_risk';
        }

        if ($orders >= 3) {
            return 'loyal';
        }

        return 'new';
    }
}
