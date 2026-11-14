<?php

namespace Maxi032\LaravelAdminPackage\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Maxi032\LaravelAdminPackage\Models\PostType;
use Maxi032\LaravelAdminPackage\Repositories\Interfaces\PostTypeRepositoryInterface;

class PostTypeService
{
    public function __construct(private readonly PostTypeRepositoryInterface $postTypeRepository)
    {
    }

    public function getPostTypeOverview(bool $trashed = false): LengthAwarePaginator
    {
        return $this->postTypeRepository->getPostTypeOverview($trashed);
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
