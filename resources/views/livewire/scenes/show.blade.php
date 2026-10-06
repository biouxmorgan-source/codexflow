<div>
    <nav class="mb-2 text-sm text-stone-500">
        <a href="{{ route('campaigns.index') }}" class="hover:text-codex" wire:navigate>Mes campagnes</a>
        › <a href="{{ route('campaigns.show', $campaign) }}" class="hover:text-codex" wire:navigate>{{ $campaign->name }}</a>
        › <a href="{{ route('scenarios.index', $campaign) }}" class="hover:text-codex" wire:navigate>{{ $scene->scenario->name }}</a>
    </nav>

    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            @if ($scene->chapter)
                <p class="text-xs font-semibold tracking-wide text-stone-500 uppercase">{{ $scene->chapter }}</p>
            @endif
            <h1 class="text-2xl font-semibold">{{ $scene->name }}</h1>
            <div class="mt-2">
                <label for="status" class="sr-only">Statut</label>
                <select id="status" wire:change="setStatus($event.target.value)" class="rounded-full border-0 py-1 pr-8 pl-3 text-sm font-medium {{ $scene->status->badge() }}">
                    @foreach (\App\Enums\SceneStatus::cases() as $status)
                        <option value="{{ $status->value }}" @selected($scene->status === $status)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <a href="{{ route('scenes.edit', [$campaign, $scene]) }}" class="btn-secondary" wire:navigate>Modifier la scène</a>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="rounded-xl border border-flow/30 bg-white p-6 shadow-sm lg:col-span-2">
            <h2 class="mb-3 font-semibold text-flow">Préparation</h2>
            @if ($scene->description)
                <div class="text-stone-700">{{ $description }}</div>
            @else
                <p class="text-sm text-stone-500">Rien de préparé pour l'instant.</p>
            @endif
        </section>

        <aside class="space-y-6">
            <section class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="mb-3 font-semibold">Dans cette scène</h2>
                @if ($entities->isEmpty())
                    <p class="text-sm text-stone-500">Aucune fiche liée.</p>
                @else
                    <ul class="space-y-3">
                        @foreach ($entities as $entity)
                            @php($state = $entity->campaignStates->first())
                            <li wire:key="entity-{{ $entity->id }}">
                                <a href="{{ route('entities.show', [$campaign, $entity]) }}" class="flex items-start gap-3 rounded-lg p-1 hover:bg-stone-50" wire:navigate>
                                    @if ($entity->hasImage())
                                        <img src="{{ route('entities.image', $entity) }}?v={{ $entity->updated_at?->timestamp }}" alt="" class="h-10 w-10 shrink-0 rounded-lg object-cover">
                                    @else
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-stone-100 text-sm font-semibold text-stone-500" aria-hidden="true">{{ mb_strtoupper(mb_substr($entity->name, 0, 1)) }}</span>
                                    @endif
                                    <span class="min-w-0">
                                        <span class="block font-medium text-codex">{{ $entity->name }}</span>
                                        <span class="block text-xs text-stone-500">{{ $entity->type->name }}@if ($state?->status) · {{ $state->status }}@endif</span>
                                        @if ($entity->pivot->note)
                                            <span class="block text-sm text-stone-700">{{ $entity->pivot->note }}</span>
                                        @elseif ($entity->summary)
                                            <span class="block text-sm text-stone-600">{{ $entity->summary }}</span>
                                        @endif
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <nav class="flex justify-between gap-3 text-sm" aria-label="Scènes voisines">
                @if ($previous)
                    <a href="{{ route('scenes.show', [$campaign, $previous]) }}" class="text-codex hover:underline" wire:navigate>← {{ $previous->name }}</a>
                @else
                    <span></span>
                @endif
                @if ($next)
                    <a href="{{ route('scenes.show', [$campaign, $next]) }}" class="text-right text-codex hover:underline" wire:navigate>{{ $next->name }} →</a>
                @endif
            </nav>

            <div class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="mb-2 font-semibold">Supprimer</h2>
                <p class="mb-3 text-sm text-stone-600">Les fiches liées sont conservées.</p>
                <button type="button" wire:click="delete" wire:confirm="Supprimer la scène {{ $scene->name }} ?" class="text-sm font-medium text-red-700 hover:underline">Supprimer la scène</button>
            </div>
        </aside>
    </div>
</div>
