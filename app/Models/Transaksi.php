<?php

namespace App\Models;

use \DateTimeInterface;
use App\Traits\Auditable;
use App\Traits\MultiTenantModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Transaksi extends Model implements HasMedia
{
    use SoftDeletes;
    use MultiTenantModelTrait;
    use InteractsWithMedia;
    use Auditable;
    use HasFactory;

    public const STATUS_SELECT = [
        'pending' => 'pending',
        'success' => 'success',
        'Expired' => 'Expired',
        'failed'  => 'failed',
        'Refund'  => 'Refund',
    ];

    public $table = 'transactions';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'paired_at',
    ];

    protected $fillable = [
        'events',
        'invoice',
        'nomor_punggung',
        'event_id',
        'tiket_id',
        'peserta_id',
        'amount',
        'note',
        'snap_token',
        'status',
        'paired_at',
        'created_at',
        'updated_at',
        'deleted_at',
        'created_by_id',
        'nama',
        'qr',
        'province',
        'city',
        'address',
        'no_hp',
        'nik',
        'email',
        // 'uid',
        'participants',
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

    public function tiket()
    {
        return $this->belongsTo(Tiket::class, 'tiket_id');
    }

    public function peserta()
    {
        return $this->belongsTo(User::class, 'peserta_id');
    }

    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function participants()
    {
        return $this->hasMany(Participant::class, 'transaction_id');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
