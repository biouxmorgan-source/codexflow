@props(['href', 'icon', 'label', 'badge' => null])
{{--
    Outil secondaire d'une campagne : une icône, le nom en toutes lettres au survol et au focus.
    Le nom reste dans le DOM pour les lecteurs d'écran et reste visible sur petit écran.
--}}
<a href="{{ $href }}" wire:navigate
    {{ $attributes->merge(['class' => 'group relative flex items-center gap-2 rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-700 shadow-sm transition hover:border-codex hover:text-codex focus-visible:border-codex']) }}>
    <x-icon :name="$icon" class="size-5 shrink-0" />
    <span class="sm:sr-only sm:group-hover:not-sr-only sm:group-focus-visible:not-sr-only">{{ $label }}</span>
    @if ($badge)
        <span class="rounded-full bg-flow px-2 py-0.5 text-xs font-semibold text-on-accent">{{ $badge }}</span>
    @endif
</a>
