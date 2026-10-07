<div>
    <nav class="mb-2 text-sm text-stone-500">
        <a href="{{ route('campaigns.index') }}" class="crumb" wire:navigate>{{ __('Mes campagnes') }}</a>
        › <a href="{{ route('campaigns.show', $campaign) }}" class="crumb" wire:navigate>{{ $campaign->name }}</a>
    </nav>

    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold">{{ __('Cartes') }}</h1>
            <p class="mt-1 max-w-2xl text-sm text-stone-600">{{ __("Une image envoyée sur l'écran de table, avec une grille et des jetons si besoin. Vos figurines restent sur la table : la carte est un support, pas une table virtuelle.") }}</p>
        </div>
        <a href="{{ route('table.remote', $campaign) }}" class="btn-secondary" wire:navigate>{{ __('Télécommande') }}</a>
    </div>

    <form wire:submit="create" class="mb-6 rounded-xl border border-stone-200 bg-white p-5 shadow-sm">
        <h2 class="font-semibold">{{ __('Nouvelle carte') }}</h2>
        @if ($this->images->isEmpty())
            <p class="mt-2 text-sm text-stone-600">
                {{ __('Ajoutez d’abord une image de carte dans les documents.') }}
                <a href="{{ route('documents.index', $campaign) }}" class="link" wire:navigate>{{ __('Documents →') }}</a>
            </p>
        @else
            <div class="mt-3 flex flex-wrap items-end gap-3">
                <div class="min-w-56 flex-1">
                    <label for="map-document" class="label">{{ __('Image') }}</label>
                    <select id="map-document" wire:model.live="documentId" class="field">
                        <option value="">{{ __('Choisir une image…') }}</option>
                        @foreach ($this->images as $image)
                            <option value="{{ $image->id }}">{{ $image->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="min-w-56 flex-1">
                    <label for="map-name" class="label">{{ __('Nom') }}</label>
                    <input id="map-name" type="text" wire:model="name" class="field" maxlength="255">
                </div>
                <button type="submit" class="btn-primary">{{ __('Créer la carte') }}</button>
            </div>
            @error('documentId') <p class="error">{{ $message }}</p> @enderror
            @error('name') <p class="error">{{ $message }}</p> @enderror
        @endif
    </form>

    @if ($maps->isEmpty())
        <p class="text-sm text-stone-500">{{ __('Aucune carte pour l’instant.') }}</p>
    @else
        <ul class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($maps as $map)
                <li wire:key="map-{{ $map->id }}" class="overflow-hidden rounded-xl border border-stone-200 bg-white shadow-sm">
                    <a href="{{ route('maps.show', [$campaign, $map]) }}" wire:navigate class="block aspect-video bg-stone-900">
                        <img src="{{ route('documents.file', $map->document) }}" alt="" class="h-full w-full object-cover" loading="lazy">
                    </a>
                    <div class="space-y-2 p-4">
                        <a href="{{ route('maps.show', [$campaign, $map]) }}" wire:navigate class="font-medium hover:text-codex">{{ $map->name }}</a>
                        <p class="text-xs text-stone-500">
                            {{ $map->grid_enabled ? __('Grille') : __('Sans grille') }}
                            · {{ trans_choice(':count jeton|:count jetons', $map->tokens_count) }}
                        </p>
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <livewire:table.show-button :campaign="$campaign" kind="map" :item-id="$map->id" compact :key="'show-map-'.$map->id" />
                            <button type="button" wire:click="delete({{ $map->id }})" wire:confirm="{{ __('Supprimer cette carte et ses jetons ? L’image reste dans les documents.') }}" class="text-xs text-stone-500 hover:text-red-700">{{ __('Supprimer') }}</button>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</div>
