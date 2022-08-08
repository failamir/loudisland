<?php

namespace App\Models;

use \DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Tiket extends Model implements HasMedia
{
    use SoftDeletes;
    use InteractsWithMedia;
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

    public $table = 'tikets';

    protected $appends = [
        'qr',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'no_tiket',
        'peserta_id',
        'checkin',
        'notes',
        'status_payment',
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

    public function tiketTransaksis()
    {
        return $this->hasMany(Transaksi::class, 'tiket_id', 'id');
    }

    public function peserta()
    {
        return $this->belongsTo(User::class, 'peserta_id');
    }

    public function getQrAttribute()
    {
        $file = $this->getMedia('qr')->last();
        if ($file) {
            $file->url       = $file->getUrl();
            $file->thumbnail = $file->getUrl('thumb');
            $file->preview   = $file->getUrl('preview');
        }

        return $file;
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
