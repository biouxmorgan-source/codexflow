@props(['screen', 'place', 'arranging' => false])
{{--
    Un bloc déplaçable d'un écran, à sa colonne de départ et sur sa largeur (App\Support\ScreenLayout).
    Hors mode « Organiser », un bloc vide n'est pas rendu. En mode « Organiser », une barre donne la poignée,
    les flèches, la largeur et « Masquer ».
--}}
@php($label = \App\Support\ScreenLayout::labels($screen)[$place['id']] ?? $place['id'])
@if ($arranging || $slot->hasActualContent())
    <div wire:key="screen-block-{{ $place['id'] }}" data-block="{{ $place['id'] }}" data-column="{{ $place['column'] }}" data-span="{{ $place['span'] }}" @class([
        'rounded-xl border-2 border-dashed border-codex/40 bg-white/60 p-2' => $arranging,
    ])>
        @if ($arranging)
            <x-screen.block-bar :label="$label" :span="$place['span']" :columns="\App\Support\ScreenLayout::COLUMNS" :hide="$place['id']" />
            @unless ($slot->hasActualContent())
                <p class="px-1 pb-1 text-xs text-stone-500">{{ __('Vide pour l’instant : ce bloc s’affiche quand il a du contenu.') }}</p>
            @endunless
        @endif
        {{ $slot }}
    </div>
@endif
