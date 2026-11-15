<?php

namespace Maxi032\LaravelAdminPackage\Composers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maxi032\LaravelAdminPackage\Models\Post;
use Maxi032\LaravelAdminPackage\Models\Category;
use Maxi032\LaravelAdminPackage\Models\CategoryType;
use Maxi032\LaravelAdminPackage\Models\PostType;
use Maxi032\LaravelAdminPackage\Repositories\Interfaces\PostTypeRepositoryInterface;
use Maxi032\LaravelAdminPackage\Repositories\PostTypeRepository;

class SidebarComposer
{
    public function __construct(
        private readonly Request $request,

        /** @var PostTypeRepository */
        private readonly PostTypeRepositoryInterface $postTypeRepository,
    ) {
    }

    public function compose(View $view): void
    {
        $type = $this->request->route('type');
        $post = $this->request->route('post');
        $category = $this->request->route('category');
        $typeName = ($type instanceof PostType || $type instanceof CategoryType) ? $type->type : $type;
        if ($category instanceof Category) {
            $typeName = $category->type?->type;
        }
        $items = $this->postTypeRepository->getPostTypesForSidebar()
            ->where('status', true)
            ->map(fn (PostType $item) => [
                'type' => $item->type,
                'label' => Str::ucfirst(Str::lower($item->displayName())),
                'addLabel' => __('Add :type', [
                    'type' => Lang::has($item->type.'.singular')
                        ? __($item->type.'.singular') : Str::headline(Str::singular($item->type)),
                ]),
                'allLabel' => Lang::has($item->type.'.all')
                    ? __($item->type.'.all') : __('All :type', ['type' => $item->displayName()]),
                'icon' => $item->icon,
                'active' => $typeName === $item->type || ($post instanceof Post && (int) $post->type_id === (int) $item->id),
                'menuId' => 'post-type-menu-'.$item->id,
            ]);

        $view->with('sidebarPostTypes', $items);
    }
}
