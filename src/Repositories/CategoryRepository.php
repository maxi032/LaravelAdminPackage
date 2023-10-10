<?php

namespace Maxi032\LaravelAdminPackage\Repositories;

use Illuminate\Support\Facades\Cache;
use JetBrains\PhpStorm\NoReturn;
use Maxi032\LaravelAdminPackage\Models\Category;
use Maxi032\LaravelAdminPackage\Repositories\Interfaces\CategoryRepositoryInterface;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function getCategoryById($categoryId)
    {
        return Category::findOrFail($categoryId);
    }

    public function deleteCategory($categoryId): void
    {
        Category::destroy($categoryId);
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

    public function getCategoriesForDropdown($lang=null)
    {
        return Cache::remember('CategoriesForDropdown', 600, function () use ($lang) {
            return Category::with(['translations' => function ($query) use ($lang) {
                if ($lang) {
                    $query->where('language', $lang);
                } else {
                    $query->where('language', app()->getLocale());
                }
            }])->get();
        });
    }
}