<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;
    /**
     * fillable
     *
     * @var array
     */
    protected $table = 'transaksi';
    
    protected $fillable = [
        'invoice', 'event_id', 'pendaftar_id', 'amount', 'note', 'status', 'snap_token'
    ];

    /**
     * campaign
     *
     * @return void
     */
    // public function campaign()
    // {
    //     return $this->belongsTo(Campaign::class);
    // }

    /**
     * donatur
     *
     * @return void
     */
    // public function donatur()
    // {
    //     return $this->belongsTo(Donatur::class);
    // }

    /**
     * getCreatedAtAttribute
     *
     * @param  mixed $date
     * @return void
     */
    public function getCreatedAtAttribute($date)
    {   
        return Carbon::parse($date)->format('d-M-Y');
    }
    
    /**
     * getUpdatedAtAttribute
     *
     * @param  mixed $date
     * @return void
     */
    public function getUpdatedAtAttribute($date)
    {   
        return Carbon::parse($date)->format('d-M-Y');
    }
}
