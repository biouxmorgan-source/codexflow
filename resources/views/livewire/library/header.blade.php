{{-- En-tête commun des pages jeu et monde : image, nom, description mise en forme, modification sur place. --}}
<nav class="mb-2 text-sm text-stone-500">
    <a href="{{ route('campaigns.index') }}" class="crumb" wire:navigate>{{ __('Mes campagnes') }}</a> › {{ $kindLabel }}
</nav>
@if ($editing && $canEdit)
    <form wire:submit="save" class="mb-6 space-y-4 rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
        <div>
            <label for="library-name" class="label">{{ __('Nom') }}</label>
            <input id="library-name" type="text" wire:model="name" class="field">
            @error('name') <p class="error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="library-description" class="label">{{ __('Description') }}</label>
            <textarea id="library-description" wire:model="description" rows="6" class="field"></textarea>
            @error('description') <p class="error">{{ $message }}</p> @enderror
        </div>
        <div>
            <span class="label">{{ __('Image') }}</span>
            <div class="flex flex-wrap items-center gap-4">
                @if ($item->hasImage())
                    <img src="{{ route($imageRoute, $item) }}?v={{ $item->updated_at?->timestamp }}" alt="{{ __('Image actuelle de :name', ['name' => $title]) }}" class="h-24 w-36 rounded-lg object-cover">
                @endif
                <div class="space-y-2">
                    <input id="library-image" type="file" wire:model="image" accept="image/jpeg,image/png,image/webp,image/gif" aria-label="{{ __('Image') }}" class="block text-sm file:mr-3 file:rounded-md file:border-0 file:bg-codex-soft file:px-3 file:py-2 file:font-medium file:text-codex">
                    @if ($item->hasImage())
                        <button type="button" wire:click="removeImage" class="link text-sm">{{ __("Retirer l'image") }}</button>
                    @endif
                </div>
            </div>
            <div wire:loading wire:target="image" class="mt-1 text-sm text-stone-500">{{ __("Envoi de l'image…") }}</div>
            @error('image') <p class="error">{{ $message }}</p> @enderror
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn-primary">{{ __('Enregistrer') }}</button>
            <button type="button" wire:click="$set('editing', false)" class="btn-secondary">{{ __('Annuler') }}</button>
        </div>
    </form>
@else
    <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
        @if ($item->hasImage())
            <img src="{{ route($imageRoute, $item) }}?v={{ $item->updated_at?->timestamp }}" alt="" class="h-32 w-48 shrink-0 rounded-xl object-cover shadow-sm">
        @endif
        <div class="min-w-0 flex-1">
            <h1 class="text-2xl font-semibold">{{ $title }}</h1>
            @if ($text)
                <div class="mt-2 max-w-3xl text-stone-700">{{ \App\Support\EntityLinks::plain($text) }}</div>
            @else
                <p class="mt-1 text-sm text-stone-500">{{ __('Pas encore de description.') }}</p>
            @endif
        </div>
        @if ($canEdit)
            <button type="button" wire:click="$set('editing', true)" class="btn-secondary">{{ __('Modifier') }}</button>
        @else
            <span class="text-sm text-stone-500">{{ __('Lecture seule : géré par le propriétaire.') }}</span>
        @endif
    </div>
@endif

<section class="mb-6 rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
    <h2 class="mb-3 font-semibold">{{ __('Campagnes') }}</h2>
    @if ($this->campaigns->isEmpty())
        <p class="text-sm text-stone-500">{{ __('Aucune campagne pour l’instant.') }}</p>
    @else
        <ul class="divide-y divide-stone-100">
            @foreach ($this->campaigns as $item)
                <li wire:key="library-campaign-{{ $item->id }}" class="flex flex-wrap items-center justify-between gap-2 py-2">
                    <a href="{{ route('campaigns.show', $item) }}" class="link" wire:navigate>{{ $item->name }}</a>
                    <span class="text-xs text-stone-500">{{ $item->status->label() }}</span>
                </li>
            @endforeach
        </ul>
    @endif
</section>
