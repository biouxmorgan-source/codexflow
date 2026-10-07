@php
    $sections = [
        __('Pour commencer') => [
            [__("Qu'est-ce que CodexFlow ?"), __("Un assistant pour le maître de jeu, pensé pour les parties autour d'une vraie table. Il prépare le monde, garde la mémoire de la campagne et distribue l'information aux joueurs, sans remplacer les dés, les figurines ni les discussions.")],
            [__('Faut-il installer quelque chose ?'), __("Non : CodexFlow fonctionne dans un navigateur récent. Vous pouvez aussi l'installer comme une application sur ordinateur, téléphone ou tablette (voir « Configuration recommandée »).")],
            [__('Comment changer la langue, le thème ou la taille du texte ?'), __('Dans « Préférences », en cliquant sur votre nom en haut à droite. Vos choix suivent votre compte sur tous vos appareils.')],
            [__('Les joueurs voient-ils mes notes de MJ ?'), __('Jamais. La zone MJ d’une fiche ne quitte pas le serveur pour un joueur : il ne voit que ce que vous lui avez révélé ou donné, et seulement pour son personnage.')],
        ],
        __('Pour le MJ') => [
            [__('Comment inviter mes joueurs ?'), __("Dans la campagne, ouvrez « Membres » et créez un lien d'invitation par joueur. Envoyez-le comme vous voulez (message, Discord, courriel) : le joueur se connecte ou crée son compte, et rejoint la campagne.")],
            [__('Puis-je préparer avec un autre MJ, ou inviter un spectateur ?'), __("Oui. En créant le lien dans « Membres », choisissez le rôle : un co-MJ prépare et mène avec vous, avec accès à la zone MJ, mais ne gère ni les membres ni la suppression de la campagne ; un spectateur ne voit que l'écran de table.")],
            [__('Comment attribuer un personnage à un joueur ?'), __('Dans la page « Personnages » de la campagne : créez la fiche ou choisissez un prétiré, puis désignez le joueur. Vous pouvez la réattribuer à tout moment, et joindre la feuille PDF.')],
            [__('Comment révéler une information ou donner un objet ?'), __('Depuis une fiche, un document, une règle ou le mode Session, utilisez « Révéler » ou « Donner » et choisissez les personnages. Ils le reçoivent aussitôt, avec une notification, et tout est noté dans le journal.')],
            [__('À quoi servent les secrets ?'), __("Un secret est une information à part (« Morel travaille pour le Culte »), reliée aux fiches, scènes ou documents concernés. Il apparaît sur ces pages et en mode Session : un clic sur un personnage le lui révèle, un autre clic annule. « Historique des révélations » montre qui a appris quoi, et pendant quelle séance.")],
            [__('Comment vérifier ce qu’un joueur peut voir ?'), __("Sur la page d'un personnage, « Voir comme » affiche exactement ce que son joueur voit, en lecture seule, avec un bandeau pour le rappeler.")],
            [__('Comment montrer une carte ou une image sur la télé ?'), __("En mode Session, ouvrez l'écran de table, glissez la fenêtre sur la télé ou le projecteur et passez en plein écran. Choisissez ensuite ce qui s'affiche ; vous pouvez aussi le partager sur les appareils des joueurs.")],
            [__('Comment utiliser une carte avec une grille et des jetons ?'), __("Dans « Cartes », transformez une image des documents en carte. Vous pouvez y ajouter une grille carrée (taille, alignement, échelle comme « 1 case = 1,5 m »), des jetons liés aux fiches et une règle pour mesurer une distance. Le cadrage choisi est celui de l'écran de table, et un jeton masqué n'y apparaît pas. Vos figurines restent les bienvenues : la carte est un support, pas une table virtuelle.")],
            [__('Puis-je piloter l’écran de table depuis mon téléphone ?'), __("Oui : ouvrez « Télécommande » (depuis le mode Session ou une carte) sur votre téléphone. Vous y videz l'écran, passez à l'élément suivant de la scène, déplacez et zoomez la carte affichée, montrez ou masquez ses jetons.")],
            [__('Où voir ce que le MJ vient de me révéler ?'), __("Ça s'affiche tout seul, dans une fenêtre sur la page où vous êtes, avec le contenu. Si vous n'étiez pas connecté, la fenêtre s'ouvre à votre prochaine visite. Tout reste ensuite sur la page de votre personnage.")],
            [__('Dois-je tout saisir à la main ?'), __("Non : la page « Importer » accepte des fichiers CSV ou JSON pour créer d'un coup fiches, scènes, règles ou champs. Mais tout peut aussi se faire directement dans l'application.")],
        ],
        __('Pour les joueurs') => [
            [__('Où trouver la fiche de mon personnage ?'), __('Dans « Mes campagnes », cliquez sur la campagne : vous arrivez directement sur la fiche de votre personnage, avec ses connaissances, ses possessions et vos notes.')],
            [__('Puis-je consulter ma fiche sans réseau ?'), __("Oui, une fois l'application installée et votre fiche ouverte au moins une fois sur l'appareil : elle reste lisible hors ligne.")],
            [__('Comment être prévenu des révélations et des messages ?'), __("Dans « Notifications », bloc « Sur cet appareil », activez les notifications : vous serez prévenu même quand l'application est fermée.")],
        ],
    ];
@endphp
<x-layouts.app :title="__('Aide')">
    <div class="max-w-3xl space-y-6">
        <div>
            <h1 class="text-2xl font-semibold">{{ __('Aide') }}</h1>
            <p class="mt-1 text-sm text-stone-600">{{ __('Les questions les plus fréquentes. Vous ne trouvez pas la réponse ? Écrivez-nous.') }}</p>
        </div>

        @foreach ($sections as $heading => $questions)
            <section class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="mb-3 font-semibold">{{ $heading }}</h2>
                <div class="divide-y divide-stone-200">
                    @foreach ($questions as [$question, $answer])
                        <details class="group py-2">
                            <summary class="cursor-pointer list-none font-medium text-codex marker:hidden">
                                <span class="mr-1 inline-block transition group-open:rotate-90" aria-hidden="true">›</span>{{ $question }}
                            </summary>
                            <p class="mt-2 pl-4 text-stone-700">{{ $answer }}</p>
                        </details>
                    @endforeach
                </div>
            </section>
        @endforeach

        <section class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
            <h2 class="mb-2 font-semibold">{{ __('Un problème ?') }}</h2>
            <p class="text-stone-700">{{ __("Si quelque chose ne marche pas comme prévu, dites-le-nous : le message arrive directement à l'équipe, avec la page concernée.") }}</p>
            <a href="{{ route('bugs.create') }}" class="btn-primary mt-3 inline-block" wire:navigate>{{ __('Signaler un problème') }}</a>
        </section>
    </div>
</x-layouts.app>
