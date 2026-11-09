<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Shop;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(): User
    {
        return User::create([
            'name' => 'Owner Toko',
            'email' => 'owner@toko.test',
            'password' => Hash::make('rahasia123'),
            'role' => 'OWNER',
        ]);
    }

    public function test_unauthenticated_notifications_return_401(): void
    {
        $this->getJson('/api/notifications')->assertStatus(401);
    }

    public function test_business_without_activity_returns_empty_notifications(): void
    {
        $user = $this->makeUser();
        $user->businesses()->create(['name' => 'Kopi Sepi', 'type' => 'fnb']);
        $this->actingAs($user);

        $this->getJson('/api/notifications')
            ->assertOk()
            ->assertJsonPath('data.notifications', [])
            ->assertJsonPath('data.counts.total', 0)
            ->assertJsonPath('data.counts.warning', 0);
    }

    public function test_notifications_summarize_business_signals(): void
    {
        $user = $this->makeUser();
        $business = $user->businesses()->create(['name' => 'Kopi Ramai', 'type' => 'fnb']);
        $supplier = $business->suppliers()->create(['name' => 'PT Kopi Nusantara', 'lead_time_days' => 2]);

        $business->products()->create([
            'supplier_id' => $supplier->id,
            'name' => 'Espresso',
            'buy_price' => 4000,
            'sell_price' => 10000,
            'hpp' => 4000,
            'min_stock' => 5,
            'stock' => 2,
        ]);

        Lead::create([
            'business_id' => $business->id,
            'name' => 'Calon Pembeli',
            'stage' => 'contacted',
            'next_follow_up_at' => Carbon::yesterday(),
        ]);

        $shop = Shop::create(['business_id' => $business->id, 'name' => 'Toko Kopi']);

        Thread::create([
            'user_id' => $user->id,
            'shop_id' => $shop->id,
            'business_id' => $business->id,
            'subject' => 'Pesan PO',
            'unread_count' => 3,
        ]);

        Purchase::create([
            'business_id' => $business->id,
            'supplier_id' => $supplier->id,
            'po_number' => 'PO-TEST-1',
            'status' => 'ordered',
            'order_date' => Carbon::today(),
        ]);

        Sale::create([
            'business_id' => $business->id,
            'user_id' => $user->id,
            'invoice_no' => 'INV-TEST-1',
            'payment_method' => 'cash',
            'total' => 50000,
        ]);

        $this->actingAs($user);

        $response = $this->getJson('/api/notifications')->assertOk();

        $notifications = collect($response->json('data.notifications'));
        $types = $notifications->pluck('type');

        $this->assertTrue($types->contains('omzet'));
        $this->assertTrue($types->contains('low_stock'));
        $this->assertTrue($types->contains('follow_up'));
        $this->assertTrue($types->contains('chat'));
        $this->assertTrue($types->contains('purchase_order'));
        $this->assertSame('/inventory', $notifications->firstWhere('type', 'low_stock')['action_url']);
        $this->assertSame('/messages', $notifications->firstWhere('type', 'chat')['action_url']);
        $this->assertSame('warning', $notifications->firstWhere('type', 'low_stock')['severity']);
        $this->assertGreaterThan(0, $response->json('data.counts.warning'));
        $this->assertSame($notifications->count(), $response->json('data.counts.total'));
    }
}
