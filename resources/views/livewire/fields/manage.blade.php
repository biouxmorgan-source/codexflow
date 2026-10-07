<div>
    <nav class="mb-2 text-sm text-stone-500">
        <a href="{{ route('campaigns.index') }}" class="crumb" wire:navigate>{{ __('Mes campagnes') }}</a>
        › <a href="{{ route('campaigns.show', $campaign) }}" class="crumb" wire:navigate>{{ $campaign->name }}</a>
    </nav>

    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold">{{ __('Champs du jeu :name', ['name' => $this->gameSystem->name]) }}</h1>
            <p class="mt-1 text-sm text-stone-600">{{ __('Nommez vos caractéristiques, compétences ou capacités. Elles apparaîtront sur les fiches de toutes les campagnes de ce jeu.') }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('imports.create', [$campaign, 'mode' => 'fields']) }}" class="btn-secondary" wire:navigate>{{ __('Importer depuis un fichier') }}</a>
            <a href="{{ route('exports.download', [$campaign, 'champs']) }}" class="btn-secondary">{{ __('Exporter') }}</a>
        </div>
    </div>

    @if (session('status'))
        <p class="mb-6 rounded-md bg-codex-soft px-3 py-2 text-sm text-codex">{{ session('status') }}</p>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="lg:col-span-2">
            @if ($this->definitions->isEmpty())
                <div class="rounded-xl border border-dashed border-stone-300 bg-white p-10 text-center">
                    <p class="text-lg font-medium">{{ __("Aucun champ pour l'instant.") }}</p>
                    <p class="mt-1 text-stone-600">{{ __('Ajoutez-en un à droite, ou importez toute une liste depuis un fichier.') }}</p>
                </div>
            @else
                <div class="space-y-6">
                    @foreach ($this->definitions->groupBy(fn ($definition) => $definition->groupLabel()) as $groupName => $definitions)
                        <div wire:key="group-{{ $groupName }}">
                            <h2 class="mb-2 font-semibold">{{ $groupName }}</h2>
                            <ul class="divide-y divide-stone-200 overflow-hidden rounded-xl border border-stone-200 bg-white">
                                @foreach ($definitions as $definition)
                                    <li wire:key="definition-{{ $definition->id }}" @class(['flex flex-wrap items-center gap-3 px-4 py-3', 'bg-codex-soft' => $editingId === $definition->id])>
                                        <span class="min-w-0 flex-1">
                                            <span class="block font-medium">{{ $definition->name }}</span>
                                            <span class="block text-sm text-stone-600">
                                                {{ $definition->type->label() }}@if ($definition->options) : {{ implode(', ', $definition->options) }}@endif
                                            </span>
                                        </span>
                                        <span @class(['shrink-0 rounded-full px-2 py-0.5 text-xs font-medium', 'bg-codex/10 text-codex' => $definition->zone === \App\Enums\Zone::Public, 'bg-flow/10 text-flow' => $definition->zone === \App\Enums\Zone::GameMaster])>{{ $definition->zone->label() }}</span>
                                        @if ($definition->player_editable)
                                            <span class="shrink-0 rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-800">{{ __('Joueur') }}</span>
                                        @endif
                                        <span class="shrink-0 rounded-full bg-stone-100 px-2 py-0.5 text-xs text-stone-600">{{ $definition->entityType?->name ?? __('Tous les types') }}</span>
                                        <span class="flex shrink-0 items-center gap-1 text-sm">
                                            <button type="button" wire:click="move({{ $definition->id }}, -1)" class="rounded px-2 py-1 text-stone-600 hover:bg-stone-100" aria-label="{{ __('Monter :name', ['name' => $definition->name]) }}">↑</button>
                                            <button type="button" wire:click="move({{ $definition->id }}, 1)" class="rounded px-2 py-1 text-stone-600 hover:bg-stone-100" aria-label="{{ __('Descendre :name', ['name' => $definition->name]) }}">↓</button>
                                            <button type="button" wire:click="edit({{ $definition->id }})" class="rounded px-2 py-1 link">{{ __('Modifier') }}</button>
                                            <button type="button" wire:click="delete({{ $definition->id }})" wire:confirm="{{ __('Supprimer le champ :name et ses valeurs sur toutes les fiches ?', ['name' => $definition->name]) }}" class="rounded px-2 py-1 text-red-700 hover:underline">{{ __('Supprimer') }}</button>
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <aside>
            <form wire:submit="save" class="space-y-4 rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="font-semibold">{{ $editingId ? __('Modifier le champ') : __('Nouveau champ') }}</h2>
                <div>
                    <label for="name" class="label">{{ __('Nom') }}</label>
                    <input id="name" type="text" wire:model="name" class="field" placeholder="{{ __('Force, Discrétion, Points de vie…') }}">
                    @error('name') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="group" class="label">{{ __('Groupe') }} <span class="font-normal text-stone-500">{{ __('(facultatif)') }}</span></label>
                    <input id="group" type="text" wire:model="group" list="group-suggestions" class="field" placeholder="{{ __('Caractéristiques, Compétences…') }}">
                    <datalist id="group-suggestions">
                        @foreach ($this->groups as $existing)
                            <option value="{{ $existing }}">
                        @endforeach
                    </datalist>
                    @error('group') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="type" class="label">{{ __('Type') }}</label>
                    <select id="type" wire:model.live="type" class="field">
                        @foreach (\App\Enums\FieldType::cases() as $fieldType)
                            <option value="{{ $fieldType->value }}">{{ $fieldType->label() }}</option>
                        @endforeach
                    </select>
                    @error('type') <p class="error">{{ $message }}</p> @enderror
                </div>
                @if ($type === \App\Enums\FieldType::Select->value)
                    <div>
                        <label for="options" class="label">{{ __('Choix') }} <span class="font-normal text-stone-500">{{ __('(un par ligne)') }}</span></label>
                        <textarea id="options" wire:model="options" rows="4" class="field"></textarea>
                        @error('options') <p class="error">{{ $message }}</p> @enderror
                    </div>
                @endif
                <div>
                    <label for="zone" class="label">{{ __('Zone') }}</label>
                    <select id="zone" wire:model.live="zone" class="field">
                        <option value="public">{{ __('Zone publique') }}</option>
                        <option value="gm">{{ __('Zone MJ') }}</option>
                    </select>
                </div>
                @if ($zone === 'public')
                    <label class="flex items-start gap-2 text-sm">
                        <input type="checkbox" wire:model="playerEditable" class="mt-1">
                        <span>{{ __('Modifiable par le joueur') }} <span class="block text-xs text-stone-500">{{ __('Sur la fiche de son personnage : PV, munitions, argent…') }}</span></span>
                    </label>
                @endif
                <div>
                    <label for="entityTypeId" class="label">{{ __('Fiches concernées') }}</label>
                    <select id="entityTypeId" wire:model="entityTypeId" class="field">
                        <option value="">{{ __('Tous les types') }}</option>
                        @foreach ($this->types as $entityType)
                            <option value="{{ $entityType->id }}">{{ $entityType->name }}</option>
                        @endforeach
                    </select>
                    @error('entityTypeId') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="btn-primary">{{ $editingId ? __('Enregistrer') : __('Ajouter') }}</button>
                    @if ($editingId)
                        <button type="button" wire:click="cancel" class="btn-secondary">{{ __('Annuler') }}</button>
                    @endif
                </div>
            </form>

            <section class="mt-6 space-y-3 rounded-xl border border-stone-200 bg-white p-5 shadow-sm">
                <h2 class="font-semibold">{{ __('Modèle partageable') }}</h2>
                <p class="text-sm text-stone-600">{{ __('La structure du jeu dans un fichier : types de fiche, champs et étiquettes, et si vous voulez les règles du jeu. Sans fiche, scène ni document. Un autre MJ l’importe ici, dans son propre jeu.') }}</p>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('archives.template', $campaign) }}" class="btn-secondary">{{ __('Exporter le modèle') }}</a>
                    <a href="{{ route('archives.template', [$campaign, 'regles' => 1]) }}" class="btn-secondary">{{ __('Avec les règles') }}</a>
                </div>
                <form wire:submit="importTemplate" class="space-y-2 border-t border-stone-200 pt-3">
                    <label for="template" class="label">{{ __('Importer un modèle') }}</label>
                    <input id="template" type="file" wire:model="template" accept=".json,application/json" class="block w-full text-sm">
                    @error('template') <p class="error">{{ $message }}</p> @enderror
                    <p class="text-xs text-stone-500">{{ __('Les champs et règles qui existent déjà sous le même nom ne sont pas touchés.') }}</p>
                    <button type="submit" class="btn-primary" wire:loading.attr="disabled" wire:target="template,importTemplate">{{ __('Importer') }}</button>
                </form>
            </section>
        </aside>
    </div>
</div>
