<?php

namespace Tests\Feature;

use App\Models\Message;
use App\Models\Shop;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MarketplaceTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $email = 'buyer@toko.test'): User
    {
        return User::create([
            'name' => 'Buyer Toko',
            'email' => $email,
            'password' => Hash::make('rahasia123'),
            'role' => 'OWNER',
        ]);
    }

    private function makeShop(array $attributes = []): Shop
    {
        return Shop::create(array_merge([
            'name' => 'Gayo Roastery',
            'city' => 'Yogyakarta',
            'address' => 'Jl. Kaliurang No. 45',
            'open_hours' => '08.00 - 20.00',
            'status' => 'open',
            'rating' => 4.8,
            'reviews_count' => 12,
            'distance_km' => 0.8,
            'map_x' => 30,
            'map_y' => 40,
            'supports_shipping' => true,
            'supports_cod' => false,
            'is_active' => true,
        ], $attributes));
    }

    public function test_shop_list_supports_chip_filters_and_closed_strip(): void
    {
        $open = $this->makeShop();
        $open->items()->create(['name' => 'Kopi Gayo 1kg', 'price' => 245000, 'unit' => 'kg']);
        $this->makeShop([
            'name' => 'Kemasan Kirana',
            'status' => 'closed',
            'supports_shipping' => false,
            'supports_cod' => true,
            'distance_km' => 5.1,
            'reopens_on' => now()->addWeek()->toDateString(),
        ]);

        $this->actingAs($this->makeUser())
            ->getJson('/api/shops?open=1')
            ->assertOk()
            ->assertJsonCount(1, 'data.shops')
            ->assertJsonPath('data.shops.0.name', 'Gayo Roastery')
            ->assertJsonPath('data.shops.0.items.0.name', 'Kopi Gayo 1kg');

        $this->actingAs($this->makeUser('lain@toko.test'))
            ->getJson('/api/shops?cod=1')
            ->assertOk()
            ->assertJsonCount(1, 'data.shops')
            ->assertJsonPath('data.shops.0.name', 'Kemasan Kirana')
            ->assertJsonPath('data.temporarily_closed.name', 'Kemasan Kirana');
    }

    public function test_shop_search_matches_name_and_city(): void
    {
        $this->makeShop(['name' => 'Gayo Roastery', 'city' => 'Yogyakarta']);
        $this->makeShop(['name' => 'Bakery Harapan', 'city' => 'Sleman']);

        $this->actingAs($this->makeUser())
            ->getJson('/api/shops?search=sleman')
            ->assertOk()
            ->assertJsonCount(1, 'data.shops')
            ->assertJsonPath('data.shops.0.name', 'Bakery Harapan');
    }

    public function test_booking_creates_thread_and_reply_is_stored(): void
    {
        $buyer = $this->makeUser();
        $shop = $this->makeShop();

        $response = $this->actingAs($buyer)
            ->postJson('/api/threads', [
                'shop_id' => $shop->id,
                'message' => 'Halo, mau pesan 20 kg kopi.',
            ])
            ->assertCreated()
            ->assertJsonPath('data.thread.subject', 'Pemesanan ke Gayo Roastery');

        $threadId = $response->json('data.thread.id');

        $this->actingAs($buyer)
            ->postJson("/api/threads/{$threadId}/messages", ['message' => 'Bisa kirim Jumat?'])
            ->assertCreated();

        $this->assertDatabaseCount('messages', 2);

        $this->actingAs($buyer)
            ->getJson("/api/threads/{$threadId}/messages")
            ->assertOk()
            ->assertJsonCount(2, 'data.messages')
            ->assertJsonPath('data.messages.1.body', 'Bisa kirim Jumat?');
    }

    public function test_thread_list_marks_unread_and_opening_clears_it(): void
    {
        $buyer = $this->makeUser();
        $shop = $this->makeShop();
        $thread = Thread::create([
            'user_id' => $buyer->id,
            'shop_id' => $shop->id,
            'subject' => 'Restock keripik',
            'last_message_at' => now(),
            'unread_count' => 2,
        ]);

        Message::create([
            'thread_id' => $thread->id,
            'sender_type' => 'seller',
            'sender_name' => $shop->name,
            'body' => 'Stok masih 60 pack Kak.',
        ]);
        Message::create([
            'thread_id' => $thread->id,
            'sender_type' => 'seller',
            'sender_name' => $shop->name,
            'body' => 'Bisa COD sore ini.',
        ]);

        $this->actingAs($buyer)
            ->getJson('/api/threads')
            ->assertOk()
            ->assertJsonPath('data.threads.0.unread_count', 2)
            ->assertJsonPath('data.unread_total', 2);

        $this->actingAs($buyer)
            ->getJson("/api/threads/{$thread->id}/messages")
            ->assertOk();

        $this->assertSame(0, $thread->fresh()->unread_count);
        $this->assertDatabaseMissing('messages', ['thread_id' => $thread->id, 'read_at' => null]);
    }

    public function test_buyer_cannot_open_another_users_thread(): void
    {
        $owner = $this->makeUser();
        $intruder = $this->makeUser('intruder@toko.test');
        $thread = Thread::create([
            'user_id' => $owner->id,
            'shop_id' => $this->makeShop()->id,
            'subject' => 'Pribadi',
            'last_message_at' => now(),
        ]);

        $this->actingAs($intruder)
            ->getJson("/api/threads/{$thread->id}/messages")
            ->assertNotFound();

        $this->actingAs($intruder)
            ->postJson("/api/threads/{$thread->id}/messages", ['message' => 'Halo'])
            ->assertNotFound();
    }
}
