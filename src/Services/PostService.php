<?php
namespace Maxi032\LaravelAdminPackage\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maxi032\LaravelAdminPackage\Models\Post;
use Maxi032\LaravelAdminPackage\Rules\CategoryMatchesPostType;

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

    public function getPostOverview(string $type, bool $trashed = false): LengthAwarePaginator
    {
        return $this->postRepository->getPostOverview($type, $trashed);
    }


    /**
     * @param array $dataArr
     * @return Post
     * @throws \Throwable
     */
    public function createPostWithTranslations(array $dataArr): Post
    {
        $this->validateCategoryType($dataArr);

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
        $this->validateCategoryType($dataArr);

        return $this->postRepository->updatePostWithTranslations($post, $dataArr);
    }

    private function validateCategoryType(array $data): void
    {
        Validator::make($data, [
            'type_id' => ['bail', 'required', 'integer', Rule::exists('post_types', 'id')->whereNull('deleted_at')],
            'category_id' => ['bail', 'required', 'integer', new CategoryMatchesPostType(
                $data['type_id'] ?? null, $this->categoryRepository
            )],
        ])->validate();
    }

    public function deletePost(int $postId): void
    {
        $this->postRepository->deletePost($postId);
    }

    public function changePostStatus(int $postId, bool $status): void
    {
        $this->postRepository->changePostStatus($postId, $status);
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
     * @param int|null $postTypeId
     * @return mixed
     */
    public function getCategoriesForDropdown($lang = null, ?int $postTypeId = null)
    {
        return $this->categoryRepository->getCategoriesForDropdown($lang, $postTypeId);
    }
}
