@props(['title' => null, 'plain' => false])
<!DOCTYPE html>
{{-- Préférences d'affichage (thème, accent, taille) ; l'écran de table garde son affichage propre. --}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    @unless ($plain) @foreach (\App\Support\Appearance::attributes(auth()->user()) as $name => $value) {{ $name }}="{{ $value }}" @endforeach @endunless>
    <head>
        <script>
            // Thème résolu avant l'affichage, pour éviter un flash clair en mode sombre.
            (() => {
                const root = document.documentElement;
                const media = window.matchMedia('(prefers-color-scheme: dark)');
                const apply = () => {
                    const choice = root.dataset.themeChoice;
                    const theme = choice === 'dark' || (choice === 'system' && media.matches) ? 'dark' : 'light';
                    if (root.dataset.theme !== theme) root.dataset.theme = theme;
                };
                apply();
                media.addEventListener('change', apply);
                // wire:navigate remplace les attributs de <html> par ceux de la page reçue,
                // qui ne connaît pas le thème du système : on le remet aussitôt.
                new MutationObserver(apply)
                    .observe(root, { attributes: true, attributeFilter: ['data-theme', 'data-theme-choice'] });
            })();
        </script>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#1d2633">
        <meta name="color-scheme" content="light dark">
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
        {{-- Balises propres à une page (référencement de la page d'accueil publique). --}}
        {{ $head ?? '' }}
        @php($scriptText = [
            'pushSubscribeFailed' => __("L'abonnement n'a pas abouti. Réessayez, ou vérifiez les réglages de notification du navigateur."),
            'pushDisableFailed' => __('La désactivation a échoué. Réessayez.'),
        ])
        <script>
            // Textes affichés par les scripts (resources/js), dans la langue de la page.
            window.codexflowText = {{ Js::from($scriptText) }};
        </script>

        <title>{{ $title ? $title.' · ' : '' }}{{ config('app.name') }}</title>

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body {{ $attributes->merge(['class' => 'min-h-screen bg-parchment font-sans text-ink antialiased']) }}>
        <div x-data="{ online: navigator.onLine && ! document.querySelector('meta[name=codexflow-offline]') }" x-on:online.window="online = ! document.querySelector('meta[name=codexflow-offline]')" x-on:offline.window="online = false" x-show="! online" x-cloak role="status"
            class="sticky top-0 z-50 bg-flow px-4 py-2 text-center text-sm font-medium text-on-accent">
            {{ __('Hors ligne : vous consultez la dernière version enregistrée sur cet appareil.') }}
        </div>
        {{ $slot }}
    </body>
</html>
