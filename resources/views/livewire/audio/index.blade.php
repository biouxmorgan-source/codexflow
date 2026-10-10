<div>
    <nav class="mb-2 text-sm text-stone-500">
        <a href="{{ route('campaigns.index') }}" class="crumb" wire:navigate>{{ __('Mes campagnes') }}</a>
        › <a href="{{ route('campaigns.show', $campaign) }}" class="crumb" wire:navigate>{{ $campaign->name }}</a>
    </nav>

    <h1 class="text-2xl font-semibold">{{ __('Sons') }}</h1>
    <p class="mb-6 text-sm text-stone-600">{{ __('Musiques et ambiances de la campagne. Liez-les à une scène, puis lancez-les en mode Session, sur votre appareil ou sur l’écran de table.') }}</p>

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
                                        <span class="block text-xs text-stone-500">{{ $track->loop ? __('En boucle') : __('Une fois') }} · {{ $track->humanSize() }}</span>
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
                                            x-on:click="$dispatch('loremundi-audio-play', @js(['url' => route('audio.file', $track), 'title' => $track->title, 'loop' => $track->loop]))"
                                            aria-label="{{ __('Écouter « :name » sur cet appareil', ['name' => $track->title]) }}">▶ {{ __('Ici') }}</button>
                                        @if ($onTable)
                                            <button type="button" wire:click="playOnTable({{ $track->id }})" class="btn-secondary min-h-0 py-1 text-sm"
                                                aria-label="{{ __('Jouer « :name » sur l’écran de table', ['name' => $track->title]) }}">▶ {{ __('Table') }}</button>
                                        @endif
                                        <button type="button" wire:click="edit({{ $track->id }})" class="px-1 text-sm text-stone-600 hover:text-codex">{{ __('Modifier') }}</button>
                                        <button type="button" wire:click="delete({{ $track->id }})" wire:confirm="{{ __('Supprimer « :name » ?', ['name' => $track->title]) }}" class="px-1 text-sm text-red-700 hover:underline">{{ __('Supprimer') }}</button>
                                    </span>
                                </div>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <form wire:submit="saveUploads" class="space-y-4 self-start rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
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
            <p class="text-xs text-stone-500">{{ __('Vos fichiers restent privés : seuls vous et vos co-MJ y accédez. Utilisez des musiques dont vous avez les droits.') }}</p>
            <button type="submit" class="btn-primary" wire:loading.attr="disabled" wire:target="uploads,saveUploads">{{ __('Téléverser') }}</button>
        </form>
    </div>
</div>
