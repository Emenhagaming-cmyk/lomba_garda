<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ProductReviewSeeder extends Seeder
{
    public function run(): void
    {
        $reviews = [
            [
                'reviewer_name' => 'Rina Wulandari',
                'rating' => 5,
                'summary' => 'Kualitasnya di atas ekspektasi',
                'body' => 'Pesanan datang cepat, packing rapi, dan kondisinya masih fresh. Sudah repeat order dua kali.',
                'is_verified' => true,
                'days_ago' => 12,
            ],
            [
                'reviewer_name' => 'Dimas Prakoso',
                'rating' => 5,
                'summary' => 'Worth it untuk harga segini',
                'body' => 'Sudah coba beberapa kali dan kualitasnya konsisten. Penjual juga responsif saat ditanya.',
                'is_verified' => true,
                'days_ago' => 9,
            ],
            [
                'reviewer_name' => 'Siti Rahmawati',
                'rating' => 4,
                'summary' => 'Cocok untuk oleh-oleh',
                'body' => 'Beli untuk keluarga, semua suka. Aroma dan rasanya masih enak saat diterima.',
                'is_verified' => true,
                'days_ago' => 7,
            ],
            [
                'reviewer_name' => 'Andi Saputra',
                'rating' => 5,
                'summary' => 'Pengiriman rapi dan cepat',
                'body' => 'Dikemas aman, tidak ada yang rusak. Sesuai deskripsi di halaman produk.',
                'is_verified' => false,
                'days_ago' => 5,
            ],
            [
                'reviewer_name' => 'Maya Lestari',
                'rating' => 4,
                'summary' => 'Langganan sejak bulan lalu',
                'body' => 'Harga bersahabat dan kualitasnya stabil. Akan pesan lagi untuk stok toko.',
                'is_verified' => true,
                'days_ago' => 4,
            ],
            [
                'reviewer_name' => 'Fajar Nugroho',
                'rating' => 4,
                'summary' => 'Lumayan, sesuai harga',
                'body' => 'Rasa dan kualitas oke. Pengiriman butuh sehari lebih lama dari perkiraan.',
                'is_verified' => true,
                'days_ago' => 2,
            ],
        ];

        Product::query()
            ->where('is_showcased', true)
            ->get()
            ->each(function (Product $product) use ($reviews): void {
                $offset = $product->id % count($reviews);

                for ($position = 0; $position < 3; $position++) {
                    $review = $reviews[($offset + $position) % count($reviews)];

                    ProductReview::firstOrCreate(
                        [
                            'product_id' => $product->id,
                            'reviewer_name' => $review['reviewer_name'],
                            'summary' => $review['summary'],
                        ],
                        [
                            'rating' => $review['rating'],
                            'body' => $review['body'],
                            'is_verified' => $review['is_verified'],
                            'reviewed_at' => Carbon::now()->subDays($review['days_ago'] + $position),
                        ],
                    );
                }
            });
    }
}
