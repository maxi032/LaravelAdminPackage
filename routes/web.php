<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix'     => '/'.config('laravel-admin-package.admin_url'),
    'middleware' => 'auth',
    'as'         => config('laravel-admin-package.admin_url').':',
], function () {
    Route::get('/', ['as' => 'dashboard', 'uses' => 'Maxi032\LaravelAdminPackage\Http\Controllers\AdminController@index']);
});
