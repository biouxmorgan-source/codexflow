@props(['value'])
{{-- Note en étoiles : la moyenne exacte est lue par le lecteur d'écran, l'affichage arrondit. --}}
@php($filled = (int) round($value))
<span {{ $attributes->merge(['class' => 'inline-flex tracking-tight text-amber-500']) }} role="img" aria-label="{{ __(':value sur 5', ['value' => \Illuminate\Support\Number::format($value, 1, locale: app()->getLocale())]) }}">
    <span aria-hidden="true">{{ str_repeat('★', $filled) }}<span class="text-stone-300">{{ str_repeat('★', 5 - $filled) }}</span></span>
</span>
