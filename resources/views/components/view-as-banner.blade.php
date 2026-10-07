@props(['name', 'exit'])
{{-- Bandeau du mode « Voir comme… » : le MJ voit ce que voit un personnage, en lecture seule. --}}
<div role="status" data-view-as class="sticky top-0 z-30 mb-6 flex flex-wrap items-center justify-between gap-3 rounded-xl bg-flow px-4 py-3 text-on-accent shadow-md ring-4 ring-flow/30">
    <p class="font-semibold">{{ __('Vous voyez la campagne comme :name voit sa fiche. Lecture seule.', ['name' => $name]) }}</p>
    <a href="{{ $exit }}" class="shrink-0 rounded-md border-2 border-on-accent px-3 py-1 text-sm font-semibold hover:bg-on-accent hover:text-flow" wire:navigate>{{ __('Quitter') }}</a>
</div>
