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
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *   schema="Pendaftar",
 *   required={"nama","email","no_hp","event_id"},
 *   @OA\Property(property="id", type="integer", readOnly=true, example=1),
 *   @OA\Property(property="no_tiket", type="string", example="012345"),
 *   @OA\Property(property="nama", type="string", example="Budi"),
 *   @OA\Property(property="nik", type="string", nullable=true, example="3201010101010001"),
 *   @OA\Property(property="email", type="string", format="email", example="budi@example.com"),
 *   @OA\Property(property="no_hp", type="string", example="08123456789"),
 *   @OA\Property(property="checkin", type="string", nullable=true, example="belum"),
 *   @OA\Property(property="notes", type="string", nullable=true),
 *   @OA\Property(property="event_id", type="integer", example=1),
 *   @OA\Property(property="payment_type", type="string", nullable=true, example="QRIS"),
 *   @OA\Property(property="total_bayar", type="number", format="float", example=200000),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
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
        'pending' => 'pending',
        'success' => 'success',
        'failed'  => 'failed',
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
        'province',
        'city',
        'address',
        'checkin',
        'notes',
        'nomor_punggung',
        'status_payment',
        'event_id',
        'payment_type',
        'total_bayar',
        'start_at',
        'finish_at',
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
