<?php
namespace Maxi032\LaravelAdminPackage\Services;

use Maxi032\LaravelAdminPackage\Repositories\Interfaces\PostRepositoryInterface;
use Maxi032\LaravelAdminPackage\Repositories\Interfaces\PostTypeRepositoryInterface;

class PostService
{
    public function __construct(private readonly PostRepositoryInterface $postRepository, private readonly PostTypeRepositoryInterface $postTypeRepository)
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

    /**
     * Get all post types
     * @return mixed
     */
    public function getPostTypesForDropdown(): mixed
    {
        return $this->postTypeRepository->getPostTypesForDropdown();
    }
}