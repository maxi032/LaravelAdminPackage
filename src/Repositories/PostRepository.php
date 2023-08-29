<?php

namespace Maxi032\LaravelAdminPackage\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Arr;
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

    public function deletePost($postId)
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
                    'status'     => $dataArr['status'] ?? 0,
                    'sort_order' => $dataArr['sort_order'] ?? 0,
                ]);

                // Save translations for the post
                $translationsData = $dataArr['translations'];

                $translationsToInsert = [];
                foreach ($translationsData as $field => $fieldValues) {
                    $i = 0;
                    foreach($languages as $lang => $language){
                        $translationsToInsert[$i]['post_id'] = $post->id;
                        $translationsToInsert[$i]['title'] = $translationsData['title'][$language['code']];
                        $translationsToInsert[$i]['slug'] = $translationsData['slug'][$language['code']];
                        $translationsToInsert[$i]['excerpt'] = $translationsData['excerpt'][$language['code']];
                        $translationsToInsert[$i]['content'] = $translationsData['content'][$language['code']];
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
            return response()->json(['type' => 'error', 'message' => $e->getMessage().' at line '.$e->getLine()]);
        }
    }

    public function updatePost($postId, array $postAttributes)
    {
        return Post::whereId($postId)->update($postAttributes);
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