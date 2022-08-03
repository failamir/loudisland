<?php

// Route::group(['prefix' => 'v1', 'as' => 'api.', 'namespace' => 'Api\V1\Admin', 'middleware' => ['auth:sanctum']], function () {
    Route::group(['prefix' => 'v1', 'as' => 'api.', 'namespace' => 'Api\V1\Admin'], function () {
    // Pendaftar
    Route::post('beli', 'PendaftarController@beliApi')->name('beliApi');
    Route::post('scan', 'PendaftarController@scan')->name('scan');
    Route::post('checkin', 'PendaftarController@checkin')->name('checkin');
    Route::post('checkin2', 'PendaftarController@checkin2')->name('checkin2');
    Route::post('checkout', 'PendaftarController@checkout')->name('checkout');
    Route::post('pendaftars/media', 'PendaftarApiController@storeMedia')->name('pendaftars.storeMedia');
    Route::apiResource('pendaftars', 'PendaftarApiController');

    // Event
    Route::apiResource('events', 'EventApiController');
    Route::get('list_checkin', 'PendaftarController@list_checkin');
    Route::get('list_checkout', 'PendaftarController@list_checkout');
});
