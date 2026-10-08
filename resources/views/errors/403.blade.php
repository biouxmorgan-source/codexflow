@include('errors.layout', ['code' => 403] + (isset($exception) && $exception->getMessage() === \App\Support\Plans\Plans::NOT_INCLUDED
    ? ['title' => 'Fonction non comprise dans la formule', 'message' => \App\Support\Plans\Plans::NOT_INCLUDED]
    : ['title' => 'Accès refusé', 'message' => 'Cette page est réservée à d’autres membres de la campagne. Si vous pensez devoir y accéder, demandez à votre MJ.']))
