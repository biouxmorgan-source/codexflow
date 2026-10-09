@php
    $sections = [
        __('Pour commencer') => [
            [__("Qu'est-ce que LoreMundi ?"), __("Un assistant pour le maître de jeu, pensé pour les parties autour d'une vraie table. Il prépare le monde, garde la mémoire de la campagne et distribue l'information aux joueurs, sans remplacer les dés, les figurines ni les discussions.")],
            [__('Faut-il installer quelque chose ?'), __("Non : LoreMundi fonctionne dans un navigateur récent. Vous pouvez aussi l'installer comme une application sur ordinateur, téléphone ou tablette (voir « Configuration recommandée »).")],
            [__('Comment changer la langue, le thème ou la taille du texte ?'), __('Dans « Préférences », en cliquant sur votre nom en haut à droite. Vos choix suivent votre compte sur tous vos appareils.')],
            [__('Les joueurs voient-ils mes notes de MJ ?'), __('Jamais. La zone MJ d’une fiche ne quitte pas le serveur pour un joueur : il ne voit que ce que vous lui avez révélé ou donné, et seulement pour son personnage.')],
        ],
        __('Pour le MJ') => [
            [__('Comment inviter mes joueurs ?'), __("Dans la campagne, ouvrez « Membres » et créez un lien d'invitation par joueur. Envoyez-le comme vous voulez (message, Discord, courriel) : le joueur se connecte ou crée son compte, et rejoint la campagne.")],
            [__('Puis-je préparer avec un autre MJ, ou inviter un spectateur ?'), __("Oui. En créant le lien dans « Membres », choisissez le rôle : un co-MJ prépare et mène avec vous, avec accès à la zone MJ, mais ne gère ni les membres ni la suppression de la campagne ; un spectateur ne voit que l'écran de table, en permanence, même quand vous ne le partagez pas aux joueurs : c'est le rôle d'une télé ou d'un projecteur.")],
            [__('Comment attribuer un personnage à un joueur ?'), __('Dans la page « Personnages » de la campagne : créez la fiche ou choisissez un prétiré, puis désignez le joueur. Vous pouvez la réattribuer à tout moment, et joindre la feuille PDF.')],
            [__('Comment révéler une information ou donner un objet ?'), __('Depuis une fiche, un document, une règle ou le mode Session, utilisez « Révéler » ou « Donner » et choisissez les personnages. Ils le reçoivent aussitôt, avec une notification, et tout est noté dans le journal.')],
            [__('À quoi servent les secrets ?'), __("Un secret est une information à part (« Morel travaille pour le Culte »), reliée aux fiches, scènes ou documents concernés. Il apparaît sur ces pages et en mode Session : un clic sur un personnage le lui révèle, un autre clic annule. « Historique des révélations » montre qui a appris quoi, et pendant quelle séance.")],
            [__('Comment vérifier ce qu’un joueur peut voir ?'), __("Sur la page d'un personnage, « Voir comme » affiche exactement ce que son joueur voit, en lecture seule, avec un bandeau pour le rappeler.")],
            [__('Comment montrer une carte ou une image sur la télé ?'), __("En mode Session, ouvrez l'écran de table, glissez la fenêtre sur la télé ou le projecteur et passez en plein écran. Choisissez ensuite ce qui s'affiche ; vous pouvez aussi le partager sur les appareils des joueurs.")],
            [__('Comment utiliser une carte avec une grille et des jetons ?'), __("Dans « Cartes », transformez une image des documents en carte. Vous pouvez y ajouter une grille carrée (taille, alignement, échelle comme « 1 case = 1,5 m »), des jetons liés aux fiches et une règle pour mesurer une distance. Le cadrage choisi est celui de l'écran de table, et un jeton masqué n'y apparaît pas. Vos figurines restent les bienvenues : la carte est un support, pas une table virtuelle.")],
            [__('Puis-je piloter l’écran de table depuis mon téléphone ?'), __("Oui : ouvrez « Télécommande » (depuis le mode Session ou une carte) sur votre téléphone. Vous y videz l'écran, passez à l'élément suivant de la scène, déplacez et zoomez la carte affichée, montrez ou masquez ses jetons.")],
            [__('Comment voir qui est lié à qui ?'), __("Le « Graphe des relations » dessine les fiches de la campagne et leurs relations. Cliquez sur une fiche pour la mettre au centre, choisissez la profondeur (1 à 3 relations) ou un type de fiche. « Voir comme… » montre le réseau tel qu'un personnage le connaît : seulement les fiches qu'il connaît et les relations publiques entre elles.")],
            [__('À quoi sert la chronologie ?'), __("À noter l'histoire du monde, les événements prévus et ce qui s'est passé en jeu, avec des dates libres (« 12 mars 1924 », « Jour 3 », « Nuit 2 »). Un événement joué noté pendant une séance est rattaché à la séance et à la scène en cours. Cochez « Visible des joueurs » pour qu'ils le lisent.")],
            [__('Où voir ce que le MJ vient de me révéler ?'), __("Ça s'affiche tout seul, dans une fenêtre sur la page où vous êtes, avec le contenu. Si vous n'étiez pas connecté, la fenêtre s'ouvre à votre prochaine visite. Tout reste ensuite sur la page de votre personnage.")],
            [__('Puis-je sauvegarder ma campagne ou la confier à quelqu’un ?'), __("Oui : en bas de la page de la campagne, « Exporter la campagne » télécharge une archive .zip avec le jeu, le monde, les fiches, scénarios, documents, cartes, secrets et la chronologie, fichiers compris. Le propriétaire peut aussi « Télécharger la sauvegarde complète », qui ajoute la table : personnages, ce qu'ils ont reçu, séances, notes, messages et journal, sans adresse e-mail ni note gardée pour soi. Depuis « Mes campagnes », « Importer une campagne » la recrée, chez vous ou chez un autre MJ ; les personnages y reviennent sans joueur, prêts à être confiés. Le formulaire d'import indique la taille maximale acceptée par le serveur : une archive plus lourde demande d'augmenter « upload_max_filesize » et « post_max_size » dans la configuration PHP.")],
            [__('Puis-je retirer des fonctions dont ma table n’a pas besoin ?'), __("Oui : sur la page de la campagne, bloc « Fonctions de la campagne », décochez l'écran de table, les cartes, les échanges entre joueurs, le graphe, la chronologie ou l'assistant IA. La fonction disparaît pour tous les membres, sans rien effacer, et revient telle quelle dès que vous la recochez.")],
            [__('Puis-je importer le contenu d’un livre de jeu ou d’un scénario ?'), __("Oui, avec l'IA de votre choix : dans « Importer », ouvrez « Préparez les fichiers avec une IA », copiez le prompt et collez-le dans votre IA avec le PDF. Elle prépare les fichiers à importer (champs, fiches, règles, scènes) et un guide pas à pas. Utilisez uniquement des documents que vous possédez.")],
            [__('Comment partager ma façon d’organiser les fiches ?'), __("Dans « Champs du jeu », « Exporter le modèle » télécharge la structure seule : types de fiche, champs et étiquettes, avec les règles du jeu si vous le souhaitez, sans aucun contenu. Un autre MJ l'importe au même endroit, dans son propre jeu ; ce qui existe déjà sous le même nom n'est pas touché.")],
            [__('Comment une IA peut-elle m’aider après une séance ?'), __("Ouvrez « Assistant IA » dans la campagne et choisissez la séance : LoreMundi prépare un texte avec vos notes et le contexte. Collez-le dans l'IA de votre choix, puis collez sa réponse : elle devient des propositions (résumé, événements, relations, statuts, notes, révélations) que vous acceptez, modifiez ou rejetez une à une. LoreMundi n'envoie rien lui-même, et rien ne change sans votre accord.")],
            [__('Puis-je brancher ma propre IA ?'), __("Oui : dans « Préférences », bloc « Assistant IA », choisissez Claude, ChatGPT ou Mistral et collez une clé d'API à votre nom. L'assistant propose alors « Analyser directement », sans copier-coller. Les appels sont facturés par le fournisseur sur votre compte : fixez-y une limite de dépense. La clé est chiffrée, jamais réaffichée ni exportée.")],
            [__('Dois-je tout saisir à la main ?'), __("Non : la page « Importer » accepte des fichiers CSV ou JSON pour créer d'un coup fiches, scènes, règles ou champs. Mais tout peut aussi se faire directement dans l'application.")],
        ],
        __('Votre compte') => [
            [__('Combien coûte LoreMundi ?'), __("Rejoindre une campagne comme joueur est toujours gratuit. Pour mener vos propres campagnes, un essai Premium commence à votre première campagne ; ensuite, la formule gratuite permet de mener une campagne, et Premium lève les limites et donne plus d'espace pour vos fichiers. Les détails sont dans « Préférences ».")],
            [__('Comment changer mon adresse e-mail ou mon mot de passe ?'), __("Dans « Préférences », bloc « Mon compte ». Votre mot de passe actuel est demandé, et l'ancienne adresse est prévenue du changement. Mot de passe oublié : utilisez le lien « Mot de passe oublié ? » de la page de connexion.")],
            [__('Comment protéger mon compte avec la double authentification ?'), __("Dans « Préférences », bloc « Sécurité », activez la double authentification avec une application de votre téléphone (Google Authenticator, Aegis, 1Password…). Gardez les codes de secours affichés à ce moment-là : chacun permet une connexion sans téléphone.")],
            [__('J’ai perdu mon téléphone et mes codes de secours'), __("Écrivez-nous depuis l'adresse e-mail de votre compte : après vérification, l'administrateur retire la double authentification, et vous pourrez la réactiver.")
                .(filled(config('codexflow.legal.email')) ? ' '.__('Adresse : :email.', ['email' => config('codexflow.legal.email')]) : '')],
            [__('Puis-je récupérer ou effacer mes données ?'), __("Oui, dans « Préférences », bloc « Mes données » : « Télécharger mes données » donne tout ce que LoreMundi garde sur vous, et « Supprimer mon compte » efface votre compte, vos campagnes et vos fichiers. Pensez à exporter vos campagnes avant.")],
        ],
        __('Pour les joueurs') => [
            [__('Où trouver la fiche de mon personnage ?'), __('Dans « Mes campagnes », cliquez sur la campagne : vous arrivez directement sur la fiche de votre personnage, avec ses connaissances, ses possessions et vos notes.')],
            [__('Puis-je consulter ma fiche sans réseau ?'), __("Oui, une fois l'application installée et votre fiche ouverte au moins une fois sur l'appareil : elle reste lisible hors ligne.")],
            [__('Puis-je voir les liens entre ce que mon personnage connaît ?'), __("Oui : depuis la page de votre personnage, « Graphe » montre les fiches qu'il connaît et les relations que le MJ a laissées visibles entre elles. « Chronologie » rassemble les événements que le MJ a partagés.")],
            [__('Comment être prévenu des révélations et des messages ?'), __("Dans « Notifications », bloc « Sur cet appareil », activez les notifications : vous serez prévenu même quand l'application est fermée.")],
        ],
    ];
@endphp
{{-- Ouverte aux visiteurs : on peut lire l'aide avant de créer un compte. --}}
<x-dynamic-component :component="auth()->check() ? 'layouts.app' : 'layouts.public'" :title="__('Aide')">
    <div @class(['max-w-3xl space-y-6', 'mx-auto px-4 pt-4 pb-16' => auth()->guest()])>
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
            {{-- Avec ou sans compte : sans compte, le formulaire demande une adresse pour répondre. --}}
            <a href="{{ route('bugs.create') }}" class="btn-primary mt-3 inline-block" @auth wire:navigate @endauth>{{ __('Signaler un problème') }}</a>
        </section>
    </div>
</x-dynamic-component>
