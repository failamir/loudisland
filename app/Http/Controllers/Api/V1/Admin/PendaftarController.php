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
use App\Models\User;
use App\Models\Tiket;
use OpenApi\Annotations as OA;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Pendaftar;
use Illuminate\Support\Facades\Http;

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
            if (is_array($t->participants_decoded)) {
                $t->participants_decoded = array_map(function ($p) use ($eventMap) {
                    $eid = isset($p['ticketId']) ? (int) $p['ticketId'] : null;
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
            // decode participants
            $participants = [];
            if (!empty($t->participants)) {
                $decoded = json_decode($t->participants, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $participants = $decoded;
                }
            }
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
                $eid = isset($p['ticketId']) ? (int) $p['ticketId'] : null;
                $ev = $eid ? ($eventMap[$eid] ?? null) : null;
                $tickets[] = [
                    'invoice'       => $t->invoice,
                    'transaction_id' => $t->id,
                    'status'        => $t->status,
                    'created_at'    => $t->created_at,
                    'participant'   => [
                        'name'   => $p['name']   ?? null,
                        'email'  => $p['email']  ?? null,
                        'phone'  => $p['phone']  ?? null,
                        'nik'    => $p['nik']    ?? null,
                        'province' => $p['province'] ?? null,
                        'city'   => $p['city']   ?? null,
                        'participant_id' => $p['participant_id'] ?? null,
                        'status_restpack' => $p['status_restpack'] ?? null,
                    ],
                    'event'         => $ev ? [
                        'id'         => $ev->id,
                        'nama_event' => $ev->nama_event,
                        'harga'      => (int) $ev->harga,
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

        // events may be serialized or JSON/plain; keep legacy behavior
        $noTiket = @unserialize($trx->events);
        if ($noTiket === false) {
            $noTiket = $trx->events;
        }

        $userDetail = User::where('id', $trx->peserta_id)->first();

        // Build QR URL if exists; do not generate here (generation happens on webhook or register)
        $qrPath = $noTiket ? public_path("qrcodes/{$noTiket}.png") : null;
        $qrUrl = ($qrPath && file_exists($qrPath)) ? url("/qrcodes/{$noTiket}.png") : null;

        // decode participants if present
        $participants = null;
        if (!empty($trx->participants)) {
            $decoded = json_decode($trx->participants, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $participants = $decoded;
            }
        }

        // Backfill: if payment success but participants missing participant_id/status_restpack, generate and persist (no WA sending here)
        if ($trx->status === 'success' && is_array($participants)) {
            $modified = false;
            foreach ($participants as $i => $p) {
                if (empty($p['participant_id'])) {
                    $length = 10;
                    $random = '';
                    for ($i = 0; $i < $length; $i++) {
                        $random .= rand(0, 1) ? rand(0, 9) : chr(rand(ord('a'), ord('z')));
                    }
                    $participants[$i]['participant_id'] = 'PID-' . Str::upper($random);
                    $modified = true;
                }
                if (empty($p['status_restpack'])) {
                    $participants[$i]['status_restpack'] = 'belum';
                    $modified = true;
                }
            }
            if ($modified) {
                $trx->participants = json_encode($participants);
                $trx->save();
            }
        }

        return response()->json([
            'invoice' => $trx->invoice,
            'status' => $trx->status,
            'amount' => $trx->amount,
            'no_tiket' => $noTiket,
            'qr_url' => $qrUrl,
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
            'participants' => $participants,
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
        $request = $_GET['uid'];
        $user = User::where(
            'uid',
            $request
            // 'password' => $request->input( 'no_hp' ),
        )->first();
        $user = Transaksi::where(
            'peserta_id',
            $user->id
            // 'password' => $request->input( 'no_hp' ),
        )->get();
        // $user->assignRole( 'User' );
        // $user->roles()->sync( 2 );

        $data = new stdClass();
        $data->message = 'success';
        $data->data = $user;
        return response()->json($data);
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

            // Default each participant status_restpack to 'belum'
            $participantsAug = array_map(function ($p) {
                if (!isset($p['status_restpack'])) {
                    $p['status_restpack'] = 'belum';
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
                // new column to be added by migration
                'participants'  => json_encode($participantsAug),
            ]);

            // Build Midtrans payload with item_details per participant
            $eventNameMap = $tickets->keyBy('id')->map(fn($t) => $t->nama_event ?? ('Event #' . $t->id));
            $itemDetails = [];
            foreach ($data['participants'] as $idx => $p) {
                $tid = $p['ticketId'];
                $itemDetails[] = [
                    'id' => 'event-' . $tid,
                    'price' => (int) ($priceMap[$tid] ?? 0),
                    'quantity' => 1,
                    'name' => ($eventNameMap[$tid] ?? ('Event #' . $tid)) . ' - ' . $p['name'],
                ];
            }

            // tambah 1.7 % di amount
            $total_payment = $amount + ($amount * 0.017);
            $fee_service = $amount * 0.017;

            //add service fee to itemDetails
            $itemDetails[] = [
                'id' => 'service-fee',
                'price' => (int) $fee_service,
                'quantity' => 1,
                'name' => 'Service Fee',
            ];

            $emailTesting = ['lvlysunday@gmail.com', 'kezia1@gmail.com', 'ifailamir@gmail.com'];
            if (in_array($user->email, $emailTesting)) {
                $total_payment = 1000.00;
            }

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
        $payload      = $request->getContent();
        $notification = json_decode($payload);

        $validSignatureKey = hash('sha512', $notification->order_id . $notification->status_code . $notification->gross_amount . config('services.midtrans.serverKey'));

        if ($notification->signature_key != $validSignatureKey) {
            return response(['message' => 'Invalid signature'], 403);
        }

        $transaction  = $notification->transaction_status;
        $type         = $notification->payment_type;
        $orderId      = $notification->order_id;
        $fraud        = $notification->fraud_status;

        // find transaction by invoice
        $data_transaction = Transaksi::where('invoice', $orderId)->first();

        if ($transaction == 'capture') {

            // For credit card transaction, we need to check whether transaction is challenge by FDS or not
            if ($type == 'credit_card') {

                if ($fraud == 'challenge') {

                    /**
                     *   update invoice to pending
                     */
                    $data_transaction->update([
                        'status' => 'pending'
                    ]);
                } else {

                    /**
                     *   update invoice to success
                     */
                    $data_transaction->update([
                        'status' => 'success'
                    ]);
                }
            }
        } elseif ($transaction == 'settlement') {

            /**
             *   update invoice to success
             */
            $data_transaction->update([
                'status' => 'success'
            ]);
            // Post-success processing: assign participant IDs, ensure status_restpack, and send WA
            $this->postPaymentSuccessActions($data_transaction);
        } elseif ($transaction == 'pending') {

            /**
             *   update invoice to pending
             */
            $data_transaction->update([
                'status' => 'pending'
            ]);
        } elseif ($transaction == 'deny') {

            /**
             *   update invoice to failed
             */
            $data_transaction->update([
                'status' => 'failed'
            ]);
        } elseif ($transaction == 'expire') {

            /**
             *   update invoice to expired
             */
            $data_transaction->update([
                'status' => 'expired'
            ]);
        } elseif ($transaction == 'cancel') {

            /**
             *   update invoice to failed
             */
            $data_transaction->update([
                'status' => 'failed'
            ]);
        }
        // Also run on capture-success (non-challenged)
        if (in_array($transaction, ['capture']) && isset($fraud) && $type === 'credit_card' && $fraud !== 'challenge') {
            $this->postPaymentSuccessActions($data_transaction);
        }
    }

    /**
     * After payment success: generate participant_id if missing, keep status_restpack default 'belum',
     * and send WhatsApp messages via WAHA to each participant phone.
     */
    protected function postPaymentSuccessActions(Transaksi $trx): void
    {
        // Decode participants
        $participants = [];
        if (!empty($trx->participants)) {
            $decoded = json_decode($trx->participants, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $participants = $decoded;
            }
        }
        if (empty($participants)) {
            return; // nothing to do
        }

        // Build map: ticketId => event name for message
        $ticketIds = collect($participants)->pluck('ticketId')->filter()->unique()->values();
        $tickets = $ticketIds->isNotEmpty() ? Event::whereIn('id', $ticketIds)->get(['id', 'nama_event']) : collect();
        $eventName = $tickets->keyBy('id')->map(fn($t) => $t->nama_event ?? ('Event #' . $t->id));

        $modified = false;
        foreach ($participants as $i => $p) {
            // assign participant_id if missing (deterministic per invoice + index)
            if (empty($p['participant_id'])) {
                $p['participant_id'] = 'PID-' . rand(1000, 9999);
                $modified = true;
            }
            if (empty($p['status_restpack'])) {
                $p['status_restpack'] = 'belum';
                $modified = true;
            }
            $participants[$i] = $p;
        }

        if ($modified) {
            $trx->participants = json_encode($participants);
            $trx->save();
        }

        // Build message text once (list all participants) and send to each participant phone
        $greetName = $trx->nama ?: ($participants[0]['name'] ?? 'Peserta');
        $lines = [];
        $lines[] = 'Hai ' . $greetName . ',';
        $lines[] = '';
        $lines[] = 'Kamu sudah bisa check tiket online melalui website daftar.mandalikakorprirun.com untuk pesanan berikut:';
        $lines[] = '';
        foreach ($participants as $p) {
            $jenis = isset($p['ticketId']) ? ($eventName[$p['ticketId']] ?? ('Event #' . $p['ticketId'])) : 'Tiket';
            $lines[] = 'ID Transaksi: ' . $trx->invoice;
            $lines[] = 'ID Peserta: ' . $p['participant_id'];
            $lines[] = 'Nama: ' . ($p['name'] ?? '-');
            $lines[] = 'Jenis Tiket: ' . $jenis;
            $lines[] = '';
            $lines[] = '==============================';
            $lines[] = '';
        }
        $lines[] = 'Check Dashboard kamu https://daftar.mandalikakorprirun.com/#/dashboard';
        $text = implode("\n", $lines);

        // Send to each participant's phone via WAHA
        foreach ($participants as $p) {
            $phone = $p['phone'] ?? null;
            if (!$phone) continue;
            $this->sendWhatsapp($phone, $text);
        }
    }

    protected function sendWhatsapp(string $phone, string $text): void
    {
        try {
            $base = rtrim(config('services.waha.base_url'), '/');
            $session = config('services.waha.session');
            $apiKey = config('services.waha.api_key');
            $chatId = $this->normalizePhone($phone);
            Http::withHeaders([
                'x-api-key' => $apiKey,
            ])->post($base . '/api/sendText', [
                'chatId' => $chatId,
                'session' => $session,
                'text' => $text,
            ]);
        } catch (\Throwable $e) {
            // swallow errors; optionally log
            \Log::warning('WA send failed: ' . $e->getMessage());
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
     * Update status_restpack to 'sudah' for a participant by invoice and participant_id
     */
    public function restpack(Request $request)
    {
        $request->validate([
            'invoice' => 'required|string',
            'participant_id' => 'required|string',
        ]);

        $trx = Transaksi::where('invoice', $request->input('invoice'))->first();
        if (!$trx) {
            return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
        }
        $participants = json_decode($trx->participants, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($participants)) {
            return response()->json(['message' => 'Participants not found'], 404);
        }
        $found = false;
        foreach ($participants as $i => $p) {
            if (($p['participant_id'] ?? null) === $request->input('participant_id')) {
                $participants[$i]['status_restpack'] = 'sudah';
                $found = true;
                break;
            }
        }
        if (!$found) {
            return response()->json(['message' => 'Participant not found'], 404);
        }
        $trx->participants = json_encode($participants);
        $trx->save();
        return response()->json(['message' => 'Status restpack updated']);
    }

    public function store(StorePendaftarRequest $request)
    {
        $u = (int)$request->input('total_bayar');

        for (
            $u = 0;
            $u < $request->input('total_bayar');
            $u++
        ) {
            $no_tiket = '0' . Pendaftar::orderBy('no_tiket', 'DESC')->first()->no_tiket + 1;
            // $pendaftar->no_tiket = '0' . Pendaftar::latest()->first()->nama;
            $total_bayar = Event::find($request->input('event_id'))->harga;
            $pendaftar = Pendaftar::create(array_merge($request->all(), [
                'no_tiket' => $no_tiket,
                'total_bayar' => $total_bayar,
            ]));
            if ($media = $request->input('ck-media', false)) {
                Media::whereIn('id', $media)->update(['model_id' => $pendaftar->id]);
            }
        }

        return redirect()->route('admin.pendaftars.index');
    }

    public function edit(Pendaftar $pendaftar)
    {
        abort_if(Gate::denies('pendaftar_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $events = Event::pluck('nama_event', 'id')->prepend(trans('global.pleaseSelect'), '');

        $pendaftar->load('event');

        return view('admin.pendaftars.edit', compact('events', 'pendaftar'));
    }

    public function update(UpdatePendaftarRequest $request, Pendaftar $pendaftar)
    {
        $pendaftar->update($request->all());

        return redirect()->route('admin.pendaftars.index');
    }

    public function show(Pendaftar $pendaftar)
    {
        abort_if(Gate::denies('pendaftar_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $pendaftar->load('event');

        return view('admin.pendaftars.show', compact('pendaftar'));
    }

    public function destroy(Pendaftar $pendaftar)
    {
        abort_if(Gate::denies('pendaftar_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $pendaftar->delete();

        return back();
    }

    public function massDestroy(MassDestroyPendaftarRequest $request)
    {
        Pendaftar::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function storeCKEditorImages(Request $request)
    {
        abort_if(Gate::denies('pendaftar_create') && Gate::denies('pendaftar_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $model         = new Pendaftar();
        $model->id     = $request->input('crud_id', 0);
        $model->exists = true;
        $media         = $model->addMediaFromRequest('upload')->toMediaCollection('ck-media');

        return response()->json(['id' => $media->id, 'url' => $media->getUrl()], Response::HTTP_CREATED);
    }
}
