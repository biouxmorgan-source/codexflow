<?php

// Novità per versione, dalla più recente alla più vecchia. Mostrate in
// «Novità» (una volta dopo ogni aggiornamento) e nella pagina con lo stesso nome.
return [

    '0.89.0' => [
        'date' => '2026-10-21',
        'title' => 'Dichiarazione dei diritti a ogni importazione',
        'items' => [
            'Prima di ogni importazione (file CSV o JSON, archivio di campagna, modello di gioco), una casella non spuntata per impostazione predefinita chiede di certificare che disponi dei diritti necessari sui contenuti importati. Senza di essa il pulsante resta grigio e il server rifiuta l’importazione.',
            'Ogni dichiarazione è registrata con la data, la versione del testo accettato e l’esito dell’importazione, senza copia dei file. Compare in «Scarica i miei dati».',
            'Le condizioni d’uso si arricchiscono di una sezione «Contenuti degli utenti e proprietà intellettuale», raggiungibile da ogni schermata di importazione.',
        ],
    ],

    '0.88.0' => [
        'date' => '2026-10-21',
        'title' => 'Riconoscere gli accessi gratuiti',
        'items' => [
            'Console di amministrazione, pagina Account: una casella «Accesso gratuito» per ogni account, con una nota facoltativa (amico, tester…). Gli accessi gratuiti compaiono sotto l’indirizzo, hanno il loro totale in cima alla pagina e il filtro «Mostra».',
        ],
    ],

    '0.87.1' => [
        'date' => '2026-10-20',
        'title' => 'Nomi dei punti leggibili sulla mappa',
        'items' => [
            'Nel tema scuro, i nomi dei punti della mappa della campagna apparivano scuri su sfondo nero: tornano bianchi, come i riquadri di iniziativa e dadi sullo schermo del tavolo e i pulsanti di pagina dei PDF.',
        ],
    ],

    '0.87.0' => [
        'date' => '2026-10-20',
        'title' => 'Tiri salvati per ogni scheda',
        'items' => [
            'Nei dadi de «Le mie schede», «Salva questo tiro» conserva un tiro con la scheda: ogni personaggio ha i suoi tiri (Palla di fuoco, Attacco…), che si ricaricano con un tocco, si aggiornano e si tolgono con ×.',
        ],
    ],

    '0.86.2' => [
        'date' => '2026-10-20',
        'title' => '«Sostituisci il file» funziona',
        'items' => [
            'Il pulsante «Sostituisci» restava disattivato dopo la scelta del nuovo file: torna cliccabile appena il file è stato inviato.',
        ],
    ],

    '0.86.1' => [
        'date' => '2026-10-20',
        'title' => 'Sostituire una mappa con un’immagine di altre dimensioni',
        'items' => [
            'Quando il documento è usato come mappa, «Sostituisci il file» mostra le dimensioni dell’immagine attuale e accetta un’immagine di altre dimensioni. Pedine, sagome e punti mantengono la posizione in pixel: un avviso segnala che si sposteranno sul disegno.',
        ],
    ],

    '0.86.0' => [
        'date' => '2026-10-20',
        'title' => 'Modificare un documento senza eliminarlo',
        'items' => [
            'Nella pagina di un documento, «Sostituisci il file» mette una nuova versione al posto della vecchia: il documento mantiene titolo, impostazioni e collegamenti, e la nuova versione compare subito in scene, schede, regole, mappe, indizi e presso i personaggi che l’hanno ricevuto.',
            'Un’immagine usata come mappa si sostituisce con un’altra immagine; pedine, griglia e punti restano al loro posto.',
        ],
    ],

    '0.85.0' => [
        'date' => '2026-10-20',
        'title' => 'Tirare i dadi dalla scheda',
        'items' => [
            'Sulla scheda di un personaggio, un piccolo dado accanto a ogni numero e a ogni contatore tira i dadi con quel valore: «Tira sotto… (soglia)» o «Aggiungi… al tiro (bonus)». Il vassoio dei dadi si apre pronto al tiro e il risultato va al tavolo come sempre.',
            'Sulla scheda del tuo personaggio, il valore resta legato alla scheda: se cambia, il tiro salvato lo segue. Anche il master può tirare dalla scheda di un giocatore.',
            '«Le mie schede» ha i suoi dadi: lo stesso piccolo dado accanto ai valori numerici e un pulsante «Dadi» in fondo alla scheda. Il risultato appare sulla scheda, visibile solo a te.',
        ],
    ],

    '0.84.2' => [
        'date' => '2026-10-20',
        'title' => 'Nuove mappe per la demo',
        'items' => [
            'La demo ha nuove mappe illustrate: il villaggio costiero per la mappa della campagna, il molo sotterraneo del faro per la sottomappa. Ricaricala da «Le mie campagne» per vederle.',
        ],
    ],

    '0.84.1' => [
        'date' => '2026-10-19',
        'title' => 'Più spazio in Le mie schede',
        'items' => [
            'Una scheda tiene ora fino a 200 blocchi, 500 campi e 30 sezioni. «+ Nuova nota» non porta più a una pagina di errore: al limite, un messaggio lo spiega.',
        ],
    ],

    '0.84.0' => [
        'date' => '2026-10-19',
        'title' => 'Sezioni in Le mie schede',
        'items' => [
            'In «Modifica», «+ Nuova sezione» divide una scheda in sezioni a cui dai un nome (Personaggio, Incantesimi, Inventario…), che riordini e rimuovi.',
            'Ogni blocco sceglie la sua sezione, e ogni sezione dispone i suoi blocchi sulle proprie tre colonne. Una scheda esistente tiene tutti i blocchi in una prima sezione.',
        ],
    ],

    '0.83.0' => [
        'date' => '2026-10-19',
        'title' => 'Più note e tre colonne in Le mie schede',
        'items' => [
            '«+ Nuova nota» aggiunge un blocco di note: quanti ne vuoi (debiti, voci, contatti…). Le tue note precedenti diventano un primo blocco «Note».',
            'I blocchi si posizionano ora su tre colonne, note comprese, con «Organizza i blocchi».',
        ],
    ],

    '0.82.0' => [
        'date' => '2026-10-19',
        'title' => 'Una demo aggiornata',
        'items' => [
            'La campagna dimostrativa mostra ora le ultime novità: sulla mappa della campagna il faro porta a una sottomappa, la sua cantina, pronta per lo scontro con pedine, due sagome d’area e un ordine di iniziativa. Ogni punto collega più schede e più scene.',
            'Dadi al tavolo: tiri preparati che leggono la caratteristica dalla scheda, un bonus con nome e i tiri della sessione 1, tra cui una richiesta del master.',
            'Inoltre: un suono da «I miei suoni», una scena «Giocata come previsto» e un’altra con «Cosa è successo», un indizio «Menzogna», una scheda e una regola della casa con la loro versione ufficiale, e giocatori che completano il proprio ritratto.',
            'Ricarica la demo da «Le mie campagne» per scoprirla: una copia già caricata resta com’è.',
        ],
    ],

    '0.81.0' => [
        'date' => '2026-10-19',
        'title' => 'Mappa della campagna: zoom, tavolo e luoghi più ricchi',
        'items' => [
            'La mappa della campagna si ingrandisce (rotella, + e −, pizzico su tablet) e si sposta trascinando. Consiglio: un’immagine 4K (3840 × 2160) resta nitida.',
            'Quando la mappa è sullo schermo del tavolo, la sottomappa aperta da un punto la sostituisce lì, e la mappa superiore torna quando risali. Nella sottomappa, «Questo luogo» tiene a portata di clic le scene e le schede del punto.',
            'Un punto ora può collegarsi a più scene e più schede: il luogo, i suoi PNG, tutte le scene che vi si giocano.',
        ],
    ],

    '0.80.0' => [
        'date' => '2026-10-19',
        'title' => 'Blocchi, note e caselle in Le mie schede',
        'items' => [
            'In «Le mie schede», i campi si raccolgono in blocchi a cui dai un nome: Caratteristiche, Abilità, Equipaggiamento, Incantesimi…',
            '«Organizza i blocchi»: trascina un blocco dalla sua maniglia, o usa le frecce, per metterlo dove vuoi, su una o due colonne.',
            'Ogni campo può avere una nota e una casella da spuntare durante la partita: un’abilità riuscita, un incantesimo preparato.',
        ],
    ],

    '0.79.0' => [
        'date' => '2026-10-19',
        'title' => 'Biblioteca delle demo',
        'items' => [
            'Nuova «Biblioteca delle demo», da «Le mie campagne»: campagne pronte da giocare (un\'ambientazione, uno scenario breve, personaggi pregenerati) di cui carichi la tua copia con un clic.',
            'Ogni demo di un autore o di un editore mantiene un link «Acquista il gioco», nella biblioteca e in cima alla tua copia.',
            'Il prezzo di Premium compare sul pulsante di abbonamento nelle Preferenze: 4,99 € al mese.',
        ],
    ],

    '0.78.0' => [
        'date' => '2026-10-18',
        'title' => 'Cosa è successo, e il libro delle avventure',
        'items' => [
            'Ogni scena ha ora due parti: «Cosa è previsto» e «Cosa è successo». Se il tavolo ha seguito il piano, basta spuntare «Giocata come previsto».',
            'Nuovo «Libro delle avventure» nella campagna: le scene giocate, scenario per scenario, poi i riassunti delle sessioni, da leggere, stampare o salvare in PDF.',
            'Si può aggiungere una versione scritta, da te o dall’IA che preferisci (testo da copiare, o la tua chiave API, a tue spese). Il libro resta riservato al master.',
        ],
    ],

    '0.77.0' => [
        'date' => '2026-10-18',
        'title' => 'Disponi le tue schermate a modo tuo',
        'items' => [
            'Nuovo pulsante «Organizza la schermata» nella pagina della campagna e in modalità Sessione: trascina ogni blocco dalla sua maniglia ⠿, con il mouse o con il dito, oppure usa le frecce.',
            'In modalità Sessione un blocco può passare da una colonna all’altra: la musica in alto, le note a destra, come preferisci.',
            '«Nascondi» toglie un blocco che non usi, «Mostra» lo fa tornare, «Disposizione predefinita» rimette tutto a posto. Questa disposizione è solo tua: giocatori e co-master mantengono la loro.',
        ],
    ],

    '0.76.0' => [
        'date' => '2026-10-18',
        'title' => 'La tua scheda, anche senza rete',
        'items' => [
            'Offline, la scheda del tuo personaggio resta utilizzabile: regola i contatori con − e +, modifica i campi che il master ti lascia cambiare e scrivi note.',
            'Tutto viene conservato sul dispositivo e inviato al ritorno della connessione, campo per campo: un contatore riceve ciò che hai tolto o aggiunto, e ciò che il master ha cambiato nel frattempo (un segreto rivelato, un campo modificato) viene mantenuto.',
            'Dopo l’invio, un riepilogo indica cosa è stato aggiornato e segnala i campi che il master ha modificato insieme a te.',
        ],
    ],

    '0.75.0' => [
        'date' => '2026-10-18',
        'title' => 'La grande mappa della campagna',
        'items' => [
            'Nuova «Mappa della campagna»: scegli una mappa come vista d’insieme e posiziona punti di interesse, una città, un dungeon, il luogo di una scena.',
            'Un punto può aprire una sottomappa con un clic, che ha a sua volta i suoi punti; il percorso di navigazione permette di risalire alla vista d’insieme.',
            'Collegati a una scena, i punti mostrano ciò che è giocato, in corso o ancora da giocare; collegati a una scheda, la aprono. Tutto resta al master, tranne il nome dei punti «Visibili al tavolo», mostrato sullo schermo del tavolo.',
        ],
    ],

    '0.74.0' => [
        'date' => '2026-10-18',
        'title' => 'I tuoi personaggi giocati altrove',
        'items' => [
            'Nuova pagina «Le mie schede»: conserva i personaggi che giochi fuori da SagaWyn, da un amico, in un club o altrove. Li vedi solo tu.',
            'Ogni personaggio ha la sua scheda PDF, un ritratto, campi liberi (testi e contatori da regolare con − e + durante la partita) e le tue note, con il nome del gioco e del tavolo per ritrovarlo.',
        ],
    ],

    '0.73.0' => [
        'date' => '2026-10-18',
        'title' => 'Una libreria di suoni per tutte le tue campagne',
        'items' => [
            'Nuova pagina «I miei suoni»: musiche e atmosfere salvate una sola volta nel tuo account, poi aggiunte alle campagne che conduci. Il file non viene copiato e conta una sola volta nel tuo spazio.',
            'Nella pagina Suoni di una campagna: «Dalla mia libreria» per aggiungere i tuoi suoni, «Sposta nella mia libreria» per riutilizzare un suono altrove, e una casella per salvare un nuovo caricamento direttamente nella libreria.',
            'Spostare nella libreria un suono già caricato in un’altra campagna unisce le due copie: le scene mantengono la loro musica e lo spazio viene liberato.',
        ],
    ],

    '0.72.0' => [
        'date' => '2026-10-18',
        'title' => 'Le istruzioni ovunque',
        'items' => [
            'Il pulsante «?» arriva su suoni, grafo delle relazioni, cronologia, opinioni dei giocatori, assistente IA, registro e scheda del personaggio.',
            'Su grafo, cronologia e scheda del personaggio, i giocatori leggono istruzioni scritte per loro.',
        ],
    ],

    '0.71.1' => [
        'date' => '2026-10-18',
        'title' => 'Togliere o aggiungere più punti in una volta',
        'items' => [
            'Sotto ogni contatore che il giocatore può modificare (PF, munizioni, denaro…), un campo «Quantità»: scrivi 8 e poi −, e il contatore perde 8 in un colpo. Il valore predefinito è 1.',
        ],
    ],

    '0.71.0' => [
        'date' => '2026-10-18',
        'title' => 'Istruzioni su altre schermate',
        'items' => [
            'Il pulsante «?» compare anche sulla scheda (lato Master), nella modalità Sessione, negli scenari e nelle scene, nell’importazione di file e nei membri della campagna.',
            'La pagina «Aiuto» ora spiega il tiro grezzo e i bonus letti dalla scheda del personaggio.',
        ],
    ],

    '0.70.0' => [
        'date' => '2026-10-18',
        'title' => 'Istruzioni sulle schermate più delicate',
        'items' => [
            'Un pulsante «?» accanto al titolo delle schermate che non si spiegano da sole apre le istruzioni in poche righe: tipi di scheda, campi del gioco, tag, dadi, mappe, telecomando dello schermo da tavolo, indizi e cronologia delle rivelazioni.',
            'Ogni istruzione rimanda alla domanda corrispondente della pagina «Aiuto», che si apre direttamente lì.',
        ],
    ],

    '0.69.0' => [
        'date' => '2026-10-18',
        'title' => 'Bonus e soglia letti sulla scheda',
        'items' => [
            'Nei dadi, il bonus può prendere uno o più valori dalla scheda del personaggio (caratteristica, abilità, qualsiasi campo numero o contatore della zona pubblica), interi o come decina, metà o quinto, oltre al bonus libero. Anche la soglia può essere letta sulla scheda. I valori si leggono al momento del tiro, sulla scheda di ogni personaggio che tira.',
            'Più bonus con nome (Arma +2, Ferita −1…) si sommano nello stesso tiro, insieme ai valori della scheda.',
            'Gli esempi già pronti spariscono dal vassoio: restano i tuoi tiri salvati.',
            'Un tiro salvato si elimina con un tocco: la × sulla sua etichetta, o «Elimina» una volta caricato.',
        ],
    ],

    '0.68.0' => [
        'date' => '2026-10-17',
        'title' => 'Tiro grezzo',
        'items' => [
            'Nuova lettura «Grezzo», scelta come predefinita: scegli tipo e numero di dadi (1d20 + 2d6…), tiri, e ogni dado appare così come cade, senza totale, senza soglia né altre regole.',
        ],
    ],

    '0.67.0' => [
        'date' => '2026-10-17',
        'title' => 'I tuoi contenuti, i tuoi diritti',
        'items' => [
            'La pagina «Privacy e note legali» precisa che SagaWyn non è proprietario di ciò che vi inserisci: ogni utente resta proprietario dei suoi contenuti e responsabile di ciò che pubblica.',
            'Nuove sezioni: «Importazione e strutturazione dei contenuti», «Servizi di intelligenza artificiale», «Segnalare un contenuto illecito», «Disponibilità e backup» e «Giochi e marchi». Le pagine di importazione vi rimandano.',
        ],
    ],

    '0.66.0' => [
        'date' => '2026-10-17',
        'title' => 'Dadi misti e tiri personali',
        'items' => [
            'Uno stesso tiro può mescolare più tipi di dadi: tocca un d20 e poi due d6, e si tira 1d20 + 2d6. Ogni tipo ha il suo numero, modificabile o rimovibile.',
            'La soglia vale per il totale («Soglia sul totale», raggiunta o non superata) o per ogni dado («Soglia per dado»: per esempio ogni dado da 4 in su è un successo).',
            'Ognuno, giocatore o Master, salva i propri tiri in «I miei tiri», li ricarica con un tocco e li aggiorna quando il personaggio cresce. Il Master conserva anche i «Tiri della campagna» per tutto il tavolo.',
        ],
    ],

    '0.65.1' => [
        'date' => '2026-10-17',
        'title' => 'Accessi verificati ovunque',
        'items' => [
            'Digitare un indirizzo a mano non apre mai una campagna, una scheda o un file a cui non si ha diritto. Un account che non è membro riceve ora «Pagina non trovata», come per un indirizzo inesistente: provare dei numeri non rivela nemmeno che una campagna esiste.',
        ],
    ],

    '0.65.0' => [
        'date' => '2026-10-17',
        'title' => 'I dadi al tavolo',
        'items' => [
            'Nuovo pulsante «Dadi» in fondo a ogni pagina della campagna: il numero e il tipo di dadi (da 4 a 100 facce, o altro), poi come leggerli: sommare, raggiungere una soglia, restare sotto una soglia, contare i successi, tenere i migliori o i più bassi, leggere in cifre. Con un bonus e dadi che si ritirano sul massimo (+) o sull\'1 (−).',
            'Il Master chiede un tiro a tutto il tavolo o ad alcuni personaggi, alla vista di tutti o in modo discreto. Ai giocatori interessati si apre una finestra; senza soglia, ognuno inserisce la sua e aggiunge il suo bonus, e una soglia nascosta resta al Master, che vede chi ha tirato cosa.',
            'I tiri al tavolo rotolano per qualche secondo sullo schermo del tavolo; un tiro discreto lo vede solo il Master. Il Master salva i suoi tiri abituali per tutto il tavolo, e i dadi si disattivano in «Funzioni della campagna».',
        ],
    ],

    '0.64.1' => [
        'date' => '2026-10-17',
        'title' => 'Sagome misurate come il righello',
        'items' => [
            'Le sagome si impostano nella stessa unità del righello (metri, piedi…): la «Dimensione reale di una casella», in Griglia, calibra entrambi, qualunque sia la dimensione delle caselle sullo schermo.',
            'Un pulsante «Elimina» più visibile su ogni sagoma, ed «Elimina tutte le sagome» a fine incantesimo.',
            'Ogni link di «Partecipa a distanza» ha il suo codice QR, da mostrare perché i giocatori lo scansionino con il telefono.',
        ],
    ],

    '0.64.0' => [
        'date' => '2026-10-17',
        'title' => 'Sagome d\'area sulla mappa',
        'items' => [
            'Nuove sagome nella pagina della mappa: cerchio, quadrato, rettangolo, cono e linea, misurate in caselle (o nell\'unità della scala). Si trascinano sulla mappa, si orientano, si nascondono, e l\'elenco dei segnalini colpiti appare subito.',
            'Le sagome visibili compaiono sullo schermo del tavolo, sotto i segnalini.',
        ],
    ],

    '0.63.0' => [
        'date' => '2026-10-17',
        'title' => 'Mappa di combattimento condivisa',
        'items' => [
            'Sullo schermo del tavolo condiviso, ogni giocatore muove la pedina del proprio personaggio. Nella mappa, «Mosso da» affida un\'altra pedina a un giocatore: seguace, famiglio, nemico controllato…',
            'Nuovo ordine di iniziativa, dal telecomando o dalla mappa: PG e nemici con il loro valore, turno in corso, round. Il GM lo mostra sopra lo schermo quando vuole; le righe nascoste restano a lui, e una fila «Presenti anche» mostra PNG o oggetti senza iniziativa.',
            '«Partecipa a distanza» nel telecomando: il link dello schermo per i giocatori e un link ospite senza account, in sola lettura e revocabile, per chi vuole solo guardare.',
        ],
    ],

    '0.62.0' => [
        'date' => '2026-10-13',
        'title' => 'La community su Discord',
        'items' => [
            'SagaWyn ha il suo server Discord: un link in fondo a ogni pagina e nella pagina iniziale per fare domande, proporre idee e incontrare altri master.',
        ],
    ],

    '0.61.0' => [
        'date' => '2026-10-13',
        'title' => 'Modalità demo prima del Kickstarter',
        'items' => [
            'Nuova modalità demo, da attivare in Amministrazione › Piani: ogni nuovo account riceve il piano Demo, con la propria copia della campagna dimostrativa e un banner che annuncia la prossima campagna di crowdfunding.',
            'La dimostrazione di un account demo è bloccata: tutto si visita, ma si usano solo la sessione, gli appunti, le pedine della mappa e lo schermo del tavolo. L’account può anche creare una piccola campagna propria (50 MB, 3 membri, senza IA né esportazione).',
            'Un invito di un amministratore apre un account normale. In «Account», «Sblocca» porta un account demo al piano gratuito e avvia la sua prova, che la demo non consuma.',
        ],
    ],

    '0.60.0' => [
        'date' => '2026-10-13',
        'title' => 'Contenuti della casa',
        'items' => [
            'Nuova casella «Contenuto della casa» sulle schede (PNG, incantesimi, oggetti, luoghi…): senza spunta la scheda è ufficiale. Un distintivo «Casa» la segnala nell’elenco e sulla scheda, e un filtro Ufficiale / Casa ordina l’universo.',
            'Per una scheda della casa o una regola di origine «Casa», la versione ufficiale sostituita si annota accanto (per esempio l’iniziativa con 1d10 invece di 1d20): resta nella zona del master, ripiegata sotto la scheda o la regola.',
            'L’elenco delle regole si filtra per origine. Esportazione e importazione della campagna conservano i contenuti della casa e la versione ufficiale.',
        ],
    ],

    '0.59.1' => [
        'date' => '2026-10-13',
        'title' => 'Verifica di messa online più precisa',
        'items' => [
            'Amministrazione: `sagawyn:check` non segnala più per errore un log in modalità debug quando LOG_LEVEL è warning.',
        ],
    ],

    '0.59.0' => [
        'date' => '2026-10-13',
        'title' => 'Il logo di Autistic Intelligence',
        'items' => [
            'Il logo di Autistic Intelligence, editore di SagaWyn, ora compare in fondo alle pagine, nella schermata di accesso e nelle note legali. Segue il tema chiaro o scuro.',
        ],
    ],

    '0.58.0' => [
        'date' => '2026-10-12',
        'title' => 'Un indizio può essere una menzogna',
        'items' => [
            'Indizi: nuova natura «Menzogna», accanto a Voce, Indizio e Verità, con il suo filtro. Come le altre nature, la vede solo il master: il giocatore a cui viene rivelato legge solo l’indizio.',
        ],
    ],

    '0.57.0' => [
        'date' => '2026-10-12',
        'title' => 'Preparare un\'intera campagna con un\'IA',
        'items' => [
            'Le mie campagne → Importa una campagna: nuovo link «Prepara l’archivio con un’IA». Il prompt (o lo skill di Claude) fa leggere il manuale di gioco di ruolo alla vostra IA, che crea un archivio .zip: gioco, mondo, campagna, schede, scene e documenti vengono creati in un colpo solo.',
        ],
    ],

    '0.56.1' => [
        'date' => '2026-10-11',
        'title' => 'Pagine gioco e mondo leggibili su telefono',
        'items' => [
            'Su telefono, l’immagine di un gioco o di un mondo compare sopra il nome a tutta larghezza: nome e descrizione non sono più schiacciati e il pulsante Modifica non li copre più.',
        ],
    ],

    '0.56.0' => [
        'date' => '2026-10-11',
        'title' => 'I segreti diventano indizi',
        'items' => [
            'I «segreti» della campagna ora si chiamano «indizi»: pagina Indizi, pannello di schede, scene e documenti, modalità Sessione, ricerca, rivelazioni e aiuto. I tuoi dati non cambiano.',
        ],
    ],

    '0.54.0' => [
        'date' => '2026-10-11',
        'title' => 'I giocatori modificano il loro personaggio',
        'items' => [
            'Pagina Campi: «Modificabile dal giocatore» si spunta per un intero gruppo (caratteristiche, abilità…) o per tutti i campi pubblici con un clic.',
            'Nuova opzione nella pagina Giocatori: il master può lasciare che ogni giocatore modifichi ritratto, riassunto e descrizione del proprio personaggio. La zona del master resta nascosta.',
        ],
    ],

    '0.51.0' => [
        'date' => '2026-10-11',
        'title' => 'Badge Fondatore e codici regalo',
        'items' => [
            'I sostenitori del Kickstarter possono essere segnati come Fondatore nella console di amministrazione: un badge ★ Fondatore visibile nelle preferenze, nell’elenco dei membri e nella scheda del personaggio.',
            'I Fondatori hanno spazio in più rispetto al loro piano, gratuito o premium (500 MB di default, regolabile in «Piani»).',
            'Nuova pagina «Le mie ricompense» (dalle preferenze): la tua ricompensa Kickstarter, codici regalo Premium da regalare tramite link, con il nome di chi ha usato ciascuno, e un campo per usare un codice ricevuto. I mesi si aggiungono dopo il Premium attuale.',
        ],
    ],

    '0.50.1' => [
        'date' => '2026-10-11',
        'title' => 'Salute: filtro per ruolo',
        'items' => [
            'Nella scheda Salute, un filtro per ruolo dell’account (senza amministratori, amministratori, premium, prova, gratuito).',
            'La campagna dimostrativa non è più conteggiata nelle statistiche.',
        ],
    ],

    '0.50.0' => [
        'date' => '2026-10-10',
        'title' => 'Salute della piattaforma',
        'items' => [
            'Nuova scheda Salute nella console di amministrazione: persone connesse e numero di accessi per giorno, settimana o mese, giocatori per campagna e per GM, spazio occupato (file, database, disco) e tempo di risposta del server ora per ora.',
            'La console avvisa quando il server rallenta: più del 5 % delle richieste oltre un secondo in 24 ore.',
        ],
    ],

    '0.49.1' => [
        'date' => '2026-10-10',
        'title' => 'Un aiuto più completo',
        'items' => [
            'L’aiuto risponde a 19 nuove domande: gioco, mondo e campagna, la demo, ricerca e link, tag, scenari, modalità Sessione, campi del gioco, regole, duplicazione, scambi, l’uscita di un giocatore, il diario, la fine di Premium e, per i giocatori: scheda, note, messaggi e pareri.',
        ],
    ],

    '0.49.0' => [
        'date' => '2026-10-10',
        'title' => 'La demo mostra tutto SagaWyn',
        'items' => [
            'La campagna dimostrativa usa ora ogni funzione: un personaggio giocante tratto da un pregenerato, con ciò che ha ricevuto (scheda, documento, regola, segreto, oggetti) e che il master può «Vedere come»; note di sessione; una lista «Da giocare»; schede fissate; campi riferimento, documento e comuni a due tipi; un allegato del master; segreti di ogni natura; tag colorati; copertine per il gioco e il mondo.',
            'Ricaricare la demo riutilizza il tipo di scheda «Fazione» già creato invece di aggiungerne un secondo.',
        ],
    ],

    '0.48.0' => [
        'date' => '2026-10-10',
        'title' => 'Una dimostrazione più completa',
        'items' => [
            'La campagna dimostrativa «Il Giuramento di Pierrecendre» contiene ora tre atmosfere sonore originali collegate alle scene, una prima sessione già giocata con il suo riassunto e una richiesta di parere ai giocatori.',
            'Correzione: la dissolvenza tra due brani non interrompe più la riproduzione in alcuni browser.',
        ],
    ],

    '0.47.0' => [
        'date' => '2026-10-10',
        'title' => 'LoreMundi diventa SagaWyn',
        'items' => [
            'LoreMundi ora si chiama SagaWyn, sempre pubblicato da Autistic Intelligence. Every world has a story.',
            'Campagne, account e archivi non cambiano: i backup creati con LoreMundi o CodexFlow si importano ancora. I file scaricati ora iniziano con «sagawyn-».',
        ],
    ],

    '0.46.0' => [
        'date' => '2026-10-10',
        'title' => 'Pareri dei giocatori',
        'items' => [
            'Alla fine di una sessione o di una campagna, chiedi ai tuoi giocatori un voto da 1 a 5 stelle, cosa è piaciuto e cosa potrebbe andare meglio («Pareri dei giocatori» nella campagna, o dalla pagina di una sessione).',
            'Risposte anonime o firmate, a scelta del Master: i giocatori lo sanno prima di rispondere. Vedi la media, la distribuzione dei voti e tutti i commenti.',
        ],
    ],

    '0.45.0' => [
        'date' => '2026-10-10',
        'title' => 'Libreria audio',
        'items' => [
            'Nuova pagina «Suoni»: carica le tue musiche e atmosfere (mp3, ogg, m4a, wav, flac), ordinale con i tag e collegale alle scene.',
            'In modalità Sessione, il blocco «Musica» propone prima i suoni della scena: «Qui» li riproduce sul tuo dispositivo, senza interrompersi quando cambi pagina; «Tavolo» li riproduce sullo schermo del tavolo, con pausa, loop e volume gestiti dalla sessione.',
        ],
    ],

    '0.44.0' => [
        'date' => '2026-10-09',
        'title' => 'Pronto per la messa online',
        'items' => [
            'L’amministrazione viene avvisata nella console quando l’installazione ha un punto bloccante (debug attivo, e-mail non configurate, tempo reale non cifrato…).',
            'Il database e i file caricati vengono salvati ogni notte sul server.',
        ],
    ],

    '0.43.0' => [
        'date' => '2026-10-09',
        'title' => 'Affidabilità',
        'items' => [
            'Nuovi test automatici verificano in un vero browser l’aggiornamento senza tempo reale, «Ricordami», il link «Vai al contenuto», la visualizzazione sul telefono con testo molto grande e una funzione disattivata dal master con una pagina aperta.',
        ],
    ],

    '0.42.0' => [
        'date' => '2026-10-09',
        'title' => 'Descrizioni formattate',
        'items' => [
            'Le descrizioni di gioco, mondo, scenario e documento hanno lo stesso editor delle schede: grassetto, corsivo, sottotitoli, elenchi, citazioni e collegamenti [[ ]] alle schede in una campagna.',
        ],
    ],

    '0.41.0' => [
        'date' => '2026-10-09',
        'title' => 'Campi condivisi',
        'items' => [
            'Un campo può riguardare più tipi di scheda insieme, per esempio i punti ferita di personaggi e creature; i modelli di gioco e i file dei campi mantengono questa scelta.',
        ],
    ],

    '0.40.0' => [
        'date' => '2026-10-09',
        'title' => 'Applicazione e sicurezza',
        'items' => [
            'L’app installata sul telefono ha una descrizione nella tua lingua e un’icona adatta alle icone rotonde di Android.',
            'Offline, le pagine restano leggibili e i pulsanti di modifica sono grigi finché la rete non torna.',
            'Il browser accetta ormai solo script, immagini e connessioni provenienti da LoreMundi stesso.',
        ],
    ],

    '0.39.0' => [
        'date' => '2026-10-09',
        'title' => 'Account e amministrazione',
        'items' => [
            'Si può segnalare un problema senza account, dalle pagine di accesso e registrazione o dall’aiuto, lasciando un indirizzo per la risposta.',
            'Le Preferenze mostrano la data di inizio dell’abbonamento e, finché il pagamento non è attivo, che il passaggio a Premium aprirà presto.',
            'Console di amministrazione: giorni di accesso anziché accessi, data dell’ultimo accesso e avviso quando l’invio push è impossibile o fallisce.',
        ],
    ],

    '0.38.0' => [
        'date' => '2026-10-09',
        'title' => 'Ricerca',
        'items' => [
            'La ricerca trova anche altre forme francesi di una parola: «lanternes» trova «lanterne», «éteinte» trova «éteintes».',
            'La ricerca dalla home cerca anche nei tuoi mondi e giochi non collegati ad alcuna campagna.',
        ],
    ],

    '0.37.0' => [
        'date' => '2026-10-09',
        'title' => 'Giocatori e ruoli',
        'items' => [
            'Una scheda rivelata mostra al giocatore le illustrazioni e i file pubblici, e le relazioni pubbliche verso schede che conosce.',
            'I giocatori hanno una «Cronaca della campagna» sulla scheda: sessioni, eventi giocati noti al tavolo e messaggi al gruppo.',
            'I co-Master vedono le pagine del gioco e del mondo in sola lettura, scaricano l’archivio e il modello del gioco, e gestiscono i campi se il proprietario lo spunta in «Membri». Un co-Master declassato non conserva le notifiche ricevute come Master.',
        ],
    ],

    '0.36.0' => [
        'date' => '2026-10-09',
        'title' => 'Comodità del Master e della sessione',
        'items' => [
            'Le pagine di un PDF mostrato al tavolo si girano dal telecomando, dalla pagina del documento o con le frecce dello schermo del Master, e i giocatori che seguono lo schermo girano con lui. Il lettore PDF indica «pagina n / N» e permette di andare a una pagina.',
            'In modalità Sessione, «Evento giocato» aggiunge il testo scritto alla cronologia, legato alla sessione e alla scena in corso.',
            'Un personaggio può essere restituito a uno qualsiasi dei suoi ex giocatori tornato nella campagna, che ritrova i propri scambi privati con il Master, senza quelli dei giocatori intermedi.',
            'Lo stato di una scheda appare sotto il titolo; i pregenerati (tag «pregenerato») sono proposti per primi in «Nuovo personaggio»; i contatori della pagina Tag elencano gli elementi; una demo caricata più volte numera campagna, gioco e mondo; il ruolo spettatore precisa che vede lo schermo da tavolo anche non condiviso.',
        ],
    ],

    '0.35.0' => [
        'date' => '2026-10-09',
        'title' => 'Correzioni del collaudo finale',
        'items' => [
            'La pagina di una sessione ha un riassunto scritto dal Master e mostra gli eventi giocati e tutto ciò che è stato rivelato o dato durante la sessione.',
            'La cronologia delle modifiche di una scheda conserva anche relazioni, file allegati, tag e stato nella campagna. La pagina di un mondo mostra la sua storia, quella di un gioco i suoi tipi di scheda.',
            'Una funzione disattivata dal Master o dal piano lo è anche nelle pagine rimaste aperte, e la modalità Sessione si apre quando le mappe sono disattivate.',
            'Correzioni: quantità annunciata in uno scambio, casella sì/no mai compilata, pagina «non trovata» tradotta, pagine leggibili sul telefono con testo grande, accessi contati una volta, aiuto aggiornato.',
        ],
    ],

    '0.34.0' => [
        'date' => '2026-10-08',
        'title' => 'Funzioni per campagna',
        'items' => [
            'Nella pagina della campagna, il Master spunta le funzioni che servono al suo tavolo: schermo del tavolo, mappe, scambi tra giocatori, grafo, cronologia, assistente IA. Una funzione senza spunta scompare per tutti senza cancellare nulla; torna appena la si spunta di nuovo.',
        ],
    ],

    '0.33.0' => [
        'date' => '2026-10-08',
        'title' => 'Rifiniture',
        'items' => [
            'La ricerca mostra il campo della scheda che contiene la parola trovata, con il suo nome.',
            'Grafo: nomi ed etichette sovrapposti vengono spostati o nascosti; passando su una scheda riappaiono.',
            '«Rivela o dai»: una casella «Tutti i personaggi attivi» seleziona tutto il tavolo in un colpo.',
            'Un giocatore rimosso e poi reinvitato ritrova il suo vecchio personaggio con un clic, dalla pagina Personaggi.',
            'Senza tempo reale (server Reverb assente o spento), campanella, messaggi e schede si aggiornano ogni 30 secondi.',
        ],
    ],

    '0.32.0' => [
        'date' => '2026-10-08',
        'title' => 'PDF e documenti',
        'items' => [
            'I PDF si aprono in un visualizzatore integrato, uguale su computer, tablet e telefono, con zoom e download.',
            'Sullo schermo del tavolo, un PDF si mostra una pagina alla volta, adattato allo schermo; le frecce girano le pagine.',
            'La scheda del personaggio mantiene il nome del file originale.',
            '«Usato da» indica lo scenario di ogni scena.',
            'Le pagine di un gioco e di un mondo possono avere un’immagine.',
        ],
    ],

    '0.31.0' => [
        'date' => '2026-10-08',
        'title' => 'Funzioni Premium ✦',
        'items' => [
            'Una piccola stella ✦ segnala le funzioni Premium. Quando il piano del proprietario di una campagna non le include, restano visibili, in grigio, con una spiegazione.',
            'Alla fine di una prova o di un abbonamento non si cancella nulla: campagne, mappe, messaggi e file restano, si spengono solo le funzioni ✦.',
            'L’amministratore può offrire un periodo regalo (Natale…) in cui gli account gratuiti hanno tutte le funzioni Premium.',
        ],
    ],

    '0.30.0' => [
        'date' => '2026-10-08',
        'title' => 'Scrittura e collegamenti',
        'items' => [
            'I campi «testo lungo» e le note del Master delle regole hanno l’editor ricco e i collegamenti [[ ]].',
            'Sulla sua scheda il giocatore vede i testi lunghi formattati, con collegamenti alle schede che il suo personaggio conosce.',
            'La nota rapida di sessione propone le schede appena scrivi «[[».',
            'Le copie sono numerate («copia 2», «copia 3») e un’importazione non riusa mai il nome di un gioco, mondo o campagna che hai già.',
        ],
    ],

    '0.29.0' => [
        'date' => '2026-10-08',
        'title' => 'Sicurezza dell’account',
        'items' => [
            'Un nuovo indirizzo e-mail viene adottato solo dopo un clic sul link che riceve; poi il vecchio indirizzo viene avvisato.',
            'I tentativi sui moduli dell’account sono contati per modulo e per indirizzo e-mail, e la pagina di attesa dice quanti secondi aspettare.',
            'Solo gli script di LoreMundi possono essere eseguiti nelle sue pagine.',
            'L’eliminazione dell’account indica che i tuoi messaggi vengono cancellati.',
        ],
    ],

    '0.28.0' => [
        'date' => '2026-10-08',
        'title' => 'E-mail con i colori di LoreMundi',
        'items' => [
            'Le e-mail (password dimenticata, cambio di indirizzo) hanno il logo e i colori di LoreMundi.',
            'Ogni e-mail parte nella lingua del destinatario, anche quando la invia l’amministratore.',
        ],
    ],

    '0.27.0' => [
        'date' => '2026-10-08',
        'title' => 'Una vetrina pubblica',
        'items' => [
            'Una pagina iniziale presenta LoreMundi ai visitatori e ai motori di ricerca, in tutte le 8 lingue.',
            'L’aiuto si legge senza account e ha una sezione «Il tuo account»: piani, e-mail, password, autenticazione a due fattori, dati.',
        ],
    ],

    '0.26.0' => [
        'date' => '2026-10-08',
        'title' => 'Correzioni dal collaudo v0.25.0',
        'items' => [
            'Una pagina rimasta aperta ricontrolla i vostri permessi a ogni azione: un giocatore rimosso o un co-master declassato non riceve più nulla di nuovo.',
            'Il giocatore a cui si affida un personaggio non legge più la conversazione privata del giocatore precedente con il master.',
            'I personaggi pregenerati della campagna dimostrativa sono proposti in «Nuovo personaggio».',
            'Autenticazione a due fattori: i codici di recupero funzionano all’accesso, e l’amministratore può rimuoverla da un account bloccato.',
            'Nuove icone LoreMundi (scheda, app installata, notifiche).',
            'Schermo del tavolo condiviso leggibile sul telefono; intestazioni di schede e pagine corrette su telefono e tablet, anche con testo grande.',
            'Correzioni: notifiche push senza errori, pulsanti della finestra «ricevuto», riferimenti a schede dopo una ridenominazione, quantità di uno scambio, righello delle mappe, avviso «Offline», messaggi di errore tradotti, link «Vai al contenuto».',
        ],
    ],

    '0.25.0' => [
        'date' => '2026-10-08',
        'title' => 'Importare un manuale di gioco con un’IA',
        'items' => [
            'In «Importa», «Prepara i file con un’IA» fornisce un prompt da incollare nell’IA che preferisci insieme al PDF di un gioco o di uno scenario: prepara i file di importazione (campi, schede, regole, scene) e una guida passo passo. Il prompt è disponibile anche come skill di Claude.',
        ],
    ],

    '0.24.0' => [
        'date' => '2026-10-08',
        'title' => 'Sicurezza e dati personali',
        'items' => [
            '«Il mio account», in Preferenze: cambia il nome, l’indirizzo e-mail (il precedente viene avvisato) e la password.',
            'Autenticazione a due fattori facoltativa, in Preferenze: un codice da un’app sul telefono, con codici di recupero.',
            '«I miei dati», in Preferenze: scarica ciò che LoreMundi conserva su di te o elimina il tuo account.',
            'Password di almeno 10 caratteri con lettere e cifre; cambiare la propria disconnette gli altri dispositivi. La console di amministrazione richiede di nuovo la password.',
            'Nuova pagina «Privacy e note legali».',
            'Un giocatore rimosso dalla campagna, o diventato spettatore, non gioca più il suo personaggio e non riceve più nulla di ciò che gli viene rivelato. Altri controlli dei permessi sono stati rafforzati sul server.',
        ],
    ],

    '0.23.0' => [
        'date' => '2026-10-08',
        'title' => 'CodexFlow diventa LoreMundi',
        'items' => [
            'CodexFlow ora si chiama LoreMundi, pubblicato da Autistic Intelligence. Every world has a story.',
            'Campagne, account e archivi non cambiano: i backup creati con CodexFlow si importano ancora. I file scaricati ora iniziano con «loremundi-».',
        ],
    ],

    '0.22.0' => [
        'date' => '2026-10-08',
        'title' => 'Backup completo',
        'items' => [
            'Il proprietario può scaricare un backup completo: l’archivio della campagna con il tavolo (personaggi, ciò che hanno ricevuto, sessioni, note, messaggi e registro), senza note «Solo io» né indirizzi email. All’importazione i personaggi tornano senza giocatore, pronti da assegnare.',
        ],
    ],

    '0.21.0' => [
        'date' => '2026-10-08',
        'title' => 'Ricerca dalla home',
        'items' => [
            'Fuori da una campagna, la barra di ricerca cerca in tutte le tue campagne, nei loro mondi e giochi, ciascuna con i tuoi permessi: tutto da Master, ciò che conosce il tuo personaggio da giocatore.',
        ],
    ],

    '0.20.0' => [
        'date' => '2026-10-08',
        'title' => '«Citato in» completo, duplicazione senza stati',
        'items' => [
            '«Citato in» mostra anche la cronologia, i segreti e i campi di altre schede che menzionano la scheda.',
            'Una campagna duplicata riparte dalle schede originali: lo stato «morto» o «prigioniero» non viene più copiato, a meno che tu non spunti la casella per mantenerlo.',
        ],
    ],

    '0.19.0' => [
        'date' => '2026-10-08',
        'title' => 'Segreti e nuovi campi',
        'items' => [
            'Ogni segreto ha un tipo (voce, indizio o verità) e uno stato che dipende da chi lo conosce: nascosto, parziale o rivelato. Puoi filtrare i segreti per entrambi.',
            'Tre nuovi tipi di campo: link web, file (un documento della campagna) e riferimento a un’altra scheda, che resta collegata anche se la scheda viene rinominata.',
            'Una campagna duplicata mantiene i collegamenti tra le sue schede copiate.',
        ],
    ],

    '0.18.0' => [
        'date' => '2026-10-08',
        'title' => 'Nuovo personaggio, Le mie campagne, pagine di gioco e mondo',
        'items' => [
            'Quando un giocatore riceve un nuovo personaggio, il Master spunta ciò che passa dal precedente: conoscenze, informazioni, documenti e regole vengono copiati, gli oggetti cambiano di mano.',
            'Le mie campagne: pulsante «Riprendi», data dell’ultima sessione e campagne archiviate a parte.',
            'Ogni gioco e ogni mondo ha la sua pagina: descrizione, campagne, regole, documenti, campi o schede riutilizzabili.',
        ],
    ],

    '0.17.0' => [
        'date' => '2026-10-08',
        'title' => 'Editor avanzato e note dei giocatori',
        'items' => [
            'I testi lunghi (descrizioni, note del Master, scene, regole, cronologia, note dei giocatori) hanno un editor con grassetto, corsivo, sottotitoli, elenchi e citazioni; «[[» propone sempre le schede da collegare.',
            'I giocatori collegano le loro note alle schede che il loro personaggio conosce, e solo a quelle.',
            'La pagina di una sessione mostra anche le note prese dai giocatori durante la sessione, tranne quelle che tengono per sé.',
        ],
    ],

    '0.16.0' => [
        'date' => '2026-10-08',
        'title' => 'Abbonamento Premium e prova gratuita',
        'items' => [
            'Passa a Premium da «Preferenze»: pagamento mensile o annuale protetto da Stripe, con fatture e disdetta nel portale Stripe. Il Premium dura fino alla fine del periodo pagato.',
            'Prova gratuita: sei settimane con tutte le funzioni, dalla tua prima campagna come Master. Un giocatore che non è mai Master non la inizia. La durata si imposta nella console di amministrazione.',
            'Una scheda «Evoluzioni» nella console di amministrazione per tenere la tabella di marcia della piattaforma.',
        ],
    ],

    '0.15.0' => [
        'date' => '2026-10-08',
        'title' => 'Console di amministrazione e piani',
        'items' => [
            'Una console di amministrazione: gli account con il loro piano, le date dell’abbonamento, lo spazio usato, la presenza di una chiave IA, le campagne e gli accessi, senza dati personali. L’amministratore imposta il piano di ciascuno e può inviare un link per reimpostare la password, senza mai vederla.',
            'Tre piani: amministratore, premium e gratuito. Spazio, numero di campagne e funzioni del piano gratuito si regolano nella console; giocare, essere co-master o spettatore non conta mai.',
            'Il backlog raccoglie i problemi segnalati, i bug e le evoluzioni, con stato, priorità e versione della correzione; vi sono conservati anche i collaudi versione dopo versione. Il tuo piano compare in «Preferenze».',
        ],
    ],

    '0.14.0' => [
        'date' => '2026-10-08',
        'title' => 'Dopo il collaudo: scambi convalidati dal master',
        'items' => [
            'Per impostazione predefinita il master convalida gli scambi tra giocatori: l’oggetto o la conoscenza cambia mano solo dopo l’accettazione. Una casella in «Personaggi dei giocatori» permette di autorizzarli sempre.',
            'Telecomando: all’ultimo elemento della scena, «Avanti» diventa «Termina» e svuota lo schermo.',
            'Diario più leggibile per gli oggetti convalidati, messaggi più chiari in «Segnala un problema» e un solo modo di rivolgersi a te in ogni lingua.',
        ],
    ],

    '0.13.1' => [
        'date' => '2026-10-08',
        'title' => 'Correzioni del collaudo V1',
        'items' => [
            'La ricerca del master trova anche segreti, informazioni e oggetti dati, note condivise dei giocatori e tag delle scene.',
            'Duplicare una campagna copia i suoi segreti e la cronologia preparata; un link a una scheda aperto durante la sessione si mostra in un pannello laterale, senza lasciare la sessione.',
            'Pagine di errore tradotte, e-mail della password nella tua lingua, modalità Sessione e Documenti leggibili sul telefono, un menu per i link nascosti sugli schermi piccoli.',
            'Un personaggio a riposo e un oggetto convalidato dal master non sono più modificabili dal giocatore; il righello temporaneo della mappa si cancella da solo.',
        ],
    ],

    '0.13.0' => [
        'date' => '2026-10-08',
        'title' => 'La tua IA, senza copia e incolla',
        'items' => [
            'In «Preferenze» puoi salvare una chiave API a tuo nome di Claude (Anthropic), ChatGPT (OpenAI) o Le Chat (Mistral). L’assistente IA propone allora «Analizza direttamente»: le proposte arrivano senza copia e incolla.',
            'Le chiamate sono fatturate dal fornitore sul tuo account. La chiave è cifrata, mai più mostrata né esportata, e la modalità «testo da incollare» resta gratuita.',
        ],
    ],

    '0.12.0' => [
        'date' => '2026-10-08',
        'title' => 'Un assistente IA, senza abbonamento',
        'items' => [
            'Nuovo strumento «Assistente IA» nella campagna: LoreMundi prepara un testo con le note della sessione e il contesto della campagna, da incollare nell’IA che preferisci. La sua risposta, incollata qui, diventa un insieme di proposte: riassunto, eventi giocati, relazioni, stati, note di campagna, rivelazioni.',
            'Ogni proposta si accetta, si modifica o si rifiuta. Nulla cambia nella campagna senza di te, e le relazioni o gli stati proposti restano propri della campagna, senza toccare il mondo condiviso.',
        ],
    ],

    '0.11.1' => [
        'date' => '2026-10-08',
        'title' => 'La dimostrazione nella tua lingua',
        'items' => [
            'La campagna dimostrativa esiste nelle otto lingue dell’interfaccia. Si carica nella tua, o in quella scelta accanto al pulsante.',
        ],
    ],

    '0.11.0' => [
        'date' => '2026-10-08',
        'title' => 'Una campagna dimostrativa',
        'items' => [
            'Campagna dimostrativa da caricare con un clic da «Le mie campagne»: un gioco inventato, «Brume & Serment», e una trama completa in tre sessioni, con schede, ritratti, relazioni, mappa, segreti, regole, cronologia e personaggi pregenerati.',
            'I piccoli pulsanti a icona della pagina di campagna non si spostano più al passaggio del mouse: il nome compare in un suggerimento, sopra al resto.',
            'I tag si inseriscono allo stesso modo ovunque, e una scheda propone «Aggiungi un tag» proprio sotto il titolo.',
        ],
    ],

    '0.10.0' => [
        'date' => '2026-10-07',
        'title' => 'Una pagina di campagna più chiara',
        'items' => [
            'La pagina della campagna riordinata: la modalità Sessione in evidenza, quattro aree di preparazione e gli altri strumenti come piccoli pulsanti con icona.',
            'Atmosfera dello schermo del tavolo, da scegliere dal telecomando: Notte, Pergamena, Ardesia o Grimorio.',
        ],
    ],

    '0.9.0' => [
        'date' => '2026-10-07',
        'title' => 'Portarsi via la campagna',
        'items' => [
            'Esportare un’intera campagna in un archivio .zip: gioco, mondo, schede, scenari, documenti, mappe, segreti, cronologia e file.',
            'Importare un archivio da «Le mie campagne»: ricrea la campagna, da te o da un altro master.',
            'Modelli di gioco condivisibili: tipi di scheda, campi, etichette e regole, senza contenuti di campagna.',
        ],
    ],

    '0.8.0' => [
        'date' => '2026-10-07',
        'title' => 'Il grafo e la cronologia',
        'items' => [
            'Grafo delle relazioni: tutte le schede collegate, o la rete attorno a una scheda, con profondità e filtro per tipo.',
            '«Vedi come» nel grafo: la rete come la conosce un personaggio. I giocatori vi accedono dal loro personaggio.',
            'Cronologia: storia del mondo, eventi previsti e giocati, con date libere come «Giorno 3».',
            'Un evento giocato annotato durante la sessione è collegato alla sessione e alla scena in corso.',
        ],
    ],

    '0.7.0' => [
        'date' => '2026-10-07',
        'title' => 'Intorno al tavolo',
        'items' => [
            'Mappe: un’immagine sullo schermo da tavolo, che ingrandisci e sposti, con una griglia quadrata facoltativa e una scala.',
            'Segnalini facoltativi, collegati alle schede (nome e ritratto): spostarli, ridimensionarli, mostrarli o nasconderli ai giocatori.',
            'Righello temporaneo: traccia una linea, la distanza appare in caselle o in metri.',
            'Telecomando: dal tuo telefono, svuota lo schermo, passa all’elemento successivo della scena, controlla la mappa.',
            'Giocatori: ciò che il Master ti rivela o ti dà appare subito, senza passare dalle notifiche.',
        ],
    ],

    '0.6.0' => [
        'date' => '2026-10-07',
        'title' => 'La memoria della campagna',
        'items' => [
            'Segreti: un’informazione a sé, collegata a schede, scene o documenti, rivelata con un clic a un personaggio o a tutto il tavolo.',
            'Cronologia delle rivelazioni: chi ha saputo cosa, quando, in quale sessione e in quale scena; ogni rivelazione può essere annullata.',
            '«Vedi come»: il Master vede la campagna esattamente come un personaggio, in sola lettura.',
            '«Citato in» mostra anche le regole e le note di sessione che citano una scheda.',
            'Relazioni: l’inversa («lavora per» / «impiega») si compila da sola.',
        ],
    ],

    '0.5.0' => [
        'date' => '2026-10-07',
        'title' => 'Mettere in ordine, insieme',
        'items' => [
            'Una pagina «Tag» per rinominare, colorare, unire ed eliminare i tuoi tag; anche le scene hanno i tag.',
            '«Duplica» una scheda, uno scenario o un\'intera campagna, per rigiocare con un altro tavolo.',
            'Nuovi ruoli: co-Master, che prepara e conduce con te, e spettatore, che guarda lo schermo da tavolo.',
            '«Mostra al tavolo» da una scheda, un ritratto, un\'illustrazione, un documento o una regola.',
            'Modalità Sessione: mostra una scheda o una regola con un clic, vedi la scena successiva, tasto N per prendere appunti.',
        ],
    ],

    '0.4.1' => [
        'date' => '2026-10-07',
        'title' => 'Aiuto e segnalazioni',
        'items' => [
            'Una pagina «Aiuto» risponde alle domande più frequenti, per il Master come per i giocatori.',
            '«Segnala un problema», in fondo a ogni pagina, invia il tuo messaggio al team insieme alla pagina interessata.',
        ],
    ],

    '0.4.0' => [
        'date' => '2026-10-07',
        'title' => 'Tutte le lingue',
        'items' => [
            'L\'interfaccia parla francese, inglese, tedesco, spagnolo, italiano, portoghese, olandese e polacco.',
            'La lingua segue quella del browser; ognuno può sceglierla in «Preferenze».',
            'Le notifiche arrivano nella lingua di chi le riceve.',
            'Il tema scuro resta attivo passando da una pagina all\'altra.',
            'Dopo aver modificato una scheda dalla pagina Personaggi, ci torni direttamente.',
        ],
    ],

    '0.3.0' => [
        'date' => '2026-10-07',
        'title' => 'Il legame vivo',
        'items' => [
            'Messaggistica tra il Master e i suoi giocatori, e pannello «Discussione» sempre a portata di mano (gruppo e privato).',
            'Notifiche: rivelazioni, oggetti ricevuti, messaggi, con un contatore nell\'intestazione.',
            'Tutto si aggiorna in diretta: messaggi, contatori, rivelazioni, senza ricaricare la pagina.',
            'LoreMundi si installa come un\'applicazione; la scheda del personaggio resta leggibile offline; notifiche sul dispositivo.',
            'Schermo da tavolo: mappe, immagini, schede e annunci sulla TV o sul proiettore, e condiviso con i giocatori se il Master lo desidera.',
            'I personaggi si scambiano oggetti e si trasmettono ciò che sanno.',
            'I giocatori annotano le loro conoscenze e aggiungono i loro oggetti; il Master convalida gli oggetti.',
            'Tema scuro, colore principale e dimensione del testo in «Preferenze».',
        ],
    ],

    '0.2.0' => [
        'date' => '2026-10-07',
        'title' => 'I giocatori',
        'items' => [
            'Inviti dei giocatori tramite link.',
            'Personaggi dei giocatori: scheda, foglio PDF, contatori (PF, magia, munizioni…) e campi modificabili dal giocatore.',
            'Rivelazioni e «Dai»: conoscenze, possedimenti, documenti e regole.',
            'Spazio del giocatore: note private o condivise, intenzioni «Da giocare», diario del personaggio.',
            'Registro delle modifiche: chi, cosa, quando, prima e dopo.',
        ],
    ],

    '0.1.0' => [
        'date' => '2026-10-06',
        'title' => 'Il Master da solo',
        'items' => [
            'Mondi, campagne e schede con zona pubblica e zona Master, collegamenti [[ ]] tra schede.',
            'Campi liberi per gioco, tipi di scheda, importazione CSV/JSON.',
            'Scenari, scene, regole e biblioteca di documenti.',
            'Modalità Sessione: scena in corso, schede utili, note rapide, «Da giocare» e schede fissate.',
            'Ricerca globale.',
        ],
    ],

];
