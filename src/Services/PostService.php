<?php
namespace Maxi032\LaravelAdminPackage\Services;

use Maxi032\LaravelAdminPackage\Repositories\Interfaces\PostRepositoryInterface;

class PostService
{
    private PostRepositoryInterface $postRepository;

    public function __construct(PostRepositoryInterface $postRepository)
    {
        $this->postRepository = $postRepository;
    }

    /**
     * @param array $dataArr
     * @return mixed
     */
    public function createPostWithTranslations(array $dataArr): mixed
    {
        return $this->postRepository->createPostWithTranslations($dataArr);
    }
}