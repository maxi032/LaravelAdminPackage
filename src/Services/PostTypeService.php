<?php

namespace Maxi032\LaravelAdminPackage\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Maxi032\LaravelAdminPackage\Models\PostType;
use Maxi032\LaravelAdminPackage\Repositories\Interfaces\PostTypeRepositoryInterface;
use Maxi032\LaravelAdminPackage\Repositories\PostTypeRepository;

class PostTypeService
{
    public function __construct(

        /** @var PostTypeRepository */
        private readonly PostTypeRepositoryInterface $postTypeRepository,
    ) {
    }

    public function getPostTypeOverview(bool $trashed = false): LengthAwarePaginator
    {
        return $this->postTypeRepository->getPostTypeOverview($trashed);
    }

    public function getPosts(PostType $postType): Collection
    {
        return $this->postTypeRepository->getPosts($postType);
    }

    public function savePostType(PostType $postType, array $data): void
    {
        $this->postTypeRepository->savePostType($postType, $data);
    }

    public function changePostTypeStatus(PostType $postType, bool $status): void
    {
        $this->postTypeRepository->changePostTypeStatus($postType, $status);
    }

    public function deletePostType(PostType $postType): void
    {
        $this->postTypeRepository->deletePostType($postType);
    }

    public function recoverPostType(PostType $postType): void
    {
        $this->postTypeRepository->recoverPostType($postType);
    }

    public function forceDeletePostType(PostType $postType): void
    {
        $this->postTypeRepository->forceDeletePostType($postType);
    }
}
