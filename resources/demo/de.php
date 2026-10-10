<?php

/*
 * Demonstrationskampagne, deutscher Text (Übersetzung von fr.php).
 *
 * Hier steht nur der Text: Die Struktur (wer mit wem verknüpft ist, Marker, Links) liegt in
 * App\Actions\Demo\LoadDemoCampaign und ist in allen Sprachen gleich. Jede Übersetzung
 * übernimmt genau diese Schlüssel. In Szenen bezeichnet „[[schlüssel]]“ ein Blatt über seinen Schlüssel.
 */
return [
    'campaign' => [
        'name' => 'Der Eid von Pierrecendre',
        'description' => 'Demonstrationskampagne: drei Sitzungen in der Hafenstadt Pierrecendre, wo ein vergessener Eid zurückkehrt, um einzufordern, was ihm zusteht. Alle Inhalte sind eigenständig und frei von Rechten Dritter.',
    ],
    'game' => [
        'name' => 'Nebel & Eid',
        'description' => 'Ein Spiel um Ermittlungen und Eide, erfunden für diese Demonstration. Vier Eigenschaften von 1 bis 5, Eide, die auf den Würfen lasten, keine geschützten Spielmechaniken.',
    ],
    'world' => [
        'name' => 'Vehrmund',
        'description' => 'Ein Archipel aus ertrunkenen Marken und auf Asche gebauten Häfen, in dem ein gegebenes Wort so viel gilt wie ein Vertrag.',
    ],

    'types' => [
        'faction' => 'Fraktion',
        'pregen' => 'Vorgefertigte Figur',
    ],

    'groups' => [
        'traits' => 'Eigenschaften',
        'profile' => 'Profil',
        'secrets' => 'Geheimnisse',
    ],

    'fields' => [
        'body' => 'Körper',
        'skill' => 'Geschick',
        'mind' => 'Geist',
        'heart' => 'Herz',
        'breath' => 'Atem',
        'oaths' => 'Gehaltene Eide',
        'trade' => 'Beruf',
        'trait' => 'Markanter Zug',
        'ties' => 'Bindungen',
        'hidden_oath' => 'Verborgener Eid',
        'betrayal' => 'Was ihn zum Verrat brächte',
    ],

    'tags' => [
        'city' => 'stadt',
        'act1' => 'akt 1',
        'act2' => 'akt 2',
        'act3' => 'akt 3',
        'intrigue' => 'intrige',
        'hall' => 'halle',
        'quays' => 'kais',
        'marshes' => 'marken',
        'guard' => 'wache',
        'pregen' => 'vorgefertigt',
        'base' => 'grundregeln',
        'oaths' => 'eide',
        'house' => 'hausregel',
        'ambience' => 'Atmosphäre',
    ],

    'quay_state' => [
        'status' => 'unter Ausgangssperre',
        'notes' => 'Seit Gueffroy ertrunken ist, nachts gesperrt.',
    ],

    'entities' => [
        'city' => [
            'name' => 'Pierrecendre',
            'summary' => 'Hafenstadt, erbaut auf dem Ascheström eines erloschenen Vulkans.',
            'description' => "Fünfzehntausend Seelen, zwei Hügel und eine halbmondförmige Bucht. Man lebt hier von Salz, Glas und Eiden: Jeder Vertrag, der in der Halle geschlossen wird, wird in eine Tafel aus verglaster Asche graviert.\n\nDie Stadt riecht nach Tang und kaltem Schwefel. Die oberen Gassen gehören den Handelshäusern, die unteren denen, die vom Wasser leben.",
            'gm_notes' => 'Die wahre Macht liegt bei der Halle, nicht bei der Wache. Bedrohen die Spieler die Wache, gibt Mornevent nach; bedrohen sie die Halle, verschließt sich die ganze Stadt.',
        ],
        'hall' => [
            'name' => 'Die Halle der Eide',
            'summary' => 'Ein Bau aus hellem Stein, in dem die Eide der Stadt graviert und verwahrt werden.',
            'description' => 'Ein Kirchenschiff ohne Gott, voller Regale mit verglasten Tafeln. Jede Tafel trägt einen Eid, seinen Tag und seine Zeugen. Man tritt barhäuptig ein und geht gebunden hinaus.',
            'gm_notes' => 'Die Tafeln aus dem Jahr des Großen Nebels wurden entfernt. Elzevir weiß, wo sie sind: im Keller des Leuchtturms, nicht in der Halle.',
        ],
        'quay' => [
            'name' => 'Der Laternenkai',
            'summary' => 'Der Kai der Fischer, die ganze Nacht erhellt von Laternen mit Fischtran.',
            'description' => 'Dreißig Laternen, in der Dämmerung entzündet von einem Jungen, der wochenweise bezahlt wird. Erlischt eine, gehen die Alten nach Hause, ohne ihr Glas zu leeren.',
            'gm_notes' => 'Die dritte Laterne von Norden wird nie wieder angezündet: Sie ist das Zeichen des Fährmanns.',
        ],
        'marshes' => [
            'name' => 'Die Ertrunkenen Marken',
            'summary' => 'Salzsümpfe zwischen Pierrecendre und dem Festland, bei Ebbe begehbar.',
            'description' => 'Drei Stunden sicherer Weg pro Ebbe, sonst zwölf Stunden Warten. Eingerammte Stangen markieren die Furt; jemand versetzt sie.',
            'gm_notes' => 'Die Eidbrüchigen versetzen die Stangen, damit Reisende sich verirren und verschwinden.',
        ],
        'lighthouse' => [
            'name' => 'Der Leuchtturm von Orvent',
            'summary' => 'Verlassener Leuchtturm auf der Südspitze, dessen Laterne in manchen Nächten noch immer brennt.',
            'description' => 'Zweiunddreißig Meter Stein, eine Wendeltreppe, ein Keller, der bei Flut unter Wasser steht.',
            'gm_notes' => 'Die verschwundenen Tafeln der Halle liegen im Keller, in einer Salzkiste. Der Unbekannte bewacht sie.',
        ],
        'ysane' => [
            'name' => 'Herrin Ysane Korr',
            'summary' => 'Hüterin der Eide: Sie graviert die Tafeln und bezeugt die Verträge.',
            'description' => 'Sechzig Jahre alt, die Hände vom Glasofen verbrannt, ein Gedächtnis, dem niemand zu widersprechen wagt.',
            'gm_notes' => 'Sie hat die Tafeln aus dem Jahr des Großen Nebels entfernen lassen: Ihr eigener Name steht auf einer davon. Sie ist nicht böse, sie hat Todesangst.',
            'fields' => [
                'trade' => 'Hüterin der Eide',
                'trait' => 'Sieht niemandem zweimal in die Augen',
                'hidden_oath' => 'Hat vor dreißig Jahren geschworen, den Ertrunkenen jedes Jahr ein Boot zu überlassen. Seitdem hat die Stadt keinen Schiffbruch mehr erlebt.',
                'betrayal' => 'Die Sicherheit ihrer Enkelin',
            ],
        ],
        'brannoc' => [
            'name' => 'Brannoc der Fährmann',
            'summary' => 'Bringt Leute und Kisten durch die Marken, zu der Stunde, die ihm passt.',
            'description' => 'Groß, bedächtig, redet wenig und rechnet schnell. Kennt die Furt auswendig, auch wenn sie versetzt wurde.',
            'gm_notes' => 'Er weiß, dass die Stangen wandern. Er schweigt, bis man ihm anbietet, seine Schuld beim Grauen Faden abzulösen.',
            'fields' => [
                'trade' => 'Fährmann',
                'trait' => 'Schwört nie, was in der Stadt als Beleidigung gilt',
                'hidden_oath' => 'Schuldet der Kompanie vom Grauen Faden elf Jahre freie Überfahrten.',
                'betrayal' => 'Der Erlass seiner Schuld',
            ],
        ],
        'elzevir' => [
            'name' => 'Meister Elzevir',
            'summary' => 'Archivar der Halle, der selbst die ältesten Tafeln zu lesen vermag.',
            'description' => 'Klein, von Asche gepudert, unfähig zu lügen, ohne zu husten.',
            'gm_notes' => 'Er hat die entfernten Tafeln abgeschrieben, bevor man sie fortbrachte. Seine Abschrift steckt im Futter seines Mantels.',
            'fields' => [
                'trade' => 'Archivar',
                'trait' => 'Hustet, wenn er lügt',
                'hidden_oath' => 'Hat Ysane geschworen, nie über das Jahr des Großen Nebels zu sprechen.',
                'betrayal' => 'Das Versprechen, dass die Tafeln an ihren Platz zurückkehren',
            ],
        ],
        'vanne' => [
            'name' => 'Schwester Vanne',
            'summary' => 'Pflegt Ertrunkene und Verbrannte, ohne zu fragen, auf welcher Seite sie stehen.',
            'description' => 'Führt einen Saal mit sechs Betten über einer Seilerei.',
            'gm_notes' => 'Sie hat letzte Woche zwei Eidbrüchige gepflegt. Sie verrät es nur gegen Salz und Verbandszeug.',
            'fields' => [
                'trade' => 'Heilerin',
                'trait' => 'Nennt jeden „Kleines“',
            ],
        ],
        'mornevent' => [
            'name' => 'Hauptmann Hald Mornevent',
            'summary' => 'Befehligt die Kaiwache: zweiundzwanzig Mann und ein Boot.',
            'description' => 'Fähig, müde und sich vollkommen bewusst, dass ihm die Mittel für sein Amt fehlen.',
            'gm_notes' => 'Er vertuscht das Verschwinden dreier Reisender, um die Stadt nicht in Panik zu versetzen. Er nimmt Hilfe an, wenn man sie ihm ohne Zuschauer anbietet.',
            'fields' => [
                'trade' => 'Hauptmann der Wache',
                'trait' => 'Schreibt alles in ein Notizbuch, das er nie wieder liest',
                'hidden_oath' => 'Hat dem Rat versprochen, dass unter seinem Befehl niemand verschwindet.',
                'betrayal' => 'Vor dem Rat das Gesicht wahren',
            ],
        ],
        'stranger' => [
            'name' => 'Der Unbekannte vom Leuchtturm',
            'summary' => 'Der, der die Laterne des Leuchtturms von Orvent wieder entzündet. Niemand hat ihn je aus der Nähe gesehen.',
            'gm_notes' => 'Es ist Gueffroy, der Laternenjunge, vor sechs Monaten ertrunken und von den Ertrunkenen zurückgegeben. Er bewacht die Tafeln und wartet nur auf eines: dass jemand seinen Namen laut ausspricht.',
            'fields' => [
                'trade' => 'Laternenanzünder',
                'trait' => 'Riecht nach kaltem Salz',
                'hidden_oath' => 'Hat im Sterben geschworen, die Laternen so lange wieder anzuzünden, bis man ihm seinen Namen zurückgibt.',
            ],
        ],
        'drowned' => [
            'name' => 'Die Ertrunkenen',
            'summary' => 'Was aus den Marken heraufsteigt, wenn der Nebel länger als drei Tage hält.',
            'description' => 'Man beschreibt sie als Gestalten, die unter dem flachen Wasser gehen, in Mannshöhe.',
            'gm_notes' => 'Sie töten nicht: Sie fordern ein. Ein Ertrunkener lässt seine Beute los, wenn jemand an ihrer Stelle den Eid hält, den er holen kam.',
        ],
        'seal' => [
            'name' => 'Das Aschesiegel',
            'summary' => 'Der Stempel, mit dem die Tafeln der Halle graviert werden. Ohne ihn ist kein Eid gültig.',
            'description' => 'Ein schwerer Zylinder aus schwarzem Glas, in den das Wappen der Stadt vertieft eingraviert ist.',
            'gm_notes' => 'Ysane hat es versteckt. Wird es öffentlich gemacht, endet die Kampagne mit Verhandlungen; wird es zerstört, endet sie mit dem Bruch.',
        ],
        'greythread' => [
            'name' => 'Die Kompanie vom Grauen Faden',
            'summary' => 'Ein Handelshaus, das Schulden aufkauft und Dienste weiterverkauft.',
            'description' => 'Drei Kontore, kein eigenes Schiff und ein Schuldbuch, dicker als das Register der Stadt.',
            'gm_notes' => 'Will das Aschesiegel: Wer die Eide graviert, bestimmt den Preis der Schulden.',
        ],
        'broken' => [
            'name' => 'Die Eidbrüchigen',
            'summary' => 'Die, die einen Eid gebrochen haben und nun außerhalb der Stadt leben, in den Marken.',
            'gm_notes' => 'Sie versetzen die Stangen, damit die Stadt endlich das Wasser fürchtet. Ihre Anführerin ist Ysanes Tochter.',
        ],
        'guard' => [
            'name' => 'Die Kaiwache',
            'summary' => 'Zweiundzwanzig Mann, zuständig für Hafen, Laternen und Ausgangssperre.',
            'gm_notes' => 'Zwei von ihnen werden vom Grauen Faden bezahlt. Mornevent weiß es nicht.',
        ],
        'teska' => [
            'name' => 'Teska die Ruderin',
            'summary' => 'Rudert seit ihrer Kindheit und kennt die Bucht besser als die Wache.',
            'description' => 'Sie haben Ihrem Bruder geschworen, Pierrecendre niemals zu verlassen. Er ist letzten Monat gegangen.',
            'fields' => [
                'trade' => 'Ruderin',
                'trait' => 'Sagt alles, und zwar sofort',
                'ties' => 'Ihr Bruder, ohne ein Wort fortgegangen. Brannoc, der ihr ein Boot schuldet.',
            ],
        ],
        'oriel' => [
            'name' => 'Oriel Chantegrèle',
            'summary' => 'Berufszeuge: Man bezahlt ihn dafür, Eiden beizuwohnen und sich an sie zu erinnern.',
            'description' => 'Sie haben zweihundert Eide bezeugt. Einen einzigen haben Sie vergessen, mit Absicht.',
            'fields' => [
                'trade' => 'Zeuge',
                'trait' => 'Wiederholt wichtige Sätze leise für sich',
                'ties' => 'Meister Elzevir, sein Lehrmeister. Der Graue Faden, der ihn zu oft in Dienst nimmt.',
            ],
        ],
        'dorn' => [
            'name' => 'Dorn Kalteisen',
            'summary' => 'Ehemaliger Wächter, entlassen, weil er sich weigerte, eine Ausgangssperre durchzusetzen.',
            'description' => 'Sie haben geschworen, nie wieder einem Befehl zu gehorchen, den Sie nicht verstehen.',
            'fields' => [
                'trade' => 'Entlassener Wächter',
                'trait' => 'Stellt sich immer zwischen die Tür und die anderen',
                'ties' => 'Mornevent, der ihn schweren Herzens entlassen hat. Schwester Vanne, die ihn zweimal zusammengeflickt hat.',
            ],
        ],
        'lisenn' => [
            'name' => 'Lisenn mit den zwei Namen',
            'summary' => 'Stammt aus den Marken und lebt in der Stadt unter einem Namen, der nicht ihr eigener ist.',
            'description' => 'Sie haben einen Eid gebrochen. Hier weiß es noch niemand.',
            'fields' => [
                'trade' => 'Führerin durch die Marken',
                'trait' => 'Schläft nie zwei Nächte am selben Ort',
                'ties' => 'Die Eidbrüchigen, die sie verlassen hat. Lisenn, die Tote, deren Namen sie trägt.',
            ],
        ],
    ],

    // Bezeichnung der Beziehung, dann ihre Gegenrichtung (null: keine Gegenrichtung).
    'relations' => [
        'ysane_hall' => ['hütet', 'gehütet von'],
        'elzevir_hall' => ['arbeitet in', 'beschäftigt'],
        'elzevir_ysane' => ['hat Schweigen geschworen gegenüber', 'hält durch einen Eid gebunden'],
        'brannoc_marshes' => ['kennt die Furt durch', 'durchquert von'],
        'brannoc_greythread' => ['steht in der Schuld von', 'hält die Schuld von'],
        'mornevent_guard' => ['befehligt', 'befehligt von'],
        'guard_quay' => ['bewacht', 'bewacht von'],
        'greythread_guard' => ['kauft zwei Männer von', null],
        'greythread_seal' => ['begehrt', 'begehrt von'],
        'broken_marshes' => ['leben in', 'beherbergen'],
        'broken_ysane' => ['werden von ihrer Tochter angeführt', null],
        'drowned_marshes' => ['steigen herauf aus', null],
        'drowned_ysane' => ['haben einen Eid mit', null],
        'stranger_lighthouse' => ['entzündet wieder', 'wieder entzündet von'],
        'stranger_quay' => ['zündete die Laternen an von', null],
        'vanne_broken' => ['hat zwei von ihnen gepflegt', null],
        'hall_city' => ['steht in', 'beherbergt'],
        'quay_city' => ['säumt', 'öffnet sich auf'],
        'seal_hall' => ['graviert die Tafeln von', null],
        'teska_brannoc' => ['hat ihm ein Boot geliehen', 'schuldet ihr ein Boot'],
        'oriel_elzevir' => ['war sein Lehrling', 'hat ausgebildet'],
        'dorn_mornevent' => ['diente unter', 'hat entlassen'],
        'lisenn_broken' => ['hat sie verlassen', null],
    ],

    'documents' => [
        'tile' => [
            'title' => 'Tafel 1147 – Der Eid des Großen Nebels',
            'description' => 'Die Abschrift, die Elzevir anfertigte, bevor die Tafeln die Halle verließen.',
            'lines' => [
                'Abschrift der Tafel 1147, Halle der Eide zu Pierrecendre.',
                '',
                'Schwörende: Ysane Korr, Hüterin.',
                'Eid: „Ein Boot im Jahr, und die Bucht bleibt ruhig.“',
                'Zeugen: Elzevir, Archivar. Gueffroy, Laternenanzünder.',
                '',
                'Vermerk des Archivars: Tafel am 3. des Salzmonds aus dem Regal entfernt.',
            ],
        ],
        'notice' => [
            'title' => 'Bekanntmachung der Ausgangssperre',
            'description' => 'Am Laternenkai angeschlagen. Den Spielern gleich in der ersten Szene zeigen.',
            'lines' => [
                'Auf Befehl von Hauptmann Hald Mornevent, Kaiwache.',
                '',
                'Der Laternenkai ist von der letzten Laterne bis zum Morgengrauen gesperrt.',
                'Niemand sticht ohne Passierschein der Wache in See.',
                'Jede erloschene Laterne ist der Wachstube zu melden.',
                '',
                'Diese Bekanntmachung gilt als Eid: Wer sie bricht, steht vor der Halle Rede und Antwort.',
            ],
        ],
        'tides' => [
            'title' => 'Gezeitentafel der Ertrunkenen Marken',
            'description' => 'Spielhilfe: drei Stunden Furt bei jeder Ebbe.',
            'lines' => [
                'Ertrunkene Marken – Querung der Furt',
                '',
                'Ebbe: drei Stunden sicherer Weg, Stangen sichtbar.',
                'Auflaufendes Wasser: eine Stunde Aufschub, Wasser bis zum halben Oberschenkel.',
                'Flut: kein Durchkommen. Zwölf Stunden Warten.',
                '',
                'Die Stangen werden jeden Monat von der Wache neu gesetzt.',
            ],
        ],
        'plan' => [
            'title' => 'Hafenplan von Pierrecendre',
            'description' => 'Der Hafen, seine Kais und die Landspitze mit dem Leuchtturm. Darf am Tisch gezeigt werden.',
            'file' => 'hafenplan',
        ],
    ],

    'map' => [
        'name' => 'Der Hafen von Pierrecendre',
        'unit' => 'm',
    ],

    'rules' => [
        'roll' => [
            'title' => 'Eidwurf',
            'category' => 'Grundregeln',
            'summary' => 'Eigenschaft + 1W6 gegen eine Schwierigkeit von 4 bis 9.',
            'procedure' => "1. Nennen Sie die eingesetzte Eigenschaft und was die Figur erreichen will.\n2. Werfen Sie 1W6 und addieren Sie die Eigenschaft.\n3. 4 für eine Aufgabe des eigenen Berufs, 7 für eine schwierige Aufgabe, 9 für das Unmögliche.\n4. Handelt die Figur, um einen Eid zu halten, gibt es +1 pro gehaltenem Eid, höchstens +3.",
            'source' => 'Grundheft, S. 12',
        ],
        'breath' => [
            'title' => 'Atem',
            'category' => 'Grundregeln',
            'summary' => 'Atem ersetzt Lebenspunkte: Man gibt ihn aus, um durchzuhalten, nicht um Schaden einzustecken.',
            'procedure' => "Geben Sie 1 Atem aus, um einen Würfel neu zu werfen, um trotz einer Verletzung weiterzumachen oder um einem Ertrunkenen zu widerstehen.\nBei 0 hält die Figur inne: Sie ist nicht tot, kann aber bis zur nächsten Rast nichts mehr versprechen.",
            'source' => 'Grundheft, S. 18',
        ],
        'breaking' => [
            'title' => 'Einen Eid brechen',
            'category' => 'Eide',
            'summary' => 'Ein gebrochener Eid bringt einen sofortigen Vorteil und einen bleibenden Preis.',
            'procedure' => "Der Spieler beschreibt, was der Bruch ihm ermöglicht: Er bekommt es, ohne Wurf.\nDann verliert er alle gehaltenen Eide, und der Tisch notiert, wer davon erfahren hat.",
            'gm_notes' => 'Einen Bruch niemals verweigern. Der Preis wird in der Fiktion bezahlt, durch die Reaktion derer, die davon erfahren.',
            'source' => 'Grundheft, S. 24',
        ],
        'mist' => [
            'title' => 'Nebelzähler',
            'category' => 'Hausregel',
            'summary' => 'Hausregel: Der Nebel steigt mit jeder Sitzung um eine Stufe, bis die Ertrunkenen durch die Stadt gehen.',
            'procedure' => "Führen Sie einen Zähler von 0 bis 6, für den Tisch sichtbar.\n+1 am Ende jeder Sitzung, +1 jedes Mal, wenn ein Eid vor Zeugen gebrochen wird.\nBei 3 wird die Furt unsicher. Bei 6 betreten die Ertrunkenen Pierrecendre.",
            'gm_notes' => 'Zähler auf dem Spielleiterschirm, bis 3 geheim.',
        ],
        'word' => [
            'title' => 'Am Tisch gegebenes Wort',
            'category' => 'Hausregel',
            'summary' => 'Zum Testen: Ein Versprechen, das der Spieler laut ausspricht, zählt als Eid.',
            'procedure' => 'Verspricht ein Spieler einer Figur etwas, notieren Sie es. Hält er es, +1 gehaltener Eid; andernfalls greift die Regel zum Eidbruch.',
            'gm_notes' => 'In Sitzung 2 testen. Risiko: Die Spieler trauen sich nichts mehr zu versprechen.',
        ],
    ],

    'scenario' => [
        'name' => 'Der Eid von Pierrecendre',
        'summary' => 'Drei Sitzungen: eine erloschene Laterne, eine Furt, die lügt, ein Leuchtturm, der einen Namen fordert.',
    ],

    'chapters' => [
        's1' => 'Sitzung 1 – Die erloschene Laterne',
        's2' => 'Sitzung 2 – Die Furt, die lügt',
        's3' => 'Sitzung 3 – Der zurückgegebene Name',
    ],

    // „notes“: die Notiz jedes Blatts in der Szene, nach Blattschlüssel (fehlt: keine Notiz).
    'scenes' => [
        'lantern' => [
            'name' => 'Die dritte Laterne',
            'description' => '[[quay]], in der Abenddämmerung: Die dritte Laterne von Norden will nicht brennen. Die Bekanntmachung der Ausgangssperre klebt noch frisch an der Mauer.',
            'gm_notes' => 'Damit gibt [[brannoc]] sein Zeichen. Lassen Sie die Spieler es herausfinden, indem sie beobachten, wer sich dem Kai nähert.',
            'notes' => ['brannoc' => 'kommt übers Wasser, lautlos', 'guard' => 'zwei Mann auf Streife'],
        ],
        'register' => [
            'name' => 'Das verweigerte Register',
            'description' => '[[hall]]: [[ysane]] verwehrt den Zugang zum Regal mit dem Jahr des Großen Nebels. [[elzevir]] hustet.',
            'gm_notes' => 'Elzevir gibt nach, wenn man ihn beiseitenimmt, außer Sichtweite von Ysane. Sonst hustet er und wechselt das Thema.',
            'notes' => ['ysane' => 'hinter dem Pult', 'elzevir' => 'zwischen den Regalen'],
        ],
        'poles' => [
            'name' => 'Die versetzten Stangen',
            'description' => '[[marshes]] bei Ebbe: Zwei Stangen fehlen, eine dritte wurde schief wieder eingeschlagen. Der Nebel hält seit vier Tagen.',
            'gm_notes' => 'Ein Geist-Wurf gegen 7 durchschaut den Schwindel. Misslingt er, steigt die Flut um einen Spieler herum: Gelegenheit, Atem auszugeben.',
            'notes' => ['brannoc' => 'weiß es und schweigt', 'broken' => 'beobachten sie aus der Ferne'],
        ],
        'notebook' => [
            'name' => 'Mornevents Notizbuch',
            'description' => '[[mornevent]] empfängt widerwillig in der Wachstube, wo [[guard]] ihren Posten hat. Drei Namen sind in seinem Notizbuch durchgestrichen.',
            'gm_notes' => 'Er redet, wenn kein Zeuge dabei ist. Die drei Namen gehören den Reisenden, die an der Furt verschwunden sind.',
            'notes' => ['marshes' => 'erwähnt, nicht besucht'],
        ],
        'ward' => [
            'name' => 'Der Saal mit den sechs Betten',
            'description' => 'Bei [[vanne]] riechen zwei frisch Verwundete nach Salz. Sie tauscht, was sie weiß, gegen Salz und Verbandszeug.',
            'notes' => ['broken' => 'zwei von ihnen, letzte Woche gepflegt'],
        ],
        'cellar' => [
            'name' => 'Der Keller des Leuchtturms',
            'description' => '[[lighthouse]] hat einen Keller, der bei Flut vollläuft. In einer Salzkiste: die Tafeln, die aus der Halle entfernt wurden.',
            'gm_notes' => 'Tafel 1147 liegt obenauf, gut sichtbar. [[stranger]] wartet darauf, dass jemand sie laut vorliest.',
            'notes' => ['stranger' => 'oben an der Treppe', 'seal' => 'nicht in der Kiste'],
        ],
        'rising' => [
            'name' => 'Was heraufsteigt',
            'description' => '[[drowned]] gehen durch die Bucht, in Mannshöhe, geradewegs auf [[city]] zu. Der Nebelzähler steht auf 6.',
            'gm_notes' => 'Sie bleiben stehen, wenn jemand an Ysanes Stelle den Eid der Tafel 1147 hält oder wenn Gueffroys Name vor Zeugen ausgesprochen wird.',
            'notes' => ['ysane' => 'auf dem Kai, ohne ihr Siegel'],
        ],
        'recast' => [
            'name' => 'Der neu gegossene Eid',
            'description' => '[[hall]], vor versammelter Stadt: [[seal]] wird der Halle zurückgegeben – oder zerbrochen.',
            'gm_notes' => 'Zwei Enden, keines davon gut. Zurückgeben: Die Stadt hält, Ysane fällt. Zerbrechen: Kein Eid bindet mehr irgendwen, und der Graue Faden kauft alles auf.',
            'notes' => ['greythread' => 'anwesend, wartet ab'],
        ],
    ],

    'secrets' => [
        'ysane_oath' => [
            'title' => 'Ysane hat den Ertrunkenen ein Boot im Jahr geschworen',
            'body' => 'Vor dreißig Jahren versprach Ysane Korr den Ertrunkenen ein Boot im Jahr, damit die Bucht ruhig bleibt. Tafel 1147 trägt den Wortlaut – und ihren Namen.',
        ],
        'stranger' => [
            'title' => 'Der Unbekannte vom Leuchtturm ist Gueffroy, der ertrunkene Laternenanzünder',
            'body' => 'Gueffroy war der Junge, der dafür bezahlt wurde, die Laternen am Kai anzuzünden. Vor sechs Monaten ertrunken, haben die Ertrunkenen ihn zurückgegeben. Er entzündet den Leuchtturm und wartet darauf, dass jemand seinen Namen ausspricht.',
        ],
        'poles' => [
            'title' => 'Die Stangen der Furt werden absichtlich versetzt',
            'body' => 'Die Eidbrüchigen versetzen die Stangen, damit Pierrecendre sein Wasser fürchtet. Drei Reisende sind dort bereits verschwunden.',
        ],
        'daughter' => [
            'title' => 'Die Anführerin der Eidbrüchigen ist Ysanes Tochter',
            'body' => 'Die Frau, die die Eidbrüchigen anführt, ist die Tochter der Hüterin. Für sie hat Ysane die Tafeln versteckt, und für sie würde sie die Stadt verraten.',
        ],
        'bought' => [
            'title' => 'Der Graue Faden hat zwei Männer der Kaiwache gekauft',
            'body' => 'Zwei Männer der Kaiwache werden von der Kompanie vom Grauen Faden bezahlt. Mornevent ahnt nichts, und die Entdeckung wird ihn brechen.',
        ],
    ],

    // Freies Datum, Titel, Beschreibung.
    'timeline' => [
        'ash' => ['Vor 300 Jahren', 'Asche bedeckt die Bucht', 'Der Ausbruch des Orvent bringt den Vulkan zum Erlöschen und gibt der Stadt ihren grauen Boden.'],
        'first_oath' => ['Vor 180 Jahren', 'Der erste gravierte Eid', 'Die Halle wird erbaut, und der erste Eid wird auf eine Aschetafel verglast.'],
        'great_mist' => ['Vor 30 Jahren', 'Das Jahr des Großen Nebels', 'Acht Monate Nebel, elf verlorene Boote – danach dreißig Jahre lang kein einziger Schiffbruch.'],
        'tile_1147' => ['Vor 30 Jahren', 'Der Eid der Tafel 1147', 'Ysane Korr verspricht den Ertrunkenen ein Boot im Jahr. Zwei Zeugen: Elzevir und Gueffroy.'],
        'drowning' => ['Vor sechs Monaten', 'Gueffroy ertrinkt am Kai', 'Der Laternenanzünder stürzt vom Laternenkai. Seine Leiche wird nie gefunden.'],
        'missing' => ['Letzten Monat', 'Drei Reisende verschwinden an der Furt', 'Mornevent streicht drei Namen in seinem Notizbuch durch und verschweigt es dem Rat.'],
        'session1' => ['Sitzung 1', 'Die dritte Laterne bleibt dunkel', 'Die Figuren entdecken das Zeichen des Fährmanns, und man verwehrt ihnen das Regal des Großen Nebels.'],
        'poles_moved' => ['Sitzung 2', 'Die Stangen werden versetzt', 'Greift niemand ein, verschwindet ein vierter Reisender in den Marken.'],
        'invasion' => ['Sitzung 3', 'Die Ertrunkenen betreten die Stadt', 'Bei Nebelzähler 6 steigen sie die Bucht herauf und gehen bis zur Halle.'],
        'ending' => ['Ende', 'Das Siegel zurückgegeben oder zerbrochen', 'Wird das Siegel zurückgegeben, fällt Ysane; wird es zerbrochen, ist die Stadt von jedem Eid befreit – und der Graue Faden von jeder Grenze.'],
    ],

    // Ambiances sonores de la bibliothèque.
    'sounds' => [
        'tide' => 'Ebbe in den Marken',
        'mist' => 'Nebel über Pierrecendre',
        'storm' => 'Gewitter über dem Leuchtturm',
    ],

    // Séance 1, déjà jouée : son résumé.
    'session' => [
        'summary' => '[[quay]]: Die Figuren gehen an Land, und [[brannoc]] zeigt ihnen, dass die dritte Laterne dunkel bleibt – das Zeichen des Fährmanns, das niemand bemerkt hat. [[hall]]: [[elzevir]] weigert sich, das Regal des großen Nebels zu öffnen, und [[ysane]] dankt ihnen etwas zu schnell. Die Sitzung endet am Wasser, bei Ebbe: [[marshes]].',
    ],
];
