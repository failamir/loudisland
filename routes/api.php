<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
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
        $query = \App\Models\Participant::all();
        return response()->json([
            'data' => $query,
            'total' => count($query)
        ]);
    });

    // Admin utilities
    Route::get('total-income', function () {
        $testingEmail = env('EMAIL_TESTING');

        $query = \App\Models\Transaksi::query()
            ->where('status', 'success')
            ->whereHas('participants');

        if (!empty($testingEmail)) {
            $query->where(function ($q) use ($testingEmail) {
                $q->where('amount', '>=', 175000)
                    ->orWhereNull('email')
                    ->orWhere('email', '!=', $testingEmail);
            });
        }

        $count = (clone $query)->count();
        $grossSum = (int) (clone $query)->sum('amount');
        $profit = (int) ($count * 5000 + floor($grossSum * 0.01));
        $netIncome = max(0, $grossSum - $profit);

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

        // $url = trim($request->input('url'));
        // if (!filter_var($url, FILTER_VALIDATE_URL)) {
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'Invalid image URL',
        //     ], 422);
        // }

        $caption = (string) $request->input('caption', 'QR ID Peserta');

        // Ensure filename is safe and has a proper extension
        $filename = trim($caption) !== ''
            ? preg_replace('/[^A-Za-z0-9._-]/', '_', $caption)
            : 'image';

        $url = $request->input('url', 'https://gooogle.com');

        // Add proper extension based on URL or default to jpg
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
            \Log::warning('WAHA sendLinkPreview timeout/connection error', [
                'exception' => $e->getMessage(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Upstream WAHA service timeout or connection issue',
            ], 504);
        }
    });

    // Route::post('waha/sendImage', function (\Illuminate\Http\Request $request) {
    //     $request->validate([
    //         'chatId' => 'required',
    //         'image' => 'required|image',
    //         'caption' => 'nullable|string',
    //     ]);
    //     $data = [
    //         'chatId' => $request->input('chatId'),
    //         'file' => [
    //             'mimetype' => $request->file('image')->getClientMimeType(),
    //             'filename' => $request->file()
    //         ],
    //         'reply_to' => null,
    //         'caption' => $request->input('caption'),
    //         'session' => 'KORPRIRUN',
    //     ];
    //     $response = Http::withHeaders([
    //         'x-api-key' => 'YV5CtoFFOFVAx3kOMfLrryCXiXK4lQpg',
    //     ])->attach('file', file_get_contents($request->file('image')->getRealPath()), $request->file('image')->getClientOriginalName())
    //         ->post('https://waha-1tssjsoucdmi.cinta.sumopod.my.id/api/sendImage', $data);

    //     if ($response->successful()) {
    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Image sent successfully',
    //         ], 200);
    //     } else {
    //         //know the error
    //         $error = $response->json();

    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Failed to send image',
    //             'error' => $error,
    //         ], 500);
    //     }
    // });
});
