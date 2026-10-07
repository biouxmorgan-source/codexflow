<div>
    <nav class="mb-2 text-sm text-stone-500">
        <a href="{{ route('campaigns.index') }}" class="crumb" wire:navigate>{{ __('Mes campagnes') }}</a>
        › <a href="{{ route('campaigns.show', $campaign) }}" class="crumb" wire:navigate>{{ $campaign->name }}</a>
    </nav>

    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold">{{ __('Scénarios') }}</h1>
            <p class="mt-1 text-sm text-stone-600">{{ __('Découpez la campagne en scénarios, regroupez les scènes par chapitre si vous le souhaitez, et suivez ce qui a été joué.') }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('imports.create', [$campaign, 'mode' => 'scenes']) }}" class="btn-secondary" wire:navigate>{{ __('Importer') }}</a>
            <a href="{{ route('exports.download', [$campaign, 'scenes']) }}" class="btn-secondary">{{ __('Exporter') }}</a>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            @if ($this->filterTag)
                <p class="flex flex-wrap items-center gap-2 rounded-lg bg-codex-soft px-4 py-2 text-sm">
                    {{ __('Scènes avec le tag') }} <x-tag :tag="$this->filterTag" />
                    <button type="button" wire:click="$set('tag', null)" class="link">{{ __('Tout afficher') }}</button>
                </p>
            @endif
            @forelse ($this->scenarios as $scenario)
                <section wire:key="scenario-{{ $scenario->id }}" @class(['rounded-xl border bg-white p-5 shadow-sm', 'border-codex' => $editingId === $scenario->id, 'border-stone-200' => $editingId !== $scenario->id])>
                    <div class="flex flex-wrap items-start gap-2">
                        <div class="min-w-0 flex-1">
                            <h2 class="text-lg font-semibold">{{ $scenario->name }}</h2>
                            @if ($scenario->summary)
                                <p class="mt-1 text-sm text-stone-600">{{ $scenario->summary }}</p>
                            @endif
                        </div>
                        <span class="flex shrink-0 items-center gap-1 text-sm">
                            <button type="button" wire:click="moveScenario({{ $scenario->id }}, -1)" class="rounded px-2 py-1 text-stone-600 hover:bg-stone-100" aria-label="{{ __('Monter :name', ['name' => $scenario->name]) }}">↑</button>
                            <button type="button" wire:click="moveScenario({{ $scenario->id }}, 1)" class="rounded px-2 py-1 text-stone-600 hover:bg-stone-100" aria-label="{{ __('Descendre :name', ['name' => $scenario->name]) }}">↓</button>
                            <button type="button" wire:click="edit({{ $scenario->id }})" class="rounded px-2 py-1 link">{{ __('Modifier') }}</button>
                            <button type="button" wire:click="delete({{ $scenario->id }})" wire:confirm="{{ __('Supprimer le scénario :name et ses :count scène(s) ? Les fiches liées sont conservées.', ['name' => $scenario->name, 'count' => $scenario->scenes->count()]) }}" class="rounded px-2 py-1 text-red-700 hover:underline">{{ __('Supprimer') }}</button>
                        </span>
                    </div>

                    @php($shownScenes = $this->filterTag ? $scenario->scenes->filter(fn ($scene) => $scene->tags->contains($this->filterTag)) : $scenario->scenes)
                    @if ($shownScenes->isNotEmpty())
                        <div class="mt-4 space-y-3">
                            @foreach ($shownScenes->groupBy(fn ($scene) => (string) $scene->chapter) as $chapter => $scenes)
                                <div wire:key="chapter-{{ $scenario->id }}-{{ $chapter }}">
                                    @if ($chapter !== '')
                                        <h3 class="mb-1 text-xs font-semibold tracking-wide text-stone-500 uppercase">{{ $chapter }}</h3>
                                    @endif
                                    <ul class="divide-y divide-stone-100 rounded-lg border border-stone-200">
                                        @foreach ($scenes as $scene)
                                            <li wire:key="scene-{{ $scene->id }}" class="flex flex-wrap items-center gap-2 px-3 py-2">
                                                <span class="min-w-0 flex-1">
                                                    <a href="{{ route('scenes.show', [$campaign, $scene]) }}" class="block truncate font-medium link" wire:navigate>{{ $scene->name }}</a>
                                                    @if ($scene->tags->isNotEmpty())
                                                        <span class="mt-0.5 flex flex-wrap gap-1 text-xs">
                                                            @foreach ($scene->tags as $sceneTag)
                                                                <x-tag :tag="$sceneTag" compact />
                                                            @endforeach
                                                        </span>
                                                    @endif
                                                </span>
                                                <label class="sr-only" for="status-{{ $scene->id }}">{{ __('Statut de :name', ['name' => $scene->name]) }}</label>
                                                <select id="status-{{ $scene->id }}" wire:change="setStatus({{ $scene->id }}, $event.target.value)" class="rounded-full border-0 py-0.5 pr-7 pl-2 text-xs font-medium {{ $scene->status->badge() }}">
                                                    @foreach (\App\Enums\SceneStatus::cases() as $status)
                                                        <option value="{{ $status->value }}" @selected($scene->status === $status)>{{ $status->label() }}</option>
                                                    @endforeach
                                                </select>
                                                <button type="button" wire:click="moveScene({{ $scene->id }}, -1)" class="rounded px-1.5 text-stone-500 hover:bg-stone-100" aria-label="{{ __('Monter :name', ['name' => $scene->name]) }}">↑</button>
                                                <button type="button" wire:click="moveScene({{ $scene->id }}, 1)" class="rounded px-1.5 text-stone-500 hover:bg-stone-100" aria-label="{{ __('Descendre :name', ['name' => $scene->name]) }}">↓</button>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <a href="{{ route('scenes.create', [$campaign, 'scenario' => $scenario->id]) }}" class="mt-3 inline-block text-sm font-medium link" wire:navigate>{{ __('+ Nouvelle scène') }}</a>
                </section>
            @empty
                <div class="rounded-xl border border-dashed border-stone-300 bg-white p-10 text-center">
                    <p class="text-lg font-medium">{{ __("Aucun scénario pour l'instant.") }}</p>
                    <p class="mt-1 text-stone-600">{{ __('Créez-en un à droite, puis ajoutez-y des scènes.') }}</p>
                </div>
            @endforelse
        </div>

        <form wire:submit="save" class="space-y-4 self-start rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
            <h2 class="font-semibold">{{ $editingId ? __('Modifier le scénario') : __('Nouveau scénario') }}</h2>
            <div>
                <label for="name" class="label">{{ __('Nom') }}</label>
                <input id="name" type="text" wire:model="name" class="field" placeholder="{{ __('L\'incendie du Poney fringant') }}">
                @error('name') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="summary" class="label">{{ __('Résumé') }} <span class="font-normal text-stone-500">{{ __('(facultatif)') }}</span></label>
                <textarea id="summary" wire:model="summary" rows="3" class="field"></textarea>
                @error('summary') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div class="flex gap-3">
                <button type="submit" class="btn-primary">{{ $editingId ? __('Enregistrer') : __('Créer') }}</button>
                @if ($editingId)
                    <button type="button" wire:click="cancel" class="btn-secondary">{{ __('Annuler') }}</button>
                @endif
            </div>
        </form>
    </div>
</div>
