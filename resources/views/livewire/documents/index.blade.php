<div>
    <nav class="mb-2 text-sm text-stone-500">
        <a href="{{ route('campaigns.index') }}" class="hover:text-codex" wire:navigate>Mes campagnes</a>
        › <a href="{{ route('campaigns.show', $campaign) }}" class="hover:text-codex" wire:navigate>{{ $campaign->name }}</a>
    </nav>

    <h1 class="text-2xl font-semibold">Documents</h1>
    <p class="mb-6 text-sm text-stone-600">PDF et images de la campagne, de son monde et de son jeu. Liez-les ensuite à une scène, une fiche ou une règle.</p>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="mb-4 flex flex-wrap items-center gap-3">
                <label for="kind" class="sr-only">Type</label>
                <select id="kind" wire:model.live="kind" class="field w-auto py-1.5 text-sm">
                    <option value="">PDF et images</option>
                    <option value="pdf">PDF</option>
                    <option value="image">Images</option>
                </select>
                @foreach ($this->tagNames as $tagName)
                    <button type="button" wire:click="$set('tag', @js($tag === $tagName ? '' : $tagName))"
                        @class(['rounded-full px-2.5 py-0.5 text-sm', 'bg-codex text-white' => $tag === $tagName, 'bg-stone-100 text-stone-700 hover:bg-codex-soft' => $tag !== $tagName])>{{ $tagName }}</button>
                @endforeach
            </div>

            @if ($this->documents->isEmpty())
                <div class="rounded-xl border border-dashed border-stone-300 bg-white p-10 text-center">
                    <p class="text-stone-600">{{ $kind !== '' || $tag !== '' ? 'Aucun document ne correspond à ce filtre.' : 'Aucun document pour l\'instant. Téléversez une carte, une lettre ou un PDF de règles.' }}</p>
                </div>
            @else
                <ul class="grid gap-3 sm:grid-cols-2">
                    @foreach ($this->documents as $document)
                        <li wire:key="doc-{{ $document->id }}">
                            <a href="{{ route('documents.show', [$campaign, $document]) }}" class="flex h-full gap-3 rounded-xl border border-stone-200 bg-white p-3 shadow-sm hover:border-codex/40" wire:navigate>
                                @if ($document->isImage())
                                    <img src="{{ route('documents.file', $document) }}" alt="" loading="lazy" class="h-16 w-16 shrink-0 rounded-lg object-cover">
                                @else
                                    <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-lg bg-red-50 text-xs font-semibold text-red-700" aria-hidden="true">PDF</span>
                                @endif
                                <span class="min-w-0">
                                    <span class="block truncate font-medium text-codex">{{ $document->title }}</span>
                                    <span class="block text-xs text-stone-500">{{ $document->scopeLabel() }} · {{ $document->zone === \App\Enums\Zone::Public ? 'Joueurs' : 'MJ seulement' }} · {{ $document->humanSize() }}</span>
                                    @if ($document->tags->isNotEmpty())
                                        <span class="mt-1 block truncate text-xs text-stone-600">{{ $document->tags->pluck('name')->implode(', ') }}</span>
                                    @endif
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <form wire:submit="saveUploads" class="space-y-4 self-start rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
            <h2 class="font-semibold">Téléverser</h2>
            <div>
                <label for="uploads" class="label">Fichiers <span class="font-normal text-stone-500">(PDF ou images, 50 Mo max.)</span></label>
                <input id="uploads" type="file" wire:model="uploads" multiple accept=".pdf,image/*" class="block w-full text-sm">
                <div wire:loading wire:target="uploads" class="mt-1 text-xs text-stone-500">Envoi en cours…</div>
                @error('uploads') <p class="error">{{ $message }}</p> @enderror
                @error('uploads.*') <p class="error">{{ $message }}</p> @enderror
            </div>
            <fieldset>
                <legend class="label">Ranger dans</legend>
                @foreach ($this->scopes() as $value => $label)
                    <label class="flex items-center gap-2 text-sm"><input type="radio" wire:model="scope" value="{{ $value }}"> {{ $label }}</label>
                @endforeach
            </fieldset>
            <fieldset>
                <legend class="label">Visibilité</legend>
                <label class="flex items-center gap-2 text-sm"><input type="radio" wire:model="zone" value="gm"> MJ seulement</label>
                <label class="flex items-center gap-2 text-sm"><input type="radio" wire:model="zone" value="public"> Consultable par les joueurs</label>
            </fieldset>
            <x-tags-input id="uploadTags" :existing="$this->tagNames" />
            <button type="submit" class="btn-primary" wire:loading.attr="disabled" wire:target="uploads,saveUploads">Téléverser</button>
        </form>
    </div>
</div>
