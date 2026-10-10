<?php

namespace Maxi032\LaravelAdminPackage\Repositories;

use Illuminate\Support\Arr;
use Maxi032\LaravelAdminPackage\Models\Post;
use Maxi032\LaravelAdminPackage\Models\PostType;
use Maxi032\LaravelAdminPackage\Repositories\Interfaces\PostRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Throwable;

class PostRepository implements PostRepositoryInterface
{
    public function getPostById($postId)
    {
        return Post::findOrFail($postId);
    }

    public function deletePost($postId): void
    {
        DB::transaction(function () use ($postId) {
            $post = Post::whereKey($postId)->lockForUpdate()->firstOrFail();

            // Keep the post and its translations recoverable and hide both from normal queries.
            foreach ($post->translations()->get() as $translation) {
                if ($translation->delete() === false) {
                    throw new \RuntimeException(__('Post translation deletion was cancelled.'));
                }
            }

            if ($post->delete() === false) {
                throw new \RuntimeException(__('Post deletion was cancelled.'));
            }
        });
    }

    public function recoverPost(int $postId): void
    {
        DB::transaction(function () use ($postId) {
            $post = Post::onlyTrashed()->whereKey($postId)->lockForUpdate()->firstOrFail();
            foreach ($post->translations()->onlyTrashed()->get() as $translation) {
                if ($translation->restore() === false) {
                    throw new \RuntimeException(__('Post translation recovery was cancelled.'));
                }
            }
            if ($post->restore() === false) {
                throw new \RuntimeException(__('Post recovery was cancelled.'));
            }
        });
    }

    public function forceDeletePost(int $postId): void
    {
        DB::transaction(function () use ($postId) {
            $post = Post::onlyTrashed()->whereKey($postId)->lockForUpdate()->firstOrFail();
            foreach ($post->translations()->withTrashed()->get() as $translation) {
                if ($translation->forceDelete() === false) {
                    throw new \RuntimeException(__('Post translation destruction was cancelled.'));
                }
            }
            // The database also cascades deletion to child posts and their translations.
            if ($post->forceDelete() === false) {
                throw new \RuntimeException(__('Post destruction was cancelled.'));
            }
        });
    }

    /**
     * @param array $dataArr
     * @return Post
     * @throws Throwable
     */
    public function createPostWithTranslations(array $dataArr): Post
    {
        return DB::transaction(function () use ($dataArr) {
            $this->lockPostType((int) $dataArr['type_id']);

            $post = Post::create([
                'type_id'     => $dataArr['type_id'],
                'category_id' => $dataArr['category_id'],
                'status'      => (int) $dataArr['status'],
                'sort_order'  => $dataArr['sort_order']
                    ?? $this->nextSortOrder((int) $dataArr['type_id']),
            ]);

            $this->saveTranslations($post, $dataArr['translations']);

            return $post;
        });
    }

    /**
     * @param Post $post
     * @param array $dataArr
     * @return Post
     * @throws Throwable
     */
    public function updatePostWithTranslations(Post $post, array $dataArr): Post
    {
        return DB::transaction(function () use ($post, $dataArr) {
            $typeId = (int) $dataArr['type_id'];
            $this->lockPostType($typeId);
            $post = Post::whereKey($post->getKey())->lockForUpdate()->firstOrFail();
            $sortOrder = $dataArr['sort_order'] ?? (
                (int) $post->type_id === $typeId
                    ? $post->sort_order
                    : $this->nextSortOrder($typeId)
            );
            $post->fill([
                'type_id' => $dataArr['type_id'],
                'category_id' => $dataArr['category_id'],
                'status' => (int) $dataArr['status'],
                'sort_order' => $sortOrder,
            ])->save();
            $this->saveTranslations($post, $dataArr['translations']);

            return $post;
        });
    }

    private function lockPostType(int $typeId): void
    {
        // Serialize position assignment for this type, including an empty list.
        PostType::whereKey($typeId)->lockForUpdate()->firstOrFail();
    }

    private function nextSortOrder(int $typeId): int
    {
        $maximum = (int) Post::where('type_id', $typeId)->max('sort_order');
        if ($maximum >= 4294967295) {
            throw new \OverflowException(__('No sort positions remain for this post type.'));
        }

        return $maximum + 1;
    }

    private function saveTranslations(Post $post, array $translations): void
    {
        $translations = Arr::only($translations, [
            'title', 'slug', 'content', 'excerpt',
            'meta_title', 'meta_keywords', 'meta_description',
        ]);

        foreach (config('laravel-admin-package.allowed_languages', []) as $language) {
            $code = $language['code'];
            $attributes = [];
            foreach ($translations as $field => $values) {
                // Preserve omitted fields; an explicit null clears an optional value.
                if (array_key_exists($code, $values)) {
                    $attributes[$field] = $values[$code];
                }
            }

            $post->translations()->updateOrCreate(['language' => $code], $attributes);
        }
    }

    public function getActivePosts()
    {
        return Post::where('status', true);
    }

    public function getActivePostsOfType($type)
    {
        return Post::status(true)->byType($type);
    }

    public function getPostsOfType($type)
    {
        return Post::byType($type);
    }
}
