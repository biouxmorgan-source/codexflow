<div>
    <nav class="mb-2 text-sm text-stone-500">
        <a href="{{ route('campaigns.index') }}" class="hover:text-codex" wire:navigate>Mes campagnes</a>
        › <a href="{{ route('campaigns.show', $campaign) }}" class="hover:text-codex" wire:navigate>{{ $campaign->name }}</a>
    </nav>

    <h1 class="mb-6 text-2xl font-semibold">{{ $entity ? 'Modifier '.$entity->name : 'Nouvelle entité' }}</h1>

    <form wire:submit="save" class="space-y-6">
        <div class="grid gap-4 rounded-xl border border-stone-200 bg-white p-6 shadow-sm md:grid-cols-2">
            <div>
                <label for="name" class="label">Nom</label>
                <input id="name" type="text" wire:model="name" class="field" autofocus>
                @error('name') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="entityTypeId" class="label">Type</label>
                <select id="entityTypeId" wire:model.live="entityTypeId" class="field">
                    @foreach ($this->types as $entityType)
                        <option value="{{ $entityType->id }}">{{ $entityType->name }}</option>
                    @endforeach
                </select>
                @error('entityTypeId') <p class="error">{{ $message }}</p> @enderror
                <a href="{{ route('entity-types.index') }}" class="mt-1 inline-block text-xs text-codex hover:underline" wire:navigate>Créer un autre type de fiche</a>
            </div>

            <div class="md:col-span-2" x-data>
                <label for="tags" class="label">Tags <span class="font-normal text-stone-500">(séparés par des virgules, visibles du MJ seulement)</span></label>
                <input id="tags" type="text" wire:model="tags" class="field" placeholder="taverne, intrigue, acte 1…">
                @error('tags') <p class="error">{{ $message }}</p> @enderror
                @if ($this->existingTags)
                    <p class="mt-2 flex flex-wrap gap-1 text-xs">
                        @foreach ($this->existingTags as $existing)
                            <button type="button" class="rounded-full bg-stone-100 px-2 py-0.5 text-stone-700 hover:bg-codex-soft"
                                x-on:click="$wire.tags = ($wire.tags.trim() ? $wire.tags.replace(/,\s*$/, '') + ', ' : '') + @js($existing)">{{ $existing }}</button>
                        @endforeach
                    </p>
                @endif
            </div>

            @if (! $entity && $campaign->world)
                <fieldset class="md:col-span-2">
                    <legend class="label">Portée</legend>
                    <div class="flex flex-col gap-2 sm:flex-row sm:gap-6">
                        <label class="flex items-center gap-2">
                            <input type="radio" wire:model="scope" value="world">
                            Monde « {{ $campaign->world->name }} » <span class="text-sm text-stone-500">(réutilisable dans toutes ses campagnes)</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" wire:model="scope" value="campaign">
                            Cette campagne seulement
                        </label>
                    </div>
                    @error('scope') <p class="error">{{ $message }}</p> @enderror
                </fieldset>
            @endif
        </div>

        <section class="rounded-xl border border-codex/30 bg-white p-6 shadow-sm">
            <h2 class="mb-1 font-semibold text-codex">Zone publique</h2>
            <p class="mb-4 text-sm text-stone-600">Ce que les joueurs pourront découvrir quand vous le révélerez.</p>
            <div class="space-y-4">
                <div>
                    <span class="label">Image principale</span>
                    <div class="flex flex-wrap items-center gap-4">
                        @if ($image && ! $errors->has('image') && $image->isPreviewable())
                            <img src="{{ $image->temporaryUrl() }}" alt="Aperçu de la nouvelle image" class="h-24 w-24 rounded-lg object-cover">
                        @elseif ($entity?->hasImage() && ! $removeImage)
                            <img src="{{ route('entities.image', $entity) }}?v={{ $entity->updated_at?->timestamp }}" alt="Image actuelle de {{ $entity->name }}" class="h-24 w-24 rounded-lg object-cover">
                        @endif
                        <div class="space-y-2">
                            <input id="image" type="file" wire:model="image" accept="image/jpeg,image/png,image/webp,image/gif" aria-label="Image principale" class="block text-sm file:mr-3 file:rounded-md file:border-0 file:bg-codex-soft file:px-3 file:py-2 file:font-medium file:text-codex">
                            <p class="text-xs text-stone-500">Portrait, plan ou illustration. JPG, PNG, WebP ou GIF, 10 Mo maximum.</p>
                            @if ($entity?->hasImage() && ! $image)
                                <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model.live="removeImage"> Retirer l'image</label>
                            @endif
                        </div>
                    </div>
                    <div wire:loading wire:target="image" class="mt-1 text-sm text-stone-500">Envoi de l'image…</div>
                    @error('image') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="summary" class="label">Résumé</label>
                    <input id="summary" type="text" wire:model="summary" class="field" placeholder="Une phrase pour reconnaître l'entité">
                    @error('summary') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="description" class="label">Description</label>
                    <x-link-textarea id="description" model="description" />
                    @error('description') <p class="error">{{ $message }}</p> @enderror
                </div>
                <x-field-inputs :definitions="$this->fieldDefinitions->where('zone', \App\Enums\Zone::Public)" />
                @can('update', $campaign->gameSystem)
                    <p class="text-sm text-stone-500">
                        <a href="{{ route('fields.index', $campaign) }}" class="text-codex hover:underline" wire:navigate>{{ $this->fieldDefinitions->isEmpty() ? 'Ajouter des champs pour ce jeu' : 'Gérer les champs du jeu' }}</a>
                        (caractéristiques, compétences, capacités…)
                    </p>
                @endcan
            </div>
        </section>

        <section class="rounded-xl border border-flow/30 bg-white p-6 shadow-sm">
            <h2 class="mb-1 font-semibold text-flow">Zone MJ</h2>
            <p class="mb-4 text-sm text-stone-600">Jamais visible des joueurs : secrets, motivations, notes de préparation.</p>
            <label for="gmNotes" class="label">Notes MJ</label>
            <x-link-textarea id="gmNotes" model="gmNotes" />
            @error('gmNotes') <p class="error">{{ $message }}</p> @enderror
            <div class="mt-4 space-y-4">
                <x-field-inputs :definitions="$this->fieldDefinitions->where('zone', \App\Enums\Zone::GameMaster)" />
            </div>
        </section>

        <div class="flex gap-3">
            <button type="submit" class="btn-primary">Enregistrer</button>
            <a href="{{ $entity ? route('entities.show', [$campaign, $entity]) : route('campaigns.show', $campaign) }}" class="btn-secondary" wire:navigate>Annuler</a>
        </div>
    </form>
</div>
