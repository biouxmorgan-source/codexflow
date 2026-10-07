<div>
    <nav class="mb-2 text-sm text-stone-500">
        <a href="{{ route('campaigns.index') }}" class="crumb" wire:navigate>{{ __('Mes campagnes') }}</a>
        › <a href="{{ route('campaigns.show', $campaign) }}" class="crumb" wire:navigate>{{ $campaign->name }}</a>
    </nav>

    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold">{{ __('Secrets') }}</h1>
            <p class="mt-1 max-w-2xl text-sm text-stone-600">{{ __('Une information à part, reliée à des fiches, scènes ou documents : « Morel travaille pour le Culte ». Cliquez sur un personnage pour la lui révéler, recliquez pour annuler.') }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('reveals.index', $campaign) }}" class="btn-secondary" wire:navigate>{{ __('Historique des révélations') }}</a>
            @unless ($editing)
                <button type="button" wire:click="create" class="btn-primary">{{ __('Nouveau secret') }}</button>
            @endunless
        </div>
    </div>

    @if ($editing)
        <form wire:submit="save" class="mb-6 space-y-4 rounded-xl border border-flow/40 bg-white p-6 shadow-sm">
            <h2 class="font-semibold">{{ $editingId ? __('Modifier le secret') : __('Nouveau secret') }}</h2>
            <div>
                <label for="secret-title" class="label">{{ __('Le secret') }}</label>
                <input id="secret-title" type="text" wire:model="title" class="field" placeholder="{{ __('Morel travaille pour le Culte d’Ambre') }}" autofocus>
                @error('title') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="secret-body" class="label">{{ __('Détails') }} <span class="font-normal text-stone-500">{{ __('(facultatif, révélés avec le secret)') }}</span></label>
                <textarea id="secret-body" wire:model="body" rows="3" class="field"></textarea>
                @error('body') <p class="error">{{ $message }}</p> @enderror
            </div>

            <fieldset class="space-y-3">
                <legend class="label">{{ __('Relié à') }}</legend>
                @php($chips = $linkedEntities->map(fn ($e) => ['kind' => 'entity', 'id' => $e->id, 'label' => $e->name])
                    ->concat($scenes->whereIn('id', $sceneIds)->map(fn ($s) => ['kind' => 'scene', 'id' => $s->id, 'label' => $s->name]))
                    ->concat($documents->whereIn('id', $documentIds)->map(fn ($d) => ['kind' => 'document', 'id' => $d->id, 'label' => $d->title])))
                @if ($chips->isNotEmpty())
                    <p class="flex flex-wrap gap-1 text-sm">
                        @foreach ($chips as $chip)
                            <span wire:key="chip-{{ $chip['kind'] }}-{{ $chip['id'] }}" class="inline-flex items-center gap-1 rounded-full bg-stone-100 px-2 py-0.5">
                                {{ $chip['label'] }}
                                <button type="button" wire:click="unlink('{{ $chip['kind'] }}', {{ $chip['id'] }})" class="text-stone-400 hover:text-red-700" aria-label="{{ __('Retirer :name', ['name' => $chip['label']]) }}">✕</button>
                            </span>
                        @endforeach
                    </p>
                @endif
                <div class="grid gap-2 md:grid-cols-3">
                    <div class="flex gap-2">
                        <div class="min-w-0 flex-1">
                            <label for="secret-entity" class="sr-only">{{ __('Fiche') }}</label>
                            <x-entity-picker id="secret-entity" model="pickedEntityId" />
                        </div>
                        <button type="button" wire:click="addEntity" class="btn-secondary">{{ __('Lier') }}</button>
                    </div>
                    <div>
                        <label for="secret-scene" class="sr-only">{{ __('Scène') }}</label>
                        <select id="secret-scene" wire:model.live="pickedSceneId" class="field">
                            <option value="">{{ __('Une scène…') }}</option>
                            @foreach ($scenes as $scene)
                                <option value="{{ $scene->id }}">{{ $scene->scenario->name }} · {{ $scene->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="secret-document" class="sr-only">{{ __('Document') }}</label>
                        <select id="secret-document" wire:model.live="pickedDocumentId" class="field">
                            <option value="">{{ __('Un document…') }}</option>
                            @foreach ($documents as $document)
                                <option value="{{ $document->id }}">{{ $document->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </fieldset>

            <div class="flex flex-wrap gap-2">
                <button type="submit" class="btn-primary">{{ __('Enregistrer') }}</button>
                <button type="button" wire:click="cancel" class="btn-secondary">{{ __('Annuler') }}</button>
                @if ($editingId)
                    <button type="button" wire:click="delete({{ $editingId }})" wire:confirm="{{ __('Supprimer ce secret ? Les personnages qui le connaissent le gardent dans leurs connaissances.') }}" class="ml-auto text-sm text-red-700 hover:underline">{{ __('Supprimer') }}</button>
                @endif
            </div>
        </form>
    @endif

    <div class="mb-4 max-w-sm">
        <label for="secret-search" class="sr-only">{{ __('Chercher un secret') }}</label>
        <input id="secret-search" type="search" wire:model.live.debounce.300ms="search" class="field" placeholder="{{ __('Chercher un secret, une fiche…') }}">
    </div>

    @if ($this->secrets->isEmpty())
        <div class="rounded-xl border border-dashed border-stone-300 bg-white p-10 text-center">
            <p class="text-lg font-medium">{{ $search !== '' ? __('Aucun secret ne correspond.') : __("Aucun secret pour l'instant.") }}</p>
            <p class="mt-1 text-stone-600">{{ __('Notez ici ce que les personnages pourraient découvrir, puis révélez-le au bon moment.') }}</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($this->secrets as $secret)
                <x-secret-card :secret="$secret" :campaign="$campaign" :characters="$this->tableCharacters">
                    <x-slot:actions>
                        <button type="button" wire:click="edit({{ $secret->id }})" class="shrink-0 text-sm link">{{ __('Modifier') }}</button>
                    </x-slot:actions>
                </x-secret-card>
            @endforeach
        </div>
    @endif
</div>
