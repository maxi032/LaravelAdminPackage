<?php

namespace Maxi032\LaravelAdminPackage\Repositories\Interfaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Maxi032\LaravelAdminPackage\Models\Category;

interface CategoryRepositoryInterface
{
    public function getCategoryById(int $categoryId);

    public function categoryMatchesPostType(int $categoryId, int $postTypeId): bool;

    public function getCategoryOfType(string $categoryType);

    public function deleteCategory($categoryId);

    public function createCategory(array $categoryAttributes);

    public function updateCategory(int $categoryId, array $categoryAttributes);

    public function getActiveCategories();

    public function getActiveCategoriesOfType(string $type);

    public function getCategoriesForDropdown($lang = null, ?int $postTypeId = null);

    public function getCategoryOverview(string $type, bool $trashed = false): LengthAwarePaginator;

    public function createCategoryWithTranslations(array $data): Category;

    public function updateCategoryWithTranslations(Category $category, array $data): Category;

    public function recoverCategory(int $categoryId): void;

    public function forceDeleteCategory(int $categoryId): void;

    public function changeCategoryStatus(int $categoryId, bool $status): void;
}
