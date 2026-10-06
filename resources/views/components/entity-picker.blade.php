@props(['id', 'model'])
{{-- Choix d'une fiche de la campagne par son nom, avec suggestions. --}}
<div x-data="entityPicker(@js($model))" class="relative" @click.outside="close()">
    <input
        id="{{ $id }}"
        type="text"
        x-ref="input"
        x-model="query"
        x-on:input.debounce.150ms="search()"
        x-on:focus="search()"
        x-on:keydown="onKeydown($event)"
        role="combobox"
        autocomplete="off"
        aria-autocomplete="list"
        :aria-expanded="open"
        aria-controls="{{ $id }}-suggestions"
        placeholder="Tapez un nom…"
        class="field"
    >
    <ul id="{{ $id }}-suggestions" x-show="open" x-cloak role="listbox"
        class="absolute inset-x-0 z-20 mt-1 max-h-64 overflow-auto rounded-md border border-stone-200 bg-white py-1 shadow-lg">
        <template x-for="(item, index) in items" :key="item.id">
            <li role="option" :aria-selected="index === active"
                x-on:mousedown.prevent="choose(item)" x-on:mouseenter="active = index"
                :class="index === active ? 'bg-codex-soft' : ''"
                class="flex cursor-pointer items-center justify-between gap-3 px-3 py-2">
                <span x-text="item.name" class="font-medium"></span>
                <span x-text="item.type" class="text-xs text-stone-500"></span>
            </li>
        </template>
    </ul>
</div>
