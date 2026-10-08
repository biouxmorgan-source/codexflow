@props(['href', 'icon', 'label', 'badge' => null, 'feature' => null, 'campaign' => null])
{{--
    Outil secondaire d'une campagne : une icône, le nom en infobulle au survol et au focus.
    L'infobulle est hors du flux : les boutons ne bougent jamais, on vise toujours le même endroit.
    Sur petit écran, où il n'y a pas de survol, le nom est écrit à côté de l'icône.
    Avec $feature : étoile ✦ si la fonction est Premium, et grisé sans lien si la formule du
    propriétaire de la campagne ne la comprend pas (ses données restent, rien n'est effacé).
    Une fonction que le MJ a coupée dans cette campagne n'apparaît pas du tout.
--}}
@if ($feature === null || $campaign === null || \App\Support\CampaignFeatures::enabled($campaign, $feature))
@php($locked = $feature !== null && ! \App\Support\Plans\Plans::allows($campaign?->owner ?? auth()->user(), $feature))
@php($tooltip = $locked ? __(':label : fonction Premium, non comprise ici', ['label' => $label]) : $label)
@if ($locked)
    <span tabindex="0" aria-disabled="true" aria-label="{{ $tooltip }}"
        {{ $attributes->merge(['class' => 'group relative flex cursor-not-allowed items-center gap-2 rounded-lg border border-dashed border-stone-300 bg-stone-50 px-3 py-2 text-sm text-stone-400']) }}>
@else
    <a href="{{ $href }}" wire:navigate
        {{ $attributes->merge(['class' => 'group relative flex items-center gap-2 rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-700 shadow-sm transition hover:border-codex hover:text-codex focus-visible:border-codex']) }}>
@endif
    <x-icon :name="$icon" class="size-5 shrink-0" />
    <span class="sm:hidden">{{ $label }}</span>
    @if ($feature)
        <x-premium :feature="$feature" class="absolute -top-2 -right-1.5 text-sm leading-none drop-shadow-sm" />
    @endif
    @if ($badge)
        <span class="rounded-full bg-flow px-2 py-0.5 text-xs font-semibold text-on-accent">{{ $badge }}</span>
    @endif
    <span role="tooltip" class="pointer-events-none absolute left-1/2 top-full z-20 mt-1 hidden -translate-x-1/2 whitespace-nowrap rounded-md bg-stone-900 px-2 py-1 text-xs font-medium text-white opacity-0 transition-opacity group-hover:opacity-100 group-focus-visible:opacity-100 group-focus:opacity-100 sm:block">{{ $tooltip }}</span>
@if ($locked)
    </span>
@else
    </a>
@endif
@endif
