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

class PendaftarController extends Controller
{
    public function __construct()
    {
        // Set midtrans configuration
        \Midtrans\Config::$serverKey    = config('services.midtrans.serverKey');
        \Midtrans\Config::$isProduction = config('services.midtrans.isProduction');
        \Midtrans\Config::$isSanitized  = config('services.midtrans.isSanitized');
        \Midtrans\Config::$is3ds        = config('services.midtrans.is3ds');
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
        ]);

        $useTemplate = (bool) $request->boolean('use_default_template', false);
        $sendAll = (bool) $request->boolean('send_all', false);
        $text = (string) $request->input('text', '');
        $search = (string) $request->input('search', '');

        // Build base query for participants
        $excluded_emails = explode(',', env('EMAIL_TESTING', ''));
        $base = Participant::query()
            ->select(['id','transaction_id','participant_id','name','email','phone','ticket_id'])
            ->where('status', '1')
            ->where('amount', '>', 100000)
            ->whereNotIn('email', $excluded_emails)
            ->whereNull('shirt_size');

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
            $tickets = Event::whereIn('id', $ticketIds)->get(['id','nama_event']);
            $eventName = $tickets->keyBy('id')->map(fn($t) => $t->nama_event ?? ('Event #' . $t->id));
        }

        $dashboardUrl = 'https://daftar.mandalikakorprirun.com/dashboard/';
        $results = [];
        $success = 0;
        $failed = 0;

        foreach ($list as $p) {
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

            try {
                $msg = $text;
                if ($useTemplate || $msg === '') {
                    $jenis = $p->ticket_id ? ($eventName[$p->ticket_id] ?? ('Event #' . $p->ticket_id)) : 'Tiket';
                    $msg = $this->buildPaymentSuccessText(($p->name ?? 'Peserta'), (string)$p->participant_id, $jenis);
                }
                $this->sendWhatsapp($p->phone, $msg, $dashboardUrl);
                $success++;
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
            ->select(['id','transaction_id','participant_id','name','email','phone','ticket_id'])
            ->where('status', '1')
            ->where('amount', '>', 100000)
            ->whereNotIn('email', $excluded_emails)
            ->whereNull('shirt_size');

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
            $tickets = Event::whereIn('id', $ticketIds)->get(['id','nama_event']);
            $eventName = $tickets->keyBy('id')->map(fn($t) => $t->nama_event ?? ('Event #' . $t->id));
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
                    $jenis = $p->ticket_id ? ($eventName[$p->ticket_id] ?? ('Event #' . $p->ticket_id)) : 'Tiket';
                    $msg = $this->buildPaymentSuccessText(($p->name ?? 'Peserta'), (string)$p->participant_id, $jenis);
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
        $trxId   = $request->input('transaction_id');

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
        $event = $p->ticket_id ? Event::select(['id','nama_event','harga','tanggal_mulai'])->find($p->ticket_id) : null;
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
        $search   = $request->query('search');
        $dateFrom = $request->query('date_from');
        $dateTo   = $request->query('date_to');
        $perPage  = max(1, (int) $request->query('per_page', 50));

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
            try { $q->whereDate('created_at', '>=', \Carbon\Carbon::parse($dateFrom)->startOfDay()); } catch (\Throwable $e) {}
        }
        if ($dateTo) {
            try { $q->whereDate('created_at', '<=', \Carbon\Carbon::parse($dateTo)->endOfDay()); } catch (\Throwable $e) {}
        }

        $paginator = $q->paginate($perPage);
        $rows = $paginator->getCollection()->map(function ($t) {
            return [
                'id'         => $t->id,
                'invoice'    => $t->invoice,
                'status'     => $t->status,
                'amount'     => (int) $t->amount,
                'created_at' => optional($t->created_at)->toDateTimeString(),
                'event'      => $t->event ? [
                    'id'         => $t->event->id,
                    'nama_event' => $t->event->nama_event,
                ] : null,
                'user'       => $t->peserta ? [
                    'id'    => $t->peserta->id,
                    'name'  => $t->peserta->name,
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
                    'invoice'       => $t->invoice,
                    'transaction_id' => $t->id,
                    'status'        => $t->status,
                    'created_at'    => $t->created_at,
                    'participant'   => [
                        'name'   => $p->name,
                        'nik'    => $p->nik,
                        'email'  => $p->email,
                        'phone'  => $p->phone,
                        'province' => $p->province ?? '',
                        'city'   => $p->city ?? '',
                        'shirt_size' => $p->shirt_size ?? null,
                        'participant_id' => $p->participant_id,
                        'status_racepack' => $p->status_racepack,
                        'status' => $p->status,
                        'qr_url' => url("/storage/participants/{$p->participant_id}.png"),
                    ],
                    'event'         => $ev ? [
                        'id'         => $ev->id,
                        'nama_event' => $ev->nama_event,
                        'harga'      => (float)$ev->harga,
                        'tanggal_mulai'    => $ev->tanggal_mulai ?? null,
                    ] : null,
                ];
            }

            // if no participants payload, still surface a generic ticket per event id
            if (empty($participants)) {
                foreach ($eventIds as $eid) {
                    $ev = $eventMap[$eid] ?? null;
                    $tickets[] = [
                        'invoice'       => $t->invoice,
                        'transaction_id' => $t->id,
                        'status'        => $t->status,
                        'created_at'    => $t->created_at,
                        'participant'   => null,
                        'event'         => $ev ? [
                            'id'         => $ev->id,
                            'nama_event' => $ev->nama_event,
                            'harga'      => (int) $ev->harga,
                            'tanggal_mulai'    => $ev->tanggal_mulai ?? null,
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
        $data['price_1']  = $data['day_1'] * 210000;
        $data['price_2']  = $data['day_2'] * 210000;
        $data['price_3']  = $data['day_3'] * 280000;

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
                'uid'     => $request->input('userId'),
                'email'    => $request->input('email'),
                'name'    => $request->input('name'),
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
        $statusVal  = $trx->status;
        $amountVal  = $trx->amount;
        $eventsVal  = $trx->events;

        // Events bisa serialized/plain
        $noTiket = @unserialize($eventsVal);
        if ($noTiket === false) {
            $noTiket = $eventsVal;
        }

        // Detail user (peserta register tunggal, jika ada)
        $userDetail = User::where('id', $trx->peserta_id)->first();

        // QR legacy untuk no_tiket (tidak generate di sini)
        $qrPathLegacy = $noTiket ? public_path("qrcodes/{$noTiket}.png") : null;
        $qrUrlLegacy  = ($qrPathLegacy && file_exists($qrPathLegacy)) ? url("/qrcodes/{$noTiket}.png") : null;

        // Ambil participants dari tabel relasi
        $participants = $trx->participants()->get();

        return response()->json([
            'invoice'     => $invoiceVal,
            'status'      => $statusVal,
            'amount'      => $amountVal,
            'no_tiket'    => $noTiket,
            'qr_url'      => $qrUrlLegacy, // hanya untuk skenario legacy no_tiket
            'expired_snap_time' => $trx->expired_snap_time,
            'user'        => $userDetail ? [
                'id'              => $userDetail->id,
                'nama'            => $userDetail->name,
                'email'           => $userDetail->email,
                'no_hp'           => $userDetail->no_hp,
                'status_payment'  => $userDetail->status_payment,
                'event_id'        => $userDetail->event_id,
                'nomor_punggung'  => $userDetail->nomor_punggung,
                'start_at'        => $userDetail->start_at,
                'finish_at'       => $userDetail->finish_at,
            ] : null,
            // Untuk peserta per-orang: QR disajikan via storage/participants (dibuat saat webhook/register)
            'participants' => $participants->map(fn($p) => [
                'participant_id'   => $p->participant_id,
                'name'             => $p->name,
                'nik'              => $p->nik,
                'email'            => $p->email,
                'phone'            => $p->phone,
                'province'         => $p->province,
                'city'             => $p->city,
                'ticket_id'        => $p->ticket_id,
                'status_racepack'  => $p->status_racepack,
                'qr_url'           => url("/storage/participants/{$p->participant_id}.png"),
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
        $next = $last && is_numeric($last->no_tiket) ? ((int)$last->no_tiket + 1) : 1;
        $no_tiket = (string)$next;

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
                'email'    => $request->input('email'),
                'name'    => $request->input('name'),
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
        // Authenticate via jwt-auth to be consistent with token issuer
        try {
            $user = \Tymon\JWTAuth\Facades\JWTAuth::parseToken()->authenticate();
            if (!$user) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $data = $request->all();
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
                'invoice'       => $no_invoice,
                'events'        => json_encode(array_values(collect($ticketIds)->unique()->values()->all())),
                'peserta_id'    => $user->id,
                'amount'        => $amount,
                'note'          => $buyerName,
                'status'        => 'pending',
                'uid'           => $user->uid,
                'province'      => $buyerProvince,
                'city'          => $buyerCity,
                // 'address'       => $buyerAddress,
                'no_hp'         => $buyerPhone,
                'nik'           => $buyerNik,
                'email'         => $buyerEmail,
                'nama'          => $buyerName,
                'expired_snap_time' => Carbon::now()->addMinutes(15),
                // new column to be added by migration
                'participants'  => json_encode($participantsAug),
            ]);

            // Build Midtrans payload with item_details per participant
            $eventNameMap = $tickets->keyBy('id')->map(fn($t) => $t->nama_event ?? ('Event #' . $t->id));
            $itemDetails = [];
            $emailTesting = explode(',', env('EMAIL_TESTING', 'kalisya@gmail.com,kezia1@gmail.com,ifailamir@gmail.com,riamakala6@gmail.com,kalisya@ayu.ku,11kexia@gmail.com'));
            // convert to array
            $emailTesting = array_map('trim', $emailTesting);
            $isTesting = in_array(Auth::user()->email, $emailTesting);

            if ($isTesting) {
                $total_payment = 1000;
                $amount = 1000;
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

            // tambah 1.5 % di amount
            $fee_service = $amount * 0.016;
            // $fee_service = $fee_service * count($itemDetails);
            $total_payment = $amount + $fee_service;
            //add service fee to itemDetails
            $itemDetails[] = [
                'id' => 'service-fee',
                'price' => (int) $fee_service,
                'quantity' => 1,
                'name' => 'Service Fee',
            ];

            //tambah PPN 11%
            $ppn = $total_payment * 0.11;
            $total_payment += $ppn;
            $itemDetails[] = [
                'id' => 'ppn',
                'price' => (int) $ppn,
                'quantity' => 1,
                'name' => 'PPN (11%)',
            ];

            $payload = [
                'transaction_details' => [
                    'order_id'      => $transaksi->invoice,
                    'gross_amount'  => (int) $total_payment,
                ],
                'customer_details' => [
                    'first_name'       => $user->name,
                    'email'            => $user->email,
                ],
                'item_details' => $itemDetails,
            ];

            $paymentUrl = Snap::createTransaction($payload)->redirect_url;
            Transaksi::where('invoice', $no_invoice)->update([
                'payment_url' => $paymentUrl
            ]);

            $resp = new stdClass();
            $resp->data = $paymentUrl;
            $resp->invoice = $no_invoice;
            $resp->participants = $data['participants'];
            $resp->service_fee = $fee_service;
            $resp->total_amount = $amount;
            $resp->total_payment = $total_payment;
            $resp->total_ticket = count($data['participants']) . ' Tiket';
            $resp->expired_snap_time = $transaksi->expired_snap_time;
            return response()->json($resp);
        } else {
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
            return response()->json(['message' => 'Invalid content type'], 400);
        }

        $payload      = $request->getContent();
        $notification = json_decode($payload);

        // Validasi signature
        $validSignatureKey = hash(
            'sha512',
            $notification->order_id .
                $notification->status_code .
                $notification->gross_amount .
                config('services.midtrans.serverKey')
        );

        if (!hash_equals($validSignatureKey, $notification->signature_key)) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $transaction  = $notification->transaction_status;
        $type         = $notification->payment_type;
        $orderId      = $notification->order_id;
        $fraud        = $notification->fraud_status;

        // Cari transaksi berdasarkan invoice
        $trx = Transaksi::where('invoice', $orderId)->first();
        if (!$trx) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Mapping status midtrans → status DB
        $statusMap = [
            'capture'    => 'success',
            'settlement' => 'success',
            'pending'    => 'pending',
            'deny'       => 'failed',
            'expire'     => 'expired',
            'cancel'     => 'failed',
        ];

        // Update status sesuai map
        if (isset($statusMap[$transaction])) {
            // Khusus credit card: challenge = pending
            if ($transaction === 'capture' && $type === 'credit_card' && $fraud === 'challenge') {
                $trx->update(['status' => 'pending']);
            } else {
                $trx->update(['status' => $statusMap[$transaction]]);
            }
        }

        // Jalankan postPaymentSuccessActions hanya jika status akhir = success
        if ($trx->status === 'success') {
            $this->postPaymentSuccessActions($trx);
        }

        return response()->json(['message' => 'OK']);
    }

    /**
     * After payment success: ensure participants exist in participants table,
     * backfill from JSON if needed, and send WhatsApp messages.
     */
    protected function postPaymentSuccessActions(Transaksi $trx): void
    {
        // \Illuminate\Support\Facades\Log::info('postPaymentSuccessActions start', [
        //     'trx_id' => $trx->id,
        //     'invoice' => $trx->invoice,
        // ]);
        // Check if participants already exist in table

        // Skip jika sudah pernah kirim notifikasi
        if ($trx->notifikasi == 1) {
            return;
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
                $text = $this->buildPaymentSuccessText(($user->name ?? 'Peserta'), (string)($noTiket ?? '-'), $jenis);

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

                return; // done via fallback
            }

            return; // nothing to do (no user fallback)
        }

        // Build map: ticket_id => event name for message
        $ticketIds = $participants->pluck('ticket_id')->filter()->unique();
        $eventName = collect();
        if ($ticketIds->isNotEmpty()) {
            $tickets = Event::whereIn('id', $ticketIds)->get(['id', 'nama_event']);
            $eventName = $tickets->keyBy('id')->map(fn($t) => $t->nama_event ?? ('Event #' . $t->id));
        }

        // Send individual message to each participant with only their own data
        foreach ($participants as $p) {
            if (empty($p->phone)) {
                continue; // Skip if no phone number
            }

            $jenis = $p->ticket_id ? ($eventName[$p->ticket_id] ?? ('Event #' . $p->ticket_id)) : 'Tiket';

            $url = 'https://daftar.mandalikakorprirun.com/dashboard/';
            $text = $this->buildPaymentSuccessText(($p->name ?? 'Peserta'), (string)$p->participant_id, $jenis);

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
                    Mail::to($recipients)->send(new WhatsAppNotification('paymentSuccess', $emailData));
                }

                // update status to success
                $trx->update(['notifikasi' => 1]);
            } catch (\Throwable $e) {
                // If sending email fails for this participant, log and continue with the next
                // \Illuminate\Support\Facades\Log::warning('Failed to send payment success email notification', [
                //     'error' => $e->getMessage(),
                //     'participant' => $p->participant_id ?? null,
                // ]);
                continue;
            }
        }
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
        if (strpos($p, '62') === 0) return $p; // already in 62 format
        if (strpos($p, '0') === 0) return '62' . substr($p, 1);
        return $p; // fallback
    }

    /**
     * Update status_racepack to 'sudah' for a participant by participant_id only
     */
    public function racepack(Request $request)
    {
        $request->validate([
            'participant_id' => 'required|string',
        ]);

        //cek apakah ada x-api-key dan benar
        if ($request->header('x-api-key') != env('X_API_KEY')) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        //get user by bearer token
        $user = auth('api')->user();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

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

        // Define the emails to exclude from the query
        $excluded_emails = explode(',', env('EMAIL_TESTING', ''));

        // Perform the database query
        $base = Participant::with(['staff:id,name'])
            ->select('participants.*')
            ->where('status', '1')
            ->whereNotIn('email', $excluded_emails);

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
        if ($dateFrom) {
            $base->whereDate('racepack_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $base->whereDate('racepack_at', '<=', $dateTo);
        }

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
}
