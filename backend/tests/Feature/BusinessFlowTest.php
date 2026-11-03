<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BusinessFlowTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(): User
    {
        $user = User::create([
            'name' => 'Owner Toko',
            'email' => 'owner@toko.test',
            'password' => Hash::make('rahasia123'),
            'role' => 'OWNER',
        ]);

        return $user;
    }

    private function makeBusiness(User $user): Business
    {
        return $user->businesses()->create(['name' => 'Kopi BETA', 'type' => 'fnb']);
    }

    private function makeProduct(Business $business, int $stock = 10): Product
    {
        $supplier = $business->suppliers()->create(['name' => 'PT Kopi Nusantara', 'lead_time_days' => 2]);

        return $business->products()->create([
            'supplier_id' => $supplier->id,
            'name' => 'Espresso',
            'buy_price' => 4000,
            'sell_price' => 10000,
            'hpp' => 4000,
            'min_stock' => 5,
            'stock' => $stock,
        ]);
    }

    public function test_unauthenticated_dashboard_returns_401(): void
    {
        $this->getJson('/api/dashboard')->assertStatus(401);
    }

    public function test_dashboard_returns_empty_state_without_business(): void
    {
        $this->actingAs($this->makeUser());

        $this->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('data.kpi.revenue_month', 0);
    }

    public function test_business_onboarding(): void
    {
        $this->actingAs($this->makeUser());

        $this->postJson('/api/business', [
            'name' => 'Kopi Tertial',
            'type' => 'fnb',
            'currency' => 'IDR',
            'payment_methods' => ['cash', 'qris'],
        ])->assertStatus(201);

        $this->assertDatabaseHas('businesses', ['name' => 'Kopi Tertial']);
    }

    public function test_sale_records_stock_movement_and_updates_kpi(): void
    {
        $user = $this->makeUser();
        $business = $this->makeBusiness($user);
        $product = $this->makeProduct($business, 10);
        $customer = $business->customers()->create(['name' => 'Budi']);

        // authenticated via session
        $this->actingAs($user);

        $response = $this->postJson('/api/sales', [
            'customer_id' => $customer->id,
            'payment_method' => 'qris',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 3],
            ],
        ])->assertStatus(201);

        $response->assertJsonPath('data.sale.total', 30000);
        $response->assertJsonPath('data.sale.items.0.product', 'Espresso');

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 7]);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'out',
            'quantity' => 3,
            'reference_type' => Sale::class,
        ]);

        $this->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('data.kpi.revenue_month', 30000);

        $this->getJson('/api/customers/'.$customer->id)
            ->assertOk()
            ->assertJsonPath('data.metrics.total_spending', 30000)
            ->assertJsonPath('data.metrics.order_count', 1);
    }

    public function test_sale_rejects_insufficient_stock(): void
    {
        $user = $this->makeUser();
        $business = $this->makeBusiness($user);
        $product = $this->makeProduct($business, 1);

        // authenticated via session
        $this->actingAs($user);

        $this->postJson('/api/sales', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 5],
            ],
        ])->assertStatus(422);

        $this->assertDatabaseCount('sales', 0);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 1]);
    }

    public function test_sale_requires_onboarding_first(): void
    {
        $this->actingAs($this->makeUser());

        $this->postJson('/api/sales', [
            'items' => [],
        ])->assertStatus(422)->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    public function test_sale_without_business_returns_conflict(): void
    {
        $user = $this->makeUser();
        $product = Product::create([
            'name' => 'Produk Tanpa Bisnis',
            'buy_price' => 1000,
            'sell_price' => 2000,
            'stock' => 5,
        ]);

        // authenticated via session
        $this->actingAs($user);

        $this->postJson('/api/sales', [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ])->assertStatus(409)->assertJsonPath('error.code', 'BUSINESS_REQUIRED');
    }

    public function test_product_crud_and_stock_adjust(): void
    {
        $user = $this->makeUser();
        $business = $this->makeBusiness($user);
        $supplier = $business->suppliers()->create(['name' => 'PT Minuman Segar', 'lead_time_days' => 1]);

        // authenticated via session
        $this->actingAs($user);

        $created = $this->postJson('/api/products', [
            'name' => 'Matcha Latte',
            'buy_price' => 8000,
            'sell_price' => 18000,
            'hpp' => 8000,
            'min_stock' => 4,
            'unit' => 'cup',
            'supplier_id' => $supplier->id,
            'initial_stock' => 12,
        ])->assertStatus(201);

        $productId = $created->json('data.product.id');
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $productId,
            'type' => 'in',
            'quantity' => 12,
            'note' => 'Stok awal',
        ]);

        $this->putJson('/api/products/'.$productId, [
            'sell_price' => 20000,
            'min_stock' => 6,
        ])->assertOk()->assertJsonPath('data.product.sell_price', 20000);

        $this->postJson('/api/stock/adjust', [
            'product_id' => $productId,
            'quantity' => -3,
            'note' => 'Penyusutan spill',
        ])->assertStatus(201);

        $this->assertDatabaseHas('products', ['id' => $productId, 'stock' => 9]);

        $this->postJson('/api/stock/adjust', [
            'product_id' => $productId,
            'quantity' => -50,
        ])->assertStatus(422)->assertJsonPath('error.code', 'STOCK_NEGATIVE');

        $this->deleteJson('/api/products/'.$productId)
            ->assertOk()
            ->assertJson(['data' => null]);

        $this->assertDatabaseHas('products', ['id' => $productId, 'is_active' => false]);
    }

    public function test_customer_segmentation_updates_with_history(): void
    {
        $user = $this->makeUser();
        $business = $this->makeBusiness($user);
        $product = $this->makeProduct($business, 50);
        $customer = $business->customers()->create(['name' => 'Siti']);

        // authenticated via session
        $this->actingAs($user);

        for ($i = 0; $i < 3; $i++) {
            $this->postJson('/api/sales', [
                'customer_id' => $customer->id,
                'items' => [['product_id' => $product->id, 'quantity' => 1]],
            ])->assertStatus(201);
        }

        $this->getJson('/api/customers')
            ->assertOk()
            ->assertJsonPath('data.customers.0.segment', 'loyal');

        $this->getJson('/api/customers/'.$customer->id)
            ->assertOk()
            ->assertJsonCount(3, 'data.history');
    }

    public function test_lead_pipeline_converts_to_customer(): void
    {
        $user = $this->makeUser();
        $business = $this->makeBusiness($user);

        // authenticated via session
        $this->actingAs($user);

        $lead = $this->postJson('/api/leads', [
            'name' => 'Andi',
            'phone' => '0812xxx',
            'source' => 'walkin',
            'stage' => 'new',
        ])->assertStatus(201)->json('data.lead');

        $this->patchJson('/api/leads/'.$lead['id'], ['stage' => 'converted'])
            ->assertOk()
            ->assertJsonPath('data.lead.converted_customer_id', function ($value) {
                return $value !== null;
            });

        $this->assertDatabaseHas('customers', [
            'business_id' => $business->id,
            'name' => 'Andi',
        ]);
    }

    public function test_purchase_receive_increases_stock(): void
    {
        $user = $this->makeUser();
        $business = $this->makeBusiness($user);
        $product = $this->makeProduct($business, 5);
        $supplier = $product->supplier;

        // authenticated via session
        $this->actingAs($user);

        $purchase = $this->postJson('/api/purchases', [
            'supplier_id' => $supplier->id,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 20, 'unit_cost' => 4000],
            ],
        ])->assertStatus(201);

        $purchaseId = $purchase->json('data.purchase.id');
        $this->assertDatabaseHas('purchases', ['id' => $purchaseId, 'status' => 'ordered']);

        $this->postJson('/api/purchases/'.$purchaseId.'/receive')
            ->assertOk()
            ->assertJsonPath('data.purchase.status', 'received');

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 25]);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'in',
            'quantity' => 20,
            'reference_type' => Purchase::class,
        ]);
    }

    public function test_sale_rejects_product_and_customer_from_other_business(): void
    {
        $user = $this->makeUser();
        $this->makeBusiness($user);

        $otherUser = User::create([
            'name' => 'Owner Lain',
            'email' => 'lain@toko.test',
            'password' => Hash::make('rahasia123'),
            'role' => 'OWNER',
        ]);
        $otherBusiness = $otherUser->businesses()->create(['name' => 'Toko Lain', 'type' => 'fnb']);
        $otherSupplier = $otherBusiness->suppliers()->create(['name' => 'PT Lain', 'lead_time_days' => 1]);
        $otherProduct = $otherBusiness->products()->create([
            'supplier_id' => $otherSupplier->id,
            'name' => 'Produk Lain',
            'buy_price' => 1000,
            'sell_price' => 2000,
            'hpp' => 1000,
            'stock' => 10,
        ]);
        $otherCustomer = $otherBusiness->customers()->create(['name' => 'Pelanggan Lain']);

        $this->actingAs($user);

        $this->postJson('/api/sales', [
            'items' => [['product_id' => $otherProduct->id, 'quantity' => 1]],
        ])->assertStatus(422)->assertJsonPath('error.code', 'VALIDATION_ERROR');

        $this->postJson('/api/sales', [
            'customer_id' => $otherCustomer->id,
            'items' => [['product_id' => $this->makeProduct($this->business($user))->id, 'quantity' => 1]],
        ])->assertStatus(422)->assertJsonPath('error.code', 'VALIDATION_ERROR');

        $this->assertDatabaseCount('sales', 0);
        $this->assertDatabaseHas('products', ['id' => $otherProduct->id, 'stock' => 10]);
    }

    public function test_sale_rejects_duplicate_item_lines_that_exceed_stock(): void
    {
        $user = $this->makeUser();
        $business = $this->makeBusiness($user);
        $product = $this->makeProduct($business, 10);

        $this->actingAs($user);

        $this->postJson('/api/sales', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 7],
                ['product_id' => $product->id, 'quantity' => 7],
            ],
        ])->assertStatus(422);

        $this->assertDatabaseCount('sales', 0);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 10]);
    }

    public function test_purchase_rejects_supplier_and_product_from_other_business(): void
    {
        $user = $this->makeUser();
        $business = $this->makeBusiness($user);
        $product = $this->makeProduct($business, 5);

        $otherUser = User::create([
            'name' => 'Owner Lain',
            'email' => 'lain@toko.test',
            'password' => Hash::make('rahasia123'),
            'role' => 'OWNER',
        ]);
        $otherBusiness = $otherUser->businesses()->create(['name' => 'Toko Lain', 'type' => 'fnb']);
        $otherSupplier = $otherBusiness->suppliers()->create(['name' => 'PT Lain', 'lead_time_days' => 1]);
        $otherProduct = $otherBusiness->products()->create([
            'supplier_id' => $otherSupplier->id,
            'name' => 'Produk Lain',
            'buy_price' => 1000,
            'sell_price' => 2000,
            'hpp' => 1000,
            'stock' => 10,
        ]);

        $this->actingAs($user);

        $this->postJson('/api/purchases', [
            'supplier_id' => $otherSupplier->id,
            'items' => [['product_id' => $product->id, 'quantity' => 5]],
        ])->assertStatus(422)->assertJsonPath('error.code', 'VALIDATION_ERROR');

        $this->postJson('/api/purchases', [
            'supplier_id' => $product->supplier_id,
            'items' => [['product_id' => $otherProduct->id, 'quantity' => 5]],
        ])->assertStatus(422)->assertJsonPath('error.code', 'VALIDATION_ERROR');

        $this->assertDatabaseCount('purchases', 0);
        $this->assertDatabaseHas('products', ['id' => $otherProduct->id, 'stock' => 10]);
    }

    public function test_receive_twice_does_not_increase_stock_twice(): void
    {
        $user = $this->makeUser();
        $business = $this->makeBusiness($user);
        $product = $this->makeProduct($business, 5);
        $supplier = $product->supplier;

        $this->actingAs($user);

        $purchaseId = $this->postJson('/api/purchases', [
            'supplier_id' => $supplier->id,
            'items' => [['product_id' => $product->id, 'quantity' => 10, 'unit_cost' => 4000]],
        ])->assertStatus(201)->json('data.purchase.id');

        $this->postJson('/api/purchases/'.$purchaseId.'/receive')->assertOk();
        $this->postJson('/api/purchases/'.$purchaseId.'/receive')
            ->assertOk()
            ->assertJsonPath('data.purchase.status', 'received');

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 15]);
        $this->assertDatabaseCount('stock_movements', 1);
    }

    public function test_follow_ups_group_leads_and_include_at_risk_customers(): void
    {
        $user = $this->makeUser();
        $business = $this->makeBusiness($user);
        $product = $this->makeProduct($business, 10);
        $customer = $business->customers()->create(['name' => 'Rina']);

        $this->actingAs($user);

        $this->postJson('/api/sales', [
            'customer_id' => $customer->id,
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ])->assertStatus(201);

        Sale::where('customer_id', $customer->id)->update([
            'created_at' => Carbon::now()->subDays(50),
        ]);

        $this->postJson('/api/leads', [
            'name' => 'Lead Overdue',
            'next_follow_up_at' => Carbon::today()->subDay()->setTime(9, 0)->toDateTimeString(),
        ])->assertStatus(201);

        $this->postJson('/api/leads', [
            'name' => 'Lead Hari Ini',
            'next_follow_up_at' => Carbon::today()->setTime(10, 0)->toDateTimeString(),
        ])->assertStatus(201);

        $this->postJson('/api/leads', [
            'name' => 'Lead Minggu Ini',
            'next_follow_up_at' => Carbon::today()->addDays(3)->setTime(10, 0)->toDateTimeString(),
        ])->assertStatus(201);

        $this->postJson('/api/leads', [
            'name' => 'Lead Konversi',
            'stage' => 'converted',
            'next_follow_up_at' => Carbon::today()->subDay()->toDateTimeString(),
        ])->assertStatus(201);

        $this->getJson('/api/leads/follow-ups')
            ->assertOk()
            ->assertJsonPath('data.counts.overdue', 1)
            ->assertJsonPath('data.counts.today', 1)
            ->assertJsonPath('data.counts.upcoming', 1)
            ->assertJsonPath('data.leads.overdue.0.name', 'Lead Overdue')
            ->assertJsonPath('data.leads.today.0.name', 'Lead Hari Ini')
            ->assertJsonPath('data.leads.upcoming.0.name', 'Lead Minggu Ini')
            ->assertJsonPath('data.counts.customers', 1)
            ->assertJsonPath('data.customers.0.name', 'Rina');
    }

    public function test_dashboard_auto_restock_insight_includes_supplier_details(): void
    {
        $user = $this->makeUser();
        $business = $this->makeBusiness($user);
        $product = $this->makeProduct($business, 42);

        $this->actingAs($user);

        $this->postJson('/api/sales', [
            'items' => [['product_id' => $product->id, 'quantity' => 40]],
        ])->assertStatus(201);

        $insights = $this->getJson('/api/dashboard')->assertOk()->json('data.insights');
        $restock = collect($insights)->firstWhere('type', 'auto_restock');

        $this->assertNotNull($restock);
        $this->assertSame($product->id, $restock['product_id']);
        $this->assertSame('PT Kopi Nusantara', $restock['supplier_name']);
        $this->assertSame($product->supplier_id, $restock['supplier_id']);
        $this->assertSame($product->buy_price, $restock['buy_price']);
        $this->assertGreaterThanOrEqual(1, $restock['recommended_quantity']);
    }

    public function test_customer_detail_exposes_repeat_order_candidates(): void
    {
        $user = $this->makeUser();
        $business = $this->makeBusiness($user);
        $espresso = $this->makeProduct($business, 50);
        $latte = $business->products()->create([
            'supplier_id' => $espresso->supplier_id,
            'name' => 'Latte',
            'buy_price' => 6000,
            'sell_price' => 15000,
            'hpp' => 6000,
            'min_stock' => 3,
            'stock' => 50,
        ]);
        $customer = $business->customers()->create(['name' => 'Dimas']);

        $this->actingAs($user);

        $this->postJson('/api/sales', [
            'customer_id' => $customer->id,
            'items' => [
                ['product_id' => $espresso->id, 'quantity' => 4],
                ['product_id' => $latte->id, 'quantity' => 1],
            ],
        ])->assertStatus(201);

        $this->travel(1)->minutes();

        $this->postJson('/api/sales', [
            'customer_id' => $customer->id,
            'items' => [
                ['product_id' => $espresso->id, 'quantity' => 2],
            ],
        ])->assertStatus(201);

        $this->getJson('/api/customers/'.$customer->id)
            ->assertOk()
            ->assertJsonCount(1, 'data.repeat.last_order')
            ->assertJsonPath('data.repeat.last_order.0.product_id', $espresso->id)
            ->assertJsonPath('data.repeat.last_order.0.quantity', 2)
            ->assertJsonPath('data.repeat.frequent.0.product_id', $espresso->id)
            ->assertJsonPath('data.repeat.frequent.0.total_qty', 6)
            ->assertJsonPath('data.repeat.frequent.1.product_id', $latte->id)
            ->assertJsonPath('data.repeat.frequent.1.total_qty', 1);
    }

    public function test_dashboard_lists_at_risk_customers_after_45_days(): void
    {
        $user = $this->makeUser();
        $business = $this->makeBusiness($user);
        $product = $this->makeProduct($business, 10);
        $customer = $business->customers()->create(['name' => 'Pelanggan Lama']);

        $this->actingAs($user);

        $this->postJson('/api/sales', [
            'customer_id' => $customer->id,
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ])->assertStatus(201);

        $this->travel(50)->days();

        $this->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonCount(1, 'data.at_risk_customers')
            ->assertJsonPath('data.at_risk_customers.0.id', $customer->id)
            ->assertJsonPath('data.at_risk_customers.0.name', 'Pelanggan Lama')
            ->assertJsonPath('data.at_risk_customers.0.days_since', 50);
    }

    private function business(User $user): Business
    {
        return $user->businesses()->first();
    }
}
