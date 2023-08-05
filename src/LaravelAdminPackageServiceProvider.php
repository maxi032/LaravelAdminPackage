<?php

namespace Maxi032\LaravelAdminPackage;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class LaravelAdminPackageServiceProvider extends ServiceProvider
{
    static string $admPackage = 'laravel-admin-package';
    public function register(): void
    {
        // Bind the data to the container using the singleton method
        $this->app->singleton(self::getAdmPackageName(), function ($app) {
            return self::getAdmPackageName();
        });

        $this->app->register(ThemeServiceProvider::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishResources();
        }

        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'maxi032-'.self::getAdmPackageName().'-translations');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        Route::middleware('api')->prefix('api')->group(function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        });

        Route::middleware('web')->group(function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        });
    }

    /**
     * Make package name available globally
     *
     * @return string
     */
    public static function getAdmPackageName(): string
    {
        return self::$admPackage;
    }

    /**
     * Publish all resources of the package
     *
     * @return void
     */
    public function publishResources(): void
    {
        $this->publishes([
            __DIR__ . '/../resources/lang' => resource_path('lang/vendor/maxi032-'.self::getAdmPackageName().'-translations'),
        ], self::getAdmPackageName().'-translations');

        // Publish migrations
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'maxi032-'.self::getAdmPackageName().'-migrations');

        // Publish seeders
        $this->publishes([
            __DIR__.'/../database/seeders' => database_path('seeders'),
        ], 'maxi032-'.self::getAdmPackageName().'-seeders');


        // Publish the configuration file
        $this->publishes([
            __DIR__.'/../config/'.self::getAdmPackageName().'.php' => config_path(self::getAdmPackageName().'.php'),
        ], 'maxi032-'.self::getAdmPackageName().'-config');

        // Publish the views files
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/maxi032/'.self::getAdmPackageName()),
        ],'maxi032-'.self::getAdmPackageName().'-views');
    }
}
