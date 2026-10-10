<?php

use Illuminate\Support\Facades\Route;
use Maxi032\LaravelAdminPackage\Http\Controllers\AdminController;
use Maxi032\LaravelAdminPackage\Http\Controllers\CategoryController;
use Maxi032\LaravelAdminPackage\Http\Controllers\PostController;
use Maxi032\LaravelAdminPackage\LaravelAdminPackageServiceProvider;

Route::group([
    'prefix' => '/'.config('laravel-admin-package.admin_url'),
    'middleware' => 'auth',
    'as' => LaravelAdminPackageServiceProvider::adminRoutePrefix(),
], function () {
    Route::get('/', [AdminController::class, 'index'])
        ->name('dashboard');
    Route::get('cms/posts/create/{type?}', [PostController::class, 'create'])->name('posts.create');
    Route::get('cms/posts/trash/{type}', [PostController::class, 'trash'])->name('posts.trash');
    Route::post('cms/posts/{post}/recover', [PostController::class, 'recover'])->withTrashed()->name('posts.recover');
    Route::delete('cms/posts/{post}/force-delete', [PostController::class, 'forceDelete'])->withTrashed()->name('posts.force_delete');
    Route::resource('cms/posts', PostController::class)->except(['show', 'create']);
    Route::post('cms/posts/change_status', [PostController::class, 'ajaxChangeStatus'])->name('posts.ajax_change_status')->middleware(['restrict_to_ajax']);
    Route::get('cms/categories/{type}', [CategoryController::class, 'list'])->name('categories.type.list');
    Route::get('cms/posts/{type}', [PostController::class, 'list'])->name('posts.type.list');
});
