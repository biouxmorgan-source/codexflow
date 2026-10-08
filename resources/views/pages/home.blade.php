{{-- Page d'accueil publique : ce que fait LoreMundi, pour les visiteurs et les moteurs de recherche. --}}
@php
    $description = __('LoreMundi aide le maître de jeu à préparer ses mondes, mener ses séances et partager avec ses joueurs ce qu’ils doivent savoir, quel que soit le jeu de rôle.');
    $locales = \App\Support\Locale::available();
    $features = [
        ['fields', __('Vos mondes, vos règles'), __('Fiches de personnages, de lieux et d’objets avec vos propres champs, liens [[ ]] entre les fiches, scénarios en scènes. Aucun système de règles imposé : LoreMundi s’adapte à votre jeu.')],
        ['remote', __('Le mode Session'), __('La scène en cours avec ses personnages, ses informations et ses règles, une recherche immédiate et des notes rapides : tout ce qu’il faut pendant la partie, sans fouiller.')],
        ['secret', __('Des secrets bien gardés'), __('Chaque fiche a une zone publique et une zone MJ. Vous révélez une information à un seul personnage ou à toute la table, quand vous le décidez : le reste ne quitte jamais votre écran.')],
        ['members', __('Vos joueurs à bord'), __('Invitation par lien, fiche de personnage avec sa feuille PDF, compteurs, connaissances et possessions, journal, objets à « Donner » et échanges entre joueurs.')],
        ['messages', __('Le lien entre les séances'), __('Messagerie de groupe et privée, notifications en direct, application installable sur téléphone, fiche lisible hors ligne.')],
        ['screen', __('Un écran pour la table'), __('Montrez un portrait, un document ou une carte avec sa grille et ses jetons sur la télé, pilotez-le depuis votre téléphone. Jamais un secret à l’écran.')],
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
        <meta property="og:site_name" content="LoreMundi">
        <meta property="og:title" content="LoreMundi · {{ __('L’assistant du maître de jeu de rôle') }}">
        <meta property="og:description" content="{{ $description }}">
        <meta property="og:url" content="{{ url('/') }}">
        <meta property="og:image" content="{{ asset('images/og-loremundi.png') }}">
        <meta property="og:locale" content="{{ str_replace('-', '_', app()->getLocale()) }}">
        <meta name="twitter:card" content="summary_large_image">
        <script type="application/ld+json">{!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'WebApplication',
            'name' => 'LoreMundi',
            'url' => url('/'),
            'applicationCategory' => 'GameApplication',
            'operatingSystem' => 'Web',
            'description' => $description,
            'inLanguage' => array_keys($locales),
            'publisher' => ['@type' => 'Organization', 'name' => 'Autistic Intelligence'],
            'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'EUR'],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    </x-slot:head>

    <main id="contenu">
        <section class="mx-auto grid max-w-6xl items-center gap-10 px-4 pt-8 pb-16 lg:grid-cols-[1fr_1.1fr] lg:pt-16">
            <div>
                <p class="text-sm font-medium tracking-wide text-mundi uppercase">Every world has a story.</p>
                <h1 class="mt-3 text-4xl font-semibold tracking-tight text-balance sm:text-5xl">{{ __('L’assistant du maître de jeu, pour toutes vos campagnes') }}</h1>
                <p class="mt-5 text-lg text-stone-700">{{ $description }}</p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('register') }}" class="btn-primary px-5 py-2.5 text-base">{{ __('Commencer gratuitement') }}</a>
                    <a href="#fonctionnalites" class="btn-secondary px-5 py-2.5 text-base">{{ __('Voir les fonctionnalités') }}</a>
                </div>
                <p class="mt-4 text-sm text-stone-500">{{ __('Une campagne de démonstration se charge en un clic. Aucune carte bancaire demandée.') }}</p>
            </div>
            <figure class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-xl">
                <img src="{{ asset('images/home-session.webp') }}" width="1280" height="800" alt="{{ __('Le mode Session de LoreMundi : la scène en cours, ses personnages et ses informations.') }}" class="block h-auto w-full" fetchpriority="high">
            </figure>
        </section>

        <section id="fonctionnalites" class="border-y border-stone-200 bg-white py-16">
            <div class="mx-auto max-w-6xl px-4">
                <h2 class="text-3xl font-semibold tracking-tight">{{ __('Tout ce qu’il faut pour mener vos parties') }}</h2>
                <p class="mt-3 max-w-3xl text-stone-700">{{ __('LoreMundi n’est pas une table virtuelle : c’est un outil pour préparer, retrouver et partager, autour d’une vraie table ou en ligne.') }}</p>
                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($features as [$icon, $title, $text])
                        <article class="rounded-xl border border-stone-200 bg-parchment p-5">
                            <x-icon :name="$icon" class="size-7 text-mundi" />
                            <h3 class="mt-3 text-lg font-semibold">{{ $title }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-stone-700">{{ $text }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="mx-auto grid max-w-6xl items-center gap-10 px-4 py-16 lg:grid-cols-[1.1fr_1fr]">
            <figure class="order-last overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-xl lg:order-first">
                <img src="{{ asset('images/home-player.webp') }}" width="1280" height="800" loading="lazy" alt="{{ __('La fiche d’un personnage vue par son joueur : compteurs, connaissances et possessions.') }}" class="block h-auto w-full">
            </figure>
            <div>
                <h2 class="text-3xl font-semibold tracking-tight">{{ __('Chaque joueur voit ce que son personnage sait') }}</h2>
                <p class="mt-4 text-stone-700">{{ __('Les connaissances et les possessions appartiennent au personnage. Le joueur retrouve sur sa fiche ce que vous lui avez révélé, prend ses notes, propose ses intentions pour la prochaine séance.') }}</p>
                <p class="mt-4 text-stone-700">{{ __('Les droits sont vérifiés par le serveur à chaque action, pas seulement masqués à l’écran : ce qui est réservé au MJ le reste.') }}</p>
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
            <p class="mt-3 text-stone-700">{{ __('LoreMundi est en développement actif : de nouvelles fonctions arrivent régulièrement, et vos retours comptent.') }}</p>
            <a href="{{ route('register') }}" class="btn-primary mt-6 inline-block px-5 py-2.5 text-base">{{ __('Créer mon compte') }}</a>
        </section>
    </main>

</x-layouts.public>
