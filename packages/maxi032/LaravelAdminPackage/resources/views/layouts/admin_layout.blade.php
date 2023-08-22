<!doctype html>
<html lang="@yield('language')">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('meta_title')</title>
    @stack('head-scripts')
    @vite('resources/sass/maxi032/laravel-admin-package/coreui/admin_app.scss')
</head>
<body>
    @include($laravelAdminPackage.'::cms.partials.sidebar')

    <div class="wrapper d-flex flex-column min-vh-100 bg-light">
        @include($laravelAdminPackage.'::cms.partials.header')
        <div class="body flex-grow-1 px-3">
            <div class="container-lg">
                @yield('content')
            </div>
        </div>
        @include($laravelAdminPackage.'::cms.partials.footer')
    </div>

    @vite('resources/js/maxi032/laravel-admin-package/coreui/admin_app.js')

    @stack('footer-scripts')
</body>
</html>
