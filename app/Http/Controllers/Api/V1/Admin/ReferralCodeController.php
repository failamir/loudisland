<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReferralCode;
use App\Models\Referal;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;

class ReferralCodeController extends Controller
{
    public function adminIndex(Request $request)
    {
        $user = $request->user('api') ?: $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        // if (Gate::denies('referral.manage')) {
        //     return response()->json(['message' => 'Forbidden'], 403);
        // }

        $q = ReferralCode::query()->with(['user' => function ($r) {
            $r->select('id', 'name', 'email');
        }])->orderByDesc('created_at');

        if ($request->filled('active')) {
            $q->where('active', filter_var($request->get('active'), FILTER_VALIDATE_BOOLEAN));
        }
        if ($request->filled('q')) {
            $kw = '%' . trim($request->get('q')) . '%';
            $q->where(function ($qq) use ($kw) {
                $qq->where('code', 'like', $kw)
                    ->orWhereHas('user', function ($u) use ($kw) {
                        $u->where('name', 'like', $kw)->orWhere('email', 'like', $kw);
                    });
            });
        }

        $limit = max(1, (int) $request->get('per_page', 50));
        $rows = $q->take($limit)->get();
        return response()->json(['data' => $rows]);
    }
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
            // Fallback: if user entered body-only, auto-prefix REF- when body length valid
            $bodyOnly = strtoupper(trim((string) ($data['code'] ?? '')));
            $bodyOnly = preg_replace('/[^A-Z0-9]/', '', $bodyOnly);
            if ($bodyOnly !== '' && strlen($bodyOnly) >= 4 && strlen($bodyOnly) <= 24) {
                $code = 'REF-' . $bodyOnly;
            }

            if (!preg_match('/^[A-Z0-9]+-[A-Z0-9]{4,24}$/', $code)) {
                return response()->json(['message' => 'Format kode tidak valid. Gunakan PREFIX-XXXXXXXX (4-24 karakter).'], 422);
            }
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
            'usage_limit' => $data['usage_limit'] ?? 1000,
            'used_count' => 0,
            'valid_from' => $data['valid_from'] ?? null,
            'valid_to' => $data['valid_to'] ?? null,
            'metadata' => [
                'full_name' => $data['full_name'] ?? null,
                'bank' => $data['bank'] ?? null,
                'account_number' => $data['account_number'] ?? null,
            ],
        ]);

        // Notify admins about new referral registration (pending approval)
        try {
            $subject = 'New Referral Registration (Pending)';
            $body = "ReferralCode ID: {$row->id}\n" .
                    "User ID: {$user->id}\n" .
                    "User: {$user->name} <{$user->email}>\n" .
                    "Code: {$row->code}\n" .
                    "Active: {$row->active}\n" .
                    "Created At: {$row->created_at}";
            Mail::raw($body, function ($message) use ($subject) {
                $message->to(['ifailamir@gmail.com', 'kardusinfo.com@gmail.com'])->subject($subject);
            });
        } catch (\Throwable $_) {
            // ignore mail failures silently
        }

        // Assign role Refer Pending (id 3) on registration
        try {
            if (method_exists($user->roles(), 'syncWithoutDetaching')) {
                $user->roles()->syncWithoutDetaching([3]);
            } else {
                if (!$user->roles()->where('roles.id', 3)->exists()) {
                    $user->roles()->attach(3);
                }
            }
        } catch (\Throwable $_) {
            // ignore role attach failures silently
        }

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


    public function validatePublic(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|min:4|max:32',
        ]);
        $code = strtoupper(trim($data['code']));
        $row = ReferralCode::whereRaw('UPPER(code) = ?', [$code])->first();
        if (!$row) {
            // fallback: body-only match (suffix after dash)
            $body = preg_replace('/[^A-Z0-9]/', '', $code);
            if ($body !== '') {
                $row = ReferralCode::whereRaw('UPPER(code) LIKE ?', ['%-' . $body])->first();
            }
        }
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

        // augment with balance (using new referral_withdrawals table)
        $sum = (int) Referal::where('user_id_referral', $user->id)->sum('value');
        // include 'queued' as reserved so available decreases while pending
        $withdrawn = (int) \App\Models\ReferralWithdrawal::where('user_id', $user->id)
            ->whereIn('status', ['queued', 'approved', 'paid'])
            ->sum('amount');
        $available = max(0, $sum - $withdrawn);
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
                'withdrawn_referral' => $withdrawn,
                'available_referral' => $available,
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


    public function adminUpdateStatus(Request $request, $id)
    {
        $user = $request->user('api') ?: $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        if (Gate::denies('referral.manage')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'active' => 'required|boolean',
        ]);

        $row = ReferralCode::find($id);
        if (!$row) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $row->update([
            'active' => (bool) $data['active'],
        ]);

        // On approve: switch roles Refer Pending (3) -> Refer Approve (4)
        if ((bool) $data['active']) {
            $u = \App\Models\User::find($row->user_id);
            if ($u) {
                try {
                    $u->roles()->detach(3);
                    if (method_exists($u->roles(), 'syncWithoutDetaching')) {
                        $u->roles()->syncWithoutDetaching([4]);
                    } else {
                        if (!$u->roles()->where('roles.id', 4)->exists()) {
                            $u->roles()->attach(4);
                        }
                    }
                } catch (\Throwable $_) {
                    // ignore role updates silently
                }
            }
        }

        // Notify about approval status change
        try {
            $subject = 'Referral Code Approval Updated';
            $statusText = ((bool)$data['active']) ? 'APPROVED' : 'DEACTIVATED';
            $owner = \App\Models\User::find($row->user_id);
            $body = "ReferralCode ID: {$row->id}\n" .
                    "User ID: {$row->user_id}\n" .
                    "Code: {$row->code}\n" .
                    "New Status: {$statusText}\n" .
                    "Owner: " . ($owner ? ($owner->name . ' <' . $owner->email . '>') : '-') . "\n" .
                    "Updated At: " . now();
            Mail::raw($body, function ($message) use ($subject) {
                $message->to(['ifailamir@gmail.com', 'kardusinfo.com@gmail.com'])->subject($subject);
            });
        } catch (\Throwable $_) {
            // ignore mail failures silently
        }

        return response()->json(['data' => $row->fresh()]);
    }
}
