<?php

Route::view('/', 'welcome');
Route::get('generate', 'PendaftarController@generate')->name('generate');
Route::post('beli', 'PendaftarController@beli')->name('beli');
// Route::post('notification', 'PendaftarController@notificationHandler')->name('notificationHandler');

Route::view('finish','finish');
Route::view('unfinish', 'unfinish');
Route::view('error', 'error');

Route::post('bayar', 'PendaftarController@bayar')->name('bayar');
Auth::routes();

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Admin', 'middleware' => ['auth', 'admin']], function () {
    Route::get('/', 'HomeController@index')->name('home');
    // Permissions
    Route::delete('permissions/destroy', 'PermissionsController@massDestroy')->name('permissions.massDestroy');
    Route::resource('permissions', 'PermissionsController');

    // Roles
    Route::delete('roles/destroy', 'RolesController@massDestroy')->name('roles.massDestroy');
    Route::resource('roles', 'RolesController');

    // Users
    Route::delete('users/destroy', 'UsersController@massDestroy')->name('users.massDestroy');
    Route::resource('users', 'UsersController');

    // Pendaftar
    Route::delete('pendaftars/destroy', 'PendaftarController@massDestroy')->name('pendaftars.massDestroy');
    Route::post('pendaftars/media', 'PendaftarController@storeMedia')->name('pendaftars.storeMedia');
    Route::post('pendaftars/ckmedia', 'PendaftarController@storeCKEditorImages')->name('pendaftars.storeCKEditorImages');
    Route::post('pendaftars/parse-csv-import', 'PendaftarController@parseCsvImport')->name('pendaftars.parseCsvImport');
    Route::post('pendaftars/process-csv-import', 'PendaftarController@processCsvImport')->name('pendaftars.processCsvImport');
    Route::resource('pendaftars', 'PendaftarController');

    // Audit Logs
    Route::resource('audit-logs', 'AuditLogsController', ['except' => ['create', 'store', 'edit', 'update', 'destroy']]);

    // Event
    Route::delete('events/destroy', 'EventController@massDestroy')->name('events.massDestroy');
    Route::post('events/parse-csv-import', 'EventController@parseCsvImport')->name('events.parseCsvImport');
    Route::post('events/process-csv-import', 'EventController@processCsvImport')->name('events.processCsvImport');
    Route::resource('events', 'EventController');

    Route::get('global-search', 'GlobalSearchController@search')->name('globalSearch');
});
Route::group(['prefix' => 'profile', 'as' => 'profile.', 'namespace' => 'Auth', 'middleware' => ['auth']], function () {
    // Change password
    if (file_exists(app_path('Http/Controllers/Auth/ChangePasswordController.php'))) {
        Route::get('password', 'ChangePasswordController@edit')->name('password.edit');
        Route::post('password', 'ChangePasswordController@update')->name('password.update');
        Route::post('profile', 'ChangePasswordController@updateProfile')->name('password.updateProfile');
        Route::post('profile/destroy', 'ChangePasswordController@destroy')->name('password.destroyProfile');
    }
});
Route::group(['as' => 'frontend.', 'namespace' => 'Frontend', 'middleware' => ['auth']], function () {
    Route::get('/home', 'HomeController@index')->name('home');

    // Permissions
    Route::delete('permissions/destroy', 'PermissionsController@massDestroy')->name('permissions.massDestroy');
    Route::resource('permissions', 'PermissionsController');

    // Roles
    Route::delete('roles/destroy', 'RolesController@massDestroy')->name('roles.massDestroy');
    Route::resource('roles', 'RolesController');

    // Users
    Route::delete('users/destroy', 'UsersController@massDestroy')->name('users.massDestroy');
    Route::resource('users', 'UsersController');

    // Pendaftar
    Route::delete('pendaftars/destroy', 'PendaftarController@massDestroy')->name('pendaftars.massDestroy');
    Route::post('pendaftars/media', 'PendaftarController@storeMedia')->name('pendaftars.storeMedia');
    Route::post('pendaftars/ckmedia', 'PendaftarController@storeCKEditorImages')->name('pendaftars.storeCKEditorImages');
    Route::resource('pendaftars', 'PendaftarController');

    // Event
    Route::delete('events/destroy', 'EventController@massDestroy')->name('events.massDestroy');
    Route::resource('events', 'EventController');

    Route::get('frontend/profile', 'ProfileController@index')->name('profile.index');
    Route::post('frontend/profile', 'ProfileController@update')->name('profile.update');
    Route::post('frontend/profile/destroy', 'ProfileController@destroy')->name('profile.destroy');
    Route::post('frontend/profile/password', 'ProfileController@password')->name('profile.password');
});
