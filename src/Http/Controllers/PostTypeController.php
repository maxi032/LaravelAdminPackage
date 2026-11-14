<?php

namespace Maxi032\LaravelAdminPackage\Http\Controllers;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Maxi032\LaravelAdminPackage\LaravelAdminPackageServiceProvider;
use Maxi032\LaravelAdminPackage\Models\PostType;
use Maxi032\LaravelAdminPackage\Requests\PostTypeRequest;
use Maxi032\LaravelAdminPackage\Services\PostTypeService;
use Throwable;

class PostTypeController extends AdminController
{
    public function __construct(private readonly PostTypeService $postTypeService)
    {
    }

    public function index(): View|JsonResponse
    {
        return $this->overviewView('cms.settings.post_types.partials.index', [
            'postTypes' => $this->postTypeService->getPostTypeOverview(),
        ], 'postTypes');
    }

    public function trash(): View|JsonResponse
    {
        return $this->overviewView('cms.settings.post_types.partials.trash', [
            'postTypes' => $this->postTypeService->getPostTypeOverview(trashed: true),
        ], 'postTypes');
    }

    public function posts(PostType $postType): View
    {
        return $this->packageView('cms.posts.partials.post_links', [
            'posts' => $this->postTypeService->getPosts($postType),
        ]);
    }

    public function create(): View
    {
        return $this->edit(new PostType);
    }

    public function edit(PostType $postType): View
    {
        return $this->packageView('cms.settings.post_types.form', [
            'postType' => $postType,
            'icons' => PostType::iconOptions(),
            'statusLocked' => $postType->status && $postType->hasPosts(),
        ]);
    }

    public function store(PostTypeRequest $request): RedirectResponse
    {
        return $this->update($request, new PostType);
    }

    public function update(PostTypeRequest $request, PostType $postType): RedirectResponse
    {
        $this->postTypeService->savePostType($postType, $request->validated());

        return $this->overviewRedirect('index')->with('message', __('Post type saved.'));
    }

    public function destroy(PostType $postType): RedirectResponse
    {
        return $this->respondToDeletion(
            'index', fn () => $this->postTypeService->deletePostType($postType),
            __('Post type moved to Trash.'), __('The post type could not be deleted.'),
        );
    }

    public function recover(PostType $postType): RedirectResponse
    {
        return $this->respondToDeletion(
            'trash', fn () => $this->postTypeService->recoverPostType($postType),
            __('Post type recovered.'), __('The post type could not be recovered.'),
        );
    }

    public function forceDelete(PostType $postType): RedirectResponse
    {
        return $this->respondToDeletion(
            'trash', fn () => $this->postTypeService->forceDeletePostType($postType),
            __('Post type permanently deleted.'), __('The post type could not be permanently deleted.'),
        );
    }

    private function respondToDeletion(string $destination, Closure $change, string $message, string $failureMessage): RedirectResponse
    {
        $redirect = $this->overviewRedirect($destination);

        try {
            $change();
        } catch (ValidationException $exception) {
            return $redirect->withErrors($exception->errors());
        } catch (ModelNotFoundException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return $redirect->withErrors(['type' => $failureMessage]);
        }

        return $redirect->with('message', $message);
    }

    public function changeStatus(Request $request, PostType $postType): JsonResponse
    {
        $request->validate(['status' => ['required', 'boolean']]);
        $status = $request->boolean('status');
        $this->postTypeService->changePostTypeStatus($postType, $status);

        return response()->json(['success' => $status ? __('Post type enabled.') : __('Post type disabled.')]);
    }

    private function overviewRedirect(string $destination): RedirectResponse
    {
        return redirect()->route(LaravelAdminPackageServiceProvider::adminRoutePrefix().'settings.post-types.'.$destination);
    }
}
