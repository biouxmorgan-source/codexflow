<div>
    <h1 class="text-2xl font-semibold">{{ __('Administration') }}</h1>
    <p class="mt-1 mb-4 text-sm text-stone-600">{{ __('Des campagnes prêtes à jouer que tout MJ charge en un clic, avec un lien vers la boutique du jeu. Préparez la campagne dans votre compte (création, import, outils d’IA), puis ajoutez-la ici : son contenu est figé au moment de l’ajout, sans membres ni personnages joueurs.') }}</p>
    <x-admin-nav />

    @if (session('status'))
        <p class="mb-4 rounded-lg bg-green-50 px-4 py-2 text-sm text-green-800" role="status">{{ session('status') }}</p>
    @endif

    @if ($editing)
        <form wire:submit="save" class="mb-6 grid gap-3 rounded-xl border border-stone-200 bg-white p-4 shadow-sm sm:grid-cols-2">
            <h2 class="font-semibold sm:col-span-2">{{ $editingId ? __('Modifier la démo') : __('Nouvelle démo') }}</h2>
            <div class="sm:col-span-2">
                <label for="demo-campaign" class="label">{{ __('Campagne d’origine') }}</label>
                <select id="demo-campaign" wire:model="campaignId" class="field">
                    <option value="">{{ $editingId ? __('Garder le contenu actuel') : __('Choisir une de vos campagnes') }}</option>
                    @foreach ($this->campaigns as $campaign)
                        <option value="{{ $campaign->id }}">{{ $campaign->name }}</option>
                    @endforeach
                </select>
                @error('campaignId') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="demo-title" class="label">{{ __('Titre') }}</label>
                <input id="demo-title" type="text" wire:model="title" class="field">
                @error('title') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="demo-game" class="label">{{ __('Jeu') }}</label>
                <input id="demo-game" type="text" wire:model="gameName" class="field">
                @error('gameName') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="demo-publisher" class="label">{{ __('Auteur ou éditeur') }}</label>
                <input id="demo-publisher" type="text" wire:model="publisher" class="field">
                @error('publisher') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="demo-locale" class="label">{{ __('Langue') }}</label>
                <select id="demo-locale" wire:model="locale" class="field">
                    @foreach (\App\Support\Locale::available() as $code => $name)
                        <option value="{{ $code }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
                <label for="demo-store" class="label">{{ __('Lien vers la boutique') }}</label>
                <input id="demo-store" type="url" wire:model="storeUrl" class="field" placeholder="https://">
                @error('storeUrl') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-2">
                <label for="demo-summary" class="label">{{ __('Présentation') }}</label>
                <textarea id="demo-summary" wire:model="summary" rows="3" class="field"></textarea>
                @error('summary') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div class="flex gap-2 sm:col-span-2">
                <button type="submit" class="btn-primary" wire:loading.attr="disabled" wire:target="save">{{ __('Enregistrer') }}</button>
                <button type="button" wire:click="$set('editing', false)" class="btn-secondary">{{ __('Annuler') }}</button>
            </div>
        </form>
    @else
        <button type="button" wire:click="create" class="btn-secondary mb-4">{{ __('+ Ajouter une démo') }}</button>
    @endif

    <ul class="space-y-3">
        @forelse ($this->items as $item)
            <li wire:key="demo-item-{{ $item->id }}" @class(['rounded-xl border border-stone-200 bg-white p-4 shadow-sm', 'opacity-70' => ! $item->published])>
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <p class="flex flex-wrap items-center gap-2 text-xs">
                            <span @class(['rounded-full px-2 py-0.5 font-medium', 'bg-green-50 text-green-800' => $item->published, 'bg-stone-100 text-stone-600' => ! $item->published])>{{ $item->published ? __('Publiée') : __('Cachée') }}</span>
                            <span class="text-stone-500">{{ \App\Support\Locale::available()[$item->locale] ?? $item->locale }} · {{ \App\Support\Plans\StorageUsage::format((int) $item->archive_size) }} · {{ trans_choice(':count chargement|:count chargements', $item->loads_count) }}</span>
                        </p>
                        <p class="mt-1 font-medium">{{ $item->title }} <span class="font-normal text-stone-600">· {{ $item->game_name }}@if ($item->publisher), {{ $item->publisher }}@endif</span></p>
                        <p class="text-xs text-stone-500">
                            @if ($item->sourceCampaign)
                                {{ __('Contenu figé le :date depuis « :campaign ».', ['date' => $item->updated_at->isoFormat('LL'), 'campaign' => $item->sourceCampaign->name]) }}
                            @else
                                {{ __('La campagne d’origine a été supprimée : le contenu figé reste disponible.') }}
                            @endif
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" wire:click="togglePublished({{ $item->id }})" class="btn-secondary py-1 text-sm">{{ $item->published ? __('Cacher') : __('Publier') }}</button>
                        @if ($item->sourceCampaign && $item->sourceCampaign->user_id === auth()->id())
                            <button type="button" wire:click="refresh({{ $item->id }})" wire:confirm="{{ __('Remplacer le contenu de la démo par celui de la campagne aujourd’hui ? Les copies déjà chargées ne changent pas.') }}" class="btn-secondary py-1 text-sm">{{ __('Mettre à jour le contenu') }}</button>
                        @endif
                        <button type="button" wire:click="edit({{ $item->id }})" class="btn-secondary py-1 text-sm">{{ __('Modifier') }}</button>
                        <button type="button" wire:click="delete({{ $item->id }})" wire:confirm="{{ __('Retirer cette démo de la bibliothèque ? Les copies déjà chargées restent à leurs MJ.') }}" class="btn-secondary py-1 text-sm text-red-700">{{ __('Supprimer') }}</button>
                    </div>
                </div>
            </li>
        @empty
            <li class="rounded-xl border border-dashed border-stone-300 p-6 text-center text-stone-500">{{ __('Aucune démo dans la bibliothèque. La démonstration de SagaWyn y figure toujours.') }}</li>
        @endforelse
    </ul>
</div>
