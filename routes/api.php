<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Api\V1\Admin\RoleApiController;
use App\Http\Controllers\Api\V1\Admin\PermissionApiController;
use App\Http\Controllers\Api\V1\Admin\NomorPunggungApiController;
use App\Http\Controllers\Api\V1\Admin\AuthController;
use App\Http\Controllers\Api\V1\Admin\UserApiController;
use App\Http\Controllers\Api\V1\Admin\PendaftarApiController;
use App\Http\Controllers\Api\V1\Admin\QrCodeApiController;
use App\Http\Controllers\Api\V1\Admin\TransactionsListController;
use App\Http\Controllers\Api\V1\Admin\SponsorApiController;
use App\Http\Controllers\Api\V1\Admin\SettingApiController;
use App\Http\Controllers\Api\V1\Admin\EventApiController;
use App\Http\Controllers\Api\V1\Admin\BannerApiController;
use App\Http\Controllers\Api\V1\Admin\TransaksiApiController;
use App\Http\Controllers\Api\V1\Admin\TiketApiController;
use App\Http\Controllers\Api\V1\Admin\PendaftarController;
use App\Http\Controllers\Api\V1\Admin\OrderController;
use App\Http\Controllers\Api\V1\Admin\WithdrawalController;

// use Illuminate\Http\Client\Http;
// Route::group(['prefix' => 'v1', 'as' => 'api.', 'namespace' => 'Api\\V1\\Admin', 'middleware' => ['auth:sanctum']], function () {
Route::group(['prefix' => 'v1', 'as' => 'api.', 'namespace' => 'Api\\V1\\Admin'], function () {
    // Auth
    Route::post('register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('login', [AuthController::class, 'login'])->name('auth.login');
    Route::post('refresh', [AuthController::class, 'refresh'])->name('auth.refresh');
    Route::post('get-token', [AuthController::class, 'getToken'])->name('auth.getToken');
    Route::middleware('auth:api')->group(function () {
        Route::get('me', [AuthController::class, 'me'])->name('auth.me');
        // Users API for FE admin
        Route::get('users', [UserApiController::class, 'index']);
        Route::get('users/{user}', [UserApiController::class, 'show']);
        Route::post('users', [UserApiController::class, 'store']);
        Route::put('users/{user}', [UserApiController::class, 'update']);
        Route::delete('users/{user}', [UserApiController::class, 'destroy']);

        // Roles & Permissions CRUD for FE admin
        Route::apiResource('roles', RoleApiController::class);
        Route::apiResource('permissions', PermissionApiController::class);

        // Racepack listing (filterable)
        Route::get('racepacks', [PendaftarController::class, 'racepackList'])->name('racepacks.index');
        // Staff list for racepack dropdown
        Route::get('staffs', [PendaftarController::class, 'staffList'])->name('racepacks.staffs');

        // Blast to registered participants (protected)
        Route::post('participants/whatsapp-blast', [PendaftarController::class, 'whatsappBlast'])->name('participants.whatsappBlast');
        Route::post('participants/email-blast', [PendaftarController::class, 'emailBlast'])->name('participants.emailBlast');

        // Orders (create ticket + transaction via Midtrans)
        Route::post('orders', [OrderController::class, 'store'])->name('orders.store');

        // Nomor Punggung QR API
        Route::get('nomor-punggung', [NomorPunggungApiController::class, 'index']);
        Route::post('nomor-punggung/pair', [NomorPunggungApiController::class, 'pair']);
        Route::post('nomor-punggung/generate', [NomorPunggungApiController::class, 'generate']);
        Route::post('nomor-punggung/unpair', [NomorPunggungApiController::class, 'unpair']);

        // Scanner endpoints for race day (protected)
        Route::post('scan/start', [PendaftarController::class, 'scanStart'])->name('scan.start');
        Route::post('scan/finish', [PendaftarController::class, 'scanFinish'])->name('scan.finish');

        // Pairings listing (protected)
        Route::get('pairings', [PendaftarController::class, 'listPairing'])->name('pairings');

        //partisipan tukar id partisipan racepack
        Route::get('racepack', [PendaftarController::class, 'listRacepack'])->name('racepack');
        Route::post('racepack', [PendaftarController::class, 'racepack'])->name('racepack');
        Route::post('reset-racepack', [PendaftarController::class, 'resetRacepack'])->name('reset-racepack');

        // QR Codes API for FE consumption (protected)
        Route::get('qrcodes', [QrCodeApiController::class, 'index'])->name('qrcodes.index');
        Route::get('qrcodes/download-all', [QrCodeApiController::class, 'downloadAll'])->name('qrcodes.downloadAll');

        // Withdrawals
        Route::get('withdrawals', [WithdrawalController::class, 'index'])->name('withdrawals.index');
        Route::get('withdrawals/summary', [WithdrawalController::class, 'summary'])->name('withdrawals.summary');
        Route::post('withdrawals', [WithdrawalController::class, 'store'])->name('withdrawals.store');
        Route::get('withdrawals/{withdrawal}', [WithdrawalController::class, 'show'])->name('withdrawals.show');
        Route::patch('withdrawals/{withdrawal}/status', [WithdrawalController::class, 'updateStatus'])->name('withdrawals.updateStatus');

        Route::post('logout', [AuthController::class, 'logout'])->name('auth.logout');
    });

    // Pendaftar
    Route::post('buy', [PendaftarController::class, 'beliApi'])->name('buy');
    Route::post('myorder', [PendaftarController::class, 'myorder'])->name('myorder');
    Route::post('myticket', [PendaftarController::class, 'myticket'])->name('myticket');
    Route::post('daftar', [PendaftarController::class, 'daftar'])->name('daftar');
    Route::get('profile', [PendaftarController::class, 'profile'])->name('profile');
    Route::post('updateprofile', [PendaftarController::class, 'updateprofile'])->name('updateprofile');
    Route::get('transaksi', [PendaftarController::class, 'transaksi'])->name('transaksi');
    Route::get('transactions', [PendaftarController::class, 'transaksi'])->name('transactions');
    // Simple transactions list for FE
    Route::get('transactions/simple', [TransactionsListController::class, 'index'])->name('transactions.simple');
    Route::get('tiket', [PendaftarController::class, 'tiket'])->name('tiket');
    // List success transactions without participant rows
    Route::get('participants/missing', [PendaftarController::class, 'missingParticipants'])->name('participants.missing');

    Route::get('participants/{participant}', [PendaftarController::class, 'showParticipant'])->name('participants.show');
    Route::get('ticket/{participant_id}', [PendaftarController::class, 'ticket'])->name('ticket.show');
    
    // Update shirt size for participants
    Route::put('participants/{participant_id}/shirt-size', [PendaftarController::class, 'updateShirtSize'])->name('participants.updateShirtSize');
    Route::post('participants/bulk-update-shirt-size', [PendaftarController::class, 'bulkUpdateShirtSize'])->name('participants.bulkUpdateShirtSize');

    // Generate/backfill participants for a transaction
    Route::post('participants/generate', [PendaftarController::class, 'generateParticipants'])->name('participants.generate');
    Route::post('notification', [PendaftarController::class, 'notificationHandler'])->name('notification');
    // New simplified registration that creates ticket and returns Midtrans URL
    Route::post('register-ticket', [PendaftarController::class, 'registerTicket'])->name('register-ticket');

    Route::post('scan', [PendaftarController::class, 'scan'])->name('scan');
    Route::post('checkin1', [PendaftarController::class, 'checkin1'])->name('checkin1');
    Route::post('checkin2', [PendaftarController::class, 'checkin2'])->name('checkin2');
    Route::post('checkout', [PendaftarController::class, 'checkout'])->name('checkout');
    Route::post('pendaftars/media', [PendaftarController::class, 'storeMedia'])->name('pendaftars.storeMedia');
    Route::apiResource('pendaftars', PendaftarApiController::class);

    // Public QR endpoints removed; use protected versions above

    // Payment status for FE
    Route::get('payment/{invoice}', [PendaftarController::class, 'paymentStatus'])->name('payment.status');

    Route::get('list_checkin', [PendaftarController::class, 'list_checkin']);
    Route::get('list_checkout', [PendaftarController::class, 'list_checkout']);
    // Tiket
    Route::post('tikets/media', [TiketApiController::class, 'storeMedia'])->name('tikets.storeMedia');
    Route::apiResource('tikets', TiketApiController::class);

    // Event
    Route::post('events/media', [EventApiController::class, 'storeMedia'])->name('events.storeMedia');
    Route::apiResource('events', EventApiController::class);

    // Banner
    Route::post('banners/media', [BannerApiController::class, 'storeMedia'])->name('banners.storeMedia');
    Route::apiResource('banners', BannerApiController::class);

    // Transaksi
    Route::post('transactions/media', [TransaksiApiController::class, 'storeMedia'])->name('transactions.storeMedia');
    // Extra show endpoint to fetch by query (?id= or ?invoice=) for modal usage
    Route::get('transactions/show', [TransaksiApiController::class, 'show'])->name('transactions.showByQuery');
    Route::apiResource('transactions', TransaksiApiController::class)
        ->parameters(['transactions' => 'transaksi']);

    // Sponsor
    Route::post('sponsors/media', [SponsorApiController::class, 'storeMedia'])->name('sponsors.storeMedia');
    Route::apiResource('sponsors', SponsorApiController::class);

    // Setting
    Route::apiResource('settings', SettingApiController::class);

    // Event
    Route::apiResource('events', EventApiController::class);

    Route::get('wilayah/provinces', function () {
        $response = Http::get('https://wilayah.id/api/provinces.json');
        return $response->json();
    });
    Route::get('wilayah/regencies/{provinceCode}', function ($provinceCode) {
        $response = Http::get("https://wilayah.id/api/regencies/{$provinceCode}.json");
        return $response->json();
    });
    Route::get('wilayah/districts/{regencyCode}', function ($regencyCode) {
        $response = Http::get("https://wilayah.id/api/districts/{$regencyCode}.json");
        return $response->json();
    });
    Route::get('wilayah/villages/{districtCode}', function ($districtCode) {
        $response = Http::get("https://wilayah.id/api/villages/{$districtCode}.json");
        return $response->json();
    });

    // Hardcoded tickets list (2 items)
    Route::get('tickets/hardcoded', function () {
        return response()->json([
            ['id' => 1, 'name' => '5K Fun Run', 'price' => 100000],
            ['id' => 2, 'name' => '10K Race', 'price' => 150000],
        ]);
    });

    //get all participants
    Route::get('participants', function () {
        $excluded_emails = explode(',', env('EMAIL_TESTING', ''));
        $query = \App\Models\Participant::where('status', '1')
            ->where('amount', '>', 100000)
            ->whereNotIn('email', $excluded_emails)
            ->whereNull('shirt_size')
            ->get();
        return response()->json([
            'data' => $query,
            'total' => count($query),
            'total_income' => $query->sum('amount'),
        ]);
    });

    // Admin utilities
    Route::get('total-income', function () {
        // $testingEmail = env('EMAIL_TESTING');

        // $query = \App\Models\Transaksi::query()
        //     ->where('status', 'success')
        //     ->whereHas('participants');
        $excluded_emails = explode(',', env('EMAIL_TESTING', ''));
        $query = \App\Models\Participant::query()
            ->where('status', '1')
            ->where('amount', '>', 100000)
            ->whereNotIn('email', $excluded_emails);
        // ->whereHas('participants');

        //where status in participant table = 1
        // $query->whereHas('participants', function ($q) {
        //     $q->where('status', '1');
        // });

        $count = (clone $query)->count();
        // print('total_tiket: ');
        // var_dump($count);
        $grossSum = (int) (clone $query)->sum('amount');
        // print('total_masuk: ');
        // var_dump($grossSum);
        $profit = (int) ($count * 5000) + (floor($grossSum * 0.02));
        // print('profit: ');
        // var_dump($profit);
        $netIncome = max(0, $grossSum - $profit);
        // print('netIncome : ');
        // var_dump($netIncome);
        // $netIncome = $netIncome - 8119;

        return response()->json([
            'total_income' => $netIncome,
            'summary' => [
                'gross_sum' => $grossSum,
                'count' => $count,
                'profit' => $profit,
                'net_income' => $netIncome,
            ],
        ]);
    });

    // TODO: email to ifailamir@kardusinfo.com and kardusinfo@failamir.com


    // buat api untuk hit waha
    // https://waha-1tssjsoucdmi.cinta.sumopod.my.id/api/sendImage
    // x-api-key:YV5CtoFFOFVAx3kOMfLrryCXiXK4lQpg
    // {
    //     "chatId": "6282282225802",
    //     "file": {
    //       "mimetype": "image/jpeg",
    //       "filename": "filename.jpg",
    //       "url": "https://akcdn.detik.net.id/visual/2015/09/23/3a9afdbe-dc0d-4ecb-bf5a-8ae156b84c45_169.jpg?w=1200"
    //     },
    //     "reply_to": null,
    //     "caption": "mirip kamu",
    //     "session": "KORPRIRUN"
    //   }

    Route::post('waha/sendImage', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'chatId' => 'required|string',
            'url' => 'required|string',
            'caption' => 'nullable|string',
        ]);

        $baseUrl = rtrim(config('services.waha.base_url'), '/');
        $apiKey = config('services.waha.api_key');
        $session = config('services.waha.session');

        if (!$baseUrl || !$apiKey || !$session) {
            return response()->json([
                'status' => 'error',
                'message' => 'WAHA configuration is missing',
            ], 500);
        }

        $url = trim($request->input('url'));
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid image URL',
            ], 422);
        }

        $caption = (string) $request->input('caption', 'QR ID Peserta');

        // Ensure filename is safe and has a proper extension
        $filename = trim($caption) !== ''
            ? preg_replace('/[^A-Za-z0-9._-]/', '_', $caption)
            : 'image';

        //     $url = $request->input('url', 'https://gooogle.com');

        //     // Add proper extension based on URL or default to jpg
        $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
        if (!in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $extension = 'jpg';
        }
        $filename = "$filename.$extension";

        $payload = [
            'chatId' => $request->input('chatId'),
            'file' => [
                'mimetype' => 'image/' . ($extension === 'jpg' ? 'jpeg' : $extension),
                'filename' => $filename,
                'url' => $url,
            ],
            'reply_to' => null,
            'caption' => $caption,
            'session' => $session,
        ];

        try {
            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
                ->timeout(30)
                ->connectTimeout(15)
                ->post($baseUrl . '/api/sendImage', $payload);
            // var_dump($response);
            // die;
            if ($response->successful()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Image sent successfully',
                ], 200);
            }

            // Non-2xx from WAHA
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send image',
                'upstream_status' => $response->status(),
                'error' => $response->json() ?? $response->body(),
            ], 502);
        } catch (ConnectionException $e) {
            \Log::warning('WAHA sendImage timeout/connection error', [
                'exception' => $e->getMessage(),
                'chatId' => $request->input('chatId'),
                'url' => $url,
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Upstream WAHA service timeout or connection issue',
                'hint' => 'Ensure the image URL is publicly accessible from WAHA and try again.',
            ], 504);
        }
    });


    // Send link with custom preview (high quality) via WAHA
    // Accepts:
    // - chatId: string (required)
    // - url: string (required) -> link to preview
    // - text: string (optional) -> message text that may include the same URL
    // - title: string (optional)
    // - description: string (optional)
    // - image_base64: string (optional, base64 without data URI)
    // - image_url: string (optional, if provided we will fetch and convert to base64)
    Route::post('waha/sendLinkPreview', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'chatId' => 'required|string',
            'url' => 'required|string|url',
            'text' => 'nullable|string',
            'title' => 'nullable|string',
            'description' => 'nullable|string',
            'image_base64' => 'nullable|string',
            'image_url' => 'nullable|string',
        ]);

        $baseUrl = rtrim(config('services.waha.base_url'), '/');
        $apiKey = config('services.waha.api_key');
        $session = config('services.waha.session');

        if (!$baseUrl || !$apiKey || !$session) {
            return response()->json([
                'status' => 'error',
                'message' => 'WAHA configuration is missing',
            ], 500);
        }

        $imageBase64 = $request->string('image_base64');
        $imageUrl = $request->string('image_url');
        if (!$imageBase64 && $imageUrl) {
            try {
                $imgResp = Http::timeout(20)->connectTimeout(10)->get($imageUrl);
                if ($imgResp->successful()) {
                    $imageBase64 = base64_encode($imgResp->body());
                }
            } catch (\Throwable $e) {
                // ignore image fetch failure, send without image
            }
        }

        $payload = [
            'session' => $session,
            'chatId' => $request->input('chatId'),
            'text' => $request->input('text', 'Check this out! ' . $request->input('url')),
            'linkPreviewHighQuality' => true,
            'preview' => [
                'url' => $request->input('url'),
                'title' => $request->input('title', ''),
                'description' => $request->input('description', ''),
            ],
        ];

        if ($imageBase64) {
            $payload['preview']['image'] = [
                'data' => $imageBase64,
            ];
        }

        try {
            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(30)->connectTimeout(15)
                ->post($baseUrl . '/api/send/link-custom-preview', $payload);

            if ($response->successful()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Link preview sent successfully',
                ], 200);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send link preview',
                'upstream_status' => $response->status(),
                'error' => $response->json() ?? $response->body(),
            ], 502);
        } catch (ConnectionException $e) {
            // \Log::warning('WAHA sendLinkPreview timeout/connection error', [
            //     'exception' => $e->getMessage(),
            // ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Upstream WAHA service timeout or connection issue',
            ], 504);
        }
    });

    // Send plain text via WAHA (proxy)
    Route::post('waha/sendText', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'chatId' => 'required|string',
            'text' => 'required|string',
        ]);

        $baseUrl = rtrim(config('services.waha.base_url'), '/');
        $apiKey = config('services.waha.api_key');
        $session = config('services.waha.session');

        if (!$baseUrl || !$apiKey || !$session) {
            return response()->json([
                'status' => 'error',
                'message' => 'WAHA configuration is missing',
            ], 500);
        }

        $payload = [
            'session' => $session,
            'chatId' => $request->input('chatId'),
            'text' => $request->input('text'),
        ];

        try {
            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(30)->connectTimeout(15)
                ->post($baseUrl . '/api/sendText', $payload);

            if ($response->successful()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Text sent successfully',
                ], 200);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send text',
                'upstream_status' => $response->status(),
                'error' => $response->json() ?? $response->body(),
            ], 502);
        } catch (ConnectionException $e) {
            Log::warning('WAHA sendText timeout/connection error', [
                'exception' => $e->getMessage(),
                'chatId' => $request->input('chatId'),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Upstream WAHA service timeout or connection issue',
            ], 504);
        }
    });

    //buat api untuk menerima csv midtrans transaction yg settlement
    //lalu mencarinya di tabel transactions lalu update status menjadi success
    //lalu mengupdate di tabel participant status menjadi success
    //isi kolomnya Date & time,Order ID,Channel,Transaction type,Amount,Transaction status,Transaction ID,Transaction time,Customer e-mail,Note

    Route::post('midtrans/settlement', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('file');
        // Store on the public disk so the file resides under storage/app/public/midtrans
        $path = $file->store('public/midtrans');
        // Read back using the absolute storage path (avoid double 'public/' prefix)
        $data = array_map('str_getcsv', file(storage_path('app/' . $path)));
        $header = array_shift($data);

        // foreach ($data as $row) {
        //     $trx = \App\Models\Transaksi::where('invoice', $row[1])->first();
        //     if ($trx) {
        //         $trx->status = 'success';
        //         $trx->save();
        //         // Participants are attached to the transaction via relation; avoid JSON attribute collision
        //         $participants = $trx->participants()->get();
        //         // var_dump($participants);
        //         // die;
        //         // Normalize amount from CSV (strip non-digits)
        //         $amount = isset($row[4]) ? (int) preg_replace('/[^0-9]/', '', $row[4]) : null;
        //         foreach ($participants as $p) {
        //             $p->status = '1';
        //             if (!is_null($amount)) {
        //                 $p->amount = \App\Models\Event::where('id', $p->ticket_id)->first()->harga;
        //             }
        //             $p->save();
        //         }
        //     }
        // }

        // update status semua transaction sesuai dari csv
        // kalo ga ada di csv update status nya jadi pending
        $trx = \App\Models\Transaksi::all();
        foreach ($trx as $row) {
            $row->status = 'pending';
            $row->save();
        }

        // update status participant semua jadi 0
        $participants = \App\Models\Participant::all();
        foreach ($participants as $row) {
            $row->status = '0';
            $row->save();
        }

        foreach ($data as $row) {
            // Hanya proses transaksi dengan Amount > 10000 (data kecil dianggap testing)
            $amountCsv = isset($row[4]) ? (int) preg_replace('/[^0-9]/', '', $row[4]) : 0;
            if ($amountCsv <= 10000) {
                continue;
            }
            $trx = \App\Models\Transaksi::where('invoice', $row[1])->first();
            if ($trx) {
                // Normalisasi status dari CSV (kolom 6 / index 5)
                $csvStatus = isset($row[5]) ? strtolower(trim($row[5])) : '';
                // Map settlement -> success; yang lain biarkan apa adanya
                $mappedStatus = $csvStatus === 'settlement' ? 'success' : $csvStatus;

                $trx->status = $mappedStatus ?: $trx->status;
                $trx->save();

                // HANYA update participant jika transaksi SUCCESS
                if ($trx->status === 'success') {
                    $participants = $trx->participants()->get();
                    foreach ($participants as $p) {
                        $event = \App\Models\Event::where('id', $p->ticket_id)->first();
                        if ($event) {
                            $p->amount = $event->harga;
                        }
                        $p->status = '1';
                        $p->save();
                    }
                }

                // jika tidak ada data participant maka dibuat, 
                // $trx->participants()->create([
                //     'ticket_id' => $trx->ticket_id,
                //     'name' => $row[3],
                //     'email' => $row[4],
                //     'phone' => $row[5],
                //     'status' => '1',
                // ]);
                // $participants = $trx->participants()->get();
                // foreach ($participants as $p) {
                //     $p->status = '1';
                //     $p->save();
                // }
            }
        }

        $sumAmountParticipant = \App\Models\Participant::where('status', '1')->sum('amount');
        $trx = \App\Models\Transaksi::where('status', 'success')->sum('amount');

        return response()->json([
            'message' => 'Midtrans settlement processed successfully',
            'sumAmountParticipant' => $sumAmountParticipant,
            'trx' => $trx,
        ]);
    });

    Route::post('waha/sendBlast', function (Request $request) {
        $request->validate([
            'chatId' => 'required',
            'image' => 'nullable|image',
            'url' => 'nullable|url',
            'caption' => 'nullable|string',
        ]);

        // kirim ke semua no hp , ubah 0 didepannya jadi 62, jeda kirim nya 2 detik

        // $noHp = [
        //     '6282237099388',
        //     '6282282225802',
        //     '6281806206202',
        //     '6281286047365',
        // ];

        // $noHp = require __DIR__ . '/data_peserta.php';
        //get $data  from csv file

        $hasCsv = $request->hasFile('csv') || $request->hasFile('file');
        $response = null;

        if ($hasCsv) {
            // Baca CSV sebagai array baris, bukan string mentah
            $uploaded = $request->file('csv') ?: $request->file('file');
            $path = $uploaded->store('public/waha');
            $rows = array_map('str_getcsv', file(storage_path('app/' . $path)));

            // Opsional: lewati header jika baris pertama berisi teks header
            if (!empty($rows)) {
                $first = $rows[0];
                $firstCell = isset($first[0]) ? strtolower(trim($first[0])) : '';
                if (strpos($firstCell, 'peserta') !== false || strpos($firstCell, 'id') !== false) {
                    array_shift($rows);
                }
            }

            // header nya peserta_id,nama,no_hp
            $dataPeserta = array_map(function ($row) {
                return [
                    'no_hp' => $row[0] ?? '',
                    // 'peserta_id' => $row[0] ?? '',
                    // 'nama' => $row[1] ?? '',
                    // 'no_hp' => $row[2] ?? '',
                ];
            }, $rows);
            // dd($dataPeserta);
            foreach ($dataPeserta as $no) {
                // $no = '62' . substr($no['no_hp'], 1);
                $no = '62' . $no['no_hp'];
                // $data = [
                //     'chatId' => $no,
                //     'file' => [
                //         'mimetype' => 'application/pdf',
                //         'filename' => 'Surat Undangan Peserta Dialog Interaktif Menko Pemberdayaan Masyarakat di Kota Kupang.pdf',
                //         'url' => 'https://mandalikakorprirun.com/storage/Surat%20Undangan%20Peserta%20Dialog%20Interaktif%20Menko%20Pemberdayaan%20Masyarakat%20di%20Kota%20Kupang.pdf',
                //     ],
                //     'reply_to' => null,
                //     'caption' => '',
                //     'session' => 'Nyala',
                // ];
                // $response = Http::withHeaders([
                //     'x-api-key' => 'df3rWS9MH4lWzj5Al5COhDnX4wsqT72L',
                //     'Content-Type' => 'application/json',
                //     'Accept' => 'application/json',
                // ])->post('https://waha-nco1sqgcadk4.babat.sumopod.my.id/api/sendFile', $data);

                // sleep(8);
                $caption = <<<TXT
                ✨️Selamat Pagi Bapak/Ibu✨️ 

                Berdasarkan Undangan Peserta Dialog Interaktif bersama Menko Pemberdayaan Masyarakat di Kota Kupang, bersama ini kami sampaikan adanya perubahan lokasi pelaksanaan kegiatan, yang
                semula direncanakan di Aula GMIT Center, menjadi:

                📅Hari/Tanggal : Rabu, 01 Oktober 2025
                ⏰️Waktu : 15.00 WITA – selesai
                📍 Tempat : Kawasan Wisata Lahi Lai Bisi Kopan (LLBK), Kupang, Nusa Tenggara Timur
                TXT;

                // Gunakan URL gambar default jika tidak diberikan di request
                // $imageUrl = $request->input('url', 'https://mandalikakorprirun.com/storage/frame.png');
                // $filename = 'qr.jpeg';

                // $data = [
                //     'chatId' => $no['no_hp'],
                //     'file' => [
                //         'mimetype' => 'image/jpeg',
                //         'filename' => $filename,
                //         'url' => $imageUrl,
                //     ],
                //     'reply_to' => null,
                //     'caption' => $request->input('caption', $caption),
                //     'session' => 'Nyala',
                // ];
                // $response = Http::withHeaders([
                //     'x-api-key' => 'df3rWS9MH4lWzj5Al5COhDnX4wsqT72L',
                //     'Content-Type' => 'application/json',
                //     'Accept' => 'application/json',
                // ])->post('https://waha-nco1sqgcadk4.babat.sumopod.my.id/api/sendImage', $data);

                //gunakan api/sendText
                $data = [
                    'chatId' => $no,
                    'text' => $caption,
                    'session' => 'Nyala',
                ];
                $response = Http::withHeaders([
                    'x-api-key' => 'df3rWS9MH4lWzj5Al5COhDnX4wsqT72L',
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])->post('https://waha-nco1sqgcadk4.babat.sumopod.my.id/api/sendText', $data);
                // sleep(2);

                // mengirim file pdf di link ini https://mandalikakorprirun.com/storage/Surat%20Undangan%20Peserta%20Dialog%20Interaktif%20Menko%20Pemberdayaan%20Masyarakat%20di%20Kota%20Kupang.pdf
                // $pdfUrl = 'https://mandalikakorprirun.com/storage/rundown%20lengkap%20acara%20BERDAYA%20BERSAMA%20KUPANG.pdf';
                // $pdfFilename = 'Rundown Lengkap Acara BERDAYA BERSAMA KUPANG.pdf';

                // $data = [
                //     'chatId' => $no['no_hp'],
                //     'file' => [
                //         'mimetype' => 'application/pdf',
                //         'filename' => $pdfFilename,
                //         'url' => $pdfUrl,
                //     ],
                //     'reply_to' => null,
                //     'caption' => $caption,
                //     'session' => 'Nyala',
                // ];
                // $response = Http::withHeaders([
                //     'x-api-key' => 'df3rWS9MH4lWzj5Al5COhDnX4wsqT72L',
                //     'Content-Type' => 'application/json',
                //     'Accept' => 'application/json',
                // ])->post('https://waha-nco1sqgcadk4.babat.sumopod.my.id/api/sendFile', $data);
            }
        }

        if (!$response) {
            return response()->json([
                'status' => 'error',
                'message' => 'No CSV uploaded or no messages sent',
            ], 422);
        }

        if ($response->successful()) {
            return response()->json([
                'status' => 'success',
                'message' => 'File sent successfully',
            ], 200);
        } else {
            //know the error
            $error = $response->json();

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send file',
                'error' => $error,
            ], 500);
        }
    });

    // });

    // Route::post('waha/Blast', function (Request $request) {
    //     $baseUrl = 'https://waha-nco1sqgcadk4.babat.sumopod.my.id';
    //     $apiKey = 'df3rWS9MH4lWzj5Al5COhDnX4wsqT72L';
    //     $session = 'Nyala';

    //     if (!$baseUrl || !$apiKey || !$session) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'WAHA configuration is missing',
    //         ], 500);
    //     }

    //     $hasCsv = $request->hasFile('csv') || $request->hasFile('file');

    //     if ($hasCsv) {
    //         $request->validate([
    //             'csv' => 'nullable|file|mimes:csv,txt',
    //             'file' => 'nullable|file|mimes:csv,txt',
    //             'url' => 'required|string|url',
    //             'caption' => 'nullable|string',
    //         ]);

    //         $caption = (string) $request->input('caption', '');
    //         $url = $request->input('url');

    //         $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
    //         if (!in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
    //             $extension = 'jpg';
    //         }
    //         $filename = (trim($caption) !== '' ? preg_replace('/[^A-Za-z0-9._-]/', '_', $caption) : 'image') . ".{$extension}";

    //         $uploaded = $request->file('csv') ?: $request->file('file');
    //         $path = $uploaded->store('public/waha');
    //         $rows = array_map('str_getcsv', file(storage_path('app/' . $path)));

    //         $numbers = [];
    //         foreach ($rows as $i => $row) {
    //             if (!isset($row[0])) {
    //                 continue;
    //             }
    //             $cell = trim($row[0]);
    //             if ($i === 0 && strcasecmp($cell, 'number') === 0) {
    //                 continue;
    //             }
    //             if ($cell === '') {
    //                 continue;
    //             }
    //             $digits = preg_replace('/[^0-9]/', '', $cell);
    //             if ($digits === '') {
    //                 continue;
    //             }
    //             if (strpos($digits, '0') === 0) {
    //                 $digits = '62' . substr($digits, 1);
    //             }
    //             if (strpos($digits, '62') !== 0) {
    //                 $digits = '62' . ltrim($digits, '0');
    //             }
    //             $numbers[] = $digits;
    //         }

    //         $results = [];
    //         foreach ($numbers as $idx => $chatId) {
    //             $payload = [
    //                 'chatId' => $chatId,
    //                 'file' => [
    //                     'mimetype' => 'image/' . ($extension === 'jpg' ? 'jpeg' : $extension),
    //                     'filename' => $filename,
    //                     'url' => $url,
    //                 ],
    //                 'reply_to' => null,
    //                 'caption' => $caption,
    //                 'session' => $session,
    //             ];

    //             try {
    //                 $response = Http::withHeaders([
    //                     'x-api-key' => $apiKey,
    //                     'Content-Type' => 'application/json',
    //                     'Accept' => 'application/json',
    //                 ])->timeout(30)->connectTimeout(15)
    //                     ->post($baseUrl . '/api/sendImage', $payload);

    //                 $ok = $response->successful();
    //                 $results[] = [
    //                     'chatId' => $chatId,
    //                     'status' => $ok ? 'success' : 'error',
    //                     'upstream_status' => $response->status(),
    //                     'error' => $ok ? null : ($response->json() ?? $response->body()),
    //                 ];
    //             } catch (ConnectionException $e) {
    //                 $results[] = [
    //                     'chatId' => $chatId,
    //                     'status' => 'error',
    //                     'upstream_status' => 0,
    //                     'error' => $e->getMessage(),
    //                 ];
    //             }

    //             if ($idx < count($numbers) - 1) {
    //                 usleep(2000000);
    //             }
    //         }

    //         return response()->json([
    //             'mode' => 'bulk',
    //             'total' => count($numbers),
    //             'success' => collect($results)->where('status', 'success')->count(),
    //             'results' => $results,
    //         ]);
    //     }

    //     // single send fallback
    //     $request->validate([
    //         'chatId' => 'required|string',
    //         'url' => 'required|string|url',
    //         'caption' => 'nullable|string',
    //     ]);

    //     $caption = (string) $request->input('caption', '');
    //     $url = $request->input('url');
    //     $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
    //     if (!in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
    //         $extension = 'jpg';
    //     }
    //     $filename = (trim($caption) !== '' ? preg_replace('/[^A-Za-z0-9._-]/', '_', $caption) : 'image') . ".{$extension}";

    //     $chatId = preg_replace('/[^0-9]/', '', (string) $request->input('chatId'));
    //     if (strpos($chatId, '0') === 0) {
    //         $chatId = '62' . substr($chatId, 1);
    //     }
    //     if (strpos($chatId, '62') !== 0) {
    //         $chatId = '62' . ltrim($chatId, '0');
    //     }

    //     $payload = [
    //         'chatId' => $chatId,
    //         'file' => [
    //             'mimetype' => 'image/' . ($extension === 'jpg' ? 'jpeg' : $extension),
    //             'filename' => $filename,
    //             'url' => $url,
    //         ],
    //         'reply_to' => null,
    //         'caption' => $caption,
    //         'session' => $session,
    //     ];

    //     try {
    //         $response = Http::withHeaders([
    //             'x-api-key' => $apiKey,
    //             'Content-Type' => 'application/json',
    //             'Accept' => 'application/json',
    //         ])->timeout(30)->connectTimeout(15)
    //             ->post($baseUrl . '/api/sendImage', $payload);

    //         if ($response->successful()) {
    //             return response()->json([
    //                 'status' => 'success',
    //                 'message' => 'Image sent successfully',
    //             ], 200);
    //         }

    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Failed to send image',
    //             'upstream_status' => $response->status(),
    //             'error' => $response->json() ?? $response->body(),
    //         ], 502);
    //     } catch (ConnectionException $e) {
    //         \Log::warning('WAHA Blast timeout/connection error', [
    //             'exception' => $e->getMessage(),
    //             'chatId' => $chatId ?? null,
    //             'url' => $url,
    //         ]);
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Upstream WAHA service timeout or connection issue',
    //         ], 504);
    //     }
    // });
});

// ... existing code ...
