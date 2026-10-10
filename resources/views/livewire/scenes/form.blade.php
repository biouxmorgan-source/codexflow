<div>
    <nav class="mb-2 text-sm text-stone-500">
        <a href="{{ route('campaigns.index') }}" class="crumb" wire:navigate>{{ __('Mes campagnes') }}</a>
        › <a href="{{ route('campaigns.show', $campaign) }}" class="crumb" wire:navigate>{{ $campaign->name }}</a>
        › <a href="{{ route('scenarios.index', $campaign) }}" class="crumb" wire:navigate>{{ __('Scénarios') }}</a>
    </nav>

    <h1 class="mb-6 text-2xl font-semibold">{{ $scene ? __('Modifier :name', ['name' => $scene->name]) : __('Nouvelle scène') }}</h1>

    @if ($scenarios->isEmpty())
        <div class="rounded-xl border border-dashed border-stone-300 bg-white p-10 text-center">
            <p class="text-lg font-medium">{{ __("Créez d'abord un scénario.") }}</p>
            <a href="{{ route('scenarios.index', $campaign) }}" class="btn-primary mt-4" wire:navigate>{{ __('Aller aux scénarios') }}</a>
        </div>
    @else
        <form wire:submit="save" class="space-y-6">
            <div class="grid gap-4 rounded-xl border border-stone-200 bg-white p-6 shadow-sm md:grid-cols-2">
                <div class="md:col-span-2">
                    <label for="name" class="label">{{ __('Nom de la scène') }}</label>
                    <input id="name" type="text" wire:model="name" class="field" autofocus placeholder="{{ __('Arrivée à l\'auberge') }}">
                    @error('name') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="scenarioId" class="label">{{ __('Scénario') }}</label>
                    <select id="scenarioId" wire:model.live="scenarioId" class="field">
                        @foreach ($scenarios as $scenario)
                            <option value="{{ $scenario->id }}">{{ $scenario->name }}</option>
                        @endforeach
                    </select>
                    @error('scenarioId') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="chapter" class="label">{{ __('Chapitre') }} <span class="font-normal text-stone-500">{{ __('(facultatif)') }}</span></label>
                    <input id="chapter" type="text" wire:model="chapter" list="chapter-suggestions" class="field" placeholder="{{ __('Acte I') }}">
                    <datalist id="chapter-suggestions">
                        @foreach ($this->chapters() as $existing)
                            <option value="{{ $existing }}"></option>
                        @endforeach
                    </datalist>
                    @error('chapter') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="status" class="label">{{ __('Statut') }}</label>
                    <select id="status" wire:model="status" class="field">
                        @foreach (\App\Enums\SceneStatus::cases() as $sceneStatus)
                            <option value="{{ $sceneStatus->value }}">{{ $sceneStatus->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <x-tags-input :existing="$this->existingTags" />
                </div>
            </div>

            <section class="rounded-xl border border-flow/30 bg-white p-6 shadow-sm">
                <h2 class="mb-1 font-semibold text-flow">{{ __('Préparation') }}</h2>
                <p class="mb-4 text-sm text-stone-600">{{ __('Ce qui se passe, ce que veulent les PNJ, les indices à placer. Réservé au MJ.') }}</p>
                <label for="description" class="label sr-only">{{ __('Préparation') }}</label>
                <x-link-textarea id="description" model="description" rows="8" />
                @error('description') <p class="error">{{ $message }}</p> @enderror
            </section>

            <section class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="mb-1 font-semibold">{{ __('Fiches de la scène') }}</h2>
                <p class="mb-4 text-sm text-stone-600">{{ __('Lieux, PNJ, objets… Ils apparaîtront sous vos yeux quand la scène sera en cours.') }}</p>

                @if ($linked)
                    <ul class="mb-4 divide-y divide-stone-100 rounded-lg border border-stone-200">
                        @foreach ($linked as $index => $row)
                            <li wire:key="linked-{{ $row['id'] }}" class="flex flex-wrap items-center gap-3 px-3 py-2">
                                <span class="min-w-40 font-medium">{{ $row['name'] }} <span class="text-xs font-normal text-stone-500">{{ $row['type'] }}</span></span>
                                <label class="sr-only" for="note-{{ $row['id'] }}">{{ __('Précision pour :name', ['name' => $row['name']]) }}</label>
                                <input id="note-{{ $row['id'] }}" type="text" wire:model="linked.{{ $index }}.note" class="field min-w-0 flex-1 py-1 text-sm" placeholder="{{ __('Précision (facultatif) : caché à la cave, arrive plus tard…') }}">
                                <button type="button" wire:click="removeEntity({{ $index }})" class="text-sm text-red-700 hover:underline" aria-label="{{ __('Retirer :name', ['name' => $row['name']]) }}">{{ __('Retirer') }}</button>
                            </li>
                        @endforeach
                    </ul>
                    @error('linked.*.note') <p class="error">{{ $message }}</p> @enderror
                @endif

                <div class="flex flex-col gap-2 sm:flex-row sm:items-start">
                    <div class="min-w-0 flex-1">
                        <label for="picked" class="sr-only">{{ __('Ajouter une fiche') }}</label>
                        <x-entity-picker id="picked" model="pickedEntityId" />
                        @error('pickedEntityId') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    <button type="button" wire:click="addEntity" class="btn-secondary">{{ __('Ajouter') }}</button>
                </div>
            </section>

            <div class="grid gap-6 md:grid-cols-2">
                <section class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                    <h2 class="mb-1 font-semibold">{{ __('Règles utiles') }}</h2>
                    <p class="mb-4 text-sm text-stone-600">{{ __('Les points de règle à avoir sous la main pendant la scène.') }}</p>
                    @if ($ruleIds)
                        <ul class="mb-3 space-y-1 text-sm">
                            @foreach ($ruleIds as $ruleId)
                                @continue(! $rules->has($ruleId))
                                <li wire:key="rule-{{ $ruleId }}" class="flex items-center gap-2">
                                    <span class="min-w-0 flex-1">{{ $rules[$ruleId]->title }}</span>
                                    <button type="button" wire:click="removeRule({{ $ruleId }})" class="text-red-700 hover:underline" aria-label="{{ __('Retirer :name', ['name' => $rules[$ruleId]->title]) }}">{{ __('Retirer') }}</button>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                    @if ($rules->except($ruleIds)->isEmpty())
                        <p class="text-sm text-stone-500">{{ $rules->isEmpty() ? __("Aucune règle dans la campagne pour l'instant.") : __('Toutes les règles sont liées.') }}</p>
                    @else
                        <div class="flex gap-2">
                            <label for="pickedRuleId" class="sr-only">{{ __('Lier une règle') }}</label>
                            <select id="pickedRuleId" wire:model="pickedRuleId" class="field min-w-0 flex-1 py-1.5 text-sm">
                                <option value="">{{ __('Choisir une règle…') }}</option>
                                @foreach ($rules->except($ruleIds) as $option)
                                    <option value="{{ $option->id }}">{{ $option->title }}{{ $option->category ? ' · '.$option->category : '' }}</option>
                                @endforeach
                            </select>
                            <button type="button" wire:click="addRule" class="btn-secondary min-h-0 py-1 text-sm">{{ __('Lier') }}</button>
                        </div>
                    @endif
                </section>

                <section class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                    <h2 class="mb-1 font-semibold">{{ __('Documents') }}</h2>
                    <p class="mb-4 text-sm text-stone-600">{{ __('Cartes, lettres, handouts : ouverts en un clic pendant la session.') }}</p>
                    @if ($documentIds)
                        <ul class="mb-3 space-y-1 text-sm">
                            @foreach ($documentIds as $documentId)
                                @continue(! $documents->has($documentId))
                                <li wire:key="doc-{{ $documentId }}" class="flex items-center gap-2">
                                    <span class="min-w-0 flex-1">{{ $documents[$documentId]->title }}</span>
                                    <button type="button" wire:click="removeDocument({{ $documentId }})" class="text-red-700 hover:underline" aria-label="{{ __('Retirer :name', ['name' => $documents[$documentId]->title]) }}">{{ __('Retirer') }}</button>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                    @if ($documents->except($documentIds)->isEmpty())
                        <p class="text-sm text-stone-500">{{ $documents->isEmpty() ? __("Aucun document dans la campagne pour l'instant.") : __('Tous les documents sont liés.') }}</p>
                    @else
                        <div class="flex gap-2">
                            <label for="pickedDocumentId" class="sr-only">{{ __('Lier un document') }}</label>
                            <select id="pickedDocumentId" wire:model="pickedDocumentId" class="field min-w-0 flex-1 py-1.5 text-sm">
                                <option value="">{{ __('Choisir un document…') }}</option>
                                @foreach ($documents->except($documentIds) as $option)
                                    <option value="{{ $option->id }}">{{ $option->title }}</option>
                                @endforeach
                            </select>
                            <button type="button" wire:click="addDocument" class="btn-secondary min-h-0 py-1 text-sm">{{ __('Lier') }}</button>
                        </div>
                    @endif
                </section>

                <section class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                    <h2 class="mb-1 font-semibold">{{ __('Sons') }}</h2>
                    <p class="mb-4 text-sm text-stone-600">{{ __('Musiques et ambiances proposées en tête du lecteur quand la scène est en cours.') }}</p>
                    @if ($trackIds)
                        <ul class="mb-3 space-y-1 text-sm">
                            @foreach ($trackIds as $trackId)
                                @continue(! $tracks->has($trackId))
                                <li wire:key="track-{{ $trackId }}" class="flex items-center gap-2">
                                    <span class="min-w-0 flex-1">{{ $tracks[$trackId]->title }}</span>
                                    <button type="button" wire:click="removeTrack({{ $trackId }})" class="text-red-700 hover:underline" aria-label="{{ __('Retirer :name', ['name' => $tracks[$trackId]->title]) }}">{{ __('Retirer') }}</button>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                    @if ($tracks->except($trackIds)->isEmpty())
                        <p class="text-sm text-stone-500">
                            @if ($tracks->isEmpty())
                                {{ __('Aucun son dans la campagne pour l’instant.') }} <a href="{{ route('audio.index', $campaign) }}" class="link" wire:navigate>{{ __('Ajouter des sons') }}</a>
                            @else
                                {{ __('Tous les sons sont liés.') }}
                            @endif
                        </p>
                    @else
                        <div class="flex gap-2">
                            <label for="pickedTrackId" class="sr-only">{{ __('Lier un son') }}</label>
                            <select id="pickedTrackId" wire:model="pickedTrackId" class="field min-w-0 flex-1 py-1.5 text-sm">
                                <option value="">{{ __('Choisir un son…') }}</option>
                                @foreach ($tracks->except($trackIds) as $option)
                                    <option value="{{ $option->id }}">{{ $option->title }}</option>
                                @endforeach
                            </select>
                            <button type="button" wire:click="addTrack" class="btn-secondary min-h-0 py-1 text-sm">{{ __('Lier') }}</button>
                        </div>
                    @endif
                </section>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="btn-primary">{{ __('Enregistrer') }}</button>
                <a href="{{ $scene ? route('scenes.show', [$campaign, $scene]) : route('scenarios.index', $campaign) }}" class="btn-secondary" wire:navigate>{{ __('Annuler') }}</a>
            </div>
        </form>
    @endif
</div>
