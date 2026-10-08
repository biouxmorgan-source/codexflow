<div>
    <nav class="mb-2 text-sm text-stone-500">
        <a href="{{ route('campaigns.index') }}" class="crumb" wire:navigate>{{ __('Mes campagnes') }}</a>
    </nav>

    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold">{{ $campaign->name }}</h1>
            <p class="mt-1 text-sm text-stone-600">
                @if ($campaign->gameSystem->user_id === auth()->id())
                    <a href="{{ route('games.show', $campaign->gameSystem) }}" class="crumb" wire:navigate>{{ $campaign->gameSystem->name }}</a>
                @else
                    {{ $campaign->gameSystem->name }}
                @endif
                ·
                @if ($campaign->world && $campaign->world->user_id === auth()->id())
                    <a href="{{ route('worlds.show', $campaign->world) }}" class="crumb" wire:navigate>{{ __('Monde : :name', ['name' => $campaign->world->name]) }}</a>
                @else
                    {{ $campaign->world ? __('Monde : :name', ['name' => $campaign->world->name]) : __('Sans monde partagé') }}
                @endif
            </p>
        </div>
    </div>

    {{-- Le mode Session d'abord, puis les quatre zones de préparation, puis les outils en icônes. --}}
    <a href="{{ route('sessions.live', $campaign) }}" class="tile mb-3 border-flow/40 bg-flow/5 hover:border-flow sm:flex-row sm:items-center sm:gap-4" wire:navigate>
        <span class="text-lg font-semibold text-flow">{{ __('Mode Session →') }}</span>
        <span class="mt-1 text-sm text-stone-600 sm:mt-0">{{ __('Pendant la partie : la scène en cours, ses fiches et documents, vos notes rapides.') }}</span>
    </a>

    <nav aria-label="{{ __('Zones de la campagne') }}" class="mb-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <a href="{{ route('scenarios.index', $campaign) }}" class="tile" wire:navigate>
            <span class="font-semibold text-codex">{{ __('Scénarios →') }}</span>
            <span class="mt-1 text-sm text-stone-600">{{ __('La campagne découpée en scénarios et en scènes, avec leur statut.') }}</span>
        </a>
        <a href="{{ route('documents.index', $campaign) }}" class="tile" wire:navigate>
            <span class="font-semibold text-codex">{{ __('Documents →') }}</span>
            <span class="mt-1 text-sm text-stone-600">{{ __('Cartes, indices et aides en PDF ou en image, à lier aux scènes.') }}</span>
        </a>
        <a href="{{ route('rules.index', $campaign) }}" class="tile" wire:navigate>
            <span class="font-semibold text-codex">{{ __('Règles →') }}</span>
            <span class="mt-1 text-sm text-stone-600">{{ __('Règles du jeu, règles maison et glossaire, à retrouver en pleine partie.') }}</span>
        </a>
        <a href="{{ route('characters.index', $campaign) }}" class="tile" wire:navigate>
            <span class="font-semibold text-codex">{{ __('Personnages →') }}</span>
            <span class="mt-1 text-sm text-stone-600">{{ __('Les personnages des joueurs : à qui ils sont confiés, leur feuille, leurs compteurs.') }}</span>
        </a>
    </nav>

    @php($unread = \App\Models\Message::unreadCount(auth()->user(), $campaign))
    <nav aria-label="{{ __('Outils de la campagne') }}" class="mb-8 flex flex-wrap gap-2">
        <x-tool-link :href="route('secrets.index', $campaign)" icon="secret" :label="__('Secrets')" />
        <x-tool-link :href="route('maps.index', $campaign)" icon="map" :label="__('Cartes')" feature="maps" :campaign="$campaign" />
        <x-tool-link :href="route('graph.index', $campaign)" icon="graph" :label="__('Graphe')" feature="graph" :campaign="$campaign" />
        <x-tool-link :href="route('timeline.index', $campaign)" icon="timeline" :label="__('Chronologie')" feature="timeline" :campaign="$campaign" />
        <x-tool-link :href="route('ai.index', $campaign)" icon="ai" :label="__('Assistant IA')" feature="ai" :campaign="$campaign" />
        <x-tool-link :href="route('members.index', $campaign)" icon="members" :label="__('Membres')" />
        <x-tool-link :href="route('messages.index', $campaign)" icon="messages" :label="__('Messages')" :badge="$unread ?: null" />
        <x-tool-link :href="route('journal.index', $campaign)" icon="journal" :label="__('Journal')" />
        <x-tool-link :href="route('table.remote', $campaign)" icon="remote" :label="__('Télécommande')" feature="table" :campaign="$campaign" />
        @can('update', $campaign->gameSystem)
            <x-tool-link :href="route('fields.index', $campaign)" icon="fields" :label="__('Champs du jeu')" />
            <x-tool-link :href="route('imports.create', $campaign)" icon="import" :label="__('Importer un fichier')" />
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
                                    <span class="mt-0.5 flex flex-wrap gap-1 text-xs">
                                        @foreach ($entity->tags as $entityTag)
                                            <x-tag :tag="$entityTag" compact />
                                        @endforeach
                                    </span>
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

    @can('update', $campaign)
        <section class="mt-10 rounded-xl border border-stone-200 bg-white p-6 shadow-sm" aria-labelledby="campaign-features">
            <h2 id="campaign-features" class="mb-1 font-semibold">{{ __('Fonctions de la campagne') }}</h2>
            <p class="mb-3 text-sm text-stone-600">{{ __('Décochez ce dont cette table n’a pas besoin : la fonction disparaît pour vous comme pour les joueurs. Rien n’est effacé, tout revient quand vous la recochez.') }}</p>
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach (\App\Support\CampaignFeatures::all() as $key => $feature)
                    <label wire:key="feature-{{ $key }}" class="flex items-start gap-2 text-sm">
                        <input type="checkbox" wire:click="toggleFeature('{{ $key }}')" @checked(\App\Support\CampaignFeatures::enabled($campaign, $key)) class="mt-1">
                        <span>
                            <span class="inline-flex items-center gap-1 font-medium">{{ $feature['label'] }} <x-premium :feature="$key" /></span>
                            <span class="block text-xs text-stone-500">{{ $feature['hint'] }}</span>
                            @unless (\App\Support\Plans\Plans::allows($campaign->owner, $key))
                                <span class="block text-xs text-amber-700">{{ __('Non comprise dans la formule actuelle : elle reste grisée même cochée.') }}</span>
                            @endunless
                        </span>
                    </label>
                @endforeach
            </div>
        </section>
    @endcan

    @can('duplicate', $campaign)
        @cannot('use-feature', ['duplication'])
            <x-premium-locked class="mt-10" :title="__('Dupliquer la campagne')" />
        @else
        <section class="mt-10 rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
            <h2 class="mb-2 flex items-center gap-1 font-semibold">{{ __('Dupliquer la campagne') }} <x-premium feature="duplication" /></h2>
            <p class="mb-3 text-sm text-stone-600">{{ __('Pour rejouer le même contenu avec une autre table : les fiches, scénarios, scènes, documents et règles de la campagne sont copiés, le jeu et le monde sont partagés. Les joueurs, les personnages, les séances et le journal ne sont pas copiés, et les scènes repartent de « Prévue ».') }}</p>
            <label class="mb-3 flex items-start gap-2 text-sm">
                <input type="checkbox" wire:model="keepStatuses" class="mt-1">
                <span>{{ __('Garder le statut des fiches dans cette campagne (mort, prisonnier…)') }}
                    <span class="block text-xs text-stone-500">{{ __('Sinon, la copie repart des fiches d’origine ; les notes et valeurs propres à la campagne sont gardées.') }}</span></span>
            </label>
            <button type="button" wire:click="duplicate" wire:confirm="{{ __('Dupliquer la campagne ? Le contenu préparé est copié ; les joueurs, les personnages, les séances et le journal ne le sont pas.') }}" class="btn-secondary">{{ __('Dupliquer') }}</button>
            @error('plan') <p class="error mt-2">{{ $message }}</p> @enderror
        </section>
        @endcannot

        @cannot('use-feature', ['archive', $campaign])
            <x-premium-locked class="mt-6" :title="__('Exporter la campagne')" />
        @else
        <section class="mt-6 rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
            <h2 class="mb-2 flex items-center gap-1 font-semibold">{{ __('Exporter la campagne') }} <x-premium feature="archive" /></h2>
            <p class="mb-3 text-sm text-stone-600">{{ __('Une archive .zip avec le jeu (champs, règles), le monde, les fiches, scénarios, documents, cartes, secrets et la chronologie, fichiers compris. Pour la sauvegarder ou la confier à un autre MJ, qui l’importe depuis « Mes campagnes ». Les joueurs, leurs personnages, les séances et le journal n’y sont pas.') }}</p>
            <a href="{{ route('archives.campaign', $campaign) }}" class="btn-secondary">{{ __('Télécharger l’archive') }}</a>
            <h3 class="mt-5 mb-1 text-sm font-semibold">{{ __('Sauvegarde complète') }}</h3>
            <p class="mb-3 text-sm text-stone-600">{{ __('La même archive avec la table : personnages et leurs fiches, ce qu’ils ont reçu, séances, notes de séance, notes partagées des joueurs, messages et journal. Les notes « Moi seul » et les adresses e-mail n’y figurent jamais. À l’import, les personnages reviennent sans joueur, prêts à être confiés ; messages et journal restent lisibles dans l’archive.') }}</p>
            <a href="{{ route('archives.campaign', [$campaign, 'complete' => 1]) }}" class="btn-secondary">{{ __('Télécharger la sauvegarde complète') }}</a>
        </section>
        @endcannot
    @endcan

    @can('delete', $campaign)
        @php($localCount = $campaign->localEntities()->count())
        <section class="mt-10 rounded-xl border border-red-200 bg-white p-6 shadow-sm">
            <h2 class="mb-2 font-semibold">{{ __('Supprimer la campagne') }}</h2>
            <p class="mb-3 text-sm text-stone-600">
                @if ($localCount > 0)
                    {{ trans_choice('Sa fiche propre à la campagne et ses fichiers seront supprimés.|Ses :count fiches propres à la campagne et leurs fichiers seront supprimés.', $localCount) }}
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
