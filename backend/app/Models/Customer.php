<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Customer extends Model
{
    use HasFactory;

    public const VIP_SPENDING = 1_500_000;

    public const AT_RISK_DAYS = 45;

    public const INACTIVE_DAYS = 90;

    public const LOYAL_ORDERS = 3;

    protected $fillable = [
        'business_id',
        'name',
        'phone',
        'email',
        'address',
        'notes',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function segment(): string
    {
        $orders = $this->sales()->count();

        return static::segmentFor(
            (int) $this->sales()->sum('total'),
            $orders,
            $this->sales()->latest()->value('created_at'),
        );
    }

    public static function segmentFor(int $spending, int $orders, ?string $lastPurchase): string
    {
        if ($orders === 0) {
            return 'new';
        }

        $days = $lastPurchase ? Carbon::parse($lastPurchase)->diffInDays() : null;

        if ($spending >= static::VIP_SPENDING) {
            return 'vip';
        }

        if ($days !== null && $days > static::INACTIVE_DAYS) {
            return 'inactive';
        }

        if ($days !== null && $days > static::AT_RISK_DAYS) {
            return 'at_risk';
        }

        if ($orders >= static::LOYAL_ORDERS) {
            return 'loyal';
        }

        return 'new';
    }
}
