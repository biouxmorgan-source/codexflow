<div>
    <nav class="mb-2 text-sm text-stone-500">
        <a href="{{ route('campaigns.index') }}" class="crumb" wire:navigate>{{ __('Mes campagnes') }}</a>
        › <a href="{{ route('campaigns.show', $campaign) }}" class="crumb" wire:navigate>{{ $campaign->name }}</a>
    </nav>

    <div class="flex items-center gap-2">
        <h1 class="text-2xl font-semibold">{{ __('Sons') }}</h1>
        <x-page-help topic="audio" />
    </div>
    <p class="mb-6 text-sm text-stone-600">
        {{ __('Musiques et ambiances de la campagne. Liez-les à une scène, puis lancez-les en mode Session, sur votre appareil ou sur l’écran de table.') }}
        <a href="{{ route('library.sounds') }}" class="font-medium text-codex hover:underline" wire:navigate>{{ __('Ma bibliothèque de sons') }}</a>
    </p>

    @php($onTable = \App\Support\CampaignFeatures::enabled($campaign, 'table') && auth()->user()->can('use-feature', ['table', $campaign]))
    <div class="grid gap-6 *:min-w-0 lg:grid-cols-3">
        <div class="lg:col-span-2">
            @if ($this->tagNames)
                <div class="mb-4 flex flex-wrap items-center gap-2">
                    @foreach ($this->tagNames as $tagName)
                        <button type="button" wire:click="$set('tag', @js($tag === $tagName ? '' : $tagName))"
                            @class(['rounded-full px-2.5 py-0.5 text-sm', 'bg-codex text-on-accent' => $tag === $tagName, 'bg-stone-100 text-stone-700 hover:bg-codex-soft' => $tag !== $tagName])>{{ $tagName }}</button>
                    @endforeach
                </div>
            @endif

            @if ($this->tracks->isEmpty())
                <div class="rounded-xl border border-dashed border-stone-300 bg-white p-10 text-center">
                    <p class="text-stone-600">{{ $tag !== '' ? __('Aucun son ne porte ce tag.') : __('Aucun son pour l’instant. Téléversez une musique de taverne, une ambiance de forêt ou un thème de combat.') }}</p>
                </div>
            @else
                <ul class="space-y-2">
                    @foreach ($this->tracks as $track)
                        <li wire:key="track-{{ $track->id }}" class="rounded-xl border border-stone-200 bg-white p-3 shadow-sm">
                            @if ($editingId === $track->id)
                                <form wire:submit="update" class="space-y-3">
                                    <div>
                                        <label for="editTitle" class="label">{{ __('Titre') }}</label>
                                        <input id="editTitle" type="text" wire:model="editTitle" class="field" maxlength="255">
                                        @error('editTitle') <p class="error">{{ $message }}</p> @enderror
                                    </div>
                                    <x-tags-input id="editTags" model="editTags" :existing="$this->tagNames" />
                                    <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="editLoop"> {{ __('Jouer en boucle') }}</label>
                                    <div class="flex gap-2">
                                        <button type="submit" class="btn-primary min-h-0 py-1 text-sm">{{ __('Enregistrer') }}</button>
                                        <button type="button" wire:click="cancel" class="btn-secondary min-h-0 py-1 text-sm">{{ __('Annuler') }}</button>
                                    </div>
                                </form>
                            @else
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate font-medium">{{ $track->title }}</span>
                                        <span class="block text-xs text-stone-500">{{ $track->loop ? __('En boucle') : __('Une fois') }} · {{ $track->humanSize() }}@if ($track->inLibrary()) · <span class="font-medium text-codex">{{ $track->user_id === auth()->id() ? __('Ma bibliothèque') : __('Bibliothèque de :name', ['name' => $track->owner->name]) }}</span>@endif</span>
                                        @if ($track->tags->isNotEmpty())
                                            <span class="mt-1 flex flex-wrap gap-1 text-xs">
                                                @foreach ($track->tags as $trackTag)
                                                    <x-tag :tag="$trackTag" compact />
                                                @endforeach
                                            </span>
                                        @endif
                                    </span>
                                    <span class="flex flex-wrap items-center gap-1.5">
                                        <button type="button" class="btn-secondary min-h-0 py-1 text-sm"
                                            x-on:click="$dispatch('sagawyn-audio-play', @js(['url' => route('audio.file', $track), 'title' => $track->title, 'loop' => $track->loop]))"
                                            aria-label="{{ __('Écouter « :name » sur cet appareil', ['name' => $track->title]) }}">▶ {{ __('Ici') }}</button>
                                        @if ($onTable)
                                            <button type="button" wire:click="playOnTable({{ $track->id }})" class="btn-secondary min-h-0 py-1 text-sm"
                                                aria-label="{{ __('Jouer « :name » sur l’écran de table', ['name' => $track->title]) }}">▶ {{ __('Table') }}</button>
                                        @endif
                                        @can('update', $track)
                                            <button type="button" wire:click="edit({{ $track->id }})" class="px-1 text-sm text-stone-600 hover:text-codex">{{ __('Modifier') }}</button>
                                        @endcan
                                        @if ($track->inLibrary())
                                            <button type="button" wire:click="removeFromCampaign({{ $track->id }})" wire:confirm="{{ __('Retirer « :name » de la campagne ? Il reste dans la bibliothèque.', ['name' => $track->title]) }}" class="px-1 text-sm text-red-700 hover:underline">{{ __('Retirer') }}</button>
                                        @else
                                            @if ($track->user_id === auth()->id())
                                                <button type="button" wire:click="moveToLibrary({{ $track->id }})" class="px-1 text-sm text-stone-600 hover:text-codex"
                                                    title="{{ __('Le son reste dans cette campagne et devient utilisable dans vos autres campagnes.') }}">{{ __('Ranger dans ma bibliothèque') }}</button>
                                            @endif
                                            <button type="button" wire:click="delete({{ $track->id }})" wire:confirm="{{ __('Supprimer « :name » ?', ['name' => $track->title]) }}" class="px-1 text-sm text-red-700 hover:underline">{{ __('Supprimer') }}</button>
                                        @endif
                                    </span>
                                </div>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="space-y-6 self-start">
        @if ($this->libraryChoices->isNotEmpty())
            <form wire:submit="addFromLibrary" class="space-y-3 rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="font-semibold">{{ __('Depuis ma bibliothèque') }}</h2>
                <p class="text-xs text-stone-500">{{ __('Le fichier n’est pas copié : il sert dans chaque campagne où vous l’ajoutez.') }}</p>
                <ul class="max-h-60 space-y-1 overflow-y-auto text-sm">
                    @foreach ($this->libraryChoices as $choice)
                        <li wire:key="choice-{{ $choice->id }}"><label class="flex items-center gap-2"><input type="checkbox" wire:model="libraryPicks" value="{{ $choice->id }}"> <span class="truncate">{{ $choice->title }}</span></label></li>
                    @endforeach
                </ul>
                <button type="submit" class="btn-secondary">{{ __('Ajouter à la campagne') }}</button>
            </form>
        @endif

        <form wire:submit="saveUploads" class="space-y-4 rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
            <h2 class="font-semibold">{{ __('Téléverser') }}</h2>
            <div>
                <label for="uploads" class="label">{{ __('Fichiers') }} <span class="font-normal text-stone-500">{{ __('(mp3, ogg, m4a, wav ou flac, 50 Mo max.)') }}</span></label>
                <input id="uploads" type="file" wire:model="uploads" multiple accept="audio/*,.mp3,.ogg,.oga,.opus,.m4a,.aac,.wav,.flac,.webm" class="block w-full text-sm">
                <div wire:loading wire:target="uploads" class="mt-1 text-xs text-stone-500">{{ __('Envoi en cours…') }}</div>
                @error('uploads') <p class="error">{{ $message }}</p> @enderror
                @error('uploads.*') <p class="error">{{ $message }}</p> @enderror
            </div>
            <x-tags-input id="uploadTags" :existing="$this->tagNames" />
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="loop"> {{ __('Jouer en boucle') }} <span class="text-xs text-stone-500">{{ __('(ambiances)') }}</span></label>
            <label class="flex items-start gap-2 text-sm"><input type="checkbox" wire:model="toLibrary" class="mt-1"> <span>{{ __('Ranger aussi dans ma bibliothèque') }} <span class="block text-xs text-stone-500">{{ __('pour le réutiliser dans mes autres campagnes sans le téléverser à nouveau') }}</span></span></label>
            <p class="text-xs text-stone-500">{{ __('Vos fichiers restent privés : seuls vous et vos co-MJ y accédez. Utilisez des musiques dont vous avez les droits.') }}</p>
            <button type="submit" class="btn-primary" wire:loading.attr="disabled" wire:target="uploads,saveUploads">{{ __('Téléverser') }}</button>
        </form>
        </div>
    </div>
</div>
