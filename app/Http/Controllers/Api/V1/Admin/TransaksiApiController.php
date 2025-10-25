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
        $percentFee = 0.02;     // 1.6%

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

        // Emails to treat as testing: set final_price to 0 for these
        $excludedEmails = array_filter(array_map(function ($e) {
            return strtolower(trim($e));
        }, explode(',', env('EMAIL_TESTING', ''))));

        $q->orderBy('id')->chunk(200, function ($rows) use (&$updated, $mode, $fixedFee, $percentFee, $excludedEmails) {
            foreach ($rows as $t) {
                $amount = (float) ($t->amount ?? 0);

                $finalByAmount = (int) round($amount);

                // Compute by formula if requested/allowed (apply per participant)
                $finalByFormula = null;
                if ($mode !== 'amount') {
                    $participants = $t->participants()->get(['ticket_id', 'amount']);

                    $gross = 0.0;
                    $count = 0;
                    foreach ($participants as $p) {
                        $pAmount = (float) ($p->amount ?? 0);
                        if ($pAmount <= 0 && $p->ticket_id) {
                            $ev = Event::select(['id', 'harga'])->find($p->ticket_id);
                            if ($ev && $ev->harga) {
                                $pAmount = (float) $ev->harga;
                            }
                        }
                        if ($pAmount > 0) {
                            $gross += $pAmount;
                            $count++;
                        }
                    }

                    // Fallback: if no participant data, use transaction amount as gross with single unit count
                    if ($gross <= 0 && $amount > 0) {
                        $gross = $amount;
                        $count = max(1, (int) $t->qty ?? 1);
                    }

                    if ($gross > 0) {
                        $profit = ($count * $fixedFee) + floor($gross * $percentFee);
                        $net = max(0, $gross - $profit);
                        $finalByFormula = (int) round($net);
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

                // If transaction email is in testing list, force to 0
                $trxEmail = strtolower(trim((string) ($t->email ?? '')));
                $forceZero = false;
                if ($trxEmail !== '' && in_array($trxEmail, $excludedEmails, true)) {
                    $newFinal = 0;
                    $forceZero = true;
                }

                if ($newFinal !== null && ($newFinal > 0 || $forceZero)) {
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

    /**
     * Backfill participants.final_price where null/empty/zero using per-participant formula.
     * POST /api/v1/participants/backfill-final-price
     * Query/body options:
     * - all=true to bypass tenant scopes
     * - with_trashed=true to include soft-deleted participants
     */
    public function backfillParticipantsFinalPrice(Request $request)
    {
        $fixedFee = 5000;
        $percentFee = 0.016;

        $all = (bool) $request->boolean('all', $request->query('all', false));
        $includeTrashed = (bool) $request->boolean('with_trashed', $request->query('with_trashed', false));

        $excludedEmails = array_filter(array_map(function ($e) {
            return strtolower(trim($e));
        }, explode(',', env('EMAIL_TESTING', ''))));

        $q = $all ? Participant::withoutGlobalScopes() : Participant::query();
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

        $q->orderBy('id')->chunk(500, function ($rows) use (&$updated, $fixedFee, $percentFee, $excludedEmails) {
            foreach ($rows as $p) {
                // Determine base ticket price per participant
                $statusVal = (string) ($p->status ?? '0');
                $newFinal = null;

                if ($statusVal === '1') {
                    // Active participant: compute final
                    $price = (float) ($p->amount ?? 0);
                    if ($price <= 0 && $p->ticket_id) {
                        $ev = Event::select(['id','harga'])->find($p->ticket_id);
                        if ($ev && $ev->harga) {
                            $price = (float) $ev->harga;
                        }
                    }
                    if ($price > 0) {
                        $calc = ($price - $fixedFee) - ($price * $percentFee);
                        if (is_finite($calc)) {
                            $newFinal = (int) round($calc);
                        }
                    }
                } else {
                    // Inactive participant: force zero
                    $newFinal = 0;
                }

                // Testing email forces zero regardless of status
                $email = strtolower(trim((string) ($p->email ?? '')));
                $forceZero = false;
                if ($email !== '' && in_array($email, $excludedEmails, true)) {
                    $newFinal = 0;
                    $forceZero = true;
                }

                if ($newFinal !== null && ($newFinal > 0 || $forceZero)) {
                    $p->final_price = $newFinal;
                    $p->save();
                    $updated++;
                }
            }
        });

        return response()->json([
            'message' => 'Participants backfill completed',
            'total_candidates' => (int) $total,
            'updated' => (int) $updated,
        ]);
    }
}
