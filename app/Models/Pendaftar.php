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

    public const PAYMENT_SELECT = [
        'cash'          => 'cash',
        'transfer bank' => 'transfer bank',
        'VA'            => 'VA',
        'Qris'          => 'Qris',
    ];

    public const STATUS_PAYMENT_SELECT = [
        'pending' => 'pending',
        'success' => 'success',
        'failed'  => 'failed',
        'refund'  => 'refund',
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
        'payment',
        'notes',
        'status_payment',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')->fit('crop', 50, 50);
        $this->addMediaConversion('preview')->fit('crop', 120, 120);
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
