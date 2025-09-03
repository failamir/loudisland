<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::view('/', 'giner/new');
Route::view('welcome', 'wizard');
// API documentation (Swagger UI)
Route::view('/docs', 'swagger')->name('swagger.docs');
Route::get('generate', 'PendaftarController@generate')->name('generate');
Route::post('beli', 'PendaftarController@beli')->name('beli');
// Route::post('notification', 'PendaftarController@notificationHandler')->name('notificationHandler');

Route::view('finish', 'finish');
Route::view('unfinish', 'unfinish');
Route::view('error', 'error');

Route::post('bayar', 'PendaftarController@bayar')->name('bayar');
// Payment finish callback page (Midtrans)
Route::get('payment/success/{invoice}', 'PendaftarController@paymentSuccess')->name('payment.success');
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

    // Route::get('global-search', 'GlobalSearchController@search')->name('globalSearch');

    // Tiket
    Route::delete('tikets/destroy', 'TiketController@massDestroy')->name('tikets.massDestroy');
    Route::post('tikets/media', 'TiketController@storeMedia')->name('tikets.storeMedia');
    Route::post('tikets/ckmedia', 'TiketController@storeCKEditorImages')->name('tikets.storeCKEditorImages');
    Route::resource('tikets', 'TiketController');

    // Event
    Route::delete('events/destroy', 'EventController@massDestroy')->name('events.massDestroy');
    Route::post('events/media', 'EventController@storeMedia')->name('events.storeMedia');
    Route::post('events/ckmedia', 'EventController@storeCKEditorImages')->name('events.storeCKEditorImages');
    Route::resource('events', 'EventController');

    // Banner
    Route::delete('banners/destroy', 'BannerController@massDestroy')->name('banners.massDestroy');
    Route::post('banners/media', 'BannerController@storeMedia')->name('banners.storeMedia');
    Route::post('banners/ckmedia', 'BannerController@storeCKEditorImages')->name('banners.storeCKEditorImages');
    Route::resource('banners', 'BannerController');

    // Audit Logs
    Route::resource('audit-logs', 'AuditLogsController', ['except' => ['create', 'store', 'edit', 'update', 'destroy']]);

    // User Alerts
    Route::delete('user-alerts/destroy', 'UserAlertsController@massDestroy')->name('user-alerts.massDestroy');
    Route::get('user-alerts/read', 'UserAlertsController@read');
    Route::resource('user-alerts', 'UserAlertsController', ['except' => ['edit', 'update']]);

    // Faq Category
    Route::delete('faq-categories/destroy', 'FaqCategoryController@massDestroy')->name('faq-categories.massDestroy');
    Route::resource('faq-categories', 'FaqCategoryController');

    // Faq Question
    Route::delete('faq-questions/destroy', 'FaqQuestionController@massDestroy')->name('faq-questions.massDestroy');
    Route::resource('faq-questions', 'FaqQuestionController');

    // Transaksi
    Route::delete('transactions/destroy', 'TransaksiController@massDestroy')->name('transactions.massDestroy');
    Route::post('transactions/media', 'TransaksiController@storeMedia')->name('transactions.storeMedia');
    Route::post('transactions/ckmedia', 'TransaksiController@storeCKEditorImages')->name('transactions.storeCKEditorImages');
    Route::post('transactions/parse-csv-import', 'TransaksiController@parseCsvImport')->name('transactions.parseCsvImport');
    Route::post('transactions/process-csv-import', 'TransaksiController@processCsvImport')->name('transactions.processCsvImport');
    Route::resource('transactions', 'TransaksiController');

    // Sponsor
    Route::delete('sponsors/destroy', 'SponsorController@massDestroy')->name('sponsors.massDestroy');
    Route::post('sponsors/media', 'SponsorController@storeMedia')->name('sponsors.storeMedia');
    Route::post('sponsors/ckmedia', 'SponsorController@storeCKEditorImages')->name('sponsors.storeCKEditorImages');
    Route::resource('sponsors', 'SponsorController');

    // Setting
    Route::delete('settings/destroy', 'SettingController@massDestroy')->name('settings.massDestroy');
    Route::resource('settings', 'SettingController');

    // QR Codes listing and bulk download
    Route::get('qrcodes', 'QrController@index')->name('qrcodes.index');
    Route::get('qrcodes/download-all', 'QrController@downloadAll')->name('qrcodes.downloadAll');
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
