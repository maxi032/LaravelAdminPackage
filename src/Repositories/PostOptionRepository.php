<?php

namespace Maxi032\LaravelAdminPackage\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Maxi032\LaravelAdminPackage\Models\Post;
use Maxi032\LaravelAdminPackage\Models\PostOption;
use Maxi032\LaravelAdminPackage\Models\PostType;
use Maxi032\LaravelAdminPackage\Repositories\Interfaces\PostOptionRepositoryInterface;
use RuntimeException;

class PostOptionRepository implements PostOptionRepositoryInterface
{
    /** Paginate current options by display order or trashed options by most recent deletion. */
    public function getPostOptionOverview(bool $trashed = false): LengthAwarePaginator
    {
        $query = $trashed
            ? PostOption::onlyTrashed()->latest('deleted_at')->orderByDesc('id')
            : PostOption::inDisplayOrder();

        return $query->paginate(10);
    }

    /** List enabled options visible in the post editor, in display order. */
    public function getEditablePostOptions(?int $postTypeId): Collection
    {
        return $postTypeId === null ? new Collection() : PostOption::editableOnPosts()
            ->whereHas('postTypes', fn (Builder $types) => $types->whereKey($postTypeId))
            ->inDisplayOrder()->get();
    }

    /** Lock submitted editable definitions for synchronization and return them keyed by option key. */
    public function lockEditablePostOptionsByKey(int $postTypeId, array $keys): Collection
    {
        $definitions = PostOption::editableOnPosts()->whereIn('key', $keys)
            ->orderBy('id')->sharedLock()->get();

        // Read the current pivot rows after locking definitions against association changes.
        $availableIds = DB::table('post_type_options')->where('post_type_id', $postTypeId)
            ->whereIn('post_option_id', $definitions->modelKeys())->sharedLock()->pluck('post_option_id');

        return $definitions->whereIn('id', $availableIds)->keyBy('key');
    }

    /** Check whether any post has a saved value for the option. */
    public function hasSavedValues(PostOption $option): bool
    {
        return $option->values()->exists();
    }

    /** Insert or update the saved value for one post, option, and locale. */
    public function savePostOptionValue(Post $post, PostOption $definition, string $locale, mixed $value): void
    {
        $row = $post->optionValues()->firstOrNew(['post_option_id' => $definition->id, 'locale' => $locale]);
        $row->setAttribute($definition->valueColumn(), $value);
        if ($row->save() === false) {
            throw new RuntimeException(__('The post options could not be saved.'));
        }
    }

    /** Delete the saved value for one post, option, and locale. */
    public function deletePostOptionValue(Post $post, int $optionId, string $locale): void
    {
        $post->optionValues()->where(['post_option_id' => $optionId, 'locale' => $locale])->delete();
    }

    /** Create and save a post option definition. */
    public function createPostOption(array $data): PostOption
    {
        if (empty($data['post_type_ids'])) {
            throw ValidationException::withMessages(['post_type_ids' => __('Select at least one post type.')]);
        }

        return DB::transaction(function () use ($data) {
            $this->lockPostTypes($data['post_type_ids']);
            $option = new PostOption(Arr::except($data, ['post_type_ids']));
            $this->save($option);
            $option->postTypes()->sync($data['post_type_ids']);

            return $option;
        });
    }

    /** Update the definition while protecting its type and translation setting if values exist. */
    public function updatePostOption(int $optionId, array $data): PostOption
    {
        return DB::transaction(function () use ($optionId, $data) {
            $this->lockPostTypes($data['post_type_ids'] ?? []);
            $option = $this->findForUpdate($optionId)->fill(Arr::except($data, ['post_type_ids']));

            // Post value writes hold a shared lock on the same definition.
            if ($option->isDirty(['type', 'is_translatable']) && $this->hasSavedValues($option)) {
                throw ValidationException::withMessages([
                    'type' => __('This option already has values. Its type and translation setting are locked.'),
                ]);
            }

            $this->save($option);
            if (array_key_exists('post_type_ids', $data)) {
                $option->postTypes()->sync($data['post_type_ids']);
            }

            return $option;
        });
    }

    private function lockPostTypes(array $ids): void
    {
        if ($ids === []) {
            return;
        }

        // Post saves also lock the type before locking option definitions.
        $types = PostType::whereKey($ids)->orderBy('id')->sharedLock()->get(['id']);
        if ($types->count() !== count($ids)) {
            throw ValidationException::withMessages(['post_type_ids' => __('Select valid post types.')]);
        }
    }

    /** Enable or disable a post option. */
    public function changePostOptionStatus(int $optionId, bool $enabled): void
    {
        DB::transaction(function () use ($optionId, $enabled) {
            $option = $this->findForUpdate($optionId);
            $option->enabled = $enabled;
            $this->save($option);
        });
    }

    /** Move the option to Trash and retain its saved values. */
    public function deletePostOption(int $optionId): void
    {
        DB::transaction(function () use ($optionId) {
            if (!$this->findForUpdate($optionId)->delete()) {
                throw new RuntimeException(__('The post option could not be deleted.'));
            }
        });
    }

    /** Restore the option and make its saved values available again. */
    public function recoverPostOption(int $optionId): void
    {
        DB::transaction(function () use ($optionId) {
            if (!$this->findForUpdate($optionId, trashed: true)->restore()) {
                throw new RuntimeException(__('The post option could not be recovered.'));
            }
        });
    }

    /** Permanently delete a trashed option and its saved values in one transaction. */
    public function forceDeletePostOption(int $optionId): void
    {
        DB::transaction(function () use ($optionId) {
            $option = $this->findForUpdate($optionId, trashed: true);
            $option->values()->delete();

            if (!$option->forceDelete()) {
                throw new RuntimeException(__('The post option could not be permanently deleted.'));
            }
        });
    }

    /** Find and lock a current or trashed option for a write transaction. */
    private function findForUpdate(int $optionId, bool $trashed = false): PostOption
    {
        $query = $trashed ? PostOption::onlyTrashed() : PostOption::query();

        return $query->whereKey($optionId)->lockForUpdate()->firstOrFail();
    }

    /** Save the definition after clearing the Boolean default for other field types. */
    private function save(PostOption $option): void
    {
        if ($option->type !== 'boolean') {
            $option->boolean_default = false;
        }

        if ($option->save() === false) {
            throw new RuntimeException(__('The post option could not be saved.'));
        }
    }
}
