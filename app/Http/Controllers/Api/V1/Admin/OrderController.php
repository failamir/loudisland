<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Tiket;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap as MidtransSnap;

class OrderController extends Controller
{
    // POST /api/v1/orders
    public function store(Request $request)
    {
        $request->validate([
            'event_id' => 'required|integer|exists:events,id',
        ]);

        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $event = Event::query()->findOrFail((int) $request->input('event_id'));
        // Ambil harga dari server (hindari kiriman client)
        $amount = (float) ($event->harga ?? 0);
        if ($amount <= 0) {
            return response()->json(['message' => 'Invalid event price'], 422);
        }

        $result = DB::transaction(function () use ($user, $event, $amount) {
            // 1) Buat tiket
            $tiket = Tiket::query()->create([
                'no_tiket'   => strtoupper('TK-' . date('ymd') . '-' . Str::random(6)),
                'peserta_id' => $user->id,
                'checkin'    => 'belum',
                'notes'      => null,
                'event_id'   => $event->id,
            ]);

            // 2) Buat transaksi untuk tiket tsb
            $invoice = strtoupper('INV-' . date('ymdHis') . '-' . Str::random(5));
            $trx = Transaksi::query()->create([
                'invoice'    => $invoice,
                'event_id'   => $event->id,
                'tiket_id'   => $tiket->id,
                'peserta_id' => $user->id,
                'amount'     => $amount,
                'note'       => null,
                'snap_token' => null,
                'status'     => 'pending',
            ]);

            return [$tiket, $trx];
        });

        [$tiket, $trx] = $result;

        // 3) Konfigurasi Midtrans
        $serverKey = env('MIDTRANS_SERVER_KEY');
        if (!$serverKey) {
            return response()->json(['message' => 'Server misconfiguration: MIDTRANS_SERVER_KEY is not set'], 500);
        }
        MidtransConfig::$serverKey = $serverKey;
        MidtransConfig::$isProduction = (bool) env('MIDTRANS_IS_PRODUCTION', false);
        MidtransConfig::$is3ds = true;
        MidtransConfig::$isSanitized = true;

        $params = [
            'transaction_details' => [
                'order_id' => $trx->invoice,
                'gross_amount' => (int) round($trx->amount),
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email'      => $user->email,
                'phone'      => $user->no_hp,
            ],
            'item_details' => [[
                'id' => (string) $event->id,
                'price' => (int) round($trx->amount),
                'quantity' => 1,
                'name' => $event->nama_event ?? ('Event #' . $event->id),
            ]],
        ];

        try {
            // Dapatkan Snap transaction (redirect_url + token)
            $snapResp = MidtransSnap::createTransaction($params);
            $snapToken = $snapResp->token ?? null;
            $redirectUrl = $snapResp->redirect_url ?? null;

            // Simpan snap_token
            $trx->snap_token = $snapToken;
            $trx->save();
        } catch (\Throwable $e) {
            Log::warning('Midtrans createTransaction failed: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to initialize Midtrans payment'], 502);
        }

        return response()->json([
            'message' => 'Order created',
            'data' => [
                'ticket' => [
                    'id' => $tiket->id,
                    'no_tiket' => $tiket->no_tiket,
                    'event_id' => $tiket->event_id,
                ],
                'transaction' => [
                    'id' => $trx->id,
                    'invoice' => $trx->invoice,
                    'amount' => (float) $trx->amount,
                    'status' => $trx->status,
                    'snap_token' => $trx->snap_token,
                    'redirect_url' => $redirectUrl ?? null,
                ],
            ],
        ], 201);
    }
}
