@props(['title' => 'Project Expedition'])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full flex-col bg-slate-50 text-slate-900 antialiased">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
            <a href="{{ url('/') }}" class="rounded text-xl font-bold tracking-tight text-[#f05f40] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#f05f40]">Project Expedition</a>
            <p class="text-sm text-slate-500">Destination explorer</p>
        </div>
    </header>

    <main class="mx-auto w-full max-w-6xl flex-1 px-4 py-8 sm:px-6 lg:px-8">
        {{ $slot }}
    </main>

    <footer class="border-t border-slate-200 bg-white">
        <p class="mx-auto max-w-6xl px-4 py-4 text-sm text-slate-500 sm:px-6 lg:px-8">Project Expedition candidate assignment</p>
    </footer>
</body>
</html>
