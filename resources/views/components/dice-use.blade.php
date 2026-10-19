@props(['name', 'value', 'field' => null, 'character' => null, 'own' => false])
{{-- Petit dé à côté d'une valeur de feuille : lance les dés avec cette valeur comme seuil ou comme bonus.
     Le plateau de dés (ou celui de la feuille personnelle) écoute l'événement « dice-use ». --}}
@php($detail = ['field' => $field, 'name' => $name, 'value' => $value, 'character' => $character, 'own' => (bool) $own])
<div {{ $attributes->class('relative inline-flex shrink-0') }} x-data="{ menu: false, detail: @js($detail), use(as) { this.menu = false; this.$dispatch('dice-use', Object.assign({}, this.detail, { as })) } }" x-on:keydown.escape="menu = false" x-on:click.outside="menu = false">
    <button type="button" x-on:click="menu = ! menu" x-bind:aria-expanded="menu" class="rounded-md p-1 text-codex hover:bg-stone-100" aria-label="{{ __('Lancer les dés avec :name', ['name' => $name]) }}" title="{{ __('Lancer les dés avec :name', ['name' => $name]) }}">
        <x-die sides="20" value="" size="size-6" aria-hidden="true" />
    </button>
    <div x-show="menu" x-cloak x-transition.opacity class="absolute right-0 top-full z-40 mt-1 w-56 rounded-lg border border-stone-200 bg-white p-1 text-left text-sm shadow-lg">
        <button type="button" x-on:click="use('threshold')" class="block w-full rounded-md px-3 py-2 text-left hover:bg-stone-100">{{ __('Lancer sous :value (seuil)', ['value' => $value]) }}</button>
        <button type="button" x-on:click="use('bonus')" class="block w-full rounded-md px-3 py-2 text-left hover:bg-stone-100">{{ __('Ajouter :value au jet (bonus)', ['value' => $value]) }}</button>
    </div>
</div>
