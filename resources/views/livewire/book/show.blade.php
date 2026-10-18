<div>
    <nav class="mb-2 text-sm text-stone-500">
        <a href="{{ route('campaigns.index') }}" class="crumb" wire:navigate>{{ __('Mes campagnes') }}</a>
        › <a href="{{ route('campaigns.show', $campaign) }}" class="crumb" wire:navigate>{{ $campaign->name }}</a>
    </nav>

    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-semibold">{{ __('Livre des aventures') }}</h1>
                <x-page-help topic="book" />
            </div>
            <p class="mt-1 max-w-3xl text-sm text-stone-600">{{ __('Les scènes jouées, dans l’ordre des scénarios, racontées par ce qui s’est passé à la table, puis les résumés des séances. Il contient vos notes MJ : vous seul le voyez.') }}</p>
        </div>
        <a href="{{ route('book.print', [$campaign, 'version' => $version]) }}" target="_blank" class="btn-secondary">{{ __('Imprimer ou enregistrer en PDF') }}</a>
    </div>

    <div class="mb-6 flex flex-wrap items-center gap-2" role="group" aria-label="{{ __('Version du livre') }}">
        <button type="button" wire:click="$set('version', 'assemblee')" @class(['rounded-full px-3 py-1 text-sm font-medium', 'bg-codex text-on-accent' => $version === 'assemblee', 'border border-stone-200 bg-white text-stone-700' => $version !== 'assemblee']) aria-pressed="{{ $version === 'assemblee' ? 'true' : 'false' }}">{{ __('Assemblé d’après les scènes') }}</button>
        <button type="button" wire:click="$set('version', 'redigee')" @class(['rounded-full px-3 py-1 text-sm font-medium', 'bg-codex text-on-accent' => $version === 'redigee', 'border border-stone-200 bg-white text-stone-700' => $version !== 'redigee']) aria-pressed="{{ $version === 'redigee' ? 'true' : 'false' }}">{{ __('Version rédigée') }}</button>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm sm:p-10 lg:col-span-2">
            @if ($version === 'redigee' && $editing)
                <form wire:submit="saveNarrative" class="space-y-3">
                    <label for="narrative" class="label">{{ __('Livre rédigé') }} <span class="font-normal text-stone-500">{{ __('(Markdown : # titre, **gras**, [[Fiche]])') }}</span></label>
                    <textarea id="narrative" wire:model="narrative" rows="24" class="field font-mono text-sm" maxlength="200000"></textarea>
                    @error('narrative') <p class="error">{{ $message }}</p> @enderror
                    <div class="flex gap-2">
                        <button type="submit" class="btn-primary">{{ __('Enregistrer') }}</button>
                        <button type="button" wire:click="$set('editing', false)" class="btn-secondary">{{ __('Annuler') }}</button>
                    </div>
                </form>
            @elseif ($version === 'redigee' && blank($campaign->adventure_book))
                <div class="py-10 text-center">
                    <p class="text-lg font-medium">{{ __('Pas encore de version rédigée.') }}</p>
                    <p class="mx-auto mt-2 max-w-md text-sm text-stone-600">{{ __('Écrivez le récit vous-même à partir du livre assemblé, ou faites-le rédiger par une IA (à droite).') }}</p>
                    <button type="button" wire:click="edit" class="btn-primary mt-4">{{ __('Écrire le récit') }}</button>
                </div>
            @elseif ($version === 'assemblee' && $chapters->isEmpty() && $sessions->isEmpty())
                <div class="py-10 text-center">
                    <p class="text-lg font-medium">{{ __('Rien à raconter pour l’instant.') }}</p>
                    <p class="mx-auto mt-2 max-w-md text-sm text-stone-600">{{ __('Le livre se remplit à mesure que vous jouez : passez une scène en « Jouée », cochez « Jouée comme prévu » ou écrivez « Ce qui s’est passé », et résumez vos séances.') }}</p>
                    <a href="{{ route('scenarios.index', $campaign) }}" class="btn-secondary mt-4" wire:navigate>{{ __('Aller aux scénarios') }}</a>
                </div>
            @else
                @if ($version === 'redigee')
                    <div class="mb-4 flex justify-end">
                        <button type="button" wire:click="edit" class="btn-secondary min-h-0 py-1 text-sm">{{ __('Modifier le récit') }}</button>
                    </div>
                @endif
                <x-book-content :campaign="$campaign" :chapters="$chapters" :sessions="$sessions" :version="$version" :narrative="$campaign->adventure_book" />
            @endif
        </div>

        <aside class="space-y-6">
            <section class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="mb-2 font-semibold">{{ __('Comment il se construit') }}</h2>
                <ul class="list-disc space-y-1 pl-5 text-sm text-stone-600">
                    <li>{{ __('Une scène entre dans le livre quand elle est « Jouée », cochée « Jouée comme prévu », ou qu’elle a un « Ce qui s’est passé ».') }}</li>
                    <li>{{ __('Son récit : « Ce qui s’est passé » s’il est écrit, sinon ce qui était prévu.') }}</li>
                    <li>{{ __('Les résumés des séances suivent, dans l’ordre.') }}</li>
                </ul>
            </section>

            @if ($this->aiUsable)
                <section class="space-y-3 rounded-xl border border-stone-200 bg-white p-6 shadow-sm" x-data="{ copied: false }">
                    <h2 class="font-semibold">{{ __('Faire rédiger par une IA') }} <span class="font-normal text-stone-500">{{ __('(facultatif)') }}</span></h2>
                    <p class="text-sm text-stone-600">{{ __('Copiez ce texte dans l’IA de votre choix, puis collez son récit dans « Version rédigée ».') }}</p>
                    <label for="book-prompt" class="sr-only">{{ __('Texte à coller dans l’IA') }}</label>
                    <textarea id="book-prompt" x-ref="prompt" readonly rows="6" class="field font-mono text-xs">{{ $this->book->prompt() }}</textarea>
                    <div class="flex flex-wrap items-center gap-3">
                        <button type="button" class="btn-secondary" x-on:click="navigator.clipboard.writeText($refs.prompt.value).then(() => { copied = true; setTimeout(() => copied = false, 2500) }, () => { $refs.prompt.select() })">{{ __('Copier le texte') }}</button>
                        <span x-show="copied" x-cloak class="text-sm text-emerald-700" role="status">{{ __('Copié !') }}</span>
                    </div>
                    <div class="border-t border-stone-200 pt-3">
                        @if ($this->directProvider)
                            <button type="button" wire:click="writeWithAi" wire:loading.attr="disabled" wire:target="writeWithAi"
                                @if (filled($campaign->adventure_book)) wire:confirm="{{ __('Remplacer la version rédigée actuelle ?') }}" @endif class="btn-primary">
                                <span wire:loading.remove wire:target="writeWithAi">{{ __('Rédiger avec :provider', ['provider' => $this->directProvider]) }}</span>
                                <span wire:loading wire:target="writeWithAi">{{ __('Rédaction en cours… (jusqu’à quelques minutes)') }}</span>
                            </button>
                            <p class="mt-1 text-xs text-stone-500">{{ __('Envoie ce texte avec votre clé, à vos frais. Le récit reste modifiable.') }}</p>
                        @else
                            <p class="text-xs text-stone-500">{!! __('Vous avez une clé d’API Claude, ChatGPT ou Mistral ? Enregistrez-la dans :link pour rédiger sans copier-coller.', ['link' => '<a href="'.route('preferences').'" class="link" wire:navigate>'.e(__('vos préférences')).'</a>']) !!}</p>
                        @endif
                        @error('direct') <p class="error" role="alert">{{ $message }}</p> @enderror
                    </div>
                    <p class="text-xs text-stone-500">{{ __('Ce texte contient vos notes MJ : collez-le seulement dans une IA en laquelle vous avez confiance.') }}</p>
                </section>
            @endif
        </aside>
    </div>
</div>
