@props(['id', 'model', 'rows' => 6])
{{-- Zone de texte avec autocomplétion des liens [[…]] vers les fiches de la campagne. --}}
<div x-data="entityLinkInput" class="relative" @click.outside="close()">
    <textarea
        id="{{ $id }}"
        x-ref="input"
        wire:model="{{ $model }}"
        rows="{{ $rows }}"
        x-on:input="onInput()"
        x-on:keydown="onKeydown($event)"
        role="combobox"
        aria-autocomplete="list"
        :aria-expanded="open"
        aria-controls="{{ $id }}-suggestions"
        {{ $attributes->merge(['class' => 'field']) }}
    ></textarea>
    <p class="mt-1 text-xs text-stone-500">Tapez <kbd class="rounded border border-stone-300 bg-stone-50 px-1">[[</kbd> pour lier une autre fiche.</p>

    <ul
        id="{{ $id }}-suggestions"
        x-show="open"
        x-cloak
        role="listbox"
        class="absolute inset-x-0 z-20 mt-1 max-h-64 overflow-auto rounded-md border border-stone-200 bg-white py-1 shadow-lg"
    >
        <template x-for="(item, index) in items" :key="item.id">
            <li
                role="option"
                :aria-selected="index === active"
                x-on:mousedown.prevent="choose(item)"
                x-on:mouseenter="active = index"
                :class="index === active ? 'bg-codex-soft' : ''"
                class="flex cursor-pointer items-center justify-between gap-3 px-3 py-2"
            >
                <span x-text="item.name" class="font-medium"></span>
                <span x-text="item.type" class="text-xs text-stone-500"></span>
            </li>
        </template>
    </ul>
</div>
