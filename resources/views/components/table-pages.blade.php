@props(['page', 'pages'])
{{-- Pages du PDF affiché à la table (télécommande, page du document) : appelle turn(-1|1) du composant. --}}
<div {{ $attributes->merge(['class' => 'flex items-center gap-2']) }} role="group" aria-label="{{ __('Pages du PDF à la table') }}">
    <button type="button" wire:click="turn(-1)" @disabled($page <= 1) class="btn-secondary justify-center px-4 py-2 disabled:opacity-40" aria-label="{{ __('Page précédente') }}">‹</button>
    <span class="min-w-24 text-center text-sm tabular-nums">{{ $pages > 0 ? __('Page :page / :pages', ['page' => $page, 'pages' => $pages]) : __('Page :page', ['page' => $page]) }}</span>
    <button type="button" wire:click="turn(1)" @disabled($pages > 0 && $page >= $pages) class="btn-secondary justify-center px-4 py-2 disabled:opacity-40" aria-label="{{ __('Page suivante') }}">›</button>
</div>
