<p {{ $attributes->merge(['class' => 'text-xs text-stone-500']) }}>
    {{ __('Vous restez responsable des documents que vous importez et des droits nécessaires pour les utiliser.') }}
    <a href="{{ route('privacy') }}#importation" class="link">{{ __('Importation et structuration de contenus') }}</a>
</p>
