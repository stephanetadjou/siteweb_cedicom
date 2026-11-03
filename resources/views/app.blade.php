<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#265CA6">
        <meta property="og:type" content="website">
        <meta property="og:image" content="{{ asset('images/hero-entrepot.jpg') }}">
        <link rel="icon" href="{{ asset('favicon.ico') }}">

        <title inertia>{{ __('meta.title') }}</title>
        <meta name="description" content="{{ __('meta.description') }}" inertia="description">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@600;700;800&family=Source+Sans+3:wght@400;600;700&display=swap">

        {{-- Active les apparitions au défilement avant le premier rendu (sauf « réduire les animations ») --}}
        <script>if ('IntersectionObserver' in window && !matchMedia('(prefers-reduced-motion: reduce)').matches) document.documentElement.classList.add('motion-ready');</script>

        @viteReactRefresh
        @vite(['resources/js/app.jsx', "resources/js/Pages/{$page['component']}.jsx"])
        @inertiaHead
    </head>
    <body>
        @inertia
    </body>
</html>
