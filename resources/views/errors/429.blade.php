@php($wait = (int) (($exception ?? null)?->getHeaders()['Retry-After'] ?? 0))
@include('errors.layout', [
    'code' => 429,
    'title' => 'Trop de demandes',
    'message' => 'Trop de demandes à la suite. Patientez un instant puis réessayez.',
    'detail' => $wait > 0 ? trans_choice('{1} Réessayez dans une seconde.|[2,*] Réessayez dans :count secondes.', $wait, ['count' => $wait]) : null,
])
