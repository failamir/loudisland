<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    use HasFactory;

    protected $table = 'participants';

    protected $fillable = [
        'transaction_id',
        'participant_id',
        'name',
        'nik',
        'email',
        'phone',
        'province',
        'city',
        'shirt_size',
        'ticket_id',
        'status_racepack',
        'status',
        'staff_user_id',
        'racepack_by',
        'racepack_at',
        'amount',
    ];

    protected $casts = [
        'racepack_at' => 'datetime',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaksi::class, 'transaction_id');
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_user_id');
    }
}
