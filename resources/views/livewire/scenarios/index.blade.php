<div>
    <nav class="mb-2 text-sm text-stone-500">
        <a href="{{ route('campaigns.index') }}" class="crumb" wire:navigate>Mes campagnes</a>
        › <a href="{{ route('campaigns.show', $campaign) }}" class="crumb" wire:navigate>{{ $campaign->name }}</a>
    </nav>

    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold">Scénarios</h1>
            <p class="mt-1 text-sm text-stone-600">Découpez la campagne en scénarios, regroupez les scènes par chapitre si vous le souhaitez, et suivez ce qui a été joué.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('imports.create', [$campaign, 'mode' => 'scenes']) }}" class="btn-secondary" wire:navigate>Importer</a>
            <a href="{{ route('exports.download', [$campaign, 'scenes']) }}" class="btn-secondary">Exporter</a>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
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
                            <button type="button" wire:click="moveScenario({{ $scenario->id }}, -1)" class="rounded px-2 py-1 text-stone-600 hover:bg-stone-100" aria-label="Monter {{ $scenario->name }}">↑</button>
                            <button type="button" wire:click="moveScenario({{ $scenario->id }}, 1)" class="rounded px-2 py-1 text-stone-600 hover:bg-stone-100" aria-label="Descendre {{ $scenario->name }}">↓</button>
                            <button type="button" wire:click="edit({{ $scenario->id }})" class="rounded px-2 py-1 link">Modifier</button>
                            <button type="button" wire:click="delete({{ $scenario->id }})" wire:confirm="Supprimer le scénario {{ $scenario->name }} et ses {{ $scenario->scenes->count() }} scène(s) ? Les fiches liées sont conservées." class="rounded px-2 py-1 text-red-700 hover:underline">Supprimer</button>
                        </span>
                    </div>

                    @if ($scenario->scenes->isNotEmpty())
                        <div class="mt-4 space-y-3">
                            @foreach ($scenario->scenes->groupBy(fn ($scene) => (string) $scene->chapter) as $chapter => $scenes)
                                <div wire:key="chapter-{{ $scenario->id }}-{{ $chapter }}">
                                    @if ($chapter !== '')
                                        <h3 class="mb-1 text-xs font-semibold tracking-wide text-stone-500 uppercase">{{ $chapter }}</h3>
                                    @endif
                                    <ul class="divide-y divide-stone-100 rounded-lg border border-stone-200">
                                        @foreach ($scenes as $scene)
                                            <li wire:key="scene-{{ $scene->id }}" class="flex flex-wrap items-center gap-2 px-3 py-2">
                                                <a href="{{ route('scenes.show', [$campaign, $scene]) }}" class="min-w-0 flex-1 truncate font-medium link" wire:navigate>{{ $scene->name }}</a>
                                                <label class="sr-only" for="status-{{ $scene->id }}">Statut de {{ $scene->name }}</label>
                                                <select id="status-{{ $scene->id }}" wire:change="setStatus({{ $scene->id }}, $event.target.value)" class="rounded-full border-0 py-0.5 pr-7 pl-2 text-xs font-medium {{ $scene->status->badge() }}">
                                                    @foreach (\App\Enums\SceneStatus::cases() as $status)
                                                        <option value="{{ $status->value }}" @selected($scene->status === $status)>{{ $status->label() }}</option>
                                                    @endforeach
                                                </select>
                                                <button type="button" wire:click="moveScene({{ $scene->id }}, -1)" class="rounded px-1.5 text-stone-500 hover:bg-stone-100" aria-label="Monter {{ $scene->name }}">↑</button>
                                                <button type="button" wire:click="moveScene({{ $scene->id }}, 1)" class="rounded px-1.5 text-stone-500 hover:bg-stone-100" aria-label="Descendre {{ $scene->name }}">↓</button>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <a href="{{ route('scenes.create', [$campaign, 'scenario' => $scenario->id]) }}" class="mt-3 inline-block text-sm font-medium link" wire:navigate>+ Nouvelle scène</a>
                </section>
            @empty
                <div class="rounded-xl border border-dashed border-stone-300 bg-white p-10 text-center">
                    <p class="text-lg font-medium">Aucun scénario pour l'instant.</p>
                    <p class="mt-1 text-stone-600">Créez-en un à droite, puis ajoutez-y des scènes.</p>
                </div>
            @endforelse
        </div>

        <form wire:submit="save" class="space-y-4 self-start rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
            <h2 class="font-semibold">{{ $editingId ? 'Modifier le scénario' : 'Nouveau scénario' }}</h2>
            <div>
                <label for="name" class="label">Nom</label>
                <input id="name" type="text" wire:model="name" class="field" placeholder="L'incendie du Poney fringant">
                @error('name') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="summary" class="label">Résumé <span class="font-normal text-stone-500">(facultatif)</span></label>
                <textarea id="summary" wire:model="summary" rows="3" class="field"></textarea>
                @error('summary') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div class="flex gap-3">
                <button type="submit" class="btn-primary">{{ $editingId ? 'Enregistrer' : 'Créer' }}</button>
                @if ($editingId)
                    <button type="button" wire:click="cancel" class="btn-secondary">Annuler</button>
                @endif
            </div>
        </form>
    </div>
</div>
