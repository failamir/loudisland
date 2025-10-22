<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'discount_type', // 'percent' or 'fixed'
        'amount',
        'expires_at',
        'usage_limit',
        'used_count',
        'active',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'float',
        'expires_at' => 'datetime',
        'usage_limit' => 'integer',
        'used_count' => 'integer',
        'active' => 'boolean',
        'metadata' => 'array',
    ];
}
