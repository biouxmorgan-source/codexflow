@props(['title' => null])
{{-- Pages ouvertes aux visiteurs (accueil, aide) : en-tête avec connexion et inscription, pied de page avec les langues. --}}
@php($locales = \App\Support\Locale::available())
<x-layouts.base :title="$title">
    @isset($head)
        <x-slot:head>{{ $head }}</x-slot:head>
    @endisset
    <header class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 px-4 py-5">
        <a href="{{ url('/') }}" class="text-2xl font-semibold tracking-tight" translate="no">
            <span class="text-ink">Saga</span><span class="text-wyn">Wyn</span>
        </a>
        <nav class="flex flex-wrap items-center gap-2 text-sm">
            <a href="{{ route('help') }}" class="rounded-md px-3 py-2 text-stone-700 hover:bg-stone-100">{{ __('Aide') }}</a>
            <a href="{{ route('login') }}" class="rounded-md px-3 py-2 font-medium text-codex hover:bg-codex-soft">{{ __('Se connecter') }}</a>
            <a href="{{ route('register') }}" class="btn-primary">{{ __('Créer un compte') }}</a>
        </nav>
    </header>

    {{ $slot }}

    <footer class="border-t border-stone-200 py-8 text-sm text-stone-500">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 px-4">
            <p>SagaWyn · by Autistic Intelligence</p>
            <nav class="flex flex-wrap gap-x-4 gap-y-1">
                <a href="{{ route('help') }}" class="hover:text-ink hover:underline">{{ __('Aide') }}</a>
                <a href="{{ route('privacy') }}" class="hover:text-ink hover:underline">{{ __('Confidentialité et mentions légales') }}</a>
            </nav>
            <nav class="flex flex-wrap gap-x-3 gap-y-1 text-xs" aria-label="{{ __('Langue') }}">
                @foreach ($locales as $code => $name)
                    <a href="{{ request()->fullUrlWithQuery(['lang' => $code]) }}" lang="{{ $code }}" hreflang="{{ $code }}"
                        @class(['hover:text-ink hover:underline', 'font-semibold text-ink' => app()->getLocale() === $code])>{{ $name }}</a>
                @endforeach
            </nav>
        </div>
    </footer>
</x-layouts.base>
