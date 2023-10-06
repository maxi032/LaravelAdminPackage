<?php

use Illuminate\Support\Facades\Route;
use Maxi032\LaravelAdminPackage\Http\Controllers\PostController;

Route::group([
    'prefix'     => '/'.config('laravel-admin-package.admin_url'),
    'middleware' => 'auth',
    'as'         => config('laravel-admin-package.admin_url').':',
], function () {
   // Route::get('/', ['as' => 'dashboard', 'uses' => 'Maxi032\LaravelAdminPackage\Http\Controllers\AdminController@index']);
    Route::resource('cms/posts', PostController::class)->except(['show']);
    Route::post('cms/posts/change_status',[PostController::class,'ajaxChangeStatus'])->name('posts.ajax_change_status')->middleware(['restrict_to_ajax']);
    Route::get('cms/posts/{type}', [PostController::class,'list'])->name('posts.type.list');
});
