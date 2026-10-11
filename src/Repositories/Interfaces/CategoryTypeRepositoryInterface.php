<?php

namespace Maxi032\LaravelAdminPackage\Repositories\Interfaces;

use Maxi032\LaravelAdminPackage\Models\CategoryType;

interface CategoryTypeRepositoryInterface
{
    public function getCategoryTypesForDropdown(): array;

    public function getCategoryTypeById(int $id): CategoryType;
}
