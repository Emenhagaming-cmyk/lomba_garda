<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\JsonResponse;

class ShowcaseController extends Controller
{
    public function index(): JsonResponse
    {
        $products = Product::query()
            ->with('business:id,name,type,address')
            ->withCount('reviews as review_count')
            ->withAvg('reviews as rating_avg', 'rating')
            ->where('is_showcased', true)
            ->where('is_active', true)
            ->whereHas('business')
            ->orderByDesc('updated_at')
            ->limit(12)
            ->get()
            ->map(fn (Product $product) => $this->serializeProduct($product));

        return response()->json(['data' => ['products' => $products]]);
    }

    public function show(Product $product): JsonResponse
    {
        abort_unless($product->is_showcased && $product->is_active, 404);

        $product->load([
            'business:id,name,type,address',
            'reviews' => fn ($query) => $query->orderByDesc('reviewed_at')->limit(5),
        ]);

        $distribution = ProductReview::query()
            ->where('product_id', $product->id)
            ->selectRaw('rating, COUNT(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating');

        $total = (int) $distribution->sum();
        $average = $total > 0 ? round((float) $distribution->map(fn ($count, $rating) => $rating * $count)->sum() / $total, 1) : null;

        $rows = collect(range(5, 1))->map(fn (int $star) => [
            'star' => $star,
            'count' => (int) ($distribution->get($star) ?? 0),
            'percent' => $total > 0 ? (int) round(((int) ($distribution->get($star) ?? 0)) / $total * 100) : 0,
        ]);

        $product->rating_avg = $average;
        $product->review_count = $total;

        return response()->json([
            'data' => [
                'product' => $this->serializeProduct($product) + [
                    'reviews' => $product->reviews->map(fn (ProductReview $review) => [
                        'id' => $review->id,
                        'reviewer_name' => $review->reviewer_name,
                        'rating' => $review->rating,
                        'summary' => $review->summary,
                        'body' => $review->body,
                        'is_verified' => $review->is_verified,
                        'reviewed_at' => $review->reviewed_at?->toIso8601String(),
                    ])->values(),
                ],
                'review_summary' => [
                    'average' => $average,
                    'total' => $total,
                    'distribution' => $rows,
                ],
            ],
        ]);
    }

    private function serializeProduct(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'category' => $product->category,
            'description' => $product->description,
            'image_url' => $product->image_url,
            'image_credit' => $product->image_credit,
            'sell_price' => $product->sell_price,
            'unit' => $product->unit,
            'rating_avg' => $product->rating_avg !== null ? round((float) $product->rating_avg, 1) : null,
            'review_count' => (int) ($product->review_count ?? 0),
            'business' => [
                'id' => $product->business->id,
                'name' => $product->business->name,
                'type' => $product->business->type,
                'address' => $product->business->address,
            ],
        ];
    }
}
