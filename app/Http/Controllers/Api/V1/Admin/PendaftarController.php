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

        //cek apakah sudah kirim notifikasi
        // if ($trx->notifikasi == 0 && $trx->status == 'success') {
        //     $this->postPaymentSuccessActions($trx);
        // }

        // events may be serialized or JSON/plain; keep legacy behavior
        $noTiket = @unserialize($trx->events);
        if ($noTiket === false) {
            $noTiket = $trx->events;
        }

        $userDetail = User::where('id', $trx->peserta_id)->first();

        // Build QR URL if exists; do not generate here (generation happens on webhook or register)
        $qrPath = $noTiket ? public_path("qrcodes/{$noTiket}.png") : null;
        $qrUrl = ($qrPath && file_exists($qrPath)) ? url("/qrcodes/{$noTiket}.png") : null;

        // Get participants from participants table, backfill if needed
        $participants = $trx->participants();
        // if ($participants->count() == 0 && !empty($trx->getAttributes()['participants'])) {
        //     $decoded = json_decode($trx->getAttributes()['participants'], true);
        //     if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        //         foreach ($decoded as $i => $p) {
        //             $pid = $p['participant_id'] ?? ('PID-' . Str::upper(Str::random(7)));
        //             Participant::create([
        //                 'transaction_id' => $trx->id,
        //                 'participant_id' => $pid,
        //                 'name' => $p['name'] ?? null,
        //                 'nik' => $p['nik'] ?? null,
        //                 'email' => $p['email'] ?? null,
        //                 'phone' => $p['phone'] ?? null,
        //                 'province' => $p['province'] ?? null,
        //                 'city' => $p['city'] ?? null,
        //                 'ticket_id' => $p['ticketId'] ?? null,
        //                 'status_racepack' => $p['status_racepack'] ?? 'belum',
        //             ]);

        //             // Generate QR code for participant_id
        //             $qrDir = public_path('participants');
        //             if (!file_exists($qrDir)) {
        //                 mkdir($qrDir, 0755, true);
        //             }
        //             $qrPath = $qrDir . '/' . $pid . '.png';
        //             if (!file_exists($qrPath)) {
        //                 QrCode::format('png')->size(300)->generate($pid, $qrPath);
        //             }
        //         }
        //         $participants = $trx->participants()->get();
        //     }
        // } else {
        $participants = $participants->get();
        // }

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
            $query->where('amount' > 10000);
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
            $fee_service = $amount * 0.015;
            // $fee_service = $fee_service * count($itemDetails);
            $total_payment = $amount + $fee_service;
            //add service fee to itemDetails
            $itemDetails[] = [
                'id' => 'service-fee',
                'price' => (int) $fee_service,
                'quantity' => 1,
                'name' => 'Service Fee',
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

        //content type application/json
        $contentType = $request->header('Content-Type');
        if ($contentType !== 'application/json') {
            return response()->json(['message' => 'Invalid content type'], 400);
        }

        $payload      = $request->getContent();
        $notification = json_decode($payload);

        // Log incoming webhook summary for debugging
        // \Illuminate\Support\Facades\Log::info('Midtrans webhook received', [
        //     'order_id' => $notification->order_id ?? null,
        //     'transaction_status' => $notification->transaction_status ?? null,
        //     'payment_type' => $notification->payment_type ?? null,
        //     'fraud_status' => $notification->fraud_status ?? null,
        //     'status_code' => $notification->status_code ?? null,
        //     'raw_len' => strlen($payload),
        // ]);

        $validSignatureKey = hash('sha512', $notification->order_id . $notification->status_code . $notification->gross_amount . config('services.midtrans.serverKey'));

        if ($notification->signature_key != $validSignatureKey) {
            // \Illuminate\Support\Facades\Log::warning('Midtrans invalid signature', [
            //     'order_id' => $notification->order_id ?? null,
            //     'provided_signature' => $notification->signature_key ?? null,
            //     'computed_signature' => $validSignatureKey,
            //     'gross_amount' => $notification->gross_amount ?? null,
            //     'status_code' => $notification->status_code ?? null,
            // ]);
            return response(['message' => 'Invalid signature'], 403);
        }

        $transaction  = $notification->transaction_status;
        $type         = $notification->payment_type;
        $orderId      = $notification->order_id;
        $fraud        = $notification->fraud_status;

        // \Illuminate\Support\Facades\Log::debug('Midtrans parsed fields', [
        //     'order_id' => $orderId,
        //     'transaction' => $transaction,
        //     'type' => $type,
        //     'fraud' => $fraud,
        // ]);

        // find transaction by invoice
        $data_transaction = Transaksi::where('invoice', $orderId)->first();
        if (!$data_transaction) {
            // \Illuminate\Support\Facades\Log::warning('Midtrans webhook: transaction not found for order_id', [
            //     'order_id' => $orderId,
            // ]);
            return response()->json(['message' => 'Order not found'], 404);
        }

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
                    // Trigger post-success actions for non-challenged credit card capture
                    // \Illuminate\Support\Facades\Log::info('Midtrans capture success (non-challenge) -> postPaymentSuccessActions', [
                    //     'invoice' => $orderId,
                    // ]);
                    $this->postPaymentSuccessActions($data_transaction);
                }
            }
        } elseif ($transaction == 'settlement') {

            /**
             *   update invoice to success
             */
            $update = $data_transaction->update([
                'status' => 'success'
            ]);
            // Post-success processing: assign participant IDs, ensure status_racepack, and send WA
            // \Illuminate\Support\Facades\Log::info('Midtrans settlement -> postPaymentSuccessActions', [
            //     'invoice' => $orderId,
            // ]);
            // if($update){
            // $this->postPaymentSuccessActions($data_transaction);
            // }
            //cek apakah sudah kirim notifikasi
            if ($data_transaction->notifikasi == 0 && $data_transaction->status == 'success') {
                $this->postPaymentSuccessActions($data_transaction);
            }
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
                        'amount' => $trx->amount,
                        'status' => 1,
                        'province' => $p['province'] ?? null,
                        'city' => $p['city'] ?? null,
                        // 'address' => $p['address'] ?? null,
                        // 'postal_code' => $p['postal_code'] ?? null,
                        // 'country' => $p['country'] ?? null,
                        // 'latitude' => $p['latitude'] ?? null,
                        // 'longitude' => $p['longitude'] ?? null,
                        // 'created_by' => $trx->created_by,
                    ]);

                    // Generate QR code for participant_id
                    $qrDir = storage_path('app/public/participants');
                    // var_dump($qrDir);
                    // die;
                    if (!file_exists($qrDir)) {
                        mkdir($qrDir, 0755, true);
                    }
                    $qrPath = $qrDir . '/' . $pid . '.png';
                    if (!file_exists($qrPath)) {
                        QrCode::format('png')->size(300)->generate($pid, $qrPath);
                    }
                    // var_dump($qrPath);
                    // die;
                }
                // Reload participants
                $participants = $trx->participants()->get();
                // \Illuminate\Support\Facades\Log::debug('participants after backfill from JSON', [
                //     'count' => $participants->count(),
                // ]);
            }
        } else {
            $participants = $participants->get();
        }

        if ($participants->isEmpty()) {
            // \Illuminate\Support\Facades\Log::warning('No participants found for transaction, attempting fallback via user/no_tiket', [
            //     'trx_id' => $trx->id,
            //     'invoice' => $trx->invoice,
            // ]);

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

                // Build message (align with participants flow)
                $lines = [];
                $lines[] = 'Hai ' . ($user->name ?? 'Peserta') . ',';
                $lines[] = '';
                $lines[] = 'Kamu sudah bisa check tiket online melalui website untuk pesanan berikut:';
                $lines[] = '';
                $lines[] = 'ID Peserta: ' . ($noTiket ?? '-');
                $lines[] = 'Nama: ' . ($user->name ?? '-');
                $lines[] = 'Jenis Tiket: ' . $jenis;
                $lines[] = '';
                $url = 'https://daftar.mandalikakorprirun.com/dashboard';
                // $lines[] = 'Check Dashboard kamu di ' . $url;
                $lines[] = 'Cek Email untuk mengunduh E-tiket Anda ';
                $lines[] = 'Jika ada masalah, silahkan hubungi kami di nomor wa ini';
                $lines[] = 'Terima kasih';

                $text = implode("\n", $lines);

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

            $lines = [];
            $lines[] = 'Hai ' . ($p->name ?? 'Peserta') . ',';
            $lines[] = '';
            $lines[] = 'Kamu sudah bisa check tiket online melalui website untuk pesanan berikut:';
            $lines[] = '';
            $lines[] = 'ID Peserta: ' . $p->participant_id;
            $lines[] = 'Nama: ' . ($p->name ?? '-');
            $lines[] = 'Jenis Tiket: ' . $jenis;
            $lines[] = '';
            $url = 'https://daftar.mandalikakorprirun.com/dashboard';
            // $lines[] = 'Check Dashboard kamu di ' . $url;
            $lines[] = 'Cek Email untuk mengunduh E-tiket Anda ';
            $lines[] = 'Jika ada masalah, silahkan hubungi kami di nomor wa ini';
            $lines[] = 'Terima kasih';

            $text = implode("\n", $lines);

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
                $trx->update([
                    'notifikasi' => 1,
                ]);
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

        // Build a base filter (without status) to compute totals and list
        $base = Participant::with(['staff:id,name'])
            ->select(['id', 'transaction_id', 'participant_id', 'name', 'email', 'phone', 'province', 'city', 'ticket_id', 'status_racepack', 'staff_user_id', 'racepack_by', 'racepack_at'])
            ->where('status', '1');

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
            ],
        ]);
    }
}
