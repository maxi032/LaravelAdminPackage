<?php

namespace Maxi032\LaravelAdminPackage;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class ThemeServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // load views from the vendor folder if found, otherwise, load from the package
        $views = [
            resource_path('views/vendor/maxi032/' . LaravelAdminPackageServiceProvider::getAdmPackageName()),
            __DIR__ . '/../resources/views' // load views from the package
        ];

        $this->loadViewsFrom($views, LaravelAdminPackageServiceProvider::getAdmPackageName());
        View::composer('*', function ($view) {
            $view->with(Str::camel(LaravelAdminPackageServiceProvider::getAdmPackageName()), LaravelAdminPackageServiceProvider::getAdmPackageName());
        });
    }
}
