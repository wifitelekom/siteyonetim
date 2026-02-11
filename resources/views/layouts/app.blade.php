<!DOCTYPE html>
<html lang="tr" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} - @yield('title', 'Genel Bakis')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="h-full bg-slate-50 font-sans text-slate-700 antialiased">
    <div x-data="{ sidebarOpen: false }" class="relative min-h-screen">
        @include('components.sidebar')

        <div class="min-h-screen lg:pl-64">
            @include('components.navbar')

            <main class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                @include('components.alert')
                @yield('content')
            </main>
        </div>
    </div>

    @include('components.modal-delete')
    @include('components.toast')
    @stack('scripts')
</body>
</html>
