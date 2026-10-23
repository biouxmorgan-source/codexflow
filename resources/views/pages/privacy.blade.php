@php
    $legal = config('codexflow.legal');
    $contact = $legal['email'] ? __('à :email', ['email' => $legal['email']]) : __('à l’adresse de contact ci-dessus');
    $sections = [
        'editeur' => [__('Qui édite SagaWyn'), array_filter([
            __('SagaWyn est édité par :publisher, nom commercial de :owner (entrepreneur individuel).', ['publisher' => $legal['publisher'], 'owner' => $legal['owner'] ?: '—']),
            $legal['siret'] ? __('SIRET : :siret', ['siret' => $legal['siret']]) : null,
            $legal['address'] ? __('Adresse : :address', ['address' => $legal['address']]) : null,
            $legal['email'] ? __('Contact : :email', ['email' => $legal['email']]) : null,
            $legal['host'] ? __('Hébergement : :host', ['host' => $legal['host']]) : null,
        ])],
        'contenus-utilisateurs' => [__('Conditions d’utilisation : contenus utilisateurs et propriété intellectuelle'), [
            __('Le contenu que vous mettez en ligne sur SagaWyn (campagnes, fiches, notes, messages, images, cartes, sons, documents) est votre propriété, ou celle de ses auteurs. SagaWyn n’en est pas propriétaire et ne revendique aucun droit sur vos créations.'),
            __('Vous pouvez télécharger à tout moment le contenu de votre compte : « Télécharger mes données », dans « Préférences », donne tout ce que SagaWyn garde sur vous ; les fiches, champs, règles et scènes s’exportent en CSV depuis l’import ; « Exporter la campagne » (✦ Premium) donne une campagne complète avec ses fichiers.'),
            __('SagaWyn se contente de les héberger, de les montrer aux personnes que vous choisissez et d’en faire les copies techniques nécessaires (sauvegardes, aperçus). Cet usage sert uniquement à faire fonctionner le service et s’arrête quand vous supprimez le contenu ou votre compte, une fois les dernières sauvegardes effacées.'),
            __('La responsabilité des données et des fichiers déposés sur SagaWyn appartient à l’utilisateur qui les dépose.'),
            __('Chaque utilisateur est seul responsable des contenus qu’il importe et partage. Il s’engage à disposer des droits nécessaires sur ses textes, images, cartes, musiques, sons et documents, et à ce qu’ils ne portent atteinte ni à la loi ni aux droits d’autrui.'),
            __('Avant chaque import, l’utilisateur déclare disposer de ces droits. Sa déclaration est enregistrée avec sa date et la version du texte accepté.'),
            __('Le MJ décide de ce qu’il partage avec sa table ; chaque joueur répond de ce qu’il écrit ou envoie (notes, messages, fichiers). Partager un contenu avec une table n’en transfère pas la propriété.'),
            __('SagaWyn ne vérifie pas systématiquement les droits attachés aux contenus importés, ne les contrôle pas avant leur mise en ligne et ne les consulte pas, sauf à votre demande (assistance), pour la sécurité du service ou pour traiter un signalement.'),
            __('SagaWyn peut restreindre l’accès à un contenu illicite ou le retirer lorsque la loi applicable l’exige.'),
            __('La responsabilité de SagaWyn reste soumise aux dispositions légales applicables.'),
        ]],
        'importation' => [__('Importation et structuration de contenus'), [
            __('SagaWyn fournit des outils, des modèles de données, des prompts et des instructions destinés à faciliter l’organisation et la structuration d’informations pour les jeux de rôle.'),
            __('Ces outils peuvent être utilisés avec des créations personnelles, des contenus sous licence ouverte ou des documents pour lesquels l’utilisateur dispose des autorisations nécessaires.'),
            __('Les transformations de documents sont effectuées à l’initiative de l’utilisateur, notamment au moyen de services d’intelligence artificielle tiers, indépendants de SagaWyn.'),
            __('L’utilisateur reste responsable du choix des documents sources, du respect de leurs licences et des droits nécessaires à la reproduction, à l’adaptation et au partage des contenus générés.'),
            __('SagaWyn ne revendique aucun droit de propriété sur les créations de ses utilisateurs et ne fournit aucune autorisation d’exploitation d’œuvres appartenant à des tiers.'),
        ]],
        'ia' => [__('Services d’intelligence artificielle'), [
            __('Les fonctions d’IA de SagaWyn passent par le fournisseur que vous choisissez, avec votre propre clé et votre propre compte. SagaWyn n’envoie un contenu à ce fournisseur que lorsque vous lancez vous-même une demande ; vos échanges avec lui relèvent de ses propres conditions.'),
            __('Un texte produit par une IA peut être inexact, incomplet ou proche d’une œuvre existante : relisez-le avant de l’utiliser ou de le partager. Vous en êtes responsable comme de vos autres contenus.'),
        ]],
        'signaler' => [__('Signaler un contenu illicite'), [
            __('Si un contenu hébergé sur SagaWyn vous semble illicite ou porte atteinte à vos droits, notamment d’auteur, écrivez-nous :contact en indiquant qui vous êtes, le contenu concerné et où il se trouve, et pourquoi il pose problème.', ['contact' => $contact]),
            __('Chaque signalement est examiné rapidement. Un contenu manifestement illicite est retiré ou rendu inaccessible ; son auteur peut en être informé et, en cas de manquements répétés, son compte suspendu.'),
        ]],
        'disponibilite' => [__('Disponibilité et sauvegardes'), [
            __('SagaWyn est entretenu avec soin, sans pouvoir garantir un fonctionnement sans interruption ni erreur. Gardez une copie de ce qui compte pour vous : « Exporter la campagne » et le téléchargement de vos données dans « Préférences » sont là pour ça.'),
        ]],
        'marques' => [__('Jeux et marques'), [
            __('SagaWyn est indépendant de tout éditeur de jeu de rôle. Les noms de jeux, d’univers et de marques que les utilisateurs citent appartiennent à leurs propriétaires respectifs ; leur présence sur la plateforme ne signifie ni partenariat ni autorisation.'),
        ]],
        'donnees' => [__('Les données que nous gardons'), [
            __('Votre nom, votre adresse e-mail et votre mot de passe, enregistré sous une forme illisible, même pour nous.'),
            __('Ce que vous créez : campagnes, fiches, notes, messages et fichiers.'),
            __('La date de vos connexions, gardée un an.'),
            __('Votre langue et vos préférences d’affichage.'),
            __('Si vous l’enregistrez, votre clé d’API d’IA, chiffrée, utilisée seulement pour vos propres demandes.'),
            __('Pour chaque import, la déclaration de droits acceptée : date, version du texte et issue de l’import, sans copie des fichiers. Gardée tant que le compte existe, puis cinq ans après sa suppression, pour prouver l’engagement pris ; seul l’administrateur du site y a accès.'),
        ]],
        'usage' => [__('À quoi elles servent'), [
            __('Uniquement à faire fonctionner SagaWyn. Elles ne sont ni vendues, ni prêtées, ni utilisées pour de la publicité.'),
            __('Paiement : Stripe, seulement si vous vous abonnez. IA : le fournisseur que vous choisissez, avec votre clé, seulement quand vous lancez une analyse.'),
            __('Cookies : seulement ceux nécessaires au fonctionnement (session, sécurité). Aucun cookie publicitaire ni de mesure d’audience.'),
        ]],
        'securite' => [__('Sécurité'), [
            __('Connexion chiffrée, mots de passe protégés, double authentification possible, clés d’API chiffrées. Les notes de MJ et les notes « Moi seul » ne quittent jamais le serveur pour qui n’a pas le droit de les lire.'),
        ]],
        'droits' => [__('Vos droits'), [
            __('Dans « Préférences », vous pouvez à tout moment télécharger vos données et supprimer votre compte.'),
            __('Pour toute question sur vos données, écrivez-nous. Vous pouvez aussi saisir la CNIL (cnil.fr).'),
        ]],
    ];
@endphp
<x-dynamic-component :component="auth()->check() ? 'layouts.app' : 'layouts.guest'" :title="__('Confidentialité et mentions légales')">
    @auth
        <h1 class="text-2xl font-semibold">{{ __('Confidentialité et mentions légales') }}</h1>
    @endauth
    <div class="mt-4 space-y-6 text-sm">
        @foreach ($sections as $id => [$heading, $lines])
            <section id="{{ $id }}" class="scroll-mt-20">
                <h2 class="font-semibold">{{ $heading }}</h2>
                @if ($loop->first)
                    <x-publisher-logo variant="complet" class="mt-3 h-28 text-ink" />
                @endif
                <ul class="mt-2 list-disc space-y-1 pl-5 text-stone-700">
                    @foreach ($lines as $line)
                        <li>{{ $line }}</li>
                    @endforeach
                </ul>
            </section>
        @endforeach
    </div>
</x-dynamic-component>
