<?php
namespace Maxi032\LaravelAdminPackage\Services;

use Maxi032\LaravelAdminPackage\Repositories\Interfaces\CategoryRepositoryInterface;
use Maxi032\LaravelAdminPackage\Repositories\Interfaces\PostRepositoryInterface;
use Maxi032\LaravelAdminPackage\Repositories\Interfaces\PostTypeRepositoryInterface;

class PostService
{
    public function __construct(
        private readonly PostRepositoryInterface $postRepository,
        private readonly PostTypeRepositoryInterface $postTypeRepository,
        private readonly CategoryRepositoryInterface $categoryRepository
    )
    {

    }


    /**
     * @param array $dataArr
     * @return mixed
     */
    public function createPostWithTranslations(array $dataArr): mixed
    {
        return $this->postRepository->createPostWithTranslations($dataArr);
    }

    public function updatePostWithTranslations(array $dataArr): mixed
    {
        return $this->postRepository->updatePostWithTranslations($dataArr);
    }

    /**
     * Get all post types
     * @return mixed
     */
    public function getPostTypesForDropdown(): mixed
    {
        return $this->postTypeRepository->getPostTypesForDropdown();
    }

    public function getCategoriesForDropdown($lang=null)
    {
        return $this->categoryRepository->getCategoriesForDropdown($lang);
    }
}