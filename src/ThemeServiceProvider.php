<?php

namespace Maxi032\LaravelAdminPackage;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class ThemeServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // load views from the package if env var is true, otherwise load from vendor folder first
        $views = env('MAXI032_LAP_LOAD_VIEWS_FROM_VENDOR_FIRST') ?
            [
                __DIR__ . '/../resources/views', // load views from the package
                 resource_path('views/vendor/maxi032/'.LaravelAdminPackageServiceProvider::getAdmPackageName()),
            ]
            :
            [
            resource_path('views/vendor/maxi032/'.LaravelAdminPackageServiceProvider::getAdmPackageName()),
            __DIR__ . '/../resources/views', // load views from the package
        ];

        $this->loadViewsFrom($views, LaravelAdminPackageServiceProvider::getAdmPackageName());
        View::composer('*', function ($view) {
            $view->with(Str::camel(LaravelAdminPackageServiceProvider::getAdmPackageName()), LaravelAdminPackageServiceProvider::getAdmPackageName());
        });
    }
}
