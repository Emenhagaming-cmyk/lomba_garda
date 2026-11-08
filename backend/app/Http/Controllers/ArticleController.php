<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Article::query()->published();

        if ($request->filled('category') && $request->string('category')->toString() !== 'Semua') {
            $query->where('category', $request->string('category'));
        }

        if ($request->filled('search')) {
            $search = '%'.$request->string('search')->trim().'%';
            $query->where(fn ($sub) => $sub->where('title', 'like', $search)->orWhere('excerpt', 'like', $search));
        }

        $articles = $query->orderByDesc('published_at')->orderByDesc('id')->get();

        return response()->json([
            'data' => [
                'articles' => $articles->map(fn (Article $article) => $this->serialize($article, false)),
                'categories' => Article::query()->published()->distinct()->orderBy('category')->pluck('category'),
            ],
        ]);
    }

    public function show(Article $article): JsonResponse
    {
        abort_unless($article->is_published, 404);

        $related = Article::query()
            ->published()
            ->where('id', '!=', $article->id)
            ->where('category', $article->category)
            ->orderByDesc('published_at')
            ->limit(2)
            ->get();

        return response()->json([
            'data' => [
                'article' => $this->serialize($article, true),
                'related' => $related->map(fn (Article $item) => $this->serialize($item, false)),
            ],
        ]);
    }

    private function serialize(Article $article, bool $withBody): array
    {
        $payload = [
            'id' => $article->id,
            'slug' => $article->slug,
            'title' => $article->title,
            'category' => $article->category,
            'author' => $article->author,
            'excerpt' => $article->excerpt,
            'read_minutes' => $article->read_minutes,
            'published_at' => $article->published_at?->toIso8601String(),
        ];

        if ($withBody) {
            $payload['body'] = $article->body;
        }

        return $payload;
    }
}
