@props(['tag', 'href' => null, 'compact' => false])
{{-- Pastille d'un tag, avec sa couleur s'il en a une. --}}
@php($classes = 'inline-flex items-center gap-1 rounded-full bg-stone-100 text-stone-700'.($compact ? ' px-1.5' : ' px-2 py-0.5').($href ? ' hover:bg-codex-soft' : ''))
@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }} wire:navigate>
@else
    <span {{ $attributes->merge(['class' => $classes]) }}>
@endif
    @if ($tag->hex())
        <span class="h-2 w-2 shrink-0 rounded-full" style="background: {{ $tag->hex() }}" aria-hidden="true"></span>
    @endif
    {{ $tag->name }}
@if ($href)
    </a>
@else
    </span>
@endif
