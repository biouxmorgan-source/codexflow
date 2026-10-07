<div>
    <nav class="mb-2 text-sm text-stone-500">
        <a href="{{ route('campaigns.index') }}" class="crumb" wire:navigate>{{ __('Mes campagnes') }}</a>
    </nav>

    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold">{{ $campaign->name }}</h1>
            <p class="mt-1 text-sm text-stone-600">
                {{ $campaign->gameSystem->name }}
                · {{ $campaign->world ? __('Monde : :name', ['name' => $campaign->world->name]) : __('Sans monde partagé') }}
            </p>
        </div>
    </div>

    {{-- Les grandes zones de la campagne, chacune avec une phrase d'explication. --}}
    <nav aria-label="{{ __('Zones de la campagne') }}" class="mb-8 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
        <a href="{{ route('sessions.live', $campaign) }}" class="tile border-flow/40 bg-flow/5 hover:border-flow" wire:navigate>
            <span class="font-semibold text-flow">{{ __('Mode Session →') }}</span>
            <span class="mt-1 text-sm text-stone-600">{{ __('Pendant la partie : la scène en cours, ses fiches et documents, vos notes rapides.') }}</span>
        </a>
        <a href="{{ route('scenarios.index', $campaign) }}" class="tile" wire:navigate>
            <span class="font-semibold text-codex">{{ __('Scénarios →') }}</span>
            <span class="mt-1 text-sm text-stone-600">{{ __('La campagne découpée en scénarios et en scènes, avec leur statut (prévue, en cours, jouée).') }}</span>
        </a>
        <a href="{{ route('documents.index', $campaign) }}" class="tile" wire:navigate>
            <span class="font-semibold text-codex">{{ __('Documents →') }}</span>
            <span class="mt-1 text-sm text-stone-600">{{ __('Cartes, indices et aides en PDF ou en image, à lier aux scènes et à ouvrir en grand.') }}</span>
        </a>
        <a href="{{ route('rules.index', $campaign) }}" class="tile" wire:navigate>
            <span class="font-semibold text-codex">{{ __('Règles →') }}</span>
            <span class="mt-1 text-sm text-stone-600">{{ __('Règles du jeu, règles maison et glossaire, pour retrouver une procédure en pleine partie.') }}</span>
        </a>
        <a href="{{ route('characters.index', $campaign) }}" class="tile" wire:navigate>
            <span class="font-semibold text-codex">{{ __('Personnages →') }}</span>
            <span class="mt-1 text-sm text-stone-600">{{ __('Les personnages des joueurs : à qui ils sont confiés, leur feuille PDF, leurs compteurs.') }}</span>
        </a>
        <a href="{{ route('members.index', $campaign) }}" class="tile" wire:navigate>
            <span class="font-semibold text-codex">{{ __('Joueurs →') }}</span>
            <span class="mt-1 text-sm text-stone-600">{{ __('Inviter vos joueurs par un lien et voir qui fait partie de la campagne.') }}</span>
        </a>
        @php($unread = \App\Models\Message::unreadCount(auth()->user(), $campaign))
        <a href="{{ route('messages.index', $campaign) }}" class="tile" wire:navigate>
            <span class="font-semibold text-codex">{{ __('Messages →') }}
                @if ($unread > 0)
                    <span class="ml-1 rounded-full bg-flow px-2 py-0.5 text-xs font-semibold text-on-accent">{{ $unread }} <span class="sr-only">{{ __('non lus') }}</span></span>
                @endif
            </span>
            <span class="mt-1 text-sm text-stone-600">{{ __('Écrire à un joueur en privé ou à tout le groupe, avec une fiche ou un document joint.') }}</span>
        </a>
        <a href="{{ route('journal.index', $campaign) }}" class="tile" wire:navigate>
            <span class="font-semibold text-codex">{{ __('Journal →') }}</span>
            <span class="mt-1 text-sm text-stone-600">{{ __("Qui a modifié quoi et quand, avec l'ancienne valeur : rien ne se perd.") }}</span>
        </a>
        @can('update', $campaign->gameSystem)
            <a href="{{ route('fields.index', $campaign) }}" class="tile" wire:navigate>
                <span class="font-semibold text-codex">{{ __('Champs du jeu →') }}</span>
                <span class="mt-1 text-sm text-stone-600">{{ __('Les caractéristiques et compétences affichées sur les fiches de :name.', ['name' => $campaign->gameSystem->name]) }}</span>
            </a>
            <a href="{{ route('imports.create', $campaign) }}" class="tile" wire:navigate>
                <span class="font-semibold text-codex">{{ __('Importer →') }}</span>
                <span class="mt-1 text-sm text-stone-600">{{ __('Ajouter en une fois des fiches, des champs, des règles ou des scènes depuis un fichier CSV.') }}</span>
            </a>
        @endcan
    </nav>

    <section>
        <div class="mb-4 flex flex-wrap items-end gap-3">
            <div class="mr-auto">
                <h2 class="text-lg font-semibold">{{ __('Univers') }}</h2>
                <p class="text-sm text-stone-600">{{ __("Personnages, lieux, créatures… Cliquez sur une fiche pour l'ouvrir.") }}</p>
            </div>
            <div>
                <label for="search" class="sr-only">{{ __('Rechercher') }}</label>
                <input id="search" type="search" wire:model.live.debounce.300ms="search" placeholder="{{ __('Rechercher par nom…') }}" class="field">
            </div>
            <div>
                <label for="type" class="sr-only">{{ __('Type') }}</label>
                <select id="type" wire:model.live="type" class="field">
                    <option value="">{{ __('Tous les types') }}</option>
                    @foreach ($this->types as $entityType)
                        <option value="{{ $entityType->id }}">{{ $entityType->name }}</option>
                    @endforeach
                </select>
            </div>
            @if ($this->tags->isNotEmpty())
                <div>
                    <label for="tag" class="sr-only">{{ __('Tag') }}</label>
                    <select id="tag" wire:model.live="tag" class="field">
                        <option value="">{{ __('Tous les tags') }}</option>
                        @foreach ($this->tags as $existingTag)
                            <option value="{{ $existingTag->id }}">#{{ $existingTag->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <a href="{{ route('exports.download', [$campaign, 'fiches', 'type' => $type ?: null]) }}" class="btn-secondary" title="{{ __('Fichier CSV réimportable, à utiliser comme modèle') }}">{{ __('Exporter') }}</a>
            <a href="{{ route('entities.create', $campaign) }}" class="btn-primary" wire:navigate>{{ __('Nouvelle entité') }}</a>
        </div>

        @if ($this->entities->isEmpty())
            <div class="rounded-xl border border-dashed border-stone-300 bg-white p-10 text-center">
                @if ($search !== '' || $type !== '' || $tag !== '')
                    <p class="text-stone-600">{{ __('Aucune entité ne correspond à ces critères.') }}</p>
                @else
                    <p class="text-lg font-medium">{{ __("Aucune entité pour l'instant.") }}</p>
                    <p class="mt-1 text-stone-600">{{ __('Ajoutez des personnages, des lieux, des organisations ou des objets.') }}</p>
                @endif
            </div>
        @else
            <ul class="divide-y divide-stone-200 overflow-hidden rounded-xl border border-stone-200 bg-white">
                @foreach ($this->entities as $entity)
                    @php($state = $entity->campaignStates->first())
                    <li wire:key="entity-{{ $entity->id }}">
                        <a href="{{ route('entities.show', [$campaign, $entity]) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-codex-soft" wire:navigate>
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
                            <span class="shrink-0 text-xs text-stone-500">{{ $entity->isWorldEntity() ? __('Monde') : __('Campagne') }}</span>
                            <span class="shrink-0 text-codex" aria-hidden="true">›</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    @can('delete', $campaign)
        @php($localCount = $campaign->localEntities()->count())
        <section class="mt-10 rounded-xl border border-red-200 bg-white p-6 shadow-sm">
            <h2 class="mb-2 font-semibold">{{ __('Supprimer la campagne') }}</h2>
            <p class="mb-3 text-sm text-stone-600">
                @if ($localCount > 0)
                    {{ trans_choice('Ses :count fiche propre à la campagne et leurs fichiers seront supprimés.|Ses :count fiches propres à la campagne et leurs fichiers seront supprimés.', $localCount) }}
                @else
                    {{ __('La campagne et ses notes de campagne seront supprimées.') }}
                @endif
                @if ($campaign->world)
                    {{ __('Les fiches du monde « :name » sont conservées.', ['name' => $campaign->world->name]) }}
                @endif
            </p>
            <button type="button" wire:click="delete" wire:confirm="{{ __('Supprimer définitivement la campagne :name ? Cette action est irréversible.', ['name' => $campaign->name]) }}" class="text-sm font-medium text-red-700 hover:underline">{{ __('Supprimer la campagne') }}</button>
        </section>
    @endcan
</div>
