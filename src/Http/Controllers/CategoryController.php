<?php

namespace Maxi032\LaravelAdminPackage\Http\Controllers;

use Illuminate\Contracts\View\View;
use Maxi032\LaravelAdminPackage\Models\Category;
use Maxi032\LaravelAdminPackage\Models\PostType;

class CategoryController extends AdminController
{
    public function list(PostType $type): View
    {
        // Post types and category types are matched by name, not by numeric ID.
        $categories = Category::byType($type->type)
            ->with(['translations' => fn ($query) => $query->where('language', app()->getLocale())])
            ->orderBy('sort_order')->orderBy('id')->paginate(20);

        return $this->packageName('cms.categories.index', [
            'postType' => $type,
            'categories' => $categories,
            'adminRoutePrefix' => config('laravel-admin-package.admin_url').':',
        ]);
    }
}
