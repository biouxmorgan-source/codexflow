<div>
    <nav class="mb-2 text-sm text-stone-500">
        <a href="{{ route('campaigns.index') }}" class="hover:text-codex" wire:navigate>Mes campagnes</a>
    </nav>

    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold">{{ $campaign->name }}</h1>
            <p class="mt-1 text-sm text-stone-600">
                {{ $campaign->gameSystem->name }}
                · {{ $campaign->world ? 'Monde : '.$campaign->world->name : 'Sans monde partagé' }}
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('scenarios.index', $campaign) }}" class="btn-secondary" wire:navigate>Scénarios</a>
            @can('update', $campaign->gameSystem)
                <a href="{{ route('fields.index', $campaign) }}" class="btn-secondary" wire:navigate>Champs du jeu</a>
                <a href="{{ route('imports.create', $campaign) }}" class="btn-secondary" wire:navigate>Importer</a>
            @endcan
            <a href="{{ route('entities.create', $campaign) }}" class="btn-primary" wire:navigate>Nouvelle entité</a>
        </div>
    </div>

    <section>
        <div class="mb-4 flex flex-wrap items-end gap-3">
            <h2 class="mr-auto text-lg font-semibold">Univers</h2>
            <div>
                <label for="search" class="sr-only">Rechercher</label>
                <input id="search" type="search" wire:model.live.debounce.300ms="search" placeholder="Rechercher par nom…" class="field">
            </div>
            <div>
                <label for="type" class="sr-only">Type</label>
                <select id="type" wire:model.live="type" class="field">
                    <option value="">Tous les types</option>
                    @foreach ($this->types as $entityType)
                        <option value="{{ $entityType->id }}">{{ $entityType->name }}</option>
                    @endforeach
                </select>
            </div>
            @if ($this->tags->isNotEmpty())
                <div>
                    <label for="tag" class="sr-only">Tag</label>
                    <select id="tag" wire:model.live="tag" class="field">
                        <option value="">Tous les tags</option>
                        @foreach ($this->tags as $existingTag)
                            <option value="{{ $existingTag->id }}">#{{ $existingTag->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>

        @if ($this->entities->isEmpty())
            <div class="rounded-xl border border-dashed border-stone-300 bg-white p-10 text-center">
                @if ($search !== '' || $type !== '' || $tag !== '')
                    <p class="text-stone-600">Aucune entité ne correspond à ces critères.</p>
                @else
                    <p class="text-lg font-medium">Aucune entité pour l'instant.</p>
                    <p class="mt-1 text-stone-600">Ajoutez des personnages, des lieux, des organisations ou des objets.</p>
                @endif
            </div>
        @else
            <ul class="divide-y divide-stone-200 overflow-hidden rounded-xl border border-stone-200 bg-white">
                @foreach ($this->entities as $entity)
                    @php($state = $entity->campaignStates->first())
                    <li wire:key="entity-{{ $entity->id }}">
                        <a href="{{ route('entities.show', [$campaign, $entity]) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-stone-50" wire:navigate>
                            @if ($entity->hasImage())
                                <img src="{{ route('entities.image', $entity) }}?v={{ $entity->updated_at?->timestamp }}" alt="" loading="lazy" class="h-10 w-10 shrink-0 rounded-lg object-cover">
                            @else
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-stone-100 text-sm font-semibold text-stone-500" aria-hidden="true">{{ mb_strtoupper(mb_substr($entity->name, 0, 1)) }}</span>
                            @endif
                            <span class="min-w-0 flex-1">
                                <span class="block truncate font-medium">{{ $entity->name }}</span>
                                @if ($entity->summary)
                                    <span class="block truncate text-sm text-stone-600">{{ $entity->summary }}</span>
                                @endif
                                @if ($entity->tags->isNotEmpty())
                                    <span class="block truncate text-xs text-stone-500">{{ $entity->tags->map(fn ($t) => '#'.$t->name)->implode(' ') }}</span>
                                @endif
                            </span>
                            @if ($state?->status)
                                <span class="shrink-0 rounded-full bg-flow/10 px-2 py-0.5 text-xs font-medium text-flow">{{ $state->status }}</span>
                            @endif
                            <span class="shrink-0 rounded-full bg-stone-100 px-2 py-0.5 text-xs text-stone-600">{{ $entity->type->name }}</span>
                            <span class="shrink-0 text-xs text-stone-500">{{ $entity->isWorldEntity() ? 'Monde' : 'Campagne' }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    @can('delete', $campaign)
        @php($localCount = $campaign->localEntities()->count())
        <section class="mt-10 rounded-xl border border-red-200 bg-white p-6 shadow-sm">
            <h2 class="mb-2 font-semibold">Supprimer la campagne</h2>
            <p class="mb-3 text-sm text-stone-600">
                @if ($localCount > 0)
                    Ses {{ $localCount }} fiche{{ $localCount > 1 ? 's' : '' }} propre{{ $localCount > 1 ? 's' : '' }} à la campagne et leurs fichiers seront supprimés.
                @else
                    La campagne et ses notes de campagne seront supprimées.
                @endif
                @if ($campaign->world)
                    Les fiches du monde « {{ $campaign->world->name }} » sont conservées.
                @endif
            </p>
            <button type="button" wire:click="delete" wire:confirm="Supprimer définitivement la campagne {{ $campaign->name }} ? Cette action est irréversible." class="text-sm font-medium text-red-700 hover:underline">Supprimer la campagne</button>
        </section>
    @endcan
</div>
