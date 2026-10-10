<div>
    <nav class="mb-2 text-sm text-stone-500">
        <a href="{{ route('campaigns.index') }}" class="crumb" wire:navigate>{{ __('Mes campagnes') }}</a>
        › <a href="{{ route('campaigns.show', $campaign) }}" class="crumb" wire:navigate>{{ $campaign->name }}</a>
        › <a href="{{ route('scenarios.index', $campaign) }}" class="crumb" wire:navigate>{{ $scene->scenario->name }}</a>
    </nav>

    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            @if ($scene->chapter)
                <p class="text-xs font-semibold tracking-wide text-stone-500 uppercase">{{ $scene->chapter }}</p>
            @endif
            <h1 class="text-2xl font-semibold">{{ $scene->name }}</h1>
            <div class="mt-2">
                <label for="status" class="sr-only">{{ __('Statut') }}</label>
                <select id="status" wire:change="setStatus($event.target.value)" class="rounded-full border-0 py-1 pr-8 pl-3 text-sm font-medium {{ $scene->status->badge() }}">
                    @foreach (\App\Enums\SceneStatus::cases() as $status)
                        <option value="{{ $status->value }}" @selected($scene->status === $status)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            @if ($scene->tags->isNotEmpty())
                <p class="mt-2 flex flex-wrap gap-1 text-xs">
                    @foreach ($scene->tags as $sceneTag)
                        <x-tag :tag="$sceneTag" :href="route('scenarios.index', [$campaign, 'tag' => $sceneTag->id])" />
                    @endforeach
                </p>
            @endif
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('journal.index', [$campaign, 'sujet' => 'scene:'.$scene->id]) }}" class="btn-secondary" wire:navigate>{{ __('Historique') }}</a>
            <a href="{{ route('scenes.edit', [$campaign, $scene]) }}" class="btn-secondary" wire:navigate>{{ __('Modifier la scène') }}</a>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="rounded-xl border border-flow/30 bg-white p-6 shadow-sm lg:col-span-2">
            <h2 class="mb-3 font-semibold text-flow">{{ __('Préparation') }}</h2>
            @if ($scene->description)
                <div class="text-stone-700">{{ $description }}</div>
            @else
                <p class="text-sm text-stone-500">{{ __("Rien de préparé pour l'instant.") }}</p>
            @endif
        </section>

        <aside class="space-y-6">
            <livewire:secrets.panel :campaign="$campaign" :items="['scene' => [$scene->id]]" :link="'scene:'.$scene->id" wire:key="secrets-scene" />
            <section class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="mb-3 font-semibold">{{ __('Dans cette scène') }}</h2>
                @if ($entities->isEmpty())
                    <p class="text-sm text-stone-500">{{ __('Aucune fiche liée.') }}</p>
                @else
                    <ul class="space-y-3">
                        @foreach ($entities as $entity)
                            @php($state = $entity->campaignStates->first())
                            <li wire:key="entity-{{ $entity->id }}">
                                <a href="{{ route('entities.show', [$campaign, $entity]) }}" class="flex items-start gap-3 rounded-lg p-1 hover:bg-codex-soft" wire:navigate>
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

            @if ($rules->isNotEmpty() || $documents->isNotEmpty() || $tracks->isNotEmpty())
                <section class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                    @if ($rules->isNotEmpty())
                        <h2 class="mb-2 font-semibold">{{ __('Règles') }}</h2>
                        <ul class="mb-4 space-y-1 text-sm">
                            @foreach ($rules as $rule)
                                <li wire:key="rule-{{ $rule->id }}"><a href="{{ route('rules.show', [$campaign, $rule]) }}" class="link" wire:navigate>{{ $rule->title }}</a></li>
                            @endforeach
                        </ul>
                    @endif
                    @if ($documents->isNotEmpty())
                        <h2 class="mb-2 font-semibold">{{ __('Documents') }}</h2>
                        <x-document-list :documents="$documents" :campaign="$campaign" />
                    @endif
                    @if ($tracks->isNotEmpty())
                        <h2 @class(['mb-2 font-semibold', 'mt-4' => $rules->isNotEmpty() || $documents->isNotEmpty()])>{{ __('Sons') }}</h2>
                        <ul class="space-y-1 text-sm">
                            @foreach ($tracks as $track)
                                <li wire:key="scene-track-{{ $track->id }}" class="flex items-center gap-2">
                                    <span class="min-w-0 flex-1 truncate">{{ $track->title }}</span>
                                    <button type="button" class="rounded-md border border-stone-200 px-1.5 py-0.5 text-xs text-stone-600 hover:border-codex hover:text-codex"
                                        x-on:click="$dispatch('loremundi-audio-play', @js(['url' => route('audio.file', $track), 'title' => $track->title, 'loop' => $track->loop]))"
                                        aria-label="{{ __('Écouter « :name » sur cet appareil', ['name' => $track->title]) }}">▶ {{ __('Écouter') }}</button>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            @endif

            <section class="rounded-xl border border-codex/30 bg-white p-6 shadow-sm">
                <h2 class="mb-1 font-semibold text-codex">{{ __('À jouer') }}</h2>
                <p class="mb-3 text-sm text-stone-600">{{ __('Ce que vous voulez placer pendant la scène. La liste vous attendra en mode Session.') }}</p>
                <ul class="space-y-1 text-sm">
                    @foreach ($toPlay as $item)
                        <li wire:key="toplay-{{ $item->id }}" class="flex items-start gap-2">
                            <span @class(['min-w-0 flex-1', 'text-stone-400 line-through' => $item->done_at])>{{ $item->body }}</span>
                            <button type="button" wire:click="deleteToPlay({{ $item->id }})" class="shrink-0 text-xs text-stone-400 hover:text-red-700" aria-label="{{ __('Supprimer « :name »', ['name' => $item->body]) }}">✕</button>
                        </li>
                    @endforeach
                </ul>
                <form wire:submit="addToPlay" class="mt-3 flex gap-2">
                    <label for="toPlayBody" class="sr-only">{{ __('Nouvel élément à jouer') }}</label>
                    <input id="toPlayBody" type="text" wire:model="toPlayBody" class="field py-1.5 text-sm" placeholder="{{ __('Mira glisse une lettre…') }}" autocomplete="off">
                    <button type="submit" class="btn-secondary min-h-0 py-1 text-sm">{{ __('Ajouter') }}</button>
                </form>
                @error('toPlayBody') <p class="error">{{ $message }}</p> @enderror
            </section>

            <nav class="flex justify-between gap-3 text-sm" aria-label="{{ __('Scènes voisines') }}">
                @if ($previous)
                    <a href="{{ route('scenes.show', [$campaign, $previous]) }}" class="link" wire:navigate>← {{ $previous->name }}</a>
                @else
                    <span></span>
                @endif
                @if ($next)
                    <a href="{{ route('scenes.show', [$campaign, $next]) }}" class="text-right link" wire:navigate>{{ $next->name }} →</a>
                @endif
            </nav>

            <div class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="mb-2 font-semibold">{{ __('Supprimer') }}</h2>
                <p class="mb-3 text-sm text-stone-600">{{ __('Les fiches liées sont conservées.') }}</p>
                <button type="button" wire:click="delete" wire:confirm="{{ __('Supprimer la scène :name ?', ['name' => $scene->name]) }}" class="text-sm font-medium text-red-700 hover:underline">{{ __('Supprimer la scène') }}</button>
            </div>
        </aside>
    </div>
</div>
