<!DOCTYPE html>
<html lang="tr" data-theme="{{ session('theme', 'light') }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} - @yield('title', 'Genel Bakış')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;450;500;600;700&display=swap"
        rel="stylesheet">
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    @stack('styles')
</head>

<body>
    <div class="d-flex" id="wrapper">
        @include('components.sidebar')

        <div id="page-content-wrapper">
            @include('components.navbar')

            <main class="container-fluid px-4 py-3">
                @include('components.alert')
                @yield('content')
            </main>
        </div>
    </div>

    @include('components.modal-delete')
    @include('components.toast')

    @stack('scripts')

    <script>
        // Dark mode init from localStorage
        (function () {
            const saved = localStorage.getItem('sy-theme');
            if (saved) {
                document.documentElement.setAttribute('data-theme', saved);
            }
        })();
    </script>
</body>

</html>