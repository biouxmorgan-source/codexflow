{{-- Lecteur sonore de cet appareil : caché tant que rien ne joue. Voir resources/js/audio-deck.js. --}}
<div x-data="audioDeck" x-on:loremundi-audio-play.window="play($event.detail)" x-on:loremundi-audio-stop.window="stop()" x-show="title" x-cloak
    class="fixed inset-x-2 bottom-16 z-40 rounded-xl border border-stone-200 bg-white p-2 shadow-lg sm:inset-x-auto sm:bottom-4 sm:left-4 sm:w-80"
    role="region" aria-label="{{ __('Lecteur sonore') }}">
    <div class="flex items-center gap-2">
        <button type="button" x-on:click="toggle()" class="flex size-9 shrink-0 items-center justify-center rounded-full bg-codex text-on-accent hover:opacity-90"
            x-bind:aria-label="playing ? @js(__('Pause')) : @js(__('Lecture'))">
            <span x-text="playing ? '❚❚' : '▶'" aria-hidden="true"></span>
        </button>
        <span class="min-w-0 flex-1">
            <span class="block truncate text-sm font-medium" x-text="title"></span>
            <span class="block text-xs text-amber-700" x-show="blocked">{{ __('Le navigateur bloque le son : relancez la lecture.') }}</span>
        </span>
        <button type="button" x-on:click="toggleLoop()" class="rounded-md px-1.5 py-1 text-sm" x-bind:class="loop ? 'bg-codex-soft text-codex' : 'text-stone-400'"
            x-bind:aria-pressed="loop" title="{{ __('En boucle') }}"><span aria-hidden="true">⟳</span><span class="sr-only">{{ __('En boucle') }}</span></button>
        <button type="button" x-on:click="stop()" class="rounded-md px-1.5 py-1 text-sm text-stone-500 hover:text-red-700" title="{{ __('Arrêter') }}"><span aria-hidden="true">■</span><span class="sr-only">{{ __('Arrêter') }}</span></button>
    </div>
    <label class="mt-1 flex items-center gap-2 text-xs text-stone-500">
        <span>{{ __('Volume') }}</span>
        <input type="range" min="0" max="100" x-model.number="volume" x-on:input="setVolume()" class="min-w-0 flex-1 accent-current">
    </label>
</div>
