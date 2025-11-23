<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\Participant;
use App\Models\User;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class OfflineImportApiController extends Controller
{
    /**
     * POST /api/v1/offline-import
     * Auth: auth:api
     * Upload CSV that matches sample headers and create offline transactions + participants
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();
        if (!$path) {
            return response()->json(['message' => 'Failed to read uploaded file'], 422);
        }

        $expected = [
            'invoice','user_uid','ticket_id','participant_name','participant_email','participant_phone','participant_nik','participant_province','participant_city','shirt_size','amount','status_racepack'
        ];
        $altHeaders = ['NO','NAMA','ANSI,KOMUNI','NO HP','UKURAN BAJU','NIP','EMAIL'];

        $handle = fopen($path, 'r');
        if ($handle === false) {
            return response()->json(['message' => 'Cannot open CSV'], 422);
        }

        $header = fgetcsv($handle);
        $mode = 'standard';
        if ($header && array_map('strval', $header) === $expected) {
            $mode = 'standard';
        } elseif ($header && array_map('strval', $header) === $altHeaders) {
            $mode = 'alt';
        } else {
            return response()->json(['message' => 'CSV header mismatch. Expected either: ['.implode(',', $expected).'] or ['.implode(',', $altHeaders).']'], 422);
        }

        $rows = [];
        while (($data = fgetcsv($handle)) !== false) {
            if (!is_array($data)) continue;
            if ($mode === 'standard') {
                if (count($data) < count($expected)) continue;
                $rows[] = array_combine($expected, array_slice($data, 0, count($expected)));
            } else {
                if (count($data) < count($altHeaders)) continue;
                $rows[] = array_combine($altHeaders, array_slice($data, 0, count($altHeaders)));
            }
        }
        fclose($handle);

        if (empty($rows)) {
            return response()->json(['message' => 'No data rows found'], 422);
        }

        // Prepare request fallbacks for alt mode
        $defaultTicketId = (int) $request->input('ticket_id', 0);
        $defaultUserUid = $request->input('user_uid');
        $defaultInvoice = $request->input('invoice');
        $defaultProvince = $request->input('province');
        $defaultCity = $request->input('city');

        if ($mode === 'alt' && $defaultTicketId <= 0) {
            return response()->json(['message' => 'ticket_id is required for this CSV format'], 422);
        }

        $grouped = collect(['ALL' => $rows]);
        $created = 0; $errors = [];
        $allParticipantIds = [];

        foreach ($grouped as $invoice => $items) {
            try {
                DB::transaction(function () use ($invoice, $items, &$created, $mode, $defaultUserUid, $defaultProvince, $defaultCity, $defaultTicketId, $defaultInvoice, &$allParticipantIds) {
                    $first = collect($items)->first();
                    if ($mode === 'standard') {
                        $uid = trim((string) $first['user_uid']);
                    } else {
                        $uid = trim((string) ($defaultUserUid ?: ($first['EMAIL'] ?? '')));
                        // fallback uid from email local part
                        if ($uid === '' && !empty($first['EMAIL'])) {
                            $uid = strstr($first['EMAIL'], '@', true) ?: null;
                        }
                    }
                    if (!$uid) {
                        throw new \RuntimeException('user_uid is empty for invoice ' . $invoice);
                    }

                    // Ensure user by uid
                    $user = User::where('uid', $uid)->first();
                    if (!$user) {
                        $user = User::create([
                            'uid' => $uid,
                            'name' => $mode === 'standard' ? ($first['participant_name'] ?? ('Offline-'.$uid)) : ($first['NAMA'] ?? ('Offline-'.$uid)),
                            'email' => $mode === 'standard' ? ($first['participant_email'] ?? null) : ($first['EMAIL'] ?? null),
                            'no_hp' => $mode === 'standard' ? ($first['participant_phone'] ?? null) : ($first['NO HP'] ?? null),
                            'nik' => $mode === 'standard' ? ($first['participant_nik'] ?? null) : ($first['NIP'] ?? null),
                            'province' => $mode === 'standard' ? ($first['participant_province'] ?? null) : ($defaultProvince ?: null),
                            'city' => $mode === 'standard' ? ($first['participant_city'] ?? null) : ($defaultCity ?: null),
                            'password' => $mode === 'standard' ? ($first['participant_phone'] ?? Str::random(8)) : (($first['NO HP'] ?? null) ?: Str::random(8)),
                        ]);
                    }

                    $participants = [];
                    $ticketIds = [];
                    $amount = 0;
                    foreach ($items as $r) {
                        $ticketId = (int) ($mode === 'standard' ? ($r['ticket_id'] ?? 0) : $defaultTicketId);
                        if ($ticketId > 0) { $ticketIds[] = $ticketId; }
                        if ($mode === 'standard') {
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
                                'status' => 1,
                            ];
                            $amount += (int) ($r['amount'] ?? 0);
                        } else {
                            $participants[] = [
                                'ticketId' => $ticketId,
                                'name' => (string) ($r['NAMA'] ?? ''),
                                'email' => (string) ($r['EMAIL'] ?? ''),
                                'phone' => (string) ($r['NO HP'] ?? ''),
                                'nik' => (string) ($r['NIP'] ?? ''),
                                'province' => (string) ($defaultProvince ?: ''),
                                'city' => (string) ($defaultCity ?: ''),
                                'shirt_size' => isset($r['UKURAN BAJU']) && $r['UKURAN BAJU'] !== '' ? (string) $r['UKURAN BAJU'] : null,
                                'status_racepack' => 'belum',
                                'status' => 1,
                            ];
                            $amount += 0; // amount unknown in alt format, treat as 0
                        }
                    }

                    // Always generate a unique invoice ID, do not use CSV-provided invoice
                    $no_invoice = 'TRX-'.Str::upper(Str::random(10));
                    while (Transaksi::where('invoice', $no_invoice)->exists()) {
                        $no_invoice = 'TRX-'.Str::upper(Str::random(10));
                    }

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
                        'note' => 'Offline import (API)',
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

                    $localIds = [];
                    foreach ($participants as $p) {
                        // set participant amount by event price if available (to satisfy blast filters amount>100000)
                        $eventPrice = null;
                        if (!empty($p['ticketId'])) {
                            $ev = Event::find($p['ticketId']);
                            if ($ev) { $eventPrice = (int) $ev->harga; }
                        }
                        $pid = 'PID-'.Str::upper(Str::random(8));
                        Participant::create([
                            'transaction_id' => $trx->id,
                            'participant_id' => $pid,
                            'name' => $p['name'],
                            'nik' => $p['nik'] ?? null,
                            'email' => $p['email'] ?? null,
                            'phone' => $p['phone'] ?? null,
                            'province' => $p['province'] ?? null,
                            'city' => $p['city'] ?? null,
                            'shirt_size' => $p['shirt_size'] ?? null,
                            'ticket_id' => $p['ticketId'] ?? null,
                            'status_racepack' => $p['status_racepack'] ?? 'belum',
                            'status' => $p['status'] ?? 1,
                            'amount' => $eventPrice,
                        ]);
                        $localIds[] = $pid;
                    }

                    // collect for notifications
                    $allParticipantIds = array_merge($allParticipantIds, $localIds);
                    $created++;
                });
            } catch (\Throwable $e) {
                Log::error('Offline import API failed', ['invoice' => $invoice, 'error' => $e->getMessage()]);
                $errors[] = 'Invoice '.$invoice.': '.$e->getMessage();
            }
        }

        // Optional notifications
        if ($request->boolean('send_notifications', false) && !empty($allParticipantIds)) {
            try {
                $pc = new \App\Http\Controllers\Api\V1\Admin\PendaftarController();
                $blastReq = new \Illuminate\Http\Request([
                    'participant_ids' => $allParticipantIds,
                    'use_default_template' => true,
                ]);
                // WhatsApp
                $pc->whatsappBlast($blastReq);
                // Email
                $pc->emailBlast($blastReq);
            } catch (\Throwable $e) {
                Log::warning('send_notifications failed', ['error' => $e->getMessage()]);
            }
        }

        return response()->json([
            'message' => 'Import selesai',
            'created' => $created,
            'errors' => $errors,
        ], empty($errors) ? 200 : 207);
    }
}
