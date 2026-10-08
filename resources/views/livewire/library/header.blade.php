{{-- En-tête commun des pages jeu et monde : nom, description mise en forme, modification sur place. --}}
<nav class="mb-2 text-sm text-stone-500">
    <a href="{{ route('campaigns.index') }}" class="crumb" wire:navigate>{{ __('Mes campagnes') }}</a> › {{ $kindLabel }}
</nav>
@if ($editing)
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
        <div class="flex gap-2">
            <button type="submit" class="btn-primary">{{ __('Enregistrer') }}</button>
            <button type="button" wire:click="$set('editing', false)" class="btn-secondary">{{ __('Annuler') }}</button>
        </div>
    </form>
@else
    <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
        <div class="min-w-0">
            <h1 class="text-2xl font-semibold">{{ $title }}</h1>
            @if ($text)
                <div class="mt-2 max-w-3xl text-stone-700">{{ \App\Support\EntityLinks::plain($text) }}</div>
            @else
                <p class="mt-1 text-sm text-stone-500">{{ __('Pas encore de description.') }}</p>
            @endif
        </div>
        <button type="button" wire:click="$set('editing', true)" class="btn-secondary">{{ __('Modifier') }}</button>
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
