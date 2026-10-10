<?php

namespace Maxi032\LaravelAdminPackage\Repositories\Interfaces;

use Maxi032\LaravelAdminPackage\Models\Post;

interface PostRepositoryInterface
{
    public function getPostById(int $postId);

    public function getPostsOfType(string $postType);

    public function deletePost($postId);

    public function recoverPost(int $postId): void;

    public function forceDeletePost(int $postId): void;

    /**
     * @throws \Throwable
     */
    public function createPostWithTranslations(array $dataArr): Post;

    /**
     * @throws \Throwable
     */
    public function updatePostWithTranslations(Post $post, array $dataArr): Post;

    public function getActivePosts();

    public function getActivePostsOfType(string $type);

}
