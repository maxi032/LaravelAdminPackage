<!doctype html>
<html lang="@yield('language')">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/@coreui/coreui@4.3.0/dist/css/coreui.min.css" rel="stylesheet"
          integrity="sha384-2E9/b2fZ+VJoP6eRIpzzMFkuqbh0qDkIFVLzJJwkESsdKPEwzb0n6E55enZ+Ee8V" crossorigin="anonymous">

    <title>@yield('meta_title')</title>
</head>
<body>
<main class="py-4">
    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/@coreui/coreui@4.3.0/dist/js/coreui.bundle.min.js"
        integrity="sha384-Iqk8EE7ao72xAVBuuQTEwrU5N2IdvpJER5ZzM6LxwcXHqj2WstttaOvopXziv0nU"
        crossorigin="anonymous"></script>

</body>
</html>
