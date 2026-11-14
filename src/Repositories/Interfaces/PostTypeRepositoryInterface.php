<?php

namespace Maxi032\LaravelAdminPackage\Repositories\Interfaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Maxi032\LaravelAdminPackage\Models\PostType;

interface PostTypeRepositoryInterface
{
    public function existsByType(string $type): bool;

    public function getPostTypesForDropdown();

    /**
     * @return EloquentCollection<int, PostType>
     */
    public function getPostTypesForSidebar(): EloquentCollection;

    public function getPostTypeById($id);

    public function getPostTypeOverview(bool $trashed = false): LengthAwarePaginator;

    public function getPosts(PostType $postType): EloquentCollection;

    public function savePostType(PostType $postType, array $data): void;

    public function changePostTypeStatus(PostType $postType, bool $status): void;

    public function deletePostType(PostType $postType): void;

    public function recoverPostType(PostType $postType): void;

    public function forceDeletePostType(PostType $postType): void;
}
