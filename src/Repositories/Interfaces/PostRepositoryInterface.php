<?php

namespace Maxi032\LaravelAdminPackage\Repositories\Interfaces;

interface PostRepositoryInterface
{
    public function createPostWithTranslations(array $dataArr);

    public function deletePost($postId);

    public function getPostById(int $postId);

    public function getPostsOfType(string $postType);

    public function updatePost(int $postId, array $postAttributes);

    public function getActivePosts();

    public function getActivePostsOfType(string $type);

}