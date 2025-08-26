<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaksi;

class TransactionsListController extends Controller
{
    // GET /api/v1/transactions/simple?status=success|pending|failed&per_page=20&page=1
    public function index(Request $request)
    {
        $query = Transaksi::query()->orderByDesc('created_at');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        if ($invoice = $request->query('invoice')) {
            $query->where('invoice', 'like', "%$invoice%");
        }

        $perPage = max(1, (int) $request->query('per_page', 20));
        $page = max(1, (int) $request->query('page', 1));

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        $data = $paginator->getCollection()->map(function ($trx) {
            return [
                'id' => $trx->id,
                'invoice' => $trx->invoice,
                'status' => $trx->status,
                'amount' => (int) $trx->amount,
                'payment_type' => $trx->payment_type,
                'created_at' => $trx->created_at,
            ];
        })->all();

        return response()->json([
            'data' => $data,
            'pagination' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }
}
