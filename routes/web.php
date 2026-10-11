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
    Route::get('cms/categories/create/{type?}', [CategoryController::class, 'create'])->name('categories.create');
    Route::get('cms/categories/trash/{type}', [CategoryController::class, 'trash'])->name('categories.trash');
    Route::post('cms/categories/{category}/recover', [CategoryController::class, 'recover'])->withTrashed()->name('categories.recover');
    Route::delete('cms/categories/{category}/force-delete', [CategoryController::class, 'forceDelete'])->withTrashed()->name('categories.force_delete');
    Route::post('cms/categories/{category}/change-status', [CategoryController::class, 'changeStatus'])->name('categories.change_status');
    Route::resource('cms/categories', CategoryController::class)->except(['index', 'show', 'create']);
    Route::get('cms/categories/{type}', [CategoryController::class, 'list'])->name('categories.type.list');
    Route::get('cms/posts/{type}', [PostController::class, 'list'])->name('posts.type.list');
});
