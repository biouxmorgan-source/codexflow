<?php

// Nowości w poszczególnych wersjach, od najnowszej do najstarszej. Wyświetlane w
// „Co nowego” (raz po każdej aktualizacji) oraz na stronie o tej samej nazwie.
return [

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
            'Kampania demonstracyjna wczytywana jednym kliknięciem z „Moich kampanii”: wymyślona gra „Brume & Serment” i pełna intryga na trzy sesje, z kartami, portretami, relacjami, mapą, sekretami, zasadami, osią czasu i gotowymi postaciami.',
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
            'Import archiwum z „Moich kampanii”: odtwarza kampanię, u ciebie lub u innego MG.',
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
