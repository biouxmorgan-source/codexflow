<div>
    <nav class="mb-2 text-sm text-stone-500">
        <a href="{{ route('campaigns.index') }}" class="crumb" wire:navigate>Mes campagnes</a>
        › <a href="{{ route('campaigns.show', $campaign) }}" class="crumb" wire:navigate>{{ $campaign->name }}</a>
        › <a href="{{ route('documents.index', $campaign) }}" class="crumb" wire:navigate>Documents</a>
    </nav>

    <div class="mb-4 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold">{{ $document->title }}</h1>
            <p class="text-sm text-stone-500">{{ $document->original_name }} · {{ $document->humanSize() }} · {{ $document->scopeLabel() }}</p>
        </div>
        <a href="{{ route('documents.file', $document) }}" target="_blank" rel="noopener" class="btn-secondary">Ouvrir dans un onglet ↗</a>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="overflow-hidden rounded-xl border border-stone-200 bg-white shadow-sm lg:col-span-2">
            @if ($document->isImage())
                <img src="{{ route('documents.file', $document) }}" alt="{{ $document->title }}" class="mx-auto max-h-[75vh] w-auto">
            @elseif ($document->isPdf())
                <iframe src="{{ route('documents.file', $document) }}" title="{{ $document->title }}" class="h-[75vh] w-full"></iframe>
            @endif
        </section>

        <aside class="space-y-6">
            <form wire:submit="save" class="space-y-4 rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                <div>
                    <label for="title" class="label">Titre</label>
                    <input id="title" type="text" wire:model="title" class="field">
                    @error('title') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="description" class="label">Description <span class="font-normal text-stone-500">(facultatif)</span></label>
                    <textarea id="description" wire:model="description" rows="3" class="field"></textarea>
                    @error('description') <p class="error">{{ $message }}</p> @enderror
                </div>
                <fieldset>
                    <legend class="label">Visibilité</legend>
                    <label class="flex items-center gap-2 text-sm"><input type="radio" wire:model="zone" value="gm"> MJ seulement</label>
                    <label class="flex items-center gap-2 text-sm"><input type="radio" wire:model="zone" value="public"> Consultable par les joueurs</label>
                </fieldset>
                <x-tags-input />
                <div class="flex items-center gap-3">
                    <button type="submit" class="btn-primary">Enregistrer</button>
                    @if ($saved)
                        <span class="text-sm text-emerald-700" wire:transition>Enregistré.</span>
                    @endif
                </div>
            </form>

            <section class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="mb-2 font-semibold">Utilisé par</h2>
                @if ($scenes->isEmpty() && $entities->isEmpty() && $rules->isEmpty())
                    <p class="text-sm text-stone-500">Rien pour l'instant. Liez ce document depuis une scène, une fiche ou une règle.</p>
                @else
                    <ul class="space-y-1 text-sm">
                        @foreach ($scenes as $scene)
                            <li><span class="text-stone-500">Scène ·</span> <a href="{{ route('scenes.show', [$campaign, $scene]) }}" class="link" wire:navigate>{{ $scene->name }}</a></li>
                        @endforeach
                        @foreach ($entities as $entity)
                            <li><span class="text-stone-500">Fiche ·</span> <a href="{{ route('entities.show', [$campaign, $entity]) }}" class="link" wire:navigate>{{ $entity->name }}</a></li>
                        @endforeach
                        @foreach ($rules as $rule)
                            <li><span class="text-stone-500">Règle ·</span> <a href="{{ route('rules.show', [$campaign, $rule]) }}" class="link" wire:navigate>{{ $rule->title }}</a></li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <div class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="mb-2 font-semibold">Supprimer</h2>
                <p class="mb-3 text-sm text-stone-600">Le fichier disparaît partout où il est lié{{ $document->campaign_id ? '' : ', dans toutes les campagnes' }}.</p>
                <button type="button" wire:click="delete" wire:confirm="Supprimer le document {{ $document->title }} ?" class="text-sm font-medium text-red-700 hover:underline">Supprimer le document</button>
            </div>
        </aside>
    </div>
</div>
