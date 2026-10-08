{{-- Pages d'erreur : même allure que l'application, dans la langue de la personne, sans rien révéler. --}}
@if (! request()->route())
    {{-- Adresse inconnue : aucun middleware n'a tourné, la langue vient du navigateur. --}}
    @php(app()->setLocale(\App\Support\Locale::resolve(request())))
@endif
@php([$title, $message] = [__($title), __($message)])
<x-layouts.base :title="$title">
    <main class="mx-auto flex min-h-screen max-w-md flex-col justify-center px-4 py-10">
        <a href="{{ url('/') }}" class="mb-8 text-center text-3xl font-semibold tracking-tight" translate="no">
            <span class="text-ink">Lore</span><span class="text-mundi">Mundi</span>
        </a>
        <div class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-medium text-stone-500">{{ __('Erreur :code', ['code' => $code]) }}</p>
            <h1 class="mt-1 text-xl font-semibold">{{ $title }}</h1>
            <p class="mt-3 text-stone-600">{{ $message }}</p>
            @if (filled($detail ?? null))
                <p class="mt-2 font-medium text-stone-700">{{ $detail }}</p>
            @endif
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ url('/') }}" class="btn-primary">{{ auth()->check() ? __('Mes campagnes') : __('Se connecter') }}</a>
                <a href="{{ url()->previous() }}" class="btn-secondary">{{ __('Revenir en arrière') }}</a>
            </div>
        </div>
    </main>
</x-layouts.base>
