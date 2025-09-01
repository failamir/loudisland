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
use App\Models\Pendaftar;
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

        $pendaftars = Pendaftar::with(['event'])->get();

        $events = Event::get();

        return view('admin.pendaftars.index', compact('events', 'pendaftars'));
    }

    public function myorder()
    {
        // abort_if(Gate::denies('pendaftar_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        // if(!Auth::check()) {
        //     return response()->json([
        //         'message' => 'Unauthorized',
        //         'status' => 401,
        //     ]);
        // }

        $user = User::where('uid', $_POST['uid'])->first();
        $transaksi = array();
        $transaksi = Transaksi::where('peserta_id', $user->id)->first();
        $pendaftar = new stdClass();
        // $pendaftar->data = Pendaftar::with(['event'])->get();
        $pendaftar->no_tiket = $user->no_tiket;
        $pendaftar->message = 'success';
        $pendaftar->status = 200;
        $pendaftar->data = $transaksi;
        $pendaftar->qr = QrCode::format('png')->size(300)->generate($transaksi->invoice);
        // return view('admin.pendaftars.detailOrder', compact('pendaftar'));
        return response()->json($pendaftar);
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

        return new PendaftarResource(Pendaftar::with(['event'])->where('checkin', 'sudah')->paginate(10));
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

        return new PendaftarResource(Pendaftar::with(['event'])->where('checkin', 'terpakai')->paginate(10));
    }

    public function create()
    {
        abort_if(Gate::denies('pendaftar_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $events = Event::pluck('nama_event', 'id')->prepend(trans('global.pleaseSelect'), '');
        $no_t = Pendaftar::orderBy('no_tiket', 'DESC')->first();
        return view('admin.pendaftars.create', compact('events', 'no_t'));
    }

    public function apibeli(Request $request)
    {
        return view('admin.pendaftars.beli', compact('request'));
    }

    public function beli(Request $request)
    {
        // $u = ( int )$request->input( 'total_bayar' );

        // for ( $u = 0; $u<$request->input( 'total_bayar' );
        // $u++ ) {
        //     $no_tiket = '0' . Pendaftar::orderBy( 'no_tiket', 'DESC' )->first()->no_tiket + 1;
        //     // $pendaftar->no_tiket = '0' . Pendaftar::latest()->first()->nama;
        //     $total_bayar = Event::find( $request->input( 'event_id' ) )->harga;
        //     $pendaftar = Pendaftar::create( array_merge( $request->all(), [
        //         'no_tiket' => $no_tiket,
        //         'total_bayar' => $total_bayar,
        // ] ) );
        //     if ( $media = $request->input( 'ck-media', false ) ) {
        //         Media::whereIn( 'id', $media )->update( [ 'model_id' => $pendaftar->id ] );
        //     }

        // }
        $events = Event::pluck('nama_event', 'id')->prepend(trans('global.pleaseSelect'), '');
        $no_t = Pendaftar::orderBy('no_tiket', 'DESC')->first();
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
            // $pendaftar->no_tiket = '0' . Pendaftar::latest()->first()->nama;
            // $total_bayar = Event::find( 1 )->harga;
            // $amount += $total_bayar;
            $code = uniqid() . uniqid();
            $pendaftar = Pendaftar::create(array_merge($request->all(), [
                'nama' => 'generate',
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
    public function checkin(Request $request)
    {
        $pendaftar = Pendaftar::where('no_tiket', $request->input('no_tiket'))->first();
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
        $pendaftar = Pendaftar::where('no_tiket', $request->input('no_tiket'))->first();
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
        $pendaftar = Pendaftar::where('no_tiket', $request->input('no_tiket'))->first();
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
                $p = Pendaftar::where('no_tiket', $noTiket)->first();
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
        $noTiket = @unserialize($trx->events);
        if ($noTiket === false) {
            $noTiket = $trx->events;
        }
        $pendaftar = $noTiket ? Pendaftar::where('no_tiket', $noTiket)->first() : null;

        // Build QR URL if exists; do not generate here (generation happens on webhook or register)
        $qrPath = $noTiket ? public_path("qrcodes/{$noTiket}.png") : null;
        $qrUrl = ($qrPath && file_exists($qrPath)) ? url("/qrcodes/{$noTiket}.png") : null;

        return response()->json([
            'invoice' => $trx->invoice,
            'status' => $trx->status,
            'amount' => $trx->amount,
            'no_tiket' => $noTiket,
            'qr_url' => $qrUrl,
            'pendaftar' => $pendaftar ? [
                'id' => $pendaftar->id,
                'nama' => $pendaftar->nama,
                'email' => $pendaftar->email,
                'no_hp' => $pendaftar->no_hp,
                'status_payment' => $pendaftar->status_payment,
                'event_id' => $pendaftar->event_id,
                'nomor_punggung' => $pendaftar->nomor_punggung,
                'start_at' => $pendaftar->start_at,
                'finish_at' => $pendaftar->finish_at,
            ] : null,
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
        $last = Pendaftar::orderBy('no_tiket', 'DESC')->first();
        $next = $last && is_numeric($last->no_tiket) ? ((int)$last->no_tiket + 1) : 1;
        $no_tiket = (string)$next;

        $event = Event::findOrFail($request->input('event_id'));

        $pendaftar = Pendaftar::create([
            'no_tiket' => $no_tiket,
            'nama' => $request->input('nama'),
            'nik' => $request->input('nik'),
            'email' => $request->input('email'),
            'no_hp' => $request->input('no_hp'),
            'province' => $request->input('province'),
            'city' => $request->input('city'),
            'address' => $request->input('address'),
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
                'first_name' => $pendaftar->nama,
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
        $p = Pendaftar::where('no_tiket', $request->input('no_tiket'))->firstOrFail();
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
        $p = Pendaftar::where('no_tiket', $request->input('no_tiket'))->firstOrFail();
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

        $query = Pendaftar::with('event');
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
            'nama' => $p->nama,
            'email' => $p->email,
            'no_hp' => $p->no_hp,
            'status_payment' => $p->status_payment,
            'checkin' => $p->checkin,
            'nomor_punggung' => $p->nomor_punggung,
            'event' => $p->event ? [
                'id' => $p->event->id,
                'nama_event' => $p->event->nama_event,
                'tanggal' => $p->event->tanggal ?? null,
            ] : null,
        ]);
    }

    /**
     * List pairing nomor punggung yang sudah terisi.
     */
    public function listPairing(Request $request)
    {
        $q = Pendaftar::query()->whereNotNull('nomor_punggung');
        if ($request->filled('search')) {
            $term = '%' . $request->input('search') . '%';
            $q->where(function ($w) use ($term) {
                $w->where('no_tiket', 'like', $term)
                    ->orWhere('nama', 'like', $term)
                    ->orWhere('nomor_punggung', 'like', $term);
            });
        }
        $items = $q->orderBy('nomor_punggung')->limit(200)->get(['id', 'no_tiket', 'nama', 'nomor_punggung']);
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
        $data = $request->all();
        $rules = [
            'userId' => 'required', //Must be a number and length of value is 8
            'ticketId' => 'required',
            'province' => 'nullable',
            'city' => 'nullable',
            // 'address' => 'required',
            'phone' => 'required',
            'nik' => 'required',
            'email' => 'required',
            'name' => 'required',
        ];

        $validator = Validator::make($data, $rules);
        if (!$validator->fails()) {
            //TODO Handle your data
            $data = $request->all();

            $length = 10;
            $random = '';
            for ($i = 0; $i < $length; $i++) {
                $random .= rand(0, 1) ? rand(0, 9) : chr(rand(ord('a'), ord('z')));
            }

            $no_invoice = 'TRX-' . Str::upper($random);

            $amount = Event::find($data['ticketId'])->harga;
            $user = User::where('uid', $data['userId'])->first();

            if ($user) {
                $data['peserta_id'] = $user->id;
            } else {
                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'uid' => $data['userId'],
                    'province' => $data['province'],
                    'city' => $data['city'],
                    //'address' => $data['address'],
                    'no_hp' => $data['phone'],
                    'nik' => $data['nik'],
                    'password' => $data['phone'],
                ]);
                $data['peserta_id'] = $user->id;
            }

            $transaksi = Transaksi::create([
                'invoice'       => $no_invoice,
                'events'   => $data['ticketId'],
                'peserta_id'    => $user->id,
                'amount'        => $amount,
                'note'          => $user->name,
                'status'        => 'pending',
                'uid'        => $user->uid,
                'province' => $data['province'],
                'city' => $data['city'],
                // 'address' => $data['address'],
                'no_hp' => $data['phone'],
                'nik' => $data['nik'],
                'email' => $data['email'],
                'nama' => $data['name'],
            ]);

            $payload = [
                'transaction_details' => [
                    'order_id'      => $transaksi->invoice,
                    'gross_amount'  => $transaksi->amount,
                ],
                'customer_details' => [
                    'first_name'       => $user->name,
                    'email'            => $user->email,
                ]
            ];

            $paymentUrl = Snap::createTransaction($payload)->redirect_url;
            Transaksi::where('invoice', $no_invoice)->update([
                'payment_url' => $paymentUrl
            ]);

            $data = new stdClass();
            $data->data = $paymentUrl;
            $data->invoice = $no_invoice;
            return response()->json($data);
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
