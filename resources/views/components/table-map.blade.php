@props(['map', 'tokens', 'imageUrl', 'tokenUrl', 'gm' => false])
{{--
    Carte dessinée en SVG, dans les pixels de l'image : la vue (zoom, déplacement) est le viewBox.
    Écran de table : seulement les jetons visibles (filtrés côté serveur). Côté MJ ($gm), les jetons
    masqués restent à leur place, en transparence.
--}}
@php
    [$vx, $vy, $vw, $vh] = $map->viewBox();
    $gs = $map->grid_size;
    $stroke = max(1, $gs / 30);
@endphp
<svg viewBox="{{ $vx }} {{ $vy }} {{ $vw }} {{ $vh }}" preserveAspectRatio="xMidYMid meet" {{ $attributes->merge(['class' => 'block h-full w-full select-none']) }}
    data-width="{{ $map->width }}" data-height="{{ $map->height }}" data-grid="{{ $gs }}" role="img" aria-label="{{ $map->name }}">
    <defs>
        <pattern id="map-grid-{{ $map->id }}" width="{{ $gs }}" height="{{ $gs }}" patternUnits="userSpaceOnUse"
            x="{{ $map->grid_offset_x }}" y="{{ $map->grid_offset_y }}">
            <path d="M {{ $gs }} 0 L 0 0 0 {{ $gs }}" fill="none" stroke="{{ $map->grid_color }}" stroke-width="{{ $stroke }}" stroke-opacity="0.55" />
        </pattern>
        <clipPath id="map-token-clip" clipPathUnits="objectBoundingBox"><circle cx="0.5" cy="0.5" r="0.5" /></clipPath>
    </defs>

    <image href="{{ $imageUrl }}" x="0" y="0" width="{{ $map->width }}" height="{{ $map->height }}" preserveAspectRatio="none" />

    @if ($map->grid_enabled)
        <rect x="0" y="0" width="{{ $map->width }}" height="{{ $map->height }}" fill="url(#map-grid-{{ $map->id }})" pointer-events="none" />
    @endif

    @foreach ($tokens as $token)
        @php($d = $map->tokenDiameter($token))
        <g wire:key="token-{{ $token->id }}" transform="translate({{ $token->x }} {{ $token->y }})" data-token="{{ $token->id }}"
            @if ($gm) class="cursor-grab" @endif
            @if ($token->hidden) opacity="0.45" @endif>
            <circle r="{{ $d / 2 }}" fill="{{ $token->color }}" stroke="#fff" stroke-width="{{ max(1.5, $d / 18) }}" @if ($token->hidden) stroke-dasharray="{{ $d / 8 }} {{ $d / 10 }}" @endif />
            @if ($token->hasPortrait())
                <image href="{{ $tokenUrl($token) }}" x="{{ -$d / 2 * 0.88 }}" y="{{ -$d / 2 * 0.88 }}" width="{{ $d * 0.88 }}" height="{{ $d * 0.88 }}" clip-path="url(#map-token-clip)" preserveAspectRatio="xMidYMid slice" pointer-events="none" />
            @else
                <text text-anchor="middle" dominant-baseline="central" fill="#fff" font-size="{{ $d * 0.38 }}" font-weight="600" font-family="system-ui, sans-serif" pointer-events="none">{{ $token->initials() }}</text>
            @endif
            @if ($token->show_label)
                <text y="{{ $d / 2 + max(8, $gs * 0.3) }}" text-anchor="middle" fill="#fff" stroke="#000" stroke-width="{{ max(2, $gs / 16) }}" paint-order="stroke" stroke-linejoin="round"
                    font-size="{{ max(12, $gs * 0.36) }}" font-weight="600" font-family="system-ui, sans-serif" pointer-events="none">{{ $token->label }}</text>
            @endif
        </g>
    @endforeach

    @if ($map->ruler)
        @php($r = $map->ruler)
        <g pointer-events="none">
            <line x1="{{ $r['x1'] }}" y1="{{ $r['y1'] }}" x2="{{ $r['x2'] }}" y2="{{ $r['y2'] }}" stroke="#facc15" stroke-width="{{ max(2, $gs / 12) }}" stroke-linecap="round" stroke-dasharray="{{ $gs / 4 }} {{ $gs / 6 }}" />
            <circle cx="{{ $r['x1'] }}" cy="{{ $r['y1'] }}" r="{{ max(3, $gs / 8) }}" fill="#facc15" />
            <circle cx="{{ $r['x2'] }}" cy="{{ $r['y2'] }}" r="{{ max(3, $gs / 8) }}" fill="#facc15" />
            <text x="{{ $r['x2'] }}" y="{{ $r['y2'] - max(10, $gs * 0.35) }}" text-anchor="middle" fill="#facc15" stroke="#000" stroke-width="{{ max(3, $gs / 10) }}" paint-order="stroke" stroke-linejoin="round"
                font-size="{{ max(14, $gs * 0.5) }}" font-weight="700" font-family="system-ui, sans-serif">{{ $map->rulerLabel() }}</text>
        </g>
    @endif
</svg>
