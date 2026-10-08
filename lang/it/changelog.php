<?php

// Novità per versione, dalla più recente alla più vecchia. Mostrate in
// «Novità» (una volta dopo ogni aggiornamento) e nella pagina con lo stesso nome.
return [

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
            'Nuovo strumento «Assistente IA» nella campagna: CodexFlow prepara un testo con le note della sessione e il contesto della campagna, da incollare nell’IA che preferisci. La sua risposta, incollata qui, diventa un insieme di proposte: riassunto, eventi giocati, relazioni, stati, note di campagna, rivelazioni.',
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
            'CodexFlow si installa come un\'applicazione; la scheda del personaggio resta leggibile offline; notifiche sul dispositivo.',
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
