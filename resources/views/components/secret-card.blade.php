@props(['secret', 'campaign', 'characters', 'compact' => false])
{{-- Un secret, ce à quoi il est relié, et qui le connaît : un clic sur un personnage le lui révèle ou le lui fait oublier. --}}
@php($knownBy = $secret->grants->pluck('player_character_id')->all())
<div {{ $attributes->merge(['class' => 'rounded-lg border border-flow/30 bg-white p-3']) }} wire:key="secret-{{ $secret->id }}">
    <div class="flex items-start gap-2">
        <span class="mt-0.5 text-flow" aria-hidden="true">🔒</span>
        <div class="min-w-0 flex-1">
            @php($state = $secret->state($characters->modelKeys()))
            <p class="font-medium">
                {{ $secret->title }}
                <span @class(['ml-1 rounded-full px-2 py-0.5 align-middle text-xs font-medium', 'bg-amber-100 text-amber-900' => $secret->kind === 'rumour', 'bg-codex-soft text-codex' => $secret->kind === 'clue', 'bg-stone-100 text-stone-700' => $secret->kind === 'truth'])>{{ $secret->kindLabel() }}</span>
                <span @class(['ml-1 text-xs font-normal', 'text-stone-500' => $state === 'hidden', 'text-flow' => $state === 'partial', 'text-green-700' => $state === 'revealed'])>· {{ \App\Models\Secret::states()[$state] }}</span>
            </p>
            @if ($secret->body && ! $compact)
                <div class="mt-1 text-sm text-stone-700">{{ \App\Support\EntityLinks::render($secret->body, $campaign) }}</div>
            @endif
            @unless ($compact)
                @php($links = $secret->entities->map(fn ($e) => ['label' => $e->name, 'url' => route('entities.show', [$campaign, $e])])
                    ->concat($secret->scenes->map(fn ($s) => ['label' => $s->name, 'url' => route('scenes.show', [$campaign, $s])]))
                    ->concat($secret->documents->map(fn ($d) => ['label' => $d->title, 'url' => route('documents.show', [$campaign, $d])])))
                @if ($links->isNotEmpty())
                    <p class="mt-1 text-xs text-stone-500">
                        {{ __('Relié à :') }}
                        @foreach ($links as $link)
                            <a href="{{ $link['url'] }}" class="link" wire:navigate>{{ $link['label'] }}</a>@if (! $loop->last), @endif
                        @endforeach
                    </p>
                @endif
            @endunless
        </div>
        {{ $actions ?? '' }}
    </div>
    @if ($characters->isNotEmpty())
        <div class="mt-2 flex flex-wrap items-center gap-1 text-xs">
            <span class="mr-1 text-stone-500">{{ __('Le savent :') }}</span>
            @foreach ($characters as $character)
                @if (in_array($character->id, $knownBy, true))
                    <button type="button" wire:click="forgetSecret({{ $secret->id }}, {{ $character->id }})" wire:confirm="{{ __('Annuler la révélation à :name ? Le secret quitte ses connaissances.', ['name' => $character->entity->name]) }}"
                        class="rounded-full bg-flow px-2 py-0.5 font-medium text-on-accent hover:opacity-80" title="{{ __('Annuler la révélation') }}">✓ {{ $character->entity->name }}</button>
                @else
                    <button type="button" wire:click="revealSecret({{ $secret->id }}, {{ $character->id }})"
                        class="rounded-full border border-stone-300 px-2 py-0.5 text-stone-600 hover:border-flow hover:text-flow" title="{{ __('Révéler à :name', ['name' => $character->entity->name]) }}">{{ $character->entity->name }}</button>
                @endif
            @endforeach
            @if (count(array_intersect($characters->modelKeys(), $knownBy)) < $characters->count())
                <button type="button" wire:click="revealSecret({{ $secret->id }})" class="ml-1 link">{{ __('Toute la table') }}</button>
            @endif
        </div>
    @endif
</div>
