<?php

namespace Maxi032\LaravelAdminPackage\Repositories\Interfaces;

interface PostTypeRepositoryInterface
{
    public function getPostTypesForDropdown();

    public function getPostTypeById($id);
}