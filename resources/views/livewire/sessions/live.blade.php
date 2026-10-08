<div x-data="{
        focus: (() => { try { return localStorage.getItem('codexflow.focus') === '1' } catch (e) { return false } })(),
        toggle() {
            this.focus = ! this.focus;
            try { localStorage.setItem('codexflow.focus', this.focus ? '1' : '0') } catch (e) {}
        },
        previewTarget(event) {
            const link = event.target.closest('a[href]');
            if (! link || ! this.$root.contains(link) || link.closest('[data-leave]') || link.target || event.ctrlKey || event.metaKey || event.shiftKey || event.button !== 0) return null;
            const match = new URL(link.href, location.href).pathname.match(/^\/campagnes\/{{ $campaign->id }}\/entites\/(\d+)$/);
            return match ? Number(match[1]) : null;
        },
    }"
    x-effect="document.body.classList.toggle('focus-mode', focus)"
    {{-- Un lien vers une fiche de la campagne s'ouvre dans le panneau d'aperçu : on ne quitte pas la session.
         wire:navigate part au relâchement du bouton : on l'arrête dès l'appui. --}}
    x-on:mousedown.window.capture="previewTarget($event) && $event.stopPropagation()"
    x-on:click.window.capture="const id = previewTarget($event); if (id) { $event.preventDefault(); $event.stopPropagation(); $wire.openPreview(id) }"
    x-on:livewire:navigating.window="document.body.classList.remove('focus-mode')"
    x-on:keydown.n.window="if (! ['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName) && ! document.activeElement.isContentEditable && document.getElementById('noteBody')) { $event.preventDefault(); document.getElementById('noteBody').focus() }">
    @php($session = $this->session)

    <div class="mb-4 flex flex-wrap items-center gap-3">
        <div class="min-w-0 flex-[1_1_16rem]">
            <nav class="text-sm text-stone-500 [.focus-mode_&]:hidden">
                <a href="{{ route('campaigns.index') }}" class="crumb" wire:navigate>{{ __('Mes campagnes') }}</a>
                › <a href="{{ route('campaigns.show', $campaign) }}" class="crumb" wire:navigate>{{ $campaign->name }}</a>
            </nav>
            <h1 class="text-2xl font-semibold">{{ $session ? $session->label() : __('Mode Session') }}</h1>
            @if ($session)
                <p class="text-sm text-stone-500">{{ __('Commencée :time', ['time' => $session->started_at->diffForHumans()]) }}</p>
            @endif
        </div>
        @if ($session)
            <button type="button" x-on:click="toggle()" class="btn-secondary" x-text="focus ? @js(__('Quitter le mode focus')) : @js(__('Mode focus'))">{{ __('Mode focus') }}</button>
            <button type="button" wire:click="end" wire:confirm="{{ __('Terminer la session ? Les notes restent consultables.') }}" class="btn-secondary">{{ __('Terminer la session') }}</button>
        @endif
    </div>

    @if (! $session)
        <div class="grid gap-6 *:min-w-0 lg:grid-cols-3">
            <section class="rounded-xl border border-stone-200 bg-white p-8 text-center shadow-sm lg:col-span-2">
                <p class="text-lg font-medium">{{ __('Prêt à jouer ?') }}</p>
                <p class="mx-auto mt-1 max-w-md text-stone-600">{{ __("La session reprend à la scène en cours ou à la prochaine scène à jouer. Ses fiches, vos éléments « À jouer » et vos fiches épinglées s'affichent tout seuls.") }}</p>
                <button type="button" wire:click="start" class="btn-primary mt-6">{{ __('Démarrer la session :number', ['number' => ($this->pastSessions->max('number') ?? 0) + 1]) }}</button>
            </section>
            <section class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="mb-2 font-semibold">{{ __('Sessions passées') }}</h2>
                @forelse ($this->pastSessions as $past)
                    <a href="{{ route('sessions.show', [$campaign, $past]) }}" class="-mx-2 flex justify-between gap-2 rounded-md px-2 py-1 text-sm text-codex hover:bg-codex-soft" wire:navigate>
                        <span>{{ $past->label() }}</span>
                        <span class="text-stone-500">{{ $past->started_at->isoFormat('L') }} · {{ trans_choice(':count note|:count notes', $past->notes_count) }}</span>
                    </a>
                @empty
                    <p class="text-sm text-stone-500">{{ __("Aucune pour l'instant.") }}</p>
                @endforelse
            </section>
        </div>
    @else
        @php($scene = $session->currentScene)
        <div class="grid gap-4 *:min-w-0 lg:grid-cols-3">
            {{-- Maintenant --}}
            <div class="space-y-4 lg:col-span-2">
                <section class="rounded-xl border border-flow/30 bg-white p-4 shadow-sm">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h2 class="mr-auto text-sm font-semibold tracking-wide text-flow uppercase">{{ __('Maintenant') }}</h2>
                        <label for="currentScene" class="sr-only">{{ __('Scène en cours') }}</label>
                        <select id="currentScene" wire:change="setScene($event.target.value || null)" class="field w-auto py-1.5 text-sm">
                            <option value="">{{ __('— Aucune scène —') }}</option>
                            @foreach ($this->scenes->groupBy(fn ($s) => $s->scenario->name) as $scenarioName => $scenarioScenes)
                                <optgroup label="{{ $scenarioName }}">
                                    @foreach ($scenarioScenes as $option)
                                        <option value="{{ $option->id }}" @selected($scene?->id === $option->id)>{{ $option->name }} · {{ $option->status->label() }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        @if ($scene)
                            <button type="button" wire:click="nextScene" class="btn-primary min-h-0 py-1.5 text-sm">{{ __('Scène jouée, suivante →') }}</button>
                        @endif
                    </div>

                    @if ($scene)
                        <p class="text-xs text-stone-500">{{ $scene->scenario->name }}{{ $scene->chapter ? ' · '.$scene->chapter : '' }}</p>
                        <h3 class="text-xl font-semibold">
                            <a href="{{ route('scenes.show', [$campaign, $scene]) }}" target="_blank" rel="noopener" class="crumb">{{ $scene->name }}</a>
                        </h3>
                        @if ($scene->description)
                            <div class="mt-2 text-stone-700">{{ \App\Support\EntityLinks::render($scene->description, $campaign) }}</div>
                        @endif
                        @if ($this->upcomingScene)
                            <p class="mt-3 border-t border-stone-100 pt-2 text-sm text-stone-500">
                                {{ __('Ensuite :') }}
                                <a href="{{ route('scenes.show', [$campaign, $this->upcomingScene]) }}" target="_blank" rel="noopener" class="font-medium link">{{ $this->upcomingScene->name }}</a>
                            </p>
                        @endif
                    @else
                        <p class="text-sm text-stone-500">{{ __('Aucune scène en cours. Choisissez-en une, ou improvisez : vos notes restent rattachées à la session.') }}</p>
                    @endif
                </section>

                @if ($this->cards->isNotEmpty())
                    <section>
                        <h2 class="mb-2 text-sm font-semibold tracking-wide text-stone-500 uppercase">{{ __('Fiches utiles') }}</h2>
                        <div class="grid gap-2 *:min-w-0 md:grid-cols-2">
                            @foreach ($this->cards as $card)
                                <x-context-card :entity="$card['entity']" :campaign="$campaign" :field-definitions="$fieldDefinitions" :note="$card['note']" :source="$card['source']" />
                            @endforeach
                        </div>
                    </section>
                @endif

                {{-- Secrets de la scène, de ses fiches et de ses documents, à révéler d'un clic. --}}
                @php($secretItems = ['scene' => $scene ? [$scene->id] : [], 'entity' => $this->cards->map(fn ($card) => $card['entity']->id)->values()->all(), 'document' => $this->documents->modelKeys()])
                @if ($scene || $secretItems['entity'] !== [])
                    <livewire:secrets.panel :campaign="$campaign" :items="$secretItems" :compact="true" :wire:key="'session-secrets-'.md5(json_encode($secretItems))" />
                @endif

                @if ($this->rules->isNotEmpty() || $this->documents->isNotEmpty())
                    <div class="grid gap-4 *:min-w-0 md:grid-cols-2">
                        @if ($this->rules->isNotEmpty())
                            <section>
                                <h2 class="mb-2 text-sm font-semibold tracking-wide text-stone-500 uppercase">{{ __('Règles') }}</h2>
                                <div class="space-y-2">
                                    @foreach ($this->rules as $rule)
                                        <details wire:key="rule-{{ $rule->id }}" class="group rounded-xl border border-stone-200 bg-white p-3 shadow-sm">
                                            <summary class="flex cursor-pointer list-none items-start gap-2">
                                                <span class="min-w-0 flex-1">
                                                    <span class="font-medium">{{ $rule->title }}</span>
                                                    <span class="ml-1 rounded-full px-2 py-0.5 text-xs font-medium {{ $rule->status->badge() }}">{{ $rule->status->label() }}</span>
                                                    @if ($rule->summary)
                                                        <span class="block text-sm text-stone-600">{{ $rule->summary }}</span>
                                                    @endif
                                                </span>
                                                <span class="text-stone-400 transition group-open:rotate-90" aria-hidden="true">›</span>
                                            </summary>
                                            @if ($rule->procedure)
                                                <div class="mt-2 border-t border-stone-100 pt-2 text-sm text-stone-700">{{ \App\Support\EntityLinks::render($rule->procedure, $campaign) }}</div>
                                            @endif
                                            @if ($rule->gm_notes)
                                                <div class="mt-2 rounded-lg bg-flow/5 p-2 text-sm text-stone-700"><span class="font-medium text-flow">{{ __('MJ :') }}</span> {{ \App\Support\EntityLinks::render($rule->gm_notes, $campaign) }}</div>
                                            @endif
                                            <span class="mt-2 flex flex-wrap gap-x-4 text-xs">
                                                <a href="{{ route('rules.show', [$campaign, $rule]) }}" target="_blank" rel="noopener" class="link">{{ __('Ouvrir la règle ↗') }}</a>
                                                <button type="button" wire:click="showOnTable('rule', {{ $rule->id }})" class="link">{{ __('Montrer à la table') }}</button>
                                            </span>
                                        </details>
                                    @endforeach
                                </div>
                            </section>
                        @endif
                        @if ($this->documents->isNotEmpty())
                            <section>
                                <h2 class="mb-2 text-sm font-semibold tracking-wide text-stone-500 uppercase">{{ __('Documents') }}</h2>
                                <ul class="space-y-2">
                                    @foreach ($this->documents as $document)
                                        <li wire:key="document-{{ $document->id }}" class="flex items-center gap-2 rounded-xl border border-stone-200 bg-white p-3 shadow-sm hover:border-codex/40">
                                            <a href="{{ route('documents.file', $document) }}" target="_blank" rel="noopener" class="flex min-w-0 flex-1 items-center gap-3">
                                                @if ($document->isImage())
                                                    <img src="{{ route('documents.file', $document) }}" alt="" loading="lazy" class="h-10 w-10 shrink-0 rounded-lg object-cover">
                                                @else
                                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-red-50 text-xs font-semibold text-red-700" aria-hidden="true">PDF</span>
                                                @endif
                                                <span class="min-w-0 flex-1 truncate font-medium text-codex">{{ $document->title }}</span>
                                                <span class="text-xs text-stone-500" aria-hidden="true">↗</span>
                                            </a>
                                            <button type="button" wire:click="showDocument({{ $document->id }})" class="shrink-0 rounded-md border border-stone-200 px-2 py-0.5 text-xs text-stone-600 hover:border-codex hover:text-codex" aria-label="{{ __("Montrer « :title » sur l'écran de table", ['title' => $document->title]) }}">{{ __('Montrer') }}</button>
                                        </li>
                                    @endforeach
                                </ul>
                            </section>
                        @endif
                    </div>
                @endif

                <section class="rounded-xl border border-stone-200 bg-white p-4 shadow-sm">
                    <h2 class="mb-2 flex items-baseline gap-2 text-sm font-semibold tracking-wide text-stone-500 uppercase">{{ __('Notes de session') }} <span class="text-xs font-normal tracking-normal normal-case">{{ __('(touche N)') }}</span></h2>
                    <form wire:submit="addNote" class="flex gap-2">
                        <label for="noteBody" class="sr-only">{{ __('Nouvelle note') }}</label>
                        {{-- « [[ » propose les fiches de la campagne, comme dans les textes longs. --}}
                        <div x-data="linkInput" class="relative min-w-0 flex-1" x-on:click.outside="close()">
                            <input id="noteBody" x-ref="input" type="text" wire:model="noteBody" class="field" placeholder="{{ __("Noter vite : les joueurs ont promis d'aider Mira…") }}" autocomplete="off"
                                role="combobox" aria-autocomplete="list" aria-controls="noteBody-suggestions" :aria-expanded="open ? 'true' : 'false'"
                                x-on:input="search()" x-on:keydown="onKeydown($event)">
                            <ul id="noteBody-suggestions" x-show="open" x-cloak role="listbox" class="absolute inset-x-0 top-full z-20 mt-1 max-h-64 overflow-auto rounded-md border border-stone-200 bg-white py-1 text-sm shadow-lg">
                                <template x-for="(item, index) in items" :key="item.id">
                                    <li role="option" :aria-selected="index === active ? 'true' : 'false'" x-on:mousedown.prevent="choose(item)"
                                        :class="index === active ? 'bg-codex-soft text-codex' : ''" class="flex cursor-pointer justify-between gap-3 px-3 py-1.5">
                                        <span x-text="item.name"></span><span class="text-stone-500" x-text="item.type"></span>
                                    </li>
                                </template>
                            </ul>
                        </div>
                        <button type="submit" class="btn-primary">{{ __('Noter') }}</button>
                    </form>
                    @error('noteBody') <p class="error">{{ $message }}</p> @enderror
                    <ul class="mt-3 space-y-2 text-sm">
                        @foreach ($this->notes as $note)
                            <li wire:key="note-{{ $note->id }}" class="flex gap-3">
                                <time class="shrink-0 font-mono text-xs text-stone-500" datetime="{{ $note->created_at->toIso8601String() }}">{{ $note->created_at->timezone(config('app.timezone'))->isoFormat('LT') }}</time>
                                <div class="min-w-0 flex-1">
                                    {{ \App\Support\EntityLinks::render($note->body, $campaign) }}
                                    @if ($note->scene)
                                        <span class="text-xs text-stone-500">· {{ $note->scene->name }}</span>
                                    @endif
                                </div>
                                <button type="button" wire:click="deleteNote({{ $note->id }})" wire:confirm="{{ __('Supprimer cette note ?') }}" class="shrink-0 text-xs text-stone-400 hover:text-red-700" aria-label="{{ __('Supprimer la note') }}">✕</button>
                            </li>
                        @endforeach
                    </ul>
                </section>
            </div>

            <aside class="space-y-4">
                {{-- À jouer --}}
                <section class="rounded-xl border border-codex/30 bg-white p-4 shadow-sm">
                    <h2 class="mb-2 text-sm font-semibold tracking-wide text-codex uppercase">{{ __('À jouer') }}</h2>
                    <ul class="space-y-1 text-sm">
                        @forelse ($this->toPlay as $item)
                            <li wire:key="toplay-{{ $item->id }}" class="flex items-start gap-2">
                                <button type="button" wire:click="markPlayed({{ $item->id }})" class="mt-0.5 h-5 w-5 shrink-0 rounded border border-stone-300 hover:border-codex hover:bg-codex-soft" aria-label="{{ __('Marquer « :label » comme joué', ['label' => $item->body]) }}"></button>
                                <span class="min-w-0 flex-1">
                                    @if ($item->rule)
                                        <a href="{{ route('rules.show', [$campaign, $item->rule]) }}" target="_blank" rel="noopener" class="link"><span class="text-xs text-stone-500">{{ __('Règle ·') }}</span> {{ $item->body }}</a>
                                    @else
                                        {{ $item->body }}
                                    @endif
                                    @if ($item->character)
                                        <span class="block text-xs font-medium text-flow">{{ __('Demandé par :name', ['name' => $item->character->entity->name]) }}</span>
                                    @endif
                                    @if ($item->scene)
                                        <span class="block text-xs text-stone-500">{{ $item->scene->name }}</span>
                                    @endif
                                </span>
                            </li>
                        @empty
                            <li class="text-stone-500">{{ __('Rien en attente.') }}</li>
                        @endforelse
                    </ul>
                    <form wire:submit="addToPlay" class="mt-3 space-y-2">
                        <label for="toPlayBody" class="sr-only">{{ __('Nouvel élément à jouer') }}</label>
                        <input id="toPlayBody" type="text" wire:model="toPlayBody" class="field py-1.5 text-sm" placeholder="{{ __('Un orage éclate…') }}" autocomplete="off">
                        @error('toPlayBody') <p class="error">{{ $message }}</p> @enderror
                        <div class="flex items-center justify-between gap-2">
                            @if ($scene)
                                <label class="flex items-center gap-2 text-xs text-stone-600"><input type="checkbox" wire:model="toPlayForScene"> {{ __('Pour cette scène') }}</label>
                            @endif
                            <button type="submit" class="btn-secondary ml-auto min-h-0 py-1 text-sm">{{ __('Ajouter') }}</button>
                        </div>
                    </form>
                </section>

                {{-- Écran de table : ce que voient les joueurs sur le second écran. --}}
                @cannot('use-feature', ['table', $campaign])
                    <section class="rounded-xl border border-dashed border-stone-300 bg-stone-50 p-4 text-sm text-stone-500">
                        <h2 class="mb-1 flex items-center gap-1 text-sm font-semibold tracking-wide uppercase">{{ __('Écran de table') }} <x-premium /></h2>
                        <p>{{ __('Fonction Premium, non comprise dans la formule du propriétaire de la campagne. Rien n’est effacé : tout revient dès le passage à Premium.') }}</p>
                    </section>
                @else
                <section class="rounded-xl border border-stone-200 bg-white p-4 shadow-sm">
                    <div class="mb-2 flex items-center gap-2">
                        <h2 class="mr-auto flex items-center gap-1 text-sm font-semibold tracking-wide text-stone-500 uppercase">{{ __('Écran de table') }} <x-premium feature="table" /></h2>
                        <a href="{{ route('table.screen', $campaign) }}" target="codexflow-table" class="link text-sm">{{ __('Ouvrir ↗') }}</a>
                    </div>
                    <p class="flex items-center gap-2 text-sm">
                        <span class="min-w-0 flex-1 truncate"><span class="text-stone-500">{{ __('Affiché :') }}</span> <span class="font-medium">{{ $this->tableLabel }}</span></span>
                        @if ($campaign->table_display)
                            <button type="button" wire:click="clearTable" class="shrink-0 text-xs text-stone-500 hover:text-red-700">{{ __('Vider') }}</button>
                        @endif
                    </p>
                    <p class="mt-1 text-xs text-stone-500">{{ __("Ouvrez l'écran, glissez la fenêtre sur la télé ou le projecteur, puis « Plein écran ».") }}</p>
                    <label class="mt-2 flex items-center gap-2 text-sm">
                        <input type="checkbox" wire:click="toggleTableShare" @checked($campaign->table_shared)>
                        {{ __('Partager avec les joueurs') }} <span class="text-xs text-stone-500">{{ __('(sur leur appareil)') }}</span>
                    </label>

                    <div class="mt-3 space-y-2">
                        @if ($this->tableDocuments->isNotEmpty())
                            <div class="flex gap-2">
                                <label for="tableDocument" class="sr-only">{{ __('Carte ou document à montrer') }}</label>
                                <select id="tableDocument" wire:model="tableDocumentId" class="field min-w-0 py-1.5 text-sm">
                                    <option value="">{{ __('Carte, image ou document…') }}</option>
                                    @foreach ($this->tableDocuments as $document)
                                        <option value="{{ $document->id }}">{{ $document->title }}</option>
                                    @endforeach
                                </select>
                                <button type="button" wire:click="showDocument()" class="btn-secondary min-h-0 py-1 text-sm">{{ __('Montrer') }}</button>
                            </div>
                            @error('tableDocumentId') <p class="error">{{ $message }}</p> @enderror
                        @endif
                        <div class="flex gap-2">
                            <div class="min-w-0 flex-1">
                                <label for="tableEntity" class="sr-only">{{ __('Fiche à montrer (zone publique)') }}</label>
                                <x-entity-picker id="tableEntity" model="tableEntityId" />
                            </div>
                            <button type="button" wire:click="showEntity" class="btn-secondary min-h-0 py-1 text-sm">{{ __('Montrer') }}</button>
                        </div>
                        @error('tableEntityId') <p class="error">{{ $message }}</p> @enderror
                        <form wire:submit="showText" class="flex gap-2">
                            <label for="tableText" class="sr-only">{{ __('Annonce à afficher') }}</label>
                            <input id="tableText" type="text" wire:model="tableText" class="field min-w-0 py-1.5 text-sm" placeholder="{{ __('Annonce : « Trois jours plus tard… »') }}" autocomplete="off" maxlength="500">
                            <button type="submit" class="btn-secondary min-h-0 py-1 text-sm">{{ __('Montrer') }}</button>
                        </form>
                        @error('tableText') <p class="error">{{ $message }}</p> @enderror
                        <p class="text-xs text-stone-500">{{ __("Une fiche s'affiche sans sa zone MJ.") }}</p>
                    </div>

                    @if ($this->tableMaps->isNotEmpty())
                        <ul class="mt-3 flex flex-wrap gap-1.5">
                            @foreach ($this->tableMaps as $map)
                                <li wire:key="session-map-{{ $map->id }}">
                                    <button type="button" wire:click="showOnTable('map', {{ $map->id }})" class="rounded-md border border-stone-200 px-2 py-0.5 text-xs text-stone-600 hover:border-codex hover:text-codex" aria-label="{{ __("Montrer la carte « :name » sur l'écran de table", ['name' => $map->name]) }}">{{ $map->name }}</button>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                    <p class="mt-3 flex gap-4 text-sm">
                        @can('use-feature', ['maps', $campaign])
                            <a href="{{ route('maps.index', $campaign) }}" class="link" wire:navigate>{{ __('Cartes →') }}</a>
                        @endcan
                        <a href="{{ route('table.remote', $campaign) }}" class="link" wire:navigate>{{ __('Télécommande →') }}</a>
                    </p>
                </section>
                @endcannot

                {{-- Révéler une information ou donner un objet en pleine partie. --}}
                <livewire:characters.give :campaign="$campaign" key="give-session" />

                {{-- Épinglé --}}
                <section class="rounded-xl border border-stone-200 bg-white p-4 shadow-sm">
                    <h2 class="mb-2 text-sm font-semibold tracking-wide text-stone-500 uppercase">{{ __('Épinglé') }}</h2>
                    <div class="space-y-2">
                        @forelse ($this->pins as $pinned)
                            <div class="relative">
                                <x-context-card :entity="$pinned" :campaign="$campaign" :field-definitions="$fieldDefinitions" source="pin" />
                                <button type="button" wire:click="unpin({{ $pinned->id }})" class="absolute top-1 right-7 rounded px-1 text-xs text-stone-400 hover:text-red-700" aria-label="{{ __('Désépingler :name', ['name' => $pinned->name]) }}">✕</button>
                            </div>
                        @empty
                            <p class="text-sm text-stone-500">{{ __('Gardez ici les fiches dont vous avez souvent besoin : un PNJ récurrent, une règle maison…') }}</p>
                        @endforelse
                    </div>
                    <div class="mt-3 flex gap-2">
                        <div class="min-w-0 flex-1">
                            <label for="pinPicker" class="sr-only">{{ __('Épingler une fiche') }}</label>
                            <x-entity-picker id="pinPicker" model="pickedPinId" />
                            @error('pickedPinId') <p class="error">{{ $message }}</p> @enderror
                        </div>
                        <button type="button" wire:click="pin" class="btn-secondary">{{ __('Épingler') }}</button>
                    </div>
                </section>
            </aside>
        </div>
    @endif
    @if ($preview = $this->preview)
        <div class="fixed inset-y-0 right-0 z-40 flex w-full max-w-md flex-col border-l border-stone-200 bg-parchment shadow-xl" role="dialog" aria-modal="false" aria-labelledby="preview-title"
            wire:key="preview-{{ $preview->id }}" x-on:keydown.escape.window="$wire.set('previewId', null)">
            <div class="flex items-center gap-2 border-b border-stone-200 bg-white px-4 py-3">
                <h2 id="preview-title" class="mr-auto truncate font-semibold">{{ $preview->name }}</h2>
                <a href="{{ route('entities.show', [$campaign, $preview]) }}" data-leave class="btn-secondary py-1 text-sm" wire:navigate>{{ __('Ouvrir la fiche') }}</a>
                <button type="button" wire:click="$set('previewId', null)" class="rounded px-2 py-1 text-stone-500 hover:bg-stone-100 hover:text-ink" aria-label="{{ __('Fermer') }}">✕</button>
            </div>
            <div class="overflow-y-auto p-4">
                <x-context-card :entity="$preview" :campaign="$campaign" :field-definitions="$fieldDefinitions" source="preview" open />
            </div>
        </div>
    @endif
</div>
