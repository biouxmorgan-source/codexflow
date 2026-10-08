@props(['feature' => null])
{{--
    Petite étoile des fonctions Premium. Avec $feature, elle n'apparaît que si la fonction
    n'est pas dans l'offre gratuite (réglée dans la console d'administration).
--}}
@if ($feature === null || \App\Support\Plans\Plans::isPremium($feature))
    <span {{ $attributes->merge(['class' => 'inline-block text-amber-500']) }} title="{{ __('Fonction Premium') }}"><span aria-hidden="true">✦</span><span class="sr-only">{{ __('Fonction Premium') }}</span></span>
@endif
