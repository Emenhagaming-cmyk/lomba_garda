<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Expense;
use App\Models\Lead;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use stdClass;
use Throwable;

class ReportAiController extends Controller
{
    private const MAX_ITERATIONS = 4;

    private const MAX_TOOL_CALLS = 8;

    private const MAX_ROWS = 20;

    private const MAX_TOOL_OUTPUT_BYTES = 10240;

    private const MAX_HISTORY_MESSAGES = 20;

    private const MAX_HISTORY_CHARS = 8000;

    private const MAX_RANGE_DAYS = 366;

    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'messages' => ['required', 'array', 'min:1', 'max:40'],
            'messages.*.role' => ['required', 'string', Rule::in(['user', 'assistant'])],
            'messages.*.content' => ['required', 'string', 'min:1', 'max:4000'],
            'period' => ['sometimes', 'array'],
            'period.from' => ['required_with:period.to', 'date_format:Y-m-d'],
            'period.to' => ['required_with:period.from', 'date_format:Y-m-d', 'after_or_equal:period.from'],
        ]);

        $businessId = $this->businessId($request->user());

        if ($businessId === null) {
            return $this->error('BUSINESS_REQUIRED', 'Lengkapi profil bisnis sebelum memakai AI Analis.', 409);
        }

        if (blank(config('services.groq.api_key'))) {
            return $this->error('AI_NOT_CONFIGURED', 'AI Analis belum dikonfigurasi di server.', 503);
        }

        if ($this->rateLimited($request)) {
            return $this->error('RATE_LIMITED', 'Terlalu banyak permintaan AI. Coba lagi sebentar lagi.', 429);
        }

        $messages = array_merge(
            [['role' => 'system', 'content' => $this->systemPrompt($validated['period'] ?? null)]],
            $this->trimHistory($validated['messages'])
        );

        $toolsUsed = [];
        $toolCallCount = 0;
        $reply = null;

        for ($iteration = 0; $iteration < self::MAX_ITERATIONS; $iteration++) {
            if ($this->globalBudgetExceeded()) {
                return $this->error('RATE_LIMITED', 'Kuota AI sedang penuh. Coba lagi sebentar lagi.', 429);
            }

            try {
                $response = Http::timeout(25)
                    ->withToken((string) config('services.groq.api_key'))
                    ->acceptJson()
                    ->post(rtrim((string) config('services.groq.base_url'), '/').'/chat/completions', [
                        'model' => config('services.groq.model'),
                        'messages' => $messages,
                        'tools' => $this->tools(),
                        'tool_choice' => 'auto',
                        'temperature' => 0.3,
                        'max_tokens' => 1500,
                    ]);
            } catch (ConnectionException) {
                return $this->error('AI_UPSTREAM_ERROR', 'AI sedang tidak bisa dihubungi. Coba lagi sebentar lagi.', 502);
            } catch (Throwable $e) {
                Log::warning('Groq request error', ['error' => $e->getMessage()]);

                return $this->error('AI_UPSTREAM_ERROR', 'AI sedang tidak tersedia. Coba lagi sebentar lagi.', 502);
            }

            if ($response->failed()) {
                Log::warning('Groq request failed', ['status' => $response->status()]);

                return $this->error('AI_UPSTREAM_ERROR', 'AI sedang tidak tersedia. Coba lagi sebentar lagi.', 502);
            }

            $message = $response->json('choices.0.message');

            if (! is_array($message)) {
                return $this->error('AI_UPSTREAM_ERROR', 'Respons AI tidak valid.', 502);
            }

            $toolCalls = is_array($message['tool_calls'] ?? null) ? $message['tool_calls'] : [];

            $assistantMessage = ['role' => 'assistant', 'content' => (string) ($message['content'] ?? '')];

            if ($toolCalls !== []) {
                $assistantMessage['tool_calls'] = $toolCalls;
            }

            $messages[] = $assistantMessage;

            if ($toolCalls === []) {
                $reply = trim((string) ($message['content'] ?? ''));
                break;
            }

            $capReached = false;

            foreach ($toolCalls as $call) {
                if ($toolCallCount >= self::MAX_TOOL_CALLS) {
                    $capReached = true;
                    break;
                }

                $toolCallCount++;
                $name = (string) ($call['function']['name'] ?? '');
                $arguments = json_decode((string) ($call['function']['arguments'] ?? '{}'), true);
                $arguments = is_array($arguments) ? $arguments : [];

                $toolsUsed[] = $name;

                $messages[] = [
                    'role' => 'tool',
                    'tool_call_id' => (string) ($call['id'] ?? ''),
                    'content' => $this->encodeToolResult($this->runTool($name, $arguments, $businessId)),
                ];
            }

            if ($capReached) {
                break;
            }
        }

        if ($reply === null || $reply === '') {
            return $this->error('AI_TOOL_LIMIT', 'AI terlalu banyak meminta data. Coba pertanyaan yang lebih spesifik.', 502);
        }

        return response()->json([
            'data' => [
                'reply' => $reply,
                'tools_used' => array_values(array_unique($toolsUsed)),
            ],
        ]);
    }

    private function error(string $code, string $message, int $status): JsonResponse
    {
        return response()->json([
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ], $status);
    }

    private function rateLimited(Request $request): bool
    {
        $userId = $request->user()->id;
        $perMinute = max(1, (int) config('services.groq.rate_per_min'));
        $perDay = max(1, (int) config('services.groq.rate_per_day'));

        $minuteKey = 'ai-chat:user:minute:'.$userId;
        $dayKey = 'ai-chat:user:day:'.$userId;

        if (RateLimiter::tooManyAttempts($minuteKey, $perMinute) || RateLimiter::tooManyAttempts($dayKey, $perDay)) {
            return true;
        }

        RateLimiter::hit($minuteKey, 60);
        RateLimiter::hit($dayKey, 86400);

        return false;
    }

    private function globalBudgetExceeded(): bool
    {
        $limit = (int) config('services.groq.rate_global_per_min');

        if ($limit <= 0) {
            return false;
        }

        $key = 'ai-chat:global:minute';

        if (RateLimiter::tooManyAttempts($key, $limit)) {
            return true;
        }

        RateLimiter::hit($key, 60);

        return false;
    }

    private function systemPrompt(?array $period): string
    {
        $lines = [
            'Anda "AI Analis TokoKu", analis bisnis untuk pemilik UMKM di Indonesia.',
            'Gunakan Bahasa Indonesia yang ringkas dan praktis. Tanggal hari ini: '.Carbon::now()->toDateString().'.',
            '',
            'ATURAN DATA',
            '- SEMUA angka wajib berasal dari hasil tool. Dilarang mengarang atau mengira-ngira angka.',
            '- Jangan menghitung ulang definisi keuangan; pakai angka yang dikembalikan tool.',
            '- Jangan pernah menyebut atau meminta business_id. Data sudah otomatis milik bisnis pengguna.',
            '- Jika data kosong atau tidak cukup, nyatakan keterbatasan data dan sarankan pencatatan yang perlu dilengkapi.',
            '',
            'DEFINISI KEUANGAN TOKOKU (jangan diubah)',
            '- Omzet/revenue = SUM(total penjualan) setelah diskon penjualan.',
            '- HPP/COGS = modal produk yang sudah terjual (cost_price x quantity).',
            '- Laba kotor = omzet - HPP.',
            '- Beban = tabel pengeluaran (expenses).',
            '- Laba bersih = laba kotor - beban. Ini laba akrual, BUKAN arus kas.',
            '- Pembelian stok (purchases) = kas keluar untuk stok, BUKAN beban; baru menjadi HPP saat produk terjual. Arus kas bisa berbeda dari laba bersih; jelaskan perbedaan ini bila ditanya soal arus kas atau sisa uang.',
            '- TokoKu belum punya fitur refund, pembatalan, pajak, atau ongkir. Nyatakan sebagai keterbatasan data bila ditanya.',
            '',
            'GAYA JAWABAN',
            '- Ringkas dan berpoin. Uang dalam format Rupiah, contoh: Rp 1.234.567.',
            '- Sebutkan periode data yang dipakai.',
            '- Akhiri dengan bagian "Saran ke depan:" berisi 2-3 aksi konkret berbasis data.',
            '- Jika data tidak cukup untuk menyimpulkan, ganti bagian itu dengan "Data yang perlu dilengkapi:".',
        ];

        if (isset($period['from'], $period['to'])) {
            $lines[] = '';
            $lines[] = 'Periode laporan yang sedang dibuka pengguna: '.$period['from'].' sampai '.$period['to'].'. Pakai periode ini bila pertanyaan tidak menyebut tanggal.';
        }

        return implode("\n", $lines);
    }

    private function trimHistory(array $messages): array
    {
        $turns = [];
        $current = [];

        foreach ($messages as $message) {
            if ($message['role'] === 'user') {
                if ($current !== []) {
                    $turns[] = $current;
                }

                $current = [$message];

                continue;
            }

            $current[] = $message;
        }

        if ($current !== []) {
            $turns[] = $current;
        }

        $turns = array_values(array_filter($turns, fn (array $turn) => $turn[0]['role'] === 'user'));

        $kept = [];
        $messageCount = 0;
        $charCount = 0;

        for ($i = count($turns) - 1; $i >= 0; $i--) {
            $turn = $turns[$i];
            $turnMessages = count($turn);
            $turnChars = array_sum(array_map(fn (array $item) => mb_strlen($item['content']), $turn));

            if ($messageCount + $turnMessages > self::MAX_HISTORY_MESSAGES || $charCount + $turnChars > self::MAX_HISTORY_CHARS) {
                break;
            }

            $messageCount += $turnMessages;
            $charCount += $turnChars;
            array_unshift($kept, $turn);
        }

        if ($kept === [] && $turns !== []) {
            $kept = [end($turns)];
        }

        return array_merge(...$kept);
    }

    private function tools(): array
    {
        $rangeFrom = ['type' => 'string', 'description' => 'Tanggal awal format YYYY-MM-DD (opsional, default awal bulan berjalan)'];
        $rangeTo = ['type' => 'string', 'description' => 'Tanggal akhir format YYYY-MM-DD (opsional, default akhir bulan berjalan)'];

        return [
            $this->toolDefinition(
                'get_report_summary',
                'Ringkasan keuangan periode: omzet, HPP, beban, laba kotor, laba bersih, jumlah transaksi, produk terlaris, dan tren penjualan. Angka sudah dihitung server.',
                ['from' => $rangeFrom, 'to' => $rangeTo]
            ),
            $this->toolDefinition(
                'get_dashboard_kpi',
                'KPI ringkas: omzet hari ini/kemarin/bulan ini, transaksi bulan ini, laba kotor/beban/laba bersih bulan ini, jumlah pelanggan, stok menipis, dan pelanggan berisiko.',
                []
            ),
            $this->toolDefinition(
                'get_sales',
                'Daftar transaksi penjualan terbaru pada rentang tanggal.',
                ['from' => $rangeFrom, 'to' => $rangeTo]
            ),
            $this->toolDefinition(
                'get_expenses',
                'Rincian pengeluaran/beban pada rentang tanggal: total, per kategori, dan daftar transaksi.',
                ['from' => $rangeFrom, 'to' => $rangeTo]
            ),
            $this->toolDefinition(
                'get_products',
                'Daftar produk beserta stok, harga jual, HPP, dan status stok. Set low_stock=true untuk hanya produk yang perlu restock.',
                ['low_stock' => ['type' => 'boolean', 'description' => 'Hanya tampilkan produk dengan stok <= stok minimum']]
            ),
            $this->toolDefinition(
                'get_customers',
                'Pelanggan teratas berdasarkan total belanja, lengkap dengan segmen (vip/loyal/at_risk/inactive/new).',
                []
            ),
            $this->toolDefinition(
                'get_leads',
                'Daftar leads/calon pelanggan beserta stage dan jadwal follow-up.',
                ['stage' => ['type' => 'string', 'enum' => Lead::STAGES, 'description' => 'Filter stage (opsional)']]
            ),
            $this->toolDefinition(
                'get_follow_ups',
                'Follow-up leads yang telat/hari ini/mendatang dan pelanggan yang berisiko berhenti belanja.',
                []
            ),
            $this->toolDefinition(
                'get_purchases',
                'Daftar purchase order (PO) beserta status dan totalnya.',
                ['status' => ['type' => 'string', 'enum' => ['draft', 'ordered', 'partially_received', 'received'], 'description' => 'Filter status PO (opsional)']]
            ),
        ];
    }

    private function toolDefinition(string $name, string $description, array $properties): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => $name,
                'description' => $description,
                'parameters' => [
                    'type' => 'object',
                    'properties' => $properties === [] ? new stdClass : $properties,
                    'additionalProperties' => false,
                ],
            ],
        ];
    }

    private function runTool(string $name, array $arguments, int $businessId): array
    {
        try {
            return match ($name) {
                'get_report_summary' => $this->toolReportSummary($arguments, $businessId),
                'get_dashboard_kpi' => $this->toolDashboardKpi($arguments, $businessId),
                'get_sales' => $this->toolSales($arguments, $businessId),
                'get_expenses' => $this->toolExpenses($arguments, $businessId),
                'get_products' => $this->toolProducts($arguments, $businessId),
                'get_customers' => $this->toolCustomers($arguments, $businessId),
                'get_leads' => $this->toolLeads($arguments, $businessId),
                'get_follow_ups' => $this->toolFollowUps($arguments, $businessId),
                'get_purchases' => $this->toolPurchases($arguments, $businessId),
                default => ['error' => 'Tool tidak dikenal: '.$name],
            };
        } catch (Throwable $e) {
            Log::warning('AI tool execution failed', ['tool' => $name, 'error' => $e->getMessage()]);

            return ['error' => 'Gagal mengambil data untuk '.$name.'. Coba lagi.'];
        }
    }

    private function validateArgs(array $arguments, array $rules, array $allowed): ?array
    {
        if (array_diff(array_keys($arguments), $allowed) !== []) {
            return null;
        }

        $validator = Validator::make($arguments, $rules);

        if ($validator->fails()) {
            return null;
        }

        return $validator->validated();
    }

    private function invalidArgs(): array
    {
        return ['error' => 'Argumen tool tidak valid. Periksa format tanggal dan nilai yang diizinkan.'];
    }

    private function resolveRange(array $args): ?array
    {
        $from = isset($args['from'])
            ? Carbon::createFromFormat('Y-m-d', $args['from'])->startOfDay()
            : Carbon::now()->startOfMonth();
        $to = isset($args['to'])
            ? Carbon::createFromFormat('Y-m-d', $args['to'])->endOfDay()
            : Carbon::now()->endOfMonth();

        if ($from->gt($to) || $from->diffInDays($to) > self::MAX_RANGE_DAYS) {
            return null;
        }

        return [$from, $to];
    }

    private function encodeToolResult(array $result): string
    {
        $json = json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if (strlen($json) <= self::MAX_TOOL_OUTPUT_BYTES) {
            return $json;
        }

        if (isset($result['rows']) && is_array($result['rows'])) {
            $rows = $result['rows'];

            while ($rows !== []) {
                array_pop($rows);
                $result['rows'] = array_values($rows);
                $result['truncated'] = true;
                $json = json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                if (strlen($json) <= self::MAX_TOOL_OUTPUT_BYTES) {
                    return $json;
                }
            }
        }

        return json_encode([
            'truncated' => true,
            'note' => 'Output tool terlalu besar dan dipotong.',
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function toolReportSummary(array $args, int $businessId): array
    {
        $args = $this->validateArgs($args, [
            'from' => ['sometimes', 'date_format:Y-m-d'],
            'to' => ['sometimes', 'date_format:Y-m-d'],
        ], ['from', 'to']);

        if ($args === null) {
            return $this->invalidArgs();
        }

        $range = $this->resolveRange($args);

        if ($range === null) {
            return $this->invalidArgs();
        }

        [$from, $to] = $range;

        $payload = Cache::remember(
            'ai:report-summary:'.$businessId.':'.$from->toDateString().':'.$to->toDateString(),
            60,
            function () use ($businessId, $from, $to): array {
                return $this->buildReportSummary($businessId, $from, $to);
            }
        );

        return [
            'period' => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
        ] + $payload;
    }

    private function buildReportSummary(int $businessId, Carbon $from, Carbon $to): array
    {
        $revenue = (int) Sale::where('business_id', $businessId)->whereBetween('created_at', [$from, $to])->sum('total');
        $orderCount = (int) Sale::where('business_id', $businessId)->whereBetween('created_at', [$from, $to])->count();
        $cogs = (int) SaleItem::whereHas('sale', fn ($query) => $query->where('business_id', $businessId)->whereBetween('created_at', [$from, $to]))
            ->selectRaw('SUM(cost_price * quantity) as cogs')
            ->value('cogs');
        $expenses = (int) Expense::where('business_id', $businessId)
            ->whereBetween('expense_date', [$from->toDateString(), $to->toDateString()])
            ->sum('amount');
        $gross = max(0, $revenue - $cogs);
        $net = $gross - $expenses;

        $topProducts = SaleItem::selectRaw('products.name, SUM(sale_items.quantity) as qty, SUM(sale_items.line_total) as total')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.business_id', $businessId)
            ->whereBetween('sales.created_at', [$from, $to])
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn ($row) => ['name' => $row->name, 'qty' => (int) $row->qty, 'total' => (int) $row->total])
            ->all();

        $daily = Sale::selectRaw('DATE(created_at) as date, SUM(total) as revenue, COUNT(*) as orders')
            ->where('business_id', $businessId)
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $days = (int) $from->copy()->startOfDay()->diffInDays($to->copy()->startOfDay()) + 1;
        $bucketDays = $days <= self::MAX_ROWS ? 1 : (int) max(7, (int) ceil($days / self::MAX_ROWS));

        if ($bucketDays === 1) {
            $salesTrend = $daily
                ->map(fn ($row) => ['date' => (string) $row->date, 'revenue' => (int) $row->revenue, 'orders' => (int) $row->orders])
                ->all();
        } else {
            $start = $from->copy()->startOfDay();
            $buckets = [];

            foreach ($daily as $row) {
                $index = (int) floor($start->diffInDays(Carbon::parse($row->date)->startOfDay()) / $bucketDays);
                $label = $start->copy()->addDays($index * $bucketDays)->toDateString();

                $buckets[$label] ??= ['date' => $label, 'revenue' => 0, 'orders' => 0];
                $buckets[$label]['revenue'] += (int) $row->revenue;
                $buckets[$label]['orders'] += (int) $row->orders;
            }

            ksort($buckets);
            $salesTrend = array_slice(array_values($buckets), 0, self::MAX_ROWS);
        }

        return [
            'summary' => compact('revenue', 'cogs', 'expenses', 'gross', 'net', 'orderCount'),
            'top_products' => $topProducts,
            'sales_trend' => $salesTrend,
            'trend_bucket_days' => $bucketDays,
            'trend_note' => $bucketDays === 1 ? 'Per hari.' : 'Diringkas per '.$bucketDays.' hari agar ringkas.',
        ];
    }

    private function toolDashboardKpi(array $args, int $businessId): array
    {
        $args = $this->validateArgs($args, [], []);

        if ($args === null) {
            return $this->invalidArgs();
        }

        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $now = Carbon::now();

        $revenueToday = (int) Sale::where('business_id', $businessId)->whereBetween('created_at', [$today, $now])->sum('total');
        $revenueYesterday = (int) Sale::where('business_id', $businessId)
            ->whereBetween('created_at', [Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay()])
            ->sum('total');
        $revenueMonth = (int) Sale::where('business_id', $businessId)->whereBetween('created_at', [$startOfMonth, $now])->sum('total');
        $ordersMonth = (int) Sale::where('business_id', $businessId)->whereBetween('created_at', [$startOfMonth, $now])->count();

        $grossMonth = (int) SaleItem::whereHas('sale', fn ($query) => $query->where('business_id', $businessId)->where('created_at', '>=', $startOfMonth))
            ->get()
            ->sum(fn (SaleItem $item) => $item->line_total - ($item->cost_price * $item->quantity));

        $expensesMonth = (int) Expense::where('business_id', $businessId)
            ->where('expense_date', '>=', $startOfMonth->toDateString())
            ->sum('amount');

        $customerCount = (int) Customer::where('business_id', $businessId)->count();
        $lowStockCount = (int) Product::where('business_id', $businessId)->whereColumn('stock', '<=', 'min_stock')->count();

        $lowStock = Product::where('business_id', $businessId)
            ->whereColumn('stock', '<=', 'min_stock')
            ->orderByRaw('CAST(stock AS SIGNED) - CAST(min_stock AS SIGNED) ASC')
            ->limit(6)
            ->get()
            ->map(fn (Product $product) => [
                'name' => $product->name,
                'stock' => (int) $product->stock,
                'min_stock' => (int) $product->min_stock,
                'unit' => $product->unit,
            ])
            ->all();

        $atRisk = Customer::where('business_id', $businessId)
            ->withCount('sales')
            ->withSum('sales', 'total')
            ->withMax('sales', 'created_at')
            ->get()
            ->map(function (Customer $customer): ?array {
                $last = $customer->sales_max_created_at;

                if ($last === null) {
                    return null;
                }

                $days = (int) Carbon::parse($last)->diffInDays();

                if ($days <= Customer::AT_RISK_DAYS) {
                    return null;
                }

                $spending = (int) ($customer->sales_sum_total ?? 0);

                return [
                    'name' => $customer->name,
                    'days_since' => $days,
                    'total_spending' => $spending,
                    'segment' => Customer::segmentFor($spending, (int) ($customer->sales_count ?? 0), $last),
                ];
            })
            ->filter()
            ->sortByDesc('days_since')
            ->take(5)
            ->values()
            ->all();

        return [
            'kpi' => [
                'revenue_today' => $revenueToday,
                'revenue_yesterday' => $revenueYesterday,
                'revenue_month' => $revenueMonth,
                'orders_month' => $ordersMonth,
                'gross_profit_month' => $grossMonth,
                'expenses_month' => $expensesMonth,
                'net_profit_month' => $grossMonth - $expensesMonth,
                'customers' => $customerCount,
                'low_stock_count' => $lowStockCount,
            ],
            'low_stock' => $lowStock,
            'at_risk_customers' => $atRisk,
        ];
    }

    private function toolSales(array $args, int $businessId): array
    {
        $args = $this->validateArgs($args, [
            'from' => ['sometimes', 'date_format:Y-m-d'],
            'to' => ['sometimes', 'date_format:Y-m-d'],
        ], ['from', 'to']);

        if ($args === null) {
            return $this->invalidArgs();
        }

        $range = $this->resolveRange($args);

        if ($range === null) {
            return $this->invalidArgs();
        }

        [$from, $to] = $range;

        $rows = Sale::with('customer:id,name')
            ->where('business_id', $businessId)
            ->whereBetween('created_at', [$from, $to])
            ->latest()
            ->limit(self::MAX_ROWS)
            ->get(['id', 'invoice_no', 'customer_id', 'total', 'payment_method', 'created_at'])
            ->map(fn (Sale $sale) => [
                'invoice_no' => $sale->invoice_no,
                'date' => $sale->created_at->toIso8601String(),
                'customer' => $sale->customer?->name ?? 'Walk-in',
                'payment_method' => $sale->payment_method,
                'total' => (int) $sale->total,
            ])
            ->all();

        return [
            'period' => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
            'rows' => $rows,
        ];
    }

    private function toolExpenses(array $args, int $businessId): array
    {
        $args = $this->validateArgs($args, [
            'from' => ['sometimes', 'date_format:Y-m-d'],
            'to' => ['sometimes', 'date_format:Y-m-d'],
        ], ['from', 'to']);

        if ($args === null) {
            return $this->invalidArgs();
        }

        $range = $this->resolveRange($args);

        if ($range === null) {
            return $this->invalidArgs();
        }

        [$from, $to] = $range;

        $base = Expense::where('business_id', $businessId)
            ->whereBetween('expense_date', [$from->toDateString(), $to->toDateString()]);

        $total = (int) (clone $base)->sum('amount');

        $byCategory = (clone $base)
            ->selectRaw("COALESCE(category, 'Lainnya') as category, SUM(amount) as total")
            ->groupBy('category')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => ['category' => $row->category, 'total' => (int) $row->total])
            ->all();

        $rows = (clone $base)
            ->orderByDesc('expense_date')
            ->limit(self::MAX_ROWS)
            ->get(['expense_date', 'category', 'amount', 'note'])
            ->map(fn (Expense $expense) => [
                'date' => $expense->expense_date->toDateString(),
                'category' => $expense->category ?? 'Lainnya',
                'amount' => (int) $expense->amount,
                'note' => $expense->note,
            ])
            ->all();

        return [
            'period' => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
            'total' => $total,
            'by_category' => $byCategory,
            'rows' => $rows,
        ];
    }

    private function toolProducts(array $args, int $businessId): array
    {
        $args = $this->validateArgs($args, [
            'low_stock' => ['sometimes', 'boolean'],
        ], ['low_stock']);

        if ($args === null) {
            return $this->invalidArgs();
        }

        $query = Product::where('business_id', $businessId);

        if (! empty($args['low_stock'])) {
            $query->whereColumn('stock', '<=', 'min_stock');
        }

        $rows = $query
            ->orderBy('stock')
            ->limit(self::MAX_ROWS)
            ->get()
            ->map(function (Product $product): array {
                $status = $product->stock <= 0 ? 'out' : ($product->stock <= $product->min_stock ? 'low' : 'ok');

                return [
                    'name' => $product->name,
                    'category' => $product->category,
                    'stock' => (int) $product->stock,
                    'min_stock' => (int) $product->min_stock,
                    'unit' => $product->unit,
                    'sell_price' => (int) $product->sell_price,
                    'hpp' => (int) $product->hpp,
                    'status' => $status,
                ];
            })
            ->all();

        return ['rows' => $rows];
    }

    private function toolCustomers(array $args, int $businessId): array
    {
        $args = $this->validateArgs($args, [], []);

        if ($args === null) {
            return $this->invalidArgs();
        }

        $rows = Customer::where('business_id', $businessId)
            ->withCount('sales')
            ->withSum('sales', 'total')
            ->withMax('sales', 'created_at')
            ->get()
            ->map(function (Customer $customer): array {
                $last = $customer->sales_max_created_at;
                $spending = (int) ($customer->sales_sum_total ?? 0);
                $orders = (int) ($customer->sales_count ?? 0);

                return [
                    'name' => $customer->name,
                    'segment' => Customer::segmentFor($spending, $orders, $last),
                    'order_count' => $orders,
                    'total_spending' => $spending,
                    'days_since_last_purchase' => $last ? (int) Carbon::parse($last)->diffInDays() : null,
                ];
            })
            ->sortByDesc('total_spending')
            ->take(self::MAX_ROWS)
            ->values()
            ->all();

        return ['rows' => $rows];
    }

    private function toolLeads(array $args, int $businessId): array
    {
        $args = $this->validateArgs($args, [
            'stage' => ['sometimes', 'string', Rule::in(Lead::STAGES)],
        ], ['stage']);

        if ($args === null) {
            return $this->invalidArgs();
        }

        $query = Lead::where('business_id', $businessId);

        if (isset($args['stage'])) {
            $query->where('stage', $args['stage']);
        }

        $rows = $query
            ->latest()
            ->limit(self::MAX_ROWS)
            ->get()
            ->map(fn (Lead $lead) => [
                'name' => $lead->name,
                'stage' => $lead->stage,
                'source' => $lead->source,
                'next_follow_up_at' => $lead->next_follow_up_at?->toIso8601String(),
                'created_at' => $lead->created_at->toIso8601String(),
            ])
            ->all();

        return ['rows' => $rows];
    }

    private function toolFollowUps(array $args, int $businessId): array
    {
        $args = $this->validateArgs($args, [], []);

        if ($args === null) {
            return $this->invalidArgs();
        }

        $today = Carbon::today();
        $weekAhead = Carbon::today()->endOfDay()->addDays(7);

        $leads = Lead::where('business_id', $businessId)
            ->whereNotNull('next_follow_up_at')
            ->where('stage', '!=', 'converted')
            ->orderBy('next_follow_up_at')
            ->get();

        $overdue = $leads->filter(fn (Lead $lead) => $lead->next_follow_up_at->lt($today))->values();
        $dueToday = $leads->filter(fn (Lead $lead) => $lead->next_follow_up_at->isSameDay($today))->values();
        $upcoming = $leads
            ->filter(fn (Lead $lead) => $lead->next_follow_up_at->gt($today->copy()->endOfDay()) && $lead->next_follow_up_at->lte($weekAhead))
            ->values();

        $atRisk = Customer::where('business_id', $businessId)
            ->withCount('sales')
            ->withSum('sales', 'total')
            ->withMax('sales', 'created_at')
            ->get()
            ->map(function (Customer $customer): ?array {
                $last = $customer->sales_max_created_at;

                if ($last === null) {
                    return null;
                }

                $days = (int) Carbon::parse($last)->diffInDays();

                if ($days <= Customer::AT_RISK_DAYS) {
                    return null;
                }

                return [
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'days_since' => $days,
                    'total_spending' => (int) ($customer->sales_sum_total ?? 0),
                ];
            })
            ->filter()
            ->sortByDesc('days_since')
            ->take(5)
            ->values()
            ->all();

        $rows = [];

        foreach ([['type' => 'overdue', 'items' => $overdue], ['type' => 'today', 'items' => $dueToday], ['type' => 'upcoming', 'items' => $upcoming]] as $group) {
            foreach ($group['items'] as $lead) {
                if (count($rows) >= 15) {
                    break 2;
                }

                $rows[] = [
                    'type' => $group['type'],
                    'name' => $lead->name,
                    'phone' => $lead->phone,
                    'next_follow_up_at' => $lead->next_follow_up_at->toIso8601String(),
                ];
            }
        }

        return [
            'counts' => [
                'overdue' => $overdue->count(),
                'today' => $dueToday->count(),
                'upcoming' => $upcoming->count(),
                'at_risk_customers' => count($atRisk),
            ],
            'rows' => $rows,
            'at_risk_customers' => $atRisk,
        ];
    }

    private function toolPurchases(array $args, int $businessId): array
    {
        $args = $this->validateArgs($args, [
            'status' => ['sometimes', 'string', Rule::in(['draft', 'ordered', 'partially_received', 'received'])],
        ], ['status']);

        if ($args === null) {
            return $this->invalidArgs();
        }

        $query = Purchase::with('supplier:id,name')->where('business_id', $businessId);

        if (isset($args['status'])) {
            $query->where('status', $args['status']);
        }

        $rows = $query
            ->latest('order_date')
            ->limit(self::MAX_ROWS)
            ->get()
            ->map(fn (Purchase $purchase) => [
                'po_number' => $purchase->po_number,
                'supplier' => $purchase->supplier?->name,
                'status' => $purchase->status,
                'order_date' => $purchase->order_date?->toDateString(),
                'received_at' => $purchase->received_at?->toIso8601String(),
                'total' => (int) $purchase->total,
            ])
            ->all();

        return ['rows' => $rows];
    }
}
