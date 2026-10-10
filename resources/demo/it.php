<?php

/*
 * Campagna dimostrativa, testo in italiano (tradotto da fr.php).
 *
 * Qui c’è solo il testo: la struttura (chi è collegato a chi, i token, i link) è in
 * App\Actions\Demo\LoadDemoCampaign ed è identica in tutte le lingue. Nelle scene,
 * « [[chiave]] » indica una scheda tramite la sua chiave.
 */
return [
    'campaign' => [
        'name' => 'Il Giuramento di Pierrecendre',
        'description' => 'Campagna dimostrativa: tre sessioni nella città portuale di Pierrecendre, dove un giuramento dimenticato torna a esigere il suo debito. Tutto il contenuto è originale e libero da diritti.',
    ],
    'game' => [
        'name' => 'Nebbia e Giuramento',
        'description' => 'Gioco d’indagine e di giuramenti, inventato per la dimostrazione. Quattro caratteristiche da 1 a 5, giuramenti che pesano sui tiri, nessuna meccanica proprietaria.',
    ],
    'world' => [
        'name' => 'Vehrmund',
        'description' => 'Un arcipelago di marche sommerse e porti costruiti sulla cenere, dove la parola data vale quanto un contratto.',
    ],

    'types' => [
        'faction' => 'Fazione',
        'pregen' => 'Pregenerato',
    ],

    'groups' => [
        'traits' => 'Caratteristiche',
        'profile' => 'Profilo',
        'secrets' => 'Segreti',
    ],

    'fields' => [
        'body' => 'Corpo',
        'skill' => 'Destrezza',
        'mind' => 'Mente',
        'heart' => 'Cuore',
        'breath' => 'Respiro',
        'oaths' => 'Giuramenti mantenuti',
        'trade' => 'Mestiere',
        'trait' => 'Tratto distintivo',
        'ties' => 'Legami',
        'hidden_oath' => 'Giuramento segreto',
        'betrayal' => 'Cosa lo spingerebbe a tradire',
    ],

    'tags' => [
        'city' => 'città',
        'act1' => 'atto 1',
        'act2' => 'atto 2',
        'act3' => 'atto 3',
        'intrigue' => 'intrigo',
        'hall' => 'sala',
        'quays' => 'moli',
        'marshes' => 'marche',
        'guard' => 'guardia',
        'pregen' => 'pregenerato',
        'base' => 'base',
        'oaths' => 'giuramenti',
        'house' => 'della casa',
        'ambience' => 'atmosfera',
    ],

    'quay_state' => [
        'status' => 'sotto coprifuoco',
        'notes' => 'Chiuso di notte da quando Gueffroy è annegato.',
    ],

    'entities' => [
        'city' => [
            'name' => 'Pierrecendre',
            'summary' => 'Città portuale sorta sulla colata di cenere di un vulcano spento.',
            'description' => "Quindicimila anime, due colline e una baia a mezzaluna. Si vive di sale, di vetro e di giuramenti: ogni contratto stretto alla Sala viene inciso su una tavoletta di cenere vetrificata.\n\nLa città sa di alghe e di zolfo freddo. Le vie alte appartengono alle case mercantili, quelle basse a chi lavora sull’acqua.",
            'gm_notes' => 'Il vero potere sta nella Sala, non nella Guardia. Se i giocatori minacciano la Guardia, Mornevent cede; se minacciano la Sala, l’intera città si chiude a riccio.',
        ],
        'hall' => [
            'name' => 'La Sala dei Giuramenti',
            'summary' => 'Edificio di pietra chiara dove i giuramenti della città vengono incisi e custoditi.',
            'description' => 'Una navata senza dio, colma di scaffali di tavolette vetrificate. Ogni tavoletta reca un giuramento, la sua data e i suoi testimoni. Si entra a capo scoperto, si esce vincolati.',
            'gm_notes' => 'Le tavolette dell’anno della Grande Nebbia sono state rimosse. Elzevir sa dove sono: nella cantina del faro, non nella Sala.',
        ],
        'quay' => [
            'name' => 'Il Molo delle Lanterne',
            'summary' => 'Il molo dei pescatori, illuminato tutta la notte da lanterne a olio di pesce.',
            'description' => 'Trenta lanterne, accese al tramonto da un ragazzino pagato a settimana. Quando una si spegne, i vecchi tornano a casa senza finire il bicchiere.',
            'gm_notes' => 'La terza lanterna da nord non viene mai riaccesa: è il segnale del Traghettatore.',
        ],
        'marshes' => [
            'name' => 'Le Marche Sommerse',
            'summary' => 'Paludi salmastre che separano Pierrecendre dal continente, percorribili con la bassa marea.',
            'description' => 'Tre ore di cammino sicuro a ogni marea, altrimenti dodici ore d’attesa. Pertiche piantate nel fango segnano il guado; qualcuno le sposta.',
            'gm_notes' => 'Sono i Giuramenti Infranti a spostare le pertiche, perché i viaggiatori si perdano e scompaiano.',
        ],
        'lighthouse' => [
            'name' => 'Il Faro di Orvent',
            'summary' => 'Faro abbandonato sulla punta meridionale, la cui lanterna certe notti si accende ancora.',
            'description' => 'Trentadue metri di pietra, una scala a chiocciola, una cantina che si allaga con l’alta marea.',
            'gm_notes' => 'Le tavolette sparite dalla Sala sono in cantina, in una cassa da sale. Le custodisce lo Sconosciuto.',
        ],
        'ysane' => [
            'name' => 'Dama Ysane Korr',
            'summary' => 'Custode dei giuramenti: incide le tavolette e fa da testimone ai contratti.',
            'description' => 'Sessant’anni, mani bruciate dal forno di vetrificazione, una memoria che nessuno osa contraddire.',
            'gm_notes' => 'È stata lei a far rimuovere le tavolette dell’anno della Grande Nebbia: il suo stesso nome è su una di esse. Non è malvagia, è terrorizzata.',
            'fields' => [
                'trade' => 'Custode dei giuramenti',
                'trait' => 'Non guarda mai due volte negli occhi la stessa persona',
                'hidden_oath' => 'Trent’anni fa ha giurato di lasciare agli Annegati una barca all’anno. Da allora la città non conosce più naufragi.',
                'betrayal' => 'La sicurezza della nipote',
            ],
        ],
        'brannoc' => [
            'name' => 'Brannoc il Traghettatore',
            'summary' => 'Porta persone e casse attraverso le marche, all’ora che fa comodo a lui.',
            'description' => 'Alto, lento, parla poco e conta in fretta. Conosce il guado a memoria, anche quando lo spostano.',
            'gm_notes' => 'Sa che le pertiche si muovono. Tacerà finché qualcuno non gli offrirà di riscattare il suo debito con il Filo Grigio.',
            'fields' => [
                'trade' => 'Traghettatore',
                'trait' => 'Non giura mai, cosa che in città suona come un insulto',
                'hidden_oath' => 'Deve undici anni di traversate gratuite alla Compagnia del Filo Grigio.',
                'betrayal' => 'La cancellazione del suo debito',
            ],
        ],
        'elzevir' => [
            'name' => 'Mastro Elzevir',
            'summary' => 'Archivista della Sala, capace di leggere le tavolette più antiche.',
            'description' => 'Piccolo, impolverato di cenere, incapace di mentire senza tossire.',
            'gm_notes' => 'Ha ricopiato le tavolette rimosse prima che le portassero via. La copia è cucita nella fodera del suo mantello.',
            'fields' => [
                'trade' => 'Archivista',
                'trait' => 'Tossisce quando mente',
                'hidden_oath' => 'Ha giurato a Ysane di non parlare mai dell’anno della Grande Nebbia.',
                'betrayal' => 'La promessa che le tavolette torneranno al loro posto',
            ],
        ],
        'vanne' => [
            'name' => 'Suor Vanne',
            'summary' => 'Cura annegati e ustionati, senza chiedere da che parte stiano.',
            'description' => 'Gestisce una sala da sei letti sopra una corderia.',
            'gm_notes' => 'La settimana scorsa ha curato due Giuramenti Infranti. Lo rivelerà solo in cambio di sale e bende.',
            'fields' => [
                'trade' => 'Guaritrice',
                'trait' => 'Chiama tutti «piccolo»',
            ],
        ],
        'mornevent' => [
            'name' => 'Capitano Hald Mornevent',
            'summary' => 'Comanda la Guardia dei Moli: ventidue uomini e una barca.',
            'description' => 'Competente, stanco, perfettamente consapevole di non avere i mezzi per il suo incarico.',
            'gm_notes' => 'Copre la scomparsa di tre viaggiatori per non seminare il panico in città. Accetterà aiuto se glielo offrono senza testimoni.',
            'fields' => [
                'trade' => 'Capitano della Guardia',
                'trait' => 'Annota tutto in un taccuino che non rilegge mai',
                'hidden_oath' => 'Ha promesso al consiglio che nessuno sarebbe scomparso sotto il suo comando.',
                'betrayal' => 'Salvare la faccia davanti al consiglio',
            ],
        ],
        'stranger' => [
            'name' => 'Lo Sconosciuto del Faro',
            'summary' => 'Colui che riaccende la lanterna del faro di Orvent. Nessuno l’ha mai visto da vicino.',
            'gm_notes' => 'È Gueffroy, il ragazzino delle lanterne, annegato sei mesi fa e restituito dagli Annegati. Custodisce le tavolette e aspetta una cosa sola: che qualcuno pronunci il suo nome ad alta voce.',
            'fields' => [
                'trade' => 'Accenditore di lanterne',
                'trait' => 'Sa di sale freddo',
                'hidden_oath' => 'Morendo, ha giurato di riaccendere le lanterne finché non gli renderanno il suo nome.',
            ],
        ],
        'drowned' => [
            'name' => 'Gli Annegati',
            'summary' => 'Ciò che risale dalle marche quando la nebbia dura più di tre giorni.',
            'description' => 'Li descrivono come sagome che camminano sott’acqua dove il fondale è basso, all’altezza di un uomo.',
            'gm_notes' => 'Non uccidono: reclamano. Un Annegato lascia la presa se qualcuno mantiene al posto suo il giuramento che è venuto a riscuotere.',
        ],
        'seal' => [
            'name' => 'Il Sigillo di Cenere',
            'summary' => 'Il punzone che incide le tavolette della Sala. Senza di esso, nessun giuramento è valido.',
            'description' => 'Un cilindro di vetro nero, pesante, con lo stemma della città inciso in negativo.',
            'gm_notes' => 'Ysane l’ha nascosto. Renderlo pubblico chiude la campagna con un accordo; distruggerlo la chiude con una rottura.',
        ],
        'greythread' => [
            'name' => 'La Compagnia del Filo Grigio',
            'summary' => 'Casa mercantile che compra debiti e rivende servizi.',
            'description' => 'Tre banchi, nessuna nave di proprietà e un registro dei debiti più spesso di quello della città.',
            'gm_notes' => 'Vuole il Sigillo di Cenere: chi incide i giuramenti stabilisce il prezzo dei debiti.',
        ],
        'broken' => [
            'name' => 'I Giuramenti Infranti',
            'summary' => 'Coloro che hanno rotto un giuramento e ora vivono fuori città, nelle marche.',
            'gm_notes' => 'Spostano le pertiche perché la città impari finalmente a temere l’acqua. La loro capa è la figlia di Ysane.',
        ],
        'guard' => [
            'name' => 'La Guardia dei Moli',
            'summary' => 'Ventidue uomini incaricati del porto, delle lanterne e del coprifuoco.',
            'gm_notes' => 'Due di loro sono pagati dal Filo Grigio. Mornevent non lo sa.',
        ],
        'teska' => [
            'name' => 'Teska la Rematrice',
            'summary' => 'Rema fin da bambina e conosce la baia meglio della Guardia.',
            'description' => 'Hai giurato a tuo fratello di non lasciare mai Pierrecendre. Lui se n’è andato il mese scorso.',
            'fields' => [
                'trade' => 'Rematrice',
                'trait' => 'Dice tutto, e subito',
                'ties' => 'Suo fratello, partito senza una parola. Brannoc, che le deve una barca.',
            ],
        ],
        'oriel' => [
            'name' => 'Oriel Chantegrèle',
            'summary' => 'Testimone di mestiere: lo pagano per assistere ai giuramenti e ricordarli.',
            'description' => 'Hai fatto da testimone a duecento giuramenti. Ne hai dimenticato uno solo, di proposito.',
            'fields' => [
                'trade' => 'Testimone',
                'trait' => 'Ripete sottovoce le frasi importanti',
                'ties' => 'Mastro Elzevir, il suo maestro di bottega. Il Filo Grigio, che lo ingaggia troppo spesso.',
            ],
        ],
        'dorn' => [
            'name' => 'Dorn Ferrofreddo',
            'summary' => 'Ex guardia, congedato per essersi rifiutato di far rispettare un coprifuoco.',
            'description' => 'Hai giurato di non obbedire mai più a un ordine che non capisci.',
            'fields' => [
                'trade' => 'Guardia congedata',
                'trait' => 'Si mette sempre tra la porta e gli altri',
                'ties' => 'Mornevent, che l’ha congedato a malincuore. Suor Vanne, che l’ha ricucito due volte.',
            ],
        ],
        'lisenn' => [
            'name' => 'Lisenn dai Due Nomi',
            'summary' => 'Viene dalle marche e vive in città sotto un nome che non è il suo.',
            'description' => 'Hai infranto un giuramento. Qui nessuno lo sa ancora.',
            'fields' => [
                'trade' => 'Guida delle marche',
                'trait' => 'Non dorme mai due notti nello stesso posto',
                'ties' => 'I Giuramenti Infranti, che ha abbandonato. Lisenn, la morta di cui porta il nome.',
            ],
        ],
    ],

    // Etichetta della relazione, poi la sua etichetta inversa (null: nessuna inversa).
    'relations' => [
        'ysane_hall' => ['custodisce', 'custodita da'],
        'elzevir_hall' => ['lavora presso', 'impiega'],
        'elzevir_ysane' => ['ha giurato il silenzio a', 'vincola con un giuramento'],
        'brannoc_marshes' => ['conosce il guado di', 'attraversate da'],
        'brannoc_greythread' => ['ha un debito con', 'detiene il debito di'],
        'mornevent_guard' => ['comanda', 'comandata da'],
        'guard_quay' => ['sorveglia', 'sorvegliato da'],
        'greythread_guard' => ['ha comprato due uomini di', null],
        'greythread_seal' => ['brama', 'bramato da'],
        'broken_marshes' => ['vivono in', 'danno rifugio a'],
        'broken_ysane' => ['sono guidati da sua figlia', null],
        'drowned_marshes' => ['risalgono da', null],
        'drowned_ysane' => ['hanno un giuramento con', null],
        'stranger_lighthouse' => ['riaccende', 'riacceso da'],
        'stranger_quay' => ['accendeva le lanterne di', null],
        'vanne_broken' => ['ne ha curati due', null],
        'hall_city' => ['sorge a', 'ospita'],
        'quay_city' => ['costeggia', 'si apre su'],
        'seal_hall' => ['incide le tavolette di', null],
        'teska_brannoc' => ['gli ha prestato una barca', 'le deve una barca'],
        'oriel_elzevir' => ['è stato suo apprendista', 'ha formato'],
        'dorn_mornevent' => ['ha servito sotto', 'ha congedato'],
        'lisenn_broken' => ['li ha abbandonati', null],
    ],

    'documents' => [
        'tile' => [
            'title' => 'Tavoletta 1147 — giuramento della Grande Nebbia',
            'description' => 'La trascrizione che Elzevir ha ricopiato prima che le tavolette lasciassero la Sala.',
            'lines' => [
                'Trascrizione della tavoletta 1147, Sala dei Giuramenti di Pierrecendre.',
                '',
                'Giurante: Ysane Korr, custode.',
                'Giuramento: «Una barca all’anno, e la baia resterà calma.»',
                'Testimoni: Elzevir, archivista. Gueffroy, accenditore di lanterne.',
                '',
                'Nota dell’archivista: tavoletta rimossa dallo scaffale il 3 del mese del sale.',
            ],
        ],
        'notice' => [
            'title' => 'Avviso di coprifuoco',
            'description' => 'Affisso sul Molo delle Lanterne. Da mostrare ai giocatori fin dalla prima scena.',
            'lines' => [
                'Per ordine del capitano Hald Mornevent, Guardia dei Moli.',
                '',
                'Il Molo delle Lanterne è chiuso dall’ultima lanterna all’alba.',
                'Nessuno prende il mare senza un lasciapassare della Guardia.',
                'Ogni lanterna spenta va segnalata al posto di guardia.',
                '',
                'Questo avviso vale come giuramento: chi lo viola ne risponde davanti alla Sala.',
            ],
        ],
        'tides' => [
            'title' => 'Tavola delle maree delle Marche Sommerse',
            'description' => 'Aiuto di gioco: tre ore di guado con la bassa marea.',
            'lines' => [
                'Marche Sommerse — attraversamento del guado',
                '',
                'Bassa marea: tre ore di cammino sicuro, pertiche visibili.',
                'Marea montante: un’ora di tregua, acqua a mezza coscia.',
                'Alta marea: nessun passaggio. Dodici ore d’attesa.',
                '',
                'Le pertiche vengono ripiantate ogni mese dalla Guardia.',
            ],
        ],
        'plan' => [
            'title' => 'Pianta del porto di Pierrecendre',
            'description' => 'Il porto, i suoi moli e la punta del faro. Si può mostrare al tavolo.',
            'file' => 'pianta-del-porto',
        ],
    ],

    'map' => [
        'name' => 'Il porto di Pierrecendre',
        'unit' => 'm',
    ],

    'rules' => [
        'roll' => [
            'title' => 'Tiro di giuramento',
            'category' => 'Base',
            'summary' => 'Caratteristica + 1d6 contro una difficoltà da 4 a 9.',
            'procedure' => "1. Dichiara la caratteristica usata e cosa vuole ottenere il personaggio.\n2. Tira 1d6 e somma la caratteristica.\n3. 4 per un compito del proprio mestiere, 7 per un compito difficile, 9 per l’impossibile.\n4. Se il personaggio agisce per mantenere un giuramento, aggiungi +1 per ogni giuramento mantenuto, fino a +3.",
            'source' => 'Manuale base, p. 12',
        ],
        'breath' => [
            'title' => 'Respiro',
            'category' => 'Base',
            'summary' => 'Il Respiro sostituisce i punti ferita: si spende per resistere, non per incassare.',
            'procedure' => "Spendi 1 Respiro per ritirare un dado, per andare avanti nonostante una ferita o per respingere un Annegato.\nA 0, il personaggio si ferma: non è morto, ma non può più promettere nulla fino al prossimo riposo.",
            'source' => 'Manuale base, p. 18',
        ],
        'breaking' => [
            'title' => 'Infrangere un giuramento',
            'category' => 'Giuramenti',
            'summary' => 'Infrangere un giuramento dà un vantaggio immediato e un prezzo duraturo.',
            'procedure' => "Il giocatore descrive cosa gli consente la rottura: lo ottiene, senza tiro.\nPoi perde tutti i giuramenti mantenuti, e il tavolo annota chi l’ha saputo.",
            'gm_notes' => 'Non rifiutare mai una rottura. Il prezzo si paga nella finzione, con la reazione di chi viene a saperlo.',
            'source' => 'Manuale base, p. 24',
        ],
        'mist' => [
            'title' => 'Conto della nebbia',
            'category' => 'Della casa',
            'summary' => 'Regola della casa: la nebbia sale di un grado a ogni sessione, finché gli Annegati non camminano in città.',
            'procedure' => "Tieni un conto da 0 a 6, visibile a tutto il tavolo.\n+1 alla fine di ogni sessione, +1 ogni volta che un giuramento viene infranto davanti a testimoni.\nA 3, il guado diventa incerto. A 6, gli Annegati entrano in Pierrecendre.",
            'gm_notes' => 'Conto dietro lo schermo, segreto fino a 3.',
        ],
        'word' => [
            'title' => 'Parola data al tavolo',
            'category' => 'Della casa',
            'summary' => 'Da provare: una promessa fatta ad alta voce dal giocatore conta come giuramento.',
            'procedure' => 'Quando un giocatore promette qualcosa a un personaggio, annotalo. Se la mantiene, +1 giuramento mantenuto; altrimenti si applica la rottura.',
            'gm_notes' => 'Da provare nella sessione 2. Rischio: i giocatori non osano più promettere nulla.',
        ],
    ],

    'scenario' => [
        'name' => 'Il Giuramento di Pierrecendre',
        'summary' => 'Tre sessioni: una lanterna spenta, un guado che mente, un faro che reclama un nome.',
    ],

    'chapters' => [
        's1' => 'Sessione 1 — La lanterna spenta',
        's2' => 'Sessione 2 — Il guado che mente',
        's3' => 'Sessione 3 — Il nome restituito',
    ],

    // «notes»: la nota di ogni scheda nella scena, per chiave della scheda (assente: nessuna nota).
    'scenes' => [
        'lantern' => [
            'name' => 'La terza lanterna',
            'description' => 'Al tramonto, [[quay]]: la terza lanterna da nord si rifiuta di accendersi. L’avviso di coprifuoco è ancora fresco sul muro.',
            'gm_notes' => 'È il segnale di [[brannoc]]. Lascia che i giocatori lo scoprano osservando chi si avvicina al molo.',
            'notes' => ['brannoc' => 'arriva dall’acqua, senza rumore', 'guard' => 'due uomini di ronda'],
        ],
        'register' => [
            'name' => 'Il registro negato',
            'description' => '[[hall]]: [[ysane]] nega l’accesso allo scaffale dell’anno della Grande Nebbia. [[elzevir]] tossisce.',
            'gm_notes' => 'Elzevir cederà se preso in disparte, lontano dagli occhi di Ysane. Altrimenti tossisce e cambia discorso.',
            'notes' => ['ysane' => 'dietro il leggio', 'elzevir' => 'tra gli scaffali'],
        ],
        'poles' => [
            'name' => 'Le pertiche spostate',
            'description' => '[[marshes]], con la bassa marea: mancano due pertiche e una terza è stata ripiantata storta. La nebbia dura da quattro giorni.',
            'gm_notes' => 'Un tiro di Mente a 7 scopre l’inganno. In caso di fallimento, la marea sale su un giocatore: occasione per spendere Respiro.',
            'notes' => ['brannoc' => 'sa, e tace', 'broken' => 'li osservano da lontano'],
        ],
        'notebook' => [
            'name' => 'Il taccuino di Mornevent',
            'description' => '[[mornevent]] riceve controvoglia nel posto di comando dove ha sede [[guard]]. Nel suo taccuino, tre nomi sono cancellati.',
            'gm_notes' => 'Parla se non ci sono testimoni. I tre nomi sono quelli dei viaggiatori scomparsi al guado.',
            'notes' => ['marshes' => 'nominate, non visitate'],
        ],
        'ward' => [
            'name' => 'La sala dai sei letti',
            'description' => 'Da [[vanne]], due feriti recenti sanno di sale. Lei scambia ciò che sa con sale e bende.',
            'notes' => ['broken' => 'due di loro, curati la settimana scorsa'],
        ],
        'cellar' => [
            'name' => 'La cantina del faro',
            'description' => '[[lighthouse]]: la cantina si allaga con l’alta marea. In una cassa da sale, le tavolette rimosse dalla Sala.',
            'gm_notes' => 'La tavoletta 1147 è in cima alla pila, bene in vista. [[stranger]] aspetta che qualcuno la legga ad alta voce.',
            'notes' => ['stranger' => 'in cima alla scala', 'seal' => 'non è nella cassa'],
        ],
        'rising' => [
            'name' => 'Ciò che risale',
            'description' => '[[drowned]] camminano nella baia, all’altezza di un uomo, dritti verso [[city]]. Il conto della nebbia è a 6.',
            'gm_notes' => 'Si fermano se qualcuno mantiene al posto di Ysane il giuramento della tavoletta 1147, o se il nome di Gueffroy viene pronunciato davanti a testimoni.',
            'notes' => ['ysane' => 'sul molo, senza il suo sigillo'],
        ],
        'recast' => [
            'name' => 'Il giuramento rifuso',
            'description' => '[[hall]], davanti alla città intera: restituire [[seal]] alla Sala, o spezzarlo.',
            'gm_notes' => 'Due finali, nessuno buono. Restituirlo: la città regge, Ysane cade. Spezzarlo: nessun giuramento vincola più nessuno, e il Filo Grigio compra tutto.',
            'notes' => ['greythread' => 'presente, attende il suo turno'],
        ],
    ],

    'secrets' => [
        'ysane_oath' => [
            'title' => 'Ysane ha giurato agli Annegati una barca all’anno',
            'body' => 'Trent’anni fa, Ysane Korr ha promesso agli Annegati una barca all’anno perché la baia restasse calma. La tavoletta 1147 ne riporta il testo, e il suo nome.',
        ],
        'stranger' => [
            'title' => 'Lo Sconosciuto del Faro è Gueffroy, l’accenditore annegato',
            'body' => 'Gueffroy era il ragazzino pagato per accendere le lanterne del molo. Annegato sei mesi fa, è stato restituito dagli Annegati. Riaccende il faro e aspetta che qualcuno pronunci il suo nome.',
        ],
        'poles' => [
            'title' => 'Le pertiche del guado vengono spostate di proposito',
            'body' => 'I Giuramenti Infranti spostano le pertiche perché Pierrecendre abbia paura della sua acqua. Tre viaggiatori vi sono già scomparsi.',
        ],
        'daughter' => [
            'title' => 'La capa dei Giuramenti Infranti è la figlia di Ysane',
            'body' => 'Chi guida i Giuramenti Infranti è la figlia della custode. È per lei che Ysane ha nascosto le tavolette, ed è per lei che tradirebbe la città.',
        ],
        'bought' => [
            'title' => 'Il Filo Grigio ha comprato due guardie del molo',
            'body' => 'Due uomini della Guardia dei Moli sono pagati dalla Compagnia del Filo Grigio. Mornevent non lo sa, e scoprirlo lo spezzerà.',
        ],
    ],

    // Data libera, titolo, descrizione.
    'timeline' => [
        'ash' => ['300 anni fa', 'La cenere ricopre la baia', 'L’eruzione del monte Orvent spegne il vulcano e dà alla città il suo suolo grigio.'],
        'first_oath' => ['180 anni fa', 'Il primo giuramento inciso', 'Viene costruita la Sala e il primo giuramento è vetrificato su una tavoletta di cenere.'],
        'great_mist' => ['30 anni fa', 'L’anno della Grande Nebbia', 'Una nebbia di otto mesi, undici barche perdute, poi più nessun naufragio per trent’anni.'],
        'tile_1147' => ['30 anni fa', 'Il giuramento della tavoletta 1147', 'Ysane Korr promette agli Annegati una barca all’anno. Due testimoni: Elzevir e Gueffroy.'],
        'drowning' => ['Sei mesi fa', 'Gueffroy annega al molo', 'L’accenditore di lanterne cade dal Molo delle Lanterne. Il suo corpo non viene ritrovato.'],
        'missing' => ['Il mese scorso', 'Tre viaggiatori scompaiono al guado', 'Mornevent cancella tre nomi dal suo taccuino e non avverte il consiglio.'],
        'session1' => ['Sessione 1', 'La terza lanterna resta spenta', 'I personaggi individuano il segnale del Traghettatore e si vedono negare lo scaffale della Grande Nebbia.'],
        'poles_moved' => ['Sessione 2', 'Le pertiche vengono spostate', 'Se nessuno interviene, un quarto viaggiatore scompare nelle marche.'],
        'invasion' => ['Sessione 3', 'Gli Annegati entrano in città', 'Con il conto della nebbia a 6, risalgono la baia e marciano fino alla Sala.'],
        'ending' => ['Fine', 'Il sigillo restituito o spezzato', 'Restituire il Sigillo fa cadere Ysane; spezzarlo libera la città da ogni giuramento, e il Filo Grigio da ogni limite.'],
    ],

    // Ambiances sonores de la bibliothèque.
    'sounds' => [
        'tide' => 'Bassa marea sulle paludi',
        'mist' => 'Nebbia di Pierrecendre',
        'storm' => 'Tempesta sul faro',
    ],

    // Séance 1, déjà jouée : son résumé.
    'session' => [
        'summary' => '[[quay]]: i personaggi sbarcano e [[brannoc]] mostra loro che la terza lanterna resta spenta, il segnale del Traghettatore che nessuno ha notato. [[hall]]: [[elzevir]] rifiuta di aprire lo scaffale della grande nebbia, e [[ysane]] li ringrazia un po’ troppo in fretta. La sessione si chiude in riva all’acqua, con la bassa marea: [[marshes]].',
    ],
];
