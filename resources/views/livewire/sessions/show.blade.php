<div>
    <nav class="mb-2 text-sm text-stone-500">
        <a href="{{ route('campaigns.index') }}" class="crumb" wire:navigate>Mes campagnes</a>
        › <a href="{{ route('campaigns.show', $campaign) }}" class="crumb" wire:navigate>{{ $campaign->name }}</a>
        › <a href="{{ route('sessions.live', $campaign) }}" class="crumb" wire:navigate>Sessions</a>
    </nav>

    <h1 class="text-2xl font-semibold">{{ $playSession->label() }}</h1>
    <p class="mt-1 mb-6 text-sm text-stone-600">
        Le {{ $playSession->started_at->locale('fr')->isoFormat('dddd D MMMM YYYY') }},
        de {{ $playSession->started_at->format('H:i') }}
        @if ($playSession->ended_at) à {{ $playSession->ended_at->format('H:i') }} @else (en cours) @endif
    </p>

    <section class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
        <h2 class="mb-3 font-semibold">Notes</h2>
        @forelse ($notes->groupBy(fn ($note) => $note->scene?->name ?? 'Hors scène') as $sceneName => $sceneNotes)
            <h3 class="mt-4 mb-1 text-sm font-semibold text-flow first:mt-0">{{ $sceneName }}</h3>
            <ul class="space-y-1 text-sm">
                @foreach ($sceneNotes as $note)
                    <li class="flex gap-3">
                        <time class="shrink-0 font-mono text-xs text-stone-500">{{ $note->created_at->format('H:i') }}</time>
                        <span>{{ \App\Support\EntityLinks::render($note->body, $campaign) }}</span>
                    </li>
                @endforeach
            </ul>
        @empty
            <p class="text-sm text-stone-500">Aucune note prise pendant cette session.</p>
        @endforelse
    </section>
</div>
