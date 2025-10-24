<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Http\Requests\StoreTransaksiRequest;
use App\Http\Requests\UpdateTransaksiRequest;
use App\Http\Resources\Admin\TransaksiResource;
use App\Models\Participant;
use App\Models\Event;
use App\Models\Transaksi;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TransaksiApiController extends Controller
{
    use MediaUploadingTrait;

    public function index(Request $request)
    {
        // abort_if(Gate::denies('transaksi_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $excluded_emails = explode(',', env('EMAIL_TESTING', ''));
        $query = Transaksi::query()
            ->with(['event', 'tiket', 'peserta', 'created_by'])
            ->where('amount', '>', 100000)
            ->whereNotIn('email', $excluded_emails)
            ->orderBy('id', 'desc');

        // Optional filters
        if ($status = $request->query('status')) {
            $query->where('status', $status)->where('amount', '>', 10000)
                ->whereNotIn('email', $excluded_emails);
        }
        if ($invoice = $request->query('invoice')) {
            $query->where('invoice', 'like', "%$invoice%")->where('amount', '>', 10000)
                ->whereNotIn('email', $excluded_emails);
        }
        // Generic keyword: search by invoice or peserta name
        if ($keyword = $request->query('keyword')) {
            $kw = "%$keyword%";
            $query->where(function ($q) use ($kw) {
                $q->where('invoice', 'like', $kw)->where('amount', '>', 10000)
                  ->orWhereHas('peserta', function ($p) use ($kw) {
                      $p->where('name', 'like', $kw);
                  });
            });
        }
        // Date filtering on created_at
        if ($from = $request->query('date_from')) {
            try {
                $query->whereDate('created_at', '>=', \Carbon\Carbon::parse($from)->startOfDay());
            } catch (\Throwable $e) {}
        }
        if ($to = $request->query('date_to')) {
            try {
                $query->whereDate('created_at', '<=', \Carbon\Carbon::parse($to)->endOfDay());
            } catch (\Throwable $e) {}
        }

        $perPage = max(1, (int) $request->query('per_page', 200));

        // Use paginator to include meta & links in API response
        $paginator = $query->paginate($perPage);

        // Compute summary counts
        $totalAll  = Transaksi::where('amount', '>', 100000)->whereNotIn('email', $excluded_emails)->count();
        $totalSucc = Transaksi::where('status', 'success')->where('amount', '>', 100000)->whereNotIn('email', $excluded_emails)->count();
        $sumSucc   = (int) Participant::where('status', 1)->where('amount', '>', 100000)->whereNotIn('email', $excluded_emails)->sum('amount');
        // $sumSucc   = (int) Transaksi::where('status', 'success')->where('amount', '>', 100000)->whereNotIn('email', $excluded_emails)->sum('amount');
        $totalPend = Transaksi::where('status', 'pending')->where('amount', '>', 100000)->whereNotIn('email', $excluded_emails)->count();
        $totalExp  = Transaksi::where('status', 'expired')->where('amount', '>', 100000)->whereNotIn('email', $excluded_emails)->count();

        return TransaksiResource::collection($paginator)
            ->additional([
                'summary' => [
                    'total'   => (int) $totalAll,
                    'success' => (int) $totalSucc,
                    'pending' => (int) $totalPend,
                    'expired' => (int) $totalExp,
                    'success_amount' => $sumSucc,
                ],
            ]);
    }

    public function store(StoreTransaksiRequest $request)
    {
        $transaksi = Transaksi::create($request->all());

        return (new TransaksiResource($transaksi))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Request $request, Transaksi $transaksi)
    {
        // abort_if(Gate::denies('transaksi_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Prefer route-model binding; fall back to query params (id or invoice)
        $bound = ($transaksi instanceof Transaksi) && $transaksi->exists && $transaksi->getKey();
        if ($bound) {
            $transaksi->load(['event', 'tiket', 'peserta']);
        } else {
            // Treat unbound/empty model as null
            $transaksi = null;
            $id = $request->query('id');
            $invoice = $request->query('invoice');

            $query = Transaksi::query()->with(['event', 'tiket', 'peserta']);
            if ($id) {
                $transaksi = $query->find($id);
            } elseif ($invoice) {
                $transaksi = $query->where('invoice', $invoice)->first();
            }
        }

        if (!$transaksi) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        // Shape concise payload for modal usage
        $payload = [
            'id'            => $transaksi->id,
            'invoice'       => $transaksi->invoice,
            'status'        => $transaksi->status,
            'amount'        => (float) $transaksi->amount,
            'payment_type'  => $transaksi->payment_type ?? null,
            'created_at'    => optional($transaksi->created_at)->toDateTimeString(),
            'peserta'       => $transaksi->peserta ? [
                'id'    => $transaksi->peserta->id,
                'name'  => $transaksi->peserta->name,
                'email' => $transaksi->peserta->email,
            ] : null,
            'event'         => $transaksi->event ? [
                'id'    => $transaksi->event->id,
                'name'  => $transaksi->event->name ?? $transaksi->event->judul ?? null,
            ] : null,
            'tiket'         => $transaksi->tiket ? [
                'id'       => $transaksi->tiket->id,
                'no_tiket' => $transaksi->tiket->no_tiket,
            ] : null,
        ];

        return response()->json(['data' => $payload]);
    }

    public function update(UpdateTransaksiRequest $request, Transaksi $transaksi)
    {
        $transaksi->update($request->all());

        return (new TransaksiResource($transaksi))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function destroy(Transaksi $transaksi)
    {
        abort_if(Gate::denies('transaksi_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $transaksi->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Backfill final_price for transactions where it is null/empty/zero.
     * POST /api/v1/transactions/backfill-final-price
     * Optional query/body:
     * - mode: auto|amount|formula (default: auto)
     *   - amount: final_price = amount
     *   - formula: final_price = sum(ticket_price) - (count*ticket_fee_fixed) - (sum(ticket_price)*fee_percent)
     *   - auto: try formula, fallback to amount
     */
    public function backfillFinalPrice(Request $request)
    {
        $mode = strtolower((string) $request->input('mode', $request->query('mode', 'auto')));
        if (!in_array($mode, ['auto', 'amount', 'formula'], true)) {
            $mode = 'auto';
        }

        $fixedFee = 5000;        // per ticket
        $percentFee = 0.016;     // 1.6%

        $all = (bool) $request->boolean('all', $request->query('all', false));
        $includeTrashed = (bool) $request->boolean('with_trashed', $request->query('with_trashed', false));

        $q = $all ? Transaksi::withoutGlobalScopes() : Transaksi::query();
        if ($includeTrashed) {
            $q->withTrashed();
        }
        $q = $q->where(function ($w) {
            $w->whereNull('final_price')
                ->orWhere('final_price', '=', '')
                ->orWhere('final_price', 0);
        });

        $total = (clone $q)->count();
        $updated = 0;

        $q->orderBy('id')->chunk(200, function ($rows) use (&$updated, $mode, $fixedFee, $percentFee) {
            foreach ($rows as $t) {
                $amount = (float) ($t->amount ?? 0);

                $finalByAmount = (int) round($amount);

                // Compute by formula if requested/allowed
                $finalByFormula = null;
                if ($mode !== 'amount') {
                    $participants = $t->participants()->get(['ticket_id', 'amount']);
                    $ticketCount = max(1, (int) $participants->count());

                    // Sum ticket price from participant.amount if present, otherwise from Event.harga
                    $ticketSum = 0.0;
                    foreach ($participants as $p) {
                        $pAmount = (float) ($p->amount ?? 0);
                        if ($pAmount <= 0 && $p->ticket_id) {
                            $ev = Event::select(['id', 'harga'])->find($p->ticket_id);
                            if ($ev && $ev->harga) {
                                $pAmount = (float) $ev->harga;
                            }
                        }
                        $ticketSum += max(0, $pAmount);
                    }

                    if ($ticketSum <= 0 && $amount > 0) {
                        // Fallback: use transaction amount and assume 1 ticket
                        $ticketSum = $amount;
                        $ticketCount = max(1, $ticketCount);
                    }

                    $calc = $ticketSum - ($ticketCount * $fixedFee) - ($ticketSum * $percentFee);
                    if (is_finite($calc)) {
                        $finalByFormula = (int) round($calc);
                    }
                }

                $newFinal = null;
                if ($mode === 'amount') {
                    $newFinal = $finalByAmount;
                } elseif ($mode === 'formula') {
                    $newFinal = $finalByFormula ?? $finalByAmount;
                } else { // auto
                    $newFinal = $finalByFormula ?? $finalByAmount;
                }

                if ($newFinal !== null && $newFinal > 0) {
                    $t->final_price = $newFinal;
                    $t->save();
                    $updated++;
                }
            }
        });

        return response()->json([
            'message' => 'Backfill completed',
            'total_candidates' => (int) $total,
            'updated' => (int) $updated,
            'mode' => $mode,
        ]);
    }
}
