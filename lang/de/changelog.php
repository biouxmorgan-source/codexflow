<?php

// Neuigkeiten pro Version, von der neuesten zur ältesten. Angezeigt in
// „Neuigkeiten“ (einmal nach jedem Update) und auf der gleichnamigen Seite.
return [

    '0.93.0' => [
        'date' => '2026-10-23',
        'title' => 'Eine neue Startseite',
        'items' => [
            'Die öffentliche Startseite zeigt jetzt alles, was SagaWyn heute kann, in drei Abschnitten: vorbereiten, am Tisch spielen und die Kampagne zwischen den Sitzungen lebendig halten. Kampfkarte, Würfel, Kampagnenkarte, Sounds, Abenteuerbuch und „Meine Bögen“ haben ihren Platz.',
            'Der Link zum Discord der Community erscheint jetzt standardmäßig, oben und unten auf der Startseite, in der Hilfe und in der Fußzeile.',
        ],
    ],

    '0.92.0' => [
        'date' => '2026-10-23',
        'title' => 'KI-Import: Prompt auf Englisch, Sprache nach Wahl',
        'items' => [
            'Der Prompt und der Claude-Skill von „Import mit einer KI vorbereiten“ sind jetzt auf Englisch, der Sprache, der KIs am besten folgen.',
            'Ein Menü „Sprache der importierten Inhalte“ wählt die Sprache, in der die KI Einträge, Szenen, Dokumenttitel und Anleitung schreibt (standardmäßig die Sprache der Oberfläche). Mit dem Skill kann man beim Import auch eine andere Sprache verlangen.',
            'Das Abenteuerbuch erkennt die Abschnitte „Was geplant ist“ und „Was geschah“ in jeder Sprache von SagaWyn.',
        ],
    ],

    '0.89.1' => [
        'date' => '2026-10-23',
        'title' => 'Klarere Auswahl beim Anlegen einer Kampagne',
        'items' => [
            'Unter „Neue Kampagne“ bieten die Listen „Spiel“ und „Welt“ nur noch die einer laufenden oder archivierten Kampagne an. Die einer gelöschten Kampagne erscheinen nicht mehr; ihr Inhalt bleibt über die Suche auffindbar.',
        ],
    ],

    '0.89.0' => [
        'date' => '2026-10-21',
        'title' => 'Rechteerklärung bei jedem Import',
        'items' => [
            'Vor jedem Import (CSV- oder JSON-Datei, Kampagnenarchiv, Spielvorlage) muss ein standardmäßig leeres Kästchen bestätigen, dass Sie die nötigen Rechte an den importierten Inhalten besitzen. Ohne Haken bleibt die Schaltfläche grau und der Server lehnt den Import ab.',
            'Jede Erklärung wird mit Datum, der Version des akzeptierten Textes und dem Ergebnis des Imports gespeichert, ohne Kopie der Dateien. Sie erscheint unter „Meine Daten herunterladen“.',
            'Die Nutzungsbedingungen erhalten einen Abschnitt „Nutzerinhalte und geistiges Eigentum“, erreichbar von jedem Importbildschirm.',
        ],
    ],

    '0.88.0' => [
        'date' => '2026-10-21',
        'title' => 'Kostenlose Zugänge erkennen',
        'items' => [
            'Administrationskonsole, Seite Konten: ein Kästchen „Kostenloser Zugang“ pro Konto, mit optionaler Notiz (Freund, Tester…). Kostenlose Zugänge erscheinen unter der Adresse, haben ihre eigene Summe oben auf der Seite und einen Filter „Anzeigen“.',
        ],
    ],

    '0.87.1' => [
        'date' => '2026-10-20',
        'title' => 'Lesbare Punktnamen auf der Karte',
        'items' => [
            'Im dunklen Design erschienen die Namen der Punkte auf der Kampagnenkarte dunkel auf schwarzem Grund: Sie sind wieder weiß, ebenso die Initiative- und Würfelbanner auf dem Tischbildschirm und die Seitenknöpfe der PDFs.',
        ],
    ],

    '0.87.0' => [
        'date' => '2026-10-20',
        'title' => 'Gespeicherte Würfe für jeden Bogen',
        'items' => [
            'In den Würfeln von „Meine Bögen“ bewahrt „Diesen Wurf speichern“ einen Wurf beim Bogen auf: Jeder Charakter hat eigene Würfe (Feuerball, Angriff…), die man mit einem Tippen lädt, aktualisiert und mit × entfernt.',
        ],
    ],

    '0.86.2' => [
        'date' => '2026-10-20',
        'title' => '„Datei ersetzen“ funktioniert',
        'items' => [
            'Die Schaltfläche „Ersetzen“ blieb nach der Auswahl der neuen Datei ausgegraut: Sie ist wieder anklickbar, sobald die Datei hochgeladen ist.',
        ],
    ],

    '0.86.1' => [
        'date' => '2026-10-20',
        'title' => 'Eine Karte durch ein Bild anderer Größe ersetzen',
        'items' => [
            'Wenn das Dokument als Karte dient, zeigt „Datei ersetzen“ die Größe des aktuellen Bildes und akzeptiert ein Bild anderer Größe. Spielfiguren, Schablonen und Punkte behalten ihre Position in Pixeln: Ein Hinweis warnt, dass sie sich auf dem Bild verschieben.',
        ],
    ],

    '0.86.0' => [
        'date' => '2026-10-20',
        'title' => 'Ein Dokument ändern, ohne es zu löschen',
        'items' => [
            'Auf der Seite eines Dokuments setzt „Datei ersetzen“ eine neue Version an die Stelle der alten: Das Dokument behält Titel, Einstellungen und Verknüpfungen, und die neue Version erscheint sofort in Szenen, Bögen, Regeln, Karten, Hinweisen und bei den Charakteren, die es erhalten haben.',
            'Ein Bild, das als Karte dient, wird durch ein anderes Bild ersetzt; Spielfiguren, Raster und Punkte bleiben an ihrem Platz.',
        ],
    ],

    '0.85.0' => [
        'date' => '2026-10-20',
        'title' => 'Würfeln vom Bogen aus',
        'items' => [
            'Auf einem Charakterbogen würfelt ein kleiner Würfel neben jeder Zahl und jedem Zähler mit diesem Wert: „Unter … würfeln (Schwelle)“ oder „… zum Wurf addieren (Bonus)“. Die Würfelablage öffnet sich wurfbereit, und das Ergebnis geht wie gewohnt an den Tisch.',
            'Auf dem Bogen deines eigenen Charakters bleibt der Wert mit dem Bogen verknüpft: Ändert er sich, folgt der gespeicherte Wurf. Die SL kann auch vom Bogen eines Spielers aus würfeln.',
            '„Meine Bögen“ hat eigene Würfel: derselbe kleine Würfel neben Zahlenwerten und eine Schaltfläche „Würfel“ unten am Bogen. Das Ergebnis erscheint auf dem Bogen, nur für dich sichtbar.',
        ],
    ],

    '0.84.2' => [
        'date' => '2026-10-20',
        'title' => 'Neue Karten für die Demo',
        'items' => [
            'Die Demo hat neue illustrierte Karten: das Küstendorf als Kampagnenkarte, den unterirdischen Kai des Leuchtturms als Unterkarte. Lade sie unter „Meine Kampagnen“ erneut, um sie zu sehen.',
        ],
    ],

    '0.84.1' => [
        'date' => '2026-10-19',
        'title' => 'Mehr Platz in Meine Bögen',
        'items' => [
            'Ein Bogen fasst jetzt bis zu 200 Blöcke, 500 Felder und 30 Reiter. „+ Neue Notiz“ führt nicht mehr zu einer Fehlerseite: an der Grenze erklärt es eine Meldung.',
        ],
    ],

    '0.84.0' => [
        'date' => '2026-10-19',
        'title' => 'Reiter in Meine Bögen',
        'items' => [
            'Unter „Bearbeiten“ teilt „+ Neuer Reiter“ einen Bogen in Reiter, die Sie benennen (Charakter, Zauber, Inventar…), ordnen und entfernen.',
            'Jeder Block wählt seinen Reiter, und jeder Reiter ordnet seine Blöcke auf eigenen drei Spalten an. Ein bestehender Bogen behält alle Blöcke in einem ersten Reiter.',
        ],
    ],

    '0.83.0' => [
        'date' => '2026-10-19',
        'title' => 'Mehrere Notizen und drei Spalten in Meine Bögen',
        'items' => [
            '„+ Neue Notiz“ fügt einen Notizblock hinzu: so viele Sie möchten (Schulden, Gerüchte, Kontakte…). Ihre bisherigen Notizen werden zu einem ersten Block „Notizen“.',
            'Blöcke lassen sich jetzt mit „Blöcke anordnen“ auf drei Spalten platzieren, Notizen eingeschlossen.',
        ],
    ],

    '0.82.0' => [
        'date' => '2026-10-19',
        'title' => 'Eine aktuelle Demo',
        'items' => [
            'Die Demo-Kampagne zeigt jetzt die neuesten Funktionen: Auf der Kampagnenkarte führt der Leuchtturm zu einer Unterkarte, seinem Keller, bereit für einen Kampf mit Spielsteinen, zwei Flächenschablonen und einer Initiativreihenfolge. Jeder Punkt verknüpft mehrere Bögen und mehrere Szenen.',
            'Würfel am Tisch: vorbereitete Würfe, die den Wert vom Bogen übernehmen, ein benannter Bonus und die Würfe aus Sitzung 1, darunter eine Anfrage der SL.',
            'Außerdem: ein Klang aus „Meine Sounds“, eine Szene „Wie geplant gespielt“ und eine mit „Was geschah“, ein Hinweis „Lüge“, ein Hausbogen und eine Hausregel mit ihrer offiziellen Fassung sowie Spieler, die ihr Porträt selbst ergänzen.',
            'Lade die Demo unter „Meine Kampagnen“ erneut, um sie zu entdecken: Eine bereits geladene Kopie bleibt, wie sie ist.',
        ],
    ],

    '0.81.0' => [
        'date' => '2026-10-19',
        'title' => 'Kampagnenkarte: Zoom, Tisch und reichere Orte',
        'items' => [
            'Die Kampagnenkarte lässt sich zoomen (Mausrad, + und −, Zwei-Finger-Geste auf dem Tablet) und durch Ziehen verschieben. Tipp: Ein 4K-Bild (3840 × 2160) bleibt scharf.',
            'Wenn die Karte auf dem Tischbildschirm ist, ersetzt die von einem Punkt geöffnete Unterkarte sie dort, und beim Zurückgehen kehrt die obere Karte zurück. In der Unterkarte hält „Dieser Ort“ die Szenen und Einträge des Punkts einen Klick entfernt.',
            'Ein Punkt kann jetzt mit mehreren Szenen und mehreren Einträgen verknüpft werden: der Ort, seine NSC, alle Szenen, die dort spielen.',
        ],
    ],

    '0.80.0' => [
        'date' => '2026-10-19',
        'title' => 'Blöcke, Notizen und Ankreuzfelder in Meine Bögen',
        'items' => [
            'In „Meine Bögen“ ordnen sich die Felder in Blöcken, die Sie benennen: Werte, Fertigkeiten, Ausrüstung, Zauber…',
            '„Blöcke anordnen“: Ziehen Sie einen Block an seinem Griff oder nutzen Sie die Pfeile, um ihn zu platzieren, wo Sie möchten, in einer oder zwei Spalten.',
            'Jedes Feld kann eine Notiz und ein Ankreuzfeld haben, das Sie während des Spiels ankreuzen: eine gelungene Fertigkeit, ein vorbereiteter Zauber.',
        ],
    ],

    '0.79.0' => [
        'date' => '2026-10-19',
        'title' => 'Demo-Bibliothek',
        'items' => [
            'Neue „Demo-Bibliothek“ unter „Meine Kampagnen“: spielfertige Kampagnen (eine Welt, ein kurzes Szenario, vorgefertigte Charaktere), von denen du mit einem Klick deine eigene Kopie lädst.',
            'Jede Demo eines Autors oder Verlags behält einen Link „Spiel kaufen“, in der Bibliothek und oben in deiner Kopie.',
            'Der Premium-Preis steht jetzt auf dem Abo-Button in den Einstellungen: 4,99 € pro Monat.',
        ],
    ],

    '0.78.0' => [
        'date' => '2026-10-18',
        'title' => 'Was geschah, und das Abenteuerbuch',
        'items' => [
            'Jede Szene hat jetzt zwei Teile: „Was geplant ist“ und „Was geschah“. Hat die Runde den Plan befolgt, kreuzen Sie einfach „Wie geplant gespielt“ an.',
            'Neu in der Kampagne: das „Abenteuerbuch“ mit den gespielten Szenen, Szenario für Szenario, und danach den Zusammenfassungen der Sitzungen, zum Lesen, Drucken oder Speichern als PDF.',
            'Dazu kann eine ausformulierte Fassung kommen, von Ihnen oder von der KI Ihrer Wahl (Text zum Kopieren oder Ihr eigener API-Schlüssel, auf Ihre Kosten). Das Buch bleibt der SL vorbehalten.',
        ],
    ],

    '0.77.0' => [
        'date' => '2026-10-18',
        'title' => 'Ordnen Sie Ihre Bildschirme nach Ihrem Geschmack',
        'items' => [
            'Neue Schaltfläche „Bildschirm anordnen“ auf der Kampagnenseite und im Sitzungsmodus: Ziehen Sie jeden Block an seinem Griff ⠿, mit der Maus oder dem Finger, oder nutzen Sie die Pfeile.',
            'Im Sitzungsmodus kann ein Block von einer Spalte in die andere wechseln: Musik oben, Notizen rechts, ganz wie Sie möchten.',
            '„Ausblenden“ entfernt einen Block, den Sie nicht nutzen, „Anzeigen“ holt ihn zurück, „Standardanordnung“ stellt alles wieder her. Diese Anordnung gehört nur Ihnen: Spieler und Co-SL behalten ihre eigene.',
        ],
    ],

    '0.76.0' => [
        'date' => '2026-10-18',
        'title' => 'Ihr Bogen, auch ohne Netz',
        'items' => [
            'Offline bleibt der Bogen Ihres Charakters nutzbar: Passen Sie Ihre Zähler mit − und + an, ändern Sie die Felder, die der SL Ihnen freigibt, und schreiben Sie Notizen.',
            'Alles wird auf dem Gerät gespeichert und bei wiederhergestellter Verbindung Feld für Feld gesendet: Ein Zähler erhält, was Sie abgezogen oder hinzugefügt haben, und was der SL inzwischen geändert hat (ein enthülltes Geheimnis, ein geändertes Feld), bleibt erhalten.',
            'Nach dem Senden zeigt eine Zusammenfassung, was aktualisiert wurde, und weist auf Felder hin, die der SL gleichzeitig mit Ihnen geändert hat.',
        ],
    ],

    '0.75.0' => [
        'date' => '2026-10-18',
        'title' => 'Die große Kampagnenkarte',
        'items' => [
            'Neue „Kampagnenkarte“: Wähle eine Karte als Übersicht und setze darauf Orte von Interesse, eine Stadt, ein Verlies, den Ort einer Szene.',
            'Ein Punkt kann mit einem Klick eine Unterkarte öffnen, die selbst Punkte hat; über die Brotkrumen geht es zurück bis zur Übersicht.',
            'Mit einer Szene verknüpft zeigen die Punkte, was gespielt, laufend oder noch geplant ist; mit einem Bogen verknüpft öffnen sie ihn. Alles bleibt beim SL, außer den Namen der Punkte „Am Tisch sichtbar“, die der Tischbildschirm zeigt.',
        ],
    ],

    '0.74.0' => [
        'date' => '2026-10-18',
        'title' => 'Ihre anderswo gespielten Charaktere',
        'items' => [
            'Neue Seite „Meine Bögen“: Bewahren Sie die Charaktere auf, die Sie außerhalb von SagaWyn spielen, bei Freunden, im Verein oder anderswo. Nur Sie sehen sie.',
            'Jeder Charakter hat seinen PDF-Bogen, ein Porträt, freie Felder (Texte und Zähler, die Sie während des Spiels mit − und + einstellen) und Ihre Notizen, dazu Spiel und Runde, um ihn wiederzufinden.',
        ],
    ],

    '0.73.0' => [
        'date' => '2026-10-18',
        'title' => 'Eine Klangbibliothek für alle Kampagnen',
        'items' => [
            'Neue Seite „Meine Sounds“: Musik und Ambiente, einmal im Konto gespeichert und dann zu den Kampagnen hinzugefügt, die du leitest. Die Datei wird nicht kopiert und zählt nur einmal für deinen Speicher.',
            'Auf der Seite Sounds einer Kampagne: „Aus meiner Bibliothek“ fügt deine Sounds hinzu, „In meine Bibliothek verschieben“ macht einen Sound anderswo nutzbar, und ein Kästchen legt einen neuen Upload direkt in der Bibliothek ab.',
            'Wird ein Sound, der schon in einer anderen Kampagne hochgeladen wurde, in die Bibliothek verschoben, werden beide Kopien zusammengeführt: Szenen behalten ihre Musik und der Speicher wird frei.',
        ],
    ],

    '0.72.0' => [
        'date' => '2026-10-18',
        'title' => 'Die Anleitung überall',
        'items' => [
            'Die Schaltfläche „?“ gibt es jetzt auch bei Klängen, Beziehungsgraph, Zeitleiste, Spielerfeedback, KI-Assistent, Protokoll und Charakterbogen.',
            'Bei Graph, Zeitleiste und Charakterbogen lesen Spieler eine Anleitung, die für sie geschrieben ist.',
        ],
    ],

    '0.71.1' => [
        'date' => '2026-10-18',
        'title' => 'Mehrere Punkte auf einmal abziehen oder hinzufügen',
        'items' => [
            'Unter jedem Zähler, den der Spieler ändern darf (LP, Munition, Geld…), ein Feld „Betrag“: 8 eingeben, dann −, und der Zähler verliert 8 auf einmal. Standardwert ist 1.',
        ],
    ],

    '0.71.0' => [
        'date' => '2026-10-18',
        'title' => 'Kurzanleitung auf weiteren Seiten',
        'items' => [
            'Der „?“-Knopf erscheint jetzt auch auf dem Blatt (SL-Seite), im Sitzungsmodus, bei Szenarien und Szenen, beim Dateiimport und bei den Mitgliedern der Kampagne.',
            'Die Hilfeseite erklärt jetzt den Rohwurf und Boni vom Charakterblatt.',
        ],
    ],

    '0.70.0' => [
        'date' => '2026-10-18',
        'title' => 'Eine Kurzanleitung auf den kniffligen Seiten',
        'items' => [
            'Ein „?“-Knopf neben dem Titel der Seiten, die sich nicht von selbst erklären, öffnet eine kurze Anleitung: Blatttypen, Felder des Spiels, Tags, Würfel, Karten, Fernbedienung des Tischbildschirms, Hinweise und Enthüllungsverlauf.',
            'Jede Anleitung verweist auf die passende Frage der Hilfeseite, die sich direkt dort öffnet.',
        ],
    ],

    '0.69.0' => [
        'date' => '2026-10-18',
        'title' => 'Bonus und Schwelle vom Bogen',
        'items' => [
            'Beim Würfeln kann der Bonus einen oder mehrere Werte vom Charakterbogen übernehmen (Eigenschaft, Fertigkeit, jedes Zahl- oder Zählerfeld der öffentlichen Zone), ganz oder als Zehner, Hälfte oder Fünftel, zusätzlich zum freien Bonus. Auch die Schwelle kann vom Bogen kommen. Die Werte werden beim Wurf vom Bogen jedes würfelnden Charakters gelesen.',
            'Mehrere benannte Boni (Waffe +2, Wunde −1…) addieren sich im selben Wurf, zusammen mit Werten vom Bogen.',
            'Die fertigen Beispiele verschwinden vom Würfelbrett: Es behält deine gespeicherten Würfe.',
            'Ein gespeicherter Wurf wird mit einem Tipp gelöscht: das × auf seinem Chip oder „Löschen“, wenn er geladen ist.',
        ],
    ],

    '0.68.0' => [
        'date' => '2026-10-17',
        'title' => 'Roher Wurf',
        'items' => [
            'Neue Lesart „Roh“, standardmäßig ausgewählt: Würfelarten und Anzahl wählen (1d20 + 2d6…), würfeln, und jeder Würfel erscheint so, wie er fällt, ohne Summe, ohne Schwelle und ohne andere Regel.',
        ],
    ],

    '0.67.0' => [
        'date' => '2026-10-17',
        'title' => 'Ihre Inhalte, Ihre Rechte',
        'items' => [
            'Die Seite „Datenschutz und Impressum“ stellt klar, dass SagaWyn nicht Eigentümer dessen ist, was Sie dort ablegen: Jede Person bleibt Eigentümerin ihrer Inhalte und ist für das verantwortlich, was sie veröffentlicht.',
            'Neue Abschnitte: „Import und Strukturierung von Inhalten“, „KI-Dienste“, „Rechtswidrige Inhalte melden“, „Verfügbarkeit und Sicherungen“ und „Spiele und Marken“. Die Importseiten verweisen darauf.',
        ],
    ],

    '0.66.0' => [
        'date' => '2026-10-17',
        'title' => 'Gemischte Würfel und eigene gespeicherte Würfe',
        'items' => [
            'Ein Wurf kann mehrere Würfelarten mischen: Tippe einen W20 und dann zwei W6 an, und es wird 1d20 + 2d6 geworfen. Jede Art hat ihre eigene Anzahl, die du ändern oder entfernen kannst.',
            'Die Schwelle gilt entweder für die Summe („Schwelle auf die Summe“, erreicht oder nicht überschritten) oder für jeden Würfel („Schwelle pro Würfel“: zum Beispiel ist jeder Würfel ab 4 ein Erfolg).',
            'Alle, Spielende wie SL, speichern ihre eigenen Würfe unter „Meine Würfe“, laden sie mit einem Tipp und aktualisieren sie, wenn sich ihr Charakter entwickelt. Die SL behält außerdem die „Würfe der Kampagne“ für den ganzen Tisch.',
        ],
    ],

    '0.65.1' => [
        'date' => '2026-10-17',
        'title' => 'Zugriffe überall geprüft',
        'items' => [
            'Eine von Hand eingetippte Adresse öffnet nie eine Kampagne, ein Blatt oder eine Datei, auf die man kein Recht hat. Ein Konto, das nicht Mitglied ist, erhält jetzt „Seite nicht gefunden“, wie bei einer Adresse, die es nicht gibt: Wer Nummern ausprobiert, erfährt nicht einmal, dass eine Kampagne existiert.',
        ],
    ],

    '0.65.0' => [
        'date' => '2026-10-17',
        'title' => 'Würfel am Tisch',
        'items' => [
            'Neue Schaltfläche „Würfel“ unten auf jeder Seite der Kampagne: Anzahl und Art der Würfel (4 bis 100 Seiten oder andere), dann wie sie gelesen werden: addieren, Schwelle erreichen, unter der Schwelle bleiben, Erfolge zählen, die besten oder niedrigsten behalten, als Ziffern lesen. Mit Bonus und Würfeln, die beim Höchstwert (+) oder bei einer 1 (−) nachgewürfelt werden.',
            'Die SL verlangt einen Wurf vom ganzen Tisch oder von einzelnen Charakteren, offen oder diskret. Bei den betroffenen Spielenden öffnet sich ein Fenster; ohne Schwelle gibt jede Person ihre eigene ein und fügt ihren Bonus hinzu, und eine verdeckte Schwelle bleibt bei der SL, die sieht, wer was gewürfelt hat.',
            'Würfe am Tisch rollen einige Sekunden über den Tischbildschirm; ein diskreter Wurf wird nur der SL gezeigt. Die SL speichert ihre üblichen Würfe für den ganzen Tisch, und die Würfel lassen sich unter „Funktionen der Kampagne“ abschalten.',
        ],
    ],

    '0.64.1' => [
        'date' => '2026-10-17',
        'title' => 'Schablonen gemessen wie das Lineal',
        'items' => [
            'Schablonen werden in derselben Einheit wie das Lineal eingestellt (Meter, Fuß…): die „Echte Größe eines Feldes“ unter Raster kalibriert beide, egal wie groß die Felder auf dem Bildschirm sind.',
            'Eine deutlichere Schaltfläche „Löschen“ an jeder Schablone und „Alle Schablonen löschen“ nach dem Zauber.',
            'Jeder Link unter „Aus der Ferne beitreten“ hat seinen QR-Code, den die Spieler mit dem Handy scannen können.',
        ],
    ],

    '0.64.0' => [
        'date' => '2026-10-17',
        'title' => 'Flächenschablonen auf der Karte',
        'items' => [
            'Neue Schablonen auf der Kartenseite: Kreis, Quadrat, Rechteck, Kegel und Linie, in Feldern bemessen (oder in der Einheit des Maßstabs). Man zieht sie über die Karte, dreht sie, verbirgt sie, und die Liste der getroffenen Token erscheint sofort.',
            'Sichtbare Schablonen erscheinen auf dem Tischbildschirm, unter den Token.',
        ],
    ],

    '0.63.0' => [
        'date' => '2026-10-17',
        'title' => 'Gemeinsame Kampfkarte',
        'items' => [
            'Auf dem geteilten Tischbildschirm bewegt jeder Spieler die Spielmarke seines Charakters. In der Karte vertraut „Bewegt von“ einem Spieler eine weitere Spielmarke an: Gefolgsmann, Vertrauter, kontrollierter Gegner…',
            'Neue Initiativereihenfolge, über die Fernbedienung oder die Karte: SC und Gegner mit ihrem Wert, aktueller Zug, Runden. Die SL zeigt sie über dem Bildschirm, wann sie möchte; verborgene Zeilen bleiben bei ihr, und eine Reihe „Ebenfalls anwesend“ zeigt NSC oder Gegenstände ohne Initiative.',
            '„Aus der Ferne beitreten“ in der Fernbedienung: der Bildschirmlink für die Spieler und ein Gastlink ohne Konto, nur lesend und widerrufbar, für jemanden, der nur zuschauen möchte.',
        ],
    ],

    '0.62.0' => [
        'date' => '2026-10-13',
        'title' => 'Die Community auf Discord',
        'items' => [
            'SagaWyn hat einen eigenen Discord-Server: ein Link unten auf jeder Seite und auf der Startseite, um Fragen zu stellen, Ideen vorzuschlagen und andere Spielleitungen zu treffen.',
        ],
    ],

    '0.61.0' => [
        'date' => '2026-10-13',
        'title' => 'Demo-Modus vor dem Kickstarter',
        'items' => [
            'Neuer Demo-Modus, einzuschalten unter Administration › Tarife: Jedes neue Konto erhält den Demo-Tarif mit einer eigenen Kopie der Demo-Kampagne und einem Banner, das die kommende Crowdfunding-Kampagne ankündigt.',
            'Die Demo-Kampagne eines Demo-Kontos ist gesperrt: Alles lässt sich ansehen, nutzbar sind aber nur Sitzung, Notizen, Spielmarken der Karte und Tischbildschirm. Das Konto kann auch eine kleine eigene Kampagne anlegen (50 MB, 3 Mitglieder, ohne KI und Export).',
            'Eine Einladung eines Administrators öffnet ein normales Konto. Unter „Konten“ setzt „Entsperren“ ein Demo-Konto auf den kostenlosen Tarif und startet seine Testphase, die die Demo nicht verbraucht.',
        ],
    ],

    '0.60.0' => [
        'date' => '2026-10-13',
        'title' => 'Hausinhalte',
        'items' => [
            'Neues Kästchen „Hausinhalt“ auf Blättern (NSCs, Zauber, Gegenstände, Orte…): ohne Haken ist das Blatt offiziell. Ein Abzeichen „Haus“ markiert es in der Liste und auf dem Blatt, ein Filter Offiziell / Haus sortiert das Universum.',
            'Für ein Hausblatt oder eine Regel mit Herkunft „Haus“ lässt sich die ersetzte offizielle Version daneben notieren (z. B. Initiative mit W10 statt W20): Sie bleibt im SL-Bereich, eingeklappt unter Blatt oder Regel.',
            'Die Regelliste lässt sich nach Herkunft filtern. Export und Import der Kampagne behalten Hausinhalte und die offizielle Version.',
        ],
    ],

    '0.59.1' => [
        'date' => '2026-10-13',
        'title' => 'Genauere Prüfung vor dem Livegang',
        'items' => [
            'Administration: `sagawyn:check` meldet kein Debug-Logging mehr fälschlich, wenn LOG_LEVEL auf warning steht.',
        ],
    ],

    '0.59.0' => [
        'date' => '2026-10-13',
        'title' => 'Das Logo von Autistic Intelligence',
        'items' => [
            'Das Logo von Autistic Intelligence, dem Herausgeber von SagaWyn, erscheint jetzt unten auf den Seiten, auf dem Anmeldebildschirm und im Impressum. Es folgt dem hellen oder dunklen Design.',
        ],
    ],

    '0.58.0' => [
        'date' => '2026-10-12',
        'title' => 'Ein Hinweis kann eine Lüge sein',
        'items' => [
            'Hinweise: neue Art „Lüge“ neben Gerücht, Hinweis und Wahrheit, mit eigenem Filter. Wie die anderen Arten sieht sie nur die SL: Ein Spieler, dem der Hinweis enthüllt wird, liest nur den Hinweis.',
        ],
    ],

    '0.57.0' => [
        'date' => '2026-10-12',
        'title' => 'Eine ganze Kampagne mit einer KI vorbereiten',
        'items' => [
            'Meine Kampagnen → Kampagne importieren: neuer Link „Archiv mit einer KI vorbereiten“. Mit dem Prompt (oder dem Claude-Skill) liest Ihre KI das Rollenspielbuch und erstellt ein .zip-Archiv: Spiel, Welt, Kampagne, Einträge, Szenen und Dokumente werden in einem Schritt erstellt.',
        ],
    ],

    '0.56.1' => [
        'date' => '2026-10-11',
        'title' => 'Spiel- und Weltseiten auf dem Handy lesbar',
        'items' => [
            'Auf dem Handy steht das Bild eines Spiels oder einer Welt jetzt in voller Breite über dem Namen: Name und Beschreibung werden nicht mehr gequetscht und die Schaltfläche Bearbeiten verdeckt sie nicht mehr.',
        ],
    ],

    '0.56.0' => [
        'date' => '2026-10-11',
        'title' => 'Aus Geheimnissen werden Hinweise',
        'items' => [
            'Die „Geheimnisse“ der Kampagne heißen jetzt „Hinweise“: Seite Hinweise, Bereich auf Einträgen, Szenen und Dokumenten, Sitzungsmodus, Suche, Enthüllungen und Hilfe. An Ihren Daten ändert sich nichts.',
        ],
    ],

    '0.54.0' => [
        'date' => '2026-10-11',
        'title' => 'Spieler bearbeiten ihren Charakter',
        'items' => [
            'Seite Felder: „Vom Spieler bearbeitbar“ lässt sich für eine ganze Gruppe (Eigenschaften, Fertigkeiten…) oder für alle öffentlichen Felder mit einem Klick setzen.',
            'Neue Option auf der Seite Spieler: Die SL kann jeden Spieler Porträt, Zusammenfassung und Beschreibung seines Charakters bearbeiten lassen. Der SL-Bereich bleibt verborgen.',
        ],
    ],

    '0.51.0' => [
        'date' => '2026-10-11',
        'title' => 'Gründer-Abzeichen und Geschenkcodes',
        'items' => [
            'Kickstarter-Unterstützer können in der Verwaltungskonsole als Gründer markiert werden: ein Abzeichen ★ Gründer in den Einstellungen, der Mitgliederliste und auf dem Charakterbogen.',
            'Gründer erhalten zusätzlichen Speicherplatz zu ihrem Tarif, kostenlos oder Premium (standardmäßig 500 MB, einstellbar unter „Tarife“).',
            'Neue Seite „Meine Belohnungen“ (über die Einstellungen): deine Kickstarter-Belohnung, Premium-Geschenkcodes zum Verschenken per Link, mit dem Namen der Person, die jeden eingelöst hat, und ein Feld zum Einlösen eines erhaltenen Codes. Die Monate werden an das laufende Premium angehängt.',
        ],
    ],

    '0.50.1' => [
        'date' => '2026-10-11',
        'title' => 'Zustand: Rollenfilter',
        'items' => [
            'Im Reiter Zustand gibt es einen Filter nach Kontorolle (ohne Administratoren, Administratoren, Premium, Test, kostenlos).',
            'Die Demo-Kampagne wird in den Statistiken nicht mehr mitgezählt.',
        ],
    ],

    '0.50.0' => [
        'date' => '2026-10-10',
        'title' => 'Zustand der Plattform',
        'items' => [
            'Neuer Reiter Zustand in der Verwaltungskonsole: angemeldete Personen und Anmeldungen pro Tag, Woche oder Monat, Spieler pro Kampagne und pro SL, belegter Speicher (Dateien, Datenbank, Festplatte) und Antwortzeit des Servers Stunde für Stunde.',
            'Die Konsole warnt, wenn der Server langsamer wird: mehr als 5 % der Anfragen über einer Sekunde in 24 Stunden.',
        ],
    ],

    '0.49.1' => [
        'date' => '2026-10-10',
        'title' => 'Eine vollständigere Hilfe',
        'items' => [
            'Die Hilfe beantwortet 19 neue Fragen: Spiel, Welt und Kampagne, die Demo, Suche und Links, Tags, Szenarien, Sitzungsmodus, Felder des Spiels, Regeln, Duplizieren, Tausch, wenn ein Spieler geht, das Journal, das Ende von Premium, und für Spieler: Bogen, Notizen, Nachrichten und Feedback.',
        ],
    ],

    '0.49.0' => [
        'date' => '2026-10-10',
        'title' => 'Die Demo zeigt ganz SagaWyn',
        'items' => [
            'Die Demo-Kampagne nutzt jetzt jede Funktion: eine Spielerfigur aus einer vorgefertigten Figur, mit allem, was sie erhalten hat (Bogen, Dokument, Regel, Geheimnis, Gegenstände), die die SL mit „Ansehen als“ betrachten kann; Sitzungsnotizen; eine „Zu spielen“-Liste; angeheftete Bögen; Felder für Verweise, Dokumente und zwei Bogentypen; ein SL-Anhang; Geheimnisse jeder Art; farbige Tags; Titelbilder für Spiel und Welt.',
            'Beim erneuten Laden der Demo wird der bereits angelegte Bogentyp „Fraktion“ wiederverwendet, statt einen zweiten anzulegen.',
        ],
    ],

    '0.48.0' => [
        'date' => '2026-10-10',
        'title' => 'Eine vollständigere Demonstration',
        'items' => [
            'Die Demo-Kampagne „Der Eid von Pierrecendre“ enthält jetzt drei eigene, mit Szenen verknüpfte Klangatmosphären, eine bereits gespielte erste Sitzung mit Zusammenfassung und eine Feedback-Anfrage an die Spieler.',
            'Korrektur: Die Überblendung zwischen zwei Stücken unterbricht die Wiedergabe in manchen Browsern nicht mehr.',
        ],
    ],

    '0.47.0' => [
        'date' => '2026-10-10',
        'title' => 'LoreMundi wird zu SagaWyn',
        'items' => [
            'LoreMundi heißt jetzt SagaWyn, weiterhin herausgegeben von Autistic Intelligence. Every world has a story.',
            'Ihre Kampagnen, Konten und Archive bleiben unverändert: Mit LoreMundi oder CodexFlow erstellte Sicherungen lassen sich weiterhin importieren. Heruntergeladene Dateien beginnen jetzt mit „sagawyn-“.',
        ],
    ],

    '0.46.0' => [
        'date' => '2026-10-10',
        'title' => 'Spieler-Feedback',
        'items' => [
            'Bitten Sie Ihre Spieler am Ende einer Sitzung oder Kampagne um eine Bewertung von 1 bis 5 Sternen, darum, was ihnen gefallen hat und was besser sein könnte („Spieler-Feedback“ in der Kampagne oder auf der Seite einer Sitzung).',
            'Anonyme oder namentliche Antworten nach Wahl der SL: Die Spieler wissen es vor dem Antworten. Sie sehen den Durchschnitt, die Verteilung der Bewertungen und alle Kommentare.',
        ],
    ],

    '0.45.0' => [
        'date' => '2026-10-10',
        'title' => 'Soundbibliothek',
        'items' => [
            'Neue Seite „Sounds“: Laden Sie Ihre Musik und Atmosphären hoch (mp3, ogg, m4a, wav, flac), ordnen Sie sie mit Tags und verknüpfen Sie sie mit Szenen.',
            'Im Sitzungsmodus zeigt der Block „Musik“ zuerst die Sounds der Szene: „Hier“ spielt sie auf Ihrem Gerät, ohne beim Seitenwechsel abzubrechen; „Tisch“ spielt sie auf dem Tischbildschirm, mit Pause, Schleife und Lautstärke aus der Sitzung gesteuert.',
        ],
    ],

    '0.44.0' => [
        'date' => '2026-10-09',
        'title' => 'Bereit für den Livegang',
        'items' => [
            'Die Administration wird in der Konsole gewarnt, wenn die Installation ein blockierendes Problem hat (Debugging aktiv, E-Mail nicht eingerichtet, unverschlüsselte Echtzeit…).',
            'Datenbank und hochgeladene Dateien werden jede Nacht auf dem Server gesichert.',
        ],
    ],

    '0.43.0' => [
        'date' => '2026-10-09',
        'title' => 'Zuverlässigkeit',
        'items' => [
            'Neue automatische Tests prüfen in einem echten Browser die Aktualisierung ohne Echtzeit, „Angemeldet bleiben“, den Link „Zum Inhalt springen“, die Anzeige auf dem Smartphone mit sehr großer Schrift und eine von der Spielleitung abgeschaltete Funktion bei geöffneter Seite.',
        ],
    ],

    '0.42.0' => [
        'date' => '2026-10-09',
        'title' => 'Formatierte Beschreibungen',
        'items' => [
            'Beschreibungen von Spielen, Welten, Szenarien und Dokumenten haben denselben Editor wie die Einträge: fett, kursiv, Zwischenüberschriften, Listen, Zitate und [[ ]]-Links zu Einträgen innerhalb einer Kampagne.',
        ],
    ],

    '0.41.0' => [
        'date' => '2026-10-09',
        'title' => 'Gemeinsame Felder',
        'items' => [
            'Ein Feld kann mehrere Eintragstypen gleichzeitig betreffen, etwa Trefferpunkte für Charaktere und Kreaturen; Spielvorlagen und Felddateien behalten diese Wahl.',
        ],
    ],

    '0.40.0' => [
        'date' => '2026-10-09',
        'title' => 'App und Sicherheit',
        'items' => [
            'Die auf dem Smartphone installierte App hat eine Beschreibung in Ihrer Sprache und ein an runde Android-Symbole angepasstes Icon.',
            'Offline bleiben die Seiten lesbar, und die Bearbeitungsschaltflächen sind ausgegraut, bis das Netz zurück ist.',
            'Der Browser akzeptiert nur noch Skripte, Bilder und Verbindungen von LoreMundi selbst.',
        ],
    ],

    '0.39.0' => [
        'date' => '2026-10-09',
        'title' => 'Konto und Verwaltung',
        'items' => [
            'Probleme lassen sich ohne Konto melden, von der Anmelde- und Registrierungsseite oder der Hilfe aus, mit einer Adresse für die Antwort.',
            'Die Einstellungen zeigen das Startdatum des Abonnements und, solange die Zahlung nicht eingerichtet ist, dass Premium bald verfügbar wird.',
            'Verwaltungskonsole: Anmeldetage statt Anmeldungen, Datum der letzten Anmeldung und eine Warnung, wenn der Push-Versand unmöglich ist oder fehlschlägt.',
        ],
    ],

    '0.38.0' => [
        'date' => '2026-10-09',
        'title' => 'Suche',
        'items' => [
            'Die Suche findet auch andere französische Formen eines Wortes: „lanternes“ findet „lanterne“, „éteinte“ findet „éteintes“.',
            'Die Suche auf der Startseite durchsucht auch Ihre Welten und Spiele, die keiner Kampagne zugeordnet sind.',
        ],
    ],

    '0.37.0' => [
        'date' => '2026-10-09',
        'title' => 'Spieler und Rollen',
        'items' => [
            'Ein enthüllter Eintrag zeigt dem Spieler seine öffentlichen Illustrationen und Dateien sowie seine öffentlichen Beziehungen zu Einträgen, die er kennt.',
            'Spieler haben auf ihrem Bogen einen „Kampagnenverlauf“: Sitzungen, der Runde bekannte gespielte Ereignisse und Nachrichten an die Gruppe.',
            'Co-SL sehen die Spiel- und Weltseiten schreibgeschützt, laden das Archiv und die Spielvorlage herunter und verwalten die Felder, wenn der Eigentümer es unter „Mitglieder“ ankreuzt. Eine herabgestufte Co-SL behält die als SL erhaltenen Benachrichtigungen nicht.',
        ],
    ],

    '0.36.0' => [
        'date' => '2026-10-09',
        'title' => 'Komfort für SL und Sitzung',
        'items' => [
            'Die Seiten eines am Tisch gezeigten PDFs lassen sich über die Fernbedienung, auf der Dokumentseite oder mit den Pfeilen des SL-Bildschirms umblättern, und die Spieler, die dem Bildschirm folgen, blättern mit. Der PDF-Betrachter zeigt „Seite n / N“ und springt zu einer Seite.',
            'Im Sitzungsmodus fügt „Gespieltes Ereignis“ den eingegebenen Text der Chronik hinzu, verknüpft mit der laufenden Sitzung und Szene.',
            'Eine Figur kann jedem ihrer früheren Spieler zurückgegeben werden, der in die Kampagne zurückkehrt; er findet seinen eigenen privaten Austausch mit der SL wieder, ohne den der Spieler dazwischen.',
            'Der Status eines Eintrags steht unter seinem Titel; vorgefertigte Figuren (Tag „vorgefertigt“) stehen bei „Neue Figur“ oben; die Zähler der Tag-Seite listen die Elemente auf; eine mehrfach geladene Demo nummeriert Kampagne, Spiel und Welt; die Zuschauerrolle sagt, dass sie den Tischbildschirm auch ungeteilt sieht.',
        ],
    ],

    '0.35.0' => [
        'date' => '2026-10-09',
        'title' => 'Korrekturen aus der Abschlussabnahme',
        'items' => [
            'Die Seite einer Sitzung hat eine Zusammenfassung der SL und zeigt die gespielten Ereignisse sowie alles, was in der Sitzung enthüllt oder gegeben wurde.',
            'Der Verlauf eines Bogens enthält jetzt auch Beziehungen, angehängte Dateien, Tags und den Status in der Kampagne. Die Seite einer Welt zeigt ihre Geschichte, die eines Spiels seine Bogentypen.',
            'Eine von der SL oder vom Tarif abgeschaltete Funktion ist auch auf offenen Seiten aus, und der Sitzungsmodus öffnet sich, wenn die Karten abgeschaltet sind.',
            'Korrekturen: angegebene Menge bei einem Austausch, nie ausgefülltes Ja/Nein-Feld, übersetzte Seite „nicht gefunden“, auf dem Telefon mit großer Schrift lesbare Seiten, Anmeldungen einmal gezählt, Hilfe aktualisiert.',
        ],
    ],

    '0.34.0' => [
        'date' => '2026-10-08',
        'title' => 'Funktionen pro Kampagne',
        'items' => [
            'Auf der Kampagnenseite hakt die Spielleitung die Funktionen an, die ihr Tisch braucht: Tischbildschirm, Karten, Tausch zwischen Spielern, Graph, Zeitleiste, KI-Assistent. Eine nicht angehakte Funktion verschwindet für alle, ohne dass etwas gelöscht wird; sie kommt zurück, sobald sie wieder angehakt wird.',
        ],
    ],

    '0.33.0' => [
        'date' => '2026-10-08',
        'title' => 'Feinschliff',
        'items' => [
            'Die Suche zeigt das Feld des Eintrags, das das gefundene Wort enthält, mit seinem Namen.',
            'Graph: Überlappende Namen und Beschriftungen werden verschoben oder ausgeblendet; beim Überfahren eines Eintrags erscheinen sie wieder.',
            '„Enthüllen oder geben“: Ein Kästchen „Alle aktiven Charaktere“ wählt den ganzen Tisch auf einmal.',
            'Ein entfernter und erneut eingeladener Spieler erhält seinen früheren Charakter mit einem Klick zurück, auf der Seite Charaktere.',
            'Ohne Echtzeit (Reverb-Server fehlt oder ist aus) aktualisieren sich Glocke, Nachrichten und Bögen alle 30 Sekunden.',
        ],
    ],

    '0.32.0' => [
        'date' => '2026-10-08',
        'title' => 'PDFs und Dokumente',
        'items' => [
            'PDFs öffnen sich in einem integrierten Viewer – gleich auf Computer, Tablet und Smartphone, mit Zoom und Download.',
            'Auf dem Tischbildschirm wird ein PDF Seite für Seite angezeigt, an den Bildschirm angepasst; mit den Pfeiltasten blättern Sie um.',
            'Der Charakterbogen behält seinen ursprünglichen Dateinamen.',
            '„Verwendet von“ nennt das Szenario jeder Szene.',
            'Spiel- und Weltseiten können ein Bild haben.',
        ],
    ],

    '0.31.0' => [
        'date' => '2026-10-08',
        'title' => 'Premium-Funktionen ✦',
        'items' => [
            'Ein kleiner Stern ✦ kennzeichnet Premium-Funktionen. Gehören sie nicht zum Angebot des Kampagneninhabers, bleiben sie sichtbar, ausgegraut, mit einer Erklärung.',
            'Am Ende einer Testphase oder eines Abos wird nichts gelöscht: Kampagnen, Karten, Nachrichten und Dateien bleiben, nur die ✦-Funktionen werden abgeschaltet.',
            'Der Administrator kann einen Geschenkzeitraum (Weihnachten…) anbieten, in dem kostenlose Konten alle Premium-Funktionen haben.',
        ],
    ],

    '0.30.0' => [
        'date' => '2026-10-08',
        'title' => 'Schreiben und Verknüpfungen',
        'items' => [
            'Felder „Langer Text“ und die SL-Notizen der Regeln haben den Rich-Text-Editor und [[ ]]-Verknüpfungen.',
            'Auf seinem Blatt sieht der Spieler lange Texte formatiert, mit Verknüpfungen zu den Blättern, die sein Charakter kennt.',
            'Die Schnellnotiz der Sitzung schlägt Blätter vor, sobald Sie „[[“ tippen.',
            'Kopien werden nummeriert („Kopie 2“, „Kopie 3“), und ein Import übernimmt nie den Namen eines Spiels, einer Welt oder Kampagne, die Sie schon haben.',
        ],
    ],

    '0.29.0' => [
        'date' => '2026-10-08',
        'title' => 'Kontosicherheit',
        'items' => [
            'Eine neue E-Mail-Adresse wird erst übernommen, wenn der an sie gesendete Link angeklickt wurde; danach wird die alte Adresse informiert.',
            'Versuche in den Kontoformularen werden pro Formular und E-Mail-Adresse gezählt, und die Warteseite nennt die Wartezeit in Sekunden.',
            'Nur die Skripte von LoreMundi können in den Seiten laufen.',
            'Beim Löschen des Kontos wird angekündigt, dass Ihre Nachrichten gelöscht werden.',
        ],
    ],

    '0.28.0' => [
        'date' => '2026-10-08',
        'title' => 'E-Mails in den Farben von LoreMundi',
        'items' => [
            'E-Mails (Passwort vergessen, Adressänderung) tragen das Logo und die Farben von LoreMundi.',
            'Jede E-Mail geht in der Sprache des Empfängers hinaus, auch wenn der Administrator sie sendet.',
        ],
    ],

    '0.27.0' => [
        'date' => '2026-10-08',
        'title' => 'Ein öffentliches Schaufenster',
        'items' => [
            'Eine Startseite stellt LoreMundi Besuchern und Suchmaschinen vor, in allen 8 Sprachen.',
            'Die Hilfe ist ohne Konto lesbar und erhält einen Bereich „Ihr Konto“: Angebote, E-Mail-Adresse, Passwort, Zwei-Faktor-Authentifizierung, Daten.',
        ],
    ],

    '0.26.0' => [
        'date' => '2026-10-08',
        'title' => 'Korrekturen aus dem Abnahmetest v0.25.0',
        'items' => [
            'Eine offen gebliebene Seite prüft Ihre Rechte bei jeder Aktion erneut: Ein entfernter Spieler oder herabgestufter Co-SL erhält nichts Neues mehr.',
            'Wer einen Charakter übernimmt, liest nicht mehr das private Gespräch des vorherigen Spielers mit der SL.',
            'Die vorgefertigten Charaktere der Demo-Kampagne werden unter „Neuer Charakter“ angeboten.',
            'Zwei-Faktor-Authentifizierung: Wiederherstellungscodes funktionieren bei der Anmeldung, und der Administrator kann sie bei einem gesperrten Konto entfernen.',
            'Neue LoreMundi-Symbole (Browser-Tab, installierte App, Benachrichtigungen).',
            'Geteilter Tischbildschirm auf dem Handy lesbar; Kopfzeilen von Bögen und Seiten auf Handy und Tablet korrigiert, auch bei großer Schrift.',
            'Korrekturen: Push-Benachrichtigungen ohne Fehler, Schaltflächen des Fensters „Erhalten“, Bogenverweise nach Umbenennung, Tauschmenge, Kartenlineal, Banner „Offline“, übersetzte Fehlermeldungen, Link „Zum Inhalt springen“.',
        ],
    ],

    '0.25.0' => [
        'date' => '2026-10-08',
        'title' => 'Ein Spielbuch mit einer KI importieren',
        'items' => [
            'Unter „Importieren“ liefert „Dateien mit einer KI vorbereiten“ einen Prompt, den Sie mit dem PDF eines Spiels oder Szenarios in die KI Ihrer Wahl einfügen: Sie bereitet die Importdateien (Felder, Karteikarten, Regeln, Szenen) und eine Schritt-für-Schritt-Anleitung vor. Den Prompt gibt es auch als Claude-Skill.',
        ],
    ],

    '0.24.0' => [
        'date' => '2026-10-08',
        'title' => 'Sicherheit und persönliche Daten',
        'items' => [
            '„Mein Konto“ unter Einstellungen: Ändern Sie Ihren Namen, Ihre E-Mail-Adresse (die bisherige Adresse wird benachrichtigt) und Ihr Passwort.',
            'Optionale Zwei-Faktor-Authentifizierung unter Einstellungen: ein Code aus einer App auf Ihrem Telefon, mit Wiederherstellungscodes.',
            '„Meine Daten“ unter Einstellungen: Laden Sie herunter, was LoreMundi über Sie speichert, oder löschen Sie Ihr Konto.',
            'Passwörter mit mindestens 10 Zeichen aus Buchstaben und Ziffern; wer sein Passwort ändert, wird auf seinen anderen Geräten abgemeldet. Die Verwaltungskonsole fragt erneut nach dem Passwort.',
            'Neue Seite „Datenschutz und Impressum“.',
            'Ein aus der Kampagne entfernter oder zum Zuschauer gemachter Spieler spielt seinen Charakter nicht mehr und erhält nichts mehr von dem, was diesem enthüllt wird. Weitere Rechteprüfungen auf dem Server wurden verstärkt.',
        ],
    ],

    '0.23.0' => [
        'date' => '2026-10-08',
        'title' => 'CodexFlow wird zu LoreMundi',
        'items' => [
            'CodexFlow heißt jetzt LoreMundi, herausgegeben von Autistic Intelligence. Every world has a story.',
            'Ihre Kampagnen, Konten und Archive bleiben unverändert: Mit CodexFlow erstellte Sicherungen lassen sich weiterhin importieren. Heruntergeladene Dateien beginnen jetzt mit „loremundi-“.',
        ],
    ],

    '0.22.0' => [
        'date' => '2026-10-08',
        'title' => 'Vollständige Sicherung',
        'items' => [
            'Der Eigentümer kann eine vollständige Sicherung herunterladen: das Kampagnenarchiv mit der Runde (Charaktere, was sie erhalten haben, Sitzungen, Notizen, Nachrichten und Protokoll), ohne Notizen „Nur ich“ und ohne E-Mail-Adressen. Beim Import kommen die Charaktere ohne Spieler zurück, bereit zur Vergabe.',
        ],
    ],

    '0.21.0' => [
        'date' => '2026-10-08',
        'title' => 'Suche von der Startseite',
        'items' => [
            'Außerhalb einer Kampagne durchsucht die Suchleiste alle Ihre Kampagnen, ihre Welten und Spiele, jeweils mit Ihren Rechten: alles als SL, als Spieler das, was Ihr Charakter kennt.',
        ],
    ],

    '0.20.0' => [
        'date' => '2026-10-08',
        'title' => 'Vollständiges „Erwähnt in“, Kopie ohne Status',
        'items' => [
            '„Erwähnt in“ zeigt auch die Zeitleiste, die Geheimnisse und die Felder anderer Einträge, die den Eintrag nennen.',
            'Eine kopierte Kampagne beginnt wieder mit den ursprünglichen Einträgen: Der Status „tot“ oder „gefangen“ wird nicht mehr übernommen, außer Sie setzen das Häkchen, um ihn zu behalten.',
        ],
    ],

    '0.19.0' => [
        'date' => '2026-10-08',
        'title' => 'Geheimnisse und neue Felder',
        'items' => [
            'Jedes Geheimnis hat eine Art (Gerücht, Hinweis oder Wahrheit) und einen Zustand, der sich daraus ergibt, wer es kennt: verborgen, teilweise oder enthüllt. Sie können Geheimnisse nach beidem filtern.',
            'Drei neue Feldtypen: Weblink, Datei (ein Dokument der Kampagne) und Verweis auf einen anderen Eintrag, der auch nach einer Umbenennung verknüpft bleibt.',
            'Eine kopierte Kampagne behält die Verknüpfungen zwischen ihren kopierten Einträgen.',
        ],
    ],

    '0.18.0' => [
        'date' => '2026-10-08',
        'title' => 'Neuer Charakter, Meine Kampagnen, Seiten für Spiel und Welt',
        'items' => [
            'Bekommt ein Spieler einen neuen Charakter, wählt die SL aus, was vom früheren übergeht: Wissen, Informationen, Dokumente und Regeln werden kopiert, Gegenstände wechseln den Besitzer.',
            'Meine Kampagnen: Schaltfläche „Fortsetzen“, Datum der letzten Sitzung, archivierte Kampagnen separat.',
            'Jedes Spiel und jede Welt hat eine eigene Seite: Beschreibung, Kampagnen, Regeln, Dokumente, Felder oder wiederverwendbare Bögen.',
        ],
    ],

    '0.17.0' => [
        'date' => '2026-10-08',
        'title' => 'Formatierter Text und Spielernotizen',
        'items' => [
            'Lange Texte (Beschreibungen, SL-Notizen, Szenen, Regeln, Zeitleiste, Spielernotizen) haben einen Editor mit Fett, Kursiv, Zwischenüberschriften, Listen und Zitaten; „[[“ schlägt weiterhin Bögen zum Verknüpfen vor.',
            'Spieler verknüpfen ihre Notizen mit den Bögen, die ihr Charakter kennt, und nur mit diesen.',
            'Die Seite einer Sitzung zeigt auch die Notizen, die die Spieler darin gemacht haben, außer denen, die sie für sich behalten.',
        ],
    ],

    '0.16.0' => [
        'date' => '2026-10-08',
        'title' => 'Premium-Abonnement und kostenlose Testphase',
        'items' => [
            'Premium über „Einstellungen“: monatliche oder jährliche Zahlung, gesichert durch Stripe, mit Rechnungen und Kündigung im Stripe-Portal. Premium gilt bis zum Ende des bezahlten Zeitraums.',
            'Kostenlose Testphase: sechs Wochen mit allen Funktionen, ab Ihrer ersten Kampagne als SL. Wer nie SL ist, beginnt sie nicht. Die Dauer wird in der Administration eingestellt.',
            'Ein Reiter „Weiterentwicklung“ in der Administration für die Planung der Plattform.',
        ],
    ],

    '0.15.0' => [
        'date' => '2026-10-08',
        'title' => 'Verwaltungskonsole und Tarife',
        'items' => [
            'Eine Verwaltungskonsole: die Konten mit ihrem Tarif, den Abodaten, dem belegten Speicher, ob ein KI-Schlüssel hinterlegt ist, den Kampagnen und Anmeldungen, ohne persönliche Daten. Die Verwaltung legt den Tarif jedes Kontos fest und kann einen Link zum Zurücksetzen des Passworts senden, ohne es je zu sehen.',
            'Drei Tarife: Verwaltung, Premium und kostenlos. Speicher, Anzahl der Kampagnen und Funktionen des kostenlosen Tarifs werden in der Konsole eingestellt; Spielen, Co-SL oder Zuschauer sein zählt nie.',
            'Das Backlog sammelt gemeldete Probleme, Fehler und Verbesserungen mit Status, Priorität und Korrekturversion; dort werden auch die Abnahmeprotokolle Version für Version aufbewahrt. Ihr Tarif steht unter „Einstellungen“.',
        ],
    ],

    '0.14.0' => [
        'date' => '2026-10-08',
        'title' => 'Nach der Abnahme: Tausch mit Bestätigung der SL',
        'items' => [
            'Standardmäßig bestätigt die SL den Tausch zwischen Spielern: Gegenstand oder Wissen wechselt erst nach der Annahme den Besitzer. Ein Kästchen unter „Charaktere der Spieler“ erlaubt den Tausch auch ohne Bestätigung.',
            'Fernbedienung: Beim letzten Element der Szene wird „Weiter“ zu „Beenden“ und leert den Bildschirm.',
            'Übersichtlicheres Journal für bestätigte Gegenstände, klarere Meldungen unter „Problem melden“ und eine einheitliche Anrede in jeder Sprache.',
        ],
    ],

    '0.13.1' => [
        'date' => '2026-10-08',
        'title' => 'Korrekturen aus dem V1-Abnahmetest',
        'items' => [
            'Die Suche der SL findet jetzt auch Geheimnisse, weitergegebene Informationen und Gegenstände, geteilte Notizen der Spieler und Szenen-Tags.',
            'Beim Duplizieren einer Kampagne werden Geheimnisse und die vorbereitete Zeitleiste mitkopiert; ein Link auf einen Eintrag öffnet sich in der Sitzung in einem Seitenfenster, ohne die Sitzung zu verlassen.',
            'Übersetzte Fehlerseiten, Passwort-E-Mail in Ihrer Sprache, Sitzungsmodus und Dokumente auf dem Handy lesbar, ein Menü für ausgeblendete Links auf kleinen Bildschirmen.',
            'Ein ruhender Charakter und ein von der SL bestätigter Gegenstand können vom Spieler nicht mehr geändert werden; das temporäre Lineal der Karte verschwindet von selbst.',
        ],
    ],

    '0.13.0' => [
        'date' => '2026-10-08',
        'title' => 'Ihre eigene KI, ohne Kopieren und Einfügen',
        'items' => [
            'In „Einstellungen“ können Sie einen API-Schlüssel auf Ihren Namen bei Claude (Anthropic), ChatGPT (OpenAI) oder Le Chat (Mistral) speichern. Der KI-Assistent bietet dann „Direkt analysieren“ an: Die Vorschläge kommen ohne Kopieren und Einfügen.',
            'Die Aufrufe stellt der Anbieter Ihrem Konto in Rechnung. Der Schlüssel wird verschlüsselt, nie wieder angezeigt oder exportiert, und der Modus „Text zum Einfügen“ bleibt kostenlos.',
        ],
    ],

    '0.12.0' => [
        'date' => '2026-10-08',
        'title' => 'Ein KI-Assistent, ohne Abo',
        'items' => [
            'Neues Werkzeug „KI-Assistent“ in der Kampagne: LoreMundi bereitet einen Text mit den Sitzungsnotizen und dem Kontext der Kampagne vor, zum Einfügen in die KI Ihrer Wahl. Deren Antwort, hier wieder eingefügt, wird zu Vorschlägen: Zusammenfassung, gespielte Ereignisse, Beziehungen, Status, Kampagnennotizen, Enthüllungen.',
            'Jeder Vorschlag lässt sich annehmen, ändern oder ablehnen. Ohne Sie ändert sich nichts an der Kampagne, und vorgeschlagene Beziehungen oder Status gelten nur für die Kampagne, ohne die geteilte Welt zu berühren.',
        ],
    ],

    '0.11.1' => [
        'date' => '2026-10-08',
        'title' => 'Die Demo in Ihrer Sprache',
        'items' => [
            'Die Demo-Kampagne gibt es jetzt in allen acht Sprachen der Oberfläche. Sie wird in Ihrer Sprache geladen oder in der neben der Schaltfläche gewählten.',
        ],
    ],

    '0.11.0' => [
        'date' => '2026-10-08',
        'title' => 'Eine Demo-Kampagne',
        'items' => [
            'Eine Demo-Kampagne, mit einem Klick aus „Meine Kampagnen“ zu laden: ein erfundenes Spiel, „Brume & Serment“, und eine vollständige Handlung über drei Sitzungen, mit Einträgen, Porträts, Beziehungen, Karte, Geheimnissen, Regeln, Zeitleiste und vorgefertigten Figuren.',
            'Die kleinen Symbolschaltflächen der Kampagnenseite verrutschen beim Überfahren nicht mehr: der Name erscheint als Kurzhinweis über allem anderen.',
            'Schlagwörter werden überall gleich eingegeben, und ein Eintrag bietet direkt unter seinem Titel „Schlagwort hinzufügen“ an.',
        ],
    ],

    '0.10.0' => [
        'date' => '2026-10-07',
        'title' => 'Eine übersichtlichere Kampagnenseite',
        'items' => [
            'Die Kampagnenseite aufgeräumt: der Sitzungsmodus als Banner, vier Vorbereitungsbereiche und die übrigen Werkzeuge als kleine Symbolschaltflächen.',
            'Stimmung des Tischbildschirms, von der Fernbedienung aus wählbar: Nacht, Pergament, Schiefer oder Grimoire.',
        ],
    ],

    '0.9.0' => [
        'date' => '2026-10-07',
        'title' => 'Die Kampagne mitnehmen',
        'items' => [
            'Eine ganze Kampagne als .zip-Archiv exportieren: Spiel, Welt, Einträge, Szenarien, Dokumente, Karten, Geheimnisse, Zeitleiste und Dateien.',
            'Ein Archiv über „Meine Kampagnen“ importieren: Es stellt die Kampagne wieder her, bei Ihnen oder bei einer anderen SL.',
            'Teilbare Spielvorlagen: Eintragstypen, Felder, Schlagwörter und Regeln, ohne Kampagneninhalt.',
        ],
    ],

    '0.8.0' => [
        'date' => '2026-10-07',
        'title' => 'Graph und Zeitleiste',
        'items' => [
            'Beziehungsgraph: alle verbundenen Einträge oder das Netz um einen Eintrag, mit Tiefe und Filter nach Typ.',
            '„Ansehen als“ im Graph: das Netz so, wie ein Charakter es kennt. Spieler öffnen ihn über ihren Charakter.',
            'Zeitleiste: Weltgeschichte, geplante und gespielte Ereignisse, mit freien Daten wie „Tag 3“.',
            'Ein während der Sitzung notiertes gespieltes Ereignis wird der Sitzung und der aktuellen Szene zugeordnet.',
        ],
    ],

    '0.7.0' => [
        'date' => '2026-10-07',
        'title' => 'Rund um den Tisch',
        'items' => [
            'Karten: ein Bild auf dem Tischbildschirm, das Sie zoomen und verschieben, mit optionalem quadratischem Raster und Maßstab.',
            'Optionale Token, mit Einträgen verknüpft (Name und Porträt): verschieben, Größe ändern, den Spielern zeigen oder verbergen.',
            'Temporäres Lineal: Ziehen Sie eine Linie, die Entfernung erscheint in Feldern oder Metern.',
            'Fernbedienung: Vom Telefon aus den Bildschirm leeren, zum nächsten Element der Szene gehen, die Karte steuern.',
            'Spieler: Was die SL Ihnen enthüllt oder gibt, erscheint sofort, ohne Umweg über die Benachrichtigungen.',
        ],
    ],

    '0.6.0' => [
        'date' => '2026-10-07',
        'title' => 'Das Gedächtnis der Kampagne',
        'items' => [
            'Geheimnisse: eine eigenständige Information, verknüpft mit Einträgen, Szenen oder Dokumenten, mit einem Klick einem Charakter oder dem ganzen Tisch enthüllt.',
            'Enthüllungsverlauf: wer was wann erfahren hat, in welcher Sitzung und welcher Szene; jede Enthüllung lässt sich rückgängig machen.',
            '„Ansehen als“: Die SL sieht die Kampagne genau wie ein Charakter, schreibgeschützt.',
            '„Erwähnt in“ zeigt jetzt auch die Regeln und Sitzungsnotizen, die einen Eintrag erwähnen.',
            'Beziehungen: Die Gegenrichtung („arbeitet für“ / „beschäftigt“) wird automatisch ausgefüllt.',
        ],
    ],

    '0.5.0' => [
        'date' => '2026-10-07',
        'title' => 'Besser ordnen, gemeinsam',
        'items' => [
            'Eine Seite „Tags“ zum Umbenennen, Einfärben, Zusammenführen und Löschen Ihrer Tags; auch Szenen haben jetzt Tags.',
            '„Duplizieren“ Sie einen Eintrag, ein Szenario oder eine ganze Kampagne, um mit einer anderen Runde erneut zu spielen.',
            'Neue Rollen: Co-SL, die mit Ihnen vorbereitet und mitleitet, und Zuschauer, die den Tischbildschirm sehen.',
            '„Am Tisch zeigen“ aus einem Eintrag, einem Porträt, einer Illustration, einem Dokument oder einer Regel.',
            'Sitzungsmodus: einen Eintrag oder eine Regel mit einem Klick zeigen, die nächste Szene sehen, Taste N für Notizen.',
        ],
    ],

    '0.4.1' => [
        'date' => '2026-10-07',
        'title' => 'Hilfe und Problemmeldungen',
        'items' => [
            'Eine „Hilfe“-Seite beantwortet die häufigsten Fragen, für die SL wie für die Spieler.',
            '„Problem melden“ unten auf jeder Seite schickt Ihre Nachricht mit der betroffenen Seite an das Team.',
        ],
    ],

    '0.4.0' => [
        'date' => '2026-10-07',
        'title' => 'Alle Sprachen',
        'items' => [
            'Die Oberfläche spricht Französisch, Englisch, Deutsch, Spanisch, Italienisch, Portugiesisch, Niederländisch und Polnisch.',
            'Die Sprache folgt dem Browser; jeder kann sie unter „Einstellungen“ wählen.',
            'Benachrichtigungen kommen in der Sprache der Person an, die sie erhält.',
            'Das dunkle Design bleibt beim Seitenwechsel erhalten.',
            'Nach dem Bearbeiten eines Eintrags von der Seite Charaktere kehren Sie direkt dorthin zurück.',
        ],
    ],

    '0.3.0' => [
        'date' => '2026-10-07',
        'title' => 'Die lebendige Verbindung',
        'items' => [
            'Nachrichten zwischen der SL und den Spielern, und ein „Chat“-Bereich, der immer griffbereit ist (Gruppe und privat).',
            'Benachrichtigungen: Enthüllungen, erhaltene Gegenstände, Nachrichten, mit einem Zähler in der Kopfzeile.',
            'Alles aktualisiert sich live: Nachrichten, Zähler, Enthüllungen, ohne die Seite neu zu laden.',
            'LoreMundi lässt sich als App installieren; der Charaktereintrag bleibt offline lesbar; Benachrichtigungen auf dem Gerät.',
            'Tischbildschirm: Karten, Bilder, Einträge und Ansagen auf dem Fernseher oder Beamer, auf Wunsch der SL auch mit den Spielern geteilt.',
            'Charaktere geben einander Gegenstände und geben weiter, was sie wissen.',
            'Spieler notieren ihr Wissen und fügen ihre Gegenstände hinzu; die SL bestätigt die Gegenstände.',
            'Dunkles Design, Akzentfarbe und Textgröße unter „Einstellungen“.',
        ],
    ],

    '0.2.0' => [
        'date' => '2026-10-07',
        'title' => 'Die Spieler',
        'items' => [
            'Einladung der Spieler per Link.',
            'Charaktere der Spieler: Eintrag, PDF-Bogen, Zähler (TP, Magie, Munition …) und vom Spieler änderbare Felder.',
            'Enthüllungen und „Geben“: Wissen, Besitz, Dokumente und Regeln.',
            'Spielerbereich: private oder geteilte Notizen, „Zu spielen“-Absichten, Journal des Charakters.',
            'Änderungsprotokoll: wer, was, wann, vorher und nachher.',
        ],
    ],

    '0.1.0' => [
        'date' => '2026-10-06',
        'title' => 'Die SL allein',
        'items' => [
            'Welten, Kampagnen und Einträge mit öffentlichem Bereich und SL-Bereich, [[ ]]-Verknüpfungen zwischen Einträgen.',
            'Freie Felder pro Spiel, Eintragstypen, CSV/JSON-Import.',
            'Szenarien, Szenen, Regeln und Dokumentenbibliothek.',
            'Sitzungsmodus: laufende Szene, nützliche Einträge, Schnellnotizen, „Zu spielen“ und angeheftete Einträge.',
            'Globale Suche.',
        ],
    ],

];
