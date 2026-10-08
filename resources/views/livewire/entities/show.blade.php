<div>
    <nav class="mb-2 text-sm text-stone-500">
        <a href="{{ route('campaigns.index') }}" class="crumb" wire:navigate>{{ __('Mes campagnes') }}</a>
        › <a href="{{ route('campaigns.show', $campaign) }}" class="crumb" wire:navigate>{{ $campaign->name }}</a>
    </nav>

    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div class="flex items-center gap-4">
            @if ($entity->hasImage())
                <a href="{{ route('entities.image', $entity) }}?v={{ $entity->updated_at?->timestamp }}" target="_blank" rel="noopener" class="shrink-0">
                    <img src="{{ route('entities.image', $entity) }}?v={{ $entity->updated_at?->timestamp }}" alt="{{ $entity->name }}" class="h-20 w-20 rounded-xl border border-stone-200 object-cover sm:h-24 sm:w-24">
                </a>
            @endif
            <div>
                <h1 class="text-2xl font-semibold">{{ $entity->name }}</h1>
                <p class="mt-1 text-sm text-stone-600">
                    {{ $entity->type->name }} ·
                    {{ $entity->isWorldEntity() ? __('Monde « :name »', ['name' => $entity->world->name]) : __('Propre à cette campagne') }}
                </p>
                <p class="mt-2 flex flex-wrap items-center gap-1">
                    @foreach ($entity->tags as $entityTag)
                        <x-tag :tag="$entityTag" :href="route('campaigns.show', [$campaign, 'tag' => $entityTag->id])" class="text-xs" />
                    @endforeach
                    @can('update', $entity)
                        <a href="{{ route('entities.edit', [$campaign, $entity]) }}#tags" class="rounded-full border border-dashed border-stone-300 px-2 py-0.5 text-xs text-stone-500 hover:border-codex hover:text-codex" wire:navigate>
                            {{ $entity->tags->isEmpty() ? __('Ajouter un tag') : __('Modifier les tags') }}
                        </a>
                    @endcan
                </p>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <livewire:table.show-button :campaign="$campaign" kind="entity" :item-id="$entity->id" wire:key="table-entity" />
            @if ($publicRelations->isNotEmpty() || $gmRelations->isNotEmpty())
                <a href="{{ route('graph.index', [$campaign, 'fiche' => $entity->id]) }}" class="btn-secondary" wire:navigate>{{ __('Voir dans le graphe') }}</a>
            @endif
            @if ($entity->hasImage())
                <livewire:table.show-button :campaign="$campaign" kind="portrait" :item-id="$entity->id" :label="__('Portrait seul')" wire:key="table-portrait" />
            @endif
            <button type="button" wire:click="togglePin" class="btn-secondary" aria-pressed="{{ $pinned ? 'true' : 'false' }}">{{ $pinned ? __('Désépingler') : __('Épingler') }}</button>
            <a href="{{ route('journal.index', [$campaign, 'sujet' => 'entity:'.$entity->id]) }}" class="btn-secondary" wire:navigate>{{ __('Historique') }}</a>
            <a href="{{ route('entities.edit', [$campaign, $entity]) }}" class="btn-secondary" wire:navigate>{{ __('Modifier la fiche') }}</a>
            @can('use-feature', ['duplication', $campaign])
            <button type="button" wire:click="duplicate" wire:confirm="{{ $entity->isWorldEntity()
                ? __("Dupliquer la fiche ? La copie reprend son contenu, ses champs, son image, ses pièces jointes, ses étiquettes et ses relations, dans le même monde. Les différences propres à chaque campagne ne sont pas copiées.")
                : __('Dupliquer la fiche ? La copie reprend son contenu, ses champs, son image, ses pièces jointes, ses étiquettes et ses relations, dans cette campagne.') }}" class="btn-secondary">{{ __('Dupliquer') }} <x-premium feature="duplication" /></button>
            @else
                <button type="button" disabled class="btn-secondary cursor-not-allowed opacity-60" title="{{ __('Fonction Premium, non comprise dans la formule du propriétaire de la campagne') }}">{{ __('Dupliquer') }} <x-premium /></button>
            @endcan
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <section class="rounded-xl border border-codex/30 bg-white p-6 shadow-sm">
                <h2 class="mb-3 font-semibold text-codex">{{ __('Zone publique') }}</h2>
                @if ($entity->summary)
                    <p class="mb-3 font-medium">{{ $entity->summary }}</p>
                @endif
                @if ($entity->description)
                    <div class="text-stone-700">{{ $description }}</div>
                @elseif (! $entity->summary && $publicAttachments->isEmpty() && ! $hasPublicFields && $publicRelations->isEmpty())
                    <p class="text-sm text-stone-500">{{ __("Rien pour l'instant.") }}</p>
                @endif
                <x-field-values :definitions="$publicFields" :entity="$entity" :campaign="$campaign" />
                <x-relation-list :relations="$publicRelations" :entity="$entity" :campaign="$campaign" />
                <x-attachment-list :attachments="$publicAttachments" :campaign="$campaign" />
            </section>

            <section class="rounded-xl border border-flow/30 bg-white p-6 shadow-sm">
                <h2 class="mb-3 font-semibold text-flow">{{ __('Zone MJ') }}</h2>
                @if ($entity->gm_notes)
                    <div class="text-stone-700">{{ $gmNotes }}</div>
                @elseif ($gmAttachments->isEmpty() && ! $hasGmFields && $gmRelations->isEmpty())
                    <p class="text-sm text-stone-500">{{ __('Aucune note MJ.') }}</p>
                @endif
                <x-field-values :definitions="$gmFields" :entity="$entity" :campaign="$campaign" />
                <x-relation-list :relations="$gmRelations" :entity="$entity" :campaign="$campaign" />
                <x-attachment-list :attachments="$gmAttachments" :campaign="$campaign" />
            </section>

            <form wire:submit="addRelation" class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm"
                x-data="{ inverses: @js($this->relationInverses()) }">
                <h2 class="mb-1 font-semibold">{{ __('Ajouter une relation') }}</h2>
                <p class="mb-4 text-sm text-stone-600">{!! __(':name <em>travaille pour</em> la Guilde, <em>habite à</em> Valdaria… Elle apparaît sur les deux fiches.', ['name' => e($entity->name)]) !!} {{ __("L'inverse se remplit tout seul pour les relations connues.") }}</p>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="relationLabel" class="label">{{ __('Relation') }}</label>
                        <input id="relationLabel" type="text" wire:model="relationLabel" list="relation-labels" class="field" placeholder="{{ __('travaille pour') }}"
                            x-on:change="const inverse = inverses[$event.target.value.trim()]; if (inverse && ! $wire.relationReverse) { $wire.relationReverse = inverse }">
                        <datalist id="relation-labels">
                            @foreach ($relationLabels as $label)
                                <option value="{{ $label }}"></option>
                            @endforeach
                        </datalist>
                        @error('relationLabel') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="relationTarget" class="label">{{ __('Fiche liée') }}</label>
                        <x-entity-picker id="relationTarget" model="relationTargetId" />
                        @error('relationTargetId') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="relationReverse" class="label">{{ __("Vu depuis l'autre fiche") }} <span class="font-normal text-stone-500">{{ __('(facultatif)') }}</span></label>
                        <input id="relationReverse" type="text" wire:model="relationReverse" class="field" placeholder="{{ __('emploie') }}">
                        @error('relationReverse') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="relationZone" class="label">{{ __('Zone') }}</label>
                        <select id="relationZone" wire:model="relationZone" class="field">
                            <option value="public">{{ __('Zone publique') }}</option>
                            <option value="gm">{{ __('Zone MJ (secrète)') }}</option>
                        </select>
                    </div>
                    @if ($entity->isWorldEntity())
                        <label class="flex items-center gap-2 text-sm md:col-span-2">
                            <input type="checkbox" wire:model="relationCampaignOnly">
                            {{ __('Seulement dans cette campagne') }} <span class="text-stone-500">{{ __('(sinon, valable dans tout le monde quand les deux fiches en font partie)') }}</span>
                        </label>
                    @endif
                </div>
                <button type="submit" class="btn-primary mt-4">{{ __('Ajouter la relation') }}</button>
            </form>

            <form wire:submit="saveUploads" class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="mb-1 font-semibold">{{ __('Joindre des fichiers') }}</h2>
                <p class="mb-4 text-sm text-stone-600">{{ __('Images, PDF, textes ou documents bureautiques, 20 Mo maximum par fichier.') }}</p>
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                    <div class="min-w-0 flex-1">
                        <label for="uploads" class="label">{{ __('Fichiers') }}</label>
                        <input id="uploads" type="file" wire:model="uploads" multiple class="block w-full text-sm file:mr-3 file:rounded-md file:border-0 file:bg-codex-soft file:px-3 file:py-2 file:font-medium file:text-codex">
                    </div>
                    <div>
                        <label for="uploadZone" class="label">{{ __('Ranger dans') }}</label>
                        <select id="uploadZone" wire:model="uploadZone" class="field">
                            <option value="gm">{{ __('Zone MJ') }}</option>
                            <option value="public">{{ __('Zone publique') }}</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-primary" wire:loading.attr="disabled" wire:target="uploads,saveUploads">{{ __('Joindre') }}</button>
                </div>
                <div wire:loading wire:target="uploads" class="mt-2 text-sm text-stone-500">{{ __('Envoi en cours…') }}</div>
                @error('uploads') <p class="error">{{ $message }}</p> @enderror
                @error('uploads.*') <p class="error">{{ $message }}</p> @enderror
            </form>
        </div>

        <aside class="space-y-6">
            <livewire:secrets.panel :campaign="$campaign" :items="['entity' => [$entity->id]]" :link="'entity:'.$entity->id" wire:key="secrets-entity" />
            <livewire:characters.give :campaign="$campaign" fixed-kind="entity" :entity-id="$entity->id" :key="'give-entity-'.$entity->id" />

            <form wire:submit="saveState" class="space-y-4 rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                <div>
                    <h2 class="font-semibold">{{ __('Dans cette campagne') }}</h2>
                    @if ($entity->isWorldEntity())
                        <p class="text-sm text-stone-600">{{ __('Ne modifie pas la fiche du monde ni les autres campagnes.') }}</p>
                    @endif
                </div>
                <div>
                    <label for="status" class="label">{{ __('Statut') }}</label>
                    <input id="status" type="text" wire:model="status" list="status-suggestions" class="field" placeholder="{{ __('vivant, mort, disparu…') }}">
                    <datalist id="status-suggestions">
                        <option value="{{ __('vivant') }}"></option><option value="{{ __('mort') }}"></option><option value="{{ __('disparu') }}"></option><option value="{{ __('détruit') }}"></option><option value="{{ __('inconnu') }}"></option>
                    </datalist>
                    @error('status') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="stateNotes" class="label">{{ __('Notes de campagne') }} <span class="font-normal text-stone-500">{{ __('(MJ)') }}</span></label>
                    <textarea id="stateNotes" wire:model="stateNotes" rows="4" class="field"></textarea>
                    @error('stateNotes') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div class="flex items-center gap-3">
                    <button type="submit" class="btn-primary">{{ __('Enregistrer') }}</button>
                    @if ($stateSaved)
                        <span class="text-sm text-codex">{{ __('Enregistré.') }}</span>
                    @endif
                </div>
            </form>

            @if ($entity->isWorldEntity() && $allFields->isNotEmpty())
                @php($overridden = $allFields->filter(fn ($definition) => $entity->overridesFieldIn($definition, $campaign)))
                <section class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                    <h2 class="font-semibold">{{ __('Champs dans cette campagne') }}</h2>
                    <p class="mb-3 text-sm text-stone-600">{{ __('Une valeur propre à la campagne ; la fiche du monde et les autres campagnes ne changent pas.') }}</p>
                    @if ($overridden->isNotEmpty())
                        <ul class="mb-3 space-y-1 text-sm">
                            @foreach ($overridden as $definition)
                                @php($value = $entity->fieldValueIn($definition, $campaign))
                                @php($worldValue = $entity->fieldValue($definition))
                                <li wire:key="override-{{ $definition->id }}" class="flex items-start gap-2">
                                    <span class="min-w-0 flex-1">
                                        <span class="font-medium">{{ $definition->name }}</span> : {{ $value === null ? __('vide') : $definition->type->format($value) }}
                                        <span class="block text-xs text-stone-500">{{ __('Monde : :value', ['value' => $worldValue === null ? __('vide') : $definition->type->format($worldValue)]) }}</span>
                                    </span>
                                    <button type="button" wire:click="removeOverride({{ $definition->id }})" class="shrink-0 text-xs link">{{ __('Valeur du monde') }}</button>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                    <form wire:submit="saveOverride" class="space-y-2">
                        <label for="overrideFieldId" class="sr-only">{{ __('Champ') }}</label>
                        <select id="overrideFieldId" wire:model.live="overrideFieldId" class="field py-1.5 text-sm">
                            <option value="">{{ __('Choisir un champ…') }}</option>
                            @foreach ($allFields as $definition)
                                <option value="{{ $definition->id }}">{{ $definition->name }}</option>
                            @endforeach
                        </select>
                        @error('overrideFieldId') <p class="error">{{ $message }}</p> @enderror
                        @php($picked = $allFields->firstWhere('id', (int) $overrideFieldId))
                        @if ($picked)
                            <label for="overrideValue" class="sr-only">{{ __('Valeur dans cette campagne') }}</label>
                            @if ($picked->type === \App\Enums\FieldType::Select)
                                <select id="overrideValue" wire:model="overrideValue" class="field py-1.5 text-sm">
                                    <option value="">{{ __('— vide —') }}</option>
                                    @foreach ($picked->options ?? [] as $option)
                                        <option value="{{ $option }}">{{ $option }}</option>
                                    @endforeach
                                </select>
                            @elseif ($picked->type === \App\Enums\FieldType::File)
                                <select id="overrideValue" wire:model="overrideValue" class="field py-1.5 text-sm">
                                    <option value="">{{ __('— vide —') }}</option>
                                    @foreach ($campaign->availableDocuments()->orderByRaw('lower(title)')->pluck('title', 'id') as $documentId => $documentTitle)
                                        <option value="{{ $documentId }}">{{ $documentTitle }}</option>
                                    @endforeach
                                </select>
                            @elseif ($picked->type === \App\Enums\FieldType::Boolean)
                                <select id="overrideValue" wire:model="overrideValue" class="field py-1.5 text-sm">
                                    <option value="">{{ __('— vide —') }}</option>
                                    <option value="oui">{{ __('Oui') }}</option>
                                    <option value="non">{{ __('Non') }}</option>
                                </select>
                            @else
                                <input id="overrideValue" type="text" wire:model="overrideValue" class="field py-1.5 text-sm" placeholder="{{ $picked->type === \App\Enums\FieldType::Date ? __('jj/mm/aaaa') : __('Valeur dans cette campagne (vide = aucune)') }}">
                            @endif
                            @error('overrideValue') <p class="error">{{ $message }}</p> @enderror
                            <button type="submit" class="btn-secondary min-h-0 py-1 text-sm">{{ __('Surcharger') }}</button>
                        @endif
                    </form>
                </section>
            @endif

            @if ($scenes->isNotEmpty())
                <section class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                    <h2 class="mb-2 font-semibold">{{ __('Scènes') }}</h2>
                    <ul class="space-y-1 text-sm">
                        @foreach ($scenes as $linkedScene)
                            <li class="flex items-center justify-between gap-2">
                                <a href="{{ route('scenes.show', [$campaign, $linkedScene]) }}" class="link" wire:navigate>{{ $linkedScene->name }}</a>
                                <span class="shrink-0 rounded-full px-2 py-0.5 text-xs {{ $linkedScene->status->badge() }}">{{ $linkedScene->status->label() }}</span>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <section class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="mb-2 font-semibold">{{ __('Documents') }}</h2>
                <x-document-list :documents="$documents" :campaign="$campaign" unlink="unlinkDocument" />
                @if ($documentOptions->isNotEmpty())
                    <div class="mt-3 flex gap-2">
                        <label for="pickedDocumentId" class="sr-only">{{ __('Lier un document') }}</label>
                        <select id="pickedDocumentId" wire:model="pickedDocumentId" class="field min-w-0 flex-1 py-1.5 text-sm">
                            <option value="">{{ __('Lier un document…') }}</option>
                            @foreach ($documentOptions as $option)
                                <option value="{{ $option->id }}">{{ $option->title }}</option>
                            @endforeach
                        </select>
                        <button type="button" wire:click="linkDocument" class="btn-secondary min-h-0 py-1 text-sm">{{ __('Lier') }}</button>
                    </div>
                    @error('pickedDocumentId') <p class="error">{{ $message }}</p> @enderror
                @endif
            </section>

            <section class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="mb-2 font-semibold">{{ __('Cité dans') }}</h2>
                @if ($backlinks->isEmpty() && $ruleBacklinks->isEmpty() && $noteBacklinks->isEmpty() && $timelineBacklinks->isEmpty() && $secretBacklinks->isEmpty())
                    <p class="text-sm text-stone-500">{{ __('Rien ne mentionne encore :name.', ['name' => $entity->name]) }}</p>
                @else
                    <ul class="space-y-1 text-sm">
                        @foreach ($backlinks as $other)
                            <li><a href="{{ route('entities.show', [$campaign, $other]) }}" class="link" wire:navigate>{{ $other->name }}</a></li>
                        @endforeach
                        @foreach ($ruleBacklinks as $citingRule)
                            <li><span class="text-xs text-stone-500">{{ __('Règle ·') }}</span> <a href="{{ route('rules.show', [$campaign, $citingRule]) }}" class="link" wire:navigate>{{ $citingRule->title }}</a></li>
                        @endforeach
                        @foreach ($timelineBacklinks as $event)
                            <li><span class="text-xs text-stone-500">{{ __('Chronologie ·') }}</span> <a href="{{ route('timeline.index', $campaign) }}#evenement-{{ $event->id }}" class="link">{{ $event->title }}</a></li>
                        @endforeach
                        @foreach ($secretBacklinks as $citingSecret)
                            <li><span class="text-xs text-stone-500">{{ __('Secret ·') }}</span> <a href="{{ route('secrets.index', $campaign) }}#secret-{{ $citingSecret->id }}" class="link">{{ $citingSecret->title }}</a></li>
                        @endforeach
                        @foreach ($noteBacklinks as $note)
                            <li>
                                <a href="{{ route('sessions.show', [$campaign, $note->playSession]) }}" class="link" wire:navigate>{{ $note->playSession->label() }}</a>
                                <span class="block truncate text-xs text-stone-500">{{ \App\Support\EntityLinks::excerpt($note->body) }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <div class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="mb-2 font-semibold">{{ __('Supprimer') }}</h2>
                <p class="mb-3 text-sm text-stone-600">
                    @if ($otherCampaigns > 0)
                        {{ trans_choice('Cette entité de monde disparaîtra aussi de :count autre campagne.|Cette entité de monde disparaîtra aussi de :count autres campagnes.', $otherCampaigns) }}
                    @else
                        {{ __('La fiche sera définitivement supprimée.') }}
                    @endif
                </p>
                <button type="button" wire:click="delete" wire:confirm="{{ __('Supprimer définitivement :name ?', ['name' => $entity->name]) }}" class="text-sm font-medium text-red-700 hover:underline">{{ __('Supprimer la fiche') }}</button>
            </div>
        </aside>
    </div>
</div>
