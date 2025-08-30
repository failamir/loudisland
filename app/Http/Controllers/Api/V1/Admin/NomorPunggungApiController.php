<?php

namespace App\Http\Controllers\Api\V1\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class NomorPunggungApiController extends Controller
{
    // List nomor punggung with QR URLs (no generation here)
    public function index(Request $request)
    {
        $dir = public_path('qrcodes');
        $items = [];
        if (is_dir($dir)) {
            $files = glob($dir . DIRECTORY_SEPARATOR . '*.png');
            foreach ($files as $file) {
                $name = basename($file); // e.g., 00001.png
                $nomor = pathinfo($name, PATHINFO_FILENAME);
                $items[] = [
                    'nomor_punggung' => $nomor,
                    'qr_url' => url('/qrcodes/' . $name),
                ];
            }
        }
        // natural sort by nomor
        usort($items, function ($a, $b) {
            return strnatcasecmp($a['nomor_punggung'], $b['nomor_punggung']);
        });

        // optional query filter by nomor
        $q = trim((string) $request->query('q', ''));
        if ($q !== '') {
            $items = array_values(array_filter($items, function ($row) use ($q) {
                return stripos($row['nomor_punggung'], $q) !== false;
            }));
        }

        $perPage = max(1, (int)($request->query('per_page', 10)));
        $page = max(1, (int)($request->query('page', 1)));
        $total = count($items);
        $offset = ($page - 1) * $perPage;
        $data = array_slice($items, $offset, $perPage);

        // enrich with pairing info from transactions only (batched lookup)
        if (!empty($data)) {
            $nomors = array_map(fn($row) => $row['nomor_punggung'], $data);
            $trxs = \App\Models\Transaksi::whereIn('nomor_punggung', $nomors)
                ->get(['id', 'nomor_punggung', 'paired_at', 'peserta_id'])
                ->keyBy('nomor_punggung');
            $users = [];
            foreach ($data as &$row) {
                /** @var \App\Models\Transaksi|null $t */
                $t = $trxs->get($row['nomor_punggung']);
                $row['paired'] = (bool) $t;
                $row['pendaftar_id'] = null; // deprecated
                $row['paired_at'] = $t ? optional($t->paired_at)->toDateTimeString() : null;
                if ($t && $t->peserta_id) {
                    if (!array_key_exists($t->peserta_id, $users)) {
                        $users[$t->peserta_id] = \App\Models\User::find($t->peserta_id);
                    }
                    $u = $users[$t->peserta_id];
                    $row['user_id'] = $t->peserta_id;
                    $row['user_name'] = $u ? ($u->name ?? null) : null;
                    $row['user_email'] = $u ? ($u->email ?? null) : null;
                } else {
                    $row['user_id'] = null;
                    $row['user_name'] = null;
                    $row['user_email'] = null;
                }
            }
            unset($row);
        }

        return response()->json([
            'data' => $data,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => (int)ceil($total / $perPage),
            ],
        ]);
    }

    // Generate QR codes for a range of nomor punggung
    public function generate(Request $request)
    {
        $request->validate([
            'start' => 'required|integer|min:1',
            'end' => 'required|integer|gte:start',
            'size' => 'nullable|integer|min:64|max:1024',
        ]);
        $start = (int)$request->input('start');
        $end = (int)$request->input('end');
        $size = (int)$request->input('size', 300);

        // Force GD driver so it doesn't require Imagick
        // Supports both common config keys depending on package version
        config([
            'simple-qrcode.image_driver' => 'gd',
            'qr-code.writer' => 'gd',
        ]);

        $dir = public_path('qrcodes');
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $created = 0;
        $skipped = 0;
        for ($i = $start; $i <= $end; $i++) {
            $nomor = str_pad($i, 5, '0', STR_PAD_LEFT);
            $qrPath = $dir . DIRECTORY_SEPARATOR . $nomor . '.png';
            if (file_exists($qrPath)) {
                $skipped++;
                continue;
            }
            QrCode::format('png')->size($size)->generate($nomor, $qrPath);
            $created++;
        }

        return response()->json([
            'start' => $start,
            'end' => $end,
            'created' => $created,
            'skipped' => $skipped,
        ]);
    }

    // Pair nomor punggung to a transaction (by nomor)
    public function pair(Request $request)
    {
        $request->validate([
            'nomor_punggung' => 'required|string',
            'transaction_id' => 'nullable|integer|required_without:invoice',
            'invoice' => 'nullable|string|required_without:transaction_id',
        ]);

        $nomor = $request->input('nomor_punggung');
        $invoice = $request->input('invoice');
        $transactionId = $request->filled('transaction_id') ? (int) $request->input('transaction_id') : null;
        // check if nomor already paired in transactions
        $existingTrxWithNomor = \App\Models\Transaksi::where('nomor_punggung', $nomor)->first();
        if ($existingTrxWithNomor) {
            return response()->json(['success' => false, 'message' => 'Nomor punggung already paired.'], 409);
        }
        // Find transaction and ensure it's successful
        /** @var \App\Models\Transaksi $trx */
        if ($invoice) {
            $trx = \App\Models\Transaksi::where('invoice', $invoice)->firstOrFail();
        } else {
            $trx = \App\Models\Transaksi::findOrFail($transactionId);
        }
        // prevent same invoice being paired more than once
        if (!empty($trx->nomor_punggung)) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice sudah dipair dengan nomor: ' . $trx->nomor_punggung,
            ], 409);
        }
        if (strtolower((string) $trx->status) !== 'success') {
            return response()->json(['success' => false, 'message' => 'Transaksi belum success. Pairing tidak diizinkan.'], 422);
        }
        // Set nomor + paired_at on the transaction, ensure peserta_id is present
        if (empty($trx->peserta_id) && $trx->email) {
            $user = \App\Models\User::where('email', $trx->email)->first();
            if ($user) {
                $trx->peserta_id = $user->id;
            }
        }
        $trx->nomor_punggung = $nomor;
        $trx->paired_at = now();
        $trx->save();

        return response()->json([
            'success' => true,
            'message' => 'Paired successfully.',
            'paired_at' => optional($trx->paired_at)->toDateTimeString(),
            'pendaftar_id' => null,
            'transaction' => [
                'id' => $trx->id,
                'paired_at' => optional($trx->paired_at)->toDateTimeString(),
                'peserta_id' => $trx->peserta_id,
            ],
        ]);
    }

    // Unpair nomor punggung from transaction
    public function unpair(Request $request)
    {
        $request->validate([
            'nomor_punggung' => 'required|string',
        ]);

        $nomor = $request->input('nomor_punggung');
        $trx = \App\Models\Transaksi::where('nomor_punggung', $nomor)->first();
        if (!$trx) {
            return response()->json(['success' => false, 'message' => 'Pairing not found.']);
        }
        $trx->nomor_punggung = null;
        $trx->paired_at = null;
        $trx->save();

        return response()->json(['success' => true, 'message' => 'Unpaired successfully.']);
    }
}
