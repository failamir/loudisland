<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Http\Requests\StoreTransaksiRequest;
use App\Http\Requests\UpdateTransaksiRequest;
use App\Http\Resources\Admin\TransaksiResource;
use App\Models\Transaksi;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TransaksiApiController extends Controller
{
    use MediaUploadingTrait;

    public function index()
    {
        // abort_if(Gate::denies('transaksi_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new TransaksiResource(Transaksi::with(['event', 'tiket', 'peserta', 'created_by'])->get());
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
}
