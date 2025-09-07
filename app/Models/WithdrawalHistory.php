<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class WithdrawalHistory extends Model
{
    use HasFactory;

    protected $table = 'withdrawal_histories';

    protected $fillable = [
        'withdrawal_id',
        'action', // created, approved, paid, rejected, canceled
        'note',
        'meta',
        'acted_by_id',
        'amount_snapshot',
        'balance_before',
        'balance_after',
    ];

    protected $casts = [
        'meta' => 'array',
        'amount_snapshot' => 'integer',
        'balance_before' => 'integer',
        'balance_after' => 'integer',
    ];

    public function withdrawal()
    {
        return $this->belongsTo(Withdrawal::class);
    }

    public function acted_by()
    {
        return $this->belongsTo(User::class, 'acted_by_id');
    }
}
