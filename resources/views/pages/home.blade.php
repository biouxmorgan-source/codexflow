{{-- Page d'accueil publique : ce que fait SagaWyn, pour les visiteurs et les moteurs de recherche. --}}
@php
    // Captures dans la langue de la page (une paire par langue de l'interface).
    $shot = \App\Support\Locale::isAvailable(app()->getLocale()) ? app()->getLocale() : \App\Support\Locale::DEFAULT;
    $description = __('SagaWyn accompagne le maître de jeu de la préparation à la dernière séance : fiches et scénarios, carte de bataille, dés, écran de table et un espace pour chaque joueur, quel que soit le jeu de rôle.');
    $locales = \App\Support\Locale::available();
    // Les fonctions, rangées par moment de la campagne : préparer, jouer, faire vivre entre les séances.
    $moments = [
        ['book', __('Préparer'), __('Un monde cohérent, des scénarios prêts à jouer, sans tout ressaisir.'), [
            ['fields', __('Vos mondes, vos règles'), __('Fiches de personnages, de lieux et d’objets avec vos propres champs, liens [[ ]], relations et tags. Aucun système de règles imposé : SagaWyn s’adapte à votre jeu.')],
            ['timeline', __('Des scénarios en scènes'), __('Chaque scène réunit ses personnages, ses indices et ses documents. Après la partie, notez si elle s’est jouée comme prévu ou ce qui s’est vraiment passé.')],
            ['map', __('Une grande carte de campagne'), __('Placez vos lieux sur une carte illustrée, reliez chaque point à ses scènes et à ses fiches, descendez dans une sous-carte d’un clic.')],
            ['ai', __('Importer plutôt que ressaisir'), __('Fichiers CSV, archives de campagne et modèles de jeu. Un livre de scénario se prépare à l’import avec l’IA de votre choix, sur votre propre clé.')],
        ]],
        ['remote', __('À la table'), __('Autour d’une vraie table ou à distance, tout ce qu’il faut pendant la partie, sans fouiller.'), [
            ['remote', __('Le mode Session'), __('La scène en cours, ses personnages, ses règles et vos notes rapides sur un écran que vous organisez à votre goût, avec une recherche immédiate.')],
            ['grid', __('Une carte de bataille partagée'), __('Grille, jetons, gabarits de zone à la bonne taille et ordre d’initiative. Les joueurs déplacent leur jeton, même à distance, par un lien ou un QR code.')],
            ['dice', __('Des dés pour toute la table'), __('Pools mixtes, seuils, résultat brut et bonus lus sur la feuille du personnage. Le MJ demande un jet, chaque joueur lance depuis son téléphone.')],
            ['screen', __('Un écran pour la table'), __('Montrez un portrait, un document, la carte de campagne ou le combat sur la télé, et pilotez tout depuis votre téléphone. Jamais un secret à l’écran.')],
            ['music', __('L’ambiance sonore'), __('Votre bibliothèque de sons, réutilisable dans toutes vos campagnes, se lance d’un geste pendant la scène.')],
            ['secret', __('Des secrets bien gardés'), __('Chaque fiche a une zone publique et une zone MJ. Vous révélez une information à un seul personnage ou à toute la table, quand vous le décidez.')],
        ]],
        ['messages', __('Entre les séances'), __('La campagne continue quand la table se lève.'), [
            ['members', __('Vos joueurs à bord'), __('Invitation par lien, fiche de personnage avec sa feuille PDF, compteurs, connaissances et possessions, objets à « Donner » et échanges entre joueurs.')],
            ['messages', __('Le lien vivant'), __('Messagerie de groupe et privée, notifications en direct, application installable sur téléphone. La fiche reste modifiable hors ligne et se synchronise au retour du réseau.')],
            ['journal', __('Le livre des aventures'), __('Le journal de campagne et le livre des aventures racontent l’histoire de votre table, scène après scène, telle qu’elle s’est vraiment jouée.')],
            ['archive', __('Mes feuilles'), __('Un espace privé pour vos feuilles de personnage, en onglets et en blocs à disposer, avec leurs lancers de dés enregistrés. Utile aussi quand c’est vous le joueur.')],
        ]],
    ];
@endphp
<x-layouts.public :title="__('L’assistant du maître de jeu de rôle')">
    <x-slot:head>
        <meta name="description" content="{{ $description }}">
        <link rel="canonical" href="{{ url('/').'?lang='.app()->getLocale() }}">
        @foreach ($locales as $code => $name)
            <link rel="alternate" hreflang="{{ $code }}" href="{{ url('/').'?lang='.$code }}">
        @endforeach
        <link rel="alternate" hreflang="x-default" href="{{ url('/') }}">
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="SagaWyn">
        <meta property="og:title" content="SagaWyn · {{ __('L’assistant du maître de jeu de rôle') }}">
        <meta property="og:description" content="{{ $description }}">
        <meta property="og:url" content="{{ url('/') }}">
        <meta property="og:image" content="{{ asset('images/og-sagawyn.png') }}">
        <meta property="og:locale" content="{{ str_replace('-', '_', app()->getLocale()) }}">
        <meta name="twitter:card" content="summary_large_image">
        <script type="application/ld+json">{!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'WebApplication',
            'name' => 'SagaWyn',
            'url' => url('/'),
            'applicationCategory' => 'GameApplication',
            'operatingSystem' => 'Web',
            'description' => $description,
            'inLanguage' => array_keys($locales),
            'publisher' => ['@type' => 'Organization', 'name' => 'Autistic Intelligence', 'logo' => asset('images/autistic-intelligence.png')],
            'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'EUR'],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    </x-slot:head>

    <main id="contenu">
        <section class="mx-auto grid max-w-6xl items-center gap-10 px-4 pt-8 pb-16 lg:grid-cols-[1fr_1.1fr] lg:pt-16">
            <div>
                <p class="text-sm font-medium tracking-wide text-wyn uppercase">Every world has a story.</p>
                <h1 class="mt-3 text-4xl font-semibold tracking-tight text-balance sm:text-5xl">{{ __('Préparez vos mondes, menez vos séances, faites vivre la campagne') }}</h1>
                <p class="mt-5 text-lg text-stone-700">{{ $description }}</p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('register') }}" class="btn-primary px-5 py-2.5 text-base">{{ __('Commencer gratuitement') }}</a>
                    <a href="#fonctionnalites" class="btn-secondary px-5 py-2.5 text-base">{{ __('Voir les fonctionnalités') }}</a>
                </div>
                <p class="mt-4 text-sm text-stone-500">{{ __('Une campagne de démonstration se charge en un clic. Aucune carte bancaire demandée.') }}</p>
                @if ($discord = config('codexflow.discord_url'))
                    <a href="{{ $discord }}" class="mt-3 inline-flex items-center gap-2 text-sm font-medium text-codex hover:underline" target="_blank" rel="noopener">
                        <x-icon name="messages" class="size-4" />{{ __('Rejoindre la communauté sur Discord') }}
                    </a>
                @endif
            </div>
            <figure class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-xl">
                <img src="{{ asset('images/home-session-'.$shot.'.webp') }}" width="1280" height="800" alt="{{ __('Le mode Session de SagaWyn : la scène en cours, ses personnages et ses informations.') }}" class="block h-auto w-full" fetchpriority="high">
            </figure>
        </section>

        <section id="fonctionnalites" class="border-y border-stone-200 bg-white py-16">
            <div class="mx-auto max-w-6xl px-4">
                <h2 class="text-3xl font-semibold tracking-tight text-balance">{{ __('Tout ce qu’il faut, de la préparation à la dernière séance') }}</h2>
                <p class="mt-3 max-w-3xl text-stone-700">{{ __('SagaWyn n’impose aucun système de règles. Il vous aide à préparer, à mener la partie autour d’une vraie table ou à distance, et à garder le fil entre les séances.') }}</p>
                @foreach ($moments as [$momentIcon, $moment, $intro, $features])
                    <div class="mt-12">
                        <div class="flex items-center gap-3">
                            <span class="flex size-10 items-center justify-center rounded-full bg-wyn/10 text-wyn"><x-icon :name="$momentIcon" class="size-5" /></span>
                            <h3 class="text-2xl font-semibold tracking-tight">{{ $moment }}</h3>
                        </div>
                        <p class="mt-2 text-stone-600">{{ $intro }}</p>
                        <div @class(['mt-6 grid gap-5 sm:grid-cols-2', count($features) % 3 ? 'lg:grid-cols-4' : 'lg:grid-cols-3'])>
                            @foreach ($features as [$icon, $title, $text])
                                <article class="rounded-xl border border-stone-200 bg-parchment p-5">
                                    <x-icon :name="$icon" class="size-7 text-wyn" />
                                    <h4 class="mt-3 text-lg font-semibold">{{ $title }}</h4>
                                    <p class="mt-2 text-sm leading-relaxed text-stone-700">{{ $text }}</p>
                                </article>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="mx-auto grid max-w-6xl items-center gap-10 px-4 py-16 lg:grid-cols-[1.1fr_1fr]">
            <figure class="order-last overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-xl lg:order-first">
                <img src="{{ asset('images/home-player-'.$shot.'.webp') }}" width="1280" height="800" loading="lazy" alt="{{ __('La fiche d’un personnage vue par son joueur : compteurs, connaissances et possessions.') }}" class="block h-auto w-full">
            </figure>
            <div>
                <h2 class="text-3xl font-semibold tracking-tight">{{ __('Chaque joueur voit ce que son personnage sait') }}</h2>
                <p class="mt-4 text-stone-700">{{ __('Les connaissances et les possessions appartiennent au personnage. Le joueur retrouve sur sa fiche ce que vous lui avez révélé, prend ses notes, propose ses intentions pour la prochaine séance.') }}</p>
                <p class="mt-4 text-stone-700">{{ __('Les droits sont vérifiés par le serveur à chaque action, pas seulement masqués à l’écran : ce qui est réservé au MJ le reste.') }}</p>
            </div>
        </section>

        <section class="border-t border-stone-200 bg-white py-16">
            <div class="mx-auto max-w-4xl px-4 text-center">
                <h2 class="text-3xl font-semibold tracking-tight">{{ __('Découvrez sans rien préparer') }}</h2>
                <p class="mt-4 text-stone-700">{{ __('Dès votre compte créé, chargez une campagne de démonstration qui montre chaque fonction : fiches reliées, carte de campagne, combat sur la carte de bataille, dés, sons, séance déjà jouée et livre des aventures. La bibliothèque de démos accueille d’autres campagnes prêtes à explorer.') }}</p>
                <a href="{{ route('register') }}" class="btn-secondary mt-6 inline-block px-5 py-2.5 text-base">{{ __('Essayer la démonstration') }}</a>
            </div>
        </section>

        <section class="bg-ink py-16 text-parchment">
            <div class="mx-auto grid max-w-6xl gap-8 px-4 md:grid-cols-3">
                <div>
                    <h2 class="text-xl font-semibold">{{ __('Indépendant de tout jeu') }}</h2>
                    <p class="mt-2 text-sm opacity-80">{{ __('Fantasy, horreur, science-fiction ou votre propre création : vous définissez vos champs, vos types de fiche et vos règles. Import CSV, et préparation des fichiers avec l’IA de votre choix.') }}</p>
                </div>
                <div>
                    <h2 class="text-xl font-semibold">{{ __('Vos données restent à vous') }}</h2>
                    <p class="mt-2 text-sm opacity-80">{{ __('Sauvegarde complète de vos campagnes, export, téléchargement de vos données et suppression du compte à tout moment. Hébergé en Europe.') }}</p>
                </div>
                <div>
                    <h2 class="text-xl font-semibold">{{ __('En 8 langues') }}</h2>
                    <p class="mt-2 text-sm opacity-80">{{ implode(', ', $locales) }}.</p>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-3xl px-4 py-16 text-center">
            <h2 class="text-3xl font-semibold tracking-tight">{{ __('Prêt pour votre prochaine séance ?') }}</h2>
            <p class="mt-3 text-stone-700">{{ __('SagaWyn est en développement actif : de nouvelles fonctions arrivent régulièrement, et vos retours comptent.') }}</p>
            <a href="{{ route('register') }}" class="btn-primary mt-6 inline-block px-5 py-2.5 text-base">{{ __('Créer mon compte') }}</a>
            @if ($discord = config('codexflow.discord_url'))
                <p class="mt-8 text-stone-700">{{ __('Une question, une idée ? Retrouvez d’autres maîtres de jeu et l’équipe SagaWyn sur Discord.') }}</p>
                <a href="{{ $discord }}" class="btn-secondary mt-3 inline-block px-5 py-2.5 text-base" target="_blank" rel="noopener">{{ __('Rejoindre la communauté sur Discord') }}</a>
            @endif
        </section>
    </main>

</x-layouts.public>
