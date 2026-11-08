<?php

namespace Maxi032\LaravelAdminPackage\Repositories\Interfaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Maxi032\LaravelAdminPackage\Models\Post;
use Maxi032\LaravelAdminPackage\Models\PostOption;

interface PostOptionRepositoryInterface
{
    public function getPostOptionOverview(bool $trashed = false): LengthAwarePaginator;

    /** @return Collection<int, PostOption> */
    public function getEditablePostOptions(): Collection;

    /**
     * Load editable options by key under a shared lock in the caller’s transaction.
     *
     * @param list<int|string> $keys
     * @return Collection<string, PostOption>
     */
    public function lockEditablePostOptionsByKey(array $keys): Collection;

    public function hasSavedValues(PostOption $option): bool;

    public function savePostOptionValue(Post $post, PostOption $definition, string $locale, mixed $value): void;

    public function deletePostOptionValue(Post $post, int $optionId, string $locale): void;

    public function createPostOption(array $data): PostOption;

    public function updatePostOption(int $optionId, array $data): PostOption;

    public function changePostOptionStatus(int $optionId, bool $enabled): void;

    public function deletePostOption(int $optionId): void;

    public function recoverPostOption(int $optionId): void;

    public function forceDeletePostOption(int $optionId): void;
}
