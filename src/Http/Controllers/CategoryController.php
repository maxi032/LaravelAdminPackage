<?php

namespace Maxi032\LaravelAdminPackage\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maxi032\LaravelAdminPackage\LaravelAdminPackageServiceProvider;
use Maxi032\LaravelAdminPackage\Models\Category;
use Maxi032\LaravelAdminPackage\Models\CategoryType;
use Maxi032\LaravelAdminPackage\Models\PostType;
use Maxi032\LaravelAdminPackage\Repositories\Interfaces\CategoryTypeRepositoryInterface;
use Maxi032\LaravelAdminPackage\Requests\CategoryRequest;
use Maxi032\LaravelAdminPackage\Services\CategoryService;
use Throwable;

class CategoryController extends AdminController
{
    public function __construct(private readonly CategoryService $categoryService)
    {
    }

    public function list(CategoryType $type): View|JsonResponse
    {
        return $this->overviewView('cms.categories.partials.index', [
            'categoryType' => $type,
            'categories' => $this->categoryService->getCategoryOverview($type->type),
            'hasPostType' => PostType::where('type', $type->type)->exists(),
        ], 'categories');
    }

    public function trash(CategoryType $type): View|JsonResponse
    {
        return $this->overviewView('cms.categories.partials.trash', [
            'categoryType' => $type,
            'categories' => $this->categoryService->getCategoryOverview($type->type, true),
        ], 'categories');
    }

    public function recover(Category $category): RedirectResponse
    {
        abort_unless($category->trashed(), 404);
        $redirect = $this->trashRedirect($category);
        try {
            $this->categoryService->recoverCategory((int) $category->getKey());
        } catch (Throwable $e) {
            Log::error('Category recovery failed.', ['exception' => $e]);
            return $redirect->withErrors(['category' => __('The category could not be recovered. Please try again.')]);
        }

        return $redirect->with('message', __('Category recovered successfully!'));
    }

    public function forceDelete(Category $category): RedirectResponse
    {
        abort_unless($category->trashed(), 404);
        $redirect = $this->trashRedirect($category);
        try {
            $this->categoryService->forceDeleteCategory((int) $category->getKey());
        } catch (\DomainException $e) {
            return $redirect->withErrors(['category' => $e->getMessage()]);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Category destruction failed.', ['exception' => $e]);
            $message = ($e->errorInfo[1] ?? null) === 1451
                ? __('A category with attached posts cannot be destroyed.')
                : __('The category could not be destroyed. Please try again.');
            return $redirect->withErrors(['category' => $message]);
        } catch (Throwable $e) {
            Log::error('Category destruction failed.', ['exception' => $e]);
            return $redirect->withErrors(['category' => __('The category could not be destroyed. Please try again.')]);
        }

        return $redirect->with('message', __('Category destroyed successfully!'));
    }

    private function trashRedirect(Category $category): RedirectResponse
    {
        $type = $category->type;
        return $type
            ? redirect()->route(LaravelAdminPackageServiceProvider::adminRoutePrefix().'categories.trash', ['type' => $type->type])
            : redirect()->route(LaravelAdminPackageServiceProvider::adminRoutePrefix().'dashboard');
    }

    /**
     * Show the category creation form.
     */
    public function create(string $type = null): Renderable
    {
        $categoryTypes = $this->categoryService->getCategoryTypesForDropdown();
        $selectedTypeId = null;
        if ($type !== null) {
            $selectedTypeId = array_search($type, $categoryTypes, true);
            abort_if($selectedTypeId === false, 404);
        }

        return $this->packageView('cms.categories.update_or_create', [
            'categoryTypes' => $categoryTypes,
            'category' => null,
            'selectedTypeId' => $selectedTypeId,
        ]);
    }

    public function edit(Category $category): View
    {
        // Route model binding will throw 404 automatically if the category is not found.
        $categoryTypes = $this->categoryService->getCategoryTypesForDropdown();

        return $this->packageView('cms.categories.update_or_create', [
            'categoryTypes' => $categoryTypes,
            'category' => $category,
        ]);
    }

    /**
     * Store record on create
     */
    public function store(CategoryRequest $request, CategoryTypeRepositoryInterface $categoryTypeRepository): RedirectResponse
    {
        $data = $request->validated();
        $routePrefix = LaravelAdminPackageServiceProvider::adminRoutePrefix();

        try {
            $this->categoryService->createCategoryWithTranslations($data);
        } catch (Throwable $e) {
            Log::error('Category creation failed.', ['exception' => $e]);

            return redirect()->route($routePrefix.'categories.create')
                ->withInput($data)
                ->withErrors(['category' => __('The category could not be saved. Please try again.')]);
        }

        $categoryType = $categoryTypeRepository->getCategoryTypeById($data['type_id']);

        return redirect()->route($routePrefix.'categories.type.list', ['type' => $categoryType->type])
            ->with('message', __('Category created successfully!'));
    }

    public function update(CategoryRequest $request, Category $category, CategoryTypeRepositoryInterface $categoryTypeRepository): RedirectResponse
    {
        $data = $request->validated();
        $routePrefix = LaravelAdminPackageServiceProvider::adminRoutePrefix();

        try {
            $this->categoryService->updateCategoryWithTranslations($category, $data);
        } catch (Throwable $e) {
            Log::error('Category update failed.', ['exception' => $e]);

            return redirect()->route($routePrefix.'categories.edit', $category)
                ->withInput($data)
                ->withErrors(['category' => __('The category could not be saved. Please try again.')]);
        }

        $categoryType = $categoryTypeRepository->getCategoryTypeById($data['type_id']);

        return redirect()->route($routePrefix.'categories.type.list', ['type' => $categoryType->type])
            ->with('message', __('Category updated successfully!'));
    }

    public function destroy(Category $category): RedirectResponse
    {
        // Binding uses the numeric ID and excludes already deleted categories.
        $type = $category->type;
        $redirect = $type
            ? redirect()->route(LaravelAdminPackageServiceProvider::adminRoutePrefix().'categories.type.list', ['type' => $type->type])
            : redirect()->route(LaravelAdminPackageServiceProvider::adminRoutePrefix().'dashboard');

        try {
            $this->categoryService->deleteCategory((int) $category->getKey());
        } catch (Throwable $e) {
            Log::error('Category deletion failed.', ['exception' => $e]);

            return $redirect->withErrors(['category' => __('The category could not be deleted. Please try again.')]);
        }

        return $redirect->with('message', __('Category deleted successfully!'));
    }

    public function changeStatus(Request $request, Category $category): RedirectResponse
    {
        $data = validator($request->all(), ['status' => ['required', 'boolean']])->validate();
        $type = $category->type;
        $redirect = $type
            ? redirect()->route(LaravelAdminPackageServiceProvider::adminRoutePrefix().'categories.type.list', ['type' => $type->type])
            : redirect()->route(LaravelAdminPackageServiceProvider::adminRoutePrefix().'dashboard');
        try {
            $this->categoryService->changeCategoryStatus((int) $category->getKey(), (bool) $data['status']);
        } catch (Throwable $e) {
            Log::error('Category status change failed.', ['exception' => $e]);
            return $redirect->withErrors(['category' => __('The category status could not be changed. Please try again.')]);
        }
        return $redirect->with('message', $data['status'] ? __('Category was activated') : __('Category was deactivated'));
    }
}
