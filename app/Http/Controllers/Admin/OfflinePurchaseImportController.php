<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Transaksi;
use App\Models\User;
use App\Models\Participant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use SpreadsheetReader;
use Carbon\Carbon;

class OfflinePurchaseImportController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('transaksi_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        return view('admin.offline_import.index');
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('transaksi_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $request->validate([
            'csv_file' => 'required|mimes:csv,txt',
        ]);

        $file = $request->file('csv_file');
        $path = $file->path();
        $reader = new SpreadsheetReader($path);

        // Expected headers (order matters)
        $expected = [
            'invoice','user_uid','ticket_id','participant_name','participant_email','participant_phone','participant_nik','participant_province','participant_city','shirt_size','amount','status_racepack'
        ];

        $headers = $reader->current();
        if (!$headers || array_map('strval', $headers) !== $expected) {
            return back()->withErrors(['csv_file' => 'Header CSV tidak sesuai. Harus: '.implode(',', $expected)])->withInput();
        }

        $rows = [];
        while ($reader->next() !== false) {
            $row = $reader->current();
            if (!is_array($row) || count($row) < count($expected)) {
                continue;
            }
            $rows[] = array_combine($expected, array_slice($row, 0, count($expected)));
        }

        if (empty($rows)) {
            return back()->withErrors(['csv_file' => 'CSV kosong atau tidak ada baris data.']);
        }

        $grouped = collect(['ALL' => $rows]);
        $created = 0; $errors = [];

        foreach ($grouped as $invoice => $items) {
            try {
                DB::transaction(function () use ($invoice, $items, &$created) {
                    $first = collect($items)->first();
                    $uid = trim((string) $first['user_uid']);
                    if ($uid === '') {
                        throw new \RuntimeException('user_uid kosong untuk invoice '.$invoice);
                    }

                    // Ensure user by uid
                    $user = User::where('uid', $uid)->first();
                    if (!$user) {
                        $user = User::create([
                            'uid' => $uid,
                            'name' => $first['participant_name'] ?? ('Offline-'.$uid),
                            'email' => $first['participant_email'] ?? null,
                            'no_hp' => $first['participant_phone'] ?? null,
                            'nik' => $first['participant_nik'] ?? null,
                            'province' => $first['participant_province'] ?? null,
                            'city' => $first['participant_city'] ?? null,
                            'password' => $first['participant_phone'] ?? Str::random(8),
                        ]);
                    }

                    // Build participants payload from rows
                    $participants = [];
                    $ticketIds = [];
                    $amount = 0;
                    foreach ($items as $r) {
                        $ticketId = (int) ($r['ticket_id'] ?? 0);
                        if ($ticketId > 0) { $ticketIds[] = $ticketId; }
                        $participants[] = [
                            'ticketId' => $ticketId,
                            'name' => (string) $r['participant_name'],
                            'email' => (string) $r['participant_email'],
                            'phone' => (string) $r['participant_phone'],
                            'nik' => (string) $r['participant_nik'],
                            'province' => (string) $r['participant_province'],
                            'city' => (string) $r['participant_city'],
                            'shirt_size' => $r['shirt_size'] !== '' ? (string) $r['shirt_size'] : null,
                            'status_racepack' => $r['status_racepack'] !== '' ? (string) $r['status_racepack'] : 'belum',
                            'status' => 0,
                        ];
                        $amount += (int) ($r['amount'] ?? 0);
                    }

                    $no_invoice = 'TRX-'.Str::upper(Str::random(10));

                    $trx = Transaksi::create([
                        'invoice' => $no_invoice,
                        'events' => json_encode(array_values(collect($ticketIds)->unique()->values()->all())),
                        'peserta_id' => 63,
                        'created_by_id' => 63,
                        'amount' => $amount,
                        'final_price' => $amount,
                        'discount' => 0,
                        'service_fee' => 0,
                        'ppn' => 0,
                        'type' => 'offline',
                        'note' => 'Offline import',
                        'status' => 'success',
                        'province' => $user->province,
                        'city' => $user->city,
                        'no_hp' => $user->no_hp,
                        'nik' => $user->nik,
                        'email' => 'ifailamir@gmail.com',
                        'nama' => $user->name,
                        'expired_snap_time' => Carbon::now(),
                        'participants' => json_encode($participants),
                    ]);

                    // Auto backfill participants like payment success
                    try {
                        // Create Participant records from payload
                        foreach ($participants as $p) {
                            Participant::create([
                                'transaction_id' => $trx->id,
                                'participant_id' => 'PID-'.Str::upper(Str::random(8)),
                                'name' => $p['name'],
                                'nik' => $p['nik'] ?? null,
                                'email' => $p['email'] ?? null,
                                'phone' => $p['phone'] ?? null,
                                'province' => $p['province'] ?? null,
                                'city' => $p['city'] ?? null,
                                'shirt_size' => $p['shirt_size'] ?? null,
                                'ticket_id' => $p['ticketId'] ?? null,
                                'status_racepack' => $p['status_racepack'] ?? 'belum',
                                'status' => $p['status'] ?? 0,
                                'amount' => null,
                            ]);
                        }
                    } catch (\Throwable $e) {
                        Log::warning('Offline import participants create failed', ['invoice' => $trx->invoice, 'error' => $e->getMessage()]);
                    }

                    $created++;
                });
            } catch (\Throwable $e) {
                $errors[] = 'Invoice '.$invoice.': '.$e->getMessage();
                Log::error('Offline import failed', ['invoice' => $invoice, 'error' => $e->getMessage()]);
            }
        }

        if (!empty($errors)) {
            return back()->with('message', "Selesai dengan sebagian error. Berhasil: {$created}. Error: ".count($errors))->with('errors_list', $errors);
        }

        return redirect()->route('admin.transactions.index')->with('message', "Import selesai. Transaksi dibuat: {$created}");
    }
}
