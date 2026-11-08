<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shop extends Model
{
    protected $fillable = [
        'business_id',
        'name',
        'city',
        'address',
        'phone',
        'open_hours',
        'status',
        'rating',
        'reviews_count',
        'distance_km',
        'map_x',
        'map_y',
        'supports_shipping',
        'supports_cod',
        'reopens_on',
        'is_active',
    ];

    protected $casts = [
        'rating' => 'float',
        'distance_km' => 'float',
        'supports_shipping' => 'boolean',
        'supports_cod' => 'boolean',
        'is_active' => 'boolean',
        'reopens_on' => 'date',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ShopItem::class)->orderBy('price');
    }

    public function threads(): HasMany
    {
        return $this->hasMany(Thread::class);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'open');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
