<?php

namespace Maxi032\LaravelAdminPackage\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Maxi032\LaravelAdminPackage\Models\PostType;

interface PostTypeRepositoryInterface
{
    public function getPostTypesForDropdown();

    /**
     * @return EloquentCollection<int, PostType>
     */
    public function getPostTypesForSidebar(): EloquentCollection;

    public function getPostTypeById($id);
}