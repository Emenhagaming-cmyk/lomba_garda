<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Message;
use App\Models\Shop;
use App\Models\ShopItem;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class MarketplaceDemoSeeder extends Seeder
{
    public function run(): void
    {
        $shops = [
            [
                'name' => 'Gayo Roastery',
                'city' => 'Yogyakarta',
                'address' => 'Jl. Kaliurang No. 45, Sleman, Yogyakarta',
                'phone' => '081211112222',
                'open_hours' => '08.00 - 20.00',
                'status' => 'open',
                'rating' => 4.8,
                'reviews_count' => 124,
                'distance_km' => 0.8,
                'map_x' => 28,
                'map_y' => 34,
                'supports_shipping' => true,
                'supports_cod' => true,
                'items' => [
                    ['Kopi Arabica Gayo 250g', 68000, 'pack'],
                    ['Kopi Arabica Gayo 1kg', 245000, 'kg'],
                    ['Cold Brew Botol 1L', 45000, 'botol'],
                    ['Nicaragua Green Bean 1kg', 310000, 'kg'],
                ],
            ],
            [
                'name' => 'Snack Nusantara',
                'city' => 'Yogyakarta',
                'address' => 'Jl. Malioboro No. 88, Yogyakarta',
                'phone' => '081233334444',
                'open_hours' => '09.00 - 22.00',
                'status' => 'open',
                'rating' => 4.6,
                'reviews_count' => 87,
                'distance_km' => 1.4,
                'map_x' => 55,
                'map_y' => 46,
                'supports_shipping' => true,
                'supports_cod' => false,
                'items' => [
                    ['Keripik Tempe Premium 200g', 18000, 'pack'],
                    ['Kacang Mede Sangrai 250g', 42000, 'pack'],
                    ['Oleh-oleh Paket Hemat', 95000, 'paket'],
                ],
            ],
            [
                'name' => 'Bakery Harapan',
                'city' => 'Yogyakarta',
                'address' => 'Jl. Gejayan No. 12, Yogyakarta',
                'phone' => '081255556666',
                'open_hours' => '06.00 - 19.00',
                'status' => 'open',
                'rating' => 4.9,
                'reviews_count' => 210,
                'distance_km' => 2.1,
                'map_x' => 44,
                'map_y' => 68,
                'supports_shipping' => true,
                'supports_cod' => true,
                'items' => [
                    ['Roti Sobek Cokelat', 25000, 'pcs'],
                    ['Croissant Butter', 18000, 'pcs'],
                    ['Kue Kering Nastar 500g', 65000, 'box'],
                ],
            ],
            [
                'name' => 'Sumber Rejeki Sembako',
                'city' => 'Sleman',
                'address' => 'Jl. Magelang KM 7 No. 3, Sleman, Yogyakarta',
                'phone' => '081277778888',
                'open_hours' => '07.00 - 20.00',
                'status' => 'open',
                'rating' => 4.4,
                'reviews_count' => 56,
                'distance_km' => 3.2,
                'map_x' => 68,
                'map_y' => 24,
                'supports_shipping' => true,
                'supports_cod' => true,
                'items' => [
                    ['Gula Pasir 1kg', 16500, 'kg'],
                    ['Minyak Goreng 2L', 38000, 'pouch'],
                    ['Beras Pandan Wangi 5kg', 78000, 'karung'],
                ],
            ],
            [
                'name' => 'Susu Segar Lestari',
                'city' => 'Bantul',
                'address' => 'Jl. Parangtritis KM 11, Bantul, Yogyakarta',
                'phone' => '081299990000',
                'open_hours' => '05.00 - 17.00',
                'status' => 'open',
                'rating' => 4.7,
                'reviews_count' => 41,
                'distance_km' => 4.6,
                'map_x' => 22,
                'map_y' => 62,
                'supports_shipping' => false,
                'supports_cod' => true,
                'items' => [
                    ['Susu Segar Pasteur 1L', 26000, 'liter'],
                    ['Yogurt Plain 500ml', 22000, 'jar'],
                ],
            ],
            [
                'name' => 'Kemasan Kirana',
                'city' => 'Yogyakarta',
                'address' => 'Jl. Bantul No. 120, Yogyakarta',
                'phone' => '081222224444',
                'open_hours' => '08.00 - 18.00',
                'status' => 'closed',
                'rating' => 4.5,
                'reviews_count' => 33,
                'distance_km' => 5.1,
                'map_x' => 78,
                'map_y' => 55,
                'supports_shipping' => true,
                'supports_cod' => false,
                'reopens_on' => Carbon::now()->addWeek(),
                'items' => [
                    ['Standing Pouch 250g', 1200, 'pcs'],
                    ['Cup Injection 12oz', 800, 'pcs'],
                ],
            ],
        ];

        $demoBusiness = Business::where('name', 'Kopi Tertial')->first();

        $created = [];

        foreach ($shops as $data) {
            $items = $data['items'];
            unset($data['items']);

            $shop = Shop::updateOrCreate(
                ['name' => $data['name']],
                $data + [
                    'business_id' => null,
                    'is_active' => true,
                ],
            );

            foreach ($items as [$name, $price, $unit]) {
                ShopItem::updateOrCreate(
                    ['shop_id' => $shop->id, 'name' => $name],
                    ['price' => $price, 'unit' => $unit],
                );
            }

            $created[$shop->name] = $shop;
        }

        $buyer = User::where('email', 'demo@tokoku.app')->first();

        if ($buyer === null) {
            return;
        }

        $threads = [
            [
                'shop' => 'Gayo Roastery',
                'subject' => 'Pesan 20 kg biji kopi Gayo',
                'unread' => 2,
                'messages' => [
                    ['seller', 'Halo Kak, stok Arabica Gayo 1kg ready 30 karung. Mau ambil berapa?', 210],
                    ['buyer', 'Halo, saya mau pesan 20 kg untuk bulan ini. Bisa harga 235rb per kg?', 195],
                    ['seller', 'Bisa Kak, 20 kg @ Rp 235.000. Bisa kirim Jumat pagi?', 180],
                    ['seller', 'Kalau oke, saya siapkan invoice-nya sekarang ya.', 176],
                ],
            ],
            [
                'shop' => 'Snack Nusantara',
                'subject' => 'Restock keripik tempe',
                'unread' => 1,
                'messages' => [
                    ['buyer', 'Mbak, keripik tempe ukuran 200g masih ada 40 pack?', 320],
                    ['seller', 'Masih Kak, 60 pack. Mau sekalian kacang mede?', 300],
                    ['buyer', 'Sementara keripik dulu 40 pack ya. Kirim ke Jl. Kaliurang.', 240],
                    ['seller', 'Siap, total Rp 720.000. Bisa COD sore ini.', 60],
                ],
            ],
            [
                'shop' => 'Bakery Harapan',
                'subject' => 'Roti sobek untuk weekend',
                'unread' => 0,
                'messages' => [
                    ['buyer', 'Halo, untuk Sabtu butuh 50 roti sobek cokelat. Bisa?', 1440],
                    ['seller', 'Bisa Kak, diambil jam 6 pagi ya biar masih hangat.', 1380],
                    ['buyer', 'Oke, saya ambil jam 6.30.', 1320],
                    ['seller', 'Diterima. Terima kasih Kak!', 1315],
                ],
            ],
            [
                'shop' => 'Sumber Rejeki Sembako',
                'subject' => 'Harga beras 5kg',
                'unread' => 0,
                'messages' => [
                    ['buyer', 'Pak, beras Pandan Wangi 5kg harga grosir berapa?', 2880],
                    ['seller', 'Ambil 10 karung jadi Rp 74.000 per karung Kak.', 2860],
                ],
            ],
        ];

        foreach ($threads as $data) {
            $shop = $created[$data['shop']] ?? null;

            if ($shop === null) {
                continue;
            }

            $thread = Thread::updateOrCreate(
                ['user_id' => $buyer->id, 'shop_id' => $shop->id],
                [
                    'business_id' => $demoBusiness?->id,
                    'subject' => $data['subject'],
                    'unread_count' => $data['unread'],
                    'last_message_at' => Carbon::now()->subMinutes($data['messages'][count($data['messages']) - 1][2]),
                ],
            );

            Message::where('thread_id', $thread->id)->delete();

            foreach ($data['messages'] as [$role, $body, $minutesAgo]) {
                $createdAt = Carbon::now()->subMinutes($minutesAgo);
                $isBuyer = $role === 'buyer';

                Message::create([
                    'thread_id' => $thread->id,
                    'sender_type' => $role,
                    'sender_name' => $isBuyer ? $buyer->name : $shop->name,
                    'body' => $body,
                    'read_at' => $isBuyer ? $createdAt : ($data['unread'] > 0 ? null : $createdAt),
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }
        }
    }
}
