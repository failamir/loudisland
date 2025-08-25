<?php

use Illuminate\Support\Facades\Route;
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
    Route::post('scan', 'PendaftarController@scan')->name('scan');
    Route::post('checkin', 'PendaftarController@checkin')->name('checkin');
    Route::post('checkin2', 'PendaftarController@checkin2')->name('checkin2');
    Route::post('checkout', 'PendaftarController@checkout')->name('checkout');
    Route::post('pendaftars/media', 'PendaftarApiController@storeMedia')->name('pendaftars.storeMedia');
    Route::apiResource('pendaftars', 'PendaftarApiController');

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


    // saya ingin jadi proxy untuk fe mobile untuk hit api https://wilayah.id/ 
    // Wilayah.id - API Data Wilayah Administrasi Pemerintahan di Indonesia
    // Wilayah.id merupakan API (Antarmuka Pemrograman Aplikasi) statis yang berisikan data wilayah yang ada di Indonesia. Data yang disediakan meliputi data provinsi, kabupaten/kota, kecamatan, dan kelurahan/desa.

    // Pembaruan data terakhir 04 Juli 2025
    // Kode dan Data Wilayah Administrasi Pemerintahan dan Pulau Indonesia sesuai dengan Kepmendagri No 300.2.2-2138 Tahun 2025 [ref: github.com/cahyadsn/wilayah ↗]

    // # Daftar API Endpoint:
    // 1. Provinsi
    // Mendapatkan data semua provinsi yang ada di Indonesia.

    // Endpoint
    // GET
    // https://wilayah.id/api/provinces.json
    // Copy
    // Response
    // Contoh response ketika mengambil data semua provinsi di Indonesia https://wilayah.id/api/provinces.json ↗

    // {
    //     "data": [
    //         {
    //           "code": "36",
    //           "name": "Banten"
    //         },
    //         {
    //           "code": "31",
    //           "name": "DKI Jakarta"
    //         },
    //         // ...
    //     ],
    //     "meta": {
    //         "administrative_area_level": 1,
    //         "updated_at": "2025-07-04"
    //     }
    // }

    // 2. Kabupaten / Kota
    // Mendapatkan data kabupaten atau kota dari provinsi.

    // Endpoint
    // GET
    // https://wilayah.id/api/regencies/[PROVINCE_CODE].json
    // Copy
    // Response
    // Contoh response ketika mengambil data kabupaten atau kota dari provinsi DKI Jakarta https://wilayah.id/api/regencies/31.json ↗

    // {
    //     "data": [
    //         {
    //             "code": "31.71",
    //             "name": "Kota Administrasi Jakarta Pusat"
    //         },
    //         {
    //             "code": "31.74",
    //             "name": "Kota Administrasi Jakarta Selatan"
    //         },
    //         // ...
    //     ],
    //     "meta": {
    //         "administrative_area_level": 2,
    //         "updated_at": "2025-07-04"
    //     }
    // }

    // 3. Kecamatan
    // Mendapatkan data kecamatan dari kabupaten atau kota.

    // Endpoint
    // GET
    // https://wilayah.id/api/districts/[REGENCY_CODE].json
    // Copy
    // Response
    // Contoh response ketika mengambil data kecamatan dari Kota Administrasi Jakarta Selatan https://wilayah.id/api/districts/31.74.json ↗

    // {
    //     "data": [
    //         {
    //             "code": "31.74.06",
    //             "name": "Cilandak"
    //         },
    //         {
    //             "code": "31.74.09",
    //             "name": "Jagakarsa"
    //         },
    //         // ...
    //     ],
    //     "meta": {
    //         "administrative_area_level": 3,
    //         "updated_at": "2025-07-04"
    //     }
    // }

    // 4. Kelurahan / Desa
    // Mendapatkan data kelurahan atau desa dari kecamatan.

    // Endpoint
    // GET
    // https://wilayah.id/api/villages/[DISTRICT_CODE].json
    // Copy
    // Response
    // Contoh response ketika mengambil data kelurahan atau desa dari kecamatan Jagakarsa https://wilayah.id/api/villages/31.74.09.json ↗

    // {
    //     "data": [
    //         {
    //             "code": "31.74.09.1002",
    //             "name": "Srengseng Sawah"
    //         },
    //         {
    //             "code": "31.74.09.1005",
    //             "name": "Tanjung Barat"
    //         }
    //         // ...
    //     ],
    //     "meta": {
    //         "administrative_area_level": 4,
    //         "updated_at": "2025-07-04"
    //     }
    // }

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
});
