<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $businessId = $request->user()->businesses()->value('id');
        $startOfDay = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $now = Carbon::now();
        $monthAgo = Carbon::now()->subDays(30);

        $revenueToday = Sale::where('business_id', $businessId)->whereBetween('created_at', [$startOfDay, $now])->sum('total');
        $revenueMonth = Sale::where('business_id', $businessId)->whereBetween('created_at', [$startOfMonth, $now])->sum('total');
        $ordersMonth = Sale::where('business_id', $businessId)->whereBetween('created_at', [$startOfMonth, $now])->count();
        $revenueYesterday = Sale::where('business_id', $businessId)
            ->whereBetween('created_at', [Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay()])
            ->sum('total');

        $grossMonth = (int) SaleItem::whereHas('sale', function (Builder $q) use ($businessId, $startOfMonth): void {
            $q->where('business_id', $businessId)->where('created_at', '>=', $startOfMonth);
        })->get()->sum(fn (SaleItem $item) => $item->line_total - ($item->cost_price * $item->quantity));

        $expensesMonth = (int) Expense::where('business_id', $businessId)
            ->where('expense_date', '>=', $startOfMonth->toDateString())
            ->sum('amount');

        $customerCount = (int) Customer::where('business_id', $businessId)->count();

        $salesQty = SaleItem::query()
            ->whereHas('sale', fn (Builder $q) => $q->where('business_id', $businessId)->where('created_at', '>=', $monthAgo))
            ->selectRaw('product_id, SUM(quantity) as total')
            ->groupBy('product_id')
            ->pluck('total', 'product_id');

        $lowStock = Product::with('supplier:id,name')
            ->where('business_id', $businessId)
            ->whereColumn('stock', '<=', 'min_stock')
            ->orderByRaw('CAST(stock AS SIGNED) - CAST(min_stock AS SIGNED) ASC')
            ->limit(6)
            ->get();

        $lastSales = SaleItem::query()
            ->whereHas('sale', fn (Builder $q) => $q->where('business_id', $businessId))
            ->selectRaw('product_id, MAX(created_at) as last_sold')
            ->groupBy('product_id')
            ->pluck('last_sold', 'product_id');

        $deadStock = Product::with('supplier:id,name')
            ->where('business_id', $businessId)
            ->where('stock', '>', 0)
            ->limit(50)
            ->get()
            ->filter(function (Product $product) use ($lastSales, $monthAgo): bool {
                $lastSold = $lastSales->get($product->id);

                return $lastSold === null || Carbon::parse($lastSold)->lt($monthAgo);
            })
            ->sortBy(fn (Product $product) => $lastSales->get($product->id))
            ->take(4)
            ->values();

        $recentSales = Sale::with('customer:id,name')
            ->where('business_id', $businessId)
            ->latest()
            ->limit(6)
            ->get(['id', 'invoice_no', 'customer_id', 'total', 'payment_method', 'created_at'])
            ->map(fn (Sale $sale) => [
                'id' => $sale->id,
                'invoice_no' => $sale->invoice_no,
                'customer' => $sale->customer?->name ?? 'Walk-in',
                'total' => $sale->total,
                'payment_method' => $sale->payment_method,
                'created_at' => $sale->created_at->toIso8601String(),
            ]);

        $insights = collect();

        Product::with('supplier:id,name,lead_time_days')
            ->where('business_id', $businessId)
            ->get()
            ->each(function (Product $product) use ($salesQty, $insights): void {
                $sold = (int) ($salesQty->get($product->id) ?? 0);
                $avgDaily = $sold / 30;

                if ($avgDaily <= 0) {
                    return;
                }

                $leadTime = $product->supplier?->lead_time_days ?? 2;
                $coverage = $product->stock / $avgDaily;

                if ($coverage >= $leadTime) {
                    return;
                }

                $recommended = (int) max(1, ceil($avgDaily * ($leadTime + 3) - $product->stock));

                $insights->push([
                    'type' => 'auto_restock',
                    'severity' => 'warning',
                    'product_id' => $product->id,
                    'title' => 'Restock '.$product->name,
                    'message' => 'Stok tersisa '.$product->stock.' (cukup ~'.number_format($coverage, 1).' hari). Rekomendasi PO: '.$recommended.' pcs.',
                    'recommended_quantity' => $recommended,
                    'action' => 'create_purchase',
                ]);
            });

        foreach ($deadStock as $product) {
            $last = $lastSales->get($product->id);
            $days = $last ? Carbon::parse($last)->diffInDays() : 30;

            $insights->push([
                'type' => 'dead_stock',
                'severity' => 'info',
                'product_id' => $product->id,
                'title' => $product->name.' kurang laku',
                'message' => 'Terakhir terjual '.$days.' hari lalu. Modal tertahan Rp '.number_format($product->stock * $product->hpp).'. Coba diskon/bundle.',
                'action' => 'promote',
            ]);
        }

        return response()->json([
            'data' => [
                'kpi' => [
                    'revenue_today' => $revenueToday,
                    'revenue_yesterday' => $revenueYesterday,
                    'revenue_month' => $revenueMonth,
                    'orders_month' => $ordersMonth,
                    'gross_profit_month' => $grossMonth,
                    'expenses_month' => $expensesMonth,
                    'net_profit_month' => $grossMonth - $expensesMonth,
                    'customers' => $customerCount,
                ],
                'low_stock' => $lowStock->map(fn (Product $p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'category' => $p->category,
                    'stock' => $p->stock,
                    'min_stock' => $p->min_stock,
                    'supplier' => $p->supplier?->name,
                ]),
                'dead_stock' => $deadStock->map(fn (Product $p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'stock' => $p->stock,
                    'last_sold_days' => $lastSales->has($p->id) ? Carbon::parse($lastSales->get($p->id))->diffInDays() : null,
                ]),
                'recent_sales' => $recentSales,
                'insights' => $insights->take(8)->values(),
            ],
        ]);
    }
}
