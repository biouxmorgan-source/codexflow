@props(['href', 'icon', 'label', 'badge' => null])
{{--
    Outil secondaire d'une campagne : une icône, le nom en infobulle au survol et au focus.
    L'infobulle est hors du flux : les boutons ne bougent jamais, on vise toujours le même endroit.
    Sur petit écran, où il n'y a pas de survol, le nom est écrit à côté de l'icône.
--}}
<a href="{{ $href }}" wire:navigate
    {{ $attributes->merge(['class' => 'group relative flex items-center gap-2 rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-700 shadow-sm transition hover:border-codex hover:text-codex focus-visible:border-codex']) }}>
    <x-icon :name="$icon" class="size-5 shrink-0" />
    <span class="sm:hidden">{{ $label }}</span>
    @if ($badge)
        <span class="rounded-full bg-flow px-2 py-0.5 text-xs font-semibold text-on-accent">{{ $badge }}</span>
    @endif
    <span role="tooltip" class="pointer-events-none absolute left-1/2 top-full z-20 mt-1 hidden -translate-x-1/2 whitespace-nowrap rounded-md bg-stone-900 px-2 py-1 text-xs font-medium text-white opacity-0 transition-opacity group-hover:opacity-100 group-focus-visible:opacity-100 sm:block">{{ $label }}</span>
</a>
