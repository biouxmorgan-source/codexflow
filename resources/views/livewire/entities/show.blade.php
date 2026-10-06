<div>
    <nav class="mb-2 text-sm text-stone-500">
        <a href="{{ route('campaigns.index') }}" class="hover:text-codex" wire:navigate>Mes campagnes</a>
        › <a href="{{ route('campaigns.show', $campaign) }}" class="hover:text-codex" wire:navigate>{{ $campaign->name }}</a>
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
                    {{ $entity->isWorldEntity() ? 'Monde « '.$entity->world->name.' »' : 'Propre à cette campagne' }}
                </p>
                @if ($entity->tags->isNotEmpty())
                    <p class="mt-2 flex flex-wrap gap-1">
                        @foreach ($entity->tags as $entityTag)
                            <a href="{{ route('campaigns.show', [$campaign, 'tag' => $entityTag->id]) }}" class="rounded-full bg-stone-100 px-2 py-0.5 text-xs text-stone-700 hover:bg-codex-soft" wire:navigate>#{{ $entityTag->name }}</a>
                        @endforeach
                    </p>
                @endif
            </div>
        </div>
        <a href="{{ route('entities.edit', [$campaign, $entity]) }}" class="btn-secondary" wire:navigate>Modifier la fiche</a>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <section class="rounded-xl border border-codex/30 bg-white p-6 shadow-sm">
                <h2 class="mb-3 font-semibold text-codex">Zone publique</h2>
                @if ($entity->summary)
                    <p class="mb-3 font-medium">{{ $entity->summary }}</p>
                @endif
                @if ($entity->description)
                    <div class="text-stone-700">{{ $description }}</div>
                @elseif (! $entity->summary && $publicAttachments->isEmpty() && ! $hasPublicFields && $publicRelations->isEmpty())
                    <p class="text-sm text-stone-500">Rien pour l'instant.</p>
                @endif
                <x-field-values :definitions="$publicFields" :entity="$entity" :campaign="$campaign" />
                <x-relation-list :relations="$publicRelations" :entity="$entity" :campaign="$campaign" />
                <x-attachment-list :attachments="$publicAttachments" />
            </section>

            <section class="rounded-xl border border-flow/30 bg-white p-6 shadow-sm">
                <h2 class="mb-3 font-semibold text-flow">Zone MJ</h2>
                @if ($entity->gm_notes)
                    <div class="text-stone-700">{{ $gmNotes }}</div>
                @elseif ($gmAttachments->isEmpty() && ! $hasGmFields && $gmRelations->isEmpty())
                    <p class="text-sm text-stone-500">Aucune note MJ.</p>
                @endif
                <x-field-values :definitions="$gmFields" :entity="$entity" :campaign="$campaign" />
                <x-relation-list :relations="$gmRelations" :entity="$entity" :campaign="$campaign" />
                <x-attachment-list :attachments="$gmAttachments" />
            </section>

            <form wire:submit="addRelation" class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="mb-1 font-semibold">Ajouter une relation</h2>
                <p class="mb-4 text-sm text-stone-600">{{ $entity->name }} <em>travaille pour</em> la Guilde, <em>habite à</em> Valdaria… Elle apparaît sur les deux fiches.</p>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="relationLabel" class="label">Relation</label>
                        <input id="relationLabel" type="text" wire:model="relationLabel" list="relation-labels" class="field" placeholder="travaille pour">
                        <datalist id="relation-labels">
                            @foreach ($relationLabels as $label)
                                <option value="{{ $label }}">
                            @endforeach
                        </datalist>
                        @error('relationLabel') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="relationTarget" class="label">Fiche liée</label>
                        <x-entity-picker id="relationTarget" model="relationTargetId" />
                        @error('relationTargetId') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="relationReverse" class="label">Vu depuis l'autre fiche <span class="font-normal text-stone-500">(facultatif)</span></label>
                        <input id="relationReverse" type="text" wire:model="relationReverse" class="field" placeholder="emploie">
                        @error('relationReverse') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="relationZone" class="label">Zone</label>
                        <select id="relationZone" wire:model="relationZone" class="field">
                            <option value="public">Zone publique</option>
                            <option value="gm">Zone MJ (secrète)</option>
                        </select>
                    </div>
                    @if ($entity->isWorldEntity())
                        <label class="flex items-center gap-2 text-sm md:col-span-2">
                            <input type="checkbox" wire:model="relationCampaignOnly">
                            Seulement dans cette campagne <span class="text-stone-500">(sinon, valable dans tout le monde quand les deux fiches en font partie)</span>
                        </label>
                    @endif
                </div>
                <button type="submit" class="btn-primary mt-4">Ajouter la relation</button>
            </form>

            <form wire:submit="saveUploads" class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="mb-1 font-semibold">Joindre des fichiers</h2>
                <p class="mb-4 text-sm text-stone-600">Images, PDF, textes ou documents bureautiques, 20 Mo maximum par fichier.</p>
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                    <div class="min-w-0 flex-1">
                        <label for="uploads" class="label">Fichiers</label>
                        <input id="uploads" type="file" wire:model="uploads" multiple class="block w-full text-sm file:mr-3 file:rounded-md file:border-0 file:bg-codex-soft file:px-3 file:py-2 file:font-medium file:text-codex">
                    </div>
                    <div>
                        <label for="uploadZone" class="label">Ranger dans</label>
                        <select id="uploadZone" wire:model="uploadZone" class="field">
                            <option value="gm">Zone MJ</option>
                            <option value="public">Zone publique</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-primary" wire:loading.attr="disabled" wire:target="uploads,saveUploads">Joindre</button>
                </div>
                <div wire:loading wire:target="uploads" class="mt-2 text-sm text-stone-500">Envoi en cours…</div>
                @error('uploads') <p class="error">{{ $message }}</p> @enderror
                @error('uploads.*') <p class="error">{{ $message }}</p> @enderror
            </form>
        </div>

        <aside class="space-y-6">
            <form wire:submit="saveState" class="space-y-4 rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                <div>
                    <h2 class="font-semibold">Dans cette campagne</h2>
                    @if ($entity->isWorldEntity())
                        <p class="text-sm text-stone-600">Ne modifie pas la fiche du monde ni les autres campagnes.</p>
                    @endif
                </div>
                <div>
                    <label for="status" class="label">Statut</label>
                    <input id="status" type="text" wire:model="status" list="status-suggestions" class="field" placeholder="vivant, mort, disparu…">
                    <datalist id="status-suggestions">
                        <option value="vivant"><option value="mort"><option value="disparu"><option value="détruit"><option value="inconnu">
                    </datalist>
                    @error('status') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="stateNotes" class="label">Notes de campagne <span class="font-normal text-stone-500">(MJ)</span></label>
                    <textarea id="stateNotes" wire:model="stateNotes" rows="4" class="field"></textarea>
                    @error('stateNotes') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div class="flex items-center gap-3">
                    <button type="submit" class="btn-primary">Enregistrer</button>
                    @if ($stateSaved)
                        <span class="text-sm text-codex">Enregistré.</span>
                    @endif
                </div>
            </form>

            <section class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="mb-2 font-semibold">Cité dans</h2>
                @if ($backlinks->isEmpty())
                    <p class="text-sm text-stone-500">Aucune autre fiche ne mentionne {{ $entity->name }}.</p>
                @else
                    <ul class="space-y-1 text-sm">
                        @foreach ($backlinks as $other)
                            <li><a href="{{ route('entities.show', [$campaign, $other]) }}" class="text-codex hover:underline" wire:navigate>{{ $other->name }}</a></li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <div class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="mb-2 font-semibold">Supprimer</h2>
                <p class="mb-3 text-sm text-stone-600">
                    @if ($otherCampaigns > 0)
                        Cette entité de monde disparaîtra aussi de {{ $otherCampaigns }} autre{{ $otherCampaigns > 1 ? 's' : '' }} campagne{{ $otherCampaigns > 1 ? 's' : '' }}.
                    @else
                        La fiche sera définitivement supprimée.
                    @endif
                </p>
                <button type="button" wire:click="delete" wire:confirm="Supprimer définitivement {{ $entity->name }} ?" class="text-sm font-medium text-red-700 hover:underline">Supprimer la fiche</button>
            </div>
        </aside>
    </div>
</div>
