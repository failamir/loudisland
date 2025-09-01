<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
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
        Route::get('roles', [UserApiController::class, 'roles']);

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

        // QR Codes API for FE consumption (protected)
        Route::get('qrcodes', [QrCodeApiController::class, 'index'])->name('qrcodes.index');
        Route::get('qrcodes/download-all', [QrCodeApiController::class, 'downloadAll'])->name('qrcodes.downloadAll');

        // Simple transactions list for FE
        Route::get('transactions/simple', [TransactionsListController::class, 'index'])->name('transactions.simple');

        Route::post('logout', [AuthController::class, 'logout'])->name('auth.logout');
    });

    // Pendaftar
    Route::post('buy', [PendaftarController::class, 'beliApi'])->name('buy');
    Route::post('myorder', [PendaftarController::class, 'myorder'])->name('myorder');
    Route::post('daftar', [PendaftarController::class, 'daftar'])->name('daftar');
    Route::get('profile', [PendaftarController::class, 'profile'])->name('profile');
    Route::post('updateprofile', [PendaftarController::class, 'updateprofile'])->name('updateprofile');
    Route::get('transaksi', [PendaftarController::class, 'transaksi'])->name('transaksi');
    Route::get('transactions', [PendaftarController::class, 'transaksi'])->name('transactions');
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

    // Admin utilities
    Route::get('total-income', function () {
        $sum = (int) \App\Models\Transaksi::where('status', 'success')->sum('amount');
        return response()->json(['total_income' => $sum]);
    });

    // TODO: email to ifailamir@kardusinfo.com and kardusinfo@failamir.com
    // TODO: add withdrawal history in database
    Route::post('withdrawals', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'amount' => 'required|integer|min:1',
            'destination' => 'required|string',
        ]);
        return response()->json([
            'status' => 'queued',
            'id' => uniqid('wd_'),
            'amount' => (int) $request->input('amount'),
            'destination' => $request->input('destination'),
        ], 202);
    });
});
