<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Article extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'category',
        'author',
        'excerpt',
        'body',
        'read_minutes',
        'published_at',
        'is_published',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_published' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function booted(): void
    {
        static::saving(function (Article $article) {
            if (blank($article->slug)) {
                $article->slug = Str::slug($article->title);
            }
        });
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->where(fn (Builder $sub) => $sub->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }
}
