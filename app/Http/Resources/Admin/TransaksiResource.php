<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Event;

class TransaksiResource extends JsonResource
{
    public function toArray($request)
    {
        $data = parent::toArray($request);

        // If 'event' relation is missing/null but legacy 'events' field exists,
        // try to resolve first event id and attach minimal event payload.
        if ((empty($data['event']) || $data['event'] === null) && !empty($this->events)) {
            $decoded = json_decode($this->events, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $maybe = @unserialize($this->events);
                $decoded = $maybe !== false ? $maybe : $this->events;
            }
            $eventIds = collect(is_array($decoded) ? $decoded : [$decoded])->filter()->values();
            $firstId = $eventIds->first();
            if ($firstId) {
                $ev = Event::find($firstId);
                if ($ev) {
                    $data['event'] = [
                        'id' => $ev->id,
                        'nama_event' => $ev->nama_event,
                        'event_code' => $ev->event_code,
                    ];
                }
            }
        }

        // Normalize: always expose promo_code and referral_code as simple strings
        // Even if relations are loaded and parent::toArray provided objects under these keys,
        // overwrite them with the relation's code value to keep FE stable.
        $promoRel = $this->whenLoaded('promoCode');
        $data['promo_code'] = $promoRel ? ($promoRel->code ?? null)
            : (is_string($data['promo_code'] ?? null) ? $data['promo_code'] : optional($this->promoCode)->code);

        $refRel = $this->whenLoaded('referralCode');
        $data['referral_code'] = $refRel ? ($refRel->code ?? null)
            : (is_string($data['referral_code'] ?? null) ? $data['referral_code'] : optional($this->referralCode)->code);

        return $data;
    }
}
