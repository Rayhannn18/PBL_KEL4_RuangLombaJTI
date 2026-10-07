<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') — RuangLombaJTI</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-page font-inter text-ink antialiased">
    <div class="mx-auto flex min-h-screen w-full max-w-5xl flex-col px-5 sm:px-8">
        <header class="flex items-center justify-between py-6">
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                <x-auth.logo />
                <span class="text-xl font-extrabold tracking-tight text-ink">RuangLomba<span class="text-brand">JTI</span></span>
            </a>
            @yield('header-action')
        </header>

        <main class="flex flex-1 flex-col items-center justify-center py-6">
            <x-auth.logo size="lg" />
            <p class="mt-3 text-[11px] font-bold uppercase tracking-[0.12em] text-muted">Portal Resmi TI POLINEMA</p>

            <div class="mt-6 w-full max-w-[430px] rounded-[28px] bg-white px-8 pb-8 pt-9 shadow-[0_20px_50px_-20px_rgba(31,74,168,0.25)] ring-1 ring-line/60">
                @yield('content')
            </div>
        </main>

        <footer class="py-8 text-center text-[13px] text-muted">
            &copy; {{ now()->year }} RuangLombaJTI — Jurusan Teknologi Informasi Politeknik Negeri Malang
        </footer>
    </div>
</body>
</html>