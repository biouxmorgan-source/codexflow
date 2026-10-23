@props(['label', 'span' => 1, 'columns' => 3, 'hide' => null])
{{--
    Barre d'un bloc en mode « Organiser » (resources/js/screen-arranger.js) : la poignée, les flèches,
    la largeur en colonnes (sur grand écran) et, si l'écran le permet, « Masquer ».
--}}
<div class="mb-2 flex flex-wrap items-center gap-1.5 text-sm">
    <button type="button" x-on:pointerdown="start($event)" class="cursor-grab touch-none rounded-md px-1.5 text-lg leading-none text-stone-500 hover:bg-codex-soft hover:text-codex active:cursor-grabbing" aria-label="{{ __('Déplacer « :name »', ['name' => $label]) }}" title="{{ __('Glisser pour déplacer') }}">⠿</button>
    <span class="mr-auto font-medium text-stone-700">{{ $label }}</span>
    @if ($columns > 1)
        <span class="hidden items-center gap-1.5 lg:inline-flex">
            <button type="button" x-on:click="shift($el, -1)" class="rounded-md px-1.5 text-stone-500 hover:bg-codex-soft hover:text-codex" aria-label="{{ __('Colonne de gauche pour « :name »', ['name' => $label]) }}">←</button>
            <button type="button" x-on:click="shift($el, 1)" class="rounded-md px-1.5 text-stone-500 hover:bg-codex-soft hover:text-codex" aria-label="{{ __('Colonne de droite pour « :name »', ['name' => $label]) }}">→</button>
            <select x-on:change="resize($el, $event.target.value)" class="rounded-md border-stone-200 py-0.5 pr-6 pl-1.5 text-xs" aria-label="{{ __('Largeur de « :name »', ['name' => $label]) }}">
                @foreach (range(1, $columns) as $width)
                    <option value="{{ $width }}" @selected($span == $width)>{{ trans_choice(':count colonne|:count colonnes', $width, ['count' => $width]) }}</option>
                @endforeach
            </select>
        </span>
    @endif
    <button type="button" x-on:click="move($el, -1)" class="rounded-md px-1.5 text-stone-500 hover:bg-codex-soft hover:text-codex" aria-label="{{ __('Monter « :name »', ['name' => $label]) }}">↑</button>
    <button type="button" x-on:click="move($el, 1)" class="rounded-md px-1.5 text-stone-500 hover:bg-codex-soft hover:text-codex" aria-label="{{ __('Descendre « :name »', ['name' => $label]) }}">↓</button>
    @if ($hide)
        <button type="button" wire:click="hideBlock('{{ $hide }}')" class="rounded-md px-1.5 text-xs text-stone-500 hover:text-red-700">{{ __('Masquer') }}</button>
    @endif
</div>
