<?php

namespace Maxi032\LaravelAdminPackage\Repositories\Interfaces;

interface CategoryRepositoryInterface
{

    public function createCategory(array $categoryAttributes);

    public function getCategoryById(int $categoryId);

    public function getCategoryOfType(string $categoryType);

    public function deleteCategory($categoryId);

    public function updateCategory(int $categoryId, array $categoryAttributes);

    public function getActiveCategories();

    public function getActiveCategoriesOfType(string $type);
}