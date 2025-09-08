<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\Withdrawal;
use App\Models\WithdrawalHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WithdrawalController extends Controller
{
    public function index(Request $request)
    {
        $query = Withdrawal::with(['created_by'])
            ->orderByDesc('created_at');

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
        $totalIncome = (int) Transaksi::where('status', 'success')->sum('amount');
        $totalWithdrawn = (int) Withdrawal::whereIn('status', ['approved', 'paid'])->sum('amount');
        $available = max(0, $totalIncome - $totalWithdrawn);

        return response()->json([
            'data' => [
                'total_income' => $totalIncome,
                'total_withdrawn' => $totalWithdrawn,
                'available_balance' => $available,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'amount' => 'required|integer|min:1',
            'bank' => 'required|string|max:150',
            'account_name' => 'required|string|max:150',
            'account_number' => 'required|string|max:150',
            'note' => 'nullable|string',
        ]);

        // Recalculate available balance before allowing
        $totalIncome = (int) Transaksi::where('status', 'success')->sum('amount');
        $totalWithdrawn = (int) Withdrawal::whereIn('status', ['approved', 'paid'])->sum('amount');
        $available = max(0, $totalIncome - $totalWithdrawn);

        if ($data['amount'] > $available) {
            return response()->json([
                'message' => 'Saldo tidak cukup untuk withdrawal ini.',
                'data' => [
                    'requested' => (int) $data['amount'],
                    'available' => $available,
                ],
            ], 422);
        }

        $userId = optional($request->user())->id;

        $withdrawal = null;
        DB::transaction(function () use (&$withdrawal, $data, $userId, $totalIncome, $totalWithdrawn, $available) {
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
                    'total_income' => $totalIncome,
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

        // recompute balances snapshot
        $totalIncome = (int) Transaksi::where('status', 'success')->sum('amount');
        $totalWithdrawn = (int) Withdrawal::whereIn('status', ['approved', 'paid'])
            ->where('id', '!=', $withdrawal->id)
            ->sum('amount');

        $availableBefore = max(0, $totalIncome - $totalWithdrawn);

        DB::transaction(function () use ($data, $withdrawal, $userId, $totalIncome, $totalWithdrawn, $availableBefore) {
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
                    'total_income' => $totalIncome,
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
