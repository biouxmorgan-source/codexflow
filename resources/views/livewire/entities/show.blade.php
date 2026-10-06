<div>
    <nav class="mb-2 text-sm text-stone-500">
        <a href="{{ route('campaigns.index') }}" class="hover:text-codex" wire:navigate>Mes campagnes</a>
        › <a href="{{ route('campaigns.show', $campaign) }}" class="hover:text-codex" wire:navigate>{{ $campaign->name }}</a>
    </nav>

    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold">{{ $entity->name }}</h1>
            <p class="mt-1 text-sm text-stone-600">
                {{ $entity->type->name }} ·
                {{ $entity->isWorldEntity() ? 'Monde « '.$entity->world->name.' »' : 'Propre à cette campagne' }}
            </p>
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
                    <div class="whitespace-pre-line text-stone-700">{{ $entity->description }}</div>
                @elseif (! $entity->summary)
                    <p class="text-sm text-stone-500">Rien pour l'instant.</p>
                @endif
            </section>

            <section class="rounded-xl border border-flow/30 bg-white p-6 shadow-sm">
                <h2 class="mb-3 font-semibold text-flow">Zone MJ</h2>
                @if ($entity->gm_notes)
                    <div class="whitespace-pre-line text-stone-700">{{ $entity->gm_notes }}</div>
                @else
                    <p class="text-sm text-stone-500">Aucune note MJ.</p>
                @endif
            </section>
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
