<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ $initialTheme ?? 'dark' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1a1a1e">

    <title inertia>{{ config('app.name', 'VERTEX Football Agency') }}</title>

    <link rel="icon" href="/favicon.svg" type="image/svg+xml">

    <script>
        (function () {
            try {
                var stored = localStorage.getItem('ab-theme');
                var theme = (stored === 'dark' || stored === 'light')
                    ? stored
                    : (document.documentElement.dataset.theme
                        || (window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark'));
                document.documentElement.dataset.theme = theme;
                document.documentElement.style.colorScheme = theme;
            } catch (e) {}
        })();
    </script>

    @fonts
    @routes
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead
</head>
<body class="min-h-screen bg-bg text-fg antialiased">
    @inertia
</body>
</html>
