<?php

namespace Maxi032\LaravelAdminPackage\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Maxi032\LaravelAdminPackage\LaravelAdminPackageServiceProvider;

class AdminController extends Controller
{
    protected function packageView(string $view, array $data = []): View
    {
        return view(
            LaravelAdminPackageServiceProvider::getAdmPackageName().'::'.$view,
            $data
        );
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return $this->packageView('dashboard');
    }
}
