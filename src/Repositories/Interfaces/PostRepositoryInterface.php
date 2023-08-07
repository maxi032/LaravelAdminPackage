<?php

namespace Maxi032\LaravelAdminPackage\Repositories\Interfaces;

interface PostRepositoryInterface
{
    public function getPostById(int $postId);

    public function getPostsOfType(string $postType);

    public function deletePost($postId);

    public function createPost(array $postAttributes);

    public function updatePost(int $postId, array $postAttributes);

    public function getActivePosts();

    public function getActivePostsOfType(string $type);

}