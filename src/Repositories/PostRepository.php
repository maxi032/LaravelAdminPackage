<?php

namespace Maxi032\LaravelAdminPackage\Repositories;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Maxi032\LaravelAdminPackage\Models\Post;
use Maxi032\LaravelAdminPackage\Models\PostType;
use Maxi032\LaravelAdminPackage\Repositories\Interfaces\PostRepositoryInterface;
use \Illuminate\Http\JsonResponse;
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
        Post::destroy($postId);
    }

    public function createPostWithTranslations(array $dataArr): JsonResponse
    {
        try {
            DB::transaction(function () use ($dataArr) {
                $this->lockPostType((int) $dataArr['type_id']);
                $post = Post::create([
                    'type_id' => $dataArr['type_id'],
                    'category_id' => $dataArr['category_id'],
                    'status' => (int) $dataArr['status'],
                    'sort_order' => $dataArr['sort_order'] ?? $this->nextSortOrder((int) $dataArr['type_id']),
                ]);
                $this->saveTranslations($post, $dataArr['translations']);
            });

            return response()->json(['type' => 'success', 'message' => 'Post created successfully!']);
        } catch (Throwable $e) {
            Log::error('Post creation failed.', ['exception' => $e]);
            return response()->json(['type' => 'error', 'message' => 'The post could not be saved. Please try again.'], 500);
        }
    }

    public function updatePostWithTranslations(Post $post, array $dataArr): JsonResponse
    {
        try {
            DB::transaction(function () use ($post, $dataArr) {
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
            });

            return response()->json(['type' => 'success', 'message' => 'Post updated successfully!']);
        } catch (Throwable $e) {
            Log::error('Post update failed.', ['exception' => $e]);
            return response()->json(['type' => 'error', 'message' => 'The post could not be saved. Please try again.'], 500);
        }
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
            throw new \OverflowException('No sort positions remain for this post type.');
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
        return Post::Status(true)->ByCategory($type);
    }

    public function getPostsOfType($type)
    {
        return Post::ByCategory($type);
    }
}