<?php

namespace Maxi032\LaravelAdminPackage\Repositories;

use Maxi032\LaravelAdminPackage\Models\CategoryType;
use Maxi032\LaravelAdminPackage\Repositories\Interfaces\CategoryTypeRepositoryInterface;

class CategoryTypeRepository implements CategoryTypeRepositoryInterface
{
    public function getCategoryTypesForDropdown(): array
    {
        return CategoryType::query()
            ->orderBy('type')->pluck('type', 'id')->all();
    }

    public function getCategoryTypeById(int $id): CategoryType
    {
        return CategoryType::findOrFail($id);
    }
}
