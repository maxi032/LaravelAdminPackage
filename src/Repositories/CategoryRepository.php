<?php

namespace Maxi032\LaravelAdminPackage\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Maxi032\LaravelAdminPackage\Models\CategoryType;
use Maxi032\LaravelAdminPackage\Models\PostType;
use Throwable;
use Maxi032\LaravelAdminPackage\Models\Category;
use Maxi032\LaravelAdminPackage\Repositories\Interfaces\CategoryRepositoryInterface;
use Maxi032\LaravelAdminPackage\Helpers\TranslationSynchronizerHelper;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function __construct(private readonly TranslationSynchronizerHelper $translationSynchronizer)
    {
    }

    public function getCategoryById($categoryId)
    {
        return Category::findOrFail($categoryId);
    }

    public function categoryMatchesPostType(int $categoryId, int $postTypeId): bool
    {
        // The two type tables have independent IDs; their names define compatibility.
        return Category::whereKey($categoryId)
            ->whereHas('type', fn ($types) => $types->whereIn(
                'type', PostType::whereKey($postTypeId)->select('type')
            ))
            ->exists();
    }

    public function deleteCategory($categoryId): void
    {
        DB::transaction(function () use ($categoryId) {
            $category = Category::whereKey($categoryId)->lockForUpdate()->firstOrFail();

            // Keep the category and its translations recoverable and hide both from normal queries.
            foreach ($category->translations()->get() as $translation) {
                if ($translation->delete() === false) {
                    throw new \RuntimeException(__('Category translation deletion was cancelled.'));
                }
            }

            if ($category->delete() === false) {
                throw new \RuntimeException(__('Category deletion was cancelled.'));
            }
        });
    }

    public function recoverCategory(int $categoryId): void
    {
        DB::transaction(function () use ($categoryId) {
            $category = Category::onlyTrashed()->whereKey($categoryId)->lockForUpdate()->firstOrFail();
            foreach ($category->translations()->onlyTrashed()->get() as $translation) {
                if ($translation->restore() === false) {
                    throw new \RuntimeException(__('Category translation recovery was cancelled.'));
                }
            }
            if ($category->restore() === false) {
                throw new \RuntimeException(__('Category recovery was cancelled.'));
            }
        });
    }

    public function forceDeleteCategory(int $categoryId): void
    {
        DB::transaction(function () use ($categoryId) {
            $category = Category::onlyTrashed()->whereKey($categoryId)->lockForUpdate()->firstOrFail();
            if ($category->post()->withTrashed()->exists()) {
                throw new \DomainException(__('A category with attached posts cannot be destroyed.'));
            }
            foreach ($category->translations()->withTrashed()->get() as $translation) {
                if ($translation->forceDelete() === false) {
                    throw new \RuntimeException(__('Category translation destruction was cancelled.'));
                }
            }
            // The database also cascades deletion to child categories and their translations.
            if ($category->forceDelete() === false) {
                throw new \RuntimeException(__('Category destruction was cancelled.'));
            }
        });
    }

    /**
     * @param array $dataArr
     * @return Category
     * @throws Throwable
     */
    public function createCategoryWithTranslations(array $dataArr): Category
    {
        return DB::transaction(function () use ($dataArr) {
            $this->lockCategoryType((int) $dataArr['type_id']);

            $category = Category::create([
                'type_id'     => $dataArr['type_id'],
                'status'      => (int) $dataArr['status'],
                'sort_order'  => $dataArr['sort_order']
                    ?? $this->nextSortOrder((int) $dataArr['type_id']),
            ]);
            if (!$category->exists) {
                throw new \RuntimeException(__('The category could not be saved. Please try again.'));
            }

            $this->translationSynchronizer->sync(
                $category->translations(),
                $dataArr['translations'],
                array_column(config('laravel-admin-package.allowed_languages', []), 'code'),
                __('The category could not be saved. Please try again.'),
                __('Category translation recovery was cancelled.'),
            );

            return $category;
        });
    }

    /**
     * @param Category $category
     * @param array $dataArr
     * @return Category
     * @throws Throwable
     */
    public function updateCategoryWithTranslations(Category $category, array $dataArr): Category
    {
        return DB::transaction(function () use ($category, $dataArr) {
            $typeId = (int) $dataArr['type_id'];
            $this->lockCategoryType($typeId);
            $category = Category::whereKey($category->getKey())->lockForUpdate()->firstOrFail();
            $sortOrder = $dataArr['sort_order'] ?? (
                (int) $category->type_id === $typeId
                    ? $category->sort_order
                    : $this->nextSortOrder($typeId)
            );
            $saved = $category->fill([
                'type_id' => $dataArr['type_id'],
                'status' => (int) $dataArr['status'],
                'sort_order' => $sortOrder,
            ])->save();
            if ($saved === false) {
                throw new \RuntimeException(__('The category could not be saved. Please try again.'));
            }
            $this->translationSynchronizer->sync(
                $category->translations(),
                $dataArr['translations'],
                array_column(config('laravel-admin-package.allowed_languages', []), 'code'),
                __('The category could not be saved. Please try again.'),
                __('Category translation recovery was cancelled.'),
            );

            return $category;
        });
    }

    private function lockCategoryType(int $typeId): void
    {
        // Serialize position assignment for this type, including an empty list.
        CategoryType::whereKey($typeId)->lockForUpdate()->firstOrFail();
    }

    private function nextSortOrder(int $typeId): int
    {
        $maximum = (int) Category::where('type_id', $typeId)->max('sort_order');
        if ($maximum >= 4294967295) {
            throw new \OverflowException(__('No sort positions remain for this category type.'));
        }

        return $maximum + 1;
    }

    public function createCategory(array $categoryAttributes)
    {
        return Category::create($categoryAttributes);
    }

    public function updateCategory($categoryId, array $categoryAttributes)
    {
        return Category::whereId($categoryId)->update($categoryAttributes);
    }

    public function getActiveCategories()
    {
        return Category::where('status', true);
    }

    public function getActiveCategoriesOfType($type)
    {
        return Category::Status(true)->ByType($type);
    }

    public function getCategoryOfType($categoryType)
    {
        return Category::ByType($categoryType);
    }

    public function getCategoriesForDropdown($lang = null, ?int $postTypeId = null)
    {
        // Query fresh data so category changes are immediately available to post forms.
        $query = Category::with(['translations' => fn ($query) => $query->where('language', $lang ?? app()->getLocale())]);
        if ($postTypeId !== null) {
            // Post types and category types have independent IDs; match their names.
            $query->whereHas('type', fn ($types) => $types->whereIn(
                'type', PostType::whereKey($postTypeId)->select('type')
            ));
        }

        return $query->get();
    }

    public function getCategoryOverview(string $type, bool $trashed = false): LengthAwarePaginator
    {
        $query = Category::byType($type);
        if ($trashed) {
            $query->onlyTrashed();
        }
        return $query->with(['translations' => function ($query) use ($trashed) {
            if ($trashed) {
                $query->withTrashed();
            }
            $query->where('language', app()->getLocale());
        }])->orderBy('sort_order')->orderBy('id')->paginate(20);
    }

    public function changeCategoryStatus(int $categoryId, bool $status): void
    {
        DB::transaction(function () use ($categoryId, $status) {
            $category = Category::whereKey($categoryId)->lockForUpdate()->firstOrFail();
            $category->status = $status;
            if ($category->save() === false) {
                throw new \RuntimeException(__('Category status change was cancelled.'));
            }
        });
    }
}
