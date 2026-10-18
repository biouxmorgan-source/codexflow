<div>
    <nav class="mb-2 text-sm text-stone-500">
        <a href="{{ route('campaigns.index') }}" class="crumb" wire:navigate>{{ __('Mes campagnes') }}</a>
        › <a href="{{ route('campaigns.show', $campaign) }}" class="crumb" wire:navigate>{{ $campaign->name }}</a>
        › <a href="{{ route('maps.index', $campaign) }}" class="crumb" wire:navigate>{{ __('Cartes') }}</a>
    </nav>

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <h1 class="text-2xl font-semibold">{{ $map->name }}</h1>
            <x-page-help topic="maps" />
        </div>
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
                <x-table-map :map="$map" :tokens="$tokens" :templates="$templates" gm
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
            <p class="mt-2 text-xs text-stone-500">{{ __('Molette pour zoomer, glisser pour déplacer la carte, un jeton ou un gabarit. Ce cadrage est celui de l’écran de table. Les jetons en pointillés sont masqués aux joueurs.') }}</p>
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
                            <li wire:key="row-{{ $token->id }}" class="py-2 text-sm"><div class="flex items-center gap-2">
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
                                </div>
                            @if ($players->isNotEmpty())
                                @php($owner = $token->entity_id ? $characters->get($token->entity_id)?->player : null)
                                <div class="mt-1 flex items-center gap-2 pl-7 text-xs text-stone-500">
                                    <label for="control-{{ $token->id }}" class="shrink-0">{{ __('Déplacé par') }}</label>
                                    <select id="control-{{ $token->id }}" wire:change="setController({{ $token->id }}, $event.target.value)" class="min-w-0 flex-1 rounded border-stone-200 py-0.5 pr-6 pl-1 text-xs">
                                        <option value="">{{ $owner ? __('MJ et :name (son personnage)', ['name' => $owner->name]) : __('MJ seulement') }}</option>
                                        @foreach ($players as $player)
                                            <option value="{{ $player->id }}" @selected($token->controller_id === $player->id)>{{ $owner ? __('Aussi :name', ['name' => $player->name]) : $player->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <section class="rounded-xl border border-stone-200 bg-white p-4 shadow-sm">
                @php($unit = $map->scale_value ? ($map->scale_unit ?: __('cases')) : __('cases'))
                @php($inUnit = fn (?float $cells) => rtrim(rtrim(number_format(($cells ?? 1) * ($map->scale_value ?: 1), 2, '.', ''), '0'), '.'))
                <h2 class="mb-3 text-sm font-semibold tracking-wide text-stone-500 uppercase">{{ __('Gabarits de zone') }}</h2>
                <form wire:submit="addTemplate" class="space-y-2">
                    <div class="flex flex-wrap items-end gap-2">
                        <div>
                            <label for="template-shape" class="label">{{ __('Forme') }}</label>
                            <select id="template-shape" wire:model.live="templateShape" class="field py-1.5 text-sm">
                                @foreach (\App\Models\MapTemplate::labels() as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="template-size" class="label">{{ match ($templateShape) { 'circle' => __('Rayon'), 'square' => __('Côté'), 'rectangle' => __('Largeur'), default => __('Longueur') } }} <span class="font-normal text-stone-500">({{ $unit }})</span></label>
                            <input id="template-size" type="number" min="0" step="any" wire:model="templateSize" class="field w-20 py-1.5 text-sm">
                        </div>
                        @if (\App\Models\MapTemplate::hasSecondSize($templateShape))
                            <div>
                                <label for="template-size2" class="label">{{ $templateShape === 'rectangle' ? __('Hauteur') : __('Largeur') }} <span class="font-normal text-stone-500">({{ $unit }})</span></label>
                                <input id="template-size2" type="number" min="0" step="any" wire:model="templateSize2" class="field w-20 py-1.5 text-sm">
                            </div>
                        @endif
                        <button type="submit" class="btn-primary py-1.5 text-sm">{{ __('Poser') }}</button>
                    </div>
                    @error('templateSize') <p class="error">{{ $message }}</p> @enderror
                    @error('templateSize2') <p class="error">{{ $message }}</p> @enderror
                    <p class="text-xs text-stone-500">{{ $map->scale_value ? __('Mesures en :unit, comme la règle (1 case = :scale).', ['unit' => $unit, 'scale' => $map->lengthLabel(1)]) : __('Mesures en cases : donnez la taille réelle d’une case dans « Grille » pour mesurer en mètres ou en pieds.') }} {{ __('Le gabarit arrive au centre de la vue, visible à la table : glissez-le sur la carte. Le cône part de son point et s’ouvre autant qu’il est long.') }}</p>
                </form>

                @if ($templates->isNotEmpty())
                    <button type="button" wire:click="deleteAllTemplates" wire:confirm="{{ __('Supprimer tous les gabarits de la carte ?') }}" class="mt-3 text-xs text-red-700 hover:underline">{{ __('Supprimer tous les gabarits') }}</button>
                    <ul class="mt-1 divide-y divide-stone-100">
                        @foreach ($templates as $template)
                            @php($caught = $template->caught($tokens, $map->grid_size))
                            <li wire:key="template-row-{{ $template->id }}" class="space-y-1.5 py-2 text-sm">
                                <div class="flex items-center gap-2">
                                    <button type="button" wire:click="recolorTemplate({{ $template->id }})" class="size-5 shrink-0 rounded border-2 border-white shadow ring-1 ring-stone-300" style="background: {{ $template->color }}" aria-label="{{ __('Changer la couleur') }}"></button>
                                    <span @class(['min-w-0 flex-1 truncate', 'text-stone-400' => $template->hidden])>{{ $template->describe($map) }}</span>
                                    <button type="button" wire:click="toggleTemplate({{ $template->id }})" @class(['w-16 rounded px-1.5 py-0.5 text-xs', 'bg-stone-100 text-stone-600' => $template->hidden, 'bg-flow/15 text-flow' => ! $template->hidden])>
                                        {{ $template->hidden ? __('Masqué') : __('Visible') }}
                                    </button>
                                    <button type="button" wire:click="deleteTemplate({{ $template->id }})" class="text-xs text-red-700 hover:underline">{{ __('Supprimer') }}</button>
                                </div>
                                <div class="flex flex-wrap items-center gap-2 pl-7 text-xs text-stone-500">
                                    <input type="number" min="0" step="any" value="{{ $inUnit($template->size) }}" wire:change="resizeTemplate({{ $template->id }}, $event.target.value)" class="field w-16 px-1.5 py-0.5 text-xs" aria-label="{{ __('Taille') }}">
                                    @if (\App\Models\MapTemplate::hasSecondSize($template->shape))
                                        ×
                                        <input type="number" min="0" step="any" value="{{ $inUnit($template->size2) }}" wire:change="resizeTemplate({{ $template->id }}, '{{ $inUnit($template->size) }}', $event.target.value)" class="field w-16 px-1.5 py-0.5 text-xs" aria-label="{{ __('Seconde dimension') }}">
                                    @endif
                                    {{ $unit }}
                                    @if ($template->rotates())
                                        <label class="ml-auto flex items-center gap-1">
                                            {{ __('Orientation') }}
                                            <input type="range" min="0" max="359" step="15" value="{{ (int) $template->angle }}" wire:change="rotateTemplate({{ $template->id }}, $event.target.value)" class="w-24">
                                        </label>
                                    @endif
                                </div>
                                <p class="pl-7 text-xs">
                                    @if ($caught->isEmpty())
                                        <span class="text-stone-400">{{ __('Aucun jeton dans la zone.') }}</span>
                                    @else
                                        <span class="font-medium">{{ trans_choice(':count touché|:count touchés', $caught->count(), ['count' => $caught->count()]) }} :</span>
                                        {{ $caught->pluck('label')->implode(', ') }}
                                    @endif
                                </p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            @if (\App\Support\CampaignFeatures::usable($campaign, 'table'))
                <livewire:table.initiative :campaign="$campaign" :key="'initiative-'.$campaign->id" />
            @endif

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
                    <label for="scale-value" class="label">{{ __('Taille réelle d’une case') }}</label>
                    <div class="flex gap-2">
                        <input id="scale-value" type="number" min="0" step="0.1" wire:model.live.debounce.500ms="scaleValue" class="field w-24 py-1 text-sm" placeholder="1,5">
                        <input type="text" wire:model.live.debounce.500ms="scaleUnit" class="field w-24 py-1 text-sm" maxlength="20" placeholder="m" aria-label="{{ __('unité') }}">
                    </div>
                    @error('scaleValue') <p class="error">{{ $message }}</p> @enderror
                    <p class="mt-1 text-xs text-stone-500">{{ __('Par exemple 1,5 m ou 5 ft. Elle calibre la règle et les gabarits, quelle que soit la taille des cases à l’écran. La grille n’impose aucun déplacement.') }}</p>
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
