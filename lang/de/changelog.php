<?php

// Neuigkeiten pro Version, von der neuesten zur ältesten. Angezeigt in
// „Neuigkeiten“ (einmal nach jedem Update) und auf der gleichnamigen Seite.
return [

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
