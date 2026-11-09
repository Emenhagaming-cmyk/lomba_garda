<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Thread;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $businessId = $this->businessId($request->user());
        $now = Carbon::now();
        $startOfDay = Carbon::today();
        $today = Carbon::today();
        $monthAgo = Carbon::now()->subDays(30);

        $notifications = collect();

        $revenueToday = (int) Sale::query()
            ->where('business_id', $businessId)
            ->whereBetween('created_at', [$startOfDay, $now])
            ->sum('total');

        $revenueYesterday = (int) Sale::query()
            ->where('business_id', $businessId)
            ->whereBetween('created_at', [Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay()])
            ->sum('total');

        if ($revenueToday > 0) {
            $notifications->push([
                'id' => 'omzet-'.$revenueToday.'-'.$revenueYesterday,
                'type' => 'omzet',
                'severity' => 'success',
                'title' => 'Omzet hari ini Rp '.$this->rupiah($revenueToday),
                'message' => $this->revenueMessage($revenueToday, $revenueYesterday),
                'action_url' => '/sales',
            ]);
        }

        $lowStock = Product::query()
            ->where('business_id', $businessId)
            ->whereColumn('stock', '<=', 'min_stock')
            ->orderByRaw('CAST(stock AS SIGNED) - CAST(min_stock AS SIGNED) ASC')
            ->limit(3)
            ->get(['id', 'name', 'stock', 'min_stock']);

        if ($lowStock->isNotEmpty()) {
            $notifications->push([
                'id' => 'low-stock-'.$lowStock->count().'-'.$lowStock->first()->id,
                'type' => 'low_stock',
                'severity' => 'warning',
                'title' => $lowStock->count().' produk stok menipis',
                'message' => $this->productListMessage($lowStock).' sudah di bawah stok minimum.',
                'action_url' => '/inventory',
            ]);
        }

        $salesQty = SaleItem::query()
            ->whereHas('sale', fn (Builder $q) => $q->where('business_id', $businessId)->where('created_at', '>=', $monthAgo))
            ->selectRaw('product_id, SUM(quantity) as total')
            ->groupBy('product_id')
            ->pluck('total', 'product_id');

        $restock = Product::with('supplier:id,name,lead_time_days')
            ->where('business_id', $businessId)
            ->get()
            ->filter(function (Product $product) use ($salesQty): bool {
                $sold = (int) ($salesQty->get($product->id) ?? 0);

                if ($sold <= 0) {
                    return false;
                }

                $leadTime = $product->supplier?->lead_time_days ?? 2;

                return $product->stock / ($sold / 30) < $leadTime;
            })
            ->take(3)
            ->values();

        if ($restock->isNotEmpty()) {
            $notifications->push([
                'id' => 'restock-'.$restock->count().'-'.$restock->first()->id,
                'type' => 'restock',
                'severity' => 'warning',
                'title' => 'Restock mendesak: '.$restock->count().' produk',
                'message' => $this->productListMessage($restock).' habis sebelum barang datang. Buat PO sekarang.',
                'action_url' => '/purchase',
            ]);
        }

        $lastSales = SaleItem::query()
            ->whereHas('sale', fn (Builder $q) => $q->where('business_id', $businessId))
            ->selectRaw('product_id, MAX(created_at) as last_sold')
            ->groupBy('product_id')
            ->pluck('last_sold', 'product_id');

        $deadStock = Product::query()
            ->where('business_id', $businessId)
            ->where('stock', '>', 0)
            ->limit(50)
            ->get(['id', 'name', 'stock', 'hpp'])
            ->filter(function (Product $product) use ($lastSales, $monthAgo): bool {
                $lastSold = $lastSales->get($product->id);

                return $lastSold === null || Carbon::parse($lastSold)->lt($monthAgo);
            });

        if ($deadStock->isNotEmpty()) {
            $blocked = (int) $deadStock->sum(fn (Product $product) => $product->stock * $product->hpp);

            $notifications->push([
                'id' => 'dead-stock-'.$deadStock->count().'-'.$blocked,
                'type' => 'dead_stock',
                'severity' => 'info',
                'title' => $deadStock->count().' produk kurang laku',
                'message' => 'Tidak terjual 30 hari terakhir. Modal tertahan Rp '.$this->rupiah($blocked).'.',
                'action_url' => '/inventory',
            ]);
        }

        $leads = Lead::query()
            ->where('business_id', $businessId)
            ->whereNotNull('next_follow_up_at')
            ->where('stage', '!=', 'converted')
            ->get(['id', 'name', 'next_follow_up_at']);

        $overdue = $leads->filter(fn (Lead $lead) => $lead->next_follow_up_at->lt($today))->count();
        $dueToday = $leads->filter(fn (Lead $lead) => $lead->next_follow_up_at->isSameDay($today))->count();

        if ($overdue + $dueToday > 0) {
            $notifications->push([
                'id' => 'follow-up-'.$overdue.'-'.$dueToday,
                'type' => 'follow_up',
                'severity' => $overdue > 0 ? 'warning' : 'info',
                'title' => ($overdue + $dueToday).' follow-up menunggu',
                'message' => trim(($overdue > 0 ? $overdue.' terlambat' : '').($overdue > 0 && $dueToday > 0 ? ' · ' : '').($dueToday > 0 ? $dueToday.' jatuh tempo hari ini' : '')).'.',
                'action_url' => '/follow-ups',
            ]);
        }

        $atRisk = Customer::query()
            ->where('business_id', $businessId)
            ->withMax('sales', 'created_at')
            ->get(['id'])
            ->filter(function (Customer $customer) use ($today): bool {
                $last = $customer->sales_max_created_at;

                return $last !== null && Carbon::parse($last)->diffInDays($today) > Customer::AT_RISK_DAYS;
            })
            ->count();

        if ($atRisk > 0) {
            $notifications->push([
                'id' => 'at-risk-'.$atRisk,
                'type' => 'at_risk',
                'severity' => 'info',
                'title' => $atRisk.' pelanggan berisiko hilang',
                'message' => 'Belum membeli lebih dari '.Customer::AT_RISK_DAYS.' hari. Kirim penawaran atau follow-up.',
                'action_url' => '/customers',
            ]);
        }

        $unreadChats = (int) Thread::query()
            ->where('user_id', $request->user()->id)
            ->sum('unread_count');

        if ($unreadChats > 0) {
            $notifications->push([
                'id' => 'chat-'.$unreadChats,
                'type' => 'chat',
                'severity' => 'info',
                'title' => $unreadChats.' pesan pemasok belum dibaca',
                'message' => 'Balasan pemesanan menunggu di inbox.',
                'action_url' => '/messages',
            ]);
        }

        $pendingPo = Purchase::query()
            ->where('business_id', $businessId)
            ->where('status', 'ordered')
            ->count();

        if ($pendingPo > 0) {
            $notifications->push([
                'id' => 'po-'.$pendingPo,
                'type' => 'purchase_order',
                'severity' => 'warning',
                'title' => $pendingPo.' PO belum diterima',
                'message' => 'Tandai barang masuk agar stok ikut bertambah.',
                'action_url' => '/purchase',
            ]);
        }

        $sorted = $notifications
            ->sortBy(fn (array $notification) => ['warning' => 0, 'info' => 1, 'success' => 2][$notification['severity']] ?? 3)
            ->values();

        return response()->json([
            'data' => [
                'notifications' => $sorted,
                'counts' => [
                    'total' => $sorted->count(),
                    'warning' => $sorted->where('severity', 'warning')->count(),
                ],
            ],
        ]);
    }

    private function revenueMessage(int $today, int $yesterday): string
    {
        if ($yesterday <= 0) {
            return 'Belum ada penjualan kemarin sebagai pembanding.';
        }

        $delta = (int) round((($today - $yesterday) / $yesterday) * 100);

        return $delta >= 0
            ? 'Naik '.$delta.'% dibanding kemarin (Rp '.$this->rupiah($yesterday).').'
            : 'Turun '.abs($delta).'% dibanding kemarin (Rp '.$this->rupiah($yesterday).').';
    }

    private function productListMessage(Collection $products): string
    {
        $names = $products->take(2)->pluck('name')->implode(', ');
        $extra = $products->count() - 2;

        return $extra > 0 ? $names.', +'.$extra.' lainnya' : $names;
    }

    private function rupiah(int $value): string
    {
        return number_format($value, 0, ',', '.');
    }
}
