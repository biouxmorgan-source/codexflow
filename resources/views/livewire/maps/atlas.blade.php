<div>
    <nav class="mb-2 text-sm text-stone-500">
        <a href="{{ route('campaigns.index') }}" class="crumb" wire:navigate>{{ __('Mes campagnes') }}</a>
        › <a href="{{ route('campaigns.show', $campaign) }}" class="crumb" wire:navigate>{{ $campaign->name }}</a>
        › <a href="{{ route('maps.index', $campaign) }}" class="crumb" wire:navigate>{{ __('Cartes') }}</a>
    </nav>

    @if ($map === null)
        <div class="mb-6 flex items-center gap-2">
            <h1 class="text-2xl font-semibold">{{ __('Carte de campagne') }}</h1>
            <x-page-help topic="campaign-map" />
        </div>
        <div class="max-w-2xl rounded-xl border border-stone-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-stone-600">{{ __('La carte de campagne est la vue d’ensemble de votre campagne : posez-y des points d’intérêt qui ouvrent une sous-carte (une ville, un donjon), une scène ou une fiche.') }}</p>
            @if ($this->maps->isEmpty())
                <p class="mt-3 text-sm text-stone-600">
                    {{ __('Créez d’abord une carte à partir d’une image des documents.') }}
                    {{ __('Conseil : une image d’au moins 3840 × 2160 pixels (4K), en JPG, reste nette quand on zoome sur la carte de campagne ou à la table. Les documents acceptent jusqu’à 50 Mo.') }}
                    <a href="{{ route('maps.index', $campaign) }}" class="link" wire:navigate>{{ __('Cartes →') }}</a>
                </p>
            @else
                <p class="mt-3 text-sm font-medium">{{ __('Choisissez la carte qui servira de vue d’ensemble :') }}</p>
                <ul class="mt-2 divide-y divide-stone-100">
                    @foreach ($this->maps as $choice)
                        <li wire:key="choice-{{ $choice->id }}" class="flex items-center justify-between gap-3 py-2 text-sm">
                            <span>{{ $choice->name }}</span>
                            <a href="{{ route('maps.atlas', [$campaign, $choice]) }}" class="link" wire:navigate>{{ __('Ouvrir') }}</a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    @else
        {{-- Fil d'Ariane des cartes : de la carte de campagne jusqu'à celle-ci. --}}
        @if ($parents !== [])
            <nav aria-label="{{ __('Cartes parentes') }}" class="mb-1 flex flex-wrap items-center gap-1 text-sm">
                @foreach ($parents as $parent)
                    <a href="{{ route('maps.atlas', [$campaign, $parent]) }}" wire:click.prevent="openMap({{ $parent->id }})" class="link">{{ $parent->name }}</a>
                    <span class="text-stone-400" aria-hidden="true">›</span>
                @endforeach
            </nav>
        @endif

        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <h1 class="text-2xl font-semibold">{{ $map->name }}</h1>
                @if ($map->is_campaign_map)
                    <span class="rounded-full bg-codex/10 px-2 py-0.5 text-xs font-medium text-codex">{{ __('Carte de campagne') }}</span>
                @endif
                <x-page-help topic="campaign-map" />
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if ($parents !== [])
                    <a href="{{ route('maps.atlas', [$campaign, end($parents)]) }}" wire:click.prevent="openMap({{ end($parents)->id }})" class="btn-secondary">{{ __('← Remonter') }}</a>
                @endif
                <livewire:table.show-button :campaign="$campaign" kind="map" :item-id="$map->id" :key="'show-atlas-'.$map->id" />
                <a href="{{ route('maps.show', [$campaign, $map]) }}" class="btn-secondary" wire:navigate>{{ __('Grille et jetons') }}</a>
            </div>
        </div>

        <div class="grid grid-cols-[minmax(0,1fr)] gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
            <section class="min-w-0" x-data="campaignMap">
                <div class="mb-2 flex flex-wrap items-center gap-2 text-sm">
                    <div class="inline-flex overflow-hidden rounded-lg border border-stone-200" role="group" aria-label="{{ __('Outil') }}">
                        <button type="button" wire:click="$set('editing', false)" @class(['px-3 py-1.5', 'bg-codex text-white' => ! $editing, 'bg-white text-stone-700' => $editing]) aria-pressed="{{ $editing ? 'false' : 'true' }}">{{ __('Explorer') }}</button>
                        <button type="button" wire:click="$set('editing', true)" @class(['border-l border-stone-200 px-3 py-1.5', 'bg-codex text-white' => $editing, 'bg-white text-stone-700' => ! $editing]) aria-pressed="{{ $editing ? 'true' : 'false' }}">{{ __('Placer des points') }}</button>
                    </div>
                    <span class="inline-flex items-center gap-1">
                        <button type="button" x-on:click="zoomBy(1.4)" class="btn-secondary px-3 py-1.5" aria-label="{{ __('Zoomer') }}">+</button>
                        <button type="button" x-on:click="zoomBy(1 / 1.4)" class="btn-secondary px-3 py-1.5" aria-label="{{ __('Dézoomer') }}">−</button>
                        <button type="button" x-on:click="reset()" x-show="z > 1" x-cloak class="btn-secondary px-3 py-1.5">{{ __('Carte entière') }}</button>
                    </span>
                    <span class="text-xs text-stone-500">
                        {{ $editing ? __('Cliquez sur la carte pour poser un point, glissez un point pour le déplacer.') : __('Cliquez sur un point : une sous-carte s’ouvre, sinon ses détails s’affichent.') }}
                    </span>
                </div>

                <div x-ref="stage" data-width="{{ $map->width }}" data-height="{{ $map->height }}" data-editing="{{ $editing ? '1' : '0' }}"
                    @class(['relative w-full touch-none overflow-hidden rounded-xl bg-stone-900 select-none', 'cursor-crosshair' => $editing])
                    style="aspect-ratio: {{ $map->width }} / {{ $map->height }}"
                    x-on:click="add($event)" x-on:wheel.prevent="wheel($event)"
                    x-on:pointerdown="panStart($event)" x-on:pointermove="panMove($event)" x-on:pointerup="panEnd($event)" x-on:pointercancel="panEnd($event)">
                    {{-- Zoom et déplacement côté navigateur : les points gardent leur taille à l'écran. --}}
                    <div x-ref="canvas" class="absolute inset-0 origin-top-left" :style="`transform: translate(${tx}px, ${ty}px) scale(${z}); --z: ${z}`">
                    <img src="{{ $map->document->fileUrl() }}" alt="{{ $map->name }}" class="pointer-events-none absolute inset-0 h-full w-full" draggable="false">

                    @foreach ($points as $point)
                        @php($progress = $point->progress())
                        <button type="button" wire:key="point-{{ $point->id }}" data-point="{{ $point->id }}"
                            style="left: {{ round($point->x / $map->width * 100, 3) }}%; top: {{ round($point->y / $map->height * 100, 3) }}%; scale: calc(1 / var(--z, 1)); transform-origin: 50% 100%"
                            @class(['group absolute flex -translate-x-1/2 -translate-y-full flex-col items-center focus:outline-none', 'cursor-grab' => $editing, 'z-10' => $selectedId === $point->id])
                            x-on:pointerdown="grab($event, {{ $point->id }})" x-on:pointermove="drag($event)" x-on:pointerup="drop($event)" x-on:pointercancel="drop($event)"
                            x-on:click.stop="open({{ $point->id }}, {{ $point->target_map_id ?? 'null' }})"
                            aria-label="{{ $point->label }}{{ $point->target ? ' · '.__('ouvre :map', ['map' => $point->target->name]) : '' }}{{ $point->scenes->isNotEmpty() ? ' · '.$point->scenes->map(fn ($scene) => $scene->status->label())->implode(', ') : '' }}">
                            <span @class([
                                'flex size-8 items-center justify-center rounded-full border-2 text-xs font-bold text-snow shadow-md transition group-hover:scale-110 group-focus-visible:ring-2 group-focus-visible:ring-snow',
                                'border-snow' => $point->shown_at_table,
                                'border-dashed border-snow/80' => ! $point->shown_at_table,
                                'ring-2 ring-yellow-300' => $selectedId === $point->id,
                                'opacity-70' => $progress === 'played',
                            ]) style="background: {{ $point->color }}">
                                @if ($point->target)
                                    <x-icon name="map" class="size-4" />
                                @elseif ($progress === 'played')
                                    <span aria-hidden="true">✓</span>
                                @elseif ($progress !== null)
                                    <span aria-hidden="true">{{ $progress === 'in_progress' ? '▶' : '•' }}</span>
                                @else
                                    <x-icon name="pin" class="size-4" />
                                @endif
                            </span>
                            <span class="-mt-0.5 h-2 w-0.5 bg-snow shadow" aria-hidden="true"></span>
                            <span class="mt-0.5 max-w-40 truncate rounded bg-black/70 px-1.5 py-0.5 text-xs font-medium text-snow">{{ $point->label }}</span>
                        </button>
                    @endforeach
                    </div>
                </div>
                <p class="mt-2 text-xs text-stone-500">
                    <span class="mr-3"><x-icon name="map" class="inline size-3.5" /> {{ __('ouvre une sous-carte') }}</span>
                    <span class="mr-3">✓ {{ __('scène jouée') }}</span>
                    <span class="mr-3">▶ {{ __('scène en cours') }}</span>
                    <span class="mr-3">• {{ __('scène à venir') }}</span>
                    <span>{{ __('Bord en pointillés : point masqué à l’écran de table.') }}</span>
                    <span class="block">{{ __('Molette, + et − pour zoomer, glisser pour déplacer la carte.') }}</span>
                    @if ($editing && $map->width < 3840 && $map->height < 2160)
                        <span class="block text-amber-700">{{ __('Image de :width × :height pixels : en dessous de la 4K (3840 × 2160), elle peut devenir floue au zoom.', ['width' => $map->width, 'height' => $map->height]) }}</span>
                    @endif
                </p>
            </section>

            <aside class="space-y-6">
                {{-- Sous-carte ouverte depuis un lieu : ses scènes et fiches restent à portée de clic. --}}
                @foreach ($places as $place)
                    @if ($place->scenes->isNotEmpty() || $place->entities->isNotEmpty() || $place->note)
                        <section wire:key="place-{{ $place->id }}" class="space-y-2 rounded-xl border border-stone-200 bg-white p-4 shadow-sm">
                            <h2 class="text-sm font-semibold tracking-wide text-stone-500 uppercase">{{ __('Ce lieu : :name', ['name' => $place->label]) }}</h2>
                            @if ($places->count() > 1)
                                <p class="text-xs text-stone-500">{{ __('Depuis :map', ['map' => $place->map->name]) }}</p>
                            @endif
                            @include('livewire.maps.partials.point-links', ['point' => $place])
                            @if ($place->note)
                                <p class="text-sm whitespace-pre-line text-stone-600">{{ $place->note }}</p>
                            @endif
                        </section>
                    @endif
                @endforeach

                @if ($this->selected)
                    @php($point = $this->selected)
                    <section wire:key="editor-{{ $point->id }}" class="space-y-3 rounded-xl border border-codex/40 bg-white p-4 shadow-sm">
                        <div class="flex items-start justify-between gap-2">
                            <h2 class="font-semibold">{{ $point->label }}</h2>
                            <button type="button" wire:click="deselect" class="text-stone-400 hover:text-stone-700" aria-label="{{ __('Fermer') }}">×</button>
                        </div>
                        @if ($point->target)
                            <button type="button" wire:click="openMap({{ $point->target->id }})" class="btn-primary py-1.5 text-sm">{{ __('Ouvrir :map', ['map' => $point->target->name]) }}</button>
                        @endif
                        @include('livewire.maps.partials.point-links', ['point' => $point, 'removable' => true])
                        @if ($point->note)
                            <p class="text-sm whitespace-pre-line text-stone-600">{{ $point->note }}</p>
                        @endif

                        <div class="space-y-2 border-t border-stone-100 pt-3">
                            <div>
                                <label for="point-scene" class="label">{{ __('Ajouter une scène') }}</label>
                                <select id="point-scene" wire:change="addScene($event.target.value)" class="field py-1.5 text-sm">
                                    <option value="">{{ __('Choisir une scène…') }}</option>
                                    @foreach ($this->scenes->whereNotIn('id', $point->scenes->modelKeys())->groupBy('scenario_id') as $scenes)
                                        <optgroup label="{{ $scenes->first()->scenario->name }}">
                                            @foreach ($scenes as $scene)
                                                <option value="{{ $scene->id }}">{{ $scene->name }} ({{ $scene->status->label() }})</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                            <form wire:submit="addEntity">
                                <label for="point-entity" class="label">{{ __('Ajouter une fiche') }} <span class="font-normal text-stone-500">{{ __('(le lieu, ses PNJ, ses objets…)') }}</span></label>
                                <div class="flex gap-2">
                                    <div class="min-w-0 flex-1" wire:key="point-entity-{{ $point->id }}-{{ $point->entities->count() }}"><x-entity-picker id="point-entity" model="pointEntityId" /></div>
                                    <button type="submit" class="btn-secondary shrink-0 py-1.5 text-sm">{{ __('Ajouter') }}</button>
                                </div>
                                @error('pointEntityId') <p class="error">{{ $message }}</p> @enderror
                            </form>
                        </div>

                        <form wire:submit="savePoint" class="space-y-2 border-t border-stone-100 pt-3">
                            <div>
                                <label for="point-label" class="label">{{ __('Nom') }}</label>
                                <input id="point-label" type="text" wire:model="pointLabel" class="field py-1.5 text-sm" maxlength="100">
                                @error('pointLabel') <p class="error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="point-target" class="label">{{ __('Sous-carte') }} <span class="font-normal text-stone-500">{{ __('(s’ouvre au clic)') }}</span></label>
                                <select id="point-target" wire:model="pointTarget" class="field py-1.5 text-sm">
                                    <option value="">{{ __('Aucune') }}</option>
                                    @foreach ($this->maps->where('id', '!=', $map->id) as $other)
                                        <option value="{{ $other->id }}">{{ $other->name }}</option>
                                    @endforeach
                                </select>
                                @error('pointTarget') <p class="error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="point-note" class="label">{{ __('Note du MJ') }}</label>
                                <textarea id="point-note" wire:model="pointNote" rows="3" class="field py-1.5 text-sm" maxlength="5000"></textarea>
                                @error('pointNote') <p class="error">{{ $message }}</p> @enderror
                            </div>
                            <label class="flex items-start gap-2 text-sm">
                                <input type="checkbox" wire:model="pointShown" class="mt-0.5">
                                <span>{{ __('Visible à la table') }} <span class="block text-xs text-stone-500">{{ __('Seul le nom s’affiche sur l’écran de table ; la note et les liens restent au MJ.') }}</span></span>
                            </label>
                            <div class="flex items-center justify-between gap-2 pt-1">
                                <button type="submit" class="btn-primary py-1.5 text-sm">{{ __('Enregistrer') }}</button>
                                <button type="button" wire:click="deletePoint({{ $point->id }})" wire:confirm="{{ __('Supprimer ce point ?') }}" class="text-xs text-red-700 hover:underline">{{ __('Supprimer') }}</button>
                            </div>
                        </form>
                    </section>
                @endif

                <section class="rounded-xl border border-stone-200 bg-white p-4 shadow-sm">
                    <h2 class="mb-3 text-sm font-semibold tracking-wide text-stone-500 uppercase">{{ __('Points d’intérêt') }}</h2>
                    @if ($points->isEmpty())
                        <p class="text-sm text-stone-500">{{ __('Aucun point pour l’instant. Choisissez « Placer des points », puis cliquez sur la carte.') }}</p>
                    @else
                        <ul class="divide-y divide-stone-100">
                            @foreach ($points as $point)
                                <li wire:key="point-row-{{ $point->id }}" class="flex items-center gap-2 py-2 text-sm">
                                    <button type="button" wire:click="recolorPoint({{ $point->id }})" class="size-4 shrink-0 rounded-full border-2 border-white shadow ring-1 ring-stone-300" style="background: {{ $point->color }}" aria-label="{{ __('Changer la couleur de :name', ['name' => $point->label]) }}"></button>
                                    <button type="button" wire:click="select({{ $point->id }})" class="min-w-0 flex-1 truncate text-left hover:text-codex">
                                        {{ $point->label }}
                                        @if ($point->target)
                                            <span class="text-xs text-stone-500">→ {{ $point->target->name }}</span>
                                        @endif
                                    </button>
                                    @if ($point->scenes->isNotEmpty())
                                        <span class="shrink-0 text-xs text-stone-500" title="{{ $point->scenes->pluck('name')->implode(', ') }}">{{ trans_choice(':count scène|:count scènes', $point->scenes->count()) }}</span>
                                    @endif
                                    <button type="button" wire:click="toggleShown({{ $point->id }})" @class(['w-16 shrink-0 rounded px-1.5 py-0.5 text-xs', 'bg-stone-100 text-stone-600' => ! $point->shown_at_table, 'bg-flow/15 text-flow' => $point->shown_at_table]) title="{{ __('Nom affiché sur l’écran de table') }}">
                                        {{ $point->shown_at_table ? __('Visible') : __('Masqué') }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>

                <section class="rounded-xl border border-stone-200 bg-white p-4 shadow-sm">
                    <h2 class="mb-3 text-sm font-semibold tracking-wide text-stone-500 uppercase">{{ __('Toutes les cartes') }}</h2>
                    @unless ($map->is_campaign_map)
                        <button type="button" wire:click="makeCampaignMap" class="btn-secondary mb-3 w-full py-1.5 text-sm">{{ __('En faire la carte de campagne') }}</button>
                    @endunless
                    @if ($leadingHere->count() > 1)
                        <p class="mb-2 text-xs text-stone-500">{{ __('On arrive aussi ici depuis : :maps.', ['maps' => $leadingHere->pluck('name')->implode(', ')]) }}</p>
                    @endif
                    <ul class="space-y-1 text-sm">
                        @foreach ($this->maps as $other)
                            <li wire:key="atlas-map-{{ $other->id }}" class="flex items-center gap-2">
                                @if ($other->id === $map->id)
                                    <span class="font-medium">{{ $other->name }}</span>
                                @else
                                    <a href="{{ route('maps.atlas', [$campaign, $other]) }}" class="link" wire:navigate>{{ $other->name }}</a>
                                @endif
                                @if ($other->is_campaign_map)
                                    <span class="text-xs text-codex">{{ __('vue d’ensemble') }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </section>
            </aside>
        </div>
    @endif
</div>
