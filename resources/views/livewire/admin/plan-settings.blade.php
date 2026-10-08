<div class="max-w-3xl">
    <h1 class="text-2xl font-semibold">{{ __('Administration') }}</h1>
    <p class="mt-1 mb-4 text-sm text-stone-600">{{ __('Ce que comprend chaque formule. Un stockage propre à un compte se règle dans « Comptes ».') }}</p>
    <x-admin-nav />

    @if (session('status'))
        <p class="mb-4 rounded-lg bg-green-50 px-4 py-2 text-sm text-green-800" role="status">{{ session('status') }}</p>
    @endif

    <form wire:submit="save" class="space-y-6">
        <section class="rounded-xl border border-stone-200 bg-white p-5 shadow-sm">
            <h2 class="font-semibold">{{ __('Administrateur') }}</h2>
            <p class="mt-1 text-sm text-stone-600">{{ __('Toutes les fonctions, sans limite de campagnes ni de stockage.') }}</p>
        </section>

        <section class="rounded-xl border border-stone-200 bg-white p-5 shadow-sm">
            <h2 class="font-semibold">{{ __('Premium') }}</h2>
            <p class="mt-1 mb-3 text-sm text-stone-600">{{ __('Toutes les fonctions et autant de campagnes que voulu.') }}</p>
            <label for="premium-storage" class="label">{{ __('Stockage (Mo)') }}</label>
            <input id="premium-storage" type="number" min="0" wire:model="premiumStorageMb" class="field max-w-40">
            @error('premiumStorageMb') <p class="error">{{ $message }}</p> @enderror
        </section>

        <section class="rounded-xl border border-stone-200 bg-white p-5 shadow-sm">
            <h2 class="font-semibold">{{ __('Gratuit') }}</h2>
            <p class="mt-1 mb-3 text-sm text-stone-600">{{ __('La formule de tout nouveau compte, y compris les joueurs invités. Jouer, être co-MJ ou spectateur ne compte jamais dans la limite de campagnes.') }}</p>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="free-storage" class="label">{{ __('Stockage (Mo)') }}</label>
                    <input id="free-storage" type="number" min="0" wire:model="freeStorageMb" class="field">
                    @error('freeStorageMb') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="free-campaigns" class="label">{{ __('Campagnes en tant que MJ') }}</label>
                    <input id="free-campaigns" type="number" min="0" wire:model="freeMaxCampaigns" class="field">
                    @error('freeMaxCampaigns') <p class="error">{{ $message }}</p> @enderror
                </div>
            </div>
            <fieldset class="mt-4">
                <legend class="label">{{ __('Fonctions comprises') }}</legend>
                <div class="space-y-2">
                    @foreach ($features as $key => $label)
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" value="{{ $key }}" wire:model="freeFeatures"> {{ $label }}
                        </label>
                    @endforeach
                </div>
                <p class="mt-2 text-xs text-stone-500">{{ __('Une fonction décochée disparaît des campagnes dont le MJ a la formule gratuite, pour lui comme pour ses joueurs.') }}</p>
            </fieldset>
        </section>

        <button type="submit" class="btn-primary">{{ __('Enregistrer') }}</button>
    </form>
</div>
