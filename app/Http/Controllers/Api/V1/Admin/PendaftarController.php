<?php

namespace App\Http\Controllers\Api\V1\Admin;

use Midtrans\Snap;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\CsvImportTrait;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Http\Requests\MassDestroyPendaftarRequest;
use App\Http\Requests\StorePendaftarRequest;
use App\Http\Requests\UpdatePendaftarRequest;
use App\Http\Resources\Admin\PendaftarResource;
use App\Models\Event;
use App\Models\Transaksi;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\Response;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use stdClass;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Tiket;
use App\Models\Participant;
use OpenApi\Annotations as OA;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Pendaftar;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\WhatsAppNotification;
use App\Models\PromoCode;
use App\Models\Referal;

class PendaftarController extends Controller
{
    public function __construct()
    {
        // Set midtrans configuration
        \Midtrans\Config::$serverKey = config('services.midtrans.serverKey');
        \Midtrans\Config::$isProduction = config('services.midtrans.isProduction');
        \Midtrans\Config::$isSanitized = config('services.midtrans.isSanitized');
        \Midtrans\Config::$is3ds = config('services.midtrans.is3ds');
    }

    // Build the exact same base query used by racepackList for reuse in exports
    private function buildRacepackBase(Request $request)
    {
        $status = $request->input('status');
        $staffId = $request->input('staff_id');
        $staffName = $request->input('staff_name');
        $search = $request->input('search');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $includeTesting = (bool) $request->boolean('include_testing', false);

        $excluded_emails = explode(',', env('EMAIL_TESTING', ''));

        $base = Participant::with(['staff:id,name'])
            ->select('participants.*')
            ->where('status', '1');
        if (!$includeTesting && !empty($excluded_emails)) {
            $base->whereNotIn('email', $excluded_emails);
        }
        if ($staffId) {
            $base->where('staff_user_id', $staffId);
        }
        if ($staffName) {
            $base->where('racepack_by', 'like', "%{$staffName}%");
        }
        if ($search) {
            $base->where(function ($q) use ($search) {
                $q->where('participant_id', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        if ($dateFrom || $dateTo) {
            try {
                $from = $dateFrom ? \Carbon\Carbon::parse($dateFrom)->startOfDay() : null;
                $to = $dateTo ? \Carbon\Carbon::parse($dateTo)->endOfDay() : null;
                $applyRange = function ($q, $column) use ($from, $to) {
                    if ($from && $to) {
                        $q->whereBetween($column, [$from, $to]);
                    } elseif ($from) {
                        $q->where($column, '>=', $from);
                    } elseif ($to) {
                        $q->where($column, '<=', $to);
                    }
                };
                if ($status === 'sudah') {
                    $applyRange($base, 'racepack_at');
                } elseif ($status === 'belum') {
                    $applyRange($base, 'created_at');
                }
                // When status is empty/all, do NOT apply date filter to avoid filtering out all rows
                // (the OR logic between sudah/belum with different date columns is too restrictive)
            } catch (\Throwable $e) { /* ignore */
            }
        }

        return $base;
    }

    /**
     * Build unified WhatsApp message text for payment success using the latest template.
     */
    protected function buildPaymentSuccessText(string $name, string $participantId, string $ticketLabel): string
    {
        $dashboardUrl = 'https://daftar.mandalikakorprirun.com/dashboard/';
        $lines = [];
        $lines[] = 'Halo Bapak/Ibu ' . trim($name) . ',';
        $lines[] = 'E-Ticket Mandalika Korpri Run Anda sudah terbit ✅';
        $lines[] = '';
        $lines[] = '🆔 ID Peserta: ' . $participantId;
        $lines[] = '👤 Nama: ' . trim($name);
        $lines[] = '🎟️ Jenis Tiket: ' . $ticketLabel;
        $lines[] = '';
        $lines[] = 'Silakan cek email atau login ke ' . $dashboardUrl . ' untuk mengunduh QR E-Ticket.';
        $lines[] = '';
        $lines[] = 'Jika ada kendala, hubungi kami melalui WA ini.';
        $lines[] = 'Terima kasih 🙏';
        return implode("\n", $lines);
    }

    /**
     * Build WhatsApp message with conditional format based on whether
     * participant email equals purchaser email, including direct ticket link.
     */
    protected function buildWhatsappTicketText(string $name, string $participantId, string $ticketLabel, ?string $participantEmail, ?string $purchaserEmail): string
    {
        $name = trim($name);
        $pid = (string) $participantId;
        $pidCode = preg_replace('/^PID-/', '', $pid);
        $ticketUrl = 'https://daftar.mandalikakorprirun.com/ticket?pid=' . $pidCode;

        $same = false;
        if ($participantEmail && $purchaserEmail) {
            $same = strtolower(trim($participantEmail)) === strtolower(trim($purchaserEmail));
        }

        $lines = [];
        $lines[] = 'Halo Bapak/Ibu ' . $name . ',';
        $lines[] = 'E-Ticket Mandalika Korpri Run Anda sudah terbit ✅';
        $lines[] = '';
        if ($same) {
            $lines[] = '🆔 ID Peserta: ' . $pid;
            $lines[] = '👤 Nama: ' . $name;
            $lines[] = '🎟️ Jenis Tiket: ' . $ticketLabel;
            $lines[] = '';
            $lines[] = 'Unduh Tiket:';
            $lines[] = $ticketUrl;
            $lines[] = '';
            $lines[] = 'Silakan gunakan link LANGSUNG di bawah ini untuk mengunduh E-Ticket Anda:';
            $lines[] = $ticketUrl;
            $lines[] = '';
            $lines[] = '---';
            $lines[] = '';
            $lines[] = 'Jika ada kendala, hubungi kami melalui WA ini.';
            $lines[] = 'Terima kasih 🙏';
        } else {
            $lines[] = '[PENTING] Anda menerima E-Ticket ini karena data Anda didaftarkan oleh seorang Pemesan (atas nama Anda).';
            $lines[] = '';
            $lines[] = 'Berikut adalah detail tiket Anda:';
            $lines[] = '';
            $lines[] = '🆔 ID Peserta: ' . $pid;
            $lines[] = '👤 Nama: ' . $name;
            $lines[] = '🎟️ Jenis Tiket: ' . $ticketLabel;
            if (!empty($purchaserEmail)) {
                $lines[] = '📧 Dipesankan oleh: ' . $purchaserEmail;
            }
            $lines[] = '';
            $lines[] = 'Anda TIDAK PERLU LOGIN untuk mengunduh tiket.';
            $lines[] = '';
            $lines[] = 'Silakan gunakan link LANGSUNG di bawah ini untuk mengunduh E-Ticket Anda:';
            $lines[] = $ticketUrl;
            $lines[] = '';
            $lines[] = '---';
            $lines[] = '';
            $lines[] = 'Ada kendala?';
            $lines[] = '• Jika terdapat KESALAHAN DATA (nama, no. HP, dll), silakan hubungi Pemesan.';
            $lines[] = '• Jika LINK UNDUHAN bermasalah, bisa hubungi kami (Admin) melalui WA ini.';
            $lines[] = '';
            $lines[] = 'Terima kasih 🙏';
        }

        return implode("\n", $lines);
    }

    /**
     * Blast WhatsApp messages to registered participants.
     * POST /api/v1/participants/whatsapp-blast
     * Body:
     * - participant_ids: array<string> (optional) list of participant_id to send to
     * - text: string (optional) custom message text
     * - use_default_template: bool (optional) use payment success template per participant
     * - send_all: bool (optional) if true, send to all registered participants (status=1, amount>100000, exclude EMAIL_TESTING)
     * - search: string (optional) filter by participant_id/name/email/phone when send_all=true
     */
    public function whatsappBlast(Request $request)
    {
        $request->validate([
            'participant_ids' => 'nullable|array',
            'participant_ids.*' => 'string',
            'text' => 'nullable|string',
            'use_default_template' => 'nullable|boolean',
            'send_all' => 'nullable|boolean',
            'search' => 'nullable|string',
            'is_invite' => 'nullable|boolean',
        ]);

        $useTemplate = (bool) $request->boolean('use_default_template', false);
        $sendAll = (bool) $request->boolean('send_all', false);
        $isInvite = filter_var($request->input('is_invite'), FILTER_VALIDATE_BOOLEAN);
        $text = (string) $request->input('text', '');
        $search = (string) $request->input('search', '');

        // Build base query for participants
        $excluded_emails = explode(',', env('EMAIL_TESTING', ''));
        $includeTesting = (bool) $request->boolean('include_testing', false);
        $base = Participant::query()
            ->select(['id', 'transaction_id', 'participant_id', 'name', 'email', 'phone', 'ticket_id', 'blast', 'invite'])
            ->where('status', '1')
            ->whereNotIn('email', $excluded_emails);

        if ($sendAll) {
            if ($search) {
                $base->where(function ($q) use ($search) {
                    $q->where('participant_id', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            }
        } else {
            $ids = (array) $request->input('participant_ids', []);
            if (empty($ids)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'participant_ids required if send_all is not true',
                ], 422);
            }
            $base->whereIn('participant_id', array_values($ids));
        }

        $list = $base->orderBy('id')->get();
        if ($list->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No participants found for the given criteria',
            ], 404);
        }

        // Prepare event name mapping (ticket_id => nama_event)
        $ticketIds = $list->pluck('ticket_id')->filter()->unique()->values();
        $eventName = collect();
        if ($ticketIds->isNotEmpty()) {
            $tickets = Event::withTrashed()->whereIn('id', $ticketIds)->get(['id', 'nama_event']);
            $eventName = $tickets->keyBy('id')->map(function ($t) {
                if ($t->id == 1)
                    return 'TIKET UNTUK ASN';
                if ($t->id == 2)
                    return 'TIKET UNTUK UMUM';
                return $t->nama_event ?? ('Event #' . $t->id);
            });
        }

        $dashboardUrl = 'https://daftar.mandalikakorprirun.com/dashboard/';
        $results = [];
        $success = 0;
        $failed = 0;
        $skipped = 0;

        $processedPhones = []; // Track phones processed in this batch

        // Pre-fetch phones that have already been invited globally
        $alreadyInvitedPhones = [];
        if ($isInvite) {
            $phonesToCheck = $list->pluck('phone')->filter()->unique()->toArray();
            if (!empty($phonesToCheck)) {
                $alreadyInvitedPhones = Participant::whereIn('phone', $phonesToCheck)
                    ->where('invite', 1)
                    ->pluck('phone')
                    ->unique()
                    ->toArray();
            }
        }

        foreach ($list as $p) {
            $alreadySent = $isInvite ? ($p->invite == 1) : ($p->blast == 1);

            if ($alreadySent) {
                $skipped++;
                $results[] = [
                    'participant_id' => $p->participant_id,
                    'phone' => $p->phone,
                    'status' => 'skipped',
                    'message' => $isInvite ? 'Already invited' : 'Already blasted',
                    'debug_is_invite' => $isInvite,
                    'debug_invite_val' => $p->invite,
                    'debug_blast_val' => $p->blast,
                ];
                continue;
            }

            if (empty($p->phone)) {
                $failed++;
                $results[] = [
                    'participant_id' => $p->participant_id,
                    'phone' => $p->phone,
                    'status' => 'error',
                    'error' => 'No phone number',
                ];
                continue;
            }

            // Check if this phone number has already been invited (globally check)
            if ($isInvite && in_array($p->phone, $alreadyInvitedPhones)) {
                $skipped++;
                $results[] = [
                    'participant_id' => $p->participant_id,
                    'phone' => $p->phone,
                    'status' => 'skipped',
                    'message' => 'Phone number already invited (global check)',
                    'debug_is_invite' => $isInvite,
                ];
                continue;
            }

            // Check for duplicate phone in this batch (only for invites)
            if ($isInvite && in_array($p->phone, $processedPhones)) {
                $skipped++;
                $results[] = [
                    'participant_id' => $p->participant_id,
                    'phone' => $p->phone,
                    'status' => 'skipped',
                    'message' => 'Duplicate phone number in this batch',
                ];
                continue;
            }

            try {
                $msg = $text;
                if ($useTemplate || $msg === '') {
                    $tid = $p->ticket_id;
                    if ($tid == 1) {
                        $jenis = 'TIKET UNTUK ASN';
                    } elseif ($tid == 2) {
                        $jenis = 'TIKET UNTUK UMUM';
                    } else {
                        $jenis = $tid ? ($eventName[$tid] ?? ('Event #' . $tid)) : 'Tiket';
                    }
                    $order = $p->transaction_id ? Transaksi::find($p->transaction_id) : null;
                    $purchaserEmail = $order->email ?? null;
                    $msg = $this->buildWhatsappTicketText(($p->name ?? 'Peserta'), (string) $p->participant_id, $jenis, $p->email ?? null, $purchaserEmail);
                }
                $this->sendWhatsapp($p->phone, $msg, $dashboardUrl);

                // Update blast/invite flags
                if ($isInvite) {
                    $p->invite = 1;
                    $p->save();
                    // Note: Transactions don't have invite flag usually, or we don't update it for now
                } else {
                    $p->blast = 1;
                    $p->save();
                    if ($p->transaction_id) {
                        Transaksi::where('id', $p->transaction_id)->update(['blast' => 1]);
                    }
                }

                $success++;
                $processedPhones[] = $p->phone; // Add to processed list
                $results[] = [
                    'participant_id' => $p->participant_id,
                    'phone' => $p->phone,
                    'status' => 'success',
                ];
            } catch (\Throwable $e) {
                $failed++;
                $results[] = [
                    'participant_id' => $p->participant_id,
                    'phone' => $p->phone,
                    'status' => 'error',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'status' => 'ok',
            'mode' => $sendAll ? 'all' : 'selected',
            'total' => $list->count(),
            'success' => $success,
            'failed' => $failed,
            'skipped' => $skipped,
            'results' => $results,
            'debug_meta' => [
                'is_invite_received' => $isInvite,
                'is_invite_raw' => $request->input('is_invite'),
            ]
        ]);
    }

    public function whatsappBlastTransactions(Request $request)
    {
        $request->validate([
            'transaction_ids' => 'nullable|array',
            'transaction_ids.*' => 'integer',
            'text' => 'nullable|string',
            'use_default_template' => 'nullable|boolean',
            'send_all' => 'nullable|boolean',
            'search' => 'nullable|string',
        ]);

        $useTemplate = (bool) $request->boolean('use_default_template', false);
        $sendAll = (bool) $request->boolean('send_all', false);
        $text = (string) $request->input('text', '');
        $search = (string) $request->input('search', '');

        $excluded_emails = explode(',', env('EMAIL_TESTING', ''));
        $base = Transaksi::query()
            ->where('status', 'success')
            ->where('amount', '>', 100000)
            ->whereNotIn('email', $excluded_emails);

        if ($sendAll) {
            if ($search) {
                $kw = "%{$search}%";
                $base->where(function ($q) use ($kw) {
                    $q->where('invoice', 'like', $kw)
                        ->orWhere('nama', 'like', $kw)
                        ->orWhere('email', 'like', $kw)
                        ->orWhere('no_hp', 'like', $kw);
                });
            }
        } else {
            $ids = (array) $request->input('transaction_ids', []);
            if (empty($ids)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'transaction_ids required if send_all is not true',
                ], 422);
            }
            $base->whereIn('id', array_values($ids));
        }

        $list = $base->orderBy('id')->get();
        if ($list->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No transactions found for the given criteria',
            ], 404);
        }

        $eventIds = $list->pluck('event_id')->filter()->unique()->values();
        $eventName = collect();
        if ($eventIds->isNotEmpty()) {
            $events = Event::withTrashed()->whereIn('id', $eventIds)->get(['id', 'nama_event']);
            $eventName = $events->keyBy('id')->map(function ($t) {
                if ($t->id == 1)
                    return 'TIKET UNTUK ASN';
                if ($t->id == 2)
                    return 'TIKET UNTUK UMUM';
                return $t->nama_event ?? ('Event #' . $t->id);
            });
        }

        $dashboardUrl = 'https://daftar.mandalikakorprirun.com/dashboard/';
        $results = [];
        $success = 0;
        $failed = 0;
        $skipped = 0;

        foreach ($list as $t) {
            // If this transaction already has participants, send per-participant ticket message with direct link
            $participants = $t->participants()->get(['participant_id', 'name', 'email', 'phone', 'ticket_id', 'blast']);
            if ($participants && $participants->count() > 0) {
                foreach ($participants as $p) {
                    $alreadySent = $isInvite ? ($p->invite == 1) : ($p->blast == 1);
                    if ($alreadySent) {
                        $skipped++;
                        $results[] = [
                            'transaction_id' => $t->id,
                            'invoice' => $t->invoice,
                            'participant_id' => $p->participant_id,
                            'phone' => $p->phone,
                            'status' => 'skipped',
                            'message' => $isInvite ? 'Already invited' : 'Already blasted',
                        ];
                        continue;
                    }

                    $destPhone = $p->phone ?: ($t->no_hp ?? null);
                    if (empty($destPhone)) {
                        $failed++;
                        $results[] = [
                            'transaction_id' => $t->id,
                            'invoice' => $t->invoice,
                            'participant_id' => $p->participant_id,
                            'phone' => $destPhone,
                            'status' => 'error',
                            'error' => 'No phone number',
                        ];
                        continue;
                    }

                    try {
                        $msg = $text;
                        if ($useTemplate || $msg === '') {
                            $tid = $p->ticket_id ?? $t->event_id;
                            if ($tid == 1) {
                                $jenis = 'TIKET UNTUK ASN';
                            } elseif ($tid == 2) {
                                $jenis = 'TIKET UNTUK UMUM';
                            } else {
                                $jenis = $tid ? ($eventName[$tid] ?? ('Event #' . $tid)) : 'Tiket';
                            }
                            $purchaserEmail = $t->email ?? null;
                            $msg = $this->buildWhatsappTicketText(($p->name ?? 'Peserta'), (string) $p->participant_id, $jenis, $p->email ?? null, $purchaserEmail);
                        }
                        $this->sendWhatsapp($destPhone, $msg, $dashboardUrl);

                        // Update blast/invite flags
                        if ($isInvite) {
                            $p->invite = 1;
                            $p->save();
                        } else {
                            $p->blast = 1;
                            $p->save();
                            Transaksi::where('id', $t->id)->update(['blast' => 1]);
                        }

                        $success++;
                        $results[] = [
                            'transaction_id' => $t->id,
                            'invoice' => $t->invoice,
                            'participant_id' => $p->participant_id,
                            'phone' => $destPhone,
                            'status' => 'success',
                        ];
                    } catch (\Throwable $e) {
                        $failed++;
                        $results[] = [
                            'transaction_id' => $t->id,
                            'invoice' => $t->invoice,
                            'participant_id' => $p->participant_id,
                            'phone' => $destPhone,
                            'status' => 'error',
                            'error' => $e->getMessage(),
                        ];
                    }
                }
                continue; // next transaction after processing its participants
            }

            // Fallback: no participant rows yet, keep previous transaction-level message
            if ($t->blast == 1) {
                $skipped++;
                $results[] = [
                    'transaction_id' => $t->id,
                    'invoice' => $t->invoice,
                    'phone' => $t->no_hp,
                    'status' => 'skipped',
                    'message' => 'Already blasted',
                ];
                continue;
            }

            $phone = $t->no_hp ?? null;
            if (empty($phone)) {
                $failed++;
                $results[] = [
                    'transaction_id' => $t->id,
                    'invoice' => $t->invoice,
                    'phone' => $phone,
                    'status' => 'error',
                    'error' => 'No phone number',
                ];
                continue;
            }

            try {
                $msg = $text;
                if ($useTemplate || $msg === '') {
                    $buyer = trim((string) ($t->nama ?? 'Pembeli'));
                    $eid = $t->event_id;
                    if ($eid == 1) {
                        $ev = 'TIKET UNTUK ASN';
                    } elseif ($eid == 2) {
                        $ev = 'TIKET UNTUK UMUM';
                    } else {
                        $ev = $eid ? ($eventName[$eid] ?? ('Event #' . $eid)) : 'Tiket';
                    }
                    $lines = [];
                    $lines[] = 'Halo Bapak/Ibu ' . $buyer . ',';
                    $lines[] = 'Pembayaran Anda telah berhasil ✅';
                    $lines[] = '';
                    $lines[] = '🧾 Invoice: ' . (string) $t->invoice;
                    $lines[] = '🎟️ Event: ' . $ev;
                    $lines[] = '';
                    $lines[] = 'Silakan cek email atau login ke ' . $dashboardUrl . ' untuk mengelola tiket.';
                    $lines[] = '';
                    $lines[] = 'Jika ada kendala, hubungi kami melalui WA ini.';
                    $lines[] = 'Terima kasih 🙏';
                    $msg = implode("\n", $lines);
                }
                $this->sendWhatsapp($phone, $msg, $dashboardUrl);

                // Update blast flag on transaction only (no participants yet)
                Transaksi::where('id', $t->id)->update(['blast' => 1]);

                $success++;
                $results[] = [
                    'transaction_id' => $t->id,
                    'invoice' => $t->invoice,
                    'phone' => $phone,
                    'status' => 'success',
                ];
            } catch (\Throwable $e) {
                $failed++;
                $results[] = [
                    'transaction_id' => $t->id,
                    'invoice' => $t->invoice,
                    'phone' => $phone,
                    'status' => 'error',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'status' => 'ok',
            'mode' => $sendAll ? 'all' : 'selected',
            'total' => $list->count(),
            'success' => $success,
            'failed' => $failed,
            'results' => $results,
        ]);
    }

    /**
     * Blast Email messages to registered participants.
     * POST /api/v1/participants/email-blast
     * Body:
     * - participant_ids: array<string> (optional) list of participant_id to send to
     * - text: string (optional) custom message text
     * - use_default_template: bool (optional) use payment success template per participant
     * - send_all: bool (optional) if true, send to all registered participants (status=1, amount>100000, exclude EMAIL_TESTING)
     * - search: string (optional) filter by participant_id/name/email/phone when send_all=true
     */
    public function emailBlast(Request $request)
    {
        $request->validate([
            'participant_ids' => 'nullable|array',
            'participant_ids.*' => 'string',
            'text' => 'nullable|string',
            'use_default_template' => 'nullable|boolean',
            'send_all' => 'nullable|boolean',
            'search' => 'nullable|string',
        ]);

        $useTemplate = (bool) $request->boolean('use_default_template', false);
        $sendAll = (bool) $request->boolean('send_all', false);
        $text = (string) $request->input('text', '');
        $search = (string) $request->input('search', '');

        $excluded_emails = explode(',', env('EMAIL_TESTING', ''));
        $base = Participant::query()
            ->select(['id', 'transaction_id', 'participant_id', 'name', 'email', 'phone', 'ticket_id'])
            ->where('status', '1')
            ->whereNotIn('email', $excluded_emails);

        if ($sendAll) {
            if ($search) {
                $base->where(function ($q) use ($search) {
                    $q->where('participant_id', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            }
        } else {
            $ids = (array) $request->input('participant_ids', []);
            if (empty($ids)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'participant_ids required if send_all is not true',
                ], 422);
            }
            $base->whereIn('participant_id', array_values($ids));
        }

        $list = $base->orderBy('id')->get();
        if ($list->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No participants found for the given criteria',
            ], 404);
        }

        // Prepare event name mapping (ticket_id => nama_event)
        $ticketIds = $list->pluck('ticket_id')->filter()->unique()->values();
        $eventName = collect();
        if ($ticketIds->isNotEmpty()) {
            $tickets = Event::withTrashed()->whereIn('id', $ticketIds)->get(['id', 'nama_event']);
            $eventName = $tickets->keyBy('id')->map(function ($t) {
                if ($t->id == 1)
                    return 'TIKET UNTUK ASN';
                if ($t->id == 2)
                    return 'TIKET UNTUK UMUM';
                return $t->nama_event ?? ('Event #' . $t->id);
            });
        }

        $results = [];
        $success = 0;
        $failed = 0;

        foreach ($list as $p) {
            if (empty($p->email)) {
                $failed++;
                $results[] = [
                    'participant_id' => $p->participant_id,
                    'email' => $p->email,
                    'status' => 'error',
                    'error' => 'No email address',
                ];
                continue;
            }

            try {
                $msg = $text;
                if ($useTemplate || $msg === '') {
                    $tid = $p->ticket_id;
                    if ($tid == 1) {
                        $jenis = 'TIKET UNTUK ASN';
                    } elseif ($tid == 2) {
                        $jenis = 'TIKET UNTUK UMUM';
                    } else {
                        $jenis = $tid ? ($eventName[$tid] ?? ('Event #' . $tid)) : 'Tiket';
                    }
                    $msg = $this->buildPaymentSuccessText(($p->name ?? 'Peserta'), (string) $p->participant_id, $jenis);
                }

                // Send as a simple raw email to avoid creating a new Mailable
                Mail::raw($msg, function ($message) use ($p) {
                    $message->to($p->email)
                        ->subject('Informasi E-Ticket Mandalika Korpri Run');
                });

                $success++;
                $results[] = [
                    'participant_id' => $p->participant_id,
                    'email' => $p->email,
                    'status' => 'success',
                ];
            } catch (\Throwable $e) {
                $failed++;
                $results[] = [
                    'participant_id' => $p->participant_id,
                    'email' => $p->email,
                    'status' => 'error',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'status' => 'ok',
            'mode' => $sendAll ? 'all' : 'selected',
            'total' => $list->count(),
            'success' => $success,
            'failed' => $failed,
            'results' => $results,
        ]);
    }

    /**
     * Generate/backfill participants for a successful transaction.
     * POST /api/v1/participants/generate
     * Accepts: invoice (string) or transaction_id (int)
     */
    public function generateParticipants(Request $request)
    {
        $invoice = $request->input('invoice');
        $trxId = $request->input('transaction_id');

        if (!$invoice && !$trxId) {
            return response()->json(['message' => 'invoice or transaction_id is required'], 422);
        }

        $trxQuery = Transaksi::query();
        if ($trxId) {
            $trxQuery->where('id', $trxId);
        } else {
            $trxQuery->where('invoice', $invoice);
        }
        $trx = $trxQuery->first();

        if (!$trx) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }
        if ($trx->status !== 'success') {
            return response()->json(['message' => 'Transaction is not success'], 422);
        }

        // Run post-success actions (will backfill participants if JSON exists and generate QR)
        try {
            $this->postPaymentSuccessActions($trx);
        } catch (\Throwable $e) {
            Log::warning('generateParticipants failed', ['invoice' => $trx->invoice, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to generate participants', 'error' => $e->getMessage()], 500);
        }

        // Return participants summary
        $participants = $trx->participants()->get(['participant_id', 'name', 'email', 'phone', 'ticket_id', 'status_racepack']);
        return response()->json([
            'message' => 'Participants generated',
            'invoice' => $trx->invoice,
            'count' => $participants->count(),
            'participants' => $participants,
        ]);
    }

    public function showParticipant($participant)
    {
        $p = Participant::where('participant_id', $participant)->first();
        if (!$p) {
            return response()->json(['message' => 'Participant not found'], 404);
        }
        //add jenis tiket dia
        $p->jenis_tiket = Event::where('id', $p->ticket_id)->first()->nama_event;
        return response()->json($p);
    }

    public function ticket($participant_id)
    {
        $p = Participant::where('participant_id', $participant_id)->first();
        if (!$p) {
            return response()->json(['message' => 'Participant not found'], 404);
        }
        $event = $p->ticket_id ? Event::select(['id', 'nama_event', 'harga', 'tanggal_mulai'])->find($p->ticket_id) : null;
        $participant = [
            'name' => $p->name,
            'phone' => $p->phone,
            'shirt_size' => $p->shirt_size,
            'participant_id' => $p->participant_id,
            'status_racepack' => $p->status_racepack,
            'status' => $p->status,
            'qr_url' => url("/storage/participants/{$p->participant_id}.png"),
        ];
        return response()->json([
            'event' => $event,
            'participant' => $participant,
        ]);
    }

    /**
     * Update shirt size for a participant
     * PUT /api/v1/participants/{participant_id}/shirt-size
     */
    public function updateShirtSize(Request $request, $participant_id)
    {
        // Support camelCase input: shirtSize
        $size = $request->input('shirtSize', $request->input('shirt_size'));
        if ($size !== null) {
            $request->merge(['shirt_size' => $size]);
        }

        $request->validate([
            'shirt_size' => 'required|string',
        ]);

        $participant = Participant::where('participant_id', $participant_id)->first();
        if (!$participant) {
            return response()->json(['message' => 'Participant not found'], 404);
        }

        $participant->update(['shirt_size' => $request->input('shirt_size')]);

        return response()->json([
            'message' => 'Shirt size updated successfully',
            'participant' => [
                'participant_id' => $participant->participant_id,
                'name' => $participant->name,
                'shirt_size' => $participant->shirt_size,
            ],
        ]);
    }

    /**
     * Bulk update shirt sizes for multiple participants
     * POST /api/v1/participants/bulk-update-shirt-size
     * Body: { "updates": [{"participant_id": "PID-XXX", "shirt_size": "L"}, ...] }
     */
    public function bulkUpdateShirtSize(Request $request)
    {
        // Normalize camelCase 'shirtSize' to snake_case 'shirt_size' for validation
        $incoming = $request->input('updates');
        if (is_array($incoming)) {
            $normalized = array_map(function ($u) {
                if (!isset($u['shirt_size']) && isset($u['shirtSize'])) {
                    $u['shirt_size'] = $u['shirtSize'];
                }
                return $u;
            }, $incoming);
            $request->merge(['updates' => $normalized]);
        }

        $request->validate([
            'updates' => 'required|array|min:1',
            'updates.*.participant_id' => 'required|string',
            'updates.*.shirt_size' => 'required|string',
        ]);

        $updates = $request->input('updates');
        $updated = [];
        $failed = [];

        foreach ($updates as $update) {
            $participant = Participant::where('participant_id', $update['participant_id'])->first();
            if (!$participant) {
                $failed[] = [
                    'participant_id' => $update['participant_id'],
                    'reason' => 'Participant not found',
                ];
                continue;
            }

            $participant->update(['shirt_size' => $update['shirt_size']]);
            $updated[] = [
                'participant_id' => $participant->participant_id,
                'name' => $participant->name,
                'shirt_size' => $participant->shirt_size,
            ];
        }

        return response()->json([
            'message' => 'Bulk update completed',
            'updated_count' => count($updated),
            'failed_count' => count($failed),
            'updated' => $updated,
            'failed' => $failed,
        ]);
    }

    /**
     * List successful transactions that do not have any participants rows yet.
     * GET /api/v1/participants/missing
     * Optional query params:
     * - search: string (search invoice or user name/email)
     * - date_from, date_to: filter by created_at range
     * - per_page: int (default 50)
     */
    public function missingParticipants(Request $request)
    {
        $search = $request->query('search');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $perPage = max(1, (int) $request->query('per_page', 50));

        $q = Transaksi::query()
            ->with(['event:id,nama_event', 'peserta:id,name,email'])
            ->where('status', 'success')
            ->whereDoesntHave('participants')
            ->orderByDesc('id');

        if ($search) {
            $kw = "%$search%";
            $q->where(function ($w) use ($kw) {
                $w->where('invoice', 'like', $kw)
                    ->orWhereHas('peserta', function ($p) use ($kw) {
                        $p->where('name', 'like', $kw)->orWhere('email', 'like', $kw);
                    });
            });
        }
        if ($dateFrom) {
            try {
                $q->whereDate('created_at', '>=', \Carbon\Carbon::parse($dateFrom)->startOfDay());
            } catch (\Throwable $e) {
            }
        }
        if ($dateTo) {
            try {
                $q->whereDate('created_at', '<=', \Carbon\Carbon::parse($dateTo)->endOfDay());
            } catch (\Throwable $e) {
            }
        }

        $paginator = $q->paginate($perPage);
        $rows = $paginator->getCollection()->map(function ($t) {
            return [
                'id' => $t->id,
                'invoice' => $t->invoice,
                'status' => $t->status,
                'amount' => (int) $t->amount,
                'created_at' => optional($t->created_at)->toDateTimeString(),
                'event' => $t->event ? [
                    'id' => $t->event->id,
                    'nama_event' => $t->event->nama_event,
                ] : null,
                'user' => $t->peserta ? [
                    'id' => $t->peserta->id,
                    'name' => $t->peserta->name,
                    'email' => $t->peserta->email,
                ] : null,
            ];
        })->values();

        return response()->json([
            'data' => $rows,
            'meta' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    /**
     * Simple staff list for racepack dropdown, derived from participants table
     * GET /api/v1/staffs
     * Optional query: search
     */
    public function staffList(Request $request)
    {
        $q = Participant::query()
            ->whereNotNull('racepack_by')
            ->select(['staff_user_id', 'racepack_by'])
            ->groupBy('staff_user_id', 'racepack_by')
            ->orderBy('racepack_by');
        if ($request->filled('search')) {
            $term = '%' . $request->input('search') . '%';
            $q->where('racepack_by', 'like', $term);
        }
        $items = $q->get()->map(function ($row) {
            return [
                'id' => $row->staff_user_id,
                'name' => $row->racepack_by,
            ];
        })->values();
        return response()->json($items);
    }
    use MediaUploadingTrait;
    use CsvImportTrait;

    public function index()
    {
        abort_if(Gate::denies('pendaftar_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $pendaftars = User::with(['event'])->get();

        $events = Event::get();

        return view('admin.pendaftars.index', compact('events', 'pendaftars'));
    }

    public function myorder()
    {
        // Get authenticated API user
        $authUser = Auth::guard('api')->user();
        if (!$authUser) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Prefer direct lookup by peserta_id = user id
        $items = Transaksi::where('peserta_id', $authUser->id)
            ->orderByDesc('created_at')
            ->get();

        // Fallbacks if nothing found: try by uid or email (if such data was stored)
        if ($items->isEmpty()) {
            $items = Transaksi::query()
                ->when(!empty($authUser->uid), fn($q) => $q->orWhere('uid', $authUser->uid))
                ->when(!empty($authUser->email), fn($q) => $q->orWhere('email', $authUser->email))
                ->orderByDesc('created_at')
                ->get();
        }

        // Attach decoded participants and events for easier consumption on FE
        $items->transform(function ($t) {
            // participants: stored as JSON text
            $t->participants_decoded = null;
            if (!empty($t->participants)) {
                $decoded = json_decode($t->participants, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $t->participants_decoded = $decoded;
                }
            }

            // events: JSON array of ticket IDs (legacy may contain serialized or plain text)
            $eventsDecoded = json_decode($t->events, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                // fallback legacy handling
                $maybe = @unserialize($t->events);
                $eventsDecoded = $maybe !== false ? $maybe : $t->events;
            }

            // Build map of event id => nama_event for quick lookup
            $eventIds = collect(is_array($eventsDecoded) ? $eventsDecoded : [$eventsDecoded])
                ->filter()
                ->unique()
                ->values();
            $eventMap = $eventIds->isNotEmpty()
                ? Event::whereIn('id', $eventIds)->get(['id', 'nama_event'])->keyBy('id')
                : collect();

            // Enrich participants with event_name based on their ticketId
            // Also force ticketId to be string for FE consistency
            if (is_array($t->participants_decoded)) {
                $t->participants_decoded = array_map(function ($p) use ($eventMap) {
                    $eid = isset($p['ticketId']) ? (int) $p['ticketId'] : null;
                    // Force ticketId to string if present
                    if (isset($p['ticketId'])) {
                        $p['ticketId'] = (string) $p['ticketId'];
                    }
                    $p['event_name'] = ($eid && isset($eventMap[$eid])) ? $eventMap[$eid]['nama_event'] : null;
                    return $p;
                }, $t->participants_decoded);
            }

            $t->events_decoded = $eventsDecoded;
            return $t;
        });

        $resp = new stdClass();
        $resp->message = 'success';
        $resp->status = 200;
        $resp->data = $items;
        return response()->json($resp);
    }

    public function myticket()
    {
        // Get authenticated API user
        $authUser = Auth::guard('api')->user();
        if (!$authUser) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Prefer direct lookup by peserta_id = user id, only successful transactions
        $trx = Transaksi::where('peserta_id', $authUser->id)
            ->where('status', 'success')
            ->orderByDesc('created_at')
            ->get();

        //cek apakah sudah kirim notifikasi
        // if ($trx->notifikasi == 0 && $trx->status == 'success') {
        //     $this->postPaymentSuccessActions($trx);
        // }

        // Fallbacks if nothing found: try by uid or email (if such data was stored)
        if ($trx->isEmpty()) {
            $trx = Transaksi::query()
                ->when(!empty($authUser->uid), fn($q) => $q->orWhere('uid', $authUser->uid))
                ->when(!empty($authUser->email), fn($q) => $q->orWhere('email', $authUser->email))
                ->where('status', 'success')
                ->orderByDesc('created_at')
                ->get();
        }

        // Build flattened tickets list from successful transactions
        $tickets = [];
        foreach ($trx as $t) {
            // Get participants from participants table, backfill if needed
            $participants = $t->participants()->where('status', 1);

            //             // Generate QR code for participant_id
            //             $qrDir = public_path('qrcodes/participants');
            //             if (!file_exists($qrDir)) {
            //                 mkdir($qrDir, 0755, true);
            //             }
            //             $qrPath = $qrDir . '/' . $pid . '.png';
            //             if (!file_exists($qrPath)) {
            //                 QrCode::format('png')->size(300)->generate($pid, $qrPath);
            //             }
            //         }
            //         $participants = $t->participants()->get();
            //     }
            // } else {
            $participants = $participants->get();
            // }
            // decode events (list of event IDs)
            $eventsDecoded = json_decode($t->events, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $maybe = @unserialize($t->events);
                $eventsDecoded = $maybe !== false ? $maybe : $t->events;
            }
            $eventIds = collect(is_array($eventsDecoded) ? $eventsDecoded : [$eventsDecoded])->filter()->unique()->values();
            $events = $eventIds->isNotEmpty() ? Event::whereIn('id', $eventIds)->get(['id', 'nama_event', 'harga', 'tanggal_mulai']) : collect();
            $eventMap = $events->keyBy('id');

            // expand one ticket per participant
            foreach ($participants as $p) {
                $eid = $p->ticket_id ? (int) $p->ticket_id : null;
                $ev = $eid ? ($eventMap[$eid] ?? null) : null;
                $tickets[] = [
                    'invoice' => $t->invoice,
                    'transaction_id' => $t->id,
                    'status' => $t->status,
                    'created_at' => $t->created_at,
                    'participant' => [
                        'name' => $p->name,
                        'nik' => $p->nik,
                        'email' => $p->email,
                        'phone' => $p->phone,
                        'province' => $p->province ?? '',
                        'city' => $p->city ?? '',
                        'shirt_size' => $p->shirt_size ?? null,
                        'participant_id' => $p->participant_id,
                        'status_racepack' => $p->status_racepack,
                        'status' => $p->status,
                        'qr_url' => url("/storage/participants/{$p->participant_id}.png"),
                    ],
                    'event' => $ev ? [
                        'id' => $ev->id,
                        'nama_event' => $ev->nama_event,
                        'harga' => (float) $ev->harga,
                        'tanggal_mulai' => $ev->tanggal_mulai ?? null,
                    ] : null,
                ];
            }

            // if no participants payload, still surface a generic ticket per event id
            if (empty($participants)) {
                foreach ($eventIds as $eid) {
                    $ev = $eventMap[$eid] ?? null;
                    $tickets[] = [
                        'invoice' => $t->invoice,
                        'transaction_id' => $t->id,
                        'status' => $t->status,
                        'created_at' => $t->created_at,
                        'participant' => null,
                        'event' => $ev ? [
                            'id' => $ev->id,
                            'nama_event' => $ev->nama_event,
                            'harga' => (int) $ev->harga,
                            'tanggal_mulai' => $ev->tanggal_mulai ?? null,
                        ] : null,
                    ];
                }
            }
        }

        $resp = new stdClass();
        $resp->message = 'success';
        $resp->status = 200;
        $resp->data = $tickets;
        return response()->json($resp);
    }

    /**
     * @OA\Get(
     *   path="/api/v1/list_checkin",
     *   tags={"Pendaftar"},
     *   summary="List pendaftar who have checked in",
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function list_checkin()
    {
        // abort_if ( Gate::denies( 'pendaftar_access' ), Response::HTTP_FORBIDDEN, '403 Forbidden' );

        return new PendaftarResource(User::with(['event'])->where('checkin', 'sudah')->paginate(10));
    }

    /**
     * @OA\Get(
     *   path="/api/v1/list_checkout",
     *   tags={"Pendaftar"},
     *   summary="List pendaftar who have checked out",
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function list_checkout()
    {
        // abort_if ( Gate::denies( 'pendaftar_access' ), Response::HTTP_FORBIDDEN, '403 Forbidden' );

        return new PendaftarResource(User::with(['event'])->where('checkin', 'terpakai')->paginate(10));
    }

    public function create()
    {
        abort_if(Gate::denies('pendaftar_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $events = Event::pluck('nama_event', 'id')->prepend(trans('global.pleaseSelect'), '');
        $no_t = User::orderBy('no_tiket', 'DESC')->first();
        return view('admin.pendaftars.create', compact('events', 'no_t'));
    }

    public function apibeli(Request $request)
    {
        return view('admin.pendaftars.beli', compact('request'));
    }

    public function beli(Request $request)
    {
        $events = Event::pluck('nama_event', 'id')->prepend(trans('global.pleaseSelect'), '');
        $no_t = User::orderBy('no_tiket', 'DESC')->first();
        $data = $request->all();
        $data['price_1'] = $data['day_1'] * 210000;
        $data['price_2'] = $data['day_2'] * 210000;
        $data['price_3'] = $data['day_3'] * 280000;

        if ($data['day_1'] == 0 && $data['day_2'] == 0 && $data['day_3'] == 0) {
            return view('welcome');
        } else {
            return view('daftar', compact('events', 'no_t', 'data'));
        }
        // return redirect()->route( 'admin.pendaftars.index' );
        // var_dump( $request->all() );
        // echo '<pre> dev ';
    }

    public function generate(Request $request)
    {
        $data = $request->all();
        // dd( $data );
        $length = 10;
        $random = '';
        for ($i = 0; $i < $length; $i++) {
            $random .= rand(0, 1) ? rand(0, 9) : chr(rand(ord('a'), ord('z')));
        }

        $no_invoice = 'TRX-' . Str::upper($random);
        // } else {

        $tiket_id = array();
        $amount = 0;

        $u1 = 12000;

        for ($u = 11600; $u < $u1; $u++) {
            $no_tiket = $u;
            $tiket_id[] = $no_tiket;
            // $pendaftar->no_tiket = '0' . User::latest()->first()->name;
            // $total_bayar = Event::find( 1 )->harga;
            // $amount += $total_bayar;
            $code = uniqid() . uniqid();
            $pendaftar = User::create(array_merge($request->all(), [
                'name' => 'generate',
                'nik' => 'generate',
                'email' => $code,
                'no_hp' => $no_tiket,
                'no_tiket' => 'generate',
                // 'total_bayar' => $total_bayar,
                // 'token' => $request->input( '_token' ),
                'status_payment' => 'pending',
            ]));
            // QrCode::format( 'png' );
            //Will return a png image
            QrCode::format('png')->size(300)->generate($code, '../public/qrcodes/' . $u . '.png');
        }

        // echo ' berhasil';
        //  return view( 'bayar', compact( 'snap' ) );
        // }
        // return redirect()->route( 'admin.pendaftars.index' );
        return response()->json([
            'status' => 'success',
            'message' => 'Pendaftar berhasil dibuat',
        ]);
    }

    /**
     * @OA\Post(
     *   path="/api/v1/checkin",
     *   tags={"Pendaftar"},
     *   summary="Mark pendaftar check-in",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"no_tiket"},
     *       @OA\Property(property="no_tiket", type="string", example="012345")
     *     )
     *   ),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function checkin1(Request $request)
    {
        $pendaftar = User::where('no_tiket', $request->input('no_tiket'))->first();
        $pendaftar->update(['checkin' => 'sudah']);
        $data = new stdClass();
        $data->message = 'success';
        $data->data = $pendaftar;
        return response()->json($data);
    }

    /**
     * @OA\Post(
     *   path="/api/v1/checkout",
     *   tags={"Pendaftar"},
     *   summary="Mark pendaftar check-out",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"no_tiket"},
     *       @OA\Property(property="no_tiket", type="string", example="012345")
     *     )
     *   ),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function checkout(Request $request)
    {
        $pendaftar = User::where('no_tiket', $request->input('no_tiket'))->first();
        $pendaftar->update(['checkin' => 'terpakai']);
        $data = new stdClass();
        $data->message = 'success';
        $data->data = $pendaftar;
        return response()->json($data);
    }

    /**
     * @OA\Post(
     *   path="/api/v1/checkin2",
     *   tags={"Pendaftar"},
     *   summary="Mark pendaftar check-in with note",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"no_tiket"},
     *       @OA\Property(property="no_tiket", type="string", example="012345")
     *     )
     *   ),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function checkin2(Request $request)
    {
        $pendaftar = User::where('no_tiket', $request->input('no_tiket'))->first();
        $pendaftar->update(['checkin' => 'sudah-note']);
        $data = new stdClass();
        $data->message = 'success';
        $data->data = $pendaftar;
        return response()->json($data);
    }

    /**
     * @OA\Post(
     *   path="/api/v1/daftar",
     *   tags={"Auth"},
     *   summary="Register new user (mobile)",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"uid","email","name"},
     *       @OA\Property(property="uid", type="string", example="U123"),
     *       @OA\Property(property="email", type="string", format="email"),
     *       @OA\Property(property="name", type="string")
     *     )
     *   ),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function daftar(Request $request)
    {
        $e_user = User::where(
            'email',
            $request->input('email')
            // 'password' => $request->input( 'no_hp' ),
        )->first();

        if (!empty($e_user)) {

            $data = new stdClass();
            $data->message = 'email sudah terdaftar';
            return response()->json($data);
        } else {
            $user = User::create([
                'uid' => $request->input('userId'),
                'email' => $request->input('email'),
                'name' => $request->input('name'),
                'password' => $request->input('userId'),
                // 'password' => $request->input( 'no_hp' ),
            ]);
            // $user->assignRole( 'User' );
            $user->roles()->sync(2);

            $data = new stdClass();
            $data->message = 'success daftar';
            $data->data = $user;
            return response()->json($data);
        }

        // Update related pendaftar based on stored no_tiket and generate QR on success
        if ($data_transaction) {
            $noTiket = @unserialize($data_transaction->events);
            if ($noTiket === false) {
                $noTiket = $data_transaction->events; // fallback if plain string
            }
            if ($noTiket) {
                $p = User::where('no_tiket', $noTiket)->first();
                if ($p) {
                    if ($data_transaction->status === 'success') {
                        $p->status_payment = 'success';
                        // ensure QR exists
                        $qrPath = public_path("qrcodes/{$noTiket}.png");
                        if (!file_exists(dirname($qrPath))) {
                            @mkdir(dirname($qrPath), 0775, true);
                        }
                        if (!file_exists($qrPath)) {
                            QrCode::format('png')->size(300)->generate($noTiket, $qrPath);
                        }
                    } else if ($data_transaction->status === 'pending') {
                        $p->status_payment = 'pending';
                    } else {
                        $p->status_payment = 'failed';
                    }
                    $p->save();
                }
            }
        }

        return true;
    }

    /**
     * Return payment status and ticket details for FE by invoice
     * GET /api/v1/payment/{invoice}
     */
    public function paymentStatus($invoice)
    {
        $trx = Transaksi::where('invoice', $invoice)->first();
        if (!$trx) {
            return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
        }

        // Safety: kalau sudah success tapi flag notifikasi belum terset, panggil sekali
        if ($trx->status === 'success' && (int) $trx->notifikasi === 0) {
            $this->postPaymentSuccessActions($trx); // fungsi sudah idempotent karena ada guard notifikasi==1
            // refresh untuk ambil nilai terbaru setelah aksi sukses
            $trx->refresh();
        }

        // Cache nilai untuk response
        $invoiceVal = $trx->invoice;
        $statusVal = $trx->status;
        $amountVal = $trx->amount;
        $eventsVal = $trx->events;

        // Events bisa serialized/plain
        $noTiket = @unserialize($eventsVal);
        if ($noTiket === false) {
            $noTiket = $eventsVal;
        }

        // Detail user (peserta register tunggal, jika ada)
        $userDetail = User::where('id', $trx->peserta_id)->first();

        // QR legacy untuk no_tiket (tidak generate di sini)
        $qrPathLegacy = $noTiket ? public_path("qrcodes/{$noTiket}.png") : null;
        $qrUrlLegacy = ($qrPathLegacy && file_exists($qrPathLegacy)) ? url("/qrcodes/{$noTiket}.png") : null;

        // Ambil participants dari tabel relasi
        $participants = $trx->participants()->get();

        return response()->json([
            'invoice' => $invoiceVal,
            'status' => $statusVal,
            'amount' => $amountVal,
            'no_tiket' => $noTiket,
            'qr_url' => $qrUrlLegacy, // hanya untuk skenario legacy no_tiket
            'expired_snap_time' => $trx->expired_snap_time,
            'user' => $userDetail ? [
                'id' => $userDetail->id,
                'nama' => $userDetail->name,
                'email' => $userDetail->email,
                'no_hp' => $userDetail->no_hp,
                'status_payment' => $userDetail->status_payment,
                'event_id' => $userDetail->event_id,
                'nomor_punggung' => $userDetail->nomor_punggung,
                'start_at' => $userDetail->start_at,
                'finish_at' => $userDetail->finish_at,
            ] : null,
            // Untuk peserta per-orang: QR disajikan via storage/participants (dibuat saat webhook/register)
            'participants' => $participants->map(fn($p) => [
                'participant_id' => $p->participant_id,
                'name' => $p->name,
                'nik' => $p->nik,
                'email' => $p->email,
                'phone' => $p->phone,
                'province' => $p->province,
                'city' => $p->city,
                'ticket_id' => $p->ticket_id,
                'status_racepack' => $p->status_racepack,
                'qr_url' => url("/storage/participants/{$p->participant_id}.png"),
            ]),
        ]);
    }

    /**
     * Register a single ticket then return Midtrans redirect URL
     */
    public function registerTicket(Request $request)
    {
        $request->validate([
            'event_id' => 'required|integer',
            'nik' => 'required|string',
            'nama' => 'required|string',
            'no_hp' => 'required|string',
            'email' => 'required|email',
            // 'address' => 'required|string',
            'province' => 'required|string',
            'city' => 'required|string',
        ]);

        // determine next no_tiket
        $last = User::orderBy('no_tiket', 'DESC')->first();
        $next = $last && is_numeric($last->no_tiket) ? ((int) $last->no_tiket + 1) : 1;
        $no_tiket = (string) $next;

        $event = Event::findOrFail($request->input('event_id'));

        $pendaftar = User::create([
            'no_tiket' => $no_tiket,
            'name' => $request->input('nama'),
            'nik' => $request->input('nik'),
            'email' => $request->input('email'),
            'no_hp' => $request->input('no_hp'),
            'region' => $request->input('province'),
            'city' => $request->input('city'),
            // 'village' => $request->input('address'),
            'event_id' => $event->id,
            'total_bayar' => $event->harga,
            'status_payment' => 'pending',
        ]);

        // Create invoice
        $random = Str::upper(Str::random(10));
        $invoice = 'TRX-' . $random;

        $transaksi = Transaksi::create([
            'invoice' => $invoice,
            'events' => serialize($no_tiket),
            'event_id' => $event->id,
            'amount' => $event->harga,
            'note' => $pendaftar->nama,
            'status' => 'pending',
        ]);

        $payload = [
            'transaction_details' => [
                'order_id' => $transaksi->invoice,
                'gross_amount' => $transaksi->amount,
            ],
            'customer_details' => [
                'first_name' => $pendaftar->name,
                'email' => $pendaftar->email,
            ],
            'callbacks' => [
                'finish' => url('/payment/success/' . $invoice),
            ],
        ];

        $paymentUrl = Snap::createTransaction($payload)->redirect_url;
        return response()->json(['url' => $paymentUrl, 'invoice' => $invoice, 'no_tiket' => $no_tiket]);
    }

    /** Start scan: pair nomor punggung and mark start */
    public function scanStart(Request $request)
    {
        $request->validate([
            'no_tiket' => 'required|string',
            'nomor_punggung' => 'required|string',
        ]);
        $p = User::where('no_tiket', $request->input('no_tiket'))->firstOrFail();
        $p->nomor_punggung = $request->input('nomor_punggung');
        $p->checkin = 'sudah';
        $p->start_at = Carbon::now();
        $p->save();
        return response()->json(['success' => true]);
    }

    /** Finish scan: mark finish time */
    public function scanFinish(Request $request)
    {
        $request->validate([
            'no_tiket' => 'required|string',
        ]);
        $p = User::where('no_tiket', $request->input('no_tiket'))->firstOrFail();
        $p->finish_at = Carbon::now();
        $p->checkin = 'terpakai';
        $p->save();
        return response()->json(['success' => true]);
    }

    /**
     * Scan QR to fetch order/participant data.
     * Accepts either no_tiket or nomor_punggung.
     */
    public function scan(Request $request)
    {
        $request->validate([
            'no_tiket' => 'nullable|string',
            'nomor_punggung' => 'nullable|string',
        ]);

        if (!$request->filled('no_tiket') && !$request->filled('nomor_punggung')) {
            return response()->json(['message' => 'no_tiket atau nomor_punggung wajib diisi'], 422);
        }

        $query = User::with('event');
        if ($request->filled('no_tiket')) {
            $query->where('no_tiket', $request->input('no_tiket'));
        } else {
            $query->where('nomor_punggung', $request->input('nomor_punggung'));
        }
        $p = $query->first();
        if (!$p) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json([
            'id' => $p->id,
            'no_tiket' => $p->no_tiket,
            'nama' => $p->name,
            'email' => $p->email,
            'no_hp' => $p->no_hp,
            'status_payment' => $p->status_payment,
            'checkin' => $p->checkin,
            'nomor_punggung' => $p->nomor_punggung,
            'event' => $p->event ? [
                'id' => $p->event->id,
                'nama_event' => $p->event->nama_event,
                'tanggal_mulai' => $p->event->tanggal_mulai ?? null,
            ] : null,
        ]);
    }

    /**
     * List pairing nomor punggung yang sudah terisi.
     */
    public function listPairing(Request $request)
    {
        $q = User::query()->whereNotNull('nomor_punggung');
        if ($request->filled('search')) {
            $term = '%' . $request->input('search') . '%';
            $q->where(function ($w) use ($term) {
                $w->where('no_tiket', 'like', $term)
                    ->orWhere('name', 'like', $term)
                    ->orWhere('nomor_punggung', 'like', $term);
            });
        }
        $items = $q->orderBy('nomor_punggung')->limit(200)->get(['id', 'no_tiket', 'name as nama', 'nomor_punggung']);
        return response()->json($items);
    }

    /**
     * @OA\Post(
     *   path="/api/v1/updateprofile",
     *   tags={"Auth"},
     *   summary="Update user profile",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"uid"},
     *       @OA\Property(property="uid", type="string"),
     *       @OA\Property(property="email", type="string", format="email"),
     *       @OA\Property(property="name", type="string"),
     *       @OA\Property(property="nik", type="string"),
     *       @OA\Property(property="no_hp", type="string")
     *     )
     *   ),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function updateprofile(Request $request)
    {
        $e_user = User::where(
            'uid',
            $request->input('uid')
            // 'password' => $request->input( 'no_hp' ),
        )->first();

        if (!empty($e_user)) {
            $e_user->update([
                // 'uid'     => $request->input( 'uid' ),
                'email' => $request->input('email'),
                'name' => $request->input('name'),
                'nik' => $request->input('nik'),
                'no_hp' => $request->input('no_hp'),
            ]);
            // $user->assignRole( 'User' );
            // $user->roles()->sync( 2 );

            $snap = new stdClass();
            $snap->data = 'success update';
            return response()->json($snap);
        } else {

            $snap = new stdClass();
            $snap->data = 'email tidak terdaftar';
            return response()->json($snap);
        }
    }

    /**
     * @OA\Get(
     *   path="/api/v1/profile",
     *   tags={"Auth"},
     *   summary="Get user profile by uid",
     *   @OA\Parameter(name="uid", in="query", required=true, @OA\Schema(type="string")),
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=404, description="Not found")
     * )
     */
    public function profile()
    {
        $request = $_GET['uid'];
        $user = User::where(
            'uid',
            $request
            // 'password' => $request->input( 'no_hp' ),
        )->first();
        // $user->assignRole( 'User' );
        // $user->roles()->sync( 2 );

        $data = new stdClass();
        $data->message = 'success';
        $data->data = $user;
        return response()->json($data);
    }

    /**
     * @OA\Get(
     *   path="/api/v1/transaksi",
     *   tags={"Transaksi"},
     *   summary="List transaksi by uid",
     *   @OA\Parameter(name="uid", in="query", required=true, @OA\Schema(type="string")),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function transaksi()
    {
        // Optional filter by uid; if not provided, return recent transactions
        $uid = $_GET['uid'] ?? null;

        $query = Transaksi::query()->with(['event']);
        if ($uid) {
            $user = User::where('uid', $uid)->first();
            if ($user) {
                $query->where('peserta_id', $user->id);
            } else {
                // if uid not found, return empty result
                return response()->json((object) [
                    'message' => 'success',
                    'data' => [],
                ]);
            }
        } else {
            // No uid -> limit to latest 200 records for dashboard usage
            $excluded_emails = explode(',', env('EMAIL_TESTING', ''));
            $query->where('amount' > 100000);
            $query->whereNotIn('email', $excluded_emails);
            $query->orderByDesc('created_at')->get();
        }

        $items = $query->get()->map(function ($t) {
            return [
                'id' => $t->id,
                'invoice' => $t->invoice,
                'status' => $t->status,
                'amount' => (int) $t->amount,
                'created_at' => optional($t->created_at)->toDateTimeString(),
                'event' => $t->event ? [
                    'id' => $t->event->id,
                    'nama_event' => $t->event->nama_event,
                    'event_code' => $t->event->event_code,
                ] : null,
            ];
        });

        $resp = new stdClass();
        $resp->message = 'success';
        $resp->data = $items;
        return response()->json($resp);
    }

    /**
     * @OA\Get(
     *   path="/api/v1/tiket",
     *   tags={"Tiket"},
     *   summary="List tiket by uid",
     *   @OA\Parameter(name="uid", in="query", required=true, @OA\Schema(type="string")),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function tiket()
    {
        $request = $_GET['uid'];
        $user = User::where(
            'uid',
            $request
        )->first();
        $user = Transaksi::where(
            'peserta_id',
            $user->id
        )->get();
        $user = Tiket::where(
            'peserta_id',
            $user->id
        )->get();
        $data = new stdClass();
        $data->message = 'success';
        $data->data = $user;
        return response()->json($data);
    }

    /**
     * @OA\Post(
     *   path="/api/v1/beli",
     *   tags={"Transaksi"},
     *   summary="Create purchase and get payment URL",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"uid","id"},
     *       @OA\Property(property="uid", type="string", example="U123"),
     *       @OA\Property(property="id", type="array", @OA\Items(type="integer", example=1))
     *     )
     *   ),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function beliApi(Request $request)
    {
        if ((int) env('REGISTRATION', 1) === 0) {
            return response()->json([
                'message' => 'pendaftaran ditutup, mohon hubungi admin untuk informasi lebih lanjut',
            ], 403);
        }

        // Authenticate via jwt-auth to be consistent with token issuer
        try {
            $user = \Tymon\JWTAuth\Facades\JWTAuth::parseToken()->authenticate();
            if (!$user) {
                Log::warning('beliApi unauthorized: no user after parseToken');
                return response()->json(['message' => 'Unauthorized'], 401);
            }
        } catch (\Throwable $e) {
            Log::warning('beliApi auth exception', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $data = $request->all();
        Log::info('beliApi request received', [
            'userId' => $request->input('userId'),
            'participants_count' => is_array($request->input('participants')) ? count($request->input('participants')) : null,
            'ip' => $request->ip(),
        ]);
        // Normalize camelCase in participants payload: shirtSize -> shirt_size
        if (isset($data['participants']) && is_array($data['participants'])) {
            foreach ($data['participants'] as &$p) {
                if (!isset($p['shirt_size']) && isset($p['shirtSize'])) {
                    $p['shirt_size'] = $p['shirtSize'];
                }
            }
            unset($p);
            $request->merge(['participants' => $data['participants']]);
            $data = $request->all();
        }
        $rules = [
            'userId' => 'required',
            // Multi-person purchase: each participant has their own ticketId and identity fields
            'participants' => 'required|array|min:1',
            'participants.*.ticketId' => 'required|integer',
            'participants.*.name' => 'required|string',
            'participants.*.email' => 'required|email',
            'participants.*.phone' => 'required|string',
            'participants.*.nik' => 'required|string',
            'participants.*.province' => 'required|string',
            'participants.*.city' => 'required|string',
            'participants.*.shirt_size' => 'nullable|string',
            // 'participants.*.address' => 'required|string',
        ];

        $validator = Validator::make($data, $rules);
        if (!$validator->fails()) {
            // Prepare invoice number
            $length = 10;
            $random = '';
            for ($i = 0; $i < $length; $i++) {
                $random .= rand(0, 1) ? rand(0, 9) : chr(rand(ord('a'), ord('z')));
            }
            $no_invoice = 'TRX-' . Str::upper($random);

            // Calculate total from participants' ticket IDs
            $ticketIds = collect($data['participants'])->pluck('ticketId')->all();
            $tickets = Event::whereIn('id', $ticketIds)->get(['id', 'nama_event', 'harga']);
            if ($tickets->isEmpty()) {
                return response()->json(['message' => 'Ticket(s) not found'], 422);
            }
            // Sum price per participant's chosen ticket
            $priceMap = $tickets->keyBy('id')->map(fn($t) => (int) $t->harga);
            $amount = 0;
            foreach ($data['participants'] as $p) {
                $amount += $priceMap[$p['ticketId']] ?? 0;
            }

            // Ensure user exists (by uid)
            $existing = User::where('uid', $data['userId'])->first();
            if ($existing) {
                $user = $existing;
            } else {
                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'uid' => $data['userId'],
                    'province' => $data['province'],
                    'city' => $data['city'],
                    // 'address' => $data['address'],
                    'no_hp' => $data['phone'],
                    'nik' => $data['nik'],
                    'password' => $data['phone'],
                ]);
            }

            // Build buyer info (fallback to first participant for address/contact)
            $first = $data['participants'][0];
            $buyerName = $user->name ?? $first['name'];
            $buyerEmail = $user->email ?? $first['email'];
            $buyerPhone = $user->no_hp ?? $first['phone'];
            $buyerNik = $user->nik ?? $first['nik'];
            $buyerProvince = $first['province'];
            $buyerCity = $first['city'];
            // $buyerAddress = $first['address'];

            // Default each participant status_racepack to 'belum'
            $participantsAug = array_map(function ($p) {
                if (!isset($p['status_racepack'])) {
                    $p['status_racepack'] = 'belum';
                }
                if (!isset($p['status'])) {
                    $p['status'] = 0;
                }
                return $p;
            }, $data['participants']);

            // Create transaction with multiple tickets stored as JSON array and participants payload
            $transaksi = Transaksi::create([
                'invoice' => $no_invoice,
                'events' => json_encode(array_values(collect($ticketIds)->unique()->values()->all())),
                'peserta_id' => $user->id,
                'amount' => $amount,
                'note' => $buyerName,
                'status' => 'pending',
                'uid' => $user->uid,
                'province' => $buyerProvince,
                'city' => $buyerCity,
                // 'address'       => $buyerAddress,
                'no_hp' => $buyerPhone,
                'nik' => $buyerNik,
                'email' => $buyerEmail,
                'nama' => $buyerName,
                'expired_snap_time' => Carbon::now()->addMinutes(15),
                // new column to be added by migration
                'participants' => json_encode($participantsAug),
            ]);
            Log::info('beliApi transaction created', [
                'invoice' => $transaksi->invoice,
                'amount' => (int) $transaksi->amount,
                'user_id' => $transaksi->peserta_id,
            ]);

            // Build Midtrans payload with item_details per participant
            $eventNameMap = $tickets->keyBy('id')->map(fn($t) => $t->nama_event ?? ('Event #' . $t->id));
            $itemDetails = [];
            $emailTesting = explode(',', env('EMAIL_TESTING', 'kalisya@gmail.com,kezia1@gmail.com,ifailamir@gmail.com,riamakala6@gmail.com,kalisya@ayu.ku,11kexia@gmail.com'));
            // convert to array
            $emailTesting = array_map('trim', $emailTesting);
            $isTesting = in_array(Auth::user()->email, $emailTesting);

            if ($isTesting) {
                $total_payment = 26000;
                $amount = 26000;
                foreach ($data['participants'] as $idx => $p) {
                    $tid = $p['ticketId'];
                    $itemDetails[] = [
                        'id' => 'event-' . $tid,
                        'price' => 1000,
                        'quantity' => 1,
                        'name' => ($eventNameMap[$tid] ?? ('Event #' . $tid)) . ' - ' . $p['name'],
                    ];
                }
            } else {
                foreach ($data['participants'] as $idx => $p) {
                    $tid = $p['ticketId'];
                    $itemDetails[] = [
                        'id' => 'event-' . $tid,
                        'price' => (int) ($priceMap[$tid] ?? 0),
                        'quantity' => 1,
                        'name' => ($eventNameMap[$tid] ?? ('Event #' . $tid)) . ' - ' . $p['name'],
                    ];
                }
            }

            // dd($itemDetails);

            $total_payment = $amount;
            // determine type and promo/referral id from FE (supports snake_case and camelCase)
            $type = $request->input('type', 'promo');
            $promo_code_id = $request->input('promo_code_id') ?? $request->input('promoCodeId');
            // Enforce ticket restriction for promo/referral by allowed_events metadata
            try {
                $participantTicketIds = array_map(function ($p) {
                    return (int) ($p['ticketId'] ?? 0);
                }, $data['participants']);
                $allowed = [];
                if (!empty($promo_code_id)) {
                    if ($type === 'promo') {
                        $pmodel = \App\Models\PromoCode::find($promo_code_id);
                        if ($pmodel && is_array($pmodel->metadata)) {
                            $allowed = $pmodel->metadata['allowed_events'] ?? [];
                        }
                    } elseif ($type === 'referral') {
                        $rmodel = \App\Models\ReferralCode::find($promo_code_id);
                        if ($rmodel && is_array($rmodel->metadata)) {
                            $allowed = $rmodel->metadata['allowed_events'] ?? [];
                        }
                    }
                }
                // Default referral rule when not configured: only ticket id=2
                if ($type === 'referral' && (!is_array($allowed) || empty($allowed))) {
                    $allowed = [2];
                }

                if (is_array($allowed)) {
                    $allowedInt = array_map('intval', $allowed);
                    // Strict for both promo and referral: all tickets must be allowed
                    if (!empty($allowedInt)) {
                        foreach ($participantTicketIds as $tid) {
                            if (!in_array((int) $tid, $allowedInt, true)) {
                                return response()->json([
                                    'message' => $type === 'referral' ? 'Kode referal hanya berlaku untuk tiket UMUM.' : 'Promo ini hanya berlaku untuk tiket tertentu.',
                                    'disallowed_ticket_id' => $tid,
                                ], 422);
                            }
                        }
                    }
                    // Additional strict rule for referral: must buy exactly 1 ticket and it must be id=2
                    if ($type === 'referral') {
                        if (count($participantTicketIds) !== 1 || (int) ($participantTicketIds[0] ?? 0) !== 2) {
                            return response()->json([
                                'message' => 'Kode referal hanya berlaku untuk pembelian 1 tiket UMUM (id=2).',
                            ], 422);
                        }
                    }
                }
            } catch (\Throwable $e) { /* silent validation failure, do not block payment */
            }
            // compute discount for promo/referral
            $discount = 0;
            $refDiscount = null; // for referral per-item adjustment
            if ($type === 'promo' && !empty($promo_code_id)) {
                $discountType = PromoCode::find($promo_code_id)->discount_type ?? null;
                if ($discountType == 'fixed') {
                    $discountAmount = PromoCode::find($promo_code_id)->amount ?? 0;
                    $discount = (float) $discountAmount;
                } else {
                    $discountAmount = PromoCode::find($promo_code_id)->amount ?? 0;
                    $discount = (float) $total_payment * ((float) $discountAmount / 100);
                }
            } elseif ($type === 'referral' && !empty($promo_code_id)) {
                // Referral discount per qualifying ticket (id=2): base 25,000 (buyer gets no extra 5,000)
                $refDiscount = 25000; // base
                try {
                    $refModel = \App\Models\ReferralCode::find($promo_code_id);
                    if ($refModel && is_array($refModel->metadata) && isset($refModel->metadata['referral_discount'])) {
                        $refDiscount = (int) $refModel->metadata['referral_discount'];
                    }
                } catch (\Throwable $e) { /* ignore */
                }
                $qualifying = 0;
                foreach ($data['participants'] as $p) {
                    if ((int) ($p['ticketId'] ?? 0) === 2) {
                        $qualifying++;
                    }
                }
                $discount = max(0, $refDiscount * $qualifying);
            }

            // Apply discount proportionally to each ticket item in item_details so prices are net of discount
            $originalSum = 0;
            foreach ($itemDetails as $it) {
                if (isset($it['id']) && strpos($it['id'], 'event-') === 0) {
                    $originalSum += (int) ($it['price'] ?? 0);
                }
            }
            if ($discount > 0 && $originalSum > 0) {
                if ($type === 'referral' && $refDiscount !== null) {
                    // Apply per-item reduction only to ticket id 2 items
                    $remaining = (int) round($discount);
                    $perItemDiscount = (int) $refDiscount;
                    foreach ($itemDetails as $idx => $it) {
                        if (isset($it['id']) && $it['id'] === 'event-2' && $remaining > 0) {
                            $price = (int) ($it['price'] ?? 0);
                            $share = min($perItemDiscount, $remaining);
                            $itemDetails[$idx]['price'] = (int) max(0, $price - $share);
                            $remaining -= $share;
                        }
                    }
                } else {
                    // Proportional for promo
                    $remaining = (int) round($discount);
                    foreach ($itemDetails as $idx => $it) {
                        if (isset($it['id']) && strpos($it['id'], 'event-') === 0) {
                            $price = (int) ($it['price'] ?? 0);
                            $share = (int) floor(($price / $originalSum) * $discount);
                            $isLast = true;
                            for ($j = $idx + 1; $j < count($itemDetails); $j++) {
                                if (isset($itemDetails[$j]['id']) && strpos($itemDetails[$j]['id'], 'event-') === 0) {
                                    $isLast = false;
                                    break;
                                }
                            }
                            if ($isLast) {
                                $share = $remaining;
                            }
                            $newPrice = max(0, $price - $share);
                            $itemDetails[$idx]['price'] = (int) $newPrice;
                            $remaining -= $share;
                        }
                    }
                }
            }

            $ticket_price = $total_payment - $discount;
            // tambah 2 % dari ticket_price
            $fee_service = $ticket_price * 0.02;
            $total_payment = $ticket_price + $fee_service;
            // add service fee to itemDetails
            $itemDetails[] = [
                'id' => 'service-fee',
                'price' => (int) $fee_service,
                'quantity' => 1,
                'name' => 'Service Fee',
            ];

            // tambah PPN 11%
            $ppn = $total_payment * 0.11;
            $total_payment += $ppn;
            $participant_count = count($data['participants']);
            $gross = (int) $ticket_price;
            $profit = (int) (($participant_count * 5000) + floor($gross * 0.02));
            $final_price = max(0, $gross - $profit) + $ppn;

            $itemDetails[] = [
                'id' => 'ppn',
                'price' => (int) $ppn,
                'quantity' => 1,
                'name' => 'PPN (11%)',
            ];

            // Ensure gross_amount equals the sum of item_details
            $gross_amount = 0;
            foreach ($itemDetails as $it) {
                $price = (int) ($it['price'] ?? 0);
                $qty = (int) ($it['quantity'] ?? 1);
                $gross_amount += ($price * max(1, $qty));
            }

            $payload = [
                'transaction_details' => [
                    'order_id' => $transaksi->invoice,
                    'gross_amount' => (int) $gross_amount,
                ],
                'customer_details' => [
                    'first_name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->no_hp,
                    'address' => $user->city . ',' . $user->province,
                ],
                'item_details' => $itemDetails,
            ];

            $paymentUrl = Snap::createTransaction($payload)->redirect_url;
            $updateTrx = Transaksi::where('invoice', $no_invoice)->update([
                'payment_url' => $paymentUrl,
                'type' => $type,
                'promo_code_id' => $promo_code_id,
                'discount' => $discount,
                'service_fee' => $fee_service,
                'ppn' => $ppn,
                'final_price' => $final_price,
            ]);
            Log::info('beliApi payment URL generated', [
                'invoice' => $no_invoice,
                'payment_url' => $paymentUrl,
                'gross_amount' => (int) $gross_amount,
            ]);

            // dd($updateTrx);

            $resp = new stdClass();
            $resp->data = $paymentUrl;
            $resp->invoice = $no_invoice;
            $resp->participants = $data['participants'];
            $resp->service_fee = $fee_service;
            $resp->total_amount = $ticket_price;
            $resp->total_payment = $total_payment;
            $resp->total_ticket = count($data['participants']) . ' Tiket';
            $resp->expired_snap_time = $transaksi->expired_snap_time;
            $resp->promo_code_id = $promo_code_id;
            $resp->discount = $discount;


            Log::info('beliApi response', [
                'invoice' => $no_invoice,
                'total_payment' => $total_payment,
                'participants' => count($data['participants']),
            ]);
            return response()->json($resp);
        } else {
            Log::warning('beliApi validation failed', [
                'errors' => $validator->errors()->all(),
                'ip' => $request->ip(),
            ]);
            return response()->json(['data' => $validator->errors()->all()]);
        }
    }

    /**
     * @OA\Post(
     *   path="/api/v1/notification",
     *   tags={"Transaksi"},
     *   summary="Midtrans notification callback",
     *   @OA\RequestBody(required=true),
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=403, description="Invalid signature")
     * )
     */
    public function notificationHandler(Request $request)
    {
        // Pastikan content-type JSON
        if ($request->header('Content-Type') !== 'application/json') {
            Log::warning('notificationHandler invalid content type', ['content_type' => $request->header('Content-Type')]);
            return response()->json(['message' => 'Invalid content type'], 400);
        }

        $payload = $request->getContent();
        $notification = json_decode($payload);
        Log::info('notificationHandler received', [
            'order_id' => $notification->order_id ?? null,
            'transaction_status' => $notification->transaction_status ?? null,
            'payment_type' => $notification->payment_type ?? null,
            'status_code' => $notification->status_code ?? null,
            'gross_amount' => $notification->gross_amount ?? null,
            'ip' => $request->ip(),
        ]);

        // Validasi signature
        $validSignatureKey = hash(
            'sha512',
            $notification->order_id .
            $notification->status_code .
            $notification->gross_amount .
            config('services.midtrans.serverKey')
        );

        if (!hash_equals($validSignatureKey, $notification->signature_key)) {
            Log::warning('notificationHandler invalid signature', ['order_id' => $notification->order_id ?? null]);
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $transaction = $notification->transaction_status;
        $type = $notification->payment_type;
        $orderId = $notification->order_id;
        $fraud = $notification->fraud_status;

        // Cari transaksi berdasarkan invoice
        $trx = Transaksi::where('invoice', $orderId)->first();
        if (!$trx) {
            Log::warning('notificationHandler order not found', ['order_id' => $orderId]);
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Mapping status midtrans → status DB
        $statusMap = [
            'capture' => 'success',
            'settlement' => 'success',
            'pending' => 'pending',
            'deny' => 'failed',
            'expire' => 'expired',
            'cancel' => 'failed',
        ];

        // Update status sesuai map
        if (isset($statusMap[$transaction])) {
            // Khusus credit card: challenge = pending
            if ($transaction === 'capture' && $type === 'credit_card' && $fraud === 'challenge') {
                $trx->update(['status' => 'pending']);
                Log::info('notificationHandler status updated', ['invoice' => $trx->invoice, 'status' => 'pending']);
            } else {
                $trx->update(['status' => $statusMap[$transaction]]);
                Log::info('notificationHandler status updated', ['invoice' => $trx->invoice, 'status' => $statusMap[$transaction]]);
            }
        }

        // Jalankan postPaymentSuccessActions hanya jika status akhir = success
        if ($trx->status == 'success') {
            $notif = $this->postPaymentSuccessActions($trx);
            Log::info('notificationHandler postPaymentSuccessActions executed', ['invoice' => $trx->invoice, 'handled' => (bool) $notif]);
            if ($notif) {
                return response()->json(['message' => 'OK']);
            }

        }
    }

    /**
     * After payment success: ensure participants exist in participants table,
     * backfill from JSON if needed, and send WhatsApp messages.
     */
    protected function postPaymentSuccessActions(Transaksi $trx): bool
    {
        // echo 123;die;
        // \Illuminate\Support\Facades\Log::info('postPaymentSuccessActions start', [
        //     'trx_id' => $trx->id,
        //     'invoice' => $trx->invoice,
        // ]);
        // Atomically claim handling to avoid duplicate sends from repeated callbacks
        $claimed = Transaksi::where('id', $trx->id)->where('notifikasi', 0)->update(['notifikasi' => 1]);
        if ($claimed === 0) {
            return true; // another process already handled notifications
        }

        // Refresh model to reflect claimed state
        $trx = $trx->fresh();

        // Increment promo usage exactly once after successful claim (only when type=promo)
        if ($trx->type === 'promo' && !empty($trx->promo_code_id)) {
            try {
                $promo = \App\Models\PromoCode::find($trx->promo_code_id);
                if ($promo) {
                    $promo->used_count = (int) ($promo->used_count ?? 0) + 1;
                    $promo->save();
                }
            } catch (\Throwable $e) {
                // silent fail
            }
        }

        // Check if participants already exist in table

        // Resolve referral code/owner early, credit later per ticket
        $referralOwner = null; // ReferralCode model
        $referralCodeResolved = null; // string
        if ($trx->type === 'referral' && !empty($trx->promo_code_id)) {
            try {
                $owner = \App\Models\ReferralCode::find($trx->promo_code_id);
                if ($owner) {
                    $code = strtoupper(trim((string) ($owner->code ?? '')));
                    $now = now();
                    $inWindow = (!$owner->valid_from || $now->gte($owner->valid_from)) && (!$owner->valid_to || $now->lte($owner->valid_to));
                    $underLimit = (is_null($owner->usage_limit) || (int) $owner->used_count < (int) $owner->usage_limit);
                    if ($owner->active && $inWindow && $underLimit) {
                        $referralOwner = $owner;
                        $referralCodeResolved = $code;
                    }
                }
            } catch (\Throwable $e) { /* silent */
            }
        } else {
            try {
                $code = strtoupper(trim((string) ($trx->note ?? '')));
                if ($code !== '') {
                    $owner = \App\Models\ReferralCode::whereRaw('UPPER(code) = ?', [$code])->first();
                    if ($owner) {
                        $now = now();
                        $inWindow = (!$owner->valid_from || $now->gte($owner->valid_from)) && (!$owner->valid_to || $now->lte($owner->valid_to));
                        $underLimit = (is_null($owner->usage_limit) || (int) $owner->used_count < (int) $owner->usage_limit);
                        if ($owner->active && $inWindow && $underLimit) {
                            $referralOwner = $owner;
                            $referralCodeResolved = $code;
                        }
                    } else {
                        // no owner found, still keep code for logging with null owner
                        $referralCodeResolved = $code;
                    }
                }
            } catch (\Throwable $e) { /* silent */
            }
        }

        $participants = $trx->participants();
        // \Illuminate\Support\Facades\Log::debug('participants in table (before backfill)', [
        //     'count' => $participants->count(),
        // ]);

        // If no participants in table but JSON exists, backfill
        if ($participants->count() == 0 && !empty($trx->getAttributes()['participants'])) {
            $decoded = json_decode($trx->getAttributes()['participants'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                foreach ($decoded as $i => $p) {
                    $pid = $p['participant_id'] ?? ('PID-' . Str::upper(Str::random(7)));
                    Participant::create([
                        'transaction_id' => $trx->id,
                        'participant_id' => $pid,
                        'name' => $p['name'] ?? null,
                        'nik' => $p['nik'] ?? null,
                        'email' => $p['email'] ?? null,
                        'phone' => $p['phone'] ?? null,
                        'ticket_id' => $p['ticketId'] ?? null,
                        'status_racepack' => $p['status_racepack'] ?? 'belum',
                        'amount' => Event::find($p['ticketId'])->harga,
                        'status' => 1,
                        'province' => $p['province'] ?? null,
                        'city' => $p['city'] ?? null,
                        // Accept both snake_case and camelCase from stored JSON
                        'shirt_size' => $p['shirt_size'] ?? ($p['shirtSize'] ?? null),
                        // 'address' => $p['address'] ?? null,
                        // 'postal_code' => $p['postal_code'] ?? null,
                        // 'country' => $p['country'] ?? null,
                        // 'latitude' => $p['latitude'] ?? null,
                        // 'longitude' => $p['longitude'] ?? null,
                        // 'created_by' => $trx->created_by,
                    ]);

                    // Generate QR code for participant_id
                    $qrDir = storage_path('app/public/participants');
                    if (!file_exists($qrDir)) {
                        mkdir($qrDir, 0755, true);
                    }
                    $qrPath = $qrDir . '/' . $pid . '.png';
                    if (!file_exists($qrPath)) {
                        QrCode::format('png')->size(300)->generate($pid, $qrPath);
                    }
                }
                // Reload participants
                $participants = $trx->participants()->get();
            }
        } else {
            $participants = $participants->get();
        }

        if ($participants->isEmpty()) {
            // Fallback: try to find single user by no_tiket stored in events
            $noTiket = @unserialize($trx->events);
            if ($noTiket === false) {
                $noTiket = $trx->events; // could be plain string
            }

            $user = $noTiket ? \App\Models\User::where('no_tiket', $noTiket)->first() : null;

            if ($user) {
                // Determine event name for message context
                $jenis = 'Tiket';
                if (!empty($user->event_id)) {
                    $ev = Event::where('id', $user->event_id)->first();
                    if ($ev) {
                        $jenis = $ev->nama_event ?? ('Event #' . $ev->id);
                    }
                } elseif (!empty($trx->event_id)) {
                    $ev = Event::where('id', $trx->event_id)->first();
                    if ($ev) {
                        $jenis = $ev->nama_event ?? ('Event #' . $ev->id);
                    }
                }

                // Build message using unified template
                $url = 'https://daftar.mandalikakorprirun.com/dashboard/';
                $text = $this->buildPaymentSuccessText(($user->name ?? 'Peserta'), (string) ($noTiket ?? '-'), $jenis);

                // Send WA if phone exists
                if (!empty($user->no_hp)) {
                    $this->sendWhatsapp($user->no_hp, $text, $url);
                }

                // Send Email if email exists
                try {
                    $recipients = array_values(array_filter([
                        $user->email ?? null,
                    ]));
                    if (!empty($recipients)) {
                        $emailData = [
                            'chatId' => $this->normalizePhone($user->no_hp ?? ''),
                            'url' => $url,
                            'text' => $text,
                            'participant' => [
                                'id' => (string) ($noTiket ?? ''),
                                'name' => $user->name ?? null,
                                'email' => $user->email ?? null,
                                'phone' => $user->no_hp ?? null,
                                'ticket' => $jenis,
                            ],
                            'transaction' => [
                                'invoice' => $trx->invoice,
                                'amount' => $trx->amount,
                                'status' => $trx->status,
                            ],
                        ];
                        Mail::to($recipients)->send(new WhatsAppNotification('paymentSuccess', $emailData));
                    }
                } catch (\Throwable $e) {
                    // \Illuminate\Support\Facades\Log::warning('Failed to send payment success email (fallback user)', [
                    //     'error' => $e->getMessage(),
                    //     'invoice' => $trx->invoice,
                    // ]);
                }

                return true; // done via fallback
            }

            return true; // nothing to do (no user fallback)
        }

        // Build map: ticket_id => event name for message
        $ticketIds = $participants->pluck('ticket_id')->filter()->unique();
        $eventName = collect();
        if ($ticketIds->isNotEmpty()) {
            $tickets = Event::whereIn('id', $ticketIds)->get(['id', 'nama_event']);
            $eventName = $tickets->keyBy('id')->map(fn($t) => $t->nama_event ?? ('Event #' . $t->id));
        }

        // Credit referral per qualifying ticket (ticket_id==2) after participants finalized
        try {
            // Count tickets with active status ('1') and ticket_id == 2
            $ticketCount = $participants->filter(function ($pp) {
                return (string) ($pp->status ?? '0') === '1' && (int) ($pp->ticket_id ?? 0) === 2;
            })->count();
            if ($ticketCount === 0) {
                // Fallback: try participants JSON and count only ticketId==2
                $decoded = null;
                try {
                    $decoded = json_decode($trx->getAttributes()['participants'] ?? 'null', true);
                } catch (\Throwable $e) {
                }
                if (is_array($decoded) && count($decoded) > 0) {
                    $ticketCount = 0;
                    foreach ($decoded as $p) {
                        if ((int) ($p['ticketId'] ?? 0) === 2) {
                            $ticketCount++;
                        }
                    }
                }
            }

            if ($ticketCount > 0 && ($referralOwner || $referralCodeResolved)) {
                $value = 5000 * (int) $ticketCount;
                \App\Models\Referal::create([
                    'user_id_referral' => $referralOwner ? (int) $referralOwner->user_id : null,
                    'kode' => $referralCodeResolved,
                    'value' => $value,
                    'tanggal' => now(),
                    'email_pemesan' => $trx->email ?? null,
                ]);

                if ($referralOwner) {
                    $referralOwner->used_count = (int) ($referralOwner->used_count ?? 0) + (int) $ticketCount;
                    $referralOwner->save();
                }
            }
        } catch (\Throwable $e) {
            // silent credit failure
        }

        // Recompute and persist final prices once
        try {
            $this->computeAndPersistFinalPrices($trx, $participants);
        } catch (\Throwable $e) {
            // ignore compute failures
        }

        // Send individual message to each participant with only their own data
        foreach ($participants as $p) {
            if (empty($p->phone)) {
                continue; // Skip if no phone number
            }

            $jenis = $p->ticket_id ? ($eventName[$p->ticket_id] ?? ('Event #' . $p->ticket_id)) : 'Tiket';

            $url = 'https://daftar.mandalikakorprirun.com/dashboard/';
            $purchaserEmail = $trx->email ?? null;
            $text = $this->buildWhatsappTicketText(($p->name ?? 'Peserta'), (string) $p->participant_id, $jenis, $p->email ?? null, $purchaserEmail);

            // \Illuminate\Support\Facades\Log::info('Sending WA for participant', [
            //     'participant_id' => $p->participant_id,
            //     'phone' => $p->phone,
            // ]);
            // Send WA synchronously
            $this->sendWhatsapp($p->phone, $text, $url);

            // Also send email notification (SMTP2GO) to participant and your email
            try {
                $recipients = array_values(array_filter([
                    $p->email ?? null,
                    // 'ifailamir@gmail.com',
                ]));
                if (!empty($recipients)) {
                    // \Illuminate\Support\Facades\Log::info('Sending paymentSuccess email', [
                    //     'to' => $recipients,
                    //     'participant_id' => $p->participant_id,
                    //     'invoice' => $trx->invoice,
                    // ]);
                    $emailData = [
                        'chatId' => $this->normalizePhone($p->phone),
                        'url' => $url,
                        'text' => $text,
                        'participant' => [
                            'id' => $p->participant_id,
                            'name' => $p->name,
                            'email' => $p->email,
                            'phone' => $p->phone,
                            'ticket' => $jenis,
                        ],
                        'transaction' => [
                            'invoice' => $trx->invoice,
                            'amount' => $trx->amount,
                            'status' => $trx->status,
                        ],
                    ];
                    Mail::to($recipients)->send(new \App\Mail\WhatsAppNotification('paymentSuccess', $emailData));
                }
            } catch (\Throwable $e) {
                // ignore email send failure per participant
            }
        }

        return true;
    }

    protected function computeAndPersistFinalPrices(Transaksi $trx, $participants): void
    {
        $excludedEmails = array_filter(array_map(function ($e) {
            return strtolower(trim($e));
        }, explode(',', env('EMAIL_TESTING', ''))));

        // Precompute total active price for proportional promo discount
        $totalActivePrice = 0.0;
        foreach ($participants as $pp0) {
            $statusVal0 = (string) ($pp0->status ?? '0');
            if ($statusVal0 === '1') {
                $p0 = (float) ($pp0->amount ?? 0);
                if ($p0 <= 0 && $pp0->ticket_id) {
                    $ev0 = Event::select(['id', 'harga'])->find($pp0->ticket_id);
                    if ($ev0 && $ev0->harga) {
                        $p0 = (float) $ev0->harga;
                    }
                }
                if ($p0 > 0) {
                    $totalActivePrice += $p0;
                }
            }
        }
        // Resolve referral per-ticket discount amount if needed
        $referralPerTicket = 25000;
        if ($trx->type === 'referral' && !empty($trx->promo_code_id)) {
            try {
                $refModel = \App\Models\ReferralCode::find($trx->promo_code_id);
                if ($refModel && is_array($refModel->metadata) && isset($refModel->metadata['referral_discount'])) {
                    $referralPerTicket = (int) $refModel->metadata['referral_discount'];
                }
            } catch (\Throwable $e) { /* ignore */
            }
        }

        $sumFinal = 0;
        $countRefer = 0;
        foreach ($participants as $pp) {
            if ($pp->ticket_id == 2) {
                $countRefer++;
            }
            $statusVal = (string) ($pp->status ?? '0');
            $newFinal = 0;
            if ($statusVal === '1') {
                $price = (float) ($pp->amount ?? 0);
                if ($price <= 0 && $pp->ticket_id) {
                    $ev = Event::select(['id', 'harga'])->find($pp->ticket_id);
                    if ($ev && $ev->harga) {
                        $price = (float) $ev->harga;
                    }
                }
                if ($price > 0) {
                    // Apply discount per participant to get net price
                    $net = $price;
                    if ($trx->type === 'referral' && (int) ($pp->ticket_id ?? 0) === 2) {
                        $net = max(0, $net - $referralPerTicket);
                    } elseif ($trx->type === 'promo' && (float) ($trx->discount ?? 0) > 0 && $totalActivePrice > 0) {
                        $share = ($price / $totalActivePrice) * (float) $trx->discount;
                        $net = max(0, $net - $share);
                    }

                    // Compute final using post-discount net
                    $calc = ($net - 5000) - ($net * 0.02);
                    $calc += ($calc * 0.11);
                    // $priceRefer = 5000 * $countRefer;
                    // $calc -= $priceRefer;
                    if (is_finite($calc)) {
                        $newFinal = (int) round($calc);
                    }
                }
            } else {
                $newFinal = 0;
            }
            $pEmail = strtolower(trim((string) ($pp->email ?? '')));
            if ($pEmail !== '' && in_array($pEmail, $excludedEmails, true)) {
                // For testing emails: keep net ticket price (original minus discount), no fees/ppn adjustments
                if (isset($net)) {
                    $newFinal = (int) round(max(0, $net));
                } else {
                    // Fallback to price when net not set
                    $newFinal = (int) round(max(0, $price));
                }
            }
            if ((int) ($pp->final_price ?? 0) !== (int) $newFinal) {
                $pp->final_price = $newFinal;
                $pp->save();
            }
            $sumFinal += $newFinal;
        }

        $trxEmail = strtolower(trim((string) ($trx->email ?? '')));
        if ($trxEmail !== '' && in_array($trxEmail, $excludedEmails, true)) {
            // For testing transactions: sum net ticket prices per participant
            $sumFinal = 0;
            foreach ($participants as $ppx) {
                $statusValX = (string) ($ppx->status ?? '0');
                if ($statusValX !== '1') {
                    continue;
                }
                $px = (float) ($ppx->amount ?? 0);
                if ($px <= 0 && $ppx->ticket_id) {
                    $evx = Event::select(['id', 'harga'])->find($ppx->ticket_id);
                    if ($evx && $evx->harga) {
                        $px = (float) $evx->harga;
                    }
                }
                if ($px <= 0) {
                    continue;
                }
                $netx = $px;
                if ($trx->type === 'referral' && (int) ($ppx->ticket_id ?? 0) === 2) {
                    $netx = max(0, $netx - $referralPerTicket);
                } elseif ($trx->type === 'promo' && (float) ($trx->discount ?? 0) > 0 && $totalActivePrice > 0) {
                    $sharex = ($px / $totalActivePrice) * (float) $trx->discount;
                    $netx = max(0, $netx - $sharex);
                }
                $sumFinal += (int) round($netx);
            }
        }

        $trx->update([
            'final_price' => (int) $sumFinal,
        ]);
    }

    protected function sendWhatsapp(string $phone, string $text, string $url): void
    {
        try {
            $base = rtrim(config('services.waha.base_url'), '/');
            $session = config('services.waha.session');
            $apiKey = config('services.waha.api_key');
            $chatId = $this->normalizePhone($phone);
            $url = $url;
            // Check if the text contains a URL
            // if (isset($url)) {
            //     // Send with link preview
            //     Http::withHeaders([
            //         'x-api-key' => $apiKey,
            //     ])->post($base . '/api/sendLinkPreview', [
            //         'chatId' => $chatId,
            //         'session' => $session,
            //         'url' => $url,
            //         'text' => $text,
            //     ]);
            // } else {
            // Fallback to regular text message if no URL found
            Http::withHeaders([
                'x-api-key' => $apiKey,
            ])->post($base . '/api/sendText', [
                        'chatId' => $chatId,
                        'session' => $session,
                        'text' => $text,
                    ]);
            // }
        } catch (\Throwable $e) {
            // Log the error
            // \Illuminate\Support\Facades\Log::warning('WA send failed: ' . $e->getMessage());
        }
    }

    protected function normalizePhone(string $phone): string
    {
        $p = preg_replace('/\D+/', '', $phone);
        if (strpos($p, '62') === 0)
            return $p; // already in 62 format
        if (strpos($p, '0') === 0)
            return '62' . substr($p, 1);
        return $p; // fallback
    }

    /**
     * Update status_racepack to 'sudah' for a participant by participant_id only
     */
    public function racepack(Request $request)
    {
        // Support both single participant_id and bulk participant_ids[]
        // Single mode keeps backward-compatible behavior

        //cek apakah ada x-api-key dan benar
        if ($request->header('x-api-key') != env('X_API_KEY')) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        //get user by bearer token
        $user = auth('api')->user();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $isBulk = is_array($request->input('participant_ids'));

        if ($isBulk) {
            // Bulk mode: validate array input
            $request->validate([
                'participant_ids' => 'required|array|min:1',
                'participant_ids.*' => 'required|string',
            ]);

            $ids = $request->input('participant_ids');

            $participants = Participant::whereIn('participant_id', $ids)->get()->keyBy('participant_id');

            $updated = [];
            $already = [];
            $notFound = [];

            foreach ($ids as $pid) {
                $participant = $participants->get($pid);
                if (!$participant) {
                    $notFound[] = $pid;
                    continue;
                }

                if ($participant->status_racepack == 'sudah') {
                    $already[] = $pid;
                    continue;
                }

                $participant->update([
                    'status_racepack' => 'sudah',
                    'staff_user_id' => $user->id ?? null,
                    'racepack_by' => $user->name ?? null,
                    'racepack_at' => now(),
                ]);

                $updated[] = $pid;
            }

            return response()->json([
                'message' => 'Bulk racepack update processed',
                'staff' => $user->name ?? null,
                'updated_count' => count($updated),
                'already_count' => count($already),
                'not_found_count' => count($notFound),
                'updated' => $updated,
                'already' => $already,
                'not_found' => $notFound,
            ], 200);
        }

        // Single mode (backward compatible)
        $request->validate([
            'participant_id' => 'required|string',
        ]);

        $participant = Participant::where('participant_id', $request->input('participant_id'))->first();
        if (!$participant) {
            return response()->json(['message' => 'Participant not found'], 404);
        }

        // cek apakah sudah? jika iya balikan "Anda Sudah Mengambil Racepack"
        if ($participant->status_racepack == 'sudah') {
            return response()->json(['message' => 'Anda Sudah Mengambil Racepack'], 400);
        }

        $participant->update([
            'status_racepack' => 'sudah',
            'staff_user_id' => $user->id ?? null,
            'racepack_by' => $user->name ?? null,
            'racepack_at' => now(),
        ]);

        return response()->json([
            'message' => 'Racepack berhasil diambil',
            'staff' => $user->name ?? null,
            'participant' => $participant ?? null,
        ], 200);
    }

    public function resetRacepack(Request $request)
    {
        // Support both single participant_id and bulk participant_ids[]

        $isBulk = is_array($request->input('participant_ids'));

        if ($isBulk) {
            $request->validate([
                'participant_ids' => 'required|array|min:1',
                'participant_ids.*' => 'required|string',
            ]);

            $ids = $request->input('participant_ids');

            $participants = Participant::whereIn('participant_id', $ids)->get()->keyBy('participant_id');

            $resetList = [];
            $notFound = [];
            $unchanged = [];

            foreach ($ids as $pid) {
                $participant = $participants->get($pid);
                if (!$participant) {
                    $notFound[] = $pid;
                    continue;
                }

                // If already belum, we mark as unchanged
                if ($participant->status_racepack === 'belum' || $participant->status_racepack === null) {
                    $unchanged[] = $pid;
                    continue;
                }

                $participant->update([
                    'status_racepack' => 'belum',
                    'staff_user_id' => null,
                    'racepack_by' => null,
                    'racepack_at' => null,
                ]);

                $resetList[] = $pid;
            }

            return response()->json([
                'message' => 'Bulk racepack reset processed',
                'reset_count' => count($resetList),
                'not_found_count' => count($notFound),
                'unchanged_count' => count($unchanged),
                'reset' => $resetList,
                'not_found' => $notFound,
                'unchanged' => $unchanged,
            ], 200);
        }

        $request->validate([
            'participant_id' => 'required|string',
        ]);
        $reset = Participant::where('participant_id', $request->input('participant_id'))->first();
        if (!$reset) {
            return response()->json(['message' => 'Participant not found'], 404);
        }
        $reset->update([
            'status_racepack' => 'belum',
            'staff_user_id' => null,
            'racepack_by' => null,
            'racepack_at' => null,
        ]);
        return response()->json([
            'message' => 'Racepack berhasil direset',
            'participant' => $reset ?? null,
        ], 200);
    }

    /**
     * List participants' racepack status with filters and pagination
     * GET /api/v1/racepacks
     * Query params:
     * - status: 'sudah' | 'belum' (optional)
     * - staff_id: int (optional)
     * - staff_name: string (optional, matches racepack_by like)
     * - search: string (optional, matches participant_id, name, email, phone)
     * - date_from, date_to: Y-m-d (optional) filter by racepack_at range
     * - per_page: int (optional) default 10
     */
    public function racepackList(Request $request)
    {
        $perPage = (int) $request->input('per_page', 10);
        $status = $request->input('status');
        $staffId = $request->input('staff_id');
        $staffName = $request->input('staff_name');
        $search = $request->input('search');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $includeTesting = (bool) $request->boolean('include_testing', false);

        $base = $this->buildRacepackBase($request);

        // Compute totals regardless of status filter
        $totalSudah = (clone $base)->where('status_racepack', 'sudah')->count();
        $totalBelum = (clone $base)->where('status_racepack', 'belum')->count();
        // Compute totals per ticket type (ASN=1, UMUM=2) regardless of status filter
        $totalAsn = (clone $base)->where('ticket_id', 1)->count();
        $totalUmum = (clone $base)->where('ticket_id', 2)->count();

        // Apply status for the listing (if provided)
        $listQuery = clone $base;
        if (in_array($status, ['sudah', 'belum'], true)) {
            $listQuery->where('status_racepack', $status);
        }

        $listQuery->orderByDesc('racepack_at')->orderByDesc('id');
        $paginator = $listQuery->paginate($perPage);

        return response()->json([
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'total_sudah' => $totalSudah,
                'total_belum' => $totalBelum,
                'total_asn' => $totalAsn,
                'total_umum' => $totalUmum,
            ],
        ]);
    }

    public function listAllRacepack(Request $request)
    {
        $perPage = (int) $request->input('per_page', 1000);
        $status = $request->input('status');
        $staffId = $request->input('staff_id');
        $staffName = $request->input('staff_name');
        $search = $request->input('search');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $includeTesting = (bool) $request->boolean('include_testing', false);

        $base = $this->buildRacepackBase($request);

        // Compute totals regardless of status filter
        $totalSudah = (clone $base)->where('status_racepack', 'sudah')->count();
        $totalBelum = (clone $base)->where('status_racepack', 'belum')->count();
        // Compute totals per ticket type (ASN=1, UMUM=2) regardless of status filter
        $totalAsn = (clone $base)->where('ticket_id', 1)->count();
        $totalUmum = (clone $base)->where('ticket_id', 2)->count();

        // Apply status for the listing (if provided)
        $listQuery = clone $base;
        if (in_array($status, ['sudah', 'belum'], true)) {
            $listQuery->where('status_racepack', $status);
        }

        $listQuery->orderByDesc('racepack_at')->orderByDesc('id');
        $paginator = $listQuery->paginate($perPage);

        $data = collect($paginator->items())->map(function ($p) {
            return [
                'name' => $p->name,
                'phone' => $p->phone,
                'participant_id' => $p->participant_id,
                'shirt_size' => $p->shirt_size,
                'status_racepack' => $p->status_racepack,
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'total_sudah' => $totalSudah,
                'total_belum' => $totalBelum,
                'total_asn' => $totalAsn,
                'total_umum' => $totalUmum,
            ],
        ]);
    }

    /**
     * Export participants racepack list as CSV using the same filters as racepackList.
     * GET /api/v1/racepacks/export
     * Query params are identical to racepackList.
     */
    public function exportRacepacks(Request $request)
    {
        $base = $this->buildRacepackBase($request);
        $status = $request->input('status');
        if (in_array($status, ['sudah', 'belum'], true)) {
            $base->where('status_racepack', $status);
        }

        // Mirror list ordering for export
        $base->orderByDesc('racepack_at')->orderByDesc('id');

        // Debug mode: return count and sample rows instead of streaming
        if ($request->boolean('debug', false)) {
            $count = (clone $base)->count();
            $sample = (clone $base)->limit(5)->get(['id', 'participant_id', 'email', 'status_racepack']);
            $sql = $base->toSql();
            $bindings = $base->getBindings();
            return response()->json([
                'debug' => true,
                'count' => $count,
                'sample' => $sample,
                'sql' => $sql,
                'bindings' => $bindings,
            ]);
        }

        // Fetch all data (542 rows is safe for memory)
        $participants = $base->get();

        // Preload event names map to avoid N+1
        $eventIds = $participants->pluck('ticket_id')->filter()->unique();
        $eventMap = $eventIds->isNotEmpty() ? Event::whereIn('id', $eventIds)->get(['id', 'nama_event'])->keyBy('id') : collect();

        $filename = 'participants-' . now()->format('Ymd-His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($participants, $eventMap) {
            $out = fopen('php://output', 'w');
            // UTF-8 BOM for Excel compatibility
            fwrite($out, "\xEF\xBB\xBF");
            // Header row
            fputcsv($out, [
                'Participant ID',
                'Name',
                'Email',
                'Phone',
                'Province',
                'City',
                'Jenis Tiket',
                'Ukuran Jersey',
                'Status',
                'Staff',
                'Racepack At'
            ]);

            foreach ($participants as $p) {
                $ticketName = null;
                if ($p->ticket_id) {
                    if ((int) $p->ticket_id === 1) {
                        $ticketName = 'ASN';
                    } elseif ((int) $p->ticket_id === 2) {
                        $ticketName = 'UMUM';
                    } else {
                        $ticketName = optional($eventMap->get($p->ticket_id))->nama_event ?? (string) $p->ticket_id;
                    }
                }
                $row = [
                    $p->participant_id,
                    $p->name,
                    $p->email,
                    $p->phone,
                    $p->province,
                    $p->city,
                    $ticketName,
                    $p->shirt_size,
                    $p->status_racepack,
                    $p->racepack_by ?: optional($p->staff)->name,
                    $p->racepack_at ? \Carbon\Carbon::parse($p->racepack_at)->timezone('Asia/Jakarta')->format('Y-m-d H:i:s') : '',
                ];
                fputcsv($out, $row);
            }

            fclose($out);
        };

        return response()->streamDownload($callback, $filename, $headers);
    }

    /**
     * Export participants racepack list as Excel-compatible .xls (HTML table)
     * GET /api/v1/racepacks/export-excel
     * Shares the same filters as exportRacepacks. Optional: include_testing=1
     */
    public function exportRacepacksExcel(Request $request)
    {
        $base = $this->buildRacepackBase($request);
        $status = $request->input('status');
        if (in_array($status, ['sudah', 'belum'], true)) {
            $base->where('status_racepack', $status);
        }

        $base->orderByDesc('racepack_at')->orderByDesc('id');

        if ($request->boolean('debug', false)) {
            $count = (clone $base)->count();
            $sample = (clone $base)->limit(5)->get(['id', 'participant_id', 'email', 'status_racepack']);
            return response()->json([
                'debug' => true,
                'count' => $count,
                'sample' => $sample,
            ]);
        }

        // Fetch all data (542 rows is safe for memory)
        $participants = $base->get();

        // Preload event names map to avoid N+1
        $eventIds = $participants->pluck('ticket_id')->filter()->unique();
        $eventMap = $eventIds->isNotEmpty() ? Event::whereIn('id', $eventIds)->get(['id', 'nama_event'])->keyBy('id') : collect();

        $filename = 'participants-' . now()->format('Ymd-His') . '.xls';
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($participants, $eventMap) {
            $out = fopen('php://output', 'w');
            // Write UTF-8 BOM
            fwrite($out, "\xEF\xBB\xBF");
            fclose($out);

            echo "<table border='1'>";
            echo "<thead><tr>";
            $tableHeaders = ['Participant ID', 'Name', 'Email', 'Phone', 'Province', 'City', 'Jenis Tiket', 'Ukuran Jersey', 'Status', 'Staff', 'Racepack At'];
            foreach ($tableHeaders as $h) {
                echo '<th>' . htmlspecialchars($h, ENT_QUOTES, 'UTF-8') . '</th>';
            }
            echo "</tr></thead><tbody>";

            foreach ($participants as $p) {
                $ticketName = null;
                if ($p->ticket_id) {
                    if ((int) $p->ticket_id === 1) {
                        $ticketName = 'ASN';
                    } elseif ((int) $p->ticket_id === 2) {
                        $ticketName = 'UMUM';
                    } else {
                        $ticketName = optional($eventMap->get($p->ticket_id))->nama_event ?? (string) $p->ticket_id;
                    }
                }
                $cells = [
                    $p->participant_id,
                    $p->name,
                    $p->email,
                    $p->phone,
                    $p->province,
                    $p->city,
                    $ticketName,
                    $p->shirt_size,
                    $p->status_racepack,
                    $p->racepack_by ?: optional($p->staff)->name,
                    $p->racepack_at ? \Carbon\Carbon::parse($p->racepack_at)->timezone('Asia/Jakarta')->format('Y-m-d H:i:s') : '',
                ];
                echo '<tr>';
                foreach ($cells as $c) {
                    echo '<td>' . htmlspecialchars((string) ($c ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
                }
                echo '</tr>';
            }

            echo "</tbody></table>";
        };

        return response()->streamDownload($callback, $filename, $headers);
    }

    /**
     * Simple direct CSV download without streaming complexity
     * GET /api/v1/racepacks/download-csv
     */
    public function downloadCsv(Request $request)
    {
        $base = $this->buildRacepackBase($request);
        $status = $request->input('status');
        if (in_array($status, ['sudah', 'belum'], true)) {
            $base->where('status_racepack', $status);
        }
        $base->orderByDesc('racepack_at')->orderByDesc('id');

        // Get all data
        $participants = $base->get();

        // Preload events
        $eventIds = $participants->pluck('ticket_id')->filter()->unique();
        $eventMap = $eventIds->isNotEmpty() ? Event::whereIn('id', $eventIds)->get(['id', 'nama_event'])->keyBy('id') : collect();

        // Build CSV content
        $csv = "\xEF\xBB\xBF"; // UTF-8 BOM
        $csv .= "Participant ID,Name,Email,Phone,Province,City,Jenis Tiket,Ukuran Jersey,Status,Staff,Racepack At\n";

        foreach ($participants as $p) {
            $ticketName = '';
            if ($p->ticket_id) {
                if ((int) $p->ticket_id === 1) {
                    $ticketName = 'ASN';
                } elseif ((int) $p->ticket_id === 2) {
                    $ticketName = 'UMUM';
                } else {
                    $ticketName = optional($eventMap->get($p->ticket_id))->nama_event ?? (string) $p->ticket_id;
                }
            }

            $row = [
                $p->participant_id ?? '',
                $p->name ?? '',
                $p->email ?? '',
                $p->phone ?? '',
                $p->province ?? '',
                $p->city ?? '',
                $ticketName,
                $p->shirt_size ?? '',
                $p->status_racepack ?? '',
                $p->racepack_by ?: optional($p->staff)->name ?? '',
                $p->racepack_at ? \Carbon\Carbon::parse($p->racepack_at)->timezone('Asia/Jakarta')->format('Y-m-d H:i:s') : '',
            ];

            // Escape and quote fields
            $escaped = array_map(function ($field) {
                $field = str_replace('"', '""', $field);
                return '"' . $field . '"';
            }, $row);

            $csv .= implode(',', $escaped) . "\n";
        }

        $filename = 'participants-' . now()->format('Ymd-His') . '.csv';

        return response($csv, 200)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Simple direct Excel download without streaming complexity
     * GET /api/v1/racepacks/download-excel
     */
    public function downloadExcel(Request $request)
    {
        $base = $this->buildRacepackBase($request);
        $status = $request->input('status');
        if (in_array($status, ['sudah', 'belum'], true)) {
            $base->where('status_racepack', $status);
        }
        $base->orderByDesc('racepack_at')->orderByDesc('id');

        // Get all data
        $participants = $base->get();

        // Preload events
        $eventIds = $participants->pluck('ticket_id')->filter()->unique();
        $eventMap = $eventIds->isNotEmpty() ? Event::whereIn('id', $eventIds)->get(['id', 'nama_event'])->keyBy('id') : collect();

        // Build HTML table compatible with Excel
        $html = "\xEF\xBB\xBF"; // UTF-8 BOM
        $html .= "<table border='1'>";
        $html .= "<thead><tr>";
        $headers = ['Participant ID', 'Name', 'Email', 'Phone', 'Province', 'City', 'Jenis Tiket', 'Ukuran Jersey', 'Status', 'Staff', 'Racepack At'];
        foreach ($headers as $h) {
            $html .= '<th>' . htmlspecialchars($h, ENT_QUOTES, 'UTF-8') . '</th>';
        }
        $html .= "</tr></thead><tbody>";

        foreach ($participants as $p) {
            $ticketName = '';
            if ($p->ticket_id) {
                if ((int) $p->ticket_id === 1) {
                    $ticketName = 'ASN';
                } elseif ((int) $p->ticket_id === 2) {
                    $ticketName = 'UMUM';
                } else {
                    $ticketName = optional($eventMap->get($p->ticket_id))->nama_event ?? (string) $p->ticket_id;
                }
            }

            $cells = [
                $p->participant_id ?? '',
                $p->name ?? '',
                $p->email ?? '',
                $p->phone ?? '',
                $p->province ?? '',
                $p->city ?? '',
                $ticketName,
                $p->shirt_size ?? '',
                $p->status_racepack ?? '',
                $p->racepack_by ?: optional($p->staff)->name ?? '',
                $p->racepack_at ? \Carbon\Carbon::parse($p->racepack_at)->timezone('Asia/Jakarta')->format('Y-m-d H:i:s') : '',
            ];

            $html .= '<tr>';
            foreach ($cells as $c) {
                $html .= '<td>' . htmlspecialchars((string) ($c ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= "</tbody></table>";

        $filename = 'participants-' . now()->format('Ymd-His') . '.xls';
        return response($html, 200)
            ->header('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
