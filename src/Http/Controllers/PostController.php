<?php

namespace Maxi032\LaravelAdminPackage\Http\Controllers;

use \Illuminate\Contracts\View\View;
use Maxi032\LaravelAdminPackage\Requests\PostRequest;
use Maxi032\LaravelAdminPackage\Services\PostService;
use \Illuminate\Http\RedirectResponse;

class PostController extends AdminController
{
    public function __construct(private readonly PostService $postService){}

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
       return view('laravel-admin-package::cms.posts.index');
    }


    /**
     * Show the Posts crud form.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function create()
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
    public function store(PostRequest $request): RedirectResponse
    {
        $savedPost = $this->postService->createPostWithTranslations($request->all());
        return ($savedPost && json_decode($savedPost->getContent())->type === 'error') ?
            redirect()->route('admin:posts.create')
            ->with(['message'=>json_decode($savedPost->getContent())->message]):
            redirect()->route('admin:posts.index')
                ->with(['message'=>json_decode($savedPost->getContent())->message]);
    }

    public function edit(): View
    {
        return view('laravel-admin-package::cms.posts.create_editForm');
    }

    public function update(PostRequest $request, $id)
    {

        Message::find($id)->update($request->validated());
        return redirect()->route('admin:posts.index');
    }

}
