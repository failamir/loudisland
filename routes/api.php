<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Api\V1\Admin\NomorPunggungApiController;
use App\Http\Controllers\Api\V1\Admin\PendaftarController;
use App\Http\Controllers\Api\V1\Admin\QrCodeApiController;
use App\Http\Controllers\Api\V1\Admin\TransaksiApiController;
use App\Http\Controllers\Api\V1\Admin\UserApiController;
use App\Http\Controllers\Api\V1\Admin\TiketApiController;
use App\Http\Controllers\Api\V1\Admin\EventApiController;
use App\Http\Controllers\Api\V1\Admin\BannerApiController;
use App\Http\Controllers\Api\V1\Admin\SponsorApiController;
use App\Http\Controllers\Api\V1\Admin\SettingApiController;
use App\Http\Controllers\Api\V1\Admin\AuthController;
use App\Http\Controllers\Api\V1\Admin\TransactionsListController;
use App\Http\Controllers\Api\V1\Admin\QrController;
use App\Http\Controllers\Api\V1\Admin\WilayahApiController;
use App\Http\Controllers\Api\V1\Admin\PendaftarApiController;
use App\Http\Controllers\Api\V1\Admin\TiketApiController;
use App\Http\Controllers\Api\V1\Admin\EventApiController;
use App\Http\Controllers\Api\V1\Admin\BannerApiController;
use App\Http\Controllers\Api\V1\Admin\SponsorApiController;
use App\Http\Controllers\Api\V1\Admin\SettingApiController;
use App\Http\Controllers\Api\V1\Admin\AuthController;
use App\Http\Controllers\Api\V1\Admin\TransactionsListController;
use App\Http\Controllers\Api\V1\Admin\QrController;
use App\Http\Controllers\Api\V1\Admin\WilayahApiController;
use App\Http\Controllers\Api\V1\Admin\QrCodeApiController;
use App\Http\Controllers\Api\V1\Admin\PendaftarApiController;
use App\Http\Controllers\Api\V1\Admin\TiketApiController;
use App\Http\Controllers\Api\V1\Admin\EventApiController;
use App\Http\Controllers\Api\V1\Admin\BannerApiController;
use App\Http\Controllers\Api\V1\Admin\SponsorApiController;
use App\Http\Controllers\Api\V1\Admin\SettingApiController;
use App\Http\Controllers\Api\V1\Admin\AuthController;
use App\Http\Controllers\Api\V1\Admin\TransactionsListController;
use App\Http\Controllers\Api\V1\Admin\QrController;
use App\Http\Controllers\Api\V1\Admin\WilayahApiController;
use App\Http\Controllers\Api\V1\Admin\QrCodeApiController;
use App\Http\Controllers\Api\V1\Admin\PendaftarApiController;
use App\Http\Controllers\Api\V1\Admin\TiketApiController;
use App\Http\Controllers\Api\V1\Admin\EventApiController;
use App\Http\Controllers\Api\V1\Admin\BannerApiController;
use App\Http\Controllers\Api\V1\Admin\SponsorApiController;
use App\Http\Controllers\Api\V1\Admin\SettingApiController;
use App\Http\Controllers\Api\V1\Admin\AuthController;
use App\Http\Controllers\Api\V1\Admin\TransactionsListController;
use App\Http\Controllers\Api\V1\Admin\QrController;
use App\Http\Controllers\Api\V1\Admin\WilayahApiController;
use App\Http\Controllers\Api\V1\Admin\QrCodeApiController;

// use Illuminate\Http\Client\Http;
// Route::group(['prefix' => 'v1', 'as' => 'api.', 'namespace' => 'Api\\V1\\Admin', 'middleware' => ['auth:sanctum']], function () {
Route::group(['prefix' => 'v1', 'as' => 'api.', 'namespace' => 'Api\\V1\\Admin'], function () {
    // Auth
    Route::post('register', 'AuthController@register')->name('auth.register');
    Route::post('login', 'AuthController@login')->name('auth.login');
    Route::post('refresh', 'AuthController@refresh')->name('auth.refresh');
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', 'AuthController@me')->name('auth.me');
        Route::get('users', 'UserApiController@index');

        // Nomor Punggung QR API
        Route::get('nomor-punggung', [NomorPunggungApiController::class, 'index']);
        Route::post('nomor-punggung/pair', [NomorPunggungApiController::class, 'pair']);
        Route::post('nomor-punggung/generate', [NomorPunggungApiController::class, 'generate']);

        // Scanner endpoints for race day (protected)
        Route::post('scan/start', 'PendaftarController@scanStart')->name('scan.start');
        Route::post('scan/finish', 'PendaftarController@scanFinish')->name('scan.finish');

        // Pairings listing (protected)
        Route::get('pairings', 'PendaftarController@listPairing')->name('pairings');

        // QR Codes API for FE consumption (protected)
        Route::get('qrcodes', 'QrCodeApiController@index')->name('qrcodes.index');
        Route::get('qrcodes/download-all', 'QrCodeApiController@downloadAll')->name('qrcodes.downloadAll');

        // Simple transactions list for FE
        Route::get('transactions/simple', 'TransactionsListController@index')->name('transactions.simple');

        Route::post('logout', 'AuthController@logout')->name('auth.logout');
    });
    // Pendaftar
    // Route::post('beli', 'PendaftarController@beliApi')->name('beliApi');
    Route::post('buy', 'PendaftarController@beliApi')->name('buy');
    Route::post('daftar', 'PendaftarController@daftar')->name('daftar');
    Route::get('profile', 'PendaftarController@profile')->name('profile');
    Route::post('updateprofile', 'PendaftarController@updateprofile')->name('updateprofile');
    Route::get('transaksi', 'PendaftarController@transaksi')->name('transaksi');
    Route::get('tiket', 'PendaftarController@tiket')->name('tiket');
    Route::post('notification', 'PendaftarController@notificationHandler')->name('notification');
    // New simplified registration that creates ticket and returns Midtrans URL
    Route::post('register-ticket', 'PendaftarController@registerTicket')->name('register-ticket');
    
    Route::post('scan', 'PendaftarController@scan')->name('scan');
    Route::post('checkin', 'PendaftarController@checkin')->name('checkin');
    Route::post('checkin2', 'PendaftarController@checkin2')->name('checkin2');
    Route::post('checkout', 'PendaftarController@checkout')->name('checkout');
    Route::post('pendaftars/media', 'PendaftarApiController@storeMedia')->name('pendaftars.storeMedia');
    Route::apiResource('pendaftars', 'PendaftarApiController');

    // Public QR endpoints removed; use protected versions above

    // Payment status for FE
    Route::get('payment/{invoice}', 'PendaftarController@paymentStatus')->name('payment.status');

    Route::get('list_checkin', 'PendaftarController@list_checkin');
    Route::get('list_checkout', 'PendaftarController@list_checkout');
    // Tiket
    Route::post('tikets/media', 'TiketApiController@storeMedia')->name('tikets.storeMedia');
    Route::apiResource('tikets', 'TiketApiController');

    // Event
    Route::post('events/media', 'EventApiController@storeMedia')->name('events.storeMedia');
    Route::apiResource('events', 'EventApiController');

    // Banner
    Route::post('banners/media', 'BannerApiController@storeMedia')->name('banners.storeMedia');
    Route::apiResource('banners', 'BannerApiController');

    // Transaksi
    Route::post('transaksis/media', 'TransaksiApiController@storeMedia')->name('transaksis.storeMedia');
    Route::apiResource('transaksis', 'TransaksiApiController');

    // Sponsor
    Route::post('sponsors/media', 'SponsorApiController@storeMedia')->name('sponsors.storeMedia');
    Route::apiResource('sponsors', 'SponsorApiController');

    // Setting
    Route::apiResource('settings', 'SettingApiController');

    // Event
    Route::apiResource('events', 'EventApiController');

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

