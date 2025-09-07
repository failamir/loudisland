<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class Withdrawal extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'withdrawals';

    protected $fillable = [
        'amount',
        'bank',
        'account_name',
        'account_number',
        'note',
        'status', // queued, approved, paid, rejected, canceled
        'created_by_id',
    ];

    protected $casts = [
        'amount' => 'integer',
    ];

    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function histories()
    {
        return $this->hasMany(WithdrawalHistory::class);
    }
}
