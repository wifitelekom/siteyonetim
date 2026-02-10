<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} - @yield('title', 'Giriş')</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-5 col-lg-4">
                <div class="text-center mb-4">
                    <h2 class="fw-bold"><i class="bi bi-building"></i> Site Yönetimi</h2>
                    <p class="text-muted">Apartman / Site Yönetim Sistemi</p>
                </div>
                @yield('content')
            </div>
        </div>
    </div>
</body>
</html>
