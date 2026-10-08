<div>
    <nav class="mb-2 text-sm text-stone-500">
        <a href="{{ route('campaigns.index') }}" class="crumb" wire:navigate>{{ __('Mes campagnes') }}</a>
        › <a href="{{ route('campaigns.show', $campaign) }}" class="crumb" wire:navigate>{{ $campaign->name }}</a>
        › <a href="{{ route('documents.index', $campaign) }}" class="crumb" wire:navigate>{{ __('Documents') }}</a>
    </nav>

    <div class="mb-4 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold">{{ $document->title }}</h1>
            <p class="text-sm text-stone-500">{{ $document->original_name }} · {{ $document->humanSize() }} · {{ $document->scopeLabel() }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <livewire:table.show-button :campaign="$campaign" kind="document" :item-id="$document->id" wire:key="table-document" />
            @if ($document->isImage() && \App\Support\CampaignFeatures::usable($campaign, 'maps'))
                <a href="{{ route('maps.index', [$campaign, 'document' => $document->id]) }}" class="btn-secondary" wire:navigate>{{ __('En faire une carte') }}</a>
            @endif
            <a href="{{ route('documents.file', $document) }}" target="_blank" rel="noopener" class="btn-secondary">{{ __('Ouvrir dans un onglet ↗') }}</a>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="overflow-hidden rounded-xl border border-stone-200 bg-white shadow-sm lg:col-span-2">
            @if ($document->isImage())
                <img src="{{ route('documents.file', $document) }}" alt="{{ $document->title }}" class="mx-auto max-h-[75vh] w-auto">
            @elseif ($document->isPdf())
                <x-pdf-viewer :url="route('documents.file', $document)" :title="$document->title" :download="route('documents.file', $document)" class="h-[75vh]" wire:ignore />
            @endif
        </section>

        <aside class="space-y-6">
            <livewire:secrets.panel :campaign="$campaign" :items="['document' => [$document->id]]" :link="'document:'.$document->id" wire:key="secrets-document" />
            <livewire:characters.give :campaign="$campaign" fixed-kind="document" :document-id="$document->id" :key="'give-document-'.$document->id" />

            <form wire:submit="save" class="space-y-4 rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                <div>
                    <label for="title" class="label">{{ __('Titre') }}</label>
                    <input id="title" type="text" wire:model="title" class="field">
                    @error('title') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="description" class="label">{{ __('Description') }} <span class="font-normal text-stone-500">{{ __('(facultatif)') }}</span></label>
                    <textarea id="description" wire:model="description" rows="3" class="field"></textarea>
                    @error('description') <p class="error">{{ $message }}</p> @enderror
                </div>
                <fieldset>
                    <legend class="label">{{ __('Visibilité') }}</legend>
                    <label class="flex items-center gap-2 text-sm"><input type="radio" wire:model="zone" value="gm"> {{ __('MJ seulement') }}</label>
                    <label class="flex items-center gap-2 text-sm"><input type="radio" wire:model="zone" value="public"> {{ __('Consultable par les joueurs') }}</label>
                </fieldset>
                <x-tags-input />
                <div class="flex items-center gap-3">
                    <button type="submit" class="btn-primary">{{ __('Enregistrer') }}</button>
                    @if ($saved)
                        <span class="text-sm text-emerald-700" wire:transition>{{ __('Enregistré.') }}</span>
                    @endif
                </div>
            </form>

            <section class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="mb-2 font-semibold">{{ __('Utilisé par') }}</h2>
                @if ($scenes->isEmpty() && $entities->isEmpty() && $rules->isEmpty())
                    <p class="text-sm text-stone-500">{{ __("Rien pour l'instant. Liez ce document depuis une scène, une fiche ou une règle.") }}</p>
                @else
                    <ul class="space-y-1 text-sm">
                        @foreach ($scenes as $scene)
                            <li><span class="text-stone-500">{{ __('Scène ·') }}</span> <a href="{{ route('scenes.show', [$campaign, $scene]) }}" class="link" wire:navigate>{{ $scene->name }}</a>@if ($scene->scenario) <span class="text-stone-500">({{ $scene->scenario->name }})</span>@endif</li>
                        @endforeach
                        @foreach ($entities as $entity)
                            <li><span class="text-stone-500">{{ __('Fiche ·') }}</span> <a href="{{ route('entities.show', [$campaign, $entity]) }}" class="link" wire:navigate>{{ $entity->name }}</a></li>
                        @endforeach
                        @foreach ($rules as $rule)
                            <li><span class="text-stone-500">{{ __('Règle ·') }}</span> <a href="{{ route('rules.show', [$campaign, $rule]) }}" class="link" wire:navigate>{{ $rule->title }}</a></li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <div class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="mb-2 font-semibold">{{ __('Supprimer') }}</h2>
                <p class="mb-3 text-sm text-stone-600">{{ $document->campaign_id ? __('Le fichier disparaît partout où il est lié.') : __('Le fichier disparaît partout où il est lié, dans toutes les campagnes.') }}</p>
                <button type="button" wire:click="delete" wire:confirm="{{ __('Supprimer le document :name ?', ['name' => $document->title]) }}" class="text-sm font-medium text-red-700 hover:underline">{{ __('Supprimer le document') }}</button>
            </div>
        </aside>
    </div>
</div>
