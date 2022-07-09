<?php

Route::group(['prefix' => 'v1', 'as' => 'api.', 'namespace' => 'Api\V1\Admin', 'middleware' => ['auth:sanctum']], function () {
    // Pendaftar
    Route::post('pendaftars/media', 'PendaftarApiController@storeMedia')->name('pendaftars.storeMedia');
    Route::apiResource('pendaftars', 'PendaftarApiController');

    // Event
    Route::apiResource('events', 'EventApiController');
});
