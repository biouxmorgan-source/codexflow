<?php

// Nieuw per versie, van de nieuwste naar de oudste. Getoond in
// ‘Wat is er nieuw’ (één keer na elke update) en op de gelijknamige pagina.
return [

    '0.16.0' => [
        'date' => '2026-10-08',
        'title' => 'Premium-abonnement en gratis proefperiode',
        'items' => [
            'Premium nemen via ‘Voorkeuren’: maandelijkse of jaarlijkse betaling, beveiligd door Stripe, met facturen en opzeggen in het Stripe-portaal. Premium loopt tot het einde van de betaalde periode.',
            'Gratis proefperiode: zes weken met alle functies, vanaf je eerste campagne als SL. Een speler die nooit SL is, begint er niet aan. De duur stel je in bij het beheer.',
            'Een tabblad ‘Roadmap’ in het beheer om de plannen voor het platform bij te houden.',
        ],
    ],

    '0.15.0' => [
        'date' => '2026-10-08',
        'title' => 'Beheerconsole en abonnementen',
        'items' => [
            'Een beheerconsole: de accounts met hun abonnement, abonnementsdata, gebruikte opslag, of er een AI-sleutel is, campagnes en aanmeldingen, zonder persoonsgegevens. De beheerder stelt ieders abonnement in en kan een link sturen om het wachtwoord opnieuw in te stellen, zonder het ooit te zien.',
            'Drie abonnementen: beheerder, premium en gratis. Opslag, aantal campagnes en de functies van het gratis abonnement stel je in de console in; spelen, co-SL of toeschouwer zijn telt nooit mee.',
            'De backlog verzamelt gemelde problemen, bugs en verbeteringen, met status, prioriteit en versie van de oplossing; daar worden ook de acceptatietests versie na versie bewaard. Je abonnement staat bij ‘Voorkeuren’.',
        ],
    ],

    '0.14.0' => [
        'date' => '2026-10-08',
        'title' => 'Na de acceptatietest: ruilen goedgekeurd door de SL',
        'items' => [
            'Standaard keurt de SL ruilen tussen spelers goed: een voorwerp of kennis wisselt pas van eigenaar als de ruil is geaccepteerd. Met een vakje bij ‘Personages van de spelers’ kun je ruilen meteen toestaan.',
            'Afstandsbediening: bij het laatste item van de scène wordt ‘Volgende’ ‘Afronden’ en wordt het scherm leeggemaakt.',
            'Duidelijker logboek voor goedgekeurde voorwerpen, duidelijkere meldingen bij ‘Probleem melden’ en één manier van aanspreken per taal.',
        ],
    ],

    '0.13.1' => [
        'date' => '2026-10-08',
        'title' => 'Correcties uit de V1-acceptatietest',
        'items' => [
            'De zoekfunctie van de SL vindt nu ook geheimen, gegeven informatie en voorwerpen, gedeelde notities van spelers en scènetags.',
            'Een campagne dupliceren kopieert ook haar geheimen en voorbereide tijdlijn; een link naar een fiche tijdens de sessie opent in een zijpaneel, zonder de sessie te verlaten.',
            'Vertaalde foutpagina’s, wachtwoordmail in je eigen taal, Sessiemodus en Documenten leesbaar op je telefoon, een menu voor verborgen links op kleine schermen.',
            'Een rustend personage en een door de SL goedgekeurd voorwerp kunnen niet meer door de speler worden gewijzigd; de tijdelijke meetlat op de kaart verdwijnt vanzelf.',
        ],
    ],

    '0.13.0' => [
        'date' => '2026-10-08',
        'title' => 'Je eigen AI, zonder kopiëren en plakken',
        'items' => [
            'In ‘Voorkeuren’ kun je een API-sleutel op jouw naam opslaan bij Claude (Anthropic), ChatGPT (OpenAI) of Le Chat (Mistral). De AI-assistent biedt dan ‘Direct analyseren’ aan: de voorstellen komen zonder kopiëren en plakken.',
            'De aanbieder rekent de aanroepen af op jouw account. De sleutel wordt versleuteld, nooit opnieuw getoond of geëxporteerd, en de modus ‘tekst om te plakken’ blijft gratis.',
        ],
    ],

    '0.12.0' => [
        'date' => '2026-10-08',
        'title' => 'Een AI-assistent, zonder abonnement',
        'items' => [
            'Nieuw hulpmiddel ‘AI-assistent’ in de campagne: CodexFlow maakt een tekst met de sessienotities en de context van de campagne, om te plakken in de AI van je keuze. Het antwoord, hier teruggeplakt, wordt een reeks voorstellen: samenvatting, gespeelde gebeurtenissen, relaties, statussen, campagnenotities, onthullingen.',
            'Elk voorstel kun je accepteren, aanpassen of weigeren. Zonder jou verandert er niets in de campagne, en voorgestelde relaties of statussen gelden alleen voor de campagne, zonder de gedeelde wereld aan te raken.',
        ],
    ],

    '0.11.1' => [
        'date' => '2026-10-08',
        'title' => 'De demo in jouw taal',
        'items' => [
            'De demo-campagne bestaat in alle acht talen van de interface. Ze wordt in jouw taal geladen, of in de taal die je naast de knop kiest.',
        ],
    ],

    '0.11.0' => [
        'date' => '2026-10-08',
        'title' => 'Een demo-campagne',
        'items' => [
            'Een demo-campagne die je met één klik laadt vanuit ‘Mijn campagnes’: een verzonnen spel, ‘Brume & Serment’, en een volledige intrige over drie sessies, met fiches, portretten, relaties, een kaart, geheimen, regels, een tijdlijn en voorgemaakte personages.',
            'De kleine pictogramknoppen op de campagnepagina verspringen niet meer bij het aanwijzen: de naam verschijnt als tooltip, boven de rest.',
            'Tags vul je overal op dezelfde manier in, en een fiche biedt ‘Een tag toevoegen’ meteen onder de titel.',
        ],
    ],

    '0.10.0' => [
        'date' => '2026-10-07',
        'title' => 'Een overzichtelijkere campagnepagina',
        'items' => [
            'De campagnepagina opgeruimd: de Sessiemodus als banner, vier voorbereidingszones en de overige hulpmiddelen als kleine pictogramknoppen.',
            'Sfeer van het tafelscherm, te kiezen via de afstandsbediening: Nacht, Perkament, Leisteen of Grimoire.',
        ],
    ],

    '0.9.0' => [
        'date' => '2026-10-07',
        'title' => 'Je campagne meenemen',
        'items' => [
            'Een hele campagne exporteren als .zip-archief: spel, wereld, fiches, scenario’s, documenten, kaarten, geheimen, tijdlijn en bestanden.',
            'Een archief importeren via ‘Mijn campagnes’: het maakt de campagne opnieuw aan, bij jou of bij een andere SL.',
            'Deelbare spelsjablonen: fichetypes, velden, labels en regels, zonder campagne-inhoud.',
        ],
    ],

    '0.8.0' => [
        'date' => '2026-10-07',
        'title' => 'De graaf en de tijdlijn',
        'items' => [
            'Relatiegraaf: alle verbonden fiches, of het netwerk rond één fiche, met een diepte en een filter op type.',
            '‘Bekijken als’ in de graaf: het netwerk zoals een personage het kent. Spelers openen het vanaf hun personage.',
            'Tijdlijn: wereldgeschiedenis, geplande en gespeelde gebeurtenissen, met vrije datums zoals ‘Dag 3’.',
            'Een gespeelde gebeurtenis die tijdens de sessie wordt genoteerd, wordt gekoppeld aan de sessie en de huidige scène.',
        ],
    ],

    '0.7.0' => [
        'date' => '2026-10-07',
        'title' => 'Rond de tafel',
        'items' => [
            'Kaarten: een afbeelding op het tafelscherm die je zoomt en verschuift, met een optioneel vierkant raster en een schaal.',
            'Optionele pionnen, gekoppeld aan fiches (naam en portret): verplaatsen, van grootte veranderen, tonen aan of verbergen voor de spelers.',
            'Tijdelijke liniaal: trek een lijn en de afstand verschijnt in vakjes of in meters.',
            'Afstandsbediening: maak vanaf je telefoon het scherm leeg, ga naar het volgende element van de scène, bedien de kaart.',
            'Spelers: wat de SL je onthult of geeft, verschijnt meteen, zonder via de meldingen te gaan.',
        ],
    ],

    '0.6.0' => [
        'date' => '2026-10-07',
        'title' => 'Het geheugen van de campagne',
        'items' => [
            'Geheimen: een losse informatie, gekoppeld aan fiches, scènes of documenten, met één klik onthuld aan een personage of aan de hele tafel.',
            'Onthullingsgeschiedenis: wie wat te weten kwam, wanneer, in welke sessie en welke scène; elke onthulling kan ongedaan worden gemaakt.',
            '‘Bekijken als’: de SL ziet de campagne precies zoals een personage, alleen-lezen.',
            '‘Genoemd in’ toont nu ook de regels en sessienotities die een fiche noemen.',
            'Relaties: de omgekeerde richting (‘werkt voor’ / ‘heeft in dienst’) vult zichzelf in.',
        ],
    ],

    '0.5.0' => [
        'date' => '2026-10-07',
        'title' => 'Beter ordenen, samen',
        'items' => [
            'Een pagina ‘Tags’ om je tags te hernoemen, een kleur te geven, samen te voegen en te verwijderen; ook scènes hebben tags.',
            '‘Dupliceren’ van een fiche, een scenario of een hele campagne, om opnieuw te spelen met een andere tafel.',
            'Nieuwe rollen: co-SL, die samen met je voorbereidt en meeleidt, en toeschouwer, die naar het tafelscherm kijkt.',
            '‘Aan tafel tonen’ vanuit een fiche, een portret, een illustratie, een document of een regel.',
            'Sessiemodus: een fiche of regel met één klik tonen, de volgende scène zien, toets N om te noteren.',
        ],
    ],

    '0.4.1' => [
        'date' => '2026-10-07',
        'title' => 'Hulp en probleemmeldingen',
        'items' => [
            'Een pagina ‘Hulp’ beantwoordt de meest gestelde vragen, voor de SL en voor de spelers.',
            '‘Een probleem melden’, onderaan elke pagina, stuurt je bericht naar het team, samen met de betreffende pagina.',
        ],
    ],

    '0.4.0' => [
        'date' => '2026-10-07',
        'title' => 'Alle talen',
        'items' => [
            'De interface spreekt Frans, Engels, Duits, Spaans, Italiaans, Portugees, Nederlands en Pools.',
            'De taal volgt die van je browser; iedereen kan hem kiezen bij ‘Voorkeuren’.',
            'Meldingen komen aan in de taal van wie ze ontvangt.',
            'Het donkere thema blijft behouden van pagina tot pagina.',
            'Na het bewerken van een fiche vanaf de pagina Personages keer je daar meteen naar terug.',
        ],
    ],

    '0.3.0' => [
        'date' => '2026-10-07',
        'title' => 'De levende band',
        'items' => [
            'Berichten tussen de SL en de spelers, en een ‘Chat’-paneel altijd binnen handbereik (groep en privé).',
            'Meldingen: onthullingen, ontvangen voorwerpen, berichten, met een teller in de kopbalk.',
            'Alles wordt live bijgewerkt: berichten, tellers, onthullingen, zonder de pagina te herladen.',
            'CodexFlow is als app te installeren; de fiche van het personage blijft offline leesbaar; meldingen op het apparaat.',
            'Tafelscherm: kaarten, afbeeldingen, fiches en aankondigingen op de tv of de beamer, en gedeeld met de spelers als de SL dat wil.',
            'Personages geven elkaar voorwerpen en geven door wat ze weten.',
            'Spelers noteren hun kennis en voegen hun voorwerpen toe; de SL valideert de voorwerpen.',
            'Donker thema, accentkleur en tekstgrootte in ‘Voorkeuren’.',
        ],
    ],

    '0.2.0' => [
        'date' => '2026-10-07',
        'title' => 'De spelers',
        'items' => [
            'Spelers uitnodigen via een link.',
            'Personages van de spelers: fiche, pdf-blad, tellers (LP, magie, munitie…) en velden die de speler kan wijzigen.',
            'Onthullingen en ‘Geven’: kennis, bezittingen, documenten en regels.',
            'Spelersruimte: privé- of gedeelde notities, ‘Te spelen’-intenties, logboek van het personage.',
            'Wijzigingslogboek: wie, wat, wanneer, voor en na.',
        ],
    ],

    '0.1.0' => [
        'date' => '2026-10-06',
        'title' => 'De SL alleen',
        'items' => [
            'Werelden, campagnes en fiches met een openbare zone en een SL-zone, [[ ]]-links tussen fiches.',
            'Vrije velden per spel, fichetypes, import via CSV/JSON.',
            'Scenario\'s, scènes, regels en documentenbibliotheek.',
            'Sessie-modus: huidige scène, handige fiches, snelle notities, ‘Te spelen’ en vastgepinde fiches.',
            'Globaal zoeken.',
        ],
    ],

];
