@props(['screen', 'name', 'arranging' => false])
{{--
    Un bloc déplaçable d'un écran. Hors mode « Organiser », l'enveloppe s'efface (display: contents) :
    l'écran reste exactement le même. En mode « Organiser », une barre donne la poignée, les flèches et « Masquer ».
--}}
@php($label = \App\Support\ScreenLayout::labels($screen)[$name] ?? $name)
<div wire:key="screen-block-{{ $name }}" data-block="{{ $name }}" @class([
    'contents' => ! $arranging,
    'rounded-xl border-2 border-dashed border-codex/40 bg-white/60 p-2' => $arranging,
])>
    @if ($arranging)
        <div class="mb-2 flex items-center gap-1.5 text-sm">
            <button type="button" x-on:pointerdown="start($event)" class="cursor-grab touch-none rounded-md px-1.5 text-lg leading-none text-stone-500 hover:bg-codex-soft hover:text-codex active:cursor-grabbing" aria-label="{{ __('Déplacer « :name »', ['name' => $label]) }}" title="{{ __('Glisser pour déplacer') }}">⠿</button>
            <span class="mr-auto font-medium text-stone-700">{{ $label }}</span>
            <button type="button" x-on:click="move($el, -1)" class="rounded-md px-1.5 text-stone-500 hover:bg-codex-soft hover:text-codex" aria-label="{{ __('Monter « :name »', ['name' => $label]) }}">↑</button>
            <button type="button" x-on:click="move($el, 1)" class="rounded-md px-1.5 text-stone-500 hover:bg-codex-soft hover:text-codex" aria-label="{{ __('Descendre « :name »', ['name' => $label]) }}">↓</button>
            <button type="button" wire:click="hideBlock('{{ $name }}')" class="rounded-md px-1.5 text-xs text-stone-500 hover:text-red-700">{{ __('Masquer') }}</button>
        </div>
        @unless ($slot->hasActualContent())
            <p class="px-1 pb-1 text-xs text-stone-500">{{ __('Vide pour l’instant : ce bloc s’affiche quand il a du contenu.') }}</p>
        @endunless
    @endif
    {{ $slot }}
</div>
