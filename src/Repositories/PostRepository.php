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

    public function createPost(array $postAttributes)
    {
        return Post::create($postAttributes);
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