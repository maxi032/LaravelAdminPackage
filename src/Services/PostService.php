<?php
namespace Maxi032\LaravelAdminPackage\Services;

use Maxi032\LaravelAdminPackage\Models\Post;

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
     * @return Post
     * @throws \Throwable
     */
    public function createPostWithTranslations(array $dataArr): Post
    {
        return $this->postRepository->createPostWithTranslations($dataArr);
    }


    /**
     * @param Post $post
     * @param array $dataArr
     * @return Post
     * @throws \Throwable
     */
    public function updatePostWithTranslations(Post $post, array $dataArr): Post
    {
        return $this->postRepository->updatePostWithTranslations($post, $dataArr);
    }

    public function deletePost(int $postId): void
    {
        $this->postRepository->deletePost($postId);
    }

    public function recoverPost(int $postId): void
    {
        $this->postRepository->recoverPost($postId);
    }

    public function forceDeletePost(int $postId): void
    {
        $this->postRepository->forceDeletePost($postId);
    }

    /**
     * Get all post types
     * @return mixed
     */
    public function getPostTypesForDropdown(): mixed
    {
        return $this->postTypeRepository->getPostTypesForDropdown();
    }

    /**
     * @param $lang
     * @return mixed
     */
    public function getCategoriesForDropdown($lang=null)
    {
        return $this->categoryRepository->getCategoriesForDropdown($lang);
    }
}
