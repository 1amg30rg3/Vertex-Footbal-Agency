<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#ffffff">

    <title inertia>{{ config('app.name', 'VERTEX Football Agency') }}</title>

    <link rel="icon" href="/favicon.svg" type="image/svg+xml">

    @fonts
    @routes
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead
</head>
<body class="min-h-screen bg-bg text-fg antialiased">
    @inertia
</body>
</html>
