<?php

/*
 * Demonstratiecampagne, Nederlandse tekst (vertaling van fr.php).
 *
 * Alleen de tekst staat hier: de structuur (wie met wie verbonden is, de tokens, de links) staat in
 * App\Actions\Demo\LoadDemoCampaign en is in alle talen gelijk. Elke vertaling gebruikt exact
 * dezelfde sleutels. In de scènes verwijst “[[sleutel]]” naar een fiche via haar sleutel.
 */
return [
    'campaign' => [
        'name' => 'De Eed van Pierrecendre',
        'description' => 'Demonstratiecampagne: drie sessies in de havenstad Pierrecendre, waar een vergeten eed terugkomt om te innen wat hem toekomt. Alle inhoud is origineel en rechtenvrij.',
    ],
    'game' => [
        'name' => 'Nevel & Eed',
        'description' => 'Een spel van onderzoek en eden, bedacht voor deze demonstratie. Vier eigenschappen van 1 tot 5, eden die meewegen bij elke worp, geen merkgebonden regels.',
    ],
    'world' => [
        'name' => 'Vehrmund',
        'description' => 'Een archipel van verdronken marken en havens gebouwd op as, waar een gegeven woord geldt als contract.',
    ],

    'types' => [
        'faction' => 'Factie',
        'pregen' => 'Kant-en-klaar personage',
    ],

    'groups' => [
        'traits' => 'Eigenschappen',
        'profile' => 'Profiel',
        'secrets' => 'Geheimen',
    ],

    'fields' => [
        'body' => 'Lijf',
        'skill' => 'Behendigheid',
        'mind' => 'Geest',
        'heart' => 'Hart',
        'breath' => 'Adem',
        'oaths' => 'Gehouden eden',
        'trade' => 'Beroep',
        'trait' => 'Kenmerk',
        'ties' => 'Banden',
        'hidden_oath' => 'Verborgen eed',
        'betrayal' => 'Waarvoor hij zou verraden',
    ],

    'tags' => [
        'city' => 'stad',
        'act1' => 'bedrijf 1',
        'act2' => 'bedrijf 2',
        'act3' => 'bedrijf 3',
        'intrigue' => 'intrige',
        'hall' => 'hal',
        'quays' => 'kades',
        'marshes' => 'marken',
        'guard' => 'wacht',
        'pregen' => 'kant-en-klaar',
        'base' => 'basis',
        'oaths' => 'eden',
        'house' => 'huisregel',
    ],

    'quay_state' => [
        'status' => 'onder avondklok',
        'notes' => '’s Nachts gesloten sinds Gueffroy verdronk.',
    ],

    'entities' => [
        'city' => [
            'name' => 'Pierrecendre',
            'summary' => 'Havenstad gebouwd op de asstroom van een uitgedoofde vulkaan.',
            'description' => "Vijftienduizend zielen, twee heuvels en een halvemaanvormige baai. Men leeft er van zout, glas en eden: elk contract dat in de Hal wordt gesloten, wordt gegraveerd in een tegel van verglaasde as.\n\nDe stad ruikt naar zeewier en koude zwavel. De hoge straten zijn van de handelshuizen, de lage straten van wie op het water werkt.",
            'gm_notes' => 'De echte macht ligt bij de Hal, niet bij de Wacht. Bedreigen de spelers de Wacht, dan geeft Mornevent toe; bedreigen ze de Hal, dan sluit de hele stad zich af.',
        ],
        'hall' => [
            'name' => 'De Hal van de Eden',
            'summary' => 'Gebouw van lichte steen waar de eden van de stad worden gegraveerd en bewaard.',
            'description' => 'Een schip zonder god, vol rekken met verglaasde tegels. Elke tegel draagt een eed, de dag en de getuigen. Je gaat er blootshoofds naar binnen en komt er gebonden weer uit.',
            'gm_notes' => 'De tegels uit het jaar van de grote nevel zijn weggehaald. Elzevir weet waar ze zijn: in de kelder van de vuurtoren, niet in de Hal.',
        ],
        'quay' => [
            'name' => 'De Lantaarnkade',
            'summary' => 'De visserskade, de hele nacht verlicht door lantaarns op visolie.',
            'description' => 'Dertig lantaarns, bij schemering aangestoken door een jongen die per week wordt betaald. Als er een uitgaat, gaan de ouden naar huis zonder hun glas leeg te drinken.',
            'gm_notes' => 'De derde lantaarn vanaf het noorden wordt nooit opnieuw aangestoken: dat is het signaal van de Veerman.',
        ],
        'marshes' => [
            'name' => 'De Verdronken Marken',
            'summary' => 'Zoutmoerassen tussen Pierrecendre en het vasteland, begaanbaar bij laagwater.',
            'description' => 'Drie uur veilige weg per getij, anders twaalf uur wachten. Ingeplante palen markeren de doorwaadplaats; iemand verzet ze.',
            'gm_notes' => 'De palen worden verzet door de Eedbrekers, zodat reizigers verdwalen en verdwijnen.',
        ],
        'lighthouse' => [
            'name' => 'De Vuurtoren van Orvent',
            'summary' => 'Verlaten vuurtoren op de zuidpunt, waarvan de lantaarn sommige nachten nog brandt.',
            'description' => 'Tweeëndertig meter steen, een wenteltrap, een kelder die bij hoogwater onderloopt.',
            'gm_notes' => 'De verdwenen tegels uit de Hal liggen in de kelder, in een zoutkist. De Onbekende bewaakt ze.',
        ],
        'ysane' => [
            'name' => 'Vrouwe Ysane Korr',
            'summary' => 'Hoedster van de eden: zij graveert de tegels en is getuige van de contracten.',
            'description' => 'Zestig jaar, handen verbrand door de glasoven, een geheugen dat niemand durft tegen te spreken.',
            'gm_notes' => 'Zij heeft de tegels uit het jaar van de grote nevel laten weghalen: haar eigen naam staat op een ervan. Ze is niet slecht, ze is doodsbang.',
            'fields' => [
                'trade' => 'Hoedster van de eden',
                'trait' => 'Kijkt niemand twee keer in de ogen',
                'hidden_oath' => 'Zwoer dertig jaar geleden de Verdronkenen elk jaar één boot te laten nemen. Sindsdien kent de stad geen schipbreuken meer.',
                'betrayal' => 'De veiligheid van haar kleindochter',
            ],
        ],
        'brannoc' => [
            'name' => 'Brannoc de Veerman',
            'summary' => 'Zet mensen en kisten over door de marken, op het uur dat hem uitkomt.',
            'description' => 'Groot, traag, zegt weinig en rekent snel. Kent de doorwaadplaats uit zijn hoofd, ook als die verzet is.',
            'gm_notes' => 'Hij weet dat de palen verschuiven. Hij zwijgt tot iemand aanbiedt zijn schuld bij de Grijze Draad af te kopen.',
            'fields' => [
                'trade' => 'Veerman',
                'trait' => 'Zweert nooit, wat in de stad als een belediging geldt',
                'hidden_oath' => 'Is de Compagnie van de Grijze Draad elf jaar gratis overtochten schuldig.',
                'betrayal' => 'Kwijtschelding van zijn schuld',
            ],
        ],
        'elzevir' => [
            'name' => 'Meester Elzevir',
            'summary' => 'Archivaris van de Hal, die de oudste tegels nog kan lezen.',
            'description' => 'Klein, bestoven met as, niet in staat te liegen zonder te kuchen.',
            'gm_notes' => 'Hij heeft de weggehaalde tegels overgeschreven voordat ze werden meegenomen. Zijn afschrift zit in de voering van zijn mantel.',
            'fields' => [
                'trade' => 'Archivaris',
                'trait' => 'Kucht als hij liegt',
                'hidden_oath' => 'Heeft Ysane gezworen nooit over het jaar van de grote nevel te spreken.',
                'betrayal' => 'De belofte dat de tegels op hun plaats worden teruggezet',
            ],
        ],
        'vanne' => [
            'name' => 'Zuster Vanne',
            'summary' => 'Verzorgt drenkelingen en brandwonden, zonder te vragen aan wiens kant ze staan.',
            'description' => 'Houdt een zaal met zes bedden boven een touwslagerij.',
            'gm_notes' => 'Ze heeft vorige week twee Eedbrekers verzorgd. Dat vertelt ze alleen in ruil voor zout en verband.',
            'fields' => [
                'trade' => 'Heelster',
                'trait' => 'Noemt iedereen “kleintje”',
            ],
        ],
        'mornevent' => [
            'name' => 'Kapitein Hald Mornevent',
            'summary' => 'Voert het bevel over de Kadewacht: tweeëntwintig man en één boot.',
            'description' => 'Bekwaam, moe, en zich er terdege van bewust dat hij de middelen voor zijn taak niet heeft.',
            'gm_notes' => 'Hij verzwijgt de verdwijning van drie reizigers om de stad niet in paniek te brengen. Hij aanvaardt hulp als die hem zonder publiek wordt aangeboden.',
            'fields' => [
                'trade' => 'Kapitein van de Wacht',
                'trait' => 'Schrijft alles op in een boekje dat hij nooit herleest',
                'hidden_oath' => 'Heeft de raad beloofd dat onder zijn bevel niemand zou verdwijnen.',
                'betrayal' => 'Zijn gezicht redden tegenover de raad',
            ],
        ],
        'stranger' => [
            'name' => 'De Onbekende van de Vuurtoren',
            'summary' => 'Hij die de lantaarn van de vuurtoren van Orvent weer aansteekt. Niemand heeft hem van dichtbij gezien.',
            'gm_notes' => 'Het is Gueffroy, de lantaarnjongen, zes maanden geleden verdronken en door de Verdronkenen teruggegeven. Hij bewaakt de tegels en wacht op één ding: dat iemand zijn naam hardop uitspreekt.',
            'fields' => [
                'trade' => 'Lantaarnaansteker',
                'trait' => 'Ruikt naar koud zout',
                'hidden_oath' => 'Zwoer stervend de lantaarns te blijven aansteken tot iemand hem zijn naam teruggeeft.',
            ],
        ],
        'drowned' => [
            'name' => 'De Verdronkenen',
            'summary' => 'Wat uit de marken opstijgt als de nevel langer dan drie dagen blijft hangen.',
            'description' => 'Men beschrijft ze als gestalten die onder het ondiepe water lopen, op manshoogte.',
            'gm_notes' => 'Ze doden niet: ze eisen op. Een Verdronkene laat zijn prooi los als iemand in diens plaats de eed nakomt waarvoor hij kwam.',
        ],
        'seal' => [
            'name' => 'Het Aszegel',
            'summary' => 'De stempel waarmee de tegels van de Hal worden gegraveerd. Zonder hem is geen enkele eed geldig.',
            'description' => 'Een zware cilinder van zwart glas, met het wapen van de stad erin uitgesneden.',
            'gm_notes' => 'Ysane heeft het verstopt. Het openbaar maken sluit de campagne af met onderhandeling; het vernietigen sluit haar af met een breuk.',
        ],
        'greythread' => [
            'name' => 'De Compagnie van de Grijze Draad',
            'summary' => 'Handelshuis dat schulden opkoopt en diensten doorverkoopt.',
            'description' => 'Drie kantoren, geen eigen schip, en een schuldenboek dikker dan het register van de stad.',
            'gm_notes' => 'Wil het Aszegel: wie de eden graveert, bepaalt de prijs van de schulden.',
        ],
        'broken' => [
            'name' => 'De Eedbrekers',
            'summary' => 'Wie een eed heeft gebroken en nu buiten de stad leeft, in de marken.',
            'gm_notes' => 'Ze verzetten de palen zodat de stad eindelijk bang wordt voor het water. Hun leidster is de dochter van Ysane.',
        ],
        'guard' => [
            'name' => 'De Kadewacht',
            'summary' => 'Tweeëntwintig man, belast met de haven, de lantaarns en de avondklok.',
            'gm_notes' => 'Twee van hen worden betaald door de Grijze Draad. Mornevent weet het niet.',
        ],
        'teska' => [
            'name' => 'Teska de Roeister',
            'summary' => 'Roeit al sinds haar kindertijd en kent de baai beter dan de Wacht.',
            'description' => 'Je hebt je broer gezworen Pierrecendre nooit te verlaten. Vorige maand is hij zelf vertrokken.',
            'fields' => [
                'trade' => 'Roeister',
                'trait' => 'Zegt alles, en meteen',
                'ties' => 'Haar broer, vertrokken zonder een woord. Brannoc, die haar een boot schuldig is.',
            ],
        ],
        'oriel' => [
            'name' => 'Oriel Chantegrèle',
            'summary' => 'Beroepsgetuige: hij wordt betaald om eden bij te wonen en ze te onthouden.',
            'description' => 'Je bent getuige geweest van tweehonderd eden. Je bent er één vergeten, met opzet.',
            'fields' => [
                'trade' => 'Getuige',
                'trait' => 'Herhaalt belangrijke zinnen zachtjes voor zich uit',
                'ties' => 'Meester Elzevir, bij wie hij in de leer ging. De Grijze Draad, die hem te vaak inhuurt.',
            ],
        ],
        'dorn' => [
            'name' => 'Dorn Koudijzer',
            'summary' => 'Oud-wachter, ontslagen omdat hij weigerde een avondklok te handhaven.',
            'description' => 'Je hebt gezworen nooit meer een bevel te gehoorzamen dat je niet begrijpt.',
            'fields' => [
                'trade' => 'Ontslagen wachter',
                'trait' => 'Gaat altijd tussen de deur en de anderen staan',
                'ties' => 'Mornevent, die hem met spijt ontsloeg. Zuster Vanne, die hem twee keer heeft dichtgenaaid.',
            ],
        ],
        'lisenn' => [
            'name' => 'Lisenn met de Twee Namen',
            'summary' => 'Komt uit de marken en leeft in de stad onder een naam die niet de hare is.',
            'description' => 'Je hebt een eed gebroken. Niemand hier weet het nog.',
            'fields' => [
                'trade' => 'Gids door de marken',
                'trait' => 'Slaapt nooit twee nachten op dezelfde plek',
                'ties' => 'De Eedbrekers, die ze heeft verlaten. Lisenn, de dode wier naam ze draagt.',
            ],
        ],
    ],

    // Label van de relatie, dan het omgekeerde label (null: geen omgekeerde).
    'relations' => [
        'ysane_hall' => ['bewaakt', 'bewaakt door'],
        'elzevir_hall' => ['werkt in', 'heeft in dienst'],
        'elzevir_ysane' => ['heeft zwijgen gezworen aan', 'houdt gebonden met een eed'],
        'brannoc_marshes' => ['kent de doorwaadplaats van', 'doorkruist door'],
        'brannoc_greythread' => ['staat in de schuld bij', 'houdt de schuld van'],
        'mornevent_guard' => ['voert het bevel over', 'onder bevel van'],
        'guard_quay' => ['bewaakt', 'bewaakt door'],
        'greythread_guard' => ['koopt twee mannen van', null],
        'greythread_seal' => ['begeert', 'begeerd door'],
        'broken_marshes' => ['leven in', 'bieden onderdak aan'],
        'broken_ysane' => ['worden geleid door haar dochter', null],
        'drowned_marshes' => ['stijgen op uit', null],
        'drowned_ysane' => ['hebben een eed met', null],
        'stranger_lighthouse' => ['steekt weer aan', 'weer aangestoken door'],
        'stranger_quay' => ['stak de lantaarns aan van', null],
        'vanne_broken' => ['heeft er twee verzorgd', null],
        'hall_city' => ['staat in', 'herbergt'],
        'quay_city' => ['grenst aan', 'komt uit op'],
        'seal_hall' => ['graveert de tegels van', null],
        'teska_brannoc' => ['heeft hem een boot geleend', 'is haar een boot schuldig'],
        'oriel_elzevir' => ['was zijn leerling', 'heeft opgeleid'],
        'dorn_mornevent' => ['diende onder', 'heeft ontslagen'],
        'lisenn_broken' => ['heeft hen verlaten', null],
    ],

    'documents' => [
        'tile' => [
            'title' => 'Tegel 1147 — eed van de grote nevel',
            'description' => 'Het afschrift dat Elzevir maakte voordat de tegels de Hal verlieten.',
            'lines' => [
                'Afschrift van tegel 1147, Hal van de Eden van Pierrecendre.',
                '',
                'Zweerster: Ysane Korr, hoedster.',
                'Eed: “Eén boot per jaar, en de baai blijft kalm.”',
                'Getuigen: Elzevir, archivaris. Gueffroy, lantaarnaansteker.',
                '',
                'Notitie van de archivaris: tegel uit het rek gehaald op de 3e van de zoutmaand.',
            ],
        ],
        'notice' => [
            'title' => 'Bekendmaking avondklok',
            'description' => 'Aangeplakt op de Lantaarnkade. Laat dit vanaf de eerste scène aan de spelers zien.',
            'lines' => [
                'Op bevel van kapitein Hald Mornevent, Kadewacht.',
                '',
                'De Lantaarnkade is gesloten van de laatste lantaarn tot het ochtendgloren.',
                'Niemand vaart uit zonder een briefje van de Wacht.',
                'Elke gedoofde lantaarn moet bij de wachtpost worden gemeld.',
                '',
                'Deze bekendmaking geldt als eed: wie haar overtreedt, staat terecht voor de Hal.',
            ],
        ],
        'tides' => [
            'title' => 'Getijdentabel van de Verdronken Marken',
            'description' => 'Spelhulp: drie uur doorwaadbaar bij laagwater.',
            'lines' => [
                'Verdronken Marken — oversteek van de doorwaadplaats',
                '',
                'Laagwater: drie uur veilige weg, palen zichtbaar.',
                'Opkomend water: één uur uitstel, water tot halverwege de dij.',
                'Hoogwater: geen doorgang. Twaalf uur wachten.',
                '',
                'De palen worden elke maand door de Wacht opnieuw geplaatst.',
            ],
        ],
        'plan' => [
            'title' => 'Plattegrond van de haven van Pierrecendre',
            'description' => 'De haven, de kades en de vuurtorenpunt. Kan aan tafel worden getoond.',
            'file' => 'plattegrond-van-de-haven',
        ],
    ],

    'map' => [
        'name' => 'De haven van Pierrecendre',
        'unit' => 'm',
    ],

    'rules' => [
        'roll' => [
            'title' => 'Eedworp',
            'category' => 'Basis',
            'summary' => 'Eigenschap + 1d6 tegen een moeilijkheid van 4 tot 9.',
            'procedure' => "1. Noem de eigenschap die je gebruikt en wat het personage wil bereiken.\n2. Gooi 1d6 en tel de eigenschap erbij op.\n3. 4 voor een vakkundige taak, 7 voor een moeilijke taak, 9 voor het onmogelijke.\n4. Handelt het personage om een eed na te komen, tel dan +1 op per gehouden eed, tot +3.",
            'source' => 'Basisboekje, p. 12',
        ],
        'breath' => [
            'title' => 'Adem',
            'category' => 'Basis',
            'summary' => 'Adem vervangt levenspunten: je geeft hem uit om vol te houden, niet om klappen op te vangen.',
            'procedure' => "Geef 1 Adem uit om een dobbelsteen opnieuw te gooien, om ondanks een verwonding door te gaan, of om een Verdronkene te weerstaan.\nBij 0 houdt het personage op: het is niet dood, maar kan tot de volgende rustpauze niets meer beloven.",
            'source' => 'Basisboekje, p. 18',
        ],
        'breaking' => [
            'title' => 'Een eed breken',
            'category' => 'Eden',
            'summary' => 'Een eed breken levert direct voordeel op en een blijvende prijs.',
            'procedure' => "De speler beschrijft wat de breuk hem oplevert: hij krijgt het, zonder worp.\nDaarna verliest hij al zijn gehouden eden, en de tafel noteert wie het te weten kwam.",
            'gm_notes' => 'Weiger nooit een breuk. De prijs wordt in de fictie betaald, via de reactie van wie het te weten komt.',
            'source' => 'Basisboekje, p. 24',
        ],
        'mist' => [
            'title' => 'Nevelteller',
            'category' => 'Huisregel',
            'summary' => 'Huisregel: de nevel stijgt elke sessie een stap, tot de Verdronkenen door de stad lopen.',
            'procedure' => "Houd een teller bij van 0 tot 6, zichtbaar voor de tafel.\n+1 aan het eind van elke sessie, +1 telkens wanneer een eed in het bijzijn van getuigen wordt gebroken.\nBij 3 wordt de doorwaadplaats onbetrouwbaar. Bij 6 trekken de Verdronkenen Pierrecendre binnen.",
            'gm_notes' => 'Teller achter het spelleiderscherm, geheim tot 3.',
        ],
        'word' => [
            'title' => 'Aan tafel gegeven woord',
            'category' => 'Huisregel',
            'summary' => 'Uit te proberen: een belofte die de speler hardop doet, telt als eed.',
            'procedure' => 'Als een speler een personage iets belooft, noteer het. Houdt hij zich eraan, +1 gehouden eed; zo niet, dan geldt de breuk.',
            'gm_notes' => 'Uitproberen in sessie 2. Risico: de spelers durven niets meer te beloven.',
        ],
    ],

    'scenario' => [
        'name' => 'De Eed van Pierrecendre',
        'summary' => 'Drie sessies: een gedoofde lantaarn, een doorwaadplaats die liegt, een vuurtoren die een naam opeist.',
    ],

    'chapters' => [
        's1' => 'Sessie 1 — De gedoofde lantaarn',
        's2' => 'Sessie 2 — De doorwaadplaats die liegt',
        's3' => 'Sessie 3 — De teruggegeven naam',
    ],

    // “notes”: de notitie van elke fiche in de scène, per fichesleutel (ontbreekt: geen notitie).
    'scenes' => [
        'lantern' => [
            'name' => 'De derde lantaarn',
            'description' => 'Bij schemering, op [[quay]], weigert de derde lantaarn vanaf het noorden aan te gaan. De bekendmaking van de avondklok is nog vers op de muur.',
            'gm_notes' => 'Het is het signaal van [[brannoc]]. Laat de spelers het ontdekken door te kijken wie de kade nadert.',
            'notes' => ['brannoc' => 'komt geruisloos over het water', 'guard' => 'twee man op ronde'],
        ],
        'register' => [
            'name' => 'Het geweigerde register',
            'description' => 'In [[hall]] weigert [[ysane]] de toegang tot het rek van het jaar van de grote nevel. [[elzevir]] kucht.',
            'gm_notes' => 'Elzevir zwicht als je hem apart neemt, buiten het zicht van Ysane. Anders kucht hij en begint over iets anders.',
            'notes' => ['ysane' => 'achter de lessenaar', 'elzevir' => 'tussen de rekken'],
        ],
        'poles' => [
            'name' => 'De verzette palen',
            'description' => 'In [[marshes]] ontbreken bij laagwater twee palen en is een derde scheef herplant. De nevel hangt al vier dagen.',
            'gm_notes' => 'Een Geest-worp tegen 7 doorziet het bedrog. Bij mislukking komt het water op rond een speler: een kans om Adem uit te geven.',
            'notes' => ['brannoc' => 'weet het, en zwijgt', 'broken' => 'kijken van een afstand toe'],
        ],
        'notebook' => [
            'name' => 'Het boekje van Mornevent',
            'description' => '[[mornevent]] ontvangt, met tegenzin, in de wachtpost van [[guard]]. In zijn boekje zijn drie namen doorgestreept.',
            'gm_notes' => 'Hij praat als er geen getuigen zijn. De drie namen zijn die van de reizigers die bij de doorwaadplaats verdwenen.',
            'notes' => ['marshes' => 'ter sprake gebracht, niet bezocht'],
        ],
        'ward' => [
            'name' => 'De zaal met zes bedden',
            'description' => 'Bij [[vanne]] ruiken twee recent gewonden naar zout. Ze ruilt wat ze weet tegen zout en verband.',
            'notes' => ['broken' => 'twee van hen, vorige week verzorgd'],
        ],
        'cellar' => [
            'name' => 'De kelder van de vuurtoren',
            'description' => 'De kelder van [[lighthouse]] loopt onder bij hoogwater. In een zoutkist: de tegels die uit de Hal zijn weggehaald.',
            'gm_notes' => 'Tegel 1147 ligt bovenop de stapel, goed zichtbaar. [[stranger]] wacht tot iemand hem hardop voorleest.',
            'notes' => ['stranger' => 'boven aan de trap', 'seal' => 'niet in de kist'],
        ],
        'rising' => [
            'name' => 'Wat opstijgt',
            'description' => '[[drowned]] lopen door de baai, op manshoogte, recht op [[city]] af. De nevelteller staat op 6.',
            'gm_notes' => 'Ze houden halt als iemand in Ysanes plaats de eed van tegel 1147 nakomt, of als de naam van Gueffroy in het bijzijn van getuigen wordt uitgesproken.',
            'notes' => ['ysane' => 'op de kade, zonder haar zegel'],
        ],
        'recast' => [
            'name' => 'De hergoten eed',
            'description' => 'In [[hall]], ten overstaan van de stad: [[seal]] teruggeven aan de Hal, of het breken.',
            'gm_notes' => 'Twee eindes, geen enkel goed. Teruggeven: de stad houdt stand, Ysane valt. Breken: geen enkele eed bindt nog iemand, en de Grijze Draad koopt alles op.',
            'notes' => ['greythread' => 'aanwezig, wacht haar beurt af'],
        ],
    ],

    'secrets' => [
        'ysane_oath' => [
            'title' => 'Ysane zwoer de Verdronkenen één boot per jaar',
            'body' => 'Dertig jaar geleden beloofde Ysane Korr de Verdronkenen één boot per jaar, opdat de baai kalm zou blijven. Tegel 1147 draagt de tekst, en haar naam.',
        ],
        'stranger' => [
            'title' => 'De Onbekende van de Vuurtoren is Gueffroy, de verdronken lantaarnaansteker',
            'body' => 'Gueffroy was het kind dat betaald werd om de lantaarns op de kade aan te steken. Zes maanden geleden verdronken, door de Verdronkenen teruggegeven. Hij steekt de vuurtoren weer aan en wacht tot iemand zijn naam uitspreekt.',
        ],
        'poles' => [
            'title' => 'De palen bij de doorwaadplaats worden met opzet verzet',
            'body' => 'De Eedbrekers verzetten de palen zodat Pierrecendre bang wordt voor zijn eigen water. Er zijn al drie reizigers verdwenen.',
        ],
        'daughter' => [
            'title' => 'De leidster van de Eedbrekers is de dochter van Ysane',
            'body' => 'Zij die de Eedbrekers leidt, is de dochter van de hoedster. Voor haar heeft Ysane de tegels verstopt, en voor haar zou ze de stad verraden.',
        ],
        'bought' => [
            'title' => 'De Grijze Draad heeft twee kadewachters omgekocht',
            'body' => 'Twee mannen van de Kadewacht worden betaald door de Compagnie van de Grijze Draad. Mornevent weet het niet, en de ontdekking zal hem breken.',
        ],
    ],

    // Vrije datum, titel, beschrijving.
    'timeline' => [
        'ash' => ['300 jaar geleden', 'De as bedekt de baai', 'De uitbarsting van de berg Orvent dooft de vulkaan en geeft de stad haar grijze bodem.'],
        'first_oath' => ['180 jaar geleden', 'Eerste gegraveerde eed', 'De Hal wordt gebouwd en de eerste eed wordt verglaasd in een tegel van as.'],
        'great_mist' => ['30 jaar geleden', 'Het jaar van de grote nevel', 'Acht maanden nevel, elf boten verloren, en daarna dertig jaar lang geen enkele schipbreuk.'],
        'tile_1147' => ['30 jaar geleden', 'De eed van tegel 1147', 'Ysane Korr belooft de Verdronkenen één boot per jaar. Twee getuigen: Elzevir en Gueffroy.'],
        'drowning' => ['Zes maanden geleden', 'Gueffroy verdrinkt bij de kade', 'De lantaarnaansteker valt van de Lantaarnkade. Zijn lichaam wordt nooit gevonden.'],
        'missing' => ['Vorige maand', 'Drie reizigers vermist bij de doorwaadplaats', 'Mornevent streept drie namen door in zijn boekje en licht de raad niet in.'],
        'session1' => ['Sessie 1', 'De derde lantaarn blijft gedoofd', 'De personages ontdekken het signaal van de Veerman en krijgen geen toegang tot het rek van de grote nevel.'],
        'poles_moved' => ['Sessie 2', 'De palen worden verzet', 'Als niemand ingrijpt, verdwijnt er een vierde reiziger in de marken.'],
        'invasion' => ['Sessie 3', 'De Verdronkenen trekken de stad binnen', 'Bij nevelteller 6 komen ze de baai op en lopen ze tot aan de Hal.'],
        'ending' => ['Einde', 'Het zegel teruggegeven of gebroken', 'Het Zegel teruggeven doet Ysane vallen; het breken bevrijdt de stad van elke eed, en de Grijze Draad van elke grens.'],
    ],
];
