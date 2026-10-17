<section class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
    <div class="flex items-center justify-between gap-3">
        <h2 class="text-sm font-semibold tracking-wide text-stone-500 uppercase">{{ __('Initiative') }}</h2>
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" wire:click="toggleShown" @checked($state['shown'])>
            {{ __('Montrer à la table') }}
        </label>
    </div>

    @if ($state['entries'] !== [])
        <div class="mt-3 flex items-center gap-2">
            <button type="button" wire:click="step(-1)" class="btn-secondary px-3 py-2" aria-label="{{ __('Tour précédent') }}">◀</button>
            <button type="button" wire:click="step(1)" class="btn-primary flex-1 justify-center py-2">{{ $state['turn'] === null ? __('Commencer') : __('Tour suivant ▶') }}</button>
            <span class="shrink-0 text-sm text-stone-500">{{ __('Round :round', ['round' => $state['round']]) }}</span>
        </div>

        <ul class="mt-3 divide-y divide-stone-100">
            @foreach ($state['entries'] as $entry)
                <li wire:key="init-{{ $entry['id'] }}" @class(['flex items-center gap-2 py-1.5 text-sm', 'bg-flow/10 -mx-2 px-2 rounded' => $state['turn'] === $entry['id']])>
                    <span class="size-3 shrink-0 rounded-full" style="background: {{ $entry['color'] }}"></span>
                    @if ($entry['ordered'])
                        <input type="text" inputmode="decimal" value="{{ $entry['value'] === null ? '' : rtrim(rtrim(number_format($entry['value'], 2, ',', ''), '0'), ',') }}"
                            wire:change="setValue({{ $entry['id'] }}, $event.target.value)" class="field w-14 px-1.5 py-0.5 text-center text-sm" maxlength="8"
                            aria-label="{{ __('Initiative de :name', ['name' => $entry['label']]) }}" placeholder="–">
                    @endif
                    <span @class(['min-w-0 flex-1 truncate', 'text-stone-400' => ! $entry['visible'], 'italic' => ! $entry['ordered']])>{{ $entry['label'] }}</span>
                    <button type="button" wire:click="toggleOrdered({{ $entry['id'] }})" class="text-xs text-stone-500 hover:text-codex" title="{{ __('Avec ou sans initiative') }}">{{ $entry['ordered'] ? __('Rangé') : __('Hors ordre') }}</button>
                    <button type="button" wire:click="toggleVisible({{ $entry['id'] }})" @class(['w-16 rounded px-1.5 py-0.5 text-xs', 'bg-stone-100 text-stone-600' => ! $entry['visible'], 'bg-flow/15 text-flow' => $entry['visible']])>
                        {{ $entry['visible'] ? __('Visible') : __('Masqué') }}
                    </button>
                    <button type="button" wire:click="shift({{ $entry['id'] }}, -1)" class="text-stone-400 hover:text-codex" aria-label="{{ __('Monter :name', ['name' => $entry['label']]) }}">↑</button>
                    <button type="button" wire:click="shift({{ $entry['id'] }}, 1)" class="text-stone-400 hover:text-codex" aria-label="{{ __('Descendre :name', ['name' => $entry['label']]) }}">↓</button>
                    <button type="button" wire:click="remove({{ $entry['id'] }})" class="text-stone-400 hover:text-red-700" aria-label="{{ __('Retirer :name', ['name' => $entry['label']]) }}">×</button>
                </li>
            @endforeach
        </ul>
        <div class="mt-2 flex flex-wrap gap-3 text-xs">
            <button type="button" wire:click="sort" class="link">{{ __('Ranger par initiative') }}</button>
            <button type="button" wire:click="resetAll" wire:confirm="{{ __('Vider la liste et revenir au round 1 ?') }}" class="link">{{ __('Nouveau combat') }}</button>
        </div>
    @endif

    <form wire:submit="add" class="mt-4 space-y-2 border-t border-stone-100 pt-3">
        <div wire:key="init-picker-{{ count($state['entries']) }}"><x-entity-picker id="init-entity" model="entityId" /></div>
        <div class="flex gap-2">
            <input type="text" wire:model="label" class="field min-w-0 py-1.5 text-sm" maxlength="100" placeholder="{{ __('Ou un nom : Gobelin 1, coffre…') }}" aria-label="{{ __('Nom') }}">
            <button type="submit" class="btn-primary shrink-0 py-1.5 text-sm">{{ __('Ajouter') }}</button>
        </div>
        @error('label') <p class="error">{{ $message }}</p> @enderror
        <div class="flex flex-wrap gap-3 text-xs">
            <button type="button" wire:click="add(false)" class="link">{{ __('Ajouter hors ordre (PNJ, objet…)') }}</button>
            <button type="button" wire:click="addCharacters" class="link">{{ __('Ajouter les personnages des joueurs') }}</button>
            @if ($hasMap)
                <button type="button" wire:click="addTokens" class="link">{{ __('Ajouter les jetons de la carte') }}</button>
            @endif
        </div>
        <p class="text-xs text-stone-500">{{ __('Les personnages des joueurs arrivent visibles, les autres masqués : montrez-les quand vous le souhaitez.') }}</p>
    </form>
</section>
