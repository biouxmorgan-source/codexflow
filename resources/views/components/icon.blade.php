@props(['name'])
{{-- Petites icônes au trait, dessinées ici pour éviter une bibliothèque entière. --}}
@php
    $paths = [
        'secret' => ['M12 3l7 4v5c0 4-3 7-7 8-4-1-7-4-7-8V7z', 'M12 10v4'],
        'map' => ['M9 4l6 3 5-3v13l-5 3-6-3-5 3V7z', 'M9 4v13', 'M15 7v13'],
        'graph' => ['M7.6 7.6l2.9 7.4', 'M16.4 7.6L13.5 15', 'M8 6h8'],
        'timeline' => ['M5 4v16', 'M5 8h7', 'M5 14h11', 'M5 19h5'],
        'members' => ['M3 20c0-3 3-5 6-5s6 2 6 5', 'M16 6a3 3 0 010 6', 'M18 20c0-2-1-3.5-2.5-4.5'],
        'messages' => ['M4 5h16v10H9l-5 4z'],
        'journal' => ['M6 4h10l3 3v13H6z', 'M9 10h7', 'M9 14h7'],
        'fields' => ['M4 6h16', 'M4 12h10', 'M4 18h13', 'M18 10v4'],
        'import' => ['M12 4v10', 'M8 10l4 4 4-4', 'M5 18h14'],
        'remote' => ['M9 3h6v18H9z'],
        'screen' => ['M3 5h18v11H3z', 'M8 20h8', 'M12 16v4'],
        'archive' => ['M4 7h16v13H4z', 'M3 4h18v3H3z', 'M10 11h4'],
    ][$name] ?? [];

    // Ronds d'une icône : un élément <circle> reste net là où un arc dessiné à la main se déforme.
    $circles = [
        'graph' => [[6, 6, 2], [18, 6, 2], [12, 18, 2]],
        'members' => [[9, 8, 3]],
        'remote' => [[12, 7, 0.6], [12, 11, 0.6], [12, 15, 0.6]],
    ][$name] ?? [];
@endphp
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" {{ $attributes->merge(['class' => 'size-5']) }}>
    @foreach ($paths as $path)
        <path d="{{ $path }}" />
    @endforeach
    @foreach ($circles as [$cx, $cy, $r])
        <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r }}" />
    @endforeach
</svg>
