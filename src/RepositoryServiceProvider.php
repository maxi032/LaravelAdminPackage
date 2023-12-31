<?php

namespace Maxi032\LaravelAdminPackage;

use Illuminate\Support\ServiceProvider;
use Maxi032\LaravelAdminPackage\Repositories\Interfaces\PostRepositoryInterface;
use Maxi032\LaravelAdminPackage\Repositories\Interfaces\PostTypeRepositoryInterface;
use Maxi032\LaravelAdminPackage\Repositories\PostRepository;
use Maxi032\LaravelAdminPackage\Repositories\PostTypeRepository;
use Maxi032\LaravelAdminPackage\Repositories\Interfaces\CategoryRepositoryInterface;
use Maxi032\LaravelAdminPackage\Repositories\CategoryRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        $this->app->bind(PostRepositoryInterface::class, PostRepository::class);
        $this->app->bind(PostTypeRepositoryInterface::class, PostTypeRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
