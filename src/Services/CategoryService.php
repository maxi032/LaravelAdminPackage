<?php

namespace Maxi032\LaravelAdminPackage\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Maxi032\LaravelAdminPackage\Models\Category;
use Maxi032\LaravelAdminPackage\Models\CategoryType;
use Maxi032\LaravelAdminPackage\Repositories\Interfaces\CategoryRepositoryInterface;
use Maxi032\LaravelAdminPackage\Repositories\Interfaces\CategoryTypeRepositoryInterface;

class CategoryService
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository,
        private readonly CategoryTypeRepositoryInterface $categoryTypeRepository,
    ) {
    }

    public function getCategoryOverview(string $type, bool $trashed = false): LengthAwarePaginator
    {
        return $this->categoryRepository->getCategoryOverview($type, $trashed);
    }

    public function getCategoryTypesForDropdown(): array
    {
        return $this->categoryTypeRepository->getCategoryTypesForDropdown();
    }

    public function getCategoryTypeById(int $id): CategoryType
    {
        return $this->categoryTypeRepository->getCategoryTypeById($id);
    }

    public function createCategoryWithTranslations(array $data): Category
    {
        return $this->categoryRepository->createCategoryWithTranslations($data);
    }

    public function updateCategoryWithTranslations(Category $category, array $data): Category
    {
        return $this->categoryRepository->updateCategoryWithTranslations($category, $data);
    }

    public function deleteCategory(int $id): void
    {
        $this->categoryRepository->deleteCategory($id);
    }

    public function recoverCategory(int $id): void
    {
        $this->categoryRepository->recoverCategory($id);
    }

    public function forceDeleteCategory(int $id): void
    {
        $this->categoryRepository->forceDeleteCategory($id);
    }

    public function changeCategoryStatus(int $id, bool $status): void
    {
        $this->categoryRepository->changeCategoryStatus($id, $status);
    }
}
