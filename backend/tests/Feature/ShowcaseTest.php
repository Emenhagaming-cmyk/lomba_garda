<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Product;
use App\Models\ProductReview;
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

    public function test_showcase_detail_returns_reviews_and_rating_summary(): void
    {
        $user = $this->makeUser();
        $business = $this->makeBusiness($user);
        $product = $this->makeProduct($business, 'Kopi Gayo', true);
        $product->update([
            'image_url' => '/products/kopi.jpg',
            'image_credit' => 'Foto: Umer Shabib · CC BY · Coffee Beans',
        ]);

        ProductReview::create([
            'product_id' => $product->id,
            'reviewer_name' => 'Rina',
            'rating' => 5,
            'summary' => 'Mantap',
            'body' => 'Sesuai deskripsi.',
            'is_verified' => true,
            'reviewed_at' => now()->subDays(1),
        ]);
        ProductReview::create([
            'product_id' => $product->id,
            'reviewer_name' => 'Dimas',
            'rating' => 5,
            'summary' => 'Worth it',
            'body' => 'Packing rapi.',
            'is_verified' => false,
            'reviewed_at' => now()->subDays(2),
        ]);
        ProductReview::create([
            'product_id' => $product->id,
            'reviewer_name' => 'Siti',
            'rating' => 4,
            'summary' => 'Enak',
            'body' => 'Cocok untuk oleh-oleh.',
            'is_verified' => true,
            'reviewed_at' => now()->subDays(3),
        ]);

        $this->getJson('/api/showcase')
            ->assertOk()
            ->assertJsonPath('data.products.0.image_url', '/products/kopi.jpg')
            ->assertJsonPath('data.products.0.rating_avg', 4.7)
            ->assertJsonPath('data.products.0.review_count', 3);

        $this->getJson("/api/showcase/{$product->id}")
            ->assertOk()
            ->assertJsonPath('data.product.name', 'Kopi Gayo')
            ->assertJsonPath('data.product.reviews.0.reviewer_name', 'Rina')
            ->assertJsonPath('data.review_summary.average', 4.7)
            ->assertJsonPath('data.review_summary.total', 3)
            ->assertJsonPath('data.review_summary.distribution.0.star', 5)
            ->assertJsonPath('data.review_summary.distribution.0.count', 2)
            ->assertJsonPath('data.review_summary.distribution.0.percent', 67)
            ->assertJsonPath('data.review_summary.distribution.1.count', 1)
            ->assertJsonPath('data.review_summary.distribution.1.percent', 33)
            ->assertJsonCount(3, 'data.product.reviews');
    }

    public function test_showcase_detail_hidden_for_unposted_or_inactive_products(): void
    {
        $user = $this->makeUser();
        $business = $this->makeBusiness($user);
        $hidden = $this->makeProduct($business, 'Kopi Robusta', false);
        $inactive = $this->makeProduct($business, 'Kopi Luwak', true);
        $inactive->update(['is_active' => false]);

        $this->getJson("/api/showcase/{$hidden->id}")->assertNotFound();
        $this->getJson("/api/showcase/{$inactive->id}")->assertNotFound();
    }

    public function test_owner_can_save_product_image_fields(): void
    {
        $user = $this->makeUser();
        $this->makeBusiness($user);

        $this->actingAs($user)
            ->postJson('/api/products', [
                'name' => 'Kopi Gayo',
                'buy_price' => 4000,
                'sell_price' => 10000,
                'image_url' => '/products/kopi-arabica-gayo.jpg',
                'image_credit' => 'Foto: Umer Shabib · CC BY · Coffee Beans',
            ])
            ->assertCreated()
            ->assertJsonPath('data.product.image_url', '/products/kopi-arabica-gayo.jpg')
            ->assertJsonPath('data.product.image_credit', 'Foto: Umer Shabib · CC BY · Coffee Beans');

        $this->assertDatabaseHas('products', [
            'name' => 'Kopi Gayo',
            'image_url' => '/products/kopi-arabica-gayo.jpg',
        ]);
    }

    public function test_product_image_search_requires_authentication_and_query(): void
    {
        $this->getJson('/api/product-images?q=kopi')->assertUnauthorized();

        $user = $this->makeUser();

        $this->actingAs($user)
            ->getJson('/api/product-images')
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
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
