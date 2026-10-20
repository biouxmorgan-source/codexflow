<?php

// Nowości w poszczególnych wersjach, od najnowszej do najstarszej. Wyświetlane w
// „Co nowego” (raz po każdej aktualizacji) oraz na stronie o tej samej nazwie.
return [

    '0.87.1' => [
        'date' => '2026-10-20',
        'title' => 'Czytelne nazwy punktów na mapie',
        'items' => [
            'W ciemnym motywie nazwy punktów na mapie kampanii były ciemne na czarnym tle: znów są białe, podobnie jak paski inicjatywy i kości na ekranie stołu oraz przyciski stron w plikach PDF.',
        ],
    ],

    '0.87.0' => [
        'date' => '2026-10-20',
        'title' => 'Zapisane rzuty dla każdej karty',
        'items' => [
            'W kościach „Moich kart” opcja „Zapisz ten rzut” zachowuje rzut przy karcie: każda postać ma własne rzuty (Kula ognia, Atak…), wczytywane jednym dotknięciem, aktualizowane i usuwane przez ×.',
        ],
    ],

    '0.86.2' => [
        'date' => '2026-10-20',
        'title' => '„Zastąp plik” działa',
        'items' => [
            'Przycisk „Zastąp” pozostawał wyszarzony po wybraniu nowego pliku: znów można go kliknąć, gdy plik zostanie wysłany.',
        ],
    ],

    '0.86.1' => [
        'date' => '2026-10-20',
        'title' => 'Zastąpienie mapy obrazem o innym rozmiarze',
        'items' => [
            'Gdy dokument służy jako mapa, „Zastąp plik” pokazuje rozmiar obecnego obrazu i przyjmuje obraz o innym rozmiarze. Pionki, szablony i punkty zachowują pozycję w pikselach: ostrzeżenie informuje, że przesuną się na rysunku.',
        ],
    ],

    '0.86.0' => [
        'date' => '2026-10-20',
        'title' => 'Zmiana dokumentu bez usuwania',
        'items' => [
            'Na stronie dokumentu „Zastąp plik” wstawia nową wersję w miejsce starej: dokument zachowuje tytuł, ustawienia i powiązania, a nowa wersja od razu pojawia się w scenach, kartach, zasadach, mapach, wskazówkach i u postaci, które go otrzymały.',
            'Obraz służący jako mapa zastępuje się innym obrazem; pionki, siatka i punkty zostają na miejscu.',
        ],
    ],

    '0.85.0' => [
        'date' => '2026-10-20',
        'title' => 'Rzuty kośćmi z karty',
        'items' => [
            'Na karcie postaci mała kość obok każdej liczby i każdego licznika rzuca kośćmi z tą wartością: „Rzuć poniżej… (próg)” albo „Dodaj… do rzutu (premia)”. Taca z kośćmi otwiera się gotowa do rzutu, a wynik trafia do stołu jak zwykle.',
            'Na karcie własnej postaci wartość pozostaje powiązana z kartą: gdy się zmieni, zapisany rzut za nią podąża. MG również może rzucać z karty gracza.',
            '„Moje karty” mają własne kości: ta sama mała kość obok wartości liczbowych i przycisk „Kości” na dole karty. Wynik pojawia się na karcie, widoczny tylko dla ciebie.',
        ],
    ],

    '0.84.2' => [
        'date' => '2026-10-20',
        'title' => 'Nowe mapy w demie',
        'items' => [
            'Demo ma nowe ilustrowane mapy: nadmorską wioskę jako mapę kampanii i podziemne nabrzeże latarni jako podmapę. Wczytaj je ponownie z „Moje kampanie”, aby je zobaczyć.',
        ],
    ],

    '0.84.1' => [
        'date' => '2026-10-19',
        'title' => 'Więcej miejsca w Moich kartach',
        'items' => [
            'Karta mieści teraz do 200 bloków, 500 pól i 30 zakładek. „+ Nowa notatka” nie prowadzi już do strony błędu: przy limicie wyjaśnia to komunikat.',
        ],
    ],

    '0.84.0' => [
        'date' => '2026-10-19',
        'title' => 'Zakładki w Moich kartach',
        'items' => [
            'W „Edytuj” przycisk „+ Nowa zakładka” dzieli kartę na zakładki, które nazywasz (Postać, Czary, Ekwipunek…), układasz i usuwasz.',
            'Każdy blok wybiera swoją zakładkę, a każda zakładka układa swoje bloki na własnych trzech kolumnach. Istniejąca karta zachowuje wszystkie bloki w pierwszej zakładce.',
        ],
    ],

    '0.83.0' => [
        'date' => '2026-10-19',
        'title' => 'Wiele notatek i trzy kolumny w Moich kartach',
        'items' => [
            '„+ Nowa notatka” dodaje blok notatek: tyle, ile chcesz (długi, plotki, kontakty…). Twoje dotychczasowe notatki stają się pierwszym blokiem „Notatki”.',
            'Bloki układasz teraz w trzech kolumnach, razem z notatkami, przez „Uporządkuj bloki”.',
        ],
    ],

    '0.82.0' => [
        'date' => '2026-10-19',
        'title' => 'Aktualne demo',
        'items' => [
            'Kampania demonstracyjna pokazuje teraz najnowsze funkcje: na mapie kampanii latarnia prowadzi do podmapy, swojej piwnicy, gotowej do walki z żetonami, dwoma szablonami obszaru i kolejnością inicjatywy. Każdy punkt łączy kilka kart i kilka scen.',
            'Kości przy stole: przygotowane rzuty, które odczytują cechę z karty, nazwana premia i rzuty z sesji 1, w tym prośba MG.',
            'Także: dźwięk z „Moich dźwięków”, scena „Rozegrana zgodnie z planem” i druga z „Co się wydarzyło”, poszlaka „Kłamstwo”, karta i zasada domowa z ich oficjalną wersją oraz gracze, którzy sami uzupełniają swój portret.',
            'Wczytaj demo ponownie z „Moje kampanie”, aby je poznać: już wczytana kopia pozostaje bez zmian.',
        ],
    ],

    '0.81.0' => [
        'date' => '2026-10-19',
        'title' => 'Mapa kampanii: zoom, stół i bogatsze miejsca',
        'items' => [
            'Mapę kampanii można przybliżać (kółko, + i −, gest szczypania na tablecie) i przesuwać przeciąganiem. Rada: obraz 4K (3840 × 2160) pozostaje ostry.',
            'Gdy mapa jest na ekranie stołu, otwarta z punktu mapa szczegółowa zastępuje ją tam, a mapa nadrzędna wraca, gdy cofasz się w górę. Na mapie szczegółowej „To miejsce” trzyma sceny i karty punktu o jedno kliknięcie.',
            'Punkt można teraz powiązać z wieloma scenami i wieloma kartami: miejsce, jego BN, wszystkie sceny, które się tam rozgrywają.',
        ],
    ],

    '0.80.0' => [
        'date' => '2026-10-19',
        'title' => 'Bloki, notatki i pola wyboru w Moich kartach',
        'items' => [
            'W „Moich kartach” pola układają się w bloki, którym nadajesz nazwy: Cechy, Umiejętności, Ekwipunek, Czary…',
            '„Uporządkuj bloki”: przeciągnij blok za uchwyt lub użyj strzałek, aby umieścić go, gdzie chcesz, w jednej lub dwóch kolumnach.',
            'Każde pole może mieć notatkę i pole wyboru do zaznaczania podczas gry: udany test umiejętności, przygotowany czar.',
        ],
    ],

    '0.79.0' => [
        'date' => '2026-10-19',
        'title' => 'Biblioteka dem',
        'items' => [
            'Nowa „Biblioteka dem” w „Moich kampaniach”: kampanie gotowe do gry (świat, krótki scenariusz, gotowe postacie), których własną kopię wczytujesz jednym kliknięciem.',
            'Każde demo autora lub wydawcy ma link „Kup grę”, w bibliotece i na górze twojej kopii.',
            'Cena Premium widnieje teraz na przycisku subskrypcji w Preferencjach: 4,99 € miesięcznie.',
        ],
    ],

    '0.78.0' => [
        'date' => '2026-10-18',
        'title' => 'Co się wydarzyło, i księga przygód',
        'items' => [
            'Każda scena ma teraz dwie części: „Co jest zaplanowane” i „Co się wydarzyło”. Jeśli drużyna trzymała się planu, wystarczy zaznaczyć „Rozegrana zgodnie z planem”.',
            'Nowa „Księga przygód” w kampanii: rozegrane sceny, scenariusz po scenariuszu, a potem podsumowania sesji, do czytania, drukowania lub zapisania jako PDF.',
            'Można dodać wersję spisaną przez ciebie lub przez wybraną SI (tekst do skopiowania albo własny klucz API, na twój koszt). Księga pozostaje tylko dla MG.',
        ],
    ],

    '0.77.0' => [
        'date' => '2026-10-18',
        'title' => 'Ułóż swoje ekrany po swojemu',
        'items' => [
            'Nowy przycisk „Uporządkuj ekran” na stronie kampanii i w trybie Sesji: przeciągnij każdy blok za uchwyt ⠿, myszą lub palcem, albo użyj strzałek.',
            'W trybie Sesji blok może przejść z jednej kolumny do drugiej: muzyka na górze, notatki po prawej, jak wolisz.',
            '„Ukryj” usuwa blok, którego nie używasz, „Pokaż” go przywraca, „Układ domyślny” przywraca wszystko. Ten układ należy tylko do ciebie: gracze i współprowadzący zachowują własny.',
        ],
    ],

    '0.76.0' => [
        'date' => '2026-10-18',
        'title' => 'Twoja karta, nawet bez sieci',
        'items' => [
            'Offline karta Twojej postaci pozostaje użyteczna: ustawiaj liczniki przyciskami − i +, zmieniaj pola, na które pozwala MG, i pisz notatki.',
            'Wszystko zostaje na urządzeniu i jest wysyłane po powrocie połączenia, pole po polu: licznik otrzymuje to, co odjąłeś lub dodałeś, a to, co MG zmienił w międzyczasie (ujawniony sekret, zmienione pole), zostaje zachowane.',
            'Po wysłaniu podsumowanie pokazuje, co zostało zaktualizowane, i wskazuje pola, które MG zmienił w tym samym czasie co Ty.',
        ],
    ],

    '0.75.0' => [
        'date' => '2026-10-18',
        'title' => 'Wielka mapa kampanii',
        'items' => [
            'Nowa „Mapa kampanii”: wybierz mapę jako widok ogólny i umieść na niej ciekawe miejsca, miasto, loch, miejsce sceny.',
            'Punkt może jednym kliknięciem otworzyć mapę szczegółową, która ma własne punkty; ścieżka nawigacji pozwala wrócić do widoku ogólnego.',
            'Powiązane ze sceną punkty pokazują, co rozegrano, co trwa, a co dopiero nadejdzie; powiązane z kartą otwierają ją. Wszystko zostaje u MG, poza nazwami punktów „Widoczne przy stole”, pokazywanymi na ekranie stołu.',
        ],
    ],

    '0.74.0' => [
        'date' => '2026-10-18',
        'title' => 'Twoje postacie z innych stołów',
        'items' => [
            'Nowa strona „Moje karty”: przechowuj postacie, którymi grasz poza SagaWyn, u znajomych, w klubie lub gdzie indziej. Widzisz je tylko Ty.',
            'Każda postać ma swoją kartę PDF, portret, dowolne pola (teksty i liczniki ustawiane przyciskami − i + podczas gry) oraz Twoje notatki, z nazwą gry i drużyny, by łatwo ją odnaleźć.',
        ],
    ],

    '0.73.0' => [
        'date' => '2026-10-18',
        'title' => 'Biblioteka dźwięków dla wszystkich kampanii',
        'items' => [
            'Nowa strona „Moje dźwięki”: muzyka i ambienty zapisane raz na koncie, a potem dodawane do prowadzonych kampanii. Plik nie jest kopiowany i liczy się tylko raz do miejsca na dane.',
            'Na stronie Dźwięki kampanii: „Z mojej biblioteki”, aby dodać swoje dźwięki, „Przenieś do mojej biblioteki”, aby użyć dźwięku gdzie indziej, oraz pole, by nowy plik trafił od razu do biblioteki.',
            'Przeniesienie do biblioteki dźwięku wgranego już w innej kampanii scala obie kopie: sceny zachowują muzykę, a miejsce zostaje zwolnione.',
        ],
    ],

    '0.72.0' => [
        'date' => '2026-10-18',
        'title' => 'Instrukcja wszędzie',
        'items' => [
            'Przycisk „?” pojawia się też przy dźwiękach, grafie relacji, osi czasu, opiniach graczy, asystencie AI, dzienniku i karcie postaci.',
            'Na grafie, osi czasu i karcie postaci gracze czytają instrukcję napisaną dla nich.',
        ],
    ],

    '0.71.1' => [
        'date' => '2026-10-18',
        'title' => 'Odejmij lub dodaj kilka punktów naraz',
        'items' => [
            'Pod każdym licznikiem, który gracz może zmieniać (PŻ, amunicja, pieniądze…), pole „Wartość”: wpisz 8, potem −, a licznik straci 8 naraz. Domyślnie to 1.',
        ],
    ],

    '0.71.0' => [
        'date' => '2026-10-18',
        'title' => 'Instrukcja na kolejnych ekranach',
        'items' => [
            'Przycisk „?” pojawia się też na karcie (po stronie MG), w trybie Sesji, w scenariuszach i scenach, przy imporcie plików i wśród członków kampanii.',
            'Strona Pomocy wyjaśnia teraz surowy rzut i premie odczytywane z karty postaci.',
        ],
    ],

    '0.70.0' => [
        'date' => '2026-10-18',
        'title' => 'Instrukcja na trudniejszych ekranach',
        'items' => [
            'Przycisk „?” obok tytułu ekranów, które nie tłumaczą się same, otwiera krótką instrukcję: typy kart, pola gry, tagi, kości, mapy, pilot ekranu stołu, tropy i historia ujawnień.',
            'Każda instrukcja prowadzi do odpowiedniego pytania na stronie Pomocy, które otwiera się od razu.',
        ],
    ],

    '0.69.0' => [
        'date' => '2026-10-18',
        'title' => 'Premia i próg z karty',
        'items' => [
            'W kościach premia może brać jedną lub kilka wartości z karty postaci (cecha, umiejętność, dowolne pole liczbowe lub licznik w strefie publicznej), w całości albo jako dziesiątki, połowę lub piątą część, obok wolnej premii. Próg też może pochodzić z karty. Wartości są odczytywane w chwili rzutu z karty każdej postaci, która rzuca.',
            'Kilka nazwanych premii (Broń +2, Rana −1…) sumuje się w jednym rzucie, razem z wartościami z karty.',
            'Gotowe przykłady znikają z tacki: zostają twoje zapisane rzuty.',
            'Zapisany rzut usuwa się jednym dotknięciem: × na jego etykiecie lub „Usuń” po wczytaniu.',
        ],
    ],

    '0.68.0' => [
        'date' => '2026-10-17',
        'title' => 'Surowy rzut',
        'items' => [
            'Nowy odczyt „Surowy”, wybrany domyślnie: wybierz rodzaj i liczbę kości (1d20 + 2d6…), rzuć, a każda kość pokaże się tak, jak upadła, bez sumy, bez progu i bez innych zasad.',
        ],
    ],

    '0.67.0' => [
        'date' => '2026-10-17',
        'title' => 'Twoje treści, Twoje prawa',
        'items' => [
            'Strona „Prywatność i informacje prawne” wyjaśnia, że SagaWyn nie jest właścicielem tego, co w nim umieszczasz: każdy użytkownik pozostaje właścicielem swoich treści i odpowiada za to, co publikuje.',
            'Nowe sekcje: „Importowanie i porządkowanie treści”, „Usługi sztucznej inteligencji”, „Zgłoś nielegalną treść”, „Dostępność i kopie zapasowe” oraz „Gry i znaki towarowe”. Strony importu do nich odsyłają.',
        ],
    ],

    '0.66.0' => [
        'date' => '2026-10-17',
        'title' => 'Mieszane kości i własne zapisane rzuty',
        'items' => [
            'Jeden rzut może łączyć kilka rodzajów kości: dotknij k20, a potem dwóch k6, i rzucane jest 1d20 + 2d6. Każdy rodzaj ma własną liczbę kości, którą można zmienić lub usunąć.',
            'Próg dotyczy sumy („Próg dla sumy”, osiągnięty lub nieprzekroczony) albo każdej kości („Próg na kość”: na przykład każda kość z wynikiem 4 lub więcej to sukces).',
            'Każdy, gracz czy MG, zapisuje własne rzuty w „Moje rzuty”, wczytuje je jednym dotknięciem i aktualizuje, gdy postać się rozwija. MG ma też „Rzuty kampanii” dla całego stołu.',
        ],
    ],

    '0.65.1' => [
        'date' => '2026-10-17',
        'title' => 'Dostęp sprawdzany wszędzie',
        'items' => [
            'Wpisanie adresu ręcznie nigdy nie otwiera kampanii, karty ani pliku, do którego nie ma się prawa. Konto, które nie jest członkiem, otrzymuje teraz „Nie znaleziono strony”, tak jak dla adresu, który nie istnieje: próbowanie numerów nie zdradza nawet, że kampania istnieje.',
        ],
    ],

    '0.65.0' => [
        'date' => '2026-10-17',
        'title' => 'Kości przy stole',
        'items' => [
            'Nowy przycisk „Kości” na dole każdej strony kampanii: liczba i rodzaj kości (od 4 do 100 ścianek lub inne), a potem sposób odczytu: dodać, osiągnąć próg, zostać poniżej progu, liczyć sukcesy, zachować najlepsze lub najsłabsze, odczytać jako cyfry. Z premią i kośćmi rzucanymi ponownie przy maksimum (+) lub przy 1 (−).',
            'MG prosi o rzut cały stół lub wybrane postacie, jawnie lub dyskretnie. U odpowiednich graczy otwiera się okno; bez progu każdy wpisuje własny i dodaje swoją premię, a ukryty próg zostaje u MG, który widzi, kto co wyrzucił.',
            'Rzuty przy stole toczą się przez kilka sekund na ekranie stołu; dyskretny rzut widzi tylko MG. MG zapisuje swoje typowe rzuty dla całego stołu, a kości można wyłączyć w „Funkcjach kampanii”.',
        ],
    ],

    '0.64.1' => [
        'date' => '2026-10-17',
        'title' => 'Szablony mierzone jak linijka',
        'items' => [
            'Szablony ustawia się w tej samej jednostce co linijkę (metry, stopy…): „Rzeczywisty rozmiar pola” w Siatce kalibruje oba, bez względu na rozmiar pól na ekranie.',
            'Wyraźniejszy przycisk „Usuń” przy każdym szablonie i „Usuń wszystkie szablony” po zaklęciu.',
            'Każdy link w „Dołącz zdalnie” ma swój kod QR, który gracze mogą zeskanować telefonem.',
        ],
    ],

    '0.64.0' => [
        'date' => '2026-10-17',
        'title' => 'Szablony obszaru na mapie',
        'items' => [
            'Nowe szablony na stronie mapy: koło, kwadrat, prostokąt, stożek i linia, mierzone w polach (lub w jednostce skali). Przeciąga się je po mapie, obraca, ukrywa, a lista trafionych żetonów pojawia się od razu.',
            'Widoczne szablony pojawiają się na ekranie stołu, pod żetonami.',
        ],
    ],

    '0.63.0' => [
        'date' => '2026-10-17',
        'title' => 'Wspólna mapa bitewna',
        'items' => [
            'Na wspólnym ekranie stołu każdy gracz przesuwa żeton swojej postaci. Na mapie opcja „Przesuwa” powierza graczowi inny żeton: towarzysza, chowańca, kontrolowanego wroga…',
            'Nowa kolejność inicjatywy, z pilota lub z mapy: BG i wrogowie z wartością, bieżąca tura, rundy. MG pokazuje ją na ekranie, kiedy chce; ukryte wiersze zostają u niego, a rząd „Obecni także” pokazuje BN lub przedmioty bez inicjatywy.',
            '„Dołącz zdalnie” w pilocie: link do ekranu dla graczy oraz link dla gościa bez konta, tylko do podglądu i możliwy do unieważnienia, dla kogoś, kto chce tylko oglądać.',
        ],
    ],

    '0.62.0' => [
        'date' => '2026-10-13',
        'title' => 'Społeczność na Discordzie',
        'items' => [
            'SagaWyn ma własny serwer Discord: link na dole każdej strony i na stronie głównej, by zadawać pytania, zgłaszać pomysły i poznawać innych mistrzów gry.',
        ],
    ],

    '0.61.0' => [
        'date' => '2026-10-13',
        'title' => 'Tryb demo przed Kickstarterem',
        'items' => [
            'Nowy tryb demo, włączany w Administracja › Plany: każde nowe konto otrzymuje plan Demo, z własną kopią kampanii demonstracyjnej i banerem zapowiadającym nadchodzącą kampanię crowdfundingową.',
            'Demonstracja konta demo jest zablokowana: wszystko można obejrzeć, ale używać można tylko sesji, notatek, żetonów na mapie i ekranu stołu. Konto może też utworzyć małą własną kampanię (50 MB, 3 członków, bez AI i eksportu).',
            'Zaproszenie od administratora otwiera zwykłe konto. W „Kontach” przycisk „Odblokuj” przenosi konto demo na plan darmowy i uruchamia jego okres próbny, którego demo nie zużywa.',
        ],
    ],

    '0.60.0' => [
        'date' => '2026-10-13',
        'title' => 'Treści domowe',
        'items' => [
            'Nowe pole „Treść domowa” na kartach (BN, zaklęcia, przedmioty, miejsca…): bez zaznaczenia karta jest oficjalna. Odznaka „Domowe” wyróżnia ją na liście i na karcie, a filtr Oficjalne / Domowe porządkuje uniwersum.',
            'Przy karcie domowej lub zasadzie o pochodzeniu „Domowa” można obok zapisać zastąpioną wersję oficjalną (np. inicjatywa na k10 zamiast k20): zostaje w strefie MG, zwinięta pod kartą lub zasadą.',
            'Listę zasad można filtrować według pochodzenia. Eksport i import kampanii zachowują treści domowe i wersję oficjalną.',
        ],
    ],

    '0.59.1' => [
        'date' => '2026-10-13',
        'title' => 'Dokładniejsza kontrola przed uruchomieniem',
        'items' => [
            'Administracja: `sagawyn:check` nie zgłasza już błędnie logowania w trybie debugowania, gdy LOG_LEVEL ma wartość warning.',
        ],
    ],

    '0.59.0' => [
        'date' => '2026-10-13',
        'title' => 'Logo Autistic Intelligence',
        'items' => [
            'Logo Autistic Intelligence, wydawcy SagaWyn, pojawia się teraz na dole stron, na ekranie logowania i w nocie prawnej. Dopasowuje się do jasnego lub ciemnego motywu.',
        ],
    ],

    '0.58.0' => [
        'date' => '2026-10-12',
        'title' => 'Trop może być kłamstwem',
        'items' => [
            'Tropy: nowy rodzaj „Kłamstwo”, obok Plotki, Tropu i Prawdy, z własnym filtrem. Jak inne rodzaje widzi go tylko MG: gracz, któremu trop zostanie ujawniony, czyta tylko sam trop.',
        ],
    ],

    '0.57.0' => [
        'date' => '2026-10-12',
        'title' => 'Przygotuj całą kampanię z pomocą AI',
        'items' => [
            'Moje kampanie → Importuj kampanię: nowy link „Przygotuj archiwum z AI”. Prompt (lub skill Claude) sprawia, że Twoja AI czyta podręcznik gry fabularnej i tworzy archiwum .zip: gra, świat, kampania, karty, sceny i dokumenty powstają za jednym razem.',
        ],
    ],

    '0.56.1' => [
        'date' => '2026-10-11',
        'title' => 'Strony gry i świata czytelne na telefonie',
        'items' => [
            'Na telefonie obraz gry lub świata wyświetla się nad nazwą na całą szerokość: nazwa i opis nie są już ściśnięte, a przycisk Edytuj ich nie zasłania.',
        ],
    ],

    '0.56.0' => [
        'date' => '2026-10-11',
        'title' => 'Sekrety stają się tropami',
        'items' => [
            '„Sekrety” kampanii nazywają się teraz „tropami”: strona Tropy, panel kart, scen i dokumentów, tryb Sesji, wyszukiwanie, ujawnienia i pomoc. Twoje dane się nie zmieniają.',
        ],
    ],

    '0.54.0' => [
        'date' => '2026-10-11',
        'title' => 'Gracze edytują swoją postać',
        'items' => [
            'Strona Pola: „Edytowalne przez gracza” można zaznaczyć dla całej grupy (cechy, umiejętności…) lub dla wszystkich pól publicznych jednym kliknięciem.',
            'Nowa opcja na stronie Gracze: MG może pozwolić każdemu graczowi edytować portret, streszczenie i opis swojej postaci. Strefa MG pozostaje ukryta.',
        ],
    ],

    '0.51.0' => [
        'date' => '2026-10-11',
        'title' => 'Odznaka Założyciela i kody podarunkowe',
        'items' => [
            'Wspierający na Kickstarterze mogą zostać oznaczeni jako Założyciel w konsoli administracyjnej: odznaka ★ Założyciel widoczna w preferencjach, na liście członków i na karcie postaci.',
            'Założyciele mają dodatkowe miejsce ponad swój plan, darmowy lub premium (domyślnie 500 MB, do ustawienia w „Planach”).',
            'Nowa strona „Moje nagrody” (z preferencji): twoja nagroda z Kickstartera, kody podarunkowe Premium do rozdania przez link, z imieniem osoby, która użyła każdego z nich, oraz pole do użycia otrzymanego kodu. Miesiące dodają się po zakończeniu obecnego Premium.',
        ],
    ],

    '0.50.1' => [
        'date' => '2026-10-11',
        'title' => 'Stan: filtr według roli',
        'items' => [
            'Na karcie Stan filtr według roli konta (bez administratorów, administratorzy, premium, okres próbny, bezpłatne).',
            'Kampania demonstracyjna nie jest już liczona w statystykach.',
        ],
    ],

    '0.50.0' => [
        'date' => '2026-10-10',
        'title' => 'Stan platformy',
        'items' => [
            'Nowa karta Stan w konsoli administracyjnej: zalogowane osoby i liczba logowań na dzień, tydzień lub miesiąc, gracze na kampanię i na MG, zajęte miejsce (pliki, baza, dysk) i czas odpowiedzi serwera godzina po godzinie.',
            'Konsola ostrzega, gdy serwer zwalnia: ponad 5 % żądań powyżej sekundy w ciągu 24 godzin.',
        ],
    ],

    '0.49.1' => [
        'date' => '2026-10-10',
        'title' => 'Pełniejsza pomoc',
        'items' => [
            'Pomoc odpowiada na 19 nowych pytań: gra, świat i kampania, demonstracja, wyszukiwanie i odnośniki, tagi, scenariusze, Tryb Sesji, pola gry, zasady, duplikowanie, wymiany, odejście gracza, dziennik, koniec Premium, a dla graczy: karta, notatki, wiadomości i opinie.',
        ],
    ],

    '0.49.0' => [
        'date' => '2026-10-10',
        'title' => 'Demo pokazuje całe SagaWyn',
        'items' => [
            'Kampania demonstracyjna korzysta teraz z każdej funkcji: postać gracza utworzona z gotowej postaci, z tym, co otrzymała (karta, dokument, zasada, sekret, przedmioty), którą MG może „Zobaczyć jako”; notatki z sesji; lista „Do rozegrania”; przypięte karty; pola odwołania, dokumentu i wspólne dla dwóch typów; załącznik MG; sekrety każdego rodzaju; kolorowe tagi; okładki gry i świata.',
            'Ponowne wczytanie demo używa już utworzonego typu karty „Frakcja” zamiast dodawać drugi.',
        ],
    ],

    '0.48.0' => [
        'date' => '2026-10-10',
        'title' => 'Pełniejsza demonstracja',
        'items' => [
            'Kampania demonstracyjna „Przysięga Pierrecendre” zawiera teraz trzy oryginalne nastroje dźwiękowe połączone ze scenami, rozegraną już pierwszą sesję z podsumowaniem oraz prośbę o opinię do graczy.',
            'Poprawka: przejście między dwoma utworami nie przerywa już odtwarzania w niektórych przeglądarkach.',
        ],
    ],

    '0.47.0' => [
        'date' => '2026-10-10',
        'title' => 'LoreMundi zmienia nazwę na SagaWyn',
        'items' => [
            'LoreMundi nazywa się teraz SagaWyn, wydawca wciąż: Autistic Intelligence. Every world has a story.',
            'Twoje kampanie, konta i archiwa się nie zmieniają: kopie zapasowe utworzone w LoreMundi lub CodexFlow nadal można zaimportować. Pobierane pliki zaczynają się teraz od „sagawyn-”.',
        ],
    ],

    '0.46.0' => [
        'date' => '2026-10-10',
        'title' => 'Opinie graczy',
        'items' => [
            'Na koniec sesji lub kampanii poproś graczy o ocenę od 1 do 5 gwiazdek, o to, co im się podobało i co mogłoby być lepsze („Opinie graczy” w kampanii albo ze strony sesji).',
            'Odpowiedzi anonimowe lub podpisane, do wyboru MG: gracze wiedzą o tym przed odpowiedzią. Widzisz średnią, rozkład ocen i wszystkie komentarze.',
        ],
    ],

    '0.45.0' => [
        'date' => '2026-10-10',
        'title' => 'Biblioteka dźwięków',
        'items' => [
            'Nowa strona „Dźwięki”: prześlij swoją muzykę i nastroje (mp3, ogg, m4a, wav, flac), uporządkuj je tagami i połącz ze scenami.',
            'W trybie Sesji blok „Muzyka” najpierw proponuje dźwięki sceny: „Tutaj” odtwarza je na twoim urządzeniu, bez przerywania przy zmianie strony; „Stół” odtwarza je na ekranie stołu, z pauzą, pętlą i głośnością sterowanymi z sesji.',
        ],
    ],

    '0.44.0' => [
        'date' => '2026-10-09',
        'title' => 'Gotowe do publikacji',
        'items' => [
            'Administrator dostaje w konsoli ostrzeżenie, gdy instalacja ma blokujący problem (włączone debugowanie, nieskonfigurowana poczta, nieszyfrowany czas rzeczywisty…).',
            'Baza danych i przesłane pliki są co noc zapisywane na serwerze.',
        ],
    ],

    '0.43.0' => [
        'date' => '2026-10-09',
        'title' => 'Niezawodność',
        'items' => [
            'Nowe testy automatyczne sprawdzają w prawdziwej przeglądarce aktualizację bez czasu rzeczywistego, „Zapamiętaj mnie”, link „Przejdź do treści”, wyświetlanie na telefonie z bardzo dużym tekstem oraz funkcję wyłączoną przez MG przy otwartej stronie.',
        ],
    ],

    '0.42.0' => [
        'date' => '2026-10-09',
        'title' => 'Sformatowane opisy',
        'items' => [
            'Opisy gier, światów, scenariuszy i dokumentów mają ten sam edytor co karty: pogrubienie, kursywa, śródtytuły, listy, cytaty oraz linki [[ ]] do kart w kampanii.',
        ],
    ],

    '0.41.0' => [
        'date' => '2026-10-09',
        'title' => 'Wspólne pola',
        'items' => [
            'Pole może dotyczyć kilku typów kart naraz, na przykład punktów życia postaci i stworzeń; szablony gry i pliki pól zachowują ten wybór.',
        ],
    ],

    '0.40.0' => [
        'date' => '2026-10-09',
        'title' => 'Aplikacja i bezpieczeństwo',
        'items' => [
            'Aplikacja zainstalowana na telefonie ma opis w Twoim języku i ikonę dopasowaną do okrągłych ikon Androida.',
            'Bez połączenia strony pozostają czytelne, a przyciski edycji są wyszarzone do powrotu sieci.',
            'Przeglądarka przyjmuje teraz tylko skrypty, obrazy i połączenia pochodzące z samego LoreMundi.',
        ],
    ],

    '0.39.0' => [
        'date' => '2026-10-09',
        'title' => 'Konto i administracja',
        'items' => [
            'Problem można zgłosić bez konta, ze stron logowania i rejestracji lub z pomocy, podając adres do odpowiedzi.',
            'Preferencje pokazują datę rozpoczęcia subskrypcji oraz, dopóki płatności nie działają, że przejście na Premium wkrótce będzie możliwe.',
            'Konsola administracyjna: dni logowania zamiast logowań, data ostatniego logowania i ostrzeżenie, gdy wysyłka push jest niemożliwa lub się nie udaje.',
        ],
    ],

    '0.38.0' => [
        'date' => '2026-10-09',
        'title' => 'Wyszukiwanie',
        'items' => [
            'Wyszukiwanie znajduje też inne francuskie formy słowa: „lanternes” znajduje „lanterne”, „éteinte” znajduje „éteintes”.',
            'Wyszukiwanie ze strony głównej przeszukuje też twoje światy i gry niepowiązane z żadną kampanią.',
        ],
    ],

    '0.37.0' => [
        'date' => '2026-10-09',
        'title' => 'Gracze i role',
        'items' => [
            'Odkryta karta pokazuje graczowi jej publiczne ilustracje i pliki oraz publiczne relacje z kartami, które zna.',
            'Gracze mają na swojej karcie „Wątek kampanii”: sesje, rozegrane wydarzenia znane przy stole i wiadomości do grupy.',
            'Współ-MG widzą strony gry i świata tylko do odczytu, pobierają archiwum i szablon gry oraz zarządzają polami, jeśli właściciel zaznaczy to w „Członkowie”. Zdegradowany współ-MG nie zachowuje powiadomień otrzymanych jako MG.',
        ],
    ],

    '0.36.0' => [
        'date' => '2026-10-09',
        'title' => 'Wygoda MG i sesji',
        'items' => [
            'Strony PDF-a pokazanego przy stole przewraca się z pilota, ze strony dokumentu lub strzałkami na ekranie MG, a gracze śledzący ekran przewracają je razem z nim. Przeglądarka PDF pokazuje „strona n / N” i pozwala przejść do strony.',
            'W trybie Sesji „Rozegrane wydarzenie” dodaje wpisany tekst do chronologii, powiązany z bieżącą sesją i sceną.',
            'Postać można oddać każdemu z jej dawnych graczy, który wraca do kampanii; odzyskuje on własne prywatne rozmowy z MG, bez rozmów graczy pośrednich.',
            'Status karty widać pod jej tytułem; gotowe postacie (tag „gotowa postać”) są proponowane na początku w „Nowa postać”; liczniki na stronie Tagów pokazują oznaczone elementy; demo wczytane kilka razy numeruje kampanię, grę i świat; rola widza mówi, że widzi ekran stołu nawet nieudostępniony.',
        ],
    ],

    '0.35.0' => [
        'date' => '2026-10-09',
        'title' => 'Poprawki z końcowego testu odbiorczego',
        'items' => [
            'Strona sesji ma streszczenie napisane przez MG i pokazuje rozegrane wydarzenia oraz wszystko, co ujawniono lub przekazano podczas sesji.',
            'Historia karty zapisuje teraz także relacje, załączone pliki, tagi i status w kampanii. Strona świata pokazuje jego historię, a strona gry jej typy kart.',
            'Funkcja wyłączona przez MG lub przez plan jest wyłączona także na otwartych stronach, a tryb sesji otwiera się, gdy mapy są wyłączone.',
            'Poprawki: ilość podawana przy wymianie, nigdy niewypełnione pole tak/nie, przetłumaczona strona „nie znaleziono”, strony czytelne na telefonie przy dużym tekście, logowania liczone raz, zaktualizowana pomoc.',
        ],
    ],

    '0.34.0' => [
        'date' => '2026-10-08',
        'title' => 'Funkcje w każdej kampanii',
        'items' => [
            'Na stronie kampanii MG zaznacza funkcje potrzebne jego stołowi: ekran stołu, mapy, wymiana między graczami, graf, oś czasu, asystent AI. Odznaczona funkcja znika dla wszystkich, nic nie jest usuwane; wraca, gdy tylko zostanie ponownie zaznaczona.',
        ],
    ],

    '0.33.0' => [
        'date' => '2026-10-08',
        'title' => 'Wykończenie',
        'items' => [
            'Wyszukiwanie pokazuje pole karty zawierające znalezione słowo, wraz z jego nazwą.',
            'Graf: nachodzące na siebie nazwy i etykiety są przesuwane lub ukrywane; najechanie na kartę przywraca je.',
            '„Ujawnij lub daj”: pole „Wszystkie aktywne postacie” zaznacza cały stół naraz.',
            'Gracz usunięty i ponownie zaproszony odzyskuje swoją dawną postać jednym kliknięciem, na stronie Postacie.',
            'Bez czasu rzeczywistego (brak serwera Reverb lub awaria) dzwonek, wiadomości i karty odświeżają się co 30 sekund.',
        ],
    ],

    '0.32.0' => [
        'date' => '2026-10-08',
        'title' => 'PDF i dokumenty',
        'items' => [
            'Pliki PDF otwierają się we wbudowanej przeglądarce, takiej samej na komputerze, tablecie i telefonie, z powiększaniem i pobieraniem.',
            'Na ekranie stołu PDF wyświetla się strona po stronie, dopasowany do ekranu; strzałki przewracają strony.',
            'Karta postaci zachowuje oryginalną nazwę pliku.',
            '„Używane przez” pokazuje scenariusz każdej sceny.',
            'Strony gry i świata mogą mieć obraz.',
        ],
    ],

    '0.31.0' => [
        'date' => '2026-10-08',
        'title' => 'Funkcje Premium ✦',
        'items' => [
            'Mała gwiazdka ✦ oznacza funkcje Premium. Gdy plan właściciela kampanii ich nie obejmuje, pozostają widoczne, wyszarzone, z wyjaśnieniem.',
            'Po zakończeniu okresu próbnego lub subskrypcji nic nie jest usuwane: kampanie, mapy, wiadomości i pliki zostają, wyłączają się tylko funkcje ✦.',
            'Administrator może podarować okres (Boże Narodzenie…), w którym darmowe konta mają wszystkie funkcje Premium.',
        ],
    ],

    '0.30.0' => [
        'date' => '2026-10-08',
        'title' => 'Pisanie i linki',
        'items' => [
            'Pola „długi tekst” i notatki MG w zasadach mają edytor formatowania i linki [[ ]].',
            'Na swojej karcie gracz widzi sformatowane długie teksty z linkami do kart, które zna jego postać.',
            'Szybka notatka z sesji podpowiada karty, gdy tylko wpiszesz „[[”.',
            'Kopie są numerowane („kopia 2”, „kopia 3”), a import nigdy nie powtarza nazwy gry, świata ani kampanii, które już masz.',
        ],
    ],

    '0.29.0' => [
        'date' => '2026-10-08',
        'title' => 'Bezpieczeństwo konta',
        'items' => [
            'Nowy adres e-mail jest przyjmowany dopiero po kliknięciu linku, który na niego przyszedł; potem stary adres dostaje powiadomienie.',
            'Próby w formularzach konta są liczone dla każdego formularza i adresu e-mail, a strona oczekiwania podaje, ile sekund trzeba odczekać.',
            'Na stronach mogą działać tylko skrypty LoreMundi.',
            'Przy usuwaniu konta jest informacja, że twoje wiadomości zostaną usunięte.',
        ],
    ],

    '0.28.0' => [
        'date' => '2026-10-08',
        'title' => 'E-maile w barwach LoreMundi',
        'items' => [
            'E-maile (zapomniane hasło, zmiana adresu) mają logo i kolory LoreMundi.',
            'Każdy e-mail jest wysyłany w języku odbiorcy, nawet gdy wysyła go administrator.',
        ],
    ],

    '0.27.0' => [
        'date' => '2026-10-08',
        'title' => 'Publiczna wizytówka',
        'items' => [
            'Strona główna przedstawia LoreMundi odwiedzającym i wyszukiwarkom, we wszystkich 8 językach.',
            'Pomoc jest dostępna bez konta i ma nową sekcję „Twoje konto”: plany, e-mail, hasło, uwierzytelnianie dwuskładnikowe, dane.',
        ],
    ],

    '0.26.0' => [
        'date' => '2026-10-08',
        'title' => 'Poprawki z testu odbiorczego v0.25.0',
        'items' => [
            'Otwarta strona ponownie sprawdza uprawnienia przy każdej akcji: usunięty gracz lub zdegradowany współ-MG nie dostaje już nic nowego.',
            'Gracz, któremu powierzono postać, nie czyta już prywatnej rozmowy poprzedniego gracza z MG.',
            'Gotowe postacie z kampanii demonstracyjnej są proponowane w „Nowa postać”.',
            'Uwierzytelnianie dwuskładnikowe: kody zapasowe działają przy logowaniu, a administrator może je usunąć z zablokowanego konta.',
            'Nowe ikony LoreMundi (karta przeglądarki, zainstalowana aplikacja, powiadomienia).',
            'Udostępniony ekran stołu czytelny na telefonie; nagłówki kart i stron poprawione na telefonie i tablecie, także przy dużym tekście.',
            'Poprawki: powiadomienia push bez błędów, przyciski okna „otrzymano”, odwołania do kart po zmianie nazwy, liczba w wymianie, linijka map, baner „Offline”, przetłumaczone komunikaty błędów, link „Przejdź do treści”.',
        ],
    ],

    '0.25.0' => [
        'date' => '2026-10-08',
        'title' => 'Import podręcznika gry z pomocą AI',
        'items' => [
            'W „Importuj” opcja „Przygotuj pliki z AI” daje prompt do wklejenia w wybranej AI razem z PDF gry lub scenariusza: przygotuje pliki importu (pola, karty, zasady, sceny) i instrukcję krok po kroku. Prompt jest też dostępny jako skill Claude.',
        ],
    ],

    '0.24.0' => [
        'date' => '2026-10-08',
        'title' => 'Bezpieczeństwo i dane osobowe',
        'items' => [
            '„Moje konto” w Preferencjach: zmień imię, adres e-mail (poprzedni adres zostanie powiadomiony) i hasło.',
            'Opcjonalne uwierzytelnianie dwuskładnikowe w Preferencjach: kod z aplikacji w telefonie, z kodami odzyskiwania.',
            '„Moje dane” w Preferencjach: pobierz to, co LoreMundi przechowuje o Tobie, albo usuń konto.',
            'Hasła co najmniej 10-znakowe z literami i cyframi; zmiana hasła wylogowuje inne urządzenia. Konsola administracyjna ponownie prosi o hasło.',
            'Nowa strona „Prywatność i informacje prawne”.',
            'Gracz usunięty z kampanii lub zmieniony w widza nie gra już swoją postacią i nie otrzymuje niczego, co jest jej ujawniane. Wzmocniono też inne kontrole uprawnień na serwerze.',
        ],
    ],

    '0.23.0' => [
        'date' => '2026-10-08',
        'title' => 'CodexFlow zmienia nazwę na LoreMundi',
        'items' => [
            'CodexFlow nazywa się teraz LoreMundi, wydawca: Autistic Intelligence. Every world has a story.',
            'Twoje kampanie, konta i archiwa się nie zmieniają: kopie zapasowe utworzone w CodexFlow nadal można zaimportować. Pobierane pliki zaczynają się teraz od „loremundi-”.',
        ],
    ],

    '0.22.0' => [
        'date' => '2026-10-08',
        'title' => 'Pełna kopia zapasowa',
        'items' => [
            'Właściciel może pobrać pełną kopię zapasową: archiwum kampanii wraz ze stołem (postacie, to, co otrzymały, sesje, notatki, wiadomości i dziennik), bez notatek „Tylko ja” i adresów e-mail. Przy imporcie postacie wracają bez gracza, gotowe do przydzielenia.',
        ],
    ],

    '0.21.0' => [
        'date' => '2026-10-08',
        'title' => 'Wyszukiwanie ze strony głównej',
        'items' => [
            'Poza kampanią pasek wyszukiwania przeszukuje wszystkie twoje kampanie, ich światy i gry, każdą z twoimi uprawnieniami: wszystko jako MG, a jako gracz to, co zna twoja postać.',
        ],
    ],

    '0.20.0' => [
        'date' => '2026-10-08',
        'title' => 'Pełne „Wspomniane w”, duplikowanie bez statusów',
        'items' => [
            '„Wspomniane w” pokazuje też oś czasu, sekrety i pola innych kart, które wspominają kartę.',
            'Zduplikowana kampania zaczyna od oryginalnych kart: status „martwy” czy „więzień” nie jest już kopiowany, chyba że zaznaczysz pole, aby go zachować.',
        ],
    ],

    '0.19.0' => [
        'date' => '2026-10-08',
        'title' => 'Sekrety i nowe pola',
        'items' => [
            'Każdy sekret ma rodzaj (plotka, trop lub prawda) i stan wynikający z tego, kto go zna: ukryty, częściowy lub ujawniony. Możesz filtrować sekrety według obu.',
            'Trzy nowe typy pól: link internetowy, plik (dokument kampanii) i odwołanie do innej karty, które działa nawet po zmianie jej nazwy.',
            'Zduplikowana kampania zachowuje powiązania między skopiowanymi kartami.',
        ],
    ],

    '0.18.0' => [
        'date' => '2026-10-08',
        'title' => 'Nowa postać, Moje kampanie, strony gry i świata',
        'items' => [
            'Gdy gracz dostaje nową postać, MG zaznacza, co przechodzi z poprzedniej: wiedza, informacje, dokumenty i zasady są kopiowane, przedmioty zmieniają właściciela.',
            'Moje kampanie: przycisk „Wznów”, data ostatniej sesji i zarchiwizowane kampanie osobno.',
            'Każda gra i każdy świat ma własną stronę: opis, kampanie, zasady, dokumenty, pola lub karty do ponownego użycia.',
        ],
    ],

    '0.17.0' => [
        'date' => '2026-10-08',
        'title' => 'Edytor tekstu i notatki graczy',
        'items' => [
            'Długie teksty (opisy, notatki MG, sceny, zasady, oś czasu, notatki graczy) mają edytor z pogrubieniem, kursywą, śródtytułami, listami i cytatami; „[[” nadal podpowiada karty do połączenia.',
            'Gracze łączą swoje notatki z kartami, które zna ich postać, i tylko z nimi.',
            'Strona sesji pokazuje też notatki graczy z tej sesji, poza tymi, które zachowują dla siebie.',
        ],
    ],

    '0.16.0' => [
        'date' => '2026-10-08',
        'title' => 'Subskrypcja Premium i darmowy okres próbny',
        'items' => [
            'Przejdź na Premium w „Preferencjach”: płatność miesięczna lub roczna zabezpieczona przez Stripe, z fakturami i rezygnacją w portalu Stripe. Premium trwa do końca opłaconego okresu.',
            'Darmowy okres próbny: sześć tygodni ze wszystkimi funkcjami, od twojej pierwszej kampanii jako MG. Gracz, który nigdy nie jest MG, go nie zaczyna. Długość ustawia się w panelu administracyjnym.',
            'Zakładka „Rozwój” w panelu administracyjnym do prowadzenia planów platformy.',
        ],
    ],

    '0.15.0' => [
        'date' => '2026-10-08',
        'title' => 'Konsola administracyjna i plany',
        'items' => [
            'Konsola administracyjna: konta z ich planem, datami subskrypcji, zajętym miejscem, informacją o kluczu AI, kampaniami i logowaniami, bez danych osobowych. Administrator ustala plan każdego konta i może wysłać link do zresetowania hasła, nigdy go nie widząc.',
            'Trzy plany: administrator, premium i darmowy. Miejsce, liczbę kampanii i funkcje planu darmowego ustawia się w konsoli; granie, bycie współ-MG lub widzem nigdy się nie liczy.',
            'Backlog zbiera zgłoszone problemy, błędy i usprawnienia ze statusem, priorytetem i wersją poprawki; przechowywane są tam też testy odbiorcze, wersja po wersji. Twój plan widać w „Preferencjach”.',
        ],
    ],

    '0.14.0' => [
        'date' => '2026-10-08',
        'title' => 'Po testach odbiorczych: wymiany zatwierdzane przez MG',
        'items' => [
            'Domyślnie MG zatwierdza wymiany między graczami: przedmiot lub wiedza zmienia właściciela dopiero po akceptacji. Pole na stronie „Postacie graczy” pozwala zezwalać na nie od razu.',
            'Pilot: przy ostatnim elemencie sceny „Dalej” zmienia się w „Zakończ” i czyści ekran.',
            'Czytelniejszy dziennik dla zatwierdzonych przedmiotów, jaśniejsze komunikaty w „Zgłoś problem” i jedna forma zwracania się do ciebie w każdym języku.',
        ],
    ],

    '0.13.1' => [
        'date' => '2026-10-08',
        'title' => 'Poprawki z testów odbiorczych V1',
        'items' => [
            'Wyszukiwarka MG znajduje też sekrety, przekazane informacje i przedmioty, udostępnione notatki graczy oraz tagi scen.',
            'Duplikowanie kampanii kopiuje jej sekrety i przygotowaną oś czasu; link do karty otwarty podczas sesji pokazuje się w panelu bocznym, bez opuszczania sesji.',
            'Przetłumaczone strony błędów, e-mail z hasłem w twoim języku, tryb Sesji i Dokumenty czytelne na telefonie, menu dla ukrytych linków na małych ekranach.',
            'Odpoczywająca postać i przedmiot zatwierdzony przez MG nie mogą już być zmieniane przez gracza; tymczasowa linijka na mapie znika sama.',
        ],
    ],

    '0.13.0' => [
        'date' => '2026-10-08',
        'title' => 'Własna AI bez kopiowania i wklejania',
        'items' => [
            'W „Ustawieniach” możesz zapisać klucz API na swoje nazwisko u Claude (Anthropic), ChatGPT (OpenAI) lub Le Chat (Mistral). Asystent AI zaproponuje wtedy „Analizuj bezpośrednio”: propozycje pojawią się bez kopiowania i wklejania.',
            'Wywołania rozlicza dostawca na twoim koncie. Klucz jest szyfrowany, nigdy ponownie wyświetlany ani eksportowany, a tryb „tekst do wklejenia” pozostaje bezpłatny.',
        ],
    ],

    '0.12.0' => [
        'date' => '2026-10-08',
        'title' => 'Asystent AI bez abonamentu',
        'items' => [
            'Nowe narzędzie „Asystent AI” w kampanii: LoreMundi przygotowuje tekst z notatkami z sesji i kontekstem kampanii do wklejenia w wybranej AI. Jej odpowiedź, wklejona z powrotem, zamienia się w propozycje: streszczenie, rozegrane wydarzenia, relacje, statusy, notatki kampanii, ujawnienia.',
            'Każdą propozycję można przyjąć, zmienić lub odrzucić. Bez Ciebie nic się w kampanii nie zmienia, a proponowane relacje czy statusy dotyczą tylko kampanii i nie zmieniają wspólnego świata.',
        ],
    ],

    '0.11.1' => [
        'date' => '2026-10-08',
        'title' => 'Demonstracja w twoim języku',
        'items' => [
            'Kampania demonstracyjna istnieje we wszystkich ośmiu językach interfejsu. Wczytuje się w twoim albo w tym, który wybierzesz obok przycisku.',
        ],
    ],

    '0.11.0' => [
        'date' => '2026-10-08',
        'title' => 'Kampania demonstracyjna',
        'items' => [
            'Kampania demonstracyjna wczytywana jednym kliknięciem z „Moje kampanie”: wymyślona gra „Brume & Serment” i pełna intryga na trzy sesje, z kartami, portretami, relacjami, mapą, sekretami, zasadami, osią czasu i gotowymi postaciami.',
            'Małe przyciski z ikonami na stronie kampanii nie przesuwają się już po najechaniu myszą: nazwa pojawia się w dymku, nad resztą.',
            'Tagi wpisuje się wszędzie tak samo, a karta proponuje „Dodaj tag” tuż pod tytułem.',
        ],
    ],

    '0.10.0' => [
        'date' => '2026-10-07',
        'title' => 'Czytelniejsza strona kampanii',
        'items' => [
            'Uporządkowana strona kampanii: tryb Sesji na banerze, cztery obszary przygotowań, a pozostałe narzędzia jako małe przyciski z ikoną.',
            'Nastrój ekranu stołu, wybierany z pilota: Noc, Pergamin, Łupek lub Grimuar.',
        ],
    ],

    '0.9.0' => [
        'date' => '2026-10-07',
        'title' => 'Zabrać kampanię ze sobą',
        'items' => [
            'Eksport całej kampanii do archiwum .zip: gra, świat, karty, scenariusze, dokumenty, mapy, sekrety, oś czasu i pliki.',
            'Import archiwum z „Moje kampanie”: odtwarza kampanię, u ciebie lub u innego MG.',
            'Szablony gry do udostępnienia: typy kart, pola, etykiety i zasady, bez treści kampanii.',
        ],
    ],

    '0.8.0' => [
        'date' => '2026-10-07',
        'title' => 'Graf i oś czasu',
        'items' => [
            'Graf relacji: wszystkie powiązane karty lub sieć wokół jednej karty, z głębokością i filtrem według typu.',
            '„Zobacz jako” w grafie: sieć tak, jak zna ją postać. Gracze otwierają go ze strony swojej postaci.',
            'Oś czasu: historia świata, zaplanowane i rozegrane wydarzenia, z dowolnymi datami, np. „Dzień 3”.',
            'Rozegrane wydarzenie zapisane podczas sesji jest przypisywane do sesji i bieżącej sceny.',
        ],
    ],

    '0.7.0' => [
        'date' => '2026-10-07',
        'title' => 'Wokół stołu',
        'items' => [
            'Mapy: obraz na ekranie stołu, który przybliżasz i przesuwasz, z opcjonalną siatką kwadratową i skalą.',
            'Opcjonalne żetony, powiązane z kartami (nazwa i portret): przesuwanie, zmiana rozmiaru, pokazywanie graczom lub ukrywanie przed nimi.',
            'Tymczasowa linijka: narysuj linię, a odległość pojawi się w polach lub w metrach.',
            'Pilot: z telefonu wyczyść ekran, przejdź do następnego elementu sceny, steruj mapą.',
            'Gracze: to, co MG ci ujawnia lub daje, pojawia się od razu, bez przechodzenia przez powiadomienia.',
        ],
    ],

    '0.6.0' => [
        'date' => '2026-10-07',
        'title' => 'Pamięć kampanii',
        'items' => [
            'Sekrety: osobna informacja, powiązana z kartami, scenami lub dokumentami, ujawniana jednym kliknięciem postaci lub całemu stołowi.',
            'Historia ujawnień: kto dowiedział się czego, kiedy, podczas której sesji i której sceny; każde ujawnienie można cofnąć.',
            '„Zobacz jako”: MG widzi kampanię dokładnie tak jak postać, tylko do odczytu.',
            '„Wspomniane w” pokazuje też reguły i notatki z sesji, które wspominają kartę.',
            'Relacje: odwrotna („pracuje dla” / „zatrudnia”) wypełnia się sama.',
        ],
    ],

    '0.5.0' => [
        'date' => '2026-10-07',
        'title' => 'Lepszy porządek, razem',
        'items' => [
            'Strona „Tagi” do zmiany nazw, kolorów, scalania i usuwania tagów; sceny też mają tagi.',
            '„Duplikuj” kartę, scenariusz lub całą kampanię, aby rozegrać ją z inną drużyną.',
            'Nowe role: współ-MG, który przygotowuje i prowadzi razem z tobą, oraz widz, który ogląda ekran stołu.',
            '„Pokaż przy stole” z karty, portretu, ilustracji, dokumentu lub zasady.',
            'Tryb sesji: pokaż kartę lub zasadę jednym kliknięciem, zobacz następną scenę, klawisz N do notatek.',
        ],
    ],

    '0.4.1' => [
        'date' => '2026-10-07',
        'title' => 'Pomoc i zgłoszenia problemów',
        'items' => [
            'Strona „Pomoc” odpowiada na najczęstsze pytania, zarówno MG, jak i graczy.',
            '„Zgłoś problem” na dole każdej strony wysyła wiadomość do zespołu wraz z adresem danej strony.',
        ],
    ],

    '0.4.0' => [
        'date' => '2026-10-07',
        'title' => 'Wszystkie języki',
        'items' => [
            'Interfejs mówi po francusku, angielsku, niemiecku, hiszpańsku, włosku, portugalsku, niderlandzku i polsku.',
            'Język jest zgodny z przeglądarką; każdy może go wybrać w „Preferencjach”.',
            'Powiadomienia przychodzą w języku odbiorcy.',
            'Ciemny motyw pozostaje włączony przy przechodzeniu między stronami.',
            'Po edycji karty ze strony Postacie wracasz prosto do niej.',
        ],
    ],

    '0.3.0' => [
        'date' => '2026-10-07',
        'title' => 'Żywa więź',
        'items' => [
            'Wiadomości między MG a graczami oraz panel „Czat” zawsze pod ręką (drużyna i rozmowy prywatne).',
            'Powiadomienia: odkrycia, otrzymane przedmioty, wiadomości, z licznikiem w nagłówku.',
            'Wszystko aktualizuje się na żywo: wiadomości, liczniki, odkrycia, bez przeładowywania strony.',
            'LoreMundi można zainstalować jak aplikację; karta postaci pozostaje dostępna offline; powiadomienia na urządzeniu.',
            'Ekran stołu: mapy, obrazy, karty i ogłoszenia na telewizorze lub projektorze, udostępniane graczom, jeśli MG tego chce.',
            'Postacie dają sobie przedmioty i przekazują sobie to, co wiedzą.',
            'Gracze notują swoją wiedzę i dodają przedmioty; MG zatwierdza przedmioty.',
            'Ciemny motyw, kolor akcentu i rozmiar tekstu w „Preferencjach”.',
        ],
    ],

    '0.2.0' => [
        'date' => '2026-10-07',
        'title' => 'Gracze',
        'items' => [
            'Zapraszanie graczy przez link.',
            'Postacie graczy: karta, arkusz PDF, liczniki (PŻ, magia, amunicja…) i pola edytowalne przez gracza.',
            'Odkrycia i „Daj”: wiedza, posiadane przedmioty, dokumenty i zasady.',
            'Przestrzeń gracza: notatki prywatne lub wspólne, zamiary „Do zagrania”, dziennik postaci.',
            'Dziennik zmian: kto, co, kiedy, przed i po.',
        ],
    ],

    '0.1.0' => [
        'date' => '2026-10-06',
        'title' => 'Sam MG',
        'items' => [
            'Światy, kampanie i karty ze strefą publiczną i strefą MG, powiązania [[ ]] między kartami.',
            'Własne pola dla każdej gry, typy kart, import CSV/JSON.',
            'Scenariusze, sceny, zasady i biblioteka dokumentów.',
            'Tryb Sesji: trwająca scena, przydatne karty, szybkie notatki, „Do zagrania” i przypięte elementy.',
            'Wyszukiwanie globalne.',
        ],
    ],

];
