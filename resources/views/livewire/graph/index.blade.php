@php
    $graph = $this->graph;
    $character = $this->character;
    $palette = ['#2563eb', '#16a34a', '#9333ea', '#dc2626', '#0891b2', '#ca8a04', '#db2777', '#4d7c0f', '#57534e', '#0f766e'];
    $colors = $graph ? $graph->types->keys()->values()->mapWithKeys(fn ($id, $i) => [$id => $palette[$i % count($palette)]]) : collect();
    $focused = $graph?->nodes->firstWhere('id', (int) $focus);
@endphp
<div>
    @if ($this->isGameMaster && $character)
        <x-view-as-banner :name="$character->entity->name" :exit="route('graph.index', $campaign)" />
    @endif

    <nav class="mb-2 text-sm text-stone-500">
        <a href="{{ route('campaigns.index') }}" class="crumb" wire:navigate>{{ __('Mes campagnes') }}</a>
        › <a href="{{ route('campaigns.show', $campaign) }}" class="crumb" wire:navigate>{{ $campaign->name }}</a>
    </nav>

    <div class="mb-4 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold">{{ __('Graphe des relations') }}</h1>
            <p class="mt-1 max-w-2xl text-sm text-stone-600">
                @if ($character)
                    {{ __('Les fiches que :name connaît et les relations visibles entre elles.', ['name' => $character->entity->name]) }}
                @else
                    {{ __('Toutes les fiches reliées de la campagne. Cliquez sur une fiche pour la mettre au centre ; les relations en pointillés sont en zone MJ.') }}
                @endif
            </p>
        </div>
        @if ($this->isGameMaster && $characters->isNotEmpty())
            <div>
                <label for="graph-as" class="label">{{ __('Voir comme…') }}</label>
                <select id="graph-as" wire:model.live="asCharacterId" class="field py-1.5 text-sm">
                    <option value="">{{ __('Le MJ (tout)') }}</option>
                    @foreach ($characters as $item)
                        <option value="{{ $item->id }}">{{ $item->entity->name }}</option>
                    @endforeach
                </select>
            </div>
        @elseif ($this->myCharacters->count() > 1)
            <div>
                <label for="graph-as" class="label">{{ __('Personnage') }}</label>
                <select id="graph-as" wire:model.live="asCharacterId" class="field py-1.5 text-sm">
                    @foreach ($this->myCharacters as $item)
                        <option value="{{ $item->id }}" @selected($character?->is($item))>{{ $item->entity->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif
    </div>

    @if ($graph === null)
        <div class="rounded-xl border border-dashed border-stone-300 bg-white p-10 text-center">
            <p class="text-lg font-medium">{{ __('Vous n’avez pas encore de personnage dans cette campagne.') }}</p>
        </div>
    @else
        <div class="mb-3 flex flex-wrap items-end gap-3 text-sm">
            @if ($graph->types->count() > 1 || $typeId !== '')
                <div>
                    <label for="graph-type" class="label">{{ __('Type de fiche') }}</label>
                    <select id="graph-type" wire:model.live="typeId" class="field py-1.5 text-sm">
                        <option value="">{{ __('Tous') }}</option>
                        @foreach ($graph->types as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            @if ($focused)
                <div>
                    <label for="graph-depth" class="label">{{ __('Profondeur') }}</label>
                    <select id="graph-depth" wire:model.live="depth" class="field py-1.5 text-sm">
                        @foreach (range(1, \App\Support\RelationGraph::MAX_DEPTH) as $level)
                            <option value="{{ $level }}">{{ trans_choice(':count relation|:count relations', $level) }}</option>
                        @endforeach
                    </select>
                </div>
                <p class="pb-2">
                    {{ __('Centré sur') }} <span class="font-semibold">{{ $focused['name'] }}</span>
                    · <a href="{{ $focused['url'] }}" class="link" wire:navigate>{{ __('Ouvrir la fiche') }}</a>
                    · <button type="button" wire:click="focusOn(null)" class="link">{{ __('Tout le réseau') }}</button>
                </p>
            @endif
        </div>

        @if ($graph->nodes->isEmpty())
            <div class="rounded-xl border border-dashed border-stone-300 bg-white p-10 text-center">
                <p class="text-lg font-medium">{{ __('Aucune relation à afficher.') }}</p>
                <p class="mt-1 text-stone-600">
                    {{ $character ? __('Les relations apparaissent quand les fiches qu’elles relient sont connues.') : __('Ajoutez des relations depuis les fiches : « travaille pour », « habite », « rival de »…') }}
                </p>
            </div>
        @else
            @php($data = ['nodes' => $graph->nodes, 'edges' => $graph->edges, 'focus' => $focused['id'] ?? null, 'colors' => $colors])
            <div wire:key="graph-{{ md5(json_encode($data)) }}" x-data="relationGraph" class="relative">
                <script type="application/json" x-ref="data">@json($data)</script>
                <svg x-ref="svg" class="h-[70vh] w-full touch-none rounded-xl border border-stone-200 bg-white select-none" role="img" aria-label="{{ __('Graphe des relations') }}"
                    x-on:pointerdown="down($event)" x-on:pointermove="move($event)" x-on:pointerup="up($event)" x-on:pointercancel="up($event)" x-on:wheel.prevent="wheel($event)">
                    <defs>
                        <marker id="graph-arrow" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="7" markerHeight="7" orient="auto-start-reverse">
                            <path d="M 0 0 L 10 5 L 0 10 z" fill="#a8a29e" />
                        </marker>
                    </defs>
                </svg>
                <div class="absolute top-2 right-2 flex gap-1">
                    <button type="button" x-on:click="zoomBy(1.25)" class="btn-secondary px-3 py-1" aria-label="{{ __('Zoomer') }}">+</button>
                    <button type="button" x-on:click="zoomBy(0.8)" class="btn-secondary px-3 py-1" aria-label="{{ __('Dézoomer') }}">−</button>
                    <button type="button" x-on:click="fit()" class="btn-secondary px-3 py-1 text-sm">{{ __('Recadrer') }}</button>
                </div>
            </div>

            <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-stone-600">
                @foreach ($graph->types as $id => $name)
                    @if ($graph->nodes->contains('type_id', $id))
                        <span class="inline-flex items-center gap-1.5"><span class="size-3 rounded-full" style="background: {{ $colors[$id] }}"></span>{{ $name }}</span>
                    @endif
                @endforeach
                <span class="text-stone-500">{{ __('Survolez une fiche pour lire ses relations ; glissez pour la déplacer.') }}</span>
            </div>
            @if ($graph->truncated)
                <p class="mt-2 text-sm text-amber-700">{{ __('Réseau trop grand : seules les fiches les plus reliées sont affichées. Centrez sur une fiche ou filtrez par type.') }}</p>
            @endif

            {{-- Version lisible sans le dessin : la liste des relations. --}}
            <details class="mt-6 rounded-xl border border-stone-200 bg-white p-4 text-sm">
                <summary class="cursor-pointer font-semibold">{{ __('Liste des relations (:count)', ['count' => $graph->edges->count()]) }}</summary>
                @php($names = $graph->nodes->pluck('name', 'id'))
                <ul class="mt-2 space-y-1">
                    @foreach ($graph->edges as $edge)
                        <li wire:key="edge-{{ $edge['id'] }}">
                            <button type="button" wire:click="focusOn({{ $edge['from'] }})" class="link">{{ $names[$edge['from']] }}</button>
                            <span class="text-stone-500">{{ $edge['label'] }}</span>
                            <button type="button" wire:click="focusOn({{ $edge['to'] }})" class="link">{{ $names[$edge['to']] }}</button>
                            @if ($edge['gm'])
                                <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs text-amber-700">{{ __('Zone MJ') }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </details>
        @endif
    @endif
</div>
