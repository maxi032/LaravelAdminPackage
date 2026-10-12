<?php

namespace Maxi032\LaravelAdminPackage\Http\Controllers;

use Illuminate\Http\JsonResponse;
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
     * Ajax overview
     *
     * @param string $view
     * @param array $data
     * @param string $records
     * @return View|JsonResponse
     */
    protected function overviewView(string $view, array $data, string $records): View|JsonResponse
    {
        $request = request();
        if ($request->ajax()) {
            $paginator = $data[$records];
            $paginator->withQueryString();

            return response()->json([
                'html' => $this->packageView($view.'_table', $data)->render(),
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                ],
            ]);
        }

        $data[$records]->withQueryString();
        // Keep a rendered first page and ordinary links available without JavaScript.
        return $this->packageView(str_replace('partials.', '', $view), $data);
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
