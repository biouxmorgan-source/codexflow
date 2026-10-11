<x-layouts.guest :title="__('Code cadeau')">
    @if ($problem)
        <p class="text-stone-700" role="alert">{{ $problem }}</p>
        <a href="{{ auth()->check() ? route('rewards') : url('/') }}" class="btn-secondary mt-6 w-full justify-center">{{ auth()->check() ? __('Mes récompenses') : __("Retour à l'accueil") }}</a>
    @else
        <p class="text-stone-700">{{ __(':name vous offre', ['name' => $gift->owner?->name ?? 'SagaWyn']) }}</p>
        <p class="mt-1 text-xl font-semibold">{{ trans_choice(':count mois de SagaWyn Premium|:count mois de SagaWyn Premium', $gift->months) }}</p>
        <p class="mt-1 font-mono text-sm text-stone-600">{{ $gift->code }}</p>

        <div class="mt-6">
            @guest
                <p class="mb-4 text-sm text-stone-600">{{ __('Pour l’utiliser, connectez-vous ou créez votre compte : vous reviendrez ensuite sur cette page.') }}</p>
                <div class="grid gap-3 sm:grid-cols-2">
                    <a href="{{ route('register') }}" class="btn-primary justify-center">{{ __('Créer un compte') }}</a>
                    <a href="{{ route('login') }}" class="btn-secondary justify-center">{{ __('Se connecter') }}</a>
                </div>
            @else
                <form method="POST" action="{{ route('gifts.redeem', $gift->code) }}">
                    @csrf
                    <button type="submit" class="btn-primary w-full justify-center">{{ __('Utiliser ce code') }}</button>
                </form>
                <p class="mt-3 text-center text-sm text-stone-500">{{ __('Connecté en tant que :name.', ['name' => auth()->user()->name]) }}</p>
                <p class="mt-1 text-center text-xs text-stone-500">{{ __('Les mois s’ajoutent à la suite de votre Premium en cours. La personne qui vous offre ce code verra votre nom.') }}</p>
            @endguest
        </div>
    @endif
</x-layouts.guest>
