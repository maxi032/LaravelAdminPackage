<?php

namespace Maxi032\LaravelAdminPackage\Composers;


use Illuminate\Http\Request;
use Illuminate\View\View;
use \Illuminate\Support\Collection;

class BreadcrumbComposer
{
    public function __construct(protected Request $request)
    {

    }

    /**
     * Bind data to the view.
     *
     * @param View $view
     * @return void
     */
    public function compose(View $view): void
    {
        $view->with('breadcrumbs', $this->byUrl());
    }

    protected function byUrl(): Collection
    {
        return collect($this->request->segments())->mapWithKeys(function ($segment, $key) {
            return [
                $segment => implode('/', array_slice($this->request->segments(), 0, $key + 1))
            ];
        });
    }

}