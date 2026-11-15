<?php

namespace Maxi032\LaravelAdminPackage\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Maxi032\LaravelAdminPackage\Models\PostOption;
use Maxi032\LaravelAdminPackage\Repositories\Interfaces\PostOptionRepositoryInterface;
use Maxi032\LaravelAdminPackage\Repositories\PostOptionRepository;

class PostOptionService
{
    public function __construct(

        /** @var PostOptionRepository */
        private readonly PostOptionRepositoryInterface $postOptionRepository,
    ) {
    }

    public function getPostOptionOverview(bool $trashed = false): LengthAwarePaginator
    {
        return $this->postOptionRepository->getPostOptionOverview($trashed);
    }

    public function getPostsWithSavedValues(PostOption $option): Collection
    {
        return $this->postOptionRepository->getPostsWithSavedValues($option);
    }

    /** @return Collection<int, PostOption> */
    public function getEditablePostOptions(?int $postTypeId): Collection
    {
        return $this->postOptionRepository->getEditablePostOptions($postTypeId);
    }

    public function newPostOption(): PostOption
    {
        return new PostOption(['enabled' => true, 'type' => 'text', 'sort_order' => 0]);
    }

    public function hasSavedValues(PostOption $option): bool
    {
        return $option->exists && $this->postOptionRepository->hasSavedValues($option);
    }

    public function createPostOption(array $data): PostOption
    {
        return $this->postOptionRepository->createPostOption($data);
    }

    public function updatePostOption(int $optionId, array $data): PostOption
    {
        return $this->postOptionRepository->updatePostOption($optionId, $data);
    }

    public function changePostOptionStatus(int $optionId, bool $enabled): void
    {
        $this->postOptionRepository->changePostOptionStatus($optionId, $enabled);
    }

    public function deletePostOption(int $optionId): void
    {
        $this->postOptionRepository->deletePostOption($optionId);
    }

    public function recoverPostOption(int $optionId): void
    {
        $this->postOptionRepository->recoverPostOption($optionId);
    }

    public function forceDeletePostOption(int $optionId): void
    {
        $this->postOptionRepository->forceDeletePostOption($optionId);
    }
}
