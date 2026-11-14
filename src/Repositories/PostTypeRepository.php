<?php

namespace Maxi032\LaravelAdminPackage\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Maxi032\LaravelAdminPackage\Models\CategoryType;
use Maxi032\LaravelAdminPackage\Models\PostType;
use Maxi032\LaravelAdminPackage\Repositories\Interfaces\PostTypeRepositoryInterface;
use RuntimeException;

class PostTypeRepository implements PostTypeRepositoryInterface
{
    public function existsByType(string $type): bool
    {
        return PostType::where('type', $type)->exists();
    }

    /**
     * @return array
     */
    public function getPostTypesForDropdown(): array
    {
        return PostType::whereNotNull('type')->orderBy('type')->pluck('type', 'id')->all();
    }

    /**
     * @return EloquentCollection<int, PostType>
     */
    public function getPostTypesForSidebar(): EloquentCollection
    {
        return PostType::query()
            ->whereNotNull('type')
            ->orderBy('type')
            ->get(['id', 'type', 'status', 'icon']);
    }

    public function getPostTypeById($id)
    {
        return PostType::find($id);
    }

    public function getPostTypeOverview(bool $trashed = false): LengthAwarePaginator
    {
        return $trashed
            ? PostType::onlyTrashed()->latest('deleted_at')->orderByDesc('id')->paginate(10)
            : PostType::withCount(['posts' => fn ($posts) => $posts->withTrashed()])
                ->orderBy('type')->orderBy('id')->paginate(10);
    }

    public function savePostType(PostType $postType, array $data): void
    {
        try {
            DB::transaction(function () use ($postType, $data) {
                $postType = $postType->exists ? $this->lockPostType($postType) : new PostType;
                $this->ensureCanChangeStatus($postType, (bool) ($data['status'] ?? $postType->status));

                // Posts resolve their category group by the post type key.
                $categoryType = $this->lockCategoryType($postType->type)
                    ?? $this->lockCategoryType($data['type'])
                    ?? new CategoryType;

                if ($categoryType->trashed()) {
                    throw ValidationException::withMessages(['type' => __('This key belongs to a deleted category type.')]);
                }

                if (!$postType->fill($data)->save() || !$categoryType->fill(['type' => $postType->type])->save()) {
                    throw new RuntimeException(__('The post type could not be saved.'));
                }
            }, 3);
        } catch (UniqueConstraintViolationException $exception) {
            throw ValidationException::withMessages(['type' => __('This key is already used by a post type or category type.')]);
        }
    }

    public function changePostTypeStatus(PostType $postType, bool $status): void
    {
        DB::transaction(function () use ($postType, $status) {
            $postType = $this->lockPostType($postType);
            $this->ensureCanChangeStatus($postType, $status);
            if (!$postType->update(['status' => $status])) {
                throw new RuntimeException(__('The post type status could not be updated.'));
            }
        }, 3);
    }

    public function deletePostType(PostType $postType): void
    {
        DB::transaction(function () use ($postType) {
            $postType = $this->lockPostType($postType);
            $categoryType = $this->lockCategoryType($postType->type);

            $this->ensureUnused($postType, $categoryType);

            if (!$postType->delete() || ($categoryType && !$categoryType->trashed() && !$categoryType->delete())) {
                throw new RuntimeException(__('The post type could not be deleted.'));
            }
        }, 3);
    }

    public function recoverPostType(PostType $postType): void
    {
        DB::transaction(function () use ($postType) {
            $postType = $this->lockPostType($postType, trashed: true);
            $categoryType = $this->lockCategoryType($postType->type);

            if (!$postType->restore() || ($categoryType?->trashed() && !$categoryType->restore())) {
                throw new RuntimeException(__('The post type could not be recovered.'));
            }
        }, 3);
    }

    public function forceDeletePostType(PostType $postType): void
    {
        DB::transaction(function () use ($postType) {
            $postType = $this->lockPostType($postType, trashed: true);
            $categoryType = $this->lockCategoryType($postType->type);
            $this->ensureUnused($postType, $categoryType);

            if (!$postType->forceDelete() || ($categoryType?->trashed() && !$categoryType->forceDelete())) {
                throw new RuntimeException(__('The post type could not be permanently deleted.'));
            }
        }, 3);
    }

    private function ensureCanChangeStatus(PostType $postType, bool $status): void
    {
        if ($postType->status && !$status && $postType->hasPosts()) {
            throw ValidationException::withMessages(['status' => __('A post type with posts cannot be disabled.')]);
        }
    }

    private function ensureUnused(PostType $postType, ?CategoryType $categoryType): void
    {
        if ($postType->hasPosts()
            || $postType->postOptions()->withTrashed()->exists()
            || $categoryType?->categories()->withTrashed()->exists()) {
            throw ValidationException::withMessages(['type' => __('This post type still has posts, categories or post options assigned to it. Check their Trash too.')]);
        }
    }

    private function lockPostType(PostType $postType, bool $trashed = false): PostType
    {
        $query = $trashed ? PostType::onlyTrashed() : PostType::query();

        return $query->whereKey($postType->getKey())->lockForUpdate()->firstOrFail();
    }

    private function lockCategoryType(?string $type): ?CategoryType
    {
        return $type === null ? null : CategoryType::withTrashed()->where('type', $type)->lockForUpdate()->first();
    }
}
