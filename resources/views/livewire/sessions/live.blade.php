<div x-data="{
        focus: (() => { try { return localStorage.getItem('codexflow.focus') === '1' } catch (e) { return false } })(),
        toggle() {
            this.focus = ! this.focus;
            try { localStorage.setItem('codexflow.focus', this.focus ? '1' : '0') } catch (e) {}
        },
    }"
    x-effect="document.body.classList.toggle('focus-mode', focus)"
    x-on:livewire:navigating.window="document.body.classList.remove('focus-mode')">
    @php($session = $this->session)

    <div class="mb-4 flex flex-wrap items-center gap-3">
        <div class="min-w-0 flex-1">
            <nav class="text-sm text-stone-500 [.focus-mode_&]:hidden">
                <a href="{{ route('campaigns.index') }}" class="crumb" wire:navigate>Mes campagnes</a>
                › <a href="{{ route('campaigns.show', $campaign) }}" class="crumb" wire:navigate>{{ $campaign->name }}</a>
            </nav>
            <h1 class="text-2xl font-semibold">{{ $session ? $session->label() : 'Mode Session' }}</h1>
            @if ($session)
                <p class="text-sm text-stone-500">Commencée {{ $session->started_at->locale('fr')->diffForHumans() }}</p>
            @endif
        </div>
        @if ($session)
            <button type="button" x-on:click="toggle()" class="btn-secondary" x-text="focus ? 'Quitter le mode focus' : 'Mode focus'">Mode focus</button>
            <button type="button" wire:click="end" wire:confirm="Terminer la session ? Les notes restent consultables." class="btn-secondary">Terminer la session</button>
        @endif
    </div>

    @if (! $session)
        <div class="grid gap-6 lg:grid-cols-3">
            <section class="rounded-xl border border-stone-200 bg-white p-8 text-center shadow-sm lg:col-span-2">
                <p class="text-lg font-medium">Prêt à jouer ?</p>
                <p class="mx-auto mt-1 max-w-md text-stone-600">La session reprend à la scène en cours ou à la prochaine scène à jouer. Ses fiches, vos éléments « À jouer » et vos fiches épinglées s'affichent tout seuls.</p>
                <button type="button" wire:click="start" class="btn-primary mt-6">Démarrer la session {{ ($this->pastSessions->max('number') ?? 0) + 1 }}</button>
            </section>
            <section class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="mb-2 font-semibold">Sessions passées</h2>
                @forelse ($this->pastSessions as $past)
                    <a href="{{ route('sessions.show', [$campaign, $past]) }}" class="-mx-2 flex justify-between gap-2 rounded-md px-2 py-1 text-sm text-codex hover:bg-codex-soft" wire:navigate>
                        <span>{{ $past->label() }}</span>
                        <span class="text-stone-500">{{ $past->started_at->format('d/m/Y') }} · {{ $past->notes_count }} note{{ $past->notes_count > 1 ? 's' : '' }}</span>
                    </a>
                @empty
                    <p class="text-sm text-stone-500">Aucune pour l'instant.</p>
                @endforelse
            </section>
        </div>
    @else
        @php($scene = $session->currentScene)
        <div class="grid gap-4 lg:grid-cols-3">
            {{-- Maintenant --}}
            <div class="space-y-4 lg:col-span-2">
                <section class="rounded-xl border border-flow/30 bg-white p-4 shadow-sm">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h2 class="mr-auto text-sm font-semibold tracking-wide text-flow uppercase">Maintenant</h2>
                        <label for="currentScene" class="sr-only">Scène en cours</label>
                        <select id="currentScene" wire:change="setScene($event.target.value || null)" class="field w-auto py-1.5 text-sm">
                            <option value="">— Aucune scène —</option>
                            @foreach ($this->scenes->groupBy(fn ($s) => $s->scenario->name) as $scenarioName => $scenarioScenes)
                                <optgroup label="{{ $scenarioName }}">
                                    @foreach ($scenarioScenes as $option)
                                        <option value="{{ $option->id }}" @selected($scene?->id === $option->id)>{{ $option->name }} · {{ $option->status->label() }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        @if ($scene)
                            <button type="button" wire:click="nextScene" class="btn-primary min-h-0 py-1.5 text-sm">Scène jouée, suivante →</button>
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
                    @else
                        <p class="text-sm text-stone-500">Aucune scène en cours. Choisissez-en une, ou improvisez : vos notes restent rattachées à la session.</p>
                    @endif
                </section>

                @if ($this->cards->isNotEmpty())
                    <section>
                        <h2 class="mb-2 text-sm font-semibold tracking-wide text-stone-500 uppercase">Fiches utiles</h2>
                        <div class="grid gap-2 md:grid-cols-2">
                            @foreach ($this->cards as $card)
                                <x-context-card :entity="$card['entity']" :campaign="$campaign" :field-definitions="$fieldDefinitions" :note="$card['note']" :source="$card['source']" />
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($this->rules->isNotEmpty() || $this->documents->isNotEmpty())
                    <div class="grid gap-4 md:grid-cols-2">
                        @if ($this->rules->isNotEmpty())
                            <section>
                                <h2 class="mb-2 text-sm font-semibold tracking-wide text-stone-500 uppercase">Règles</h2>
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
                                                <p class="mt-2 rounded-lg bg-flow/5 p-2 text-sm whitespace-pre-line text-stone-700"><span class="font-medium text-flow">MJ :</span> {{ $rule->gm_notes }}</p>
                                            @endif
                                            <a href="{{ route('rules.show', [$campaign, $rule]) }}" target="_blank" rel="noopener" class="mt-2 inline-block text-xs link">Ouvrir la règle ↗</a>
                                        </details>
                                    @endforeach
                                </div>
                            </section>
                        @endif
                        @if ($this->documents->isNotEmpty())
                            <section>
                                <h2 class="mb-2 text-sm font-semibold tracking-wide text-stone-500 uppercase">Documents</h2>
                                <ul class="space-y-2">
                                    @foreach ($this->documents as $document)
                                        <li wire:key="document-{{ $document->id }}">
                                            <a href="{{ route('documents.file', $document) }}" target="_blank" rel="noopener" class="flex items-center gap-3 rounded-xl border border-stone-200 bg-white p-3 shadow-sm hover:border-codex/40">
                                                @if ($document->isImage())
                                                    <img src="{{ route('documents.file', $document) }}" alt="" loading="lazy" class="h-10 w-10 shrink-0 rounded-lg object-cover">
                                                @else
                                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-red-50 text-xs font-semibold text-red-700" aria-hidden="true">PDF</span>
                                                @endif
                                                <span class="min-w-0 flex-1 truncate font-medium text-codex">{{ $document->title }}</span>
                                                <span class="text-xs text-stone-500" aria-hidden="true">↗</span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </section>
                        @endif
                    </div>
                @endif

                <section class="rounded-xl border border-stone-200 bg-white p-4 shadow-sm">
                    <h2 class="mb-2 text-sm font-semibold tracking-wide text-stone-500 uppercase">Notes de session</h2>
                    <form wire:submit="addNote" class="flex gap-2">
                        <label for="noteBody" class="sr-only">Nouvelle note</label>
                        <input id="noteBody" type="text" wire:model="noteBody" class="field" placeholder="Noter vite : les joueurs ont promis d'aider Mira…" autocomplete="off">
                        <button type="submit" class="btn-primary">Noter</button>
                    </form>
                    @error('noteBody') <p class="error">{{ $message }}</p> @enderror
                    <ul class="mt-3 space-y-2 text-sm">
                        @foreach ($this->notes as $note)
                            <li wire:key="note-{{ $note->id }}" class="flex gap-3">
                                <time class="shrink-0 font-mono text-xs text-stone-500" datetime="{{ $note->created_at->toIso8601String() }}">{{ $note->created_at->timezone(config('app.timezone'))->format('H:i') }}</time>
                                <span class="min-w-0 flex-1">
                                    {{ \App\Support\EntityLinks::render($note->body, $campaign) }}
                                    @if ($note->scene)
                                        <span class="text-xs text-stone-500">· {{ $note->scene->name }}</span>
                                    @endif
                                </span>
                                <button type="button" wire:click="deleteNote({{ $note->id }})" wire:confirm="Supprimer cette note ?" class="shrink-0 text-xs text-stone-400 hover:text-red-700" aria-label="Supprimer la note">✕</button>
                            </li>
                        @endforeach
                    </ul>
                </section>
            </div>

            <aside class="space-y-4">
                {{-- À jouer --}}
                <section class="rounded-xl border border-codex/30 bg-white p-4 shadow-sm">
                    <h2 class="mb-2 text-sm font-semibold tracking-wide text-codex uppercase">À jouer</h2>
                    <ul class="space-y-1 text-sm">
                        @forelse ($this->toPlay as $item)
                            <li wire:key="toplay-{{ $item->id }}" class="flex items-start gap-2">
                                <button type="button" wire:click="markPlayed({{ $item->id }})" class="mt-0.5 h-5 w-5 shrink-0 rounded border border-stone-300 hover:border-codex hover:bg-codex-soft" aria-label="Marquer « {{ $item->body }} » comme joué"></button>
                                <span class="min-w-0 flex-1">
                                    @if ($item->rule)
                                        <a href="{{ route('rules.show', [$campaign, $item->rule]) }}" target="_blank" rel="noopener" class="link"><span class="text-xs text-stone-500">Règle ·</span> {{ $item->body }}</a>
                                    @else
                                        {{ $item->body }}
                                    @endif
                                    @if ($item->character)
                                        <span class="block text-xs font-medium text-flow">Demandé par {{ $item->character->entity->name }}</span>
                                    @endif
                                    @if ($item->scene)
                                        <span class="block text-xs text-stone-500">{{ $item->scene->name }}</span>
                                    @endif
                                </span>
                            </li>
                        @empty
                            <li class="text-stone-500">Rien en attente.</li>
                        @endforelse
                    </ul>
                    <form wire:submit="addToPlay" class="mt-3 space-y-2">
                        <label for="toPlayBody" class="sr-only">Nouvel élément à jouer</label>
                        <input id="toPlayBody" type="text" wire:model="toPlayBody" class="field py-1.5 text-sm" placeholder="Un orage éclate…" autocomplete="off">
                        @error('toPlayBody') <p class="error">{{ $message }}</p> @enderror
                        <div class="flex items-center justify-between gap-2">
                            @if ($scene)
                                <label class="flex items-center gap-2 text-xs text-stone-600"><input type="checkbox" wire:model="toPlayForScene"> Pour cette scène</label>
                            @endif
                            <button type="submit" class="btn-secondary ml-auto min-h-0 py-1 text-sm">Ajouter</button>
                        </div>
                    </form>
                </section>

                {{-- Révéler une information ou donner un objet en pleine partie. --}}
                <livewire:characters.give :campaign="$campaign" key="give-session" />

                {{-- Épinglé --}}
                <section class="rounded-xl border border-stone-200 bg-white p-4 shadow-sm">
                    <h2 class="mb-2 text-sm font-semibold tracking-wide text-stone-500 uppercase">Épinglé</h2>
                    <div class="space-y-2">
                        @forelse ($this->pins as $pinned)
                            <div class="relative">
                                <x-context-card :entity="$pinned" :campaign="$campaign" :field-definitions="$fieldDefinitions" source="pin" />
                                <button type="button" wire:click="unpin({{ $pinned->id }})" class="absolute top-1 right-7 rounded px-1 text-xs text-stone-400 hover:text-red-700" aria-label="Désépingler {{ $pinned->name }}">✕</button>
                            </div>
                        @empty
                            <p class="text-sm text-stone-500">Gardez ici les fiches dont vous avez souvent besoin : un PNJ récurrent, une règle maison…</p>
                        @endforelse
                    </div>
                    <div class="mt-3 flex gap-2">
                        <div class="min-w-0 flex-1">
                            <label for="pinPicker" class="sr-only">Épingler une fiche</label>
                            <x-entity-picker id="pinPicker" model="pickedPinId" />
                            @error('pickedPinId') <p class="error">{{ $message }}</p> @enderror
                        </div>
                        <button type="button" wire:click="pin" class="btn-secondary">Épingler</button>
                    </div>
                </section>
            </aside>
        </div>
    @endif
</div>
