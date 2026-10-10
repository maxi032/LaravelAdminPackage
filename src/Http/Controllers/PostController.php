<?php

namespace Maxi032\LaravelAdminPackage\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maxi032\LaravelAdminPackage\Enums\PostStatusEnum;
use Maxi032\LaravelAdminPackage\LaravelAdminPackageServiceProvider;
use Maxi032\LaravelAdminPackage\Models\Post;
use Maxi032\LaravelAdminPackage\Models\PostType;
use Maxi032\LaravelAdminPackage\Repositories\Interfaces\PostTypeRepositoryInterface;
use Maxi032\LaravelAdminPackage\Requests\PostRequest;
use Maxi032\LaravelAdminPackage\Services\PostService;
use Throwable;

class PostController extends AdminController
{
    public function __construct(private readonly PostService $postService)
    {
    }

    /**
     * Show a list of the resource of a certain type
     */
    public function list(PostType $type): Renderable
    {
        // Route model binding will throw 404 automatically it $type is not found
        $type->load(['posts.translations']);
        $posts = $type->posts;

        return $this->packageView('cms.posts.index', [
            'posts' => $posts,
            'postType' => $type,
            'inactiveStatus' => PostStatusEnum::DRAFT,
            'activeStatus' => PostStatusEnum::PENDING,
        ]);
    }

    public function trash(PostType $type): Renderable
    {
        $posts = $type->posts()->onlyTrashed()
            ->with(['translations' => fn ($query) => $query->withTrashed()])
            ->orderByDesc('deleted_at')->orderByDesc('id')->get();

        return $this->packageView('cms.posts.trash', ['posts' => $posts, 'postType' => $type]);
    }

    public function recover(Post $post): RedirectResponse
    {
        abort_unless($post->trashed(), 404);
        $redirect = $this->trashRedirect($post);
        try {
            $this->postService->recoverPost((int) $post->getKey());
        } catch (Throwable $e) {
            Log::error('Post recovery failed.', ['exception' => $e]);
            return $redirect->withErrors(['post' => __('The post could not be recovered. Please try again.')]);
        }

        return $redirect->with('message', __('Post recovered successfully!'));
    }

    public function forceDelete(Post $post): RedirectResponse
    {
        abort_unless($post->trashed(), 404);
        $redirect = $this->trashRedirect($post);
        try {
            $this->postService->forceDeletePost((int) $post->getKey());
        } catch (Throwable $e) {
            Log::error('Post destruction failed.', ['exception' => $e]);
            return $redirect->withErrors(['post' => __('The post could not be destroyed. Please try again.')]);
        }

        return $redirect->with('message', __('Post destroyed successfully!'));
    }

    private function trashRedirect(Post $post): RedirectResponse
    {
        $type = $post->type;
        return $type
            ? redirect()->route(LaravelAdminPackageServiceProvider::adminRoutePrefix().'posts.trash', ['type' => $type->type])
            : redirect()->route(LaravelAdminPackageServiceProvider::adminRoutePrefix().'dashboard');
    }

    /**
     * Show the Posts crud form.
     */
    public function create(string $type = null): Renderable
    {
        $postTypes = $this->postService->getPostTypesForDropdown();
        $categories = $this->postService->getCategoriesForDropdown();
        $selectedTypeId = null;
        if ($type !== null) {
            $selectedTypeId = array_search($type, $postTypes, true);
            abort_if($selectedTypeId === false, 404);
        }

        return $this->packageView('cms.posts.update_or_create', [
            'postTypes' => $postTypes,
            'post' => null,
            'selectedTypeId' => $selectedTypeId,
            'categories' => $categories,
        ]);
    }

    public function edit(Post $post): View
    {
        // Route model binding will throw 404 automatically if $post is not found
        $postTypes = $this->postService->getPostTypesForDropdown();
        $categories = $this->postService->getCategoriesForDropdown();

        return $this->packageView('cms.posts.update_or_create', [
            'postTypes' => $postTypes,
            'post' => $post,
            'categories' => $categories,
        ]);
    }

    public function show(): Renderable
    {
        $postTypes = $this->postService->getPostTypesForDropdown();

        return $this->packageView('cms.posts.update_or_create', compact('postTypes'));
    }

    /**
     * Store record on create
     */
    public function store(PostRequest $request, PostTypeRepositoryInterface $postTypeRepository): RedirectResponse
    {
        $data = $request->validated();
        $routePrefix = LaravelAdminPackageServiceProvider::adminRoutePrefix();

        try {
            $this->postService->createPostWithTranslations($data);
        } catch (Throwable $e) {
            Log::error('Post creation failed.', ['exception' => $e]);

            return redirect()->route($routePrefix.'posts.create')
                ->withInput($data)
                ->withErrors(['post' => __('The post could not be saved. Please try again.')]);
        }

        $postType = $postTypeRepository->getPostTypeById($data['type_id']);

        return redirect()->route($routePrefix.'posts.type.list', ['type' => $postType->type])
            ->with('message', __('Post created successfully!'));
    }

    public function update(PostRequest $request, Post $post, PostTypeRepositoryInterface $postTypeRepository): RedirectResponse
    {
        $data = $request->validated();
        $routePrefix = LaravelAdminPackageServiceProvider::adminRoutePrefix();

        try {
            $this->postService->updatePostWithTranslations($post, $data);
        } catch (Throwable $e) {
            Log::error('Post update failed.', ['exception' => $e]);

            return redirect()->route($routePrefix.'posts.edit', $post)
                ->withInput($data)
                ->withErrors(['post' => __('The post could not be saved. Please try again.')]);
        }

        $postType = $postTypeRepository->getPostTypeById($data['type_id']);

        return redirect()->route($routePrefix.'posts.type.list', ['type' => $postType->type])
            ->with('message', __('Post updated successfully!'));
    }

    public function destroy(Post $post): RedirectResponse
    {
        // Binding uses the numeric ID and excludes posts that are already soft deleted.
        $type = $post->type;
        $redirect = $type
            ? redirect()->route(LaravelAdminPackageServiceProvider::adminRoutePrefix().'posts.type.list', ['type' => $type->type])
            : redirect()->route(LaravelAdminPackageServiceProvider::adminRoutePrefix().'dashboard');

        try {
            $this->postService->deletePost((int) $post->getKey());
        } catch (Throwable $e) {
            Log::error('Post deletion failed.', ['exception' => $e]);

            return $redirect->withErrors(['post' => __('The post could not be deleted. Please try again.')]);
        }

        return $redirect->with('message', __('Post deleted successfully!'));
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

        return response()->json(['success' => ($status == 0) ? __('Post was deactivated') : __('Post was activated')]);
    }
}
