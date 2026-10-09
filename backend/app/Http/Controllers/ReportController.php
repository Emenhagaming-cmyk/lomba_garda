<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    public function summary(Request $request): JsonResponse
    {
        $businessId = $this->businessId($request->user());
        $from = $request->filled('from') ? Carbon::parse($request->from)->startOfDay() : Carbon::now()->startOfMonth();
        $to = $request->filled('to') ? Carbon::parse($request->to)->endOfDay() : Carbon::now()->endOfMonth();

        $salesQuery = Sale::where('business_id', $businessId)->whereBetween('created_at', [$from, $to]);
        $revenue = (int) $salesQuery->sum('total');
        $orderCount = $salesQuery->count();
        $cogs = (int) SaleItem::whereHas('sale', fn ($q) => $q->where('business_id', $businessId)->whereBetween('created_at', [$from, $to]))
            ->selectRaw('SUM(cost_price * quantity) as cogs')
            ->value('cogs');
        $expenses = (int) Expense::where('business_id', $businessId)->whereBetween('created_at', [$from, $to])->sum('amount');
        $gross = max(0, $revenue - $cogs);
        $net = $gross - $expenses;

        return response()->json([
            'data' => [
                'period' => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
                'summary' => compact('revenue', 'cogs', 'expenses', 'gross', 'net', 'orderCount'),
                'topProducts' => SaleItem::selectRaw('products.name, SUM(sale_items.quantity) as qty, SUM(sale_items.line_total) as total')
                    ->join('products', 'products.id', '=', 'sale_items.product_id')
                    ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
                    ->where('sales.business_id', $businessId)
                    ->whereBetween('sales.created_at', [$from, $to])
                    ->groupBy('products.id', 'products.name')
                    ->orderByDesc('total')
                    ->limit(10)
                    ->get(),
                'salesByDay' => Sale::selectRaw('DATE(created_at) as date, SUM(total) as revenue, COUNT(*) as orders')
                    ->where('business_id', $businessId)
                    ->whereBetween('created_at', [$from, $to])
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get(),
            ],
        ]);
    }
}
