<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferralWithdrawal extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'bank',
        'account_name',
        'account_number',
        'status',
        'note',
    ];

    protected $casts = [
        'amount' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
