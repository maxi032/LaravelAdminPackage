<?php

namespace Maxi032\LaravelAdminPackage\Repositories;

use Maxi032\LaravelAdminPackage\Models\PostType;
use Illuminate\Support\Facades\Cache;
use Maxi032\LaravelAdminPackage\Repositories\Interfaces\PostTypeRepositoryInterface;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class PostTypeRepository implements PostTypeRepositoryInterface
{
    /**
     * @return array
     */
    public function getPostTypesForDropdown(): array
    {
        return Cache::remember('postTypesForDropdown', 600, function () {
            return PostType::select('id', 'type')->pluck('type', 'id')->toArray();
        });
    }
}