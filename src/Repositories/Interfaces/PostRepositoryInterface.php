<?php

namespace Maxi032\LaravelAdminPackage\Repositories\Interfaces;

use Maxi032\LaravelAdminPackage\Models\Post;

interface PostRepositoryInterface
{
    public function getPostById(int $postId);

    public function getPostsOfType(string $postType);

    public function deletePost($postId);

    public function createPostWithTranslations(array $dataArr);

    public function updatePostWithTranslations(Post $post, array $dataArr);

    public function getActivePosts();

    public function getActivePostsOfType(string $type);

}