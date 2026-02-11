<!DOCTYPE html>
<html lang="tr" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} - @yield('title', 'Giris')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
    @vite('resources/sass/app.scss')
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-700 antialiased">
    <div class="min-h-screen lg:grid lg:grid-cols-2">
        <aside class="relative hidden overflow-hidden bg-gradient-to-br from-indigo-600 via-indigo-700 to-slate-900 p-12 text-white lg:flex lg:flex-col lg:justify-between">
            <div class="absolute -left-16 top-24 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>
            <div class="absolute bottom-8 right-0 h-72 w-72 rounded-full bg-cyan-300/10 blur-3xl"></div>
            <div class="relative">
                <div class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-white/15 ring-1 ring-white/30">
                    <span class="material-symbols-outlined text-3xl">apartment</span>
                </div>
                <h1 class="mt-8 text-4xl font-extrabold leading-tight">
                    Site yonetimini tek panelden kontrol edin.
                </h1>
                <p class="mt-4 max-w-md text-sm text-indigo-100/90">
                    Tahakkuk, tahsilat, gider ve rapor akislarini hizli ve tutarli bir deneyimle yonetin.
                </p>
            </div>
            <p class="relative text-xs text-indigo-100/80">
                &copy; {{ date('Y') }} Site Yonetim Sistemi
            </p>
        </aside>

        <main class="relative flex items-center justify-center p-4 sm:p-8">
            <div class="w-full max-w-md">
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
