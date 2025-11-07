<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReferralWithdrawal;
use App\Models\Referal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;

class ReferralWithdrawalController extends Controller
{
    // User: submit referral withdrawal request
    public function store(Request $request)
    {
        $user = $request->user('api') ?: $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $data = $request->validate([
            'amount' => 'required|integer|min:50000',
            'bank' => 'required|string|max:150',
            'account_name' => 'nullable|string|max:150',
            'account_number' => 'required|string|max:150',
            'note' => 'nullable|string',
        ]);

        // Calculate available balance (treat 'queued' as reserved)
        $totalEarning = (int) Referal::where('user_id_referral', $user->id)->sum('value');
        $totalWithdrawn = (int) ReferralWithdrawal::where('user_id', $user->id)
            ->whereIn('status', ['queued', 'approved', 'paid'])
            ->sum('amount');
        $available = max(0, $totalEarning - $totalWithdrawn);

        if ($data['amount'] > $available) {
            return response()->json([
                'message' => 'Saldo referral tidak cukup untuk withdrawal ini.',
                'data' => [
                    'requested' => (int) $data['amount'],
                    'available' => $available,
                ],
            ], 422);
        }

        // Default account_name from referral profile if empty
        if (empty($data['account_name'])) {
            $ref = \App\Models\ReferralCode::where('user_id', $user->id)->orderByDesc('created_at')->first();
            if ($ref && !empty($ref->metadata['full_name'])) {
                $data['account_name'] = $ref->metadata['full_name'];
            }
        }

        $withdrawal = ReferralWithdrawal::create([
            'user_id' => $user->id,
            'amount' => $data['amount'],
            'bank' => $data['bank'],
            'account_name' => $data['account_name'],
            'account_number' => $data['account_number'],
            'note' => $data['note'] ?? null,
            'status' => 'queued',
        ]);

        $subject = 'New Referral Withdrawal Requested';
        $body = "ID: {$withdrawal->id}\n" .
                "User ID: {$user->id}\n" .
                "Amount: {$withdrawal->amount}\n" .
                "Bank: {$withdrawal->bank}\n" .
                "Account Name: {$withdrawal->account_name}\n" .
                "Account Number: {$withdrawal->account_number}\n" .
                "Status: {$withdrawal->status}\n" .
                "Note: " . ($withdrawal->note ?? '-') . "\n" .
                "Created At: {$withdrawal->created_at}";
        // Mail::raw($body, function ($message) use ($subject) {
        //     $message->to(['ifailamir@gmail.com', 'kardusinfo.com@gmail.com'])->subject($subject);
        // });

        return response()->json([
            'message' => 'Withdrawal referral berhasil diajukan',
            'data' => $withdrawal->load('user'),
        ], 201);
    }

    // User: list own referral withdrawals
    public function index(Request $request)
    {
        $user = $request->user('api') ?: $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $limit = max(1, (int) $request->get('per_page', 20));
        $rows = ReferralWithdrawal::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->take($limit)
            ->get();

        return response()->json(['data' => $rows]);
    }

    // Admin: list all referral withdrawals with filters
    public function adminIndex(Request $request)
    {
        $user = $request->user('api') ?: $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        if (Gate::denies('referral.manage')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $query = ReferralWithdrawal::with(['user' => function ($r) {
            $r->select('id', 'name', 'email');
        }])->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', (string) $request->get('status'));
        }

        if ($request->filled('q')) {
            $kw = '%' . trim($request->get('q')) . '%';
            $query->where(function ($qq) use ($kw) {
                $qq->where('account_name', 'like', $kw)
                   ->orWhere('account_number', 'like', $kw)
                   ->orWhereHas('user', function ($u) use ($kw) {
                       $u->where('name', 'like', $kw)->orWhere('email', 'like', $kw);
                   });
            });
        }

        $limit = max(1, (int) $request->get('per_page', 50));
        $rows = $query->take($limit)->get();

        return response()->json(['data' => $rows]);
    }

    // Admin: update withdrawal status
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
            'action' => 'required|string|in:approved,paid,rejected,canceled',
            'note' => 'nullable|string',
        ]);

        $withdrawal = ReferralWithdrawal::find($id);
        if (!$withdrawal) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $withdrawal->update([
            'status' => $data['action'],
            'note' => $data['note'] ?? $withdrawal->note,
        ]);

        $subject = 'Referral Withdrawal Status Updated';
        $body = "ID: {$withdrawal->id}\n" .
                "User ID: {$withdrawal->user_id}\n" .
                "New Status: {$data['action']}\n" .
                "Amount: {$withdrawal->amount}\n" .
                "Bank: {$withdrawal->bank}\n" .
                "Account Name: {$withdrawal->account_name}\n" .
                "Account Number: {$withdrawal->account_number}\n" .
                "Note: " . (($data['note'] ?? $withdrawal->note) ?? '-') . "\n" .
                "Updated At: " . now();
        // Mail::raw($body, function ($message) use ($subject) {
        //     $message->to(['ifailamir@gmail.com', 'kardusinfo.com@gmail.com'])->subject($subject);
        // });

        return response()->json([
            'message' => 'Status withdrawal referral diperbarui',
            'data' => $withdrawal->fresh()->load('user'),
        ]);
    }

    // User: get referral balance summary
    public function balance(Request $request)
    {
        $user = $request->user('api') ?: $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $totalEarning = (int) Referal::where('user_id_referral', $user->id)->sum('value');
        $totalWithdrawn = (int) ReferralWithdrawal::where('user_id', $user->id)
            ->whereIn('status', ['queued', 'approved', 'paid'])
            ->sum('amount');
        $available = max(0, $totalEarning - $totalWithdrawn);

        return response()->json([
            'data' => [
                'total_earning' => $totalEarning,
                'total_withdrawn' => $totalWithdrawn,
                'available_balance' => $available,
            ],
        ]);
    }
}
