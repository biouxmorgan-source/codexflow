@props(['variant' => 'horizontal'])
{{--
    Logo d'Autistic Intelligence, l'éditeur. Le dessin sert de masque : il prend la couleur du texte
    (classe text-…), donc suit le thème clair ou sombre et reste net à toute taille.
    horizontal : pictogramme et nom côte à côte ; complet : pictogramme au-dessus du nom.
--}}
@php
    [$file, $ratio] = $variant === 'complet'
        ? ['autistic-intelligence.svg', '1105 / 1246']
        : ['autistic-intelligence-horizontal.svg', '2928 / 936'];
@endphp
<span role="img" aria-label="Autistic Intelligence" translate="no"
    {{ $attributes->class('publisher-logo') }}
    style="--logo: url('{{ asset('images/'.$file) }}'); aspect-ratio: {{ $ratio }}"></span>
