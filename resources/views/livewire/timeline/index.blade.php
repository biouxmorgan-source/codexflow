<div>
    <nav class="mb-2 text-sm text-stone-500">
        <a href="{{ route('campaigns.index') }}" class="crumb" wire:navigate>{{ __('Mes campagnes') }}</a>
        › <a href="{{ route('campaigns.show', $campaign) }}" class="crumb" wire:navigate>{{ $campaign->name }}</a>
    </nav>

    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold">{{ __('Chronologie') }}</h1>
            <p class="mt-1 max-w-2xl text-sm text-stone-600">
                @if ($this->isGameMaster)
                    {{ __('L’histoire du monde, ce que vous avez prévu et ce qui s’est passé en jeu. Les dates sont libres : « 12 mars 1924 », « Jour 3 », « Nuit 2 ». Les joueurs ne voient que les événements visibles.') }}
                @else
                    {{ __('Ce que votre groupe sait de l’histoire du monde et de vos aventures.') }}
                @endif
            </p>
        </div>
        @if ($this->isGameMaster && ! $editing)
            <div class="flex flex-wrap gap-2">
                <button type="button" wire:click="create('played')" class="btn-secondary">{{ __('Noter un événement joué') }}</button>
                <button type="button" wire:click="create('{{ in_array($kind, \App\Models\TimelineEvent::KINDS, true) ? $kind : 'world' }}')" class="btn-primary">{{ __('Nouvel événement') }}</button>
            </div>
        @endif
    </div>

    @if ($editing)
        <form wire:submit="save" class="mb-6 space-y-4 rounded-xl border border-flow/40 bg-white p-6 shadow-sm">
            <h2 class="font-semibold">{{ $editingId ? __('Modifier l’événement') : __('Nouvel événement') }}</h2>
            <div class="grid gap-4 md:grid-cols-[12rem_14rem_1fr]">
                <div>
                    <label for="event-kind" class="label">{{ __('Type') }}</label>
                    <select id="event-kind" wire:model.live="formKind" class="field">
                        @foreach ($kinds as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="event-date" class="label">{{ __('Date') }} <span class="font-normal text-stone-500">{{ __('(libre)') }}</span></label>
                    <input id="event-date" type="text" wire:model="dateLabel" class="field" maxlength="100" placeholder="{{ __('12 mars 1924, Jour 3…') }}">
                    @error('dateLabel') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="event-title" class="label">{{ __('Événement') }}</label>
                    <input id="event-title" type="text" wire:model="title" class="field" maxlength="255" placeholder="{{ __('Incendie du vieux moulin') }}" autofocus>
                    @error('title') <p class="error">{{ $message }}</p> @enderror
                </div>
            </div>
            <div>
                <label for="event-description" class="label">{{ __('Détails') }} <span class="font-normal text-stone-500">{{ __('(facultatif, [[ ]] pour lier une fiche)') }}</span></label>
                <x-link-textarea id="event-description" model="description" rows="3" />
                @error('description') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div class="flex flex-wrap items-end gap-6">
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" wire:model="public">
                    {{ __('Visible des joueurs') }}
                </label>
                @if ($formKind === 'played' && $sessions->isNotEmpty())
                    <div>
                        <label for="event-session" class="label">{{ __('Séance') }}</label>
                        <select id="event-session" wire:model="sessionId" class="field py-1.5">
                            <option value="">{{ __('Aucune') }}</option>
                            @foreach ($sessions as $session)
                                <option value="{{ $session->id }}">{{ $session->label() }}</option>
                            @endforeach
                        </select>
                        @error('sessionId') <p class="error">{{ $message }}</p> @enderror
                    </div>
                @endif
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="submit" class="btn-primary">{{ __('Enregistrer') }}</button>
                <button type="button" wire:click="cancel" class="btn-secondary">{{ __('Annuler') }}</button>
                @if ($editingId)
                    <button type="button" wire:click="delete({{ $editingId }})" wire:confirm="{{ __('Supprimer cet événement ?') }}" class="ml-auto text-sm text-red-700 hover:underline">{{ __('Supprimer') }}</button>
                @endif
            </div>
        </form>
    @endif

    <div class="mb-6 flex flex-wrap gap-2 text-sm" role="tablist">
        @foreach (['' => __('Tout')] + $kinds as $value => $label)
            <button type="button" wire:click="$set('kind', '{{ $value }}')" role="tab" aria-selected="{{ $kind === $value ? 'true' : 'false' }}" @class([
                'rounded-full border px-3 py-1',
                'border-codex bg-codex text-white' => $kind === $value,
                'border-stone-200 bg-white text-stone-700 hover:border-codex' => $kind !== $value,
            ])>{{ $label }}</button>
        @endforeach
    </div>

    @if ($this->events->isEmpty())
        <div class="rounded-xl border border-dashed border-stone-300 bg-white p-10 text-center">
            <p class="text-lg font-medium">{{ __('Aucun événement pour l’instant.') }}</p>
            @if ($this->isGameMaster)
                <p class="mt-1 text-stone-600">{{ __('Notez les grandes dates du monde, ce qui doit arriver, puis ce qui s’est passé à la table.') }}</p>
            @endif
        </div>
    @else
        <ol class="relative ml-3 space-y-5 border-l-2 border-stone-200 pl-6">
            @foreach ($this->events as $event)
                <li wire:key="event-{{ $event->id }}" id="evenement-{{ $event->id }}" class="relative scroll-mt-20">
                    <span @class([
                        'absolute top-1.5 -left-[33px] size-4 rounded-full border-2 border-white shadow',
                        'bg-stone-500' => $event->kind === 'world',
                        'bg-codex' => $event->kind === 'planned',
                        'bg-flow' => $event->kind === 'played',
                    ]) aria-hidden="true"></span>
                    <div class="rounded-xl border border-stone-200 bg-white p-4 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <div class="min-w-0">
                                <p class="flex flex-wrap items-center gap-2 text-xs">
                                    @if ($event->date_label)
                                        <span class="font-semibold text-stone-700">{{ $event->date_label }}</span>
                                    @endif
                                    <span @class([
                                        'rounded-full px-2 py-0.5',
                                        'bg-stone-100 text-stone-600' => $event->kind === 'world',
                                        'bg-codex/10 text-codex' => $event->kind === 'planned',
                                        'bg-flow/15 text-flow' => $event->kind === 'played',
                                    ])>{{ $kinds[$event->kind] }}</span>
                                    @if ($event->playSession)
                                        <span class="text-stone-500">{{ $event->playSession->label() }}@if ($event->scene) · {{ $event->scene->name }}@endif</span>
                                    @endif
                                    @if ($this->isGameMaster && $event->zone === \App\Enums\Zone::GameMaster)
                                        <span class="rounded-full bg-amber-100 px-2 py-0.5 text-amber-700">{{ __('Zone MJ') }}</span>
                                    @endif
                                </p>
                                <h2 class="mt-1 font-semibold">{{ $event->title }}</h2>
                            </div>
                            @if ($this->isGameMaster)
                                <div class="flex shrink-0 items-center gap-1 text-sm">
                                    <button type="button" wire:click="move({{ $event->id }}, -1)" @disabled($loop->first && $kind === '') class="rounded px-1.5 text-stone-500 hover:text-codex disabled:opacity-30" aria-label="{{ __('Monter :name', ['name' => $event->title]) }}">↑</button>
                                    <button type="button" wire:click="move({{ $event->id }}, 1)" @disabled($loop->last && $kind === '') class="rounded px-1.5 text-stone-500 hover:text-codex disabled:opacity-30" aria-label="{{ __('Descendre :name', ['name' => $event->title]) }}">↓</button>
                                    <button type="button" wire:click="edit({{ $event->id }})" class="link ml-2">{{ __('Modifier') }}</button>
                                </div>
                            @endif
                        </div>
                        @if ($event->description)
                            <div class="mt-2 text-sm text-stone-700">{{ $this->linkedDescription($event) }}</div>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>
    @endif
</div>
