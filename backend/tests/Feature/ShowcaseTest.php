<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ShowcaseTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $email = 'owner@toko.test'): User
    {
        return User::create([
            'name' => 'Owner Toko',
            'email' => $email,
            'password' => Hash::make('rahasia123'),
            'role' => 'OWNER',
        ]);
    }

    private function makeBusiness(User $user, string $name = 'Kopi BETA'): Business
    {
        return $user->businesses()->create(['name' => $name, 'type' => 'fnb']);
    }

    private function makeProduct(Business $business, string $name, bool $showcased): Product
    {
        return $business->products()->create([
            'name' => $name,
            'buy_price' => 4000,
            'sell_price' => 10000,
            'hpp' => 4000,
            'stock' => 10,
            'is_active' => true,
            'is_showcased' => $showcased,
        ]);
    }

    public function test_public_showcase_only_lists_posted_products(): void
    {
        $user = $this->makeUser();
        $business = $this->makeBusiness($user);
        $this->makeProduct($business, 'Kopi Gayo', true);
        $this->makeProduct($business, 'Kopi Robusta', false);

        $response = $this->getJson('/api/showcase');

        $response->assertOk()
            ->assertJsonCount(1, 'data.products')
            ->assertJsonPath('data.products.0.name', 'Kopi Gayo')
            ->assertJsonPath('data.products.0.business.name', 'Kopi BETA');
    }

    public function test_owner_can_toggle_product_showcase(): void
    {
        $user = $this->makeUser();
        $business = $this->makeBusiness($user);
        $product = $this->makeProduct($business, 'Kopi Gayo', false);

        $this->actingAs($user)
            ->postJson("/api/products/{$product->id}/showcase")
            ->assertOk()
            ->assertJsonPath('data.product.is_showcased', true);

        $this->assertDatabaseHas('products', ['id' => $product->id, 'is_showcased' => true]);

        $this->actingAs($user)
            ->postJson("/api/products/{$product->id}/showcase")
            ->assertOk()
            ->assertJsonPath('data.product.is_showcased', false);
    }

    public function test_owner_cannot_toggle_another_business_product(): void
    {
        $owner = $this->makeUser('owner@toko.test');
        $other = $this->makeUser('other@toko.test');
        $otherProduct = $this->makeProduct($this->makeBusiness($other, 'Toko Lain'), 'Roti', false);

        $this->actingAs($owner)
            ->postJson("/api/products/{$otherProduct->id}/showcase")
            ->assertNotFound();
    }

    public function test_user_can_update_profile_name(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)
            ->putJson('/api/profile', ['name' => 'Nama Baru'])
            ->assertOk()
            ->assertJsonPath('data.user.name', 'Nama Baru');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Nama Baru']);
    }

    public function test_profile_name_is_required(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)
            ->putJson('/api/profile', ['name' => ''])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }
}
