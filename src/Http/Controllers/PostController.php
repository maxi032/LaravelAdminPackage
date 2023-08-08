<?php

namespace Maxi032\LaravelAdminPackage\Http\Controllers;

use \Illuminate\Contracts\View\View;
use Maxi032\LaravelAdminPackage\Requests\PostRequest;
use Maxi032\LaravelAdminPackage\Services\PostService;

class PostController extends AdminController
{
    private PostService $postService;
    public function __construct(PostService $postService){
        $this->postService = $postService;
    }

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
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function create()
    {
        return view('laravel-admin-package::dashboard');
    }

    public function store(PostRequest $request)
    {
        $savedPost = $this->postService->createPostWithTranslations($request->validated());
        if($savedPost['type'] === "success"){
            return redirect()->route('cms.posts.index')
                ->with($savedPost);
        } else {
            return redirect()->back()
                ->with($savedPost);
        }
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
