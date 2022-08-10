<?php

namespace App\Models;

use \DateTimeInterface;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Pendaftar extends Model implements HasMedia
{
    use SoftDeletes;
    use InteractsWithMedia;
    use Auditable;
    use HasFactory;

    public const CHECKIN_SELECT = [
        'sudah' => 'sudah',
        'belum' => 'belum',
    ];

    public const PAYMENT_TYPE_SELECT = [
        'Cash'     => 'Cash',
        'Transfer' => 'Transfer',
        'QRIS'     => 'QRIS',
    ];

    public const STATUS_PAYMENT_SELECT = [
        'Pending' => 'Pending',
        'Success' => 'Success',
        'Failed'  => 'Failed',
        'Refund'  => 'Refund',
    ];

    public $table = 'pendaftars';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'no_tiket',
        'nama',
        'nik',
        'email',
        'no_hp',
        'checkin',
        'notes',
        // 'status_payment',
        'event_id',
        'payment_type',
        'total_bayar',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')->fit('crop', 50, 50);
        $this->addMediaConversion('preview')->fit('crop', 120, 120);
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
