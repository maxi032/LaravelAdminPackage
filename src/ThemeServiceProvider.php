<?php

namespace Maxi032\LaravelAdminPackage;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Maxi032\LaravelAdminPackage\Composers\BreadcrumbComposer;
use Maxi032\LaravelAdminPackage\Composers\SidebarComposer;

class ThemeServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $namespace = LaravelAdminPackageServiceProvider::getAdmPackageName();
        $packageViews = __DIR__.'/../resources/views';
        $publishedViews = resource_path('views/vendor/maxi032/'.$namespace);

        $this->loadViewsFrom([$publishedViews, $packageViews], $namespace);
        $this->callAfterResolving('view', function ($view) use ($namespace, $packageViews, $publishedViews) {
            // Include Laravel's conventional application overrides, but enforce package views precedence.
            $hints = $view->getFinder()->getHints()[$namespace] ?? [];
            $applicationViews = array_values(array_diff($hints, [$packageViews, $publishedViews]));
            $paths = config('laravel-admin-package.package_views_first', false)
                ? [$packageViews, $publishedViews, ...$applicationViews]
                : [$publishedViews, ...$applicationViews, $packageViews];

            $view->replaceNamespace($namespace, array_values(array_unique($paths)));
        });

        View::composer($namespace.'::*', function ($view) {
            $view->with('adminRoutePrefix', LaravelAdminPackageServiceProvider::adminRoutePrefix());
        });
        View::composer('*', function ($view) {
            $view->with(Str::camel(LaravelAdminPackageServiceProvider::getAdmPackageName()), LaravelAdminPackageServiceProvider::getAdmPackageName());
        });
        View::composer('*', BreadcrumbComposer::class);
        View::composer(LaravelAdminPackageServiceProvider::getAdmPackageName().'::cms.partials.sidebar', SidebarComposer::class);
    }
}
