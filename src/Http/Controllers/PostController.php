<?php

namespace Maxi032\LaravelAdminPackage\Http\Controllers;

use \Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Maxi032\LaravelAdminPackage\Enums\PostStatusEnum;
use Maxi032\LaravelAdminPackage\Models\Post;
use Maxi032\LaravelAdminPackage\Models\PostType;
use Maxi032\LaravelAdminPackage\Repositories\Interfaces\PostTypeRepositoryInterface;
use Maxi032\LaravelAdminPackage\Repositories\PostTypeRepository;
use Maxi032\LaravelAdminPackage\Requests\PostRequest;
use Maxi032\LaravelAdminPackage\Services\PostService;
use \Illuminate\Http\RedirectResponse;
use \Illuminate\Contracts\Support\Renderable;
use \Illuminate\Http\JsonResponse;

class PostController extends AdminController
{
    public function __construct(private readonly PostService $postService)
    {
    }

    /**
     * Show the application dashboard.
     *
     * @return Renderable
     */
    public function list($postType)
    {
        $posts = Post::with(['translations', 'type' => fn($query) => $query->where('type', $postType)])
            ->whereHas('type', fn($query) => $query->where('type', $postType)
            )->get();

        return view('laravel-admin-package::cms.posts.index', [
            "posts"          => $posts,
            "postType"       => $postType,
            "inactiveStatus" => PostStatusEnum::DRAFT,
            "activeStatus"   => PostStatusEnum::PENDING
        ]);
    }

    /**
     * Show the application dashboard.
     *
     * @return Renderable
     */
    public function ajaxList($postType)
    {
        $posts = Post::with(['translations', 'type' => fn($query) => $query->where('type', $postType)])
            ->whereHas('type', fn($query) => $query->where('type', $postType)
            )->get();
        return $posts;
    }


    /**
     * Show the Posts crud form.
     *
     * @return Renderable
     */
    public function create(): Renderable
    {
        $postTypes = $this->postService->getPostTypesForDropdown();
        return view('laravel-admin-package::cms/posts.update_or_create', [
            'postTypes' => $postTypes,
            'post' => null
        ]);
    }

    public function edit(Post $post)
    {
        $postTypes = $this->postService->getPostTypesForDropdown();
        return view('laravel-admin-package::cms/posts.update_or_create', [
            'postTypes' => $postTypes,
            'post'      => $post
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
        $savedPost = $this->postService->createPostWithTranslations($request->all());
        $postType = $postTypeRepository->getPostTypeById($request->get('type_id'));
        return ($savedPost && json_decode($savedPost->getContent())->type === 'error') ?
            redirect()->route('admin:posts.create')
                ->with(['message' => json_decode($savedPost->getContent())->message]) :
            redirect()->route('admin:posts.type.list', ['type' => $postType->type])
                ->with(['message' => json_decode($savedPost->getContent())->message]);
    }

    public function update(Post $post, PostTypeRepositoryInterface $postTypeRepository): RedirectResponse
    {
        // Manually create an instance of PostRequest
        $postRequest = app(PostRequest::class);
        $data = $postRequest->all();
        $postType = $postTypeRepository->getPostTypeById($postRequest->get('type_id'));
        $updatedPost = $this->postService->updatePostWithTranslations($data);
        return ($updatedPost && json_decode($updatedPost->getContent())->type === 'error') ?
            redirect()->route('admin:posts.edit',$post)
                ->with(['message' => json_decode($updatedPost->getContent())->message]) :
            redirect()->route('admin:posts.type.list', ['type' => $postType->type])
                ->with(['message' => json_decode($updatedPost->getContent())->message]);
    }

    public function ajaxChangeStatus(Request $request): JsonResponse
    {
        $id = $request->get('id');
        $status = $request->get('status');
        $post = Post::findOrFail($id);
        $post->status = $status;
        $post->save();

        return response()->json(['success' => 'Post was ' . ($post->status == 1) ? 'activated' : 'deactivated']);
    }

}
