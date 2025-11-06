<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\Withdrawal;
use App\Models\WithdrawalHistory;
use App\Models\Participant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class WithdrawalController extends Controller
{
    /**
     * Calculate net income from successful transactions with business rules:
     * - Exclude transactions where (amount < 175000) AND (email == EMAIL_TESTING)
     * - totalSuccess = all remaining success transactions
     * - TotalProfitKita = (count(totalSuccess) * 5000) + (sum(totalSuccess) * 0.01)
     * - Total Pemasukan (net income) = sum(totalSuccess) - TotalProfitKita
     *
     * @return array{gross_sum:int,count:int,profit:int,net_income:int}
     */
    private function calculateNetIncome(): array
    {
        $testingEmail = env('EMAIL_TESTING');

        $query = Transaksi::query()->where('status', 'success');

        // Apply exclusion ONLY if testing email is configured
        if (!empty($testingEmail)) {
            // Keep rows that do NOT satisfy (amount < 175000 AND email == testingEmail)
            $query->where(function ($q) use ($testingEmail) {
                $q->where('amount', '>=', 175000)
                    ->orWhereNull('email')
                    ->orWhere('email', '!=', $testingEmail);
            });
        }

        // Clone queries to avoid re-building
        $count = (clone $query)->count();
        $grossSum = (int) (clone $query)->sum('amount');

        // Profit: per-success fee + 1% of gross sum (floor to int)
        $profit = (int) ($count * 5000 + floor($grossSum * 0.015));
        $netIncome = max(0, $grossSum - $profit);

        return [
            'gross_sum'  => (int) $grossSum,
            'count'      => (int) $count,
            'profit'     => (int) $profit,
            'net_income' => (int) $netIncome,
        ];
    }

    public function index(Request $request)
    {
        if (Auth::id() == 1) {
            $query = Withdrawal::with(['created_by'])
            ->orderByDesc('created_at');
        } else {
            $query = Withdrawal::with(['created_by'])
                ->where('created_by_id', Auth::id())
                ->where('note', '!=', 'referral')
                ->orderByDesc('created_at');
        }
        if ($request->filled('status')) {
            $query->where('status', (string) $request->get('status'));
        }

        // Return simple array for easier FE consumption
        $limit = max(1, (int) $request->get('per_page', 20));
        $rows = $query->take($limit)->get();

        return response()->json([
            'data' => $rows,
        ]);
    }

    public function summary()
    {
        // Use participants-based net income logic (same as Dashboard):
        // gross = sum(participants.amount) where status = '1'
        // profit = count * 5000 + floor(gross * 0.01)
        // net_income = max(0, gross - profit)
        $excluded_emails = explode(',', env('EMAIL_TESTING', ''));
        $participantsQuery = Transaksi::query()->where('status', 'success')->where('amount', '>', 100000)->whereNotIn('email', $excluded_emails);
        $count = (int) (clone $participantsQuery)->count();
        $grossSum = (int) (clone $participantsQuery)->sum('amount');
        $profit = (int) ($count * 5000) + (int) floor($grossSum * 0.015);
        $netIncome = max(0, $grossSum - $profit);

        $totalWithdrawn = (int) Withdrawal::whereIn('status', ['approved', 'paid'])->sum('amount');
        $available = max(0, $netIncome - $totalWithdrawn);

        return response()->json([
            'data' => [
                'total_income' => $netIncome,
                'total_withdrawn' => $totalWithdrawn,
                'available_balance' => $available,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'amount' => 'required|integer|min:50000',
            'bank' => 'required|string|max:150',
            'account_name' => 'nullable|string|max:150',
            'account_number' => 'required|string|max:150',
            'note' => 'nullable|string',
        ]);

        // recompute balances snapshot using participants-based net income
        $excluded_emails = explode(',', env('EMAIL_TESTING', ''));
        $participantsQuery = Participant::query()->where('status', '1')->whereNotIn('email', $excluded_emails);
        $count = (int) (clone $participantsQuery)->count();
        $grossSum = (int) (clone $participantsQuery)->sum('amount');
        $profit = (int) ($count * 5000) + (int) floor($grossSum * 0.015);
        $netIncome = max(0, $grossSum - $profit);

        $totalWithdrawn = (int) Withdrawal::whereIn('status', ['approved', 'paid'])->sum('amount');
        $available = max(0, $netIncome - $totalWithdrawn);

        if ($data['amount'] > $available) {
            return response()->json([
                'message' => 'Saldo tidak cukup untuk withdrawal ini.',
                'data' => [
                    'requested' => (int) $data['amount'],
                    'available' => $available,
                ],
            ], 422);
        }

        $user = $request->user();
        $userId = optional($user)->id;

        // default account_name from referral profile if empty
        if (empty($data['account_name'])) {
            $ref = \App\Models\ReferralCode::where('user_id', $userId)->orderByDesc('created_at')->first();
            if ($ref && !empty($ref->metadata['full_name'])) {
                $data['account_name'] = $ref->metadata['full_name'];
            }
        }

        $withdrawal = null;
        DB::transaction(function () use (&$withdrawal, $data, $userId, $netIncome, $totalWithdrawn, $available) {
            $withdrawal = Withdrawal::create([
                'amount' => $data['amount'],
                'bank' => $data['bank'],
                'account_name' => $data['account_name'],
                'account_number' => $data['account_number'],
                'note' => $data['note'] ?? null,
                'status' => 'queued',
                'created_by_id' => $userId,
            ]);

            WithdrawalHistory::create([
                'withdrawal_id' => $withdrawal->id,
                'action' => 'created',
                'note' => $data['note'] ?? null,
                'acted_by_id' => $userId,
                'amount_snapshot' => $withdrawal->amount,
                'balance_before' => $available,
                'balance_after' => $available - $withdrawal->amount,
                'meta' => [
                    'total_income' => $netIncome,
                    'total_withdrawn' => $totalWithdrawn,
                ],
            ]);
        });

        return response()->json([
            'message' => 'Withdrawal berhasil diajukan',
            'data' => $withdrawal->load('created_by'),
        ], 201);
    }

    public function show(Withdrawal $withdrawal)
    {
        $withdrawal->load(['created_by', 'histories.acted_by']);
        return response()->json(['data' => $withdrawal]);
    }

    public function updateStatus(Request $request, Withdrawal $withdrawal)
    {
        $data = $request->validate([
            'action' => 'required|string|in:approved,paid,rejected,canceled',
            'note' => 'nullable|string',
        ]);

        $userId = optional($request->user())->id;

        // recompute balances snapshot using participants-based net income
        $participantsQuery = Participant::query()->where('status', '1');
        $count = (int) (clone $participantsQuery)->count();
        $grossSum = (int) (clone $participantsQuery)->sum('amount');
        $profit = (int) ($count * 5000) + (int) floor($grossSum * 0.015);
        $netIncome = max(0, $grossSum - $profit);

        $totalWithdrawn = (int) Withdrawal::whereIn('status', ['approved', 'paid'])
            ->where('id', '!=', $withdrawal->id)
            ->sum('amount');

        $availableBefore = max(0, $netIncome - $totalWithdrawn);

        DB::transaction(function () use ($data, $withdrawal, $userId, $netIncome, $totalWithdrawn, $availableBefore) {
            $withdrawal->update(['status' => $data['action']]);

            WithdrawalHistory::create([
                'withdrawal_id' => $withdrawal->id,
                'action' => $data['action'],
                'note' => $data['note'] ?? null,
                'acted_by_id' => $userId,
                'amount_snapshot' => $withdrawal->amount,
                'balance_before' => $availableBefore,
                'balance_after' => in_array($data['action'], ['approved', 'paid'])
                    ? max(0, $availableBefore - $withdrawal->amount)
                    : $availableBefore,
                'meta' => [
                    'total_income' => $netIncome,
                    'total_withdrawn' => $totalWithdrawn,
                ],
            ]);
        });

        return response()->json([
            'message' => 'Status withdrawal diperbarui',
            'data' => $withdrawal->fresh()->load(['created_by']),
        ]);
    }
}
