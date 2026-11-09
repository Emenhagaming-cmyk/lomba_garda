<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ProductImageController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:60'],
        ]);

        try {
            $response = Http::timeout(8)
                ->acceptJson()
                ->withHeaders(['User-Agent' => 'TokoKu/1.0 (UMKM demo)'])
                ->get('https://api.openverse.org/v1/images/', [
                    'q' => $validated['q'],
                    'page_size' => 8,
                    'license_type' => 'commercial',
                    'size' => 'medium',
                ]);

            if (! $response->successful()) {
                return response()->json(['data' => ['images' => []]]);
            }

            $images = collect($response->json('results', []))
                ->map(fn (array $image) => [
                    'id' => $image['id'] ?? null,
                    'title' => $image['title'] ?: 'Tanpa judul',
                    'thumbnail' => $image['thumbnail'] ?? null,
                    'url' => $image['url'] ?? null,
                    'creator' => $image['creator'] ?? null,
                    'license' => isset($image['license']) ? strtoupper($image['license']) : null,
                    'license_url' => $image['license_url'] ?? null,
                    'attribution' => $image['attribution'] ?? null,
                ])
                ->filter(fn (array $image) => $image['thumbnail'] && $image['url'])
                ->values();

            return response()->json(['data' => ['images' => $images]]);
        } catch (\Throwable) {
            return response()->json(['data' => ['images' => []]]);
        }
    }
}
