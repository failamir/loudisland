<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\PromoCode;
use App\Models\ReferralCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class PromoCodeApplyController extends Controller
{
    protected function findByCode(string $code): ?PromoCode
    {
        $input = strtoupper(trim($code));
        $exact = PromoCode::whereRaw('UPPER(code) = ?', [$input])->first();
        if ($exact) {
            return $exact;
        }
        // Fallback: body-only (match suffix after dash)
        $body = preg_replace('/[^A-Z0-9]/', '', $input);
        if ($body === '') {
            return null;
        }
        return PromoCode::whereRaw('UPPER(code) LIKE ?', ['%-' . $body])->first();
    }

    protected function computeDiscount(PromoCode $promo, float $baseAmount): array
    {
        $discountValue = 0.0;
        if ($promo->discount_type === 'percent') {
            $discountValue = round($baseAmount * ($promo->amount / 100), 2);
        } else {
            $discountValue = min($baseAmount, (float) $promo->amount);
        }
        $finalAmount = max(0, $baseAmount - $discountValue);
        return [$discountValue, $finalAmount];
    }

    protected function isUsable(PromoCode $promo): array
    {
        if (!$promo->active) {
            return [false, 'inactive'];
        }
        if ($promo->expires_at && now()->greaterThan($promo->expires_at)) {
            return [false, 'expired'];
        }
        if (!is_null($promo->usage_limit) && $promo->used_count >= $promo->usage_limit) {
            return [false, 'usage_limit_reached'];
        }
        return [true, null];
    }

    protected function ticketsAllowedForCode(?array $allowed, array $ticketIds): bool
    {
        if (empty($ticketIds)) {
            return true;
        }
        if (!is_array($allowed) || empty($allowed)) {
            return true;
        }
        $allowedInt = array_map('intval', $allowed);
        foreach ($ticketIds as $tid) {
            if (!in_array((int) $tid, $allowedInt, true)) {
                return false;
            }
        }
        return true;
    }

    public function validateCode(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:100'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'ticket_ids' => ['required', 'array', 'min:1'],
            'ticket_ids.*' => ['integer', 'exists:events,id'],
        ]);

        $promo = $this->findByCode($data['code']);
        if (!$promo) {
            return response()->json([
                'valid' => false,
                'message' => 'Maaf, kode promo yang Anda masukkan tidak ditemukan.',
            ], 404);
        }
        [$ok, $reason] = $this->isUsable($promo);
        if (!$ok) {
            $message = $reason === 'inactive'
                ? 'Maaf, kode promo ini belum dapat digunakan saat ini.'
                : ($reason === 'expired'
                    ? ("Masa berlaku kode '" . $promo->code . "' telah habis.")
                    : ($reason === 'usage_limit_reached'
                        ? 'Maaf, kuota penggunaan kode promo ini telah habis.'
                        : $reason));
            return response()->json([
                'valid' => false,
                'message' => $message,
                'discount_type' => $promo->discount_type,
                'amount' => (float) $promo->amount,
                'expires_at' => optional($promo->expires_at)->toDateTimeString(),
                'usage_left' => is_null($promo->usage_limit) ? null : max(0, $promo->usage_limit - $promo->used_count),
                'tnc' => $promo->tnc,
            ]);
        }

        // Additional business rules
        // Determine base amount from server-side ticket prices (sum of selected tickets)
        $ticketIds = array_map('intval', $data['ticket_ids']);
        $events = Event::select(['id', 'harga'])->whereIn('id', $ticketIds)->get();
        if ($events->isEmpty()) {
            return response()->json([
                'valid' => false,
                'message' => 'Tiket tidak ditemukan.',
            ], 422);
        }
        // Sum price per occurrence (handle duplicates in ticket_ids)
        $priceMap = $events->keyBy('id')->map(function ($e) {
            return (float) ($e->harga ?? 0);
        });
        $baseAmount = 0.0;
        foreach ($ticketIds as $tid) {
            $baseAmount += (float) ($priceMap[$tid] ?? 0);
        }
        if ($baseAmount <= 0) {
            return response()->json([
                'valid' => false,
                'message' => 'Harga tiket tidak valid.',
            ], 422);
        }
        $qty = (int) ($data['quantity'] ?? 1);
        // min_purchase and max_purchase are interpreted as ticket count limits
        if (!is_null($promo->min_purchase) && $qty < (int) $promo->min_purchase) {
            return response()->json([
                'valid' => false,
                'message' => 'Promo ini berlaku untuk pembelian minimal ' . (int) $promo->min_purchase . ' tiket.',
                'required_min_purchase' => (int) $promo->min_purchase,
                'discount_type' => $promo->discount_type,
                'amount' => (float) $promo->amount,
                'expires_at' => optional($promo->expires_at)->toDateTimeString(),
                'usage_left' => is_null($promo->usage_limit) ? null : max(0, $promo->usage_limit - $promo->used_count),
                'tnc' => $promo->tnc,
            ]);
        }
        if (!is_null($promo->max_purchase) && $qty > (int) $promo->max_purchase) {
            return response()->json([
                'valid' => false,
                'message' => 'Promo ini tidak berlaku untuk pembelian lebih dari ' . $promo->max_purchase . ' tiket.',
                'max_purchase' => (int) $promo->max_purchase,
                'discount_type' => $promo->discount_type,
                'amount' => (float) $promo->amount,
                'expires_at' => optional($promo->expires_at)->toDateTimeString(),
                'usage_left' => is_null($promo->usage_limit) ? null : max(0, $promo->usage_limit - $promo->used_count),
                'tnc' => $promo->tnc,
            ]);
        }

        // ticket ids already provided by client
        $allowed = is_array($promo->metadata) && array_key_exists('allowed_events', $promo->metadata) ? ($promo->metadata['allowed_events'] ?? []) : [];
        if (!$this->ticketsAllowedForCode($allowed, $ticketIds)) {
            return response()->json([
                'valid' => false,
                'message' => 'Promo ini hanya berlaku untuk tiket tertentu.',
                'discount_type' => $promo->discount_type,
                'amount' => (float) $promo->amount,
                'expires_at' => optional($promo->expires_at)->toDateTimeString(),
                'usage_left' => is_null($promo->usage_limit) ? null : max(0, $promo->usage_limit - $promo->used_count),
                'tnc' => $promo->tnc,
            ]);
        }

        [$discountValue, $finalAmount] = $this->computeDiscount($promo, $baseAmount);

        return response()->json([
            'valid' => true,
            'message' => null,
            'discount_type' => $promo->discount_type,
            'amount' => (float) $promo->amount,
            'discount_value' => $discountValue,
            'final_amount' => $finalAmount,
            'expires_at' => optional($promo->expires_at)->toDateTimeString(),
            'usage_left' => is_null($promo->usage_limit) ? null : max(0, $promo->usage_limit - $promo->used_count),
            'tnc' => $promo->tnc,
        ]);
    }

    public function redeem(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:100'],
            'ticket_ids' => ['required', 'array', 'min:1'],
            'ticket_ids.*' => ['integer', 'exists:events,id'],
            'order_id' => ['nullable', 'integer'],
            'quantity' => ['nullable', 'integer', 'min:0'],
        ]);

        return DB::transaction(function () use ($data) {
            $promo = $this->findByCode($data['code']);
            if (!$promo) {
                // Not a promo? Try referral code in the same endpoint
                $refCode = strtoupper(trim($data['code']));
                $ref = ReferralCode::whereRaw('UPPER(code) = ?', [$refCode])->first();
                if (!$ref) {
                    // Fallback: body-only suffix match
                    $body = preg_replace('/[^A-Z0-9]/', '', $refCode);
                    if ($body !== '') {
                        $ref = ReferralCode::whereRaw('UPPER(code) LIKE ?', ['%-' . $body])->first();
                    }
                }
                if (!$ref) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Maaf, kode yang Anda masukkan tidak ditemukan.',
                    ], 404);
                }

                $now = now();
                $inWindow = (!$ref->valid_from || $now->gte($ref->valid_from)) && (!$ref->valid_to || $now->lte($ref->valid_to));
                $underLimit = (is_null($ref->usage_limit) || (int)$ref->used_count < (int)$ref->usage_limit);
                if (!$ref->active || !$inWindow || !$underLimit) {
                    return response()->json([
                        'status' => 'error',
                        'message' => !$ref->active ? 'Kode referal tidak aktif.' : (!$inWindow ? 'Masa berlaku kode referal telah habis atau belum dimulai.' : 'Kuota pemakaian kode referal telah habis.'),
                    ], 422);
                }

                // ticket ids provided by client
                $ticketIds = array_map('intval', $data['ticket_ids']);
                $allowed = is_array($ref->metadata) && array_key_exists('allowed_events', $ref->metadata) ? ($ref->metadata['allowed_events'] ?? []) : [];
                // Default rule: referral only valid for ticket id 2 if not explicitly configured
                if (!is_array($allowed) || empty($allowed)) {
                    $allowed = [2];
                }
                if (!$this->ticketsAllowedForCode($allowed, $ticketIds)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Kode referal hanya berlaku untuk tiket UMUM.',
                    ], 422);
                }

                // Referral applies fixed discount per qualifying ticket (default 25,000)
                // Determine base amount from server-side event prices (sum of selected tickets)
                $events = Event::select(['id', 'harga'])->whereIn('id', $ticketIds)->get();
                // Sum price per occurrence (handle duplicates in ticket_ids)
                $priceMap = $events->keyBy('id')->map(function ($e) {
                    return (float) ($e->harga ?? 0);
                });
                $baseAmount = 0.0;
                foreach ($ticketIds as $tid) {
                    $baseAmount += (float) ($priceMap[$tid] ?? 0);
                }
                $refDiscount = 25000;
                try {
                    if (is_array($ref->metadata) && isset($ref->metadata['referral_discount'])) {
                        $refDiscount = (int) $ref->metadata['referral_discount'];
                    }
                } catch (\Throwable $_) {
                }
                // count qualifying tickets (id == 2)
                $qualifyingCount = 0;
                foreach ($ticketIds as $tid) {
                    if ((int) $tid === 2) {
                        $qualifyingCount++;
                    }
                }
                $discountValue = max(0, $refDiscount * $qualifyingCount);
                $finalAmount = max(0, $baseAmount - $discountValue);
                return response()->json([
                    'status' => 'ok',
                    'type' => 'referral',
                    'promo_code_id' => $ref->id,
                    'discount_value' => $discountValue,
                    'final_amount' => $finalAmount,
                    'eligible_ticket_ids' => [2]
                ]);
            }
            [$ok, $reason] = $this->isUsable($promo);
            if (!$ok) {
                $message = $reason === 'inactive'
                    ? 'Maaf, kode promo ini belum dapat digunakan saat ini.'
                    : ($reason === 'expired'
                        ? ("Masa berlaku kode '" . $promo->code . "' telah habis.")
                        : ($reason === 'usage_limit_reached'
                            ? 'Maaf, kuota penggunaan kode promo ini telah habis.'
                            : $reason));
                return response()->json([
                    'status' => 'error',
                    'message' => $message,
                ], 422);
            }
            // Determine base amount from server-side event prices (sum of selected tickets)
            $ticketIds = array_map('intval', $data['ticket_ids']);
            $events = Event::select(['id', 'harga'])->whereIn('id', $ticketIds)->get();
            // Sum price per occurrence (handle duplicates in ticket_ids)
            $priceMap = $events->keyBy('id')->map(function ($e) {
                return (float) ($e->harga ?? 0);
            });
            $baseAmount = 0.0;
            foreach ($ticketIds as $tid) {
                $baseAmount += (float) ($priceMap[$tid] ?? 0);
            }
            $qty = (int) ($data['quantity'] ?? 1);
            // min_purchase and max_purchase are interpreted as ticket count limits
            if (!is_null($promo->min_purchase) && $qty < (int) $promo->min_purchase) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Promo ini berlaku untuk pembelian minimal ' . (int) $promo->min_purchase . ' tiket.',
                    'required_min_purchase' => (int) $promo->min_purchase,
                ], 422);
            }
            if (!is_null($promo->max_purchase) && $qty > (int) $promo->max_purchase) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Promo ini tidak berlaku untuk pembelian lebih dari ' . $promo->max_purchase . ' tiket.',
                    'max_purchase' => (int) $promo->max_purchase,
                ], 422);
            }

            // ticket ids already provided by client
            $allowed = is_array($promo->metadata) && array_key_exists('allowed_events', $promo->metadata) ? ($promo->metadata['allowed_events'] ?? []) : [];
            if (!$this->ticketsAllowedForCode($allowed, $ticketIds)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Promo ini hanya berlaku untuk tiket tertentu.',
                ], 422);
            }

            [$discountValue, $finalAmount] = $this->computeDiscount($promo, $baseAmount);

            return response()->json([
                'status' => 'ok',
                'type' => 'promo',
                'promo_code_id' => $promo->id,
                'discount_value' => $discountValue,
                'final_amount' => $finalAmount,
            ]);
        });
    }
}
