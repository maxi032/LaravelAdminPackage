<?php

namespace Maxi032\LaravelAdminPackage\Repositories;

use Maxi032\LaravelAdminPackage\Models\Category;
use Maxi032\LaravelAdminPackage\Repositories\Interfaces\CategoryRepositoryInterface;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function getCategoryById($categoryId)
    {
        return Category::findOrFail($categoryId);
    }

    public function createCategory(array $categoryAttributes)
    {
        return Category::create($categoryAttributes);
    }

    public function updateCategory($categoryId, array $categoryAttributes)
    {
        return Category::whereId($categoryId)->update($categoryAttributes);
    }

    public function getActiveCategories()
    {
        return Category::where('status', true);
    }

    public function getActiveCategoriesOfType($type)
    {
        return Category::Status(true)->ByType($type);
    }

    public function getCategoryOfType($categoryType)
    {
        return Category::ByType($categoryType);
    }

    public function deleteCategory($categoryId): void
    {
        Category::destroy($categoryId);
    }
}