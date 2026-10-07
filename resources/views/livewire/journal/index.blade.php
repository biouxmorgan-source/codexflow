<div>
    <nav class="mb-2 text-sm text-stone-500">
        <a href="{{ route('campaigns.index') }}" class="crumb" wire:navigate>Mes campagnes</a>
        › <a href="{{ route('campaigns.show', $campaign) }}" class="crumb" wire:navigate>{{ $campaign->name }}</a>
    </nav>

    <h1 class="text-2xl font-semibold">Journal des modifications</h1>
    <p class="mt-1 mb-6 text-sm text-stone-600">
        Tout ce qui a été créé, modifié ou supprimé dans la campagne, son monde et son jeu, avec l'ancienne et la nouvelle valeur.
        Visible par vous seul : le journal contient la zone MJ.
    </p>

    <div class="mb-4 flex flex-wrap items-end gap-3">
        <div>
            <label for="journal-search" class="sr-only">Rechercher un élément</label>
            <input id="journal-search" type="search" wire:model.live.debounce.300ms="search" placeholder="Nom de l'élément…" class="field">
        </div>
        <div>
            <label for="journal-type" class="sr-only">Type d'élément</label>
            <select id="journal-type" wire:model.live="type" class="field">
                <option value="">Tous les éléments</option>
                @foreach (\App\Models\ActivityLog::SUBJECTS as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        @if ($subject !== '')
            <button type="button" wire:click="$set('subject', '')" class="btn-secondary">Voir tout le journal</button>
        @endif
    </div>

    @php($entries = $this->entries)
    @if ($entries->isEmpty())
        <div class="rounded-xl border border-dashed border-stone-300 bg-white p-10 text-center">
            <p class="text-lg font-medium">Rien dans le journal pour l'instant.</p>
            <p class="mt-1 text-stone-600">Chaque création, modification ou suppression de fiche, scène, règle, document ou champ s'inscrira ici.</p>
        </div>
    @else
        <ol class="space-y-3">
            @foreach ($entries->getCollection()->chunkWhile(fn ($entry, $key, $chunk) => $entry->batch !== null && $entry->batch === $chunk->last()->batch) as $group)
                @php($first = $group->first())
                @if ($group->count() > 1)
                    {{-- Opération groupée (import) : une ligne, détails dépliables. --}}
                    <li wire:key="batch-{{ $first->batch }}-{{ $first->id }}" class="rounded-xl border border-stone-200 bg-white p-4 shadow-sm">
                        <details>
                            <summary class="flex flex-wrap items-baseline gap-x-2">
                                <span class="text-sm text-stone-500">{{ $first->created_at->locale('fr')->isoFormat('D MMM YYYY, HH:mm') }}</span>
                                <span><span class="font-medium">{{ $first->user?->name ?? 'Système' }}</span> a importé {{ $group->count() }} éléments</span>
                                <span class="text-sm text-stone-500">({{ $group->groupBy('subject_type')->map(fn ($items, $type) => $items->count().' '.mb_strtolower(\App\Models\ActivityLog::SUBJECTS[$type] ?? $type))->implode(', ') }})</span>
                                <span class="link text-sm">Détails</span>
                            </summary>
                            <ul class="mt-3 space-y-2 border-t border-stone-100 pt-3">
                                @foreach ($group as $entry)
                                    @include('livewire.journal.entry', ['entry' => $entry, 'compact' => true])
                                @endforeach
                            </ul>
                        </details>
                    </li>
                @else
                    <li wire:key="entry-{{ $first->id }}" class="rounded-xl border border-stone-200 bg-white p-4 shadow-sm">
                        <ul>
                            @include('livewire.journal.entry', ['entry' => $first, 'compact' => false])
                        </ul>
                    </li>
                @endif
            @endforeach
        </ol>

        <div class="mt-6">{{ $entries->links() }}</div>
    @endif
</div>
