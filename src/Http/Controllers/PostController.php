<?php

namespace Maxi032\LaravelAdminPackage\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Maxi032\LaravelAdminPackage\Enums\PostStatusEnum;
use Maxi032\LaravelAdminPackage\Models\Post;
use Maxi032\LaravelAdminPackage\Models\PostType;
use Maxi032\LaravelAdminPackage\Repositories\Interfaces\PostTypeRepositoryInterface;
use Maxi032\LaravelAdminPackage\Requests\PostRequest;
use Maxi032\LaravelAdminPackage\Services\PostService;

class PostController extends AdminController
{
    public function __construct(private readonly PostService $postService)
    {
    }

    /**
     * Show a list of the resource of a certain type
     *
     * @param PostType $type
     *
     * @return Renderable
     */
    public function list(PostType $type): Renderable
    {
        // Route model binding will throw 404 automatically it $type is not found
        $type->load(['posts.translations']);
        $posts = $type->posts;



        return view('laravel-admin-package::cms.posts.index', [
            "posts"          => $posts,
            "postType"       => $type,
            "inactiveStatus" => PostStatusEnum::DRAFT,
            "activeStatus"   => PostStatusEnum::PENDING
        ]);
    }

    /**
     * Show the Posts crud form.
     *
     * @return Renderable
     */
    public function create(): Renderable
    {
        $postTypes = $this->postService->getPostTypesForDropdown();
        $categories = $this->postService->getCategoriesForDropdown();
        return view('laravel-admin-package::cms/posts.update_or_create', [
            'postTypes'  => $postTypes,
            'post'       => null,
            'categories' => $categories
        ]);
    }

    public function edit(Post $post): View
    {
        // Route model binding will throw 404 automatically if $post is not found
        $postTypes = $this->postService->getPostTypesForDropdown();
        $categories = $this->postService->getCategoriesForDropdown();

        return view('laravel-admin-package::cms/posts.update_or_create', [
            'postTypes'  => $postTypes,
            'post'       => $post,
            'categories' => $categories
        ]);
    }

    public function show(): Renderable
    {
        $postTypes = $this->postService->getPostTypesForDropdown();
        return view('laravel-admin-package::cms/posts.update_or_create', compact('postTypes'));
    }

    /**
     * Store record on create
     *
     * @param PostRequest $request
     * @return RedirectResponse
     */
    public function store(PostRequest $request, PostTypeRepositoryInterface $postTypeRepository): RedirectResponse
    {
        $data = $request->validated();
        $result = $this->postService->createPostWithTranslations($data)->getData(true);
        $routePrefix = config('laravel-admin-package.admin_url').':';

        if ($result['type'] === 'error') {
            return redirect()->route($routePrefix.'posts.create')
                ->withInput($data)->withErrors(['post' => $result['message']]);
        }

        $postType = $postTypeRepository->getPostTypeById($data['type_id']);
        return redirect()->route($routePrefix.'posts.type.list', ['type' => $postType->type])
            ->with('message', $result['message']);
    }

    public function update(PostRequest $request, Post $post, PostTypeRepositoryInterface $postTypeRepository): RedirectResponse
    {
        $data = $request->validated();
        $result = $this->postService->updatePostWithTranslations($post, $data)->getData(true);
        $routePrefix = config('laravel-admin-package.admin_url').':';

        if ($result['type'] === 'error') {
            return redirect()->route($routePrefix.'posts.edit', $post)
                ->withInput($data)->withErrors(['post' => $result['message']]);
        }

        $postType = $postTypeRepository->getPostTypeById($data['type_id']);
        return redirect()->route($routePrefix.'posts.type.list', ['type' => $postType->type])
            ->with('message', $result['message']);
    }

    public function ajaxChangeStatus(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id' => ['required', 'integer'],
            'status' => ['required', 'boolean'],
        ]);
        $status = (int) $data['status'];
        $post = Post::findOrFail($data['id']);
        $post->status = $status;
        $post->save();

        return response()->json(['success' => ($status == 0) ? 'Post was deactivated' : 'Post was activated']);
    }

}
