<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class ShowcaseDemoSeeder extends Seeder
{
    public function run(): void
    {
        $images = [
            'Kopi Arabica Gayo 250g' => ['/products/kopi-arabica-gayo.jpg', 'Foto: Umer Shabib · CC BY · Coffee Beans'],
            'Cold Brew Botol 1L' => ['/products/cold-brew.jpg', 'Foto: shalommama · CC BY · cold brew coffee'],
            'Paket Manual Brew' => ['/products/manual-brew.jpg', 'Foto: nan palmero · CC BY'],
            'Keripik Tempe Premium' => ['/products/keripik-tempe.jpg', 'Foto: moria · CC BY · Tricolore Tempeh'],
            'Kacang Mede Sangrai' => ['/products/kacang-mede.jpg', 'Foto: Bernt Rostad · CC BY · Cashew nuts'],
            'Oleh-oleh Paket Hemat' => ['/products/oleh-oleh.jpg', 'Foto: ann_espino · CC BY · Indonesian snacks'],
            'Roti Sobek Cokelat' => ['/products/roti-sobek.jpg', 'Foto: stu_spivack · CC BY-SA · chocolate brioche'],
            'Croissant Butter' => ['/products/croissant.jpg', 'Foto: The Marmot · CC BY · Butter Croissants'],
            'Kue Kering Nastar' => ['/products/nastar.jpg', 'Foto: avlxyz · CC BY-SA · Pineapple Tarts'],
            'Kopi Arabica Gayo' => ['/products/kopi-arabica-gayo.jpg', 'Foto: Umer Shabib · CC BY · Coffee Beans'],
            'Cappuccino' => ['/products/cappuccino.jpg', 'Foto: Chic Bee · CC BY'],
            'Matcha Latte' => ['/products/matcha-latte.jpg', 'Foto: Kirinohana · CC BY · Matcha Latte'],
            'Croissant' => ['/products/croissant.jpg', 'Foto: The Marmot · CC BY · Butter Croissants'],
            'Roti Bakar Coklat' => ['/products/roti-bakar.jpg', 'Foto: Daniel. · CC BY · Kaya Toast with Coffee'],
            'Nasi Goreng Special' => ['/products/nasi-goreng.jpg', 'Foto: BPPrice · CC BY · Nasi Goreng'],
        ];

        $stores = [
            [
                'user' => ['name' => 'Nadia Kopi', 'email' => 'nadia@tokoku.app'],
                'business' => [
                    'name' => 'Gayo Roastery',
                    'type' => 'fnb',
                    'phone' => '081211112222',
                    'address' => 'Jl. Kaliurang No. 45, Yogyakarta',
                ],
                'products' => [
                    ['Kopi Arabica Gayo 250g', 'Kopi', 68000, 'Biji kopi single origin Gayo, medium roast, notes cokelat & jeruk.'],
                    ['Cold Brew Botol 1L', 'Minuman', 45000, 'Cold brew siap minum, diseduh 18 jam, tanpa gula.'],
                    ['Paket Manual Brew', 'Paket', 125000, 'V60 + filter + 250g kopi Gayo untuk pemula.'],
                ],
            ],
            [
                'user' => ['name' => 'Bagas Snack', 'email' => 'bagas@tokoku.app'],
                'business' => [
                    'name' => 'Snack Nusantara',
                    'type' => 'retail',
                    'phone' => '081233334444',
                    'address' => 'Jl. Malioboro No. 88, Yogyakarta',
                ],
                'products' => [
                    ['Keripik Tempe Premium', 'Snack', 18000, 'Keripik tempe renyah, tanpa pengawet, kemasan 200g.'],
                    ['Kacang Mede Sangrai', 'Snack', 42000, 'Kacang mede sangrai original, kemasan 250g.'],
                    ['Oleh-oleh Paket Hemat', 'Paket', 95000, 'Isi 5 snack khas Jogja, cocok untuk oleh-oleh.'],
                ],
            ],
            [
                'user' => ['name' => 'Sari Bakery', 'email' => 'sari@tokoku.app'],
                'business' => [
                    'name' => 'Bakery Harapan',
                    'type' => 'fnb',
                    'phone' => '081255556666',
                    'address' => 'Jl. Gejayan No. 12, Yogyakarta',
                ],
                'products' => [
                    ['Roti Sobek Cokelat', 'Food', 25000, 'Roti sobek lembut isi cokelat, fresh from oven tiap pagi.'],
                    ['Croissant Butter', 'Food', 18000, 'Croissant butter Prancis, 27 layer, renyah di luar.'],
                    ['Kue Kering Nastar', 'Food', 65000, 'Nastar homemade, 500g, isi selai nanas asli.'],
                ],
            ],
        ];

        foreach ($stores as $store) {
            $user = User::firstOrCreate(
                ['email' => $store['user']['email']],
                [
                    'name' => $store['user']['name'],
                    'password' => 'Rahasia123!',
                    'role' => 'OWNER',
                ],
            );

            $business = Business::firstOrCreate(
                ['user_id' => $user->id],
                $store['business'] + ['currency' => 'IDR'],
            );

            foreach ($store['products'] as $index => [$name, $category, $sellPrice, $description]) {
                $product = Product::firstOrCreate(
                    ['business_id' => $business->id, 'name' => $name],
                    [
                        'sku' => 'SC-'.str_pad((string) ($business->id * 10 + $index), 4, '0', STR_PAD_LEFT),
                        'category' => $category,
                        'description' => $description,
                        'unit' => 'pcs',
                        'buy_price' => (int) round($sellPrice * 0.6),
                        'sell_price' => $sellPrice,
                        'hpp' => (int) round($sellPrice * 0.6),
                        'min_stock' => 5,
                        'stock' => 40,
                        'is_active' => true,
                        'is_showcased' => true,
                    ],
                );

                $this->attachImage($product, $images[$name] ?? null);
            }
        }

        $demoBusiness = Business::where('name', 'Kopi Tertial')->first();

        if ($demoBusiness === null) {
            return;
        }

        $showcaseCopy = [
            'Kopi Arabica Gayo' => 'Single origin Gayo, medium roast, cocok untuk manual brew.',
            'Cappuccino' => 'Espresso + susu creamy, favorit pelanggan setia.',
            'Matcha Latte' => 'Matcha premium dengan susu segar, manisnya pas.',
            'Croissant' => 'Croissant butter renyah, dipanggang tiap pagi.',
            'Roti Bakar Coklat' => 'Roti bakar klasik isi cokelat melimpah.',
            'Nasi Goreng Special' => 'Nasi goreng kampung dengan telur dan ayam.',
        ];

        foreach ($showcaseCopy as $name => $description) {
            $product = Product::where('business_id', $demoBusiness->id)
                ->where('name', $name)
                ->first();

            if ($product === null) {
                continue;
            }

            $product->update(['is_showcased' => true, 'description' => $description]);
            $this->attachImage($product, $images[$name] ?? null);
        }
    }

    private function attachImage(Product $product, ?array $image): void
    {
        if ($image === null || $product->image_url !== null) {
            return;
        }

        $product->update([
            'image_url' => $image[0],
            'image_credit' => $image[1],
        ]);
    }
}
