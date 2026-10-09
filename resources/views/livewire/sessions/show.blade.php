<div>
    <nav class="mb-2 text-sm text-stone-500">
        <a href="{{ route('campaigns.index') }}" class="crumb" wire:navigate>{{ __('Mes campagnes') }}</a>
        › <a href="{{ route('campaigns.show', $campaign) }}" class="crumb" wire:navigate>{{ $campaign->name }}</a>
        › <a href="{{ route('sessions.live', $campaign) }}" class="crumb" wire:navigate>{{ __('Sessions') }}</a>
    </nav>

    <h1 class="text-2xl font-semibold">{{ $playSession->label() }}</h1>
    <p class="mt-1 mb-6 text-sm text-stone-600">
        @if ($playSession->ended_at)
            {{ __('Le :date, de :start à :end', ['date' => $playSession->started_at->isoFormat('dddd D MMMM YYYY'), 'start' => $playSession->started_at->isoFormat('LT'), 'end' => $playSession->ended_at->isoFormat('LT')]) }}
        @else
            {{ __('Le :date, de :start (en cours)', ['date' => $playSession->started_at->isoFormat('dddd D MMMM YYYY'), 'start' => $playSession->started_at->isoFormat('LT')]) }}
        @endif
    </p>

    <section class="mb-6 rounded-xl border border-stone-200 bg-white p-6 shadow-sm" aria-labelledby="summary-title">
        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
            <h2 id="summary-title" class="font-semibold">{{ __('Résumé') }}</h2>
            @unless ($editingSummary)
                <button type="button" wire:click="$set('editingSummary', true)" class="btn-secondary">{{ $playSession->summary ? __('Modifier') : __('Écrire le résumé') }}</button>
            @endunless
        </div>
        @if ($editingSummary)
            <form wire:submit="saveSummary" class="space-y-3">
                <label for="summary" class="sr-only">{{ __('Résumé') }}</label>
                <x-link-textarea id="summary" model="summary" rows="6" :placeholder="__('Ce qui s’est passé pendant la séance…')" />
                @error('summary') <p class="text-sm text-red-700">{{ $message }}</p> @enderror
                <div class="flex gap-2">
                    <button type="submit" class="btn-primary">{{ __('Enregistrer') }}</button>
                    <button type="button" wire:click="$set('editingSummary', false)" class="btn-secondary">{{ __('Annuler') }}</button>
                </div>
            </form>
        @elseif ($playSession->summary)
            <div class="text-sm text-stone-700">{{ \App\Support\EntityLinks::render($playSession->summary, $campaign) }}</div>
        @else
            <p class="text-sm text-stone-500">{{ __('Pas encore de résumé pour cette séance.') }}</p>
        @endif
    </section>

    <section class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
        <h2 class="mb-3 font-semibold">{{ __('Notes') }}</h2>
        @forelse ($notes->groupBy(fn ($note) => $note->scene?->name ?? __('Hors scène')) as $sceneName => $sceneNotes)
            <h3 class="mt-4 mb-1 text-sm font-semibold text-flow first:mt-0">{{ $sceneName }}</h3>
            <ul class="space-y-1 text-sm">
                @foreach ($sceneNotes as $note)
                    <li class="flex gap-3">
                        <time class="shrink-0 font-mono text-xs text-stone-500">{{ $note->created_at->isoFormat('LT') }}</time>
                        <div class="min-w-0">{{ \App\Support\EntityLinks::render($note->body, $campaign) }}</div>
                    </li>
                @endforeach
            </ul>
        @empty
            <p class="text-sm text-stone-500">{{ __('Aucune note prise pendant cette session.') }}</p>
        @endforelse
    </section>

    <section class="mt-6 rounded-xl border border-stone-200 bg-white p-6 shadow-sm" aria-labelledby="events-title">
        <h2 id="events-title" class="mb-3 font-semibold">{{ __('Événements joués') }}</h2>
        @forelse ($events as $event)
            <article wire:key="session-event-{{ $event->id }}" class="border-t border-stone-100 py-2 first:border-t-0 first:pt-0 text-sm">
                <p class="font-medium">
                    @if ($event->date_label)
                        <span class="text-stone-500">{{ $event->date_label }} ·</span>
                    @endif
                    {{ $event->title }}
                </p>
                @if ($event->description)
                    <div class="text-stone-700">{{ \App\Support\EntityLinks::render($event->description, $campaign) }}</div>
                @endif
            </article>
        @empty
            <p class="text-sm text-stone-500">{{ __('Aucun événement noté pendant cette session.') }}</p>
        @endforelse
    </section>

    <section class="mt-6 rounded-xl border border-stone-200 bg-white p-6 shadow-sm" aria-labelledby="reveals-title">
        <h2 id="reveals-title" class="mb-3 font-semibold">{{ __('Révélé ou donné') }}</h2>
        @forelse ($reveals->groupBy(fn ($grant) => $grant->character?->entity?->name ?? '') as $characterName => $grants)
            <h3 class="mt-4 mb-1 text-sm font-semibold text-flow first:mt-0">{{ $characterName }}</h3>
            <ul class="space-y-1 text-sm">
                @foreach ($grants as $grant)
                    <li wire:key="session-grant-{{ $grant->id }}" class="flex gap-3">
                        <time class="shrink-0 font-mono text-xs text-stone-500">{{ $grant->created_at->isoFormat('LT') }}</time>
                        <span>{{ \App\Models\CharacterGrant::kinds()[$grant->kind] ?? '' }} · {{ $grant->label() }}@if ($grant->scene) <span class="text-stone-500">({{ $grant->scene->name }})</span>@endif</span>
                    </li>
                @endforeach
            </ul>
        @empty
            <p class="text-sm text-stone-500">{{ __('Rien n’a été révélé ni donné pendant cette session.') }}</p>
        @endforelse
    </section>

    <section class="mt-6 rounded-xl border border-stone-200 bg-white p-6 shadow-sm" aria-labelledby="player-notes-title">
        <h2 id="player-notes-title" class="mb-3 font-semibold">{{ __('Notes des joueurs') }}</h2>
        @forelse ($playerNotes as $note)
            <article wire:key="player-note-{{ $note->id }}" class="border-t border-stone-100 py-3 first:border-t-0 first:pt-0">
                <p class="mb-1 flex flex-wrap items-baseline gap-x-2 text-xs text-stone-500">
                    <span class="font-semibold text-flow">{{ $note->character->entity->name }}</span>
                    <time>{{ $note->created_at->isoFormat('LT') }}</time>
                    <span>· {{ $note->visibilityLabel() }}</span>
                </p>
                <div class="text-sm text-stone-700">{{ \App\Support\EntityLinks::render($note->body, $campaign) }}</div>
            </article>
        @empty
            <p class="text-sm text-stone-500">{{ __('Aucune note de joueur pendant cette session.') }}</p>
        @endforelse
    </section>
</div>
