<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReferralCode;
use App\Models\Referal;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ReferralCodeController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user('api') ?: $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $rows = ReferralCode::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();
        return response()->json(['data' => $rows]);
    }

    public function store(Request $request)
    {
        $user = $request->user('api') ?: $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // One-time registration: deny if user already has a referral code
        $existing = ReferralCode::where('user_id', $user->id)->first();
        if ($existing) {
            return response()->json([
                'message' => 'Anda sudah memiliki kode referal. Pendaftaran hanya dapat dilakukan sekali.',
                'data' => $existing,
            ], 409);
        }

        $data = $request->validate([
            'code' => 'nullable|string|min:4|max:32',
            'active' => 'nullable|boolean',
            'usage_limit' => 'nullable|integer|min:1',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
            // referral profile metadata
            'full_name' => 'nullable|string|max:150',
            'bank' => 'nullable|string|max:150',
            'account_number' => 'nullable|string|max:150',
        ]);

        // Normalize incoming code or generate default
        if (!empty($data['code'])) {
            $raw = strtoupper(trim((string) $data['code']));
            // keep dash to preserve custom prefix; drop invalid chars
            $norm = preg_replace('/[^A-Z0-9-]/', '', $raw);
            if (strpos($norm, '-') === false) {
                // no dash provided, treat as body and add default prefix
                $body = preg_replace('/[^A-Z0-9]/', '', $norm);
                $body = substr($body, 0, 24);
                $norm = ($body !== '' ? $body : $this->randomBody(8));
            }
            $code = $norm;
        } else {
            $code = $this->generateCode();
        }

        // Validate pattern PREFIX-BODY with body length 4..24
        if (!preg_match('/^[A-Z0-9]+-[A-Z0-9]{4,24}$/', $code)) {
            return response()->json(['message' => 'Format kode tidak valid. Gunakan PREFIX-XXXXXXXX (4-24 karakter).'], 422);
        }

        // Ensure unique (case-insensitive)
        $exists = ReferralCode::whereRaw('UPPER(code) = ?', [strtoupper($code)])->exists();
        if ($exists) {
            return response()->json(['message' => 'Kode sudah digunakan.'], 422);
        }

        $row = ReferralCode::create([
            'user_id' => $user->id,
            'code' => $code,
            // Default inactive; admin will approve/activate later
            'active' => false,
            'usage_limit' => $data['usage_limit'] ?? null,
            'used_count' => 0,
            'valid_from' => $data['valid_from'] ?? null,
            'valid_to' => $data['valid_to'] ?? null,
            'metadata' => [
                'full_name' => $data['full_name'] ?? null,
                'bank' => $data['bank'] ?? null,
                'account_number' => $data['account_number'] ?? null,
            ],
        ]);

        return response()->json(['data' => $row], 201);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user('api') ?: $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $row = ReferralCode::where('id', $id)->where('user_id', $user->id)->first();
        if (!$row) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $data = $request->validate([
            'active' => 'nullable|boolean',
            'usage_limit' => 'nullable|integer|min:1',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
        ]);

        $row->update([
            'active' => array_key_exists('active', $data) ? (bool) $data['active'] : $row->active,
            'usage_limit' => array_key_exists('usage_limit', $data) ? $data['usage_limit'] : $row->usage_limit,
            'valid_from' => array_key_exists('valid_from', $data) ? $data['valid_from'] : $row->valid_from,
            'valid_to' => array_key_exists('valid_to', $data) ? $data['valid_to'] : $row->valid_to,
        ]);

        return response()->json(['data' => $row->fresh()]);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user('api') ?: $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $row = ReferralCode::where('id', $id)->where('user_id', $user->id)->first();
        if (!$row) {
            return response()->json(['message' => 'Not found'], 404);
        }
        $row->delete();
        return response()->json(['message' => 'Deleted']);
    }

    public function balance(Request $request)
    {
        $user = $request->user('api') ?: $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $sum = (int) Referal::where('user_id_referral', $user->id)->sum('value');
        $count = (int) Referal::where('user_id_referral', $user->id)->count();

        return response()->json([
            'data' => [
                'balance' => $sum,
                'total_uses' => $count,
            ],
        ]);
    }

    public function validatePublic(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|min:4|max:32',
        ]);
        $code = strtoupper(trim($data['code']));
        $row = ReferralCode::whereRaw('UPPER(code) = ?', [$code])->first();
        if (!$row) {
            return response()->json(['valid' => false, 'reason' => 'not_found']);
        }
        $now = now();
        $inWindow = (!$row->valid_from || $now->gte($row->valid_from)) && (!$row->valid_to || $now->lte($row->valid_to));
        $underLimit = (is_null($row->usage_limit) || (int)$row->used_count < (int)$row->usage_limit);
        $ok = $row->active && $inWindow && $underLimit;
        return response()->json([
            'valid' => (bool) $ok,
            'active' => (bool) $row->active,
            'usage_limit' => $row->usage_limit,
            'used_count' => $row->used_count,
            'valid_from' => $row->valid_from,
            'valid_to' => $row->valid_to,
        ]);
    }

    private function generateCode(int $length = 8, string $prefix = 'REF'): string
    {
        return strtoupper($prefix . '-' . $this->randomBody($length));
    }

    private function randomBody(int $length = 8): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $body = '';
        for ($i = 0; $i < $length; $i++) {
            $body .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $body;
    }

    // Return current user's referral profile (code + metadata). No auto-create.
    public function mine(Request $request)
    {
        $user = $request->user('api') ?: $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $row = ReferralCode::where('user_id', $user->id)->orderByDesc('created_at')->first();
        if (!$row) {
            return response()->json(['message' => 'Referral belum terdaftar untuk akun ini. Silakan daftar terlebih dahulu.'], 404);
        }

        // augment with balance
        $sum = (int) Referal::where('user_id_referral', $user->id)->sum('value');
        // recent uses list (desc by tanggal) with per_page limit (default 50)
        $limit = max(1, (int) $request->get('per_page', 50));
        $uses = Referal::where('user_id_referral', $user->id)
            ->orderByDesc('tanggal')
            ->take($limit)
            ->get(['tanggal', 'email_pemesan', 'value']);

        return response()->json([
            'data' => [
                'id' => $row->id,
                'code' => $row->code,
                'active' => (bool) $row->active,
                'usage_limit' => $row->usage_limit,
                'used_count' => $row->used_count,
                'valid_from' => $row->valid_from,
                'valid_to' => $row->valid_to,
                'metadata' => $row->metadata,
                'balance' => $sum,
                'uses' => $uses,
            ],
        ]);
    }

    // Update current user's referral profile metadata and active flag
    public function updateMine(Request $request)
    {
        $user = $request->user('api') ?: $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $data = $request->validate([
            'full_name' => 'nullable|string|max:150',
            'bank' => 'nullable|string|max:150',
            'account_number' => 'nullable|string|max:150',
            'active' => 'nullable|boolean',
        ]);

        $row = ReferralCode::where('user_id', $user->id)->orderByDesc('created_at')->first();
        if (!$row) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $meta = $row->metadata ?? [];
        foreach (['full_name', 'bank', 'account_number'] as $k) {
            if (array_key_exists($k, $data)) {
                $meta[$k] = $data[$k];
            }
        }

        $row->update([
            'active' => array_key_exists('active', $data) ? (bool) $data['active'] : $row->active,
            'metadata' => $meta,
        ]);

        return response()->json(['data' => $row->fresh()]);
    }

    // List referral transactions that credit current user (for dashboard)
    public function transactions(Request $request)
    {
        $user = $request->user('api') ?: $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $limit = max(1, (int) $request->get('per_page', 20));
        $rows = Referal::where('user_id_referral', $user->id)
            ->orderByDesc('tanggal')
            ->take($limit)
            ->get(['tanggal', 'email_pemesan', 'value', 'kode']);
        return response()->json(['data' => $rows]);
    }

    // List my withdrawals (for dashboard history)
    public function myWithdrawals(Request $request)
    {
        $user = $request->user('api') ?: $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $limit = max(1, (int) $request->get('per_page', 20));
        $rows = \App\Models\Withdrawal::where('created_by_id', $user->id)
            ->orderByDesc('created_at')
            ->take($limit)
            ->get(['id','amount','bank','account_name','account_number','status','created_at']);
        return response()->json(['data' => $rows]);
    }
}
