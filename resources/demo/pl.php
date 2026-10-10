<?php

/*
 * Kampania demonstracyjna, tekst po polsku.
 *
 * Tu jest wyłącznie tekst: struktura (kto z kim jest powiązany, żetony, linki) znajduje się w
 * App\Actions\Demo\LoadDemoCampaign i jest taka sama we wszystkich językach. Każde tłumaczenie
 * zachowuje dokładnie te same klucze. W scenach „[[klucz]]” oznacza kartę o danym kluczu.
 */
return [
    'campaign' => [
        'name' => 'Przysięga Pierrecendre',
        'description' => 'Kampania demonstracyjna: trzy sesje w portowym mieście Pierrecendre, gdzie zapomniana przysięga wraca, by upomnieć się o swoje. Cała treść jest oryginalna i wolna od praw autorskich.',
    ],
    'game' => [
        'name' => 'Mgła i Przysięga',
        'description' => 'Gra o śledztwach i przysięgach, wymyślona na potrzeby demonstracji. Cztery cechy w skali od 1 do 5, przysięgi ważące na rzutach, żadnej zastrzeżonej mechaniki.',
    ],
    'world' => [
        'name' => 'Vehrmund',
        'description' => 'Archipelag zatopionych marchii i portów wzniesionych na popiele, gdzie dane słowo ma moc umowy.',
    ],

    'types' => [
        'faction' => 'Frakcja',
        'pregen' => 'Gotowa postać',
    ],

    'groups' => [
        'traits' => 'Cechy',
        'profile' => 'Profil',
        'secrets' => 'Sekrety',
    ],

    'fields' => [
        'body' => 'Ciało',
        'skill' => 'Zręczność',
        'mind' => 'Umysł',
        'heart' => 'Serce',
        'breath' => 'Tchnienie',
        'oaths' => 'Dotrzymane przysięgi',
        'trade' => 'Fach',
        'trait' => 'Znak szczególny',
        'ties' => 'Więzi',
        'hidden_oath' => 'Ukryta przysięga',
        'betrayal' => 'Co skłoniłoby go do zdrady',
    ],

    'tags' => [
        'city' => 'miasto',
        'act1' => 'akt 1',
        'act2' => 'akt 2',
        'act3' => 'akt 3',
        'intrigue' => 'intryga',
        'hall' => 'hala',
        'quays' => 'nabrzeża',
        'marshes' => 'marchie',
        'guard' => 'straż',
        'pregen' => 'gotowa postać',
        'base' => 'podstawy',
        'oaths' => 'przysięgi',
        'house' => 'domowe',
        'ambience' => 'nastrój',
    ],

    'quay_state' => [
        'status' => 'godzina policyjna',
        'notes' => 'Zamknięte nocą od czasu, gdy utonął Gueffroy.',
    ],

    'entities' => [
        'city' => [
            'name' => 'Pierrecendre',
            'summary' => 'Miasto portowe zbudowane na popielnym jęzorze wygasłego wulkanu.',
            'description' => "Piętnaście tysięcy dusz, dwa wzgórza i zatoka w kształcie półksiężyca. Żyje się tu z soli, szkła i przysiąg: każdą umowę zawartą w Hali ryje się na płytce z zeszklonego popiołu.\n\nMiasto pachnie wodorostami i zimną siarką. Górne ulice należą do domów kupieckich, dolne do tych, którzy żyją z wody.",
            'gm_notes' => 'Prawdziwa władza leży w Hali, nie w Straży. Jeśli gracze zagrożą Straży, Mornevent ustąpi; jeśli zagrożą Hali, całe miasto zamknie się przed nimi.',
        ],
        'hall' => [
            'name' => 'Hala Przysiąg',
            'summary' => 'Budowla z jasnego kamienia, w której ryje się i przechowuje przysięgi miasta.',
            'description' => 'Nawa bez boga, pełna regałów z zeszklonymi płytkami. Każda płytka nosi przysięgę, jej datę i świadków. Wchodzi się tu z odkrytą głową, wychodzi związanym.',
            'gm_notes' => 'Płytki z roku Wielkiej Mgły zostały usunięte. Elzevir wie, gdzie są: w piwnicy latarni, nie w Hali.',
        ],
        'quay' => [
            'name' => 'Nabrzeże Latarni',
            'summary' => 'Nabrzeże rybaków, przez całą noc oświetlone latarniami na tran.',
            'description' => 'Trzydzieści latarni zapalanych o zmierzchu przez chłopaka opłacanego tygodniówką. Gdy któraś zgaśnie, starzy wracają do domów, nie dopiwszy kufla.',
            'gm_notes' => 'Trzecia latarnia licząc od północy nigdy nie zostaje ponownie zapalona: to sygnał Przewoźnika.',
        ],
        'marshes' => [
            'name' => 'Zatopione Marchie',
            'summary' => 'Słone bagna oddzielające Pierrecendre od lądu, przejezdne w czasie odpływu.',
            'description' => 'Trzy godziny bezpiecznej drogi na jeden odpływ, w przeciwnym razie dwanaście godzin czekania. Bród znaczą wbite tyczki; ktoś je przestawia.',
            'gm_notes' => 'Tyczki przestawiają Krzywoprzysięzcy, by podróżni błądzili i przepadali bez śladu.',
        ],
        'lighthouse' => [
            'name' => 'Latarnia Orvent',
            'summary' => 'Opuszczona latarnia morska na południowym cyplu, której światło wciąż zapala się niektórymi nocami.',
            'description' => 'Trzydzieści dwa metry kamienia, kręte schody, piwnica zalewana w czasie przypływu.',
            'gm_notes' => 'Zaginione płytki z Hali leżą w piwnicy, w skrzyni na sól. Pilnuje ich Nieznajomy.',
        ],
        'ysane' => [
            'name' => 'Pani Ysane Korr',
            'summary' => 'Strażniczka przysiąg: ryje płytki i świadczy przy zawieraniu umów.',
            'description' => 'Sześćdziesiąt lat, dłonie poparzone przy piecu do szklenia, pamięć, której nikt nie śmie podważyć.',
            'gm_notes' => 'To ona kazała usunąć płytki z roku Wielkiej Mgły: na jednej z nich widnieje jej własne imię. Nie jest zła, jest przerażona.',
            'fields' => [
                'trade' => 'Strażniczka przysiąg',
                'trait' => 'Nigdy nie patrzy dwa razy w oczy tej samej osobie',
                'hidden_oath' => 'Trzydzieści lat temu przysięgła, że co roku pozwoli Topielcom zabrać jedną łódź. Od tamtej pory miasto nie zna rozbitków.',
                'betrayal' => 'Bezpieczeństwo wnuczki',
            ],
        ],
        'brannoc' => [
            'name' => 'Brannoc Przewoźnik',
            'summary' => 'Przeprowadza ludzi i skrzynie przez marchie, o porze, która jemu pasuje.',
            'description' => 'Wysoki, powolny, mówi mało, liczy szybko. Zna bród na pamięć, nawet przestawiony.',
            'gm_notes' => 'Wie, że tyczki się przesuwają. Będzie milczał, dopóki ktoś nie zaproponuje mu wykupienia długu u Szarej Nici.',
            'fields' => [
                'trade' => 'Przewoźnik',
                'trait' => 'Nigdy nie przysięga, co w mieście uchodzi za obelgę',
                'hidden_oath' => 'Jest winien Kompanii Szarej Nici jedenaście lat darmowych przepraw.',
                'betrayal' => 'Umorzenie długu',
            ],
        ],
        'elzevir' => [
            'name' => 'Mistrz Elzevir',
            'summary' => 'Archiwista Hali, który potrafi odczytać najstarsze płytki.',
            'description' => 'Niski, przyprószony popiołem, nie umie skłamać bez kaszlu.',
            'gm_notes' => 'Przepisał usunięte płytki, zanim je wyniesiono. Odpis trzyma w podszewce płaszcza.',
            'fields' => [
                'trade' => 'Archiwista',
                'trait' => 'Kaszle, kiedy kłamie',
                'hidden_oath' => 'Przysiągł Ysane, że nigdy nie wspomni o roku Wielkiej Mgły.',
                'betrayal' => 'Obietnica, że płytki wrócą na swoje miejsce',
            ],
        ],
        'vanne' => [
            'name' => 'Siostra Vanne',
            'summary' => 'Leczy topielców i poparzonych, nie pytając, po czyjej są stronie.',
            'description' => 'Prowadzi salę na sześć łóżek nad warsztatem powroźnika.',
            'gm_notes' => 'W zeszłym tygodniu opatrywała dwoje Krzywoprzysięzców. Powie o tym tylko w zamian za sól i bandaże.',
            'fields' => [
                'trade' => 'Uzdrowicielka',
                'trait' => 'Do każdego mówi „dziecko”',
            ],
        ],
        'mornevent' => [
            'name' => 'Kapitan Hald Mornevent',
            'summary' => 'Dowodzi Strażą Nabrzeży: dwudziestu dwóch ludzi i jedna łódź.',
            'description' => 'Kompetentny, zmęczony, doskonale świadomy, że nie ma środków na to, czego wymaga jego urząd.',
            'gm_notes' => 'Tuszuje zaginięcie trzech podróżnych, by nie siać paniki w mieście. Przyjmie pomoc, jeśli zaoferuje mu się ją bez świadków.',
            'fields' => [
                'trade' => 'Kapitan Straży',
                'trait' => 'Wszystko zapisuje w notesie, do którego nigdy nie zagląda',
                'hidden_oath' => 'Obiecał radzie, że pod jego dowództwem nikt nie zaginie.',
                'betrayal' => 'Zachowanie twarzy przed radą',
            ],
        ],
        'stranger' => [
            'name' => 'Nieznajomy z Latarni',
            'summary' => 'Ten, kto na nowo zapala światło Latarni Orvent. Nikt nie widział go z bliska.',
            'gm_notes' => 'To Gueffroy, chłopak od latarni, który utonął pół roku temu i został zwrócony przez Topielców. Strzeże płytek i czeka tylko na jedno: aż ktoś wypowie jego imię na głos.',
            'fields' => [
                'trade' => 'Latarnik',
                'trait' => 'Pachnie zimną solą',
                'hidden_oath' => 'Umierając, przysiągł zapalać latarnie, dopóki ktoś nie odda mu imienia.',
            ],
        ],
        'drowned' => [
            'name' => 'Topielcy',
            'summary' => 'To, co wypełza z marchii, gdy mgła utrzymuje się dłużej niż trzy dni.',
            'description' => 'Opisuje się ich jako sylwetki kroczące pod płytką wodą, wyprostowane, wzrostu człowieka.',
            'gm_notes' => 'Nie zabijają: upominają się. Topielec puszcza swoją zdobycz, jeśli ktoś w jej miejsce dotrzyma przysięgi, po którą przyszedł.',
        ],
        'seal' => [
            'name' => 'Pieczęć z Popiołu',
            'summary' => 'Stempel, którym ryje się płytki Hali. Bez niego żadna przysięga nie jest ważna.',
            'description' => 'Ciężki walec z czarnego szkła z wklęsłym herbem miasta.',
            'gm_notes' => 'Ysane ją ukryła. Ujawnienie jej kończy kampanię negocjacjami; zniszczenie – zerwaniem.',
        ],
        'greythread' => [
            'name' => 'Kompania Szarej Nici',
            'summary' => 'Dom kupiecki, który skupuje długi i odsprzedaje usługi.',
            'description' => 'Trzy kantory, ani jednego własnego statku i księga długów grubsza niż rejestr miasta.',
            'gm_notes' => 'Chce Pieczęci z Popiołu: kto ryje przysięgi, ten ustala cenę długów.',
        ],
        'broken' => [
            'name' => 'Krzywoprzysięzcy',
            'summary' => 'Ci, którzy złamali przysięgę i żyją teraz poza miastem, w marchiach.',
            'gm_notes' => 'Przestawiają tyczki, by miasto wreszcie zaczęło bać się wody. Przewodzi im córka Ysane.',
        ],
        'guard' => [
            'name' => 'Straż Nabrzeży',
            'summary' => 'Dwudziestu dwóch ludzi odpowiedzialnych za port, latarnie i godzinę policyjną.',
            'gm_notes' => 'Dwóch z nich bierze pieniądze od Szarej Nici. Mornevent o tym nie wie.',
        ],
        'teska' => [
            'name' => 'Teska Wioślarka',
            'summary' => 'Wiosłuje od dziecka, zna zatokę lepiej niż Straż.',
            'description' => 'Przysięgłaś bratu, że nigdy nie opuścisz Pierrecendre. On odszedł w zeszłym miesiącu.',
            'fields' => [
                'trade' => 'Wioślarka',
                'trait' => 'Mówi wszystko i od razu',
                'ties' => 'Brat, który odszedł bez słowa. Brannoc, który jest jej winien łódź.',
            ],
        ],
        'oriel' => [
            'name' => 'Oriel Chantegrèle',
            'summary' => 'Zawodowy świadek: płaci mu się za obecność przy przysięgach i za pamięć o nich.',
            'description' => 'Byłeś świadkiem dwustu przysiąg. Zapomniałeś tylko jedną – celowo.',
            'fields' => [
                'trade' => 'Świadek',
                'trait' => 'Powtarza półgłosem ważne zdania',
                'ties' => 'Mistrz Elzevir, u którego terminował. Szara Nić, która zbyt często go wynajmuje.',
            ],
        ],
        'dorn' => [
            'name' => 'Dorn Zimnożelazny',
            'summary' => 'Były strażnik, wydalony za odmowę egzekwowania godziny policyjnej.',
            'description' => 'Przysiągłeś, że już nigdy nie wykonasz rozkazu, którego nie rozumiesz.',
            'fields' => [
                'trade' => 'Wydalony strażnik',
                'trait' => 'Zawsze staje między drzwiami a resztą',
                'ties' => 'Mornevent, który wydalił go z żalem. Siostra Vanne, która dwa razy go zszywała.',
            ],
        ],
        'lisenn' => [
            'name' => 'Lisenn o Dwóch Imionach',
            'summary' => 'Pochodzi z marchii, w mieście żyje pod cudzym imieniem.',
            'description' => 'Złamałaś przysięgę. Nikt tutaj jeszcze o tym nie wie.',
            'fields' => [
                'trade' => 'Przewodniczka po marchiach',
                'trait' => 'Nigdy nie śpi dwie noce w tym samym miejscu',
                'ties' => 'Krzywoprzysięzcy, których porzuciła. Lisenn, zmarła, której imię nosi.',
            ],
        ],
    ],

    // Etykieta relacji, potem etykieta odwrotna (null: brak odwrotnej).
    'relations' => [
        'ysane_hall' => ['strzeże', 'strzeżona przez'],
        'elzevir_hall' => ['pracuje w', 'zatrudnia'],
        'elzevir_ysane' => ['przysiągł milczenie wobec', 'trzyma przysięgą'],
        'brannoc_marshes' => ['zna bród przez', 'przemierzane przez'],
        'brannoc_greythread' => ['jest dłużnikiem', 'trzyma dług'],
        'mornevent_guard' => ['dowodzi', 'dowodzona przez'],
        'guard_quay' => ['pilnuje', 'pilnowane przez'],
        'greythread_guard' => ['przekupiła dwóch ludzi z', null],
        'greythread_seal' => ['pożąda', 'pożądana przez'],
        'broken_marshes' => ['żyją w', 'dają schronienie'],
        'broken_ysane' => ['przewodzi im jej córka', null],
        'drowned_marshes' => ['wypełzają z', null],
        'drowned_ysane' => ['są związani przysięgą z', null],
        'stranger_lighthouse' => ['na nowo zapala', 'zapalana przez'],
        'stranger_quay' => ['zapalał latarnie na', null],
        'vanne_broken' => ['opatrzyła dwoje z nich', null],
        'hall_city' => ['wznosi się w', 'mieści'],
        'quay_city' => ['okala', 'wychodzi na'],
        'seal_hall' => ['ryje płytki dla', null],
        'teska_brannoc' => ['pożyczyła mu łódź', 'jest jej winien łódź'],
        'oriel_elzevir' => ['terminował u', 'wyszkolił'],
        'dorn_mornevent' => ['służył pod', 'wydalił'],
        'lisenn_broken' => ['porzuciła ich', null],
    ],

    'documents' => [
        'tile' => [
            'title' => 'Płytka 1147 – przysięga Wielkiej Mgły',
            'description' => 'Odpis, który Elzevir sporządził, zanim płytki opuściły Halę.',
            'lines' => [
                'Odpis płytki 1147, Hala Przysiąg w Pierrecendre.',
                '',
                'Przysięgająca: Ysane Korr, strażniczka.',
                'Przysięga: „Jedna łódź na rok, a zatoka pozostanie spokojna”.',
                'Świadkowie: Elzevir, archiwista. Gueffroy, latarnik.',
                '',
                'Uwaga archiwisty: płytka zdjęta z regału 3. dnia miesiąca soli.',
            ],
        ],
        'notice' => [
            'title' => 'Obwieszczenie o godzinie policyjnej',
            'description' => 'Przybite na Nabrzeżu Latarni. Pokaż je graczom już w pierwszej scenie.',
            'lines' => [
                'Na rozkaz kapitana Halda Morneventa, Straż Nabrzeży.',
                '',
                'Nabrzeże Latarni jest zamknięte od ostatniej latarni do świtu.',
                'Nikt nie wypływa w morze bez przepustki Straży.',
                'Każdą zgasłą latarnię należy zgłosić na posterunku.',
                '',
                'To obwieszczenie ma moc przysięgi: kto je złamie, odpowie przed Halą.',
            ],
        ],
        'tides' => [
            'title' => 'Tablica pływów Zatopionych Marchii',
            'description' => 'Pomoc do gry: trzy godziny brodu na każdy odpływ.',
            'lines' => [
                'Zatopione Marchie – przeprawa przez bród',
                '',
                'Odpływ: trzy godziny bezpiecznej drogi, tyczki widoczne.',
                'Przypływ narasta: godzina zwłoki, woda do połowy uda.',
                'Pełny przypływ: przeprawa niemożliwa. Dwanaście godzin czekania.',
                '',
                'Straż co miesiąc wbija tyczki na nowo.',
            ],
        ],
        'plan' => [
            'title' => 'Plan portu Pierrecendre',
            'description' => 'Port, jego nabrzeża i cypel z latarnią. Można pokazać przy stole.',
            'file' => 'plan-portu',
        ],
    ],

    'map' => [
        'name' => 'Port Pierrecendre',
        'unit' => 'm',
    ],

    'rules' => [
        'roll' => [
            'title' => 'Rzut przysięgi',
            'category' => 'Podstawy',
            'summary' => 'Cecha + 1k6 przeciwko trudności od 4 do 9.',
            'procedure' => "1. Zapowiedz, której cechy używasz i co postać chce osiągnąć.\n2. Rzuć 1k6 i dodaj cechę.\n3. 4 przy zadaniu w ramach fachu, 7 przy trudnym zadaniu, 9 przy niemożliwym.\n4. Jeśli postać działa, by dotrzymać przysięgi, dodaj +1 za każdą dotrzymaną przysięgę, maksymalnie +3.",
            'source' => 'Podręcznik podstawowy, s. 12',
        ],
        'breath' => [
            'title' => 'Tchnienie',
            'category' => 'Podstawy',
            'summary' => 'Tchnienie zastępuje punkty życia: wydaje się je, by wytrwać, nie by przyjmować ciosy.',
            'procedure' => "Wydaj 1 Tchnienie, by przerzucić kość, by iść dalej mimo rany albo by odeprzeć Topielca.\nPrzy 0 postać się zatrzymuje: nie umiera, ale do następnego odpoczynku nie może już niczego obiecać.",
            'source' => 'Podręcznik podstawowy, s. 18',
        ],
        'breaking' => [
            'title' => 'Złamanie przysięgi',
            'category' => 'Przysięgi',
            'summary' => 'Złamanie przysięgi daje natychmiastową korzyść i trwałą cenę.',
            'procedure' => "Gracz opisuje, co umożliwia mu złamanie przysięgi: dostaje to, bez rzutu.\nNastępnie traci wszystkie dotrzymane przysięgi, a stół zapisuje, kto się o tym dowiedział.",
            'gm_notes' => 'Nigdy nie odmawiaj złamania przysięgi. Cenę płaci się w fikcji, przez reakcje tych, którzy się o nim dowiedzą.',
            'source' => 'Podręcznik podstawowy, s. 24',
        ],
        'mist' => [
            'title' => 'Licznik mgły',
            'category' => 'Domowe',
            'summary' => 'Zasada domowa: mgła podnosi się o stopień z każdą sesją, aż Topielcy wejdą do miasta.',
            'procedure' => "Prowadź licznik od 0 do 6, widoczny dla całego stołu.\n+1 na koniec każdej sesji, +1 za każdym razem, gdy ktoś złamie przysięgę przy świadkach.\nPrzy 3 bród staje się niepewny. Przy 6 Topielcy wkraczają do Pierrecendre.",
            'gm_notes' => 'Licz za zasłonką MG, w tajemnicy aż do 3.',
        ],
        'word' => [
            'title' => 'Słowo dane przy stole',
            'category' => 'Domowe',
            'summary' => 'Do przetestowania: obietnica złożona na głos przez gracza liczy się jako przysięga.',
            'procedure' => 'Gdy gracz coś obiecuje postaci, zanotuj to. Jeśli dotrzyma słowa, +1 dotrzymana przysięga; jeśli nie, stosuje się zasady złamania przysięgi.',
            'gm_notes' => 'Przetestować na sesji 2. Ryzyko: gracze przestaną cokolwiek obiecywać.',
        ],
    ],

    'scenario' => [
        'name' => 'Przysięga Pierrecendre',
        'summary' => 'Trzy sesje: zgasła latarnia, kłamliwy bród i latarnia morska, która domaga się imienia.',
    ],

    'chapters' => [
        's1' => 'Sesja 1 – Zgasła latarnia',
        's2' => 'Sesja 2 – Bród, który kłamie',
        's3' => 'Sesja 3 – Oddane imię',
    ],

    // „notes”: notatka każdej karty w scenie, według klucza karty (brak: bez notatki).
    'scenes' => [
        'lantern' => [
            'name' => 'Trzecia latarnia',
            'description' => 'Zmierzch, [[quay]]. Trzecia latarnia licząc od północy nie chce się zapalić. Obwieszczenie o godzinie policyjnej jest wciąż świeże na murze.',
            'gm_notes' => 'To sygnał, który daje [[brannoc]]. Niech gracze sami to odkryją, obserwując, kto zbliża się do nabrzeża.',
            'notes' => ['brannoc' => 'przypływa od strony wody, bezszelestnie', 'guard' => 'dwóch ludzi na obchodzie'],
        ],
        'register' => [
            'name' => 'Odmowa wglądu w rejestr',
            'description' => '[[hall]]. [[ysane]] odmawia dostępu do regału z roku Wielkiej Mgły. [[elzevir]] kaszle.',
            'gm_notes' => 'Elzevir ustąpi, jeśli ktoś weźmie go na stronę, z dala od oczu Ysane. W przeciwnym razie zakaszle i zmieni temat.',
            'notes' => ['ysane' => 'za pulpitem', 'elzevir' => 'między regałami'],
        ],
        'poles' => [
            'name' => 'Przestawione tyczki',
            'description' => '[[marshes]], odpływ. Brakuje dwóch tyczek, a trzecią wbito krzywo. Mgła trzyma się od czterech dni.',
            'gm_notes' => 'Rzut na Umysł przeciw 7 pozwala przejrzeć podstęp. Przy porażce przypływ zaskakuje jednego z graczy: okazja, by wydać Tchnienie.',
            'notes' => ['brannoc' => 'wie, ale milczy', 'broken' => 'obserwują z daleka'],
        ],
        'notebook' => [
            'name' => 'Notes Morneventa',
            'description' => '[[mornevent]] niechętnie przyjmuje gości na posterunku, którego pilnuje [[guard]]. W jego notesie skreślono trzy nazwiska.',
            'gm_notes' => 'Mówi, jeśli nie ma świadków. Trzy nazwiska należą do podróżnych, którzy zaginęli przy brodzie.',
            'notes' => ['marshes' => 'wspomniane, nie odwiedzone'],
        ],
        'ward' => [
            'name' => 'Sala na sześć łóżek',
            'description' => 'Tu leczy [[vanne]]. Dwoje świeżo rannych pachnie solą. Za sól i bandaże zdradzi, co wie.',
            'notes' => ['broken' => 'dwoje z nich, opatrzonych w zeszłym tygodniu'],
        ],
        'cellar' => [
            'name' => 'Piwnica latarni',
            'description' => '[[lighthouse]]: piwnica, którą zalewa przypływ. W skrzyni na sól leżą płytki zabrane z Hali.',
            'gm_notes' => 'Płytka 1147 leży na wierzchu stosu, na widoku. [[stranger]] czeka, aż ktoś przeczyta ją na głos.',
            'notes' => ['stranger' => 'na szczycie schodów', 'seal' => 'nie ma jej w skrzyni'],
        ],
        'rising' => [
            'name' => 'To, co wypełza',
            'description' => '[[drowned]] kroczą przez zatokę, wyprostowani, wzrostu człowieka, prosto ku miastu – oto [[city]]. Licznik mgły wskazuje 6.',
            'gm_notes' => 'Zatrzymają się, jeśli ktoś w miejsce Ysane dotrzyma przysięgi z płytki 1147 albo jeśli imię Gueffroya padnie przy świadkach.',
            'notes' => ['ysane' => 'na nabrzeżu, bez swojej pieczęci'],
        ],
        'recast' => [
            'name' => 'Przysięga przekuta',
            'description' => '[[hall]], przed całym miastem. [[seal]] wraca do Hali – albo zostaje roztrzaskana.',
            'gm_notes' => 'Dwa zakończenia, żadne dobre. Zwrot: miasto przetrwa, Ysane upadnie. Zniszczenie: żadna przysięga już nikogo nie wiąże, a Szara Nić wykupuje wszystko.',
            'notes' => ['greythread' => 'obecna, czeka na swoją kolej'],
        ],
    ],

    'secrets' => [
        'ysane_oath' => [
            'title' => 'Ysane przysięgła Topielcom jedną łódź na rok',
            'body' => 'Trzydzieści lat temu Ysane Korr obiecała Topielcom jedną łódź rocznie w zamian za spokój zatoki. Płytka 1147 nosi treść tej przysięgi i jej imię.',
        ],
        'stranger' => [
            'title' => 'Nieznajomy z Latarni to Gueffroy, utopiony latarnik',
            'body' => 'Gueffroy był chłopakiem, któremu płacono za zapalanie latarni na nabrzeżu. Utonął pół roku temu, a Topielcy go zwrócili. Zapala światło latarni morskiej i czeka, aż ktoś wypowie jego imię.',
        ],
        'poles' => [
            'title' => 'Tyczki brodu są przestawiane celowo',
            'body' => 'Krzywoprzysięzcy przestawiają tyczki, by Pierrecendre zaczęło bać się własnej wody. Zaginęło tam już trzech podróżnych.',
        ],
        'daughter' => [
            'title' => 'Przywódczyni Krzywoprzysięzców jest córką Ysane',
            'body' => 'Ta, która przewodzi Krzywoprzysięzcom, jest córką strażniczki. To dla niej Ysane ukryła płytki i dla niej zdradziłaby miasto.',
        ],
        'bought' => [
            'title' => 'Szara Nić przekupiła dwóch strażników nabrzeża',
            'body' => 'Dwóch ludzi ze Straży Nabrzeży bierze pieniądze od Kompanii Szarej Nici. Mornevent o tym nie wie, a gdy się dowie, to go złamie.',
        ],
    ],

    // Dowolna data, tytuł, opis.
    'timeline' => [
        'ash' => ['300 lat temu', 'Popiół pokrywa zatokę', 'Erupcja góry Orvent gasi wulkan i daje miastu jego szarą ziemię.'],
        'first_oath' => ['180 lat temu', 'Pierwsza wyryta przysięga', 'Powstaje Hala, a pierwszą przysięgę zeszkla się na płytce z popiołu.'],
        'great_mist' => ['30 lat temu', 'Rok Wielkiej Mgły', 'Mgła trwająca osiem miesięcy, jedenaście straconych łodzi, a potem trzydzieści lat bez jednego rozbicia.'],
        'tile_1147' => ['30 lat temu', 'Przysięga z płytki 1147', 'Ysane Korr obiecuje Topielcom jedną łódź na rok. Dwoje świadków: Elzevir i Gueffroy.'],
        'drowning' => ['Pół roku temu', 'Gueffroy tonie przy nabrzeżu', 'Latarnik spada z Nabrzeża Latarni. Ciała nie odnaleziono.'],
        'missing' => ['W zeszłym miesiącu', 'Trzech podróżnych przepada przy brodzie', 'Mornevent skreśla trzy nazwiska w notesie i nie zawiadamia rady.'],
        'session1' => ['Sesja 1', 'Trzecia latarnia pozostaje ciemna', 'Postacie dostrzegają sygnał Przewoźnika i odmawia się im dostępu do regału z roku Wielkiej Mgły.'],
        'poles_moved' => ['Sesja 2', 'Tyczki zostają przestawione', 'Jeśli nikt nie zainterweniuje, w marchiach przepada czwarty podróżny.'],
        'invasion' => ['Sesja 3', 'Topielcy wkraczają do miasta', 'Gdy licznik mgły dojdzie do 6, wychodzą z zatoki i idą aż pod Halę.'],
        'ending' => ['Koniec', 'Pieczęć zwrócona lub rozbita', 'Zwrot Pieczęci obala Ysane; jej rozbicie uwalnia miasto od wszelkich przysiąg, a Szarą Nić od wszelkich ograniczeń.'],
    ],

    // Ambiances sonores de la bibliothèque.
    'sounds' => [
        'tide' => 'Odpływ na mokradłach',
        'mist' => 'Mgła nad Pierrecendre',
        'storm' => 'Burza nad latarnią',
    ],

    // Séance 1, déjà jouée : son résumé.
    'session' => [
        'summary' => '[[quay]]: postacie schodzą na ląd, a [[brannoc]] pokazuje im, że trzecia latarnia pozostaje zgaszona – to sygnał Przewoźnika, którego nikt nie zauważył. [[hall]]: [[elzevir]] odmawia otwarcia półki wielkiej mgły, a [[ysane]] dziękuje im trochę za szybko. Sesja kończy się nad wodą, podczas odpływu: [[marshes]].',
    ],
];
