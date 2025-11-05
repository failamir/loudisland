<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use App\Models\ReferralCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class PromoCodeApplyController extends Controller
{
    protected function findByCode(string $code): ?PromoCode
    {
        return PromoCode::whereRaw('LOWER(code) = ?', [strtolower(trim($code))])->first();
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

    public function validateCode(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:100'],
            'base_amount' => ['required', 'numeric', 'min:0'],
            'quantity' => ['nullable', 'integer', 'min:1'],
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
        $baseAmount = (float) $data['base_amount'];
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
            'base_amount' => ['required', 'numeric', 'min:0'],
            'order_id' => ['nullable', 'integer'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        return DB::transaction(function () use ($data) {
            $promo = $this->findByCode($data['code']);
            if (!$promo) {
                // Not a promo? Try referral code in the same endpoint
                $refCode = strtoupper(trim($data['code']));
                $ref = ReferralCode::whereRaw('UPPER(code) = ?', [$refCode])->first();
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

                // Referral does not change price; FE should still pass this code to order
                $baseAmount = (float) $data['base_amount'];
                return response()->json([
                    'status' => 'ok',
                    'type' => 'referral',
                    'promo_code_id' => $ref->code,
                    'discount_value' => 0,
                    'final_amount' => $baseAmount,
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
            $baseAmount = (float) $data['base_amount'];
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
