<?php

namespace Maxi032\LaravelAdminPackage\Http\Controllers;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Maxi032\LaravelAdminPackage\LaravelAdminPackageServiceProvider;
use Maxi032\LaravelAdminPackage\Models\PostOption;
use Maxi032\LaravelAdminPackage\Requests\PostOptionRequest;
use Maxi032\LaravelAdminPackage\Services\PostOptionService;
use Throwable;

class PostOptionController extends AdminController
{
    public function __construct(private readonly PostOptionService $postOptionService)
    {
    }

    public function index(): View|JsonResponse
    {
        return $this->overviewView('cms.settings.post_options.partials.index', [
            'options' => $this->postOptionService->getPostOptionOverview(),
        ], 'options');
    }

    public function trash(): View|JsonResponse
    {
        return $this->overviewView('cms.settings.post_options.partials.trash', [
            'options' => $this->postOptionService->getPostOptionOverview(trashed: true),
        ], 'options');
    }

    public function create(): View
    {
        return $this->editForm($this->postOptionService->newPostOption());
    }

    public function edit(PostOption $option): View
    {
        return $this->editForm($option);
    }

    public function store(PostOptionRequest $request): RedirectResponse
    {
        $this->postOptionService->createPostOption($request->validated());

        return $this->saved();
    }

    public function update(PostOptionRequest $request, PostOption $option): RedirectResponse
    {
        $this->postOptionService->updatePostOption((int) $option->getKey(), $request->validated());

        return $this->saved();
    }

    public function changeStatus(Request $request, PostOption $option): JsonResponse
    {
        $request->validate(['status' => ['required', 'boolean']]);
        $enabled = $request->boolean('status');

        $this->postOptionService->changePostOptionStatus((int) $option->getKey(), $enabled);

        return response()->json([
            'success' => $enabled ? __('Post option enabled.') : __('Post option disabled.'),
        ]);
    }

    public function destroy(PostOption $option): RedirectResponse
    {
        return $this->respondToDeletion(
            'index', fn () => $this->postOptionService->deletePostOption((int) $option->getKey()),
            __('Post option moved to Trash.'), __('The post option could not be deleted.'),
        );
    }

    public function recover(PostOption $option): RedirectResponse
    {
        return $this->respondToDeletion(
            'trash', fn () => $this->postOptionService->recoverPostOption((int) $option->getKey()),
            __('Post option recovered.'), __('The post option could not be recovered.'),
        );
    }

    public function forceDelete(PostOption $option): RedirectResponse
    {
        return $this->respondToDeletion(
            'trash', fn () => $this->postOptionService->forceDeletePostOption((int) $option->getKey()),
            __('Post option permanently deleted.'), __('The post option could not be permanently deleted.'),
        );
    }

    private function respondToDeletion(
        string $destination,
        Closure $change,
        string $message,
        string $failureMessage,
    ): RedirectResponse {
        $route = LaravelAdminPackageServiceProvider::adminRoutePrefix().'settings.post-options.';
        $redirect = redirect()->route($route.$destination);

        try {
            $change();
        } catch (ModelNotFoundException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return $redirect->withErrors(['option' => $failureMessage]);
        }

        return $redirect->with('message', $message);
    }

    private function editForm(PostOption $option): View
    {
        return $this->packageView('cms.settings.post_options.form', [
            'option' => $option,
            'schemaLocked' => $this->postOptionService->hasSavedValues($option),
        ]);
    }

    private function saved(): RedirectResponse
    {
        return redirect()->route(LaravelAdminPackageServiceProvider::adminRoutePrefix().'settings.post-options.index')
            ->with('message', __('Post option saved.'));
    }
}
