@php
    $legal = config('codexflow.legal');
    $sections = [
        __('Qui édite LoreMundi') => array_filter([
            __('LoreMundi est édité par :publisher, nom commercial de :owner (entrepreneur individuel).', ['publisher' => $legal['publisher'], 'owner' => $legal['owner'] ?: '—']),
            $legal['siret'] ? __('SIRET : :siret', ['siret' => $legal['siret']]) : null,
            $legal['address'] ? __('Adresse : :address', ['address' => $legal['address']]) : null,
            $legal['email'] ? __('Contact : :email', ['email' => $legal['email']]) : null,
            $legal['host'] ? __('Hébergement : :host', ['host' => $legal['host']]) : null,
        ]),
        __('Les données que nous gardons') => [
            __('Votre nom, votre adresse e-mail et votre mot de passe, enregistré sous une forme illisible, même pour nous.'),
            __('Ce que vous créez : campagnes, fiches, notes, messages et fichiers.'),
            __('La date de vos connexions, gardée un an.'),
            __('Votre langue et vos préférences d’affichage.'),
            __('Si vous l’enregistrez, votre clé d’API d’IA, chiffrée, utilisée seulement pour vos propres demandes.'),
        ],
        __('À quoi elles servent') => [
            __('Uniquement à faire fonctionner LoreMundi. Elles ne sont ni vendues, ni prêtées, ni utilisées pour de la publicité.'),
            __('Paiement : Stripe, seulement si vous vous abonnez. IA : le fournisseur que vous choisissez, avec votre clé, seulement quand vous lancez une analyse.'),
            __('Cookies : seulement ceux nécessaires au fonctionnement (session, sécurité). Aucun cookie publicitaire ni de mesure d’audience.'),
        ],
        __('Sécurité') => [
            __('Connexion chiffrée, mots de passe protégés, double authentification possible, clés d’API chiffrées. Les notes de MJ et les notes « Moi seul » ne quittent jamais le serveur pour qui n’a pas le droit de les lire.'),
        ],
        __('Vos droits') => [
            __('Dans « Préférences », vous pouvez à tout moment télécharger vos données et supprimer votre compte.'),
            __('Pour toute question sur vos données, écrivez-nous. Vous pouvez aussi saisir la CNIL (cnil.fr).'),
        ],
    ];
@endphp
<x-dynamic-component :component="auth()->check() ? 'layouts.app' : 'layouts.guest'" :title="__('Confidentialité et mentions légales')">
    @auth
        <h1 class="text-2xl font-semibold">{{ __('Confidentialité et mentions légales') }}</h1>
    @endauth
    <div class="mt-4 space-y-6 text-sm">
        @foreach ($sections as $heading => $lines)
            <section>
                <h2 class="font-semibold">{{ $heading }}</h2>
                <ul class="mt-2 list-disc space-y-1 pl-5 text-stone-700">
                    @foreach ($lines as $line)
                        <li>{{ $line }}</li>
                    @endforeach
                </ul>
            </section>
        @endforeach
    </div>
</x-dynamic-component>
