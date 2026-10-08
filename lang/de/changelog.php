<?php

// Neuigkeiten pro Version, von der neuesten zur ältesten. Angezeigt in
// „Neuigkeiten“ (einmal nach jedem Update) und auf der gleichnamigen Seite.
return [

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
            'Neues Werkzeug „KI-Assistent“ in der Kampagne: CodexFlow bereitet einen Text mit den Sitzungsnotizen und dem Kontext der Kampagne vor, zum Einfügen in die KI Ihrer Wahl. Deren Antwort, hier wieder eingefügt, wird zu Vorschlägen: Zusammenfassung, gespielte Ereignisse, Beziehungen, Status, Kampagnennotizen, Enthüllungen.',
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
            'CodexFlow lässt sich als App installieren; der Charaktereintrag bleibt offline lesbar; Benachrichtigungen auf dem Gerät.',
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
