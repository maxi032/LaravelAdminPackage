<?php

namespace Maxi032\LaravelAdminPackage\Repositories;

use Maxi032\LaravelAdminPackage\Models\Post;
use Maxi032\LaravelAdminPackage\Repositories\Interfaces\PostRepositoryInterface;

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

    public function createPostWithTranslations(array $dataArr)
    {
        try {
            \DB::transaction(function () use ($dataArr) {
                // Create the post using the create method
                $post = Post::create([
                    'type_id' => $dataArr['type_id'],
                    'status' => $dataArr['status'],
                ]);

                // Save translations for the post
                $translationsData = $dataArr['contentTranslations'];

                foreach ($translationsData as $locale => $translationData) {
                    $post->translations()->create([
                        'code' => $locale,
                        'content' => $translationData['content'],
                    ]);
                }
            });

            // The transaction was successful
            return response()->json(['type'=>'success','message' => 'Post created successfully!']);
        } catch (\Exception $e) {
            return response()->json(['type'=>'error','message' => $e->getMessage()]);
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