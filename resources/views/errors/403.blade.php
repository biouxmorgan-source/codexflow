@php($reason = isset($exception) ? $exception->getMessage() : null)
@include('errors.layout', ['code' => 403] + match ($reason) {
    \App\Support\Plans\Plans::NOT_INCLUDED => ['title' => 'Fonction non comprise dans la formule', 'message' => \App\Support\Plans\Plans::NOT_INCLUDED],
    \App\Support\CampaignFeatures::DISABLED => ['title' => 'Fonction désactivée', 'message' => 'Le MJ a désactivé cette fonction dans cette campagne. Il peut la réactiver depuis la page de la campagne.'],
    default => ['title' => 'Accès refusé', 'message' => 'Cette page est réservée à d’autres membres de la campagne. Si vous pensez devoir y accéder, demandez à votre MJ.'],
})
