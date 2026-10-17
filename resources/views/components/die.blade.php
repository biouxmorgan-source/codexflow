@props(['sides' => 6, 'value' => null, 'state' => 'kept', 'size' => 'size-10', 'rolling' => false])
{{-- Un dé dessiné à sa forme (4, 6, 8, 10, 12, 20 faces ; un rond pour les autres), avec sa valeur.
     state : kept (compté), dropped (écarté), hit (réussite), miss (raté), sub (retranché). --}}
@php
    $shape = match ((int) $sides) {
        4 => ['polygon', '50,6 96,90 4,90', 66],
        6 => ['rect', null, 50],
        8 => ['polygon', '50,3 97,50 50,97 3,50', 50],
        10, 100 => ['polygon', '50,3 96,40 50,97 4,40', 46],
        12 => ['polygon', '50,4 96,37 78,94 22,94 4,37', 56],
        20 => ['polygon', '50,3 93,26 93,74 50,97 7,74 7,26', 50],
        default => ['circle', null, 50],
    };
    $fill = match ($state) {
        'dropped' => 'fill-stone-200 stroke-stone-300',
        'hit' => 'fill-wyn stroke-wyn',
        'miss' => 'fill-stone-300 stroke-stone-400',
        'sub' => 'fill-flow stroke-flow',
        default => 'fill-codex stroke-codex',
    };
    $text = in_array($state, ['dropped', 'miss'], true) ? 'fill-stone-600' : 'fill-on-accent';
    $digits = strlen((string) $value);
    $font = $digits >= 4 ? 22 : ($digits === 3 ? 28 : 36);
@endphp
<svg viewBox="0 0 100 100" {{ $attributes->class([$size, 'shrink-0 drop-shadow-sm', 'dice-tumble' => $rolling]) }} role="img" aria-label="{{ __('Dé à :sides faces : :value', ['sides' => $sides, 'value' => $value]) }}">
    <g @class([$fill, 'opacity-60' => $state === 'dropped']) stroke-width="4" stroke-linejoin="round">
        @if ($shape[0] === 'rect')
            <rect x="8" y="8" width="84" height="84" rx="16" />
        @elseif ($shape[0] === 'circle')
            <circle cx="50" cy="50" r="44" />
        @else
            <polygon points="{{ $shape[1] }}" />
        @endif
    </g>
    @if ((int) $sides === 20)
        <polygon points="50,22 78,68 22,68" class="fill-none stroke-white/25" stroke-width="2" />
    @endif
    <text x="50" y="{{ $shape[2] }}" text-anchor="middle" dominant-baseline="central" font-size="{{ $font }}" font-weight="700" @class([$text, 'tabular-nums'])
        @if ($rolling) x-data="dieFace({{ (int) $value }}, {{ (int) $sides }})" x-text="shown" @endif>{{ $value }}</text>
</svg>
