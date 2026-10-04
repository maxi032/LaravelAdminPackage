<?php

namespace Maxi032\LaravelAdminPackage\Composers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Lang;
use Illuminate\View\View;
use Maxi032\LaravelAdminPackage\Models\Post;
use Maxi032\LaravelAdminPackage\Models\PostType;
use Maxi032\LaravelAdminPackage\Repositories\Interfaces\PostTypeRepositoryInterface;

class SidebarComposer
{
    public function __construct(
        private readonly Request $request,
        private readonly PostTypeRepositoryInterface $postTypeRepository,
    ) {
    }

    public function compose(View $view): void
    {
        $type = $this->request->route('type');
        $post = $this->request->route('post');
        $typeName = $type instanceof PostType ? $type->type : $type;
        $icons = [
            'faq' => 'cil-speech',
            'products' => 'cil-basket',
            'downloads' => 'cil-cloud-download',
            'blog_articles' => 'cil-newspaper',
        ];

        $items = $this->postTypeRepository->getPostTypesForSidebar()
            ->map(fn (PostType $item) => [
                'type' => $item->type,
                'label' => Lang::has($item->type.'.plural')
                    ? __($item->type.'.plural') : Str::headline($item->type),
                'singularLabel' => Lang::has($item->type.'.singular')
                    ? __($item->type.'.singular') : Str::headline(Str::singular($item->type)),
                'icon' => $icons[$item->type] ?? 'cil-description',
                'active' => $typeName === $item->type || ($post instanceof Post && (int) $post->type_id === (int) $item->id),
                'menuId' => 'post-type-menu-'.$item->id,
            ]);

        $view->with('sidebarPostTypes', $items);
        $view->with('adminRoutePrefix', config('laravel-admin-package.admin_url').':');
    }
}
