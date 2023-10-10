<?php

namespace Maxi032\LaravelAdminPackage\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Maxi032\LaravelAdminPackage\Models\Post;
use Maxi032\LaravelAdminPackage\Repositories\Interfaces\PostRepositoryInterface;
use \Illuminate\Http\JsonResponse;
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
        $languages = config('laravel-admin-package.allowed_languages');
        try {
            \DB::transaction(function () use ($dataArr, $languages) {
                // Create the post using the create method
                $post = Post::create([
                    'type_id'    => $dataArr['type_id'],
                    'category_id'=> $dataArr['category_id'],
                    'status'     => $dataArr['status'] ? 1 : 0,
                    'sort_order' => $dataArr['sort_order'] ?? 0,
                ]);

                // Save translations for the post
                $translationsData = $dataArr['translations'];

                $translationsToInsert = [];
                foreach ($translationsData as $field => $fieldValues) {
                    $i = 0;
                    foreach ($languages as $lang => $language) {
                        $translationsToInsert[$i]['post_id'] = $post->id;
                        $translationsToInsert[$i]['title'] = $translationsData['title'][$language['code']];
                        $translationsToInsert[$i]['slug'] = $translationsData['slug'][$language['code']];
                        $translationsToInsert[$i]['excerpt'] = $translationsData['excerpt'][$language['code']];
                        $translationsToInsert[$i]['content'] = $translationsData['content'][$language['code']];
                        $translationsToInsert[$i]['meta_title'] = $translationsData['meta_title'][$language['code']] ?? $translationsToInsert[$i]['title'];
                        $translationsToInsert[$i]['meta_keywords'] = $translationsData['meta_keywords'][$language['code']];
                        $translationsToInsert[$i]['meta_description'] = $translationsData['meta_description'][$language['code']];
                        $translationsToInsert[$i]['language'] = $language['code'];
                        $translationsToInsert[$i]['created_at'] = Carbon::now();
                        $i++;
                    }
                }

                $post->translations()->insert($translationsToInsert);
            });

            // The transaction was successful
            return response()->json(['type' => 'success', 'message' => 'Post created successfully!']);
        } catch (Throwable $e) {
            return response()->json(['type' => 'error', 'message' => $e->getMessage() . ' in file ' . $e->getFile() . ' at line ' . $e->getLine()]);
        }
    }

    public function updatePostWithTranslations(array $dataArr): JsonResponse
    {
        $languages = config('laravel-admin-package.allowed_languages');
        try {
            \DB::transaction(function () use ($dataArr, $languages) {
                // Find the post
                $post = Post::find($dataArr['id'])->load('translations');
                $post->type_id = $dataArr['type_id'];
                $post->category_id = $dataArr['category_id'];
                $post->status = isset($dataArr['status']) ? 1 : 0;
                $post->sort_order = $dataArr['sort_order'] ?? 0;

                $translationsData = $dataArr['translations'];

                //prepare the translations array to be updated
                $translationsToInsert = [];
                foreach ($translationsData as $field => $fieldValues) {
                    $i = 0;
                    foreach ($languages as $lang => $language) {
                        $translationsToInsert[$i]['post_id'] = $post->id;
                        $translationsToInsert[$i][$field] = $fieldValues[$language['code']];
                        $translationsToInsert[$i]['language'] = $language['code'];
                        $i++;
                    }
                }

                // find the corresponding translation by language and update it
                foreach ($translationsToInsert as $k => $translation) {
                    $post->translations->where('language', $translation['language'])->first()->update($translation);
                }
                $post->push();
            });

            // The transaction was successful
            return response()->json(['type' => 'success', 'message' => 'Post updated successfully!']);
        } catch (Throwable $e) {
            Log::error($e->getMessage() . " at line No : " . __LINE__ . " : File " . __FILE__);
            return response()->json(['type' => 'error', 'message' => $e->getMessage()]);
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