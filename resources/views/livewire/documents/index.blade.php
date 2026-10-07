<div>
    <nav class="mb-2 text-sm text-stone-500">
        <a href="{{ route('campaigns.index') }}" class="crumb" wire:navigate>{{ __('Mes campagnes') }}</a>
        › <a href="{{ route('campaigns.show', $campaign) }}" class="crumb" wire:navigate>{{ $campaign->name }}</a>
    </nav>

    <h1 class="text-2xl font-semibold">{{ __('Documents') }}</h1>
    <p class="mb-6 text-sm text-stone-600">{{ __('PDF et images de la campagne, de son monde et de son jeu. Liez-les ensuite à une scène, une fiche ou une règle.') }}</p>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="mb-4 flex flex-wrap items-center gap-3">
                <label for="kind" class="sr-only">{{ __('Type') }}</label>
                <select id="kind" wire:model.live="kind" class="field w-auto py-1.5 text-sm">
                    <option value="">{{ __('PDF et images') }}</option>
                    <option value="pdf">PDF</option>
                    <option value="image">{{ __('Images') }}</option>
                </select>
                @foreach ($this->tagNames as $tagName)
                    <button type="button" wire:click="$set('tag', @js($tag === $tagName ? '' : $tagName))"
                        @class(['rounded-full px-2.5 py-0.5 text-sm', 'bg-codex text-on-accent' => $tag === $tagName, 'bg-stone-100 text-stone-700 hover:bg-codex-soft' => $tag !== $tagName])>{{ $tagName }}</button>
                @endforeach
            </div>

            @if ($this->documents->isEmpty())
                <div class="rounded-xl border border-dashed border-stone-300 bg-white p-10 text-center">
                    <p class="text-stone-600">{{ $kind !== '' || $tag !== '' ? __('Aucun document ne correspond à ce filtre.') : __("Aucun document pour l'instant. Téléversez une carte, une lettre ou un PDF de règles.") }}</p>
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
                                    <span class="block text-xs text-stone-500">{{ $document->scopeLabel() }} · {{ $document->zone === \App\Enums\Zone::Public ? __('Joueurs') : __('MJ seulement') }} · {{ $document->humanSize() }}</span>
                                    @if ($document->tags->isNotEmpty())
                                        <span class="mt-1 flex flex-wrap gap-1 text-xs">
                                            @foreach ($document->tags as $documentTag)
                                                <x-tag :tag="$documentTag" compact />
                                            @endforeach
                                        </span>
                                    @endif
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <form wire:submit="saveUploads" class="space-y-4 self-start rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
            <h2 class="font-semibold">{{ __('Téléverser') }}</h2>
            <div>
                <label for="uploads" class="label">{{ __('Fichiers') }} <span class="font-normal text-stone-500">{{ __('(PDF ou images, 50 Mo max.)') }}</span></label>
                <input id="uploads" type="file" wire:model="uploads" multiple accept=".pdf,image/*" class="block w-full text-sm">
                <div wire:loading wire:target="uploads" class="mt-1 text-xs text-stone-500">{{ __('Envoi en cours…') }}</div>
                @error('uploads') <p class="error">{{ $message }}</p> @enderror
                @error('uploads.*') <p class="error">{{ $message }}</p> @enderror
            </div>
            <fieldset>
                <legend class="label">{{ __('Ranger dans') }}</legend>
                @foreach ($this->scopes() as $value => $label)
                    <label class="flex items-center gap-2 text-sm"><input type="radio" wire:model="scope" value="{{ $value }}"> {{ $label }}</label>
                @endforeach
            </fieldset>
            <fieldset>
                <legend class="label">{{ __('Visibilité') }}</legend>
                <label class="flex items-center gap-2 text-sm"><input type="radio" wire:model="zone" value="gm"> {{ __('MJ seulement') }}</label>
                <label class="flex items-center gap-2 text-sm"><input type="radio" wire:model="zone" value="public"> {{ __('Consultable par les joueurs') }}</label>
            </fieldset>
            <x-tags-input id="uploadTags" :existing="$this->tagNames" />
            <button type="submit" class="btn-primary" wire:loading.attr="disabled" wire:target="uploads,saveUploads">{{ __('Téléverser') }}</button>
        </form>
    </div>
</div>
