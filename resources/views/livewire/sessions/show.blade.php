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

    <section class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
        <h2 class="mb-3 font-semibold">{{ __('Notes') }}</h2>
        @forelse ($notes->groupBy(fn ($note) => $note->scene?->name ?? __('Hors scène')) as $sceneName => $sceneNotes)
            <h3 class="mt-4 mb-1 text-sm font-semibold text-flow first:mt-0">{{ $sceneName }}</h3>
            <ul class="space-y-1 text-sm">
                @foreach ($sceneNotes as $note)
                    <li class="flex gap-3">
                        <time class="shrink-0 font-mono text-xs text-stone-500">{{ $note->created_at->isoFormat('LT') }}</time>
                        <span>{{ \App\Support\EntityLinks::render($note->body, $campaign) }}</span>
                    </li>
                @endforeach
            </ul>
        @empty
            <p class="text-sm text-stone-500">{{ __('Aucune note prise pendant cette session.') }}</p>
        @endforelse
    </section>
</div>
