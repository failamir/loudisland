<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\Event;

class TransactionsListController extends Controller
{
    // GET /api/v1/transactions/simple?status=success|pending|failed&per_page=20&page=1
    public function index(Request $request)
    {
        $query = Transaksi::query()->with(['event'])->orderBy('id', 'desc');

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
            // Resolve event: prefer relation; if null, infer from `events` payload
            $ev = $trx->event;
            if (!$ev && !empty($trx->events)) {
                $decoded = json_decode($trx->events, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $maybe = @unserialize($trx->events);
                    $decoded = $maybe !== false ? $maybe : $trx->events;
                }
                $eventIds = collect(is_array($decoded) ? $decoded : [$decoded])->filter()->values();
                $firstId = $eventIds->first();
                if ($firstId) {
                    $ev = Event::find($firstId);
                }
            }

            // Ambil nama peserta dari relasi participants() (bukan kolom string `participants` di tabel transactions)
            $participantNames = $trx->participants()
                ->pluck('name')
                ->filter()
                ->implode(', ');

            return [
                'id' => $trx->id,
                'invoice' => $trx->invoice,
                'status' => $trx->status,
                'amount' => (int) $trx->amount,
                'nama' => $trx->nama,
                'no_hp' => $trx->no_hp,
                'participant_name' => $participantNames,
                'type' => $trx->type,
                'is_offline' => ($trx->type === 'offline'),
                'payment_type' => $trx->payment_type,
                'created_at' => $trx->created_at,
                'event' => $ev ? [
                    'id' => $ev->id,
                    'nama_event' => $ev->nama_event,
                    'event_code' => $ev->event_code,
                ] : null,
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
