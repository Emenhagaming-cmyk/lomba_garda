<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_ORDERED = 'ordered';

    public const STATUS_PARTIALLY_RECEIVED = 'partially_received';

    public const STATUS_RECEIVED = 'received';

    protected $fillable = [
        'business_id',
        'supplier_id',
        'po_number',
        'status',
        'order_date',
        'received_at',
        'total',
        'note',
    ];

    protected $casts = [
        'order_date' => 'date',
        'received_at' => 'datetime',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }
}
