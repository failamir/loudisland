<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
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
        ]);

        $promo = $this->findByCode($data['code']);
        if (!$promo) {
            return response()->json([
                'valid' => false,
                'reason' => 'not_found',
            ], 404);
        }
        [$ok, $reason] = $this->isUsable($promo);
        if (!$ok) {
            return response()->json([
                'valid' => false,
                'reason' => $reason,
                'discount_type' => $promo->discount_type,
                'amount' => (float) $promo->amount,
                'expires_at' => optional($promo->expires_at)->toDateTimeString(),
                'usage_left' => is_null($promo->usage_limit) ? null : max(0, $promo->usage_limit - $promo->used_count),
            ]);
        }

        [$discountValue, $finalAmount] = $this->computeDiscount($promo, (float) $data['base_amount']);

        return response()->json([
            'valid' => true,
            'reason' => null,
            'discount_type' => $promo->discount_type,
            'amount' => (float) $promo->amount,
            'discount_value' => $discountValue,
            'final_amount' => $finalAmount,
            'expires_at' => optional($promo->expires_at)->toDateTimeString(),
            'usage_left' => is_null($promo->usage_limit) ? null : max(0, $promo->usage_limit - $promo->used_count),
        ]);
    }

    public function redeem(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:100'],
            'base_amount' => ['required', 'numeric', 'min:0'],
            'order_id' => ['nullable', 'integer'],
        ]);

        return DB::transaction(function () use ($data) {
            $promo = $this->findByCode($data['code']);
            if (!$promo) {
                return response()->json([
                    'status' => 'error',
                    'reason' => 'not_found',
                ], 404);
            }
            [$ok, $reason] = $this->isUsable($promo);
            if (!$ok) {
                return response()->json([
                    'status' => 'error',
                    'reason' => $reason,
                ], 422);
            }

            [$discountValue, $finalAmount] = $this->computeDiscount($promo, (float) $data['base_amount']);

            if (!is_null($promo->usage_limit)) {
                $promo->used_count = $promo->used_count + 1;
            }
            $promo->save();

            return response()->json([
                'status' => 'ok',
                'promo_code_id' => $promo->id,
                'discount_value' => $discountValue,
                'final_amount' => $finalAmount,
            ]);
        });
    }
}
