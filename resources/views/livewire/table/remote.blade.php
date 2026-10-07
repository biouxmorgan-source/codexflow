{{-- Sans Reverb, la télécommande se resynchronise toute seule toutes les 15 secondes. --}}
<div wire:poll.15s class="mx-auto max-w-lg space-y-5">
    <nav class="text-sm text-stone-500">
        <a href="{{ route('campaigns.show', $campaign) }}" class="crumb" wire:navigate>{{ $campaign->name }}</a>
        @if ($scene)
            › <a href="{{ route('sessions.live', $campaign) }}" class="crumb" wire:navigate>{{ __('Session') }}</a>
        @endif
    </nav>

    <section class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between gap-3">
            <h1 class="text-lg font-semibold">{{ __('Télécommande') }}</h1>
            <a href="{{ route('table.screen', $campaign) }}" target="codexflow-table" class="link text-sm">{{ __('Écran ↗') }}</a>
        </div>
        <p class="mt-2 text-sm"><span class="text-stone-500">{{ __('Affiché :') }}</span> <span class="font-medium">{{ $label }}</span></p>
        <div class="mt-3 grid grid-cols-2 gap-2">
            <button type="button" wire:click="clear" @disabled(! $shown) class="btn-secondary justify-center py-3 disabled:opacity-40">{{ __("Vider l'écran") }}</button>
            <button type="button" wire:click="next" @disabled($this->sceneItems->isEmpty()) class="btn-primary justify-center py-3 disabled:opacity-40">{{ __('Suivant ▶') }}</button>
        </div>
        <label class="mt-3 flex items-center gap-2 text-sm text-stone-600">
            <input type="checkbox" wire:click="toggleShare" @checked($campaign->table_shared)>
            {{ __('Les joueurs suivent l’écran sur leur appareil') }}
        </label>
        <div class="mt-3">
            <label for="table-theme" class="label">{{ __('Ambiance de l’écran') }}</label>
            <select id="table-theme" wire:change="setTheme($event.target.value)" class="field py-1.5 text-sm">
                @foreach (\App\Support\TableTheme::labels() as $value => $label)
                    <option value="{{ $value }}" @selected($campaign->table_theme === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </section>

    @if ($map)
        <section class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
            <h2 class="mb-2 text-sm font-semibold tracking-wide text-stone-500 uppercase">{{ __('Carte : :name', ['name' => $map->name]) }}</h2>
            <div class="aspect-video overflow-hidden rounded-lg bg-black">
                <x-table-map :map="$map" :tokens="$map->tokens" gm
                    :image-url="route('documents.file', $map->document)"
                    :token-url="fn ($token) => route('entities.image', [$token->entity, 'v' => $token->entity->updated_at?->timestamp])" />
            </div>
            <div class="mt-3 grid grid-cols-3 gap-2 text-lg" role="group" aria-label="{{ __('Déplacer la vue') }}">
                <button type="button" wire:click="zoom(false)" class="btn-secondary justify-center py-2" aria-label="{{ __('Dézoomer') }}">−</button>
                <button type="button" wire:click="pan(0, -1)" class="btn-secondary justify-center py-2" aria-label="{{ __('Haut') }}">↑</button>
                <button type="button" wire:click="zoom(true)" class="btn-secondary justify-center py-2" aria-label="{{ __('Zoomer') }}">+</button>
                <button type="button" wire:click="pan(-1, 0)" class="btn-secondary justify-center py-2" aria-label="{{ __('Gauche') }}">←</button>
                <button type="button" wire:click="fit" class="btn-secondary justify-center py-2 text-sm">{{ __('Entière') }}</button>
                <button type="button" wire:click="pan(1, 0)" class="btn-secondary justify-center py-2" aria-label="{{ __('Droite') }}">→</button>
                <span></span>
                <button type="button" wire:click="pan(0, 1)" class="btn-secondary justify-center py-2" aria-label="{{ __('Bas') }}">↓</button>
                <span></span>
            </div>
            @if ($map->ruler)
                <button type="button" wire:click="clearRuler" class="btn-secondary mt-2 w-full justify-center">{{ __('Effacer la règle') }} ({{ $map->rulerLabel() }})</button>
            @endif
            @if ($map->tokens->isNotEmpty())
                <ul class="mt-3 flex flex-wrap gap-2">
                    @foreach ($map->tokens as $token)
                        <li wire:key="remote-token-{{ $token->id }}">
                            <button type="button" wire:click="toggleToken({{ $token->id }})" @class([
                                'inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-sm',
                                'border-stone-200 text-stone-400 line-through' => $token->hidden,
                                'border-flow bg-flow/10 font-medium' => ! $token->hidden,
                            ]) aria-pressed="{{ $token->hidden ? 'false' : 'true' }}">
                                <span class="size-3 rounded-full" style="background: {{ $token->color }}"></span>{{ $token->label }}
                            </button>
                        </li>
                    @endforeach
                </ul>
                <p class="mt-1 text-xs text-stone-500">{{ __('Touchez un jeton pour le montrer ou le masquer.') }}</p>
            @endif
            <a href="{{ route('maps.show', [$campaign, $map]) }}" class="link mt-3 inline-block text-sm" wire:navigate>{{ __('Préparer la carte →') }}</a>
        </section>
    @endif

    <section class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
        <h2 class="mb-2 text-sm font-semibold tracking-wide text-stone-500 uppercase">
            {{ $scene ? __('Scène : :name', ['name' => $scene->name]) : __('Scène en cours') }}
        </h2>
        @if ($this->sceneItems->isEmpty())
            <p class="text-sm text-stone-500">{{ $scene ? __('Rien de relié à cette scène.') : __('Aucune session ouverte : lancez-la depuis le mode Session.') }}</p>
        @else
            <ul class="space-y-2">
                @foreach ($this->sceneItems as $item)
                    @php($on = \App\Support\TableDisplay::isShowing($campaign, $item['kind'], $item['id']))
                    <li wire:key="item-{{ $item['kind'] }}-{{ $item['id'] }}">
                        <button type="button" wire:click="show('{{ $item['kind'] }}', {{ $item['id'] }})" @class([
                            'flex w-full items-center justify-between gap-3 rounded-xl border px-4 py-3 text-left',
                            'border-flow bg-flow/10' => $on,
                            'border-stone-200 hover:border-codex' => ! $on,
                        ])>
                            <span class="min-w-0 truncate font-medium">{{ $item['label'] }}</span>
                            <span class="shrink-0 text-xs text-stone-500">{{ $on ? __('À la table') : $item['type'] }}</span>
                        </button>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    @if ($maps->isNotEmpty())
        <section class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
            <h2 class="mb-2 text-sm font-semibold tracking-wide text-stone-500 uppercase">{{ __('Cartes') }}</h2>
            <ul class="grid grid-cols-2 gap-2">
                @foreach ($maps as $item)
                    @php($on = \App\Support\TableDisplay::isShowing($campaign, 'map', $item->id))
                    <li wire:key="remote-map-{{ $item->id }}">
                        <button type="button" wire:click="show('map', {{ $item->id }})" @class([
                            'w-full truncate rounded-xl border px-3 py-3 text-left text-sm font-medium',
                            'border-flow bg-flow/10' => $on,
                            'border-stone-200 hover:border-codex' => ! $on,
                        ])>{{ $item->name }}</button>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    <form wire:submit="announce" class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
        <label for="remote-text" class="mb-2 block text-sm font-semibold tracking-wide text-stone-500 uppercase">{{ __('Annonce') }}</label>
        <div class="flex gap-2">
            <input id="remote-text" type="text" wire:model="text" class="field min-w-0" maxlength="500" placeholder="{{ __('Annonce : « Trois jours plus tard… »') }}" autocomplete="off">
            <button type="submit" class="btn-primary shrink-0">{{ __('Afficher') }}</button>
        </div>
        @error('text') <p class="error">{{ $message }}</p> @enderror
    </form>
</div>
