<div>
    <nav class="mb-2 text-sm text-stone-500">
        <a href="{{ route('campaigns.index') }}" class="crumb" wire:navigate>{{ __('Mes campagnes') }}</a>
        › <a href="{{ route('campaigns.show', $campaign) }}" class="crumb" wire:navigate>{{ $campaign->name }}</a>
        › <a href="{{ route('maps.index', $campaign) }}" class="crumb" wire:navigate>{{ __('Cartes') }}</a>
    </nav>

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-semibold">{{ $map->name }}</h1>
        <div class="flex flex-wrap items-center gap-2">
            <livewire:table.show-button :campaign="$campaign" kind="map" :item-id="$map->id" :key="'show-map-'.$map->id" />
            @if (\App\Support\CampaignFeatures::usable($campaign, 'table'))
            <a href="{{ route('table.remote', $campaign) }}" class="btn-secondary" wire:navigate>{{ __('Télécommande') }}</a>
        @endif
        </div>
    </div>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
        {{-- La carte, cadrée comme sur l'écran de table. --}}
        <section x-data="mapEditor" class="min-w-0"
            data-scale="{{ $map->scale_value }}" data-unit="{{ $map->scale_unit }}"
            data-case="{{ __('case') }}" data-cases="{{ __('cases') }}">
            <div class="mb-2 flex flex-wrap items-center gap-2 text-sm">
                <div class="inline-flex overflow-hidden rounded-lg border border-stone-200" role="group" aria-label="{{ __('Outil') }}">
                    <button type="button" x-on:click="mode = 'move'" :class="mode === 'move' ? 'bg-codex text-white' : 'bg-white text-stone-700'" class="px-3 py-1.5" :aria-pressed="mode === 'move'">{{ __('Déplacer') }}</button>
                    <button type="button" x-on:click="mode = 'ruler'" :class="mode === 'ruler' ? 'bg-codex text-white' : 'bg-white text-stone-700'" class="border-l border-stone-200 px-3 py-1.5" :aria-pressed="mode === 'ruler'">{{ __('Mesurer') }}</button>
                </div>
                <button type="button" x-on:click="zoomBy(1.25)" class="btn-secondary px-3 py-1.5" aria-label="{{ __('Zoomer') }}">+</button>
                <button type="button" x-on:click="zoomBy(0.8)" class="btn-secondary px-3 py-1.5" aria-label="{{ __('Dézoomer') }}">−</button>
                <button type="button" wire:click="resetView" class="btn-secondary px-3 py-1.5">{{ __('Carte entière') }}</button>
                @if ($map->activeRuler())
                    <button type="button" wire:click="clearRuler" class="btn-secondary px-3 py-1.5">{{ __('Effacer la règle') }} ({{ $map->rulerLabel() }})</button>
                @endif
            </div>

            <div x-ref="stage" class="relative aspect-video w-full touch-none overflow-hidden rounded-xl bg-black"
                :class="mode === 'ruler' ? 'cursor-crosshair' : 'cursor-move'"
                x-on:pointerdown="down($event)" x-on:pointermove="move($event)" x-on:pointerup="up($event)" x-on:pointercancel="up($event)"
                x-on:wheel.prevent="wheel($event)">
                <x-table-map :map="$map" :tokens="$tokens" gm
                    :image-url="route('documents.file', $map->document)"
                    :token-url="fn ($token) => route('entities.image', [$token->entity, 'v' => $token->entity->updated_at?->timestamp])" />
                {{-- Règle en cours de tracé, avant l'envoi. --}}
                <svg wire:ignore x-ref="overlay" class="pointer-events-none absolute inset-0 h-full w-full" preserveAspectRatio="xMidYMid meet">
                    <g x-show="ruler" x-cloak>
                        <line x-ref="rulerLine" stroke="#facc15" stroke-linecap="round" />
                        <text x-ref="rulerText" text-anchor="middle" fill="#facc15" stroke="#000" paint-order="stroke" stroke-linejoin="round" font-weight="700" font-family="system-ui, sans-serif"></text>
                    </g>
                </svg>
            </div>
            <p class="mt-2 text-xs text-stone-500">{{ __('Molette pour zoomer, glisser pour déplacer la carte ou un jeton. Ce cadrage est celui de l’écran de table. Les jetons en pointillés sont masqués aux joueurs.') }}</p>
        </section>

        <aside class="space-y-6">
            <section class="rounded-xl border border-stone-200 bg-white p-4 shadow-sm">
                <h2 class="mb-3 text-sm font-semibold tracking-wide text-stone-500 uppercase">{{ __('Jetons') }}</h2>

                <form wire:submit="addToken" class="space-y-2">
                    <label for="token-entity" class="label">{{ __('Fiche liée') }} <span class="font-normal text-stone-500">{{ __('(nom et portrait)') }}</span></label>
                    <div wire:key="token-picker-{{ $tokens->count() }}"><x-entity-picker id="token-entity" model="tokenEntityId" /></div>
                    <label for="token-label" class="label">{{ __('Ou un nom') }}</label>
                    <div class="flex gap-2">
                        <input id="token-label" type="text" wire:model="tokenLabel" class="field min-w-0 py-1.5 text-sm" maxlength="100" placeholder="{{ __('Gobelin 1, coffre…') }}">
                        <button type="submit" class="btn-primary shrink-0 py-1.5 text-sm">{{ __('Ajouter') }}</button>
                    </div>
                    @error('tokenLabel') <p class="error">{{ $message }}</p> @enderror
                    <p class="text-xs text-stone-500">{{ __('Un nouveau jeton arrive masqué, au centre de la vue.') }}</p>
                </form>

                @if ($tokens->isNotEmpty())
                    <div class="mt-4 flex gap-3 text-xs">
                        <button type="button" wire:click="setAllHidden(false)" class="link">{{ __('Tout montrer') }}</button>
                        <button type="button" wire:click="setAllHidden(true)" class="link">{{ __('Tout masquer') }}</button>
                    </div>
                    <ul class="mt-2 divide-y divide-stone-100">
                        @foreach ($tokens as $token)
                            <li wire:key="row-{{ $token->id }}" class="flex items-center gap-2 py-2 text-sm">
                                <button type="button" wire:click="recolorToken({{ $token->id }})" class="size-5 shrink-0 rounded-full border-2 border-white shadow ring-1 ring-stone-300" style="background: {{ $token->color }}" title="{{ __('Changer la couleur') }}" aria-label="{{ __('Changer la couleur de :name', ['name' => $token->label]) }}"></button>
                                <span @class(['min-w-0 flex-1 truncate', 'text-stone-400' => $token->hidden])>{{ $token->label }}</span>
                                <select wire:change="resizeToken({{ $token->id }}, $event.target.value)" class="rounded border-stone-200 py-0.5 pr-6 pl-1 text-xs" aria-label="{{ __('Taille de :name', ['name' => $token->label]) }}">
                                    @foreach (\App\Models\MapToken::SIZES as $size)
                                        <option value="{{ $size }}" @selected($token->size == $size)>{{ $size == 0.5 ? '½' : $size }}</option>
                                    @endforeach
                                </select>
                                <button type="button" wire:click="toggleLabel({{ $token->id }})" @class(['text-xs', 'text-codex' => $token->show_label, 'text-stone-400 line-through' => ! $token->show_label]) title="{{ __('Nom affiché sous le jeton') }}">{{ __('Nom') }}</button>
                                <button type="button" wire:click="toggleToken({{ $token->id }})" @class(['w-16 rounded px-1.5 py-0.5 text-xs', 'bg-stone-100 text-stone-600' => $token->hidden, 'bg-flow/15 text-flow' => ! $token->hidden])>
                                    {{ $token->hidden ? __('Masqué') : __('Visible') }}
                                </button>
                                <button type="button" wire:click="deleteToken({{ $token->id }})" wire:confirm="{{ __('Supprimer ce jeton ?') }}" class="text-stone-400 hover:text-red-700" aria-label="{{ __('Supprimer :name', ['name' => $token->label]) }}">×</button>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <section class="space-y-3 rounded-xl border border-stone-200 bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold tracking-wide text-stone-500 uppercase">{{ __('Grille') }}</h2>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" wire:model.live="gridEnabled">
                    {{ __('Afficher une grille carrée') }}
                </label>
                <div>
                    <label for="grid-size" class="label">{{ __('Taille des cases') }} <span class="font-normal text-stone-500">{{ __('(pixels de l’image)') }}</span></label>
                    <div class="flex items-center gap-2">
                        <input type="range" min="10" max="{{ max(400, (int) $gridSize) }}" step="0.5" wire:model.live.debounce.150ms="gridSize" class="min-w-0 flex-1" aria-label="{{ __('Taille des cases') }}">
                        <input id="grid-size" type="number" min="5" max="2000" step="0.5" wire:model.live.debounce.400ms="gridSize" class="field w-20 py-1 text-sm">
                    </div>
                    @error('gridSize') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="grid-x" class="label">{{ __('Décalage →') }}</label>
                        <input id="grid-x" type="range" min="0" max="{{ (float) $gridSize ?: 100 }}" step="0.5" wire:model.live.debounce.150ms="gridOffsetX" class="w-full">
                    </div>
                    <div>
                        <label for="grid-y" class="label">{{ __('Décalage ↓') }}</label>
                        <input id="grid-y" type="range" min="0" max="{{ (float) $gridSize ?: 100 }}" step="0.5" wire:model.live.debounce.150ms="gridOffsetY" class="w-full">
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <label for="grid-color" class="label mb-0">{{ __('Couleur') }}</label>
                    <input id="grid-color" type="color" wire:model.live.debounce.300ms="gridColor" class="h-8 w-12 rounded border border-stone-200">
                </div>
                <div>
                    <label for="scale-value" class="label">{{ __('Échelle : 1 case =') }}</label>
                    <div class="flex gap-2">
                        <input id="scale-value" type="number" min="0" step="0.1" wire:model.live.debounce.500ms="scaleValue" class="field w-24 py-1 text-sm" placeholder="1,5">
                        <input type="text" wire:model.live.debounce.500ms="scaleUnit" class="field w-24 py-1 text-sm" maxlength="20" placeholder="m" aria-label="{{ __('unité') }}">
                    </div>
                    @error('scaleValue') <p class="error">{{ $message }}</p> @enderror
                    <p class="mt-1 text-xs text-stone-500">{{ __('Facultatif : la règle affiche alors la distance. La grille n’impose aucun déplacement.') }}</p>
                </div>
            </section>

            <section class="rounded-xl border border-stone-200 bg-white p-4 shadow-sm">
                <label for="map-name" class="label">{{ __('Nom de la carte') }}</label>
                <input id="map-name" type="text" wire:model.live.debounce.600ms="name" class="field py-1.5 text-sm" maxlength="255">
                @error('name') <p class="error">{{ $message }}</p> @enderror
            </section>
        </aside>
    </div>
</div>
