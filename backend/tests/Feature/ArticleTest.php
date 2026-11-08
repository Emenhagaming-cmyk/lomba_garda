<?php

namespace Tests\Feature;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_articles_list_only_published(): void
    {
        Article::create([
            'title' => 'Tips Stok Sehat',
            'category' => 'Operasional',
            'excerpt' => 'Ringkasan tips.',
            'body' => "## Isi\nTeks.",
            'published_at' => now()->subDay(),
            'is_published' => true,
        ]);

        Article::create([
            'title' => 'Draf Belum Tayang',
            'category' => 'Operasional',
            'excerpt' => 'Draf.',
            'body' => 'Draf.',
            'is_published' => false,
        ]);

        $response = $this->getJson('/api/articles');

        $response->assertOk()
            ->assertJsonCount(1, 'data.articles')
            ->assertJsonPath('data.articles.0.slug', 'tips-stok-sehat')
            ->assertJsonPath('data.categories.0', 'Operasional')
            ->assertJsonMissingPath('data.articles.0.body');
    }

    public function test_article_detail_is_accessible_by_slug_and_hides_drafts(): void
    {
        $article = Article::create([
            'title' => 'Cara Menentukan Harga',
            'category' => 'Keuangan',
            'excerpt' => 'Ringkas.',
            'body' => 'Isi lengkap.',
            'published_at' => now()->subDay(),
            'is_published' => true,
        ]);

        $draft = Article::create([
            'title' => 'Draf Berita',
            'category' => 'Keuangan',
            'excerpt' => 'Draf.',
            'body' => 'Draf.',
            'is_published' => false,
        ]);

        $this->getJson("/api/articles/{$article->slug}")
            ->assertOk()
            ->assertJsonPath('data.article.body', 'Isi lengkap.');

        $this->getJson("/api/articles/{$draft->slug}")->assertNotFound();
    }
}
