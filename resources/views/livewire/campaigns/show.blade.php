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
        <a href="{{ route('entities.create', $campaign) }}" class="btn-primary" wire:navigate>Nouvelle entité</a>
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
        </div>

        @if ($this->entities->isEmpty())
            <div class="rounded-xl border border-dashed border-stone-300 bg-white p-10 text-center">
                @if ($search !== '' || $type !== '')
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
</div>
