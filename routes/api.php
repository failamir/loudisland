<?php

// Route::group(['prefix' => 'v1', 'as' => 'api.', 'namespace' => 'Api\V1\Admin', 'middleware' => ['auth:sanctum']], function () {
    Route::group(['prefix' => 'v1', 'as' => 'api.', 'namespace' => 'Api\V1\Admin'], function () {
    // Pendaftar
    Route::post('beli', 'PendaftarController@beliApi')->name('beliApi');
    Route::post('notification', 'PendaftarController@notification')->name('notification');
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
    // Route::get('list_checkin', 'PendaftarController@list_checkin');
    // Route::get('list_checkout', 'PendaftarController@list_checkout');
});
