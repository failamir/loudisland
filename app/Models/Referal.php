<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Referal extends Model
{
    use HasFactory;

    protected $table = 'referal';

    protected $fillable = [
        'user_id_referral',
        'kode',
        'value',
        'tanggal',
        'email_pemesan',
    ];
}
