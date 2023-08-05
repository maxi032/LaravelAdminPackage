<?php

namespace Maxi032\LaravelAdminPackage\Http\Controllers;

use Illuminate\Routing\Controller;

class AdminController extends Controller
{

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
       return view('laravel-admin-package::dashboard');
    }
}
