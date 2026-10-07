@props(['title' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#1d2633">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @guest
            <meta name="codexflow-guest" content="1">
        @endguest
        @if (filled(config('webpush.vapid.public_key')))
            <meta name="vapid-public-key" content="{{ config('webpush.vapid.public_key') }}">
        @endif
        <link rel="manifest" href="/manifest.webmanifest">
        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">

        <title>{{ $title ? $title.' · ' : '' }}{{ config('app.name') }}</title>

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body {{ $attributes->merge(['class' => 'min-h-screen bg-parchment font-sans text-ink antialiased']) }}>
        <div x-data="{ online: navigator.onLine }" x-on:online.window="online = true" x-on:offline.window="online = false" x-show="! online" x-cloak role="status"
            class="sticky top-0 z-50 bg-flow px-4 py-2 text-center text-sm font-medium text-white">
            Hors ligne : vous consultez la dernière version enregistrée sur cet appareil.
        </div>
        {{ $slot }}
    </body>
</html>
