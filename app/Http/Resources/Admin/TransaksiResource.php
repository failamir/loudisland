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

        // Flatten promo code string for FE consumption
        if (!array_key_exists('promo_code', $data)) {
            $data['promo_code'] = optional($this->whenLoaded('promoCode'))->code ?? null;
        }

        return $data;
    }
}
