<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
