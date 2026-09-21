<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lead extends Model
{
    use HasFactory;

    public const STAGES = ['new', 'contacted', 'qualified', 'offer', 'converted'];

    protected $fillable = [
        'business_id',
        'name',
        'phone',
        'email',
        'source',
        'stage',
        'notes',
        'next_follow_up_at',
        'converted_customer_id',
    ];

    protected $casts = [
        'next_follow_up_at' => 'datetime',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function convertedCustomer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'converted_customer_id');
    }
}
