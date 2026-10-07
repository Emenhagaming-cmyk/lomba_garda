<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Lead;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BusinessDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (Sale::exists()) {
            return;
        }

        mt_srand(20260921);

        $owner = User::firstOrCreate(
            ['email' => 'demo@tokoku.app'],
            ['name' => 'Demo Owner', 'password' => 'Rahasia123!', 'role' => 'OWNER'],
        );

        $business = Business::firstOrCreate(
            ['user_id' => $owner->id],
            [
                'name' => 'Kopi Tertial',
                'type' => 'fnb',
                'currency' => 'IDR',
                'payment_methods' => ['cash', 'qris', 'transfer'],
                'phone' => '081234567890',
                'email' => 'halo@kopitertial.id',
                'address' => 'Jl. Sudirman No. 12, Yogyakarta',
            ],
        );

        $suppliers = [];

        foreach ([
            ['name' => 'Arsa Coffee Roasters', 'lead_time_days' => 3],
            ['name' => 'Susu dan Creamery', 'lead_time_days' => 2],
            ['name' => 'Bakery Harapan', 'lead_time_days' => 1],
        ] as $s) {
            $suppliers[] = Supplier::firstOrCreate(
                ['business_id' => $business->id, 'name' => $s['name']],
                $s + ['phone' => '08'.mt_rand(1000000000, 9999999999), 'notes' => 'Rekanan utama'],
            );
        }

        $defs = [
            ['Kopi Arabica Gayo', 'Kopi', 12000, 25000, 14000, 30, null],
            ['Kopi Robusta Lampung', 'Kopi', 10000, 22000, 12000, 30, null],
            ['Espresso', 'Minuman', 15000, 28000, 18000, 20, $suppliers[0]->id],
            ['Cappuccino', 'Minuman', 14000, 30000, 17000, 25, $suppliers[1]->id],
            ['Espresso Coklat', 'Minuman', 16000, 35000, 19000, 15, $suppliers[1]->id],
            ['Matcha Latte', 'Minuman', 18000, 38000, 21000, 15, $suppliers[1]->id],
            ['Croissant', 'Food', 8000, 18000, 9000, 20, $suppliers[2]->id],
            ['Roti Bakar Coklat', 'Food', 6000, 15000, 7000, 20, $suppliers[2]->id],
            ['Nasi Goreng Special', 'Food', 12000, 32000, 15000, 20, null],
            ['Mie Goreng Tektek', 'Food', 9000, 28000, 11000, 20, null],
            ['Kentang Goreng', 'Food', 8000, 20000, 9500, 20, $suppliers[2]->id],
            ['Es Teh Manis', 'Minuman', 4000, 10000, 5000, 30, null],
            ['Jus Alpukat', 'Minuman', 7000, 18000, 8500, 20, null],
            ['Air Mineral', 'Minuman', 3000, 6000, 3500, 40, null],
        ];

        $baseStock = [60, 50, 12, 8, 6, 40, 4, 35, 45, 30, 10, 60, 15, 80];

        $products = [];
        $stockLeft = [];

        foreach ($defs as $i => [$name, $category, $buy, $sell, $hpp, $minStock, $supplierId]) {
            $product = Product::firstOrCreate(
                ['business_id' => $business->id, 'name' => $name],
                [
                    'supplier_id' => $supplierId,
                    'sku' => 'SKU-'.str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
                    'category' => $category,
                    'unit' => 'pcs',
                    'buy_price' => $buy,
                    'sell_price' => $sell,
                    'hpp' => $hpp,
                    'min_stock' => $minStock,
                    'is_active' => true,
                ],
            );
            $products[] = $product;
            $stockLeft[$product->id] = $baseStock[$i];

            StockMovement::create([
                'product_id' => $product->id,
                'type' => StockMovement::TYPE_IN,
                'quantity' => $baseStock[$i],
                'note' => 'Stok awal',
            ]);
        }

        $customerNames = [
            'Budi Santoso', 'Siti Aminah', 'Andi Wijaya', 'Rina Puspita', 'Dedi Kurniawan',
            'Maya Sari', 'Fajar Nugroho', 'Lestari Handayani', 'Agus Prasetyo', 'Nina Marlina',
        ];

        $customers = [];

        foreach ($customerNames as $name) {
            $customers[] = Customer::firstOrCreate(
                ['business_id' => $business->id, 'name' => $name],
                [
                    'phone' => '08'.mt_rand(1000000000, 9999999999),
                    'notes' => $name === 'Budi Santoso' ? 'Pelanggan pelanggan tetap, sering order kopi' : null,
                ],
            );
        }

        $leadDefs = [
            ['Rizky Ramadhan', 'instagram', 'new', 'Tanya menu catering event kampus'],
            ['Olivia Tan', 'teman', 'contacted', 'Mau pesan 50 cup untuk acara kantor'],
            ['Bayu Aji', 'google', 'qualified', 'Perusahaan butuh kopi langganan bulanan'],
            ['Dewi Larasati', 'instagram', 'offer', 'Penawaran harga untuk event pernikahan'],
            ['Kevin Hartono', 'walk-in', 'new', null],
        ];

        foreach ($leadDefs as [$name, $source, $stage, $notes]) {
            Lead::firstOrCreate(
                ['business_id' => $business->id, 'name' => $name],
                [
                    'phone' => '08'.mt_rand(1000000000, 9999999999),
                    'source' => $source,
                    'stage' => $stage,
                    'notes' => $notes,
                    'next_follow_up_at' => Carbon::now()->addDays(mt_rand(1, 5)),
                ],
            );
        }

        $invoice = 0;

        for ($day = 30; $day >= 1; $day--) {
            $date = Carbon::now()->subDays($day);
            $isWeekend = $date->isSaturday() || $date->isSunday();
            $transactionCount = $isWeekend ? mt_rand(5, 8) : mt_rand(3, 6);

            for ($t = 0; $t < $transactionCount; $t++) {
                $customer = $customers[array_rand($customers)];
                $itemCount = mt_rand(1, 3);
                $lineItems = [];

                for ($k = 0; $k < $itemCount; $k++) {
                    $product = $products[array_rand($products)];
                    $quantity = mt_rand(1, 3);

                    if ($stockLeft[$product->id] < $quantity) {
                        continue;
                    }

                    $lineItems[] = [
                        'product' => $product,
                        'quantity' => $quantity,
                    ];
                }

                if ($lineItems === []) {
                    continue;
                }

                $sale = Sale::create([
                    'business_id' => $business->id,
                    'user_id' => $owner->id,
                    'customer_id' => $customer->id,
                    'invoice_no' => 'INV-'.str_pad((string) (++$invoice), 6, '0', STR_PAD_LEFT),
                    'payment_method' => ['cash', 'qris', 'transfer'][mt_rand(0, 2)],
                    'discount' => 0,
                    'total' => 0,
                    'note' => null,
                    'created_at' => $date->copy()->setTime(mt_rand(8, 21), mt_rand(0, 59)),
                    'updated_at' => $date,
                ]);

                $total = 0;

                foreach ($lineItems as $line) {
                    $product = $line['product'];
                    $quantity = $line['quantity'];
                    $unitPrice = $product->sell_price;
                    $lineTotal = $unitPrice * $quantity;

                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'cost_price' => $product->hpp,
                        'discount' => 0,
                        'line_total' => $lineTotal,
                        'created_at' => $sale->created_at,
                        'updated_at' => $sale->created_at,
                    ]);

                    $total += $lineTotal;
                    $stockLeft[$product->id] -= $quantity;

                    StockMovement::create([
                        'product_id' => $product->id,
                        'type' => StockMovement::TYPE_OUT,
                        'quantity' => $quantity,
                        'reference_type' => Sale::class,
                        'reference_id' => $sale->id,
                        'note' => null,
                        'created_at' => $sale->created_at,
                        'updated_at' => $sale->created_at,
                    ]);
                }

                $sale->update(['total' => $total, 'created_at' => $sale->created_at]);
            }
        }

        $purchaseDefs = [
            [$suppliers[0], 'Kopi Arabica Gayo', 20, 12000, Carbon::now()->subDays(14)],
            [$suppliers[1], 'Cappuccino', 10, 18000, Carbon::now()->subDays(10)],
            [$suppliers[2], 'Croissant', 15, 8000, Carbon::now()->subDays(6)],
        ];

        $po = 0;

        foreach ($purchaseDefs as [$supplier, $productName, $qty, $unitCost, $receivedAt]) {
            $product = Product::where('business_id', $business->id)->where('name', $productName)->firstOrFail();

            $purchase = Purchase::firstOrCreate(
                ['business_id' => $business->id, 'po_number' => 'PO-'.str_pad((string) (++$po), 4, '0', STR_PAD_LEFT)],
                [
                    'supplier_id' => $supplier->id,
                    'status' => Purchase::STATUS_RECEIVED,
                    'order_date' => $receivedAt->copy()->subDays(2)->toDateString(),
                    'received_at' => $receivedAt,
                    'total' => $unitCost * $qty,
                    'note' => null,
                ],
            );

            PurchaseItem::create([
                'purchase_id' => $purchase->id,
                'product_id' => $product->id,
                'quantity' => $qty,
                'unit_cost' => $unitCost,
                'line_total' => $unitCost * $qty,
            ]);

            $stockLeft[$product->id] += $qty;

            StockMovement::create([
                'product_id' => $product->id,
                'type' => StockMovement::TYPE_IN,
                'quantity' => $qty,
                'reference_type' => Purchase::class,
                'reference_id' => $purchase->id,
                'note' => 'Penerimaan PO '.$purchase->po_number,
                'created_at' => $receivedAt,
                'updated_at' => $receivedAt,
            ]);
        }

        $expenseDefs = [
            ['Sewa tempat', 2500000, Carbon::now()->startOfMonth()],
            ['Gaji barista', 3200000, Carbon::now()->subDays(20)],
            ['Bahan baku harian', 600000, Carbon::now()->subDays(8)],
            ['Internet & listrik', 350000, Carbon::now()->subDays(5)],
            ['Promosi sosial media', 250000, Carbon::now()->subDays(3)],
        ];

        foreach ($expenseDefs as [$category, $amount, $date]) {
            Expense::create([
                'business_id' => $business->id,
                'user_id' => $owner->id,
                'category' => $category,
                'amount' => $amount,
                'note' => null,
                'expense_date' => $date->toDateString(),
            ]);
        }

        DB::transaction(function () use ($products, $stockLeft) {
            foreach ($products as $product) {
                $product->forceFill(['stock' => $stockLeft[$product->id]])->save();
            }
        });
    }
}
