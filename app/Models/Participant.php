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
        'email',
        'phone',
        'ticket_id',
        'status_restpack',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaksi::class, 'transaction_id');
    }
}
