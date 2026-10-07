<?php

/*
 * Campaña de demostración, texto en español (traducción de fr.php).
 *
 * Aquí solo está el texto: la estructura (quién está vinculado con quién, las fichas, los enlaces)
 * está en App\Actions\Demo\LoadDemoCampaign y es la misma en todos los idiomas. Cada traducción
 * reproduce exactamente estas claves. En las escenas, «[[clave]]» designa una ficha por su clave.
 */
return [
    'campaign' => [
        'name' => 'El Juramento de Pierrecendre',
        'description' => 'Campaña de demostración: tres sesiones en la ciudad portuaria de Pierrecendre, donde un juramento olvidado vuelve a reclamar lo que se le debe. Todo el contenido es original y libre de derechos.',
    ],
    'game' => [
        'name' => 'Bruma y Juramento',
        'description' => 'Juego de investigación y juramentos, inventado para la demostración. Cuatro características puntuadas de 1 a 5, juramentos que pesan en las tiradas y ninguna mecánica propietaria.',
    ],
    'world' => [
        'name' => 'Vehrmund',
        'description' => 'Un archipiélago de marcas anegadas y puertos construidos sobre la ceniza, donde la palabra dada vale tanto como un contrato.',
    ],

    'types' => [
        'faction' => 'Facción',
        'pregen' => 'Pregenerado',
    ],

    'groups' => [
        'traits' => 'Características',
        'profile' => 'Perfil',
        'secrets' => 'Secretos',
    ],

    'fields' => [
        'body' => 'Cuerpo',
        'skill' => 'Destreza',
        'mind' => 'Mente',
        'heart' => 'Corazón',
        'breath' => 'Aliento',
        'oaths' => 'Juramentos cumplidos',
        'trade' => 'Oficio',
        'trait' => 'Rasgo distintivo',
        'ties' => 'Vínculos',
        'hidden_oath' => 'Juramento oculto',
        'betrayal' => 'Qué le haría traicionar',
    ],

    'tags' => [
        'city' => 'ciudad',
        'act1' => 'acto 1',
        'act2' => 'acto 2',
        'act3' => 'acto 3',
        'intrigue' => 'intriga',
        'hall' => 'lonja',
        'quays' => 'muelles',
        'marshes' => 'marcas',
        'guard' => 'guardia',
        'pregen' => 'pregenerado',
        'base' => 'básico',
        'oaths' => 'juramentos',
        'house' => 'de la casa',
    ],

    'quay_state' => [
        'status' => 'bajo toque de queda',
        'notes' => 'Cerrado por la noche desde que Gueffroy se ahogó.',
    ],

    'entities' => [
        'city' => [
            'name' => 'Pierrecendre',
            'summary' => 'Ciudad portuaria levantada sobre la colada de ceniza de un volcán extinto.',
            'description' => "Quince mil almas, dos colinas y una bahía en media luna. Aquí se vive de la sal, del vidrio y de los juramentos: todo contrato cerrado en la Lonja se graba en una tablilla de ceniza vitrificada.\n\nLa ciudad huele a algas y a azufre frío. Las calles altas pertenecen a las casas de comercio; las bajas, a quienes trabajan el agua.",
            'gm_notes' => 'El verdadero poder está en la Lonja, no en la Guardia. Si los jugadores amenazan a la Guardia, Mornevent cede; si amenazan a la Lonja, toda la ciudad se cierra en banda.',
        ],
        'hall' => [
            'name' => 'La Lonja de los Juramentos',
            'summary' => 'Edificio de piedra clara donde se graban y se custodian los juramentos de la ciudad.',
            'description' => 'Una nave sin dios, llena de estanterías de tablillas vitrificadas. Cada tablilla lleva un juramento, su fecha y sus testigos. Se entra con la cabeza descubierta; se sale atado.',
            'gm_notes' => 'Las tablillas del año de la gran bruma fueron retiradas. Elzevir sabe dónde están: en el sótano del faro, no en la Lonja.',
        ],
        'quay' => [
            'name' => 'El Muelle de las Linternas',
            'summary' => 'El muelle de los pescadores, iluminado toda la noche por linternas de aceite de pescado.',
            'description' => 'Treinta linternas, encendidas al anochecer por un chaval al que pagan por semanas. Cuando una se apaga, los viejos vuelven a casa sin acabarse el vaso.',
            'gm_notes' => 'La tercera linterna empezando por el norte nunca se vuelve a encender: es la señal del Barquero.',
        ],
        'marshes' => [
            'name' => 'Las Marcas Anegadas',
            'summary' => 'Marismas saladas que separan Pierrecendre del continente, transitables con la marea baja.',
            'description' => 'Tres horas de camino seguro por marea; si no, doce horas de espera. Unas pértigas clavadas señalan el vado. Alguien las mueve.',
            'gm_notes' => 'Los Perjuros mueven las pértigas para que los viajeros se pierdan y desaparezcan.',
        ],
        'lighthouse' => [
            'name' => 'El Faro de Orvent',
            'summary' => 'Faro abandonado en la punta sur, cuya linterna aún se enciende algunas noches.',
            'description' => 'Treinta y dos metros de piedra, una escalera de caracol y un sótano que se inunda con la marea alta.',
            'gm_notes' => 'Las tablillas desaparecidas de la Lonja están en el sótano, dentro de un cajón de sal. El Desconocido las vigila.',
        ],
        'ysane' => [
            'name' => 'Dama Ysane Korr',
            'summary' => 'Guardiana de los juramentos: graba las tablillas y da fe de los contratos.',
            'description' => 'Sesenta años, manos quemadas por el horno de vitrificar y una memoria que nadie se atreve a contradecir.',
            'gm_notes' => 'Fue ella quien hizo retirar las tablillas del año de la gran bruma: su propio nombre figura en una de ellas. No es malvada; está aterrada.',
            'fields' => [
                'trade' => 'Guardiana de los juramentos',
                'trait' => 'Nunca mira dos veces a los ojos a la misma persona',
                'hidden_oath' => 'Juró, hace treinta años, dejar que los Ahogados se llevaran una barca al año. Desde entonces la ciudad no ha vuelto a sufrir un naufragio.',
                'betrayal' => 'La seguridad de su nieta',
            ],
        ],
        'brannoc' => [
            'name' => 'Brannoc el Barquero',
            'summary' => 'Pasa gente y cajas a través de las Marcas, a la hora que a él le conviene.',
            'description' => 'Alto, lento, habla poco y cuenta deprisa. Se sabe el vado de memoria, aunque lo muevan.',
            'gm_notes' => 'Sabe que las pértigas se mueven. Callará hasta que alguien le ofrezca saldar su deuda con el Hilo Gris.',
            'fields' => [
                'trade' => 'Barquero',
                'trait' => 'Nunca jura, algo que en la ciudad se toma por un insulto',
                'hidden_oath' => 'Debe once años de travesías gratuitas a la Compañía del Hilo Gris.',
                'betrayal' => 'Que le borren la deuda',
            ],
        ],
        'elzevir' => [
            'name' => 'Maese Elzevir',
            'summary' => 'Archivero de la Lonja, capaz de leer las tablillas más antiguas.',
            'description' => 'Bajito, empolvado de ceniza, incapaz de mentir sin toser.',
            'gm_notes' => 'Copió las tablillas retiradas antes de que se las llevaran. Su copia está en el forro de su abrigo.',
            'fields' => [
                'trade' => 'Archivero',
                'trait' => 'Tose cuando miente',
                'hidden_oath' => 'Juró a Ysane no hablar jamás del año de la gran bruma.',
                'betrayal' => 'Que le prometan que las tablillas volverán a su sitio',
            ],
        ],
        'vanne' => [
            'name' => 'Sor Vanne',
            'summary' => 'Cura a ahogados y quemados, sin preguntar de qué lado están.',
            'description' => 'Atiende una sala de seis camas encima de una cordelería.',
            'gm_notes' => 'La semana pasada curó a dos Perjuros. Solo lo contará a cambio de sal y vendas.',
            'fields' => [
                'trade' => 'Sanadora',
                'trait' => 'Llama a todo el mundo «pequeño»',
            ],
        ],
        'mornevent' => [
            'name' => 'Capitán Hald Mornevent',
            'summary' => 'Está al mando de la Guardia de los Muelles: veintidós hombres y una barca.',
            'description' => 'Competente, cansado y plenamente consciente de que no tiene medios para su cargo.',
            'gm_notes' => 'Encubre la desaparición de tres viajeros para no alarmar a la ciudad. Aceptará ayuda si se la ofrecen sin público delante.',
            'fields' => [
                'trade' => 'Capitán de la Guardia',
                'trait' => 'Lo apunta todo en un cuaderno que nunca relee',
                'hidden_oath' => 'Prometió al consejo que nadie desaparecería bajo su mando.',
                'betrayal' => 'Guardar las apariencias ante el consejo',
            ],
        ],
        'stranger' => [
            'name' => 'El Desconocido del Faro',
            'summary' => 'Quien vuelve a encender la linterna del faro de Orvent. Nadie lo ha visto de cerca.',
            'gm_notes' => 'Es Gueffroy, el chaval de las linternas, ahogado hace seis meses y devuelto por los Ahogados. Custodia las tablillas y solo espera una cosa: que alguien lea su nombre en voz alta.',
            'fields' => [
                'trade' => 'Farolero',
                'trait' => 'Huele a sal fría',
                'hidden_oath' => 'Juró, al morir, encender las linternas hasta que le devuelvan su nombre.',
            ],
        ],
        'drowned' => [
            'name' => 'Los Ahogados',
            'summary' => 'Lo que emerge de las Marcas cuando la bruma dura más de tres días.',
            'description' => 'Se los describe como siluetas que caminan bajo el agua poco profunda, a la altura de un hombre.',
            'gm_notes' => 'No matan: reclaman. Un Ahogado suelta a su presa si alguien cumple en su lugar el juramento que ha venido a buscar.',
        ],
        'seal' => [
            'name' => 'El Sello de Ceniza',
            'summary' => 'El punzón que graba las tablillas de la Lonja. Sin él, ningún juramento es válido.',
            'description' => 'Un cilindro de vidrio negro, pesado, con las armas de la ciudad grabadas en hueco.',
            'gm_notes' => 'Ysane lo ha escondido. Hacerlo público cierra la campaña con una negociación; destruirlo la cierra con una ruptura.',
        ],
        'greythread' => [
            'name' => 'La Compañía del Hilo Gris',
            'summary' => 'Casa de comercio que compra deudas y revende servicios.',
            'description' => 'Tres factorías, ni un solo barco propio y un libro de deudas más grueso que el registro de la ciudad.',
            'gm_notes' => 'Quiere el Sello de Ceniza: quien graba los juramentos fija el precio de las deudas.',
        ],
        'broken' => [
            'name' => 'Los Perjuros',
            'summary' => 'Quienes rompieron un juramento y ahora viven fuera de la ciudad, en las Marcas.',
            'gm_notes' => 'Mueven las pértigas para que la ciudad aprenda por fin a temer al agua. Su cabecilla es la hija de Ysane.',
        ],
        'guard' => [
            'name' => 'La Guardia de los Muelles',
            'summary' => 'Veintidós hombres a cargo del puerto, las linternas y el toque de queda.',
            'gm_notes' => 'Dos de ellos están a sueldo del Hilo Gris. Mornevent no lo sabe.',
        ],
        'teska' => [
            'name' => 'Teska la Remera',
            'summary' => 'Rema desde niña y conoce la bahía mejor que la Guardia.',
            'description' => 'Le juraste a tu hermano que nunca te irías de Pierrecendre. Él se marchó el mes pasado.',
            'fields' => [
                'trade' => 'Remera',
                'trait' => 'Lo dice todo, y en el acto',
                'ties' => 'Su hermano, que se fue sin decir palabra. Brannoc, que le debe una barca.',
            ],
        ],
        'oriel' => [
            'name' => 'Oriel Chantegrèle',
            'summary' => 'Testigo de oficio: le pagan por presenciar juramentos y recordarlos.',
            'description' => 'Has sido testigo de doscientos juramentos. Solo has olvidado uno, a propósito.',
            'fields' => [
                'trade' => 'Testigo',
                'trait' => 'Repite en voz baja las frases importantes',
                'ties' => 'Maese Elzevir, su maestro de aprendizaje. El Hilo Gris, que lo contrata demasiado a menudo.',
            ],
        ],
        'dorn' => [
            'name' => 'Dorn Hierrofrío',
            'summary' => 'Antiguo guardia, despedido por negarse a hacer cumplir un toque de queda.',
            'description' => 'Juraste no volver a obedecer jamás una orden que no entiendas.',
            'fields' => [
                'trade' => 'Guardia despedido',
                'trait' => 'Siempre se coloca entre la puerta y los demás',
                'ties' => 'Mornevent, que lo despidió a su pesar. Sor Vanne, que lo ha cosido dos veces.',
            ],
        ],
        'lisenn' => [
            'name' => 'Lisenn de los Dos Nombres',
            'summary' => 'Viene de las Marcas y vive en la ciudad con un nombre que no es el suyo.',
            'description' => 'Rompiste un juramento. Aquí nadie lo sabe todavía.',
            'fields' => [
                'trade' => 'Guía de las Marcas',
                'trait' => 'Nunca duerme dos noches en el mismo sitio',
                'ties' => 'Los Perjuros, a quienes abandonó. Lisenn, la muerta cuyo nombre lleva.',
            ],
        ],
    ],

    // Etiqueta de la relación y luego su etiqueta inversa (null: sin inversa).
    'relations' => [
        'ysane_hall' => ['custodia', 'custodiada por'],
        'elzevir_hall' => ['trabaja en', 'emplea a'],
        'elzevir_ysane' => ['juró silencio a', 'lo tiene atado por un juramento'],
        'brannoc_marshes' => ['conoce el vado de', 'atravesadas por'],
        'brannoc_greythread' => ['tiene una deuda con', 'posee la deuda de'],
        'mornevent_guard' => ['está al mando de', 'a las órdenes de'],
        'guard_quay' => ['vigila', 'vigilado por'],
        'greythread_guard' => ['ha comprado a dos hombres de', null],
        'greythread_seal' => ['codicia', 'codiciado por'],
        'broken_marshes' => ['viven en', 'dan cobijo a'],
        'broken_ysane' => ['están liderados por su hija', null],
        'drowned_marshes' => ['emergen de', null],
        'drowned_ysane' => ['tienen un juramento con', null],
        'stranger_lighthouse' => ['vuelve a encender', 'reencendido por'],
        'stranger_quay' => ['encendía las linternas de', null],
        'vanne_broken' => ['ha curado a dos de ellos', null],
        'hall_city' => ['se alza en', 'alberga'],
        'quay_city' => ['bordea', 'se abre a'],
        'seal_hall' => ['graba las tablillas de', null],
        'teska_brannoc' => ['le prestó una barca', 'le debe una barca'],
        'oriel_elzevir' => ['fue su aprendiz', 'formó a'],
        'dorn_mornevent' => ['sirvió a las órdenes de', 'despidió a'],
        'lisenn_broken' => ['los abandonó', null],
    ],

    'documents' => [
        'tile' => [
            'title' => 'Tablilla 1147 — juramento de la gran bruma',
            'description' => 'La transcripción que Elzevir copió antes de que las tablillas salieran de la Lonja.',
            'lines' => [
                'Transcripción de la tablilla 1147, Lonja de los Juramentos de Pierrecendre.',
                '',
                'Jurante: Ysane Korr, guardiana.',
                'Juramento: «Una barca al año, y la bahía seguirá en calma».',
                'Testigos: Elzevir, archivero. Gueffroy, farolero.',
                '',
                'Nota del archivero: tablilla retirada del estante el día 3 del mes de la sal.',
            ],
        ],
        'notice' => [
            'title' => 'Bando de toque de queda',
            'description' => 'Clavado en el Muelle de las Linternas. Muéstralo a los jugadores desde la primera escena.',
            'lines' => [
                'Por orden del capitán Hald Mornevent, Guardia de los Muelles.',
                '',
                'El Muelle de las Linternas queda cerrado desde la última linterna hasta el alba.',
                'Nadie se hará a la mar sin un pase de la Guardia.',
                'Toda linterna apagada deberá notificarse en el cuartel.',
                '',
                'Este bando vale como juramento: quien lo infrinja responderá ante la Lonja.',
            ],
        ],
        'tides' => [
            'title' => 'Tabla de mareas de las Marcas Anegadas',
            'description' => 'Ayuda de juego: tres horas de vado con la marea baja.',
            'lines' => [
                'Marcas Anegadas — paso del vado',
                '',
                'Marea baja: tres horas de camino seguro, pértigas visibles.',
                'Marea creciente: una hora de margen, agua por medio muslo.',
                'Marea alta: no hay paso. Doce horas de espera.',
                '',
                'La Guardia vuelve a clavar las pértigas cada mes.',
            ],
        ],
        'plan' => [
            'title' => 'Plano del puerto de Pierrecendre',
            'description' => 'El puerto, sus muelles y la punta del faro. Se puede mostrar en la mesa.',
            'file' => 'plano-del-puerto',
        ],
    ],

    'map' => [
        'name' => 'El puerto de Pierrecendre',
        'unit' => 'm',
    ],

    'rules' => [
        'roll' => [
            'title' => 'Tirada de juramento',
            'category' => 'Básico',
            'summary' => 'Característica + 1d6 contra una dificultad de 4 a 9.',
            'procedure' => "1. Anuncia la característica que usas y lo que el personaje quiere conseguir.\n2. Tira 1d6 y suma la característica.\n3. 4 para una tarea propia del oficio, 7 para una tarea difícil, 9 para lo imposible.\n4. Si el personaje actúa para cumplir un juramento, suma +1 por juramento cumplido, hasta +3.",
            'source' => 'Libreto básico, p. 12',
        ],
        'breath' => [
            'title' => 'Aliento',
            'category' => 'Básico',
            'summary' => 'El Aliento sustituye a los puntos de vida: se gasta para aguantar, no para encajar golpes.',
            'procedure' => "Gasta 1 de Aliento para repetir una tirada, para seguir adelante pese a una herida o para rechazar a un Ahogado.\nA 0, el personaje se detiene: no está muerto, pero no puede prometer nada hasta el siguiente descanso.",
            'source' => 'Libreto básico, p. 18',
        ],
        'breaking' => [
            'title' => 'Romper un juramento',
            'category' => 'Juramentos',
            'summary' => 'Romper un juramento otorga una ventaja inmediata y un precio duradero.',
            'procedure' => "El jugador describe lo que la ruptura le permite: lo consigue, sin tirada.\nDespués pierde todos sus juramentos cumplidos, y la mesa anota quién se ha enterado.",
            'gm_notes' => 'Nunca niegues una ruptura. El precio se paga en la ficción, con la reacción de quienes se enteran.',
            'source' => 'Libreto básico, p. 24',
        ],
        'mist' => [
            'title' => 'Cuenta de la bruma',
            'category' => 'De la casa',
            'summary' => 'Regla de la casa: la bruma sube un grado en cada sesión, hasta que los Ahogados caminan por la ciudad.',
            'procedure' => "Lleva una cuenta de 0 a 6, visible para la mesa.\n+1 al final de cada sesión, +1 cada vez que se rompe un juramento ante testigos.\nA 3, el vado se vuelve incierto. A 6, los Ahogados entran en Pierrecendre.",
            'gm_notes' => 'Llévala tras la pantalla, en secreto hasta llegar a 3.',
        ],
        'word' => [
            'title' => 'Palabra dada en la mesa',
            'category' => 'De la casa',
            'summary' => 'Por probar: una promesa que el jugador hace en voz alta cuenta como juramento.',
            'procedure' => 'Cuando un jugador le prometa algo a un personaje, anótalo. Si la cumple, +1 juramento cumplido; si no, se aplica la ruptura.',
            'gm_notes' => 'Probar en la sesión 2. Riesgo: que los jugadores ya no se atrevan a prometer nada.',
        ],
    ],

    'scenario' => [
        'name' => 'El Juramento de Pierrecendre',
        'summary' => 'Tres sesiones: una linterna apagada, un vado que miente y un faro que reclama un nombre.',
    ],

    'chapters' => [
        's1' => 'Sesión 1 — La linterna apagada',
        's2' => 'Sesión 2 — El vado que miente',
        's3' => 'Sesión 3 — El nombre devuelto',
    ],

    // «notes»: la nota de cada ficha en la escena, por clave de ficha (si falta: sin nota).
    'scenes' => [
        'lantern' => [
            'name' => 'La tercera linterna',
            'description' => 'Al anochecer, en [[quay]], la tercera linterna empezando por el norte se niega a encenderse. El bando de toque de queda aún está fresco en la pared.',
            'gm_notes' => 'Es la señal de [[brannoc]]. Deja que los jugadores lo descubran observando quién se acerca al muelle.',
            'notes' => ['brannoc' => 'llega por el agua, sin hacer ruido', 'guard' => 'dos hombres de ronda'],
        ],
        'register' => [
            'name' => 'El registro vedado',
            'description' => 'En [[hall]], [[ysane]] niega el acceso al estante del año de la gran bruma. [[elzevir]] tose.',
            'gm_notes' => 'Elzevir cederá si lo llevan aparte, lejos de la vista de Ysane. Si no, tose y cambia de tema.',
            'notes' => ['ysane' => 'tras el atril', 'elzevir' => 'entre las estanterías'],
        ],
        'poles' => [
            'name' => 'Las pértigas movidas',
            'description' => 'En [[marshes]], con la marea baja, faltan dos pértigas y una tercera ha sido clavada torcida. La bruma dura ya cuatro días.',
            'gm_notes' => 'Una tirada de Mente a 7 descubre el engaño. Si falla, la marea sube sobre un jugador: ocasión de gastar Aliento.',
            'notes' => ['brannoc' => 'lo sabe, y calla', 'broken' => 'los observan de lejos'],
        ],
        'notebook' => [
            'name' => 'El cuaderno de Mornevent',
            'description' => '[[mornevent]] recibe a regañadientes en el cuartel de [[guard]]. En su cuaderno hay tres nombres tachados.',
            'gm_notes' => 'Habla si no hay testigos. Los tres nombres son los de los viajeros desaparecidos en el vado.',
            'notes' => ['marshes' => 'mencionadas, no visitadas'],
        ],
        'ward' => [
            'name' => 'La sala de las seis camas',
            'description' => 'En casa de [[vanne]], dos heridos recientes huelen a sal. Ella cambia lo que sabe por sal y vendas.',
            'notes' => ['broken' => 'dos de ellos, curados la semana pasada'],
        ],
        'cellar' => [
            'name' => 'El sótano del faro',
            'description' => 'En [[lighthouse]], el sótano se inunda con la marea alta. Dentro de un cajón de sal: las tablillas retiradas de la Lonja.',
            'gm_notes' => 'La tablilla 1147 está encima del montón, bien a la vista. [[stranger]] espera a que alguien la lea en voz alta.',
            'notes' => ['stranger' => 'en lo alto de la escalera', 'seal' => 'no está en el cajón'],
        ],
        'rising' => [
            'name' => 'Lo que emerge',
            'description' => '[[drowned]] caminan por la bahía, a la altura de un hombre, directos hacia [[city]]. La cuenta de la bruma está en 6.',
            'gm_notes' => 'Se detienen si alguien cumple, en lugar de Ysane, el juramento de la tablilla 1147, o si se pronuncia el nombre de Gueffroy ante testigos.',
            'notes' => ['ysane' => 'en el muelle, sin su sello'],
        ],
        'recast' => [
            'name' => 'El juramento refundido',
            'description' => 'En [[hall]], ante toda la ciudad: devolver [[seal]] a la Lonja, o romperlo.',
            'gm_notes' => 'Dos finales, ninguno bueno. Devolverlo: la ciudad resiste, Ysane cae. Romperlo: ya ningún juramento ata a nadie, y el Hilo Gris lo compra todo.',
            'notes' => ['greythread' => 'presente, espera su turno'],
        ],
    ],

    'secrets' => [
        'ysane_oath' => [
            'title' => 'Ysane juró a los Ahogados una barca al año',
            'body' => 'Hace treinta años, Ysane Korr prometió a los Ahogados una barca al año para que la bahía siguiera en calma. La tablilla 1147 lleva el texto, y su nombre.',
        ],
        'stranger' => [
            'title' => 'El Desconocido del Faro es Gueffroy, el farolero ahogado',
            'body' => 'Gueffroy era el chaval al que pagaban por encender las linternas del muelle. Ahogado hace seis meses, los Ahogados lo devolvieron. Enciende el faro y espera a que alguien diga su nombre.',
        ],
        'poles' => [
            'title' => 'Las pértigas del vado se mueven a propósito',
            'body' => 'Los Perjuros mueven las pértigas para que Pierrecendre tema a su propia agua. Tres viajeros ya han desaparecido allí.',
        ],
        'daughter' => [
            'title' => 'La cabecilla de los Perjuros es la hija de Ysane',
            'body' => 'Quien lidera a los Perjuros es la hija de la guardiana. Por ella escondió Ysane las tablillas, y por ella traicionaría a la ciudad.',
        ],
        'bought' => [
            'title' => 'El Hilo Gris ha comprado a dos guardias del muelle',
            'body' => 'Dos hombres de la Guardia de los Muelles están a sueldo de la Compañía del Hilo Gris. Mornevent no lo sabe, y descubrirlo lo hundirá.',
        ],
    ],

    // Fecha libre, título, descripción.
    'timeline' => [
        'ash' => ['Hace 300 años', 'La ceniza cubre la bahía', 'La erupción del monte Orvent extingue el volcán y da a la ciudad su suelo gris.'],
        'first_oath' => ['Hace 180 años', 'Primer juramento grabado', 'Se construye la Lonja y el primer juramento queda vitrificado en una tablilla de ceniza.'],
        'great_mist' => ['Hace 30 años', 'El año de la gran bruma', 'Una bruma de ocho meses, once barcas perdidas y, después, ni un solo naufragio en treinta años.'],
        'tile_1147' => ['Hace 30 años', 'El juramento de la tablilla 1147', 'Ysane Korr promete a los Ahogados una barca al año. Dos testigos: Elzevir y Gueffroy.'],
        'drowning' => ['Hace seis meses', 'Gueffroy se ahoga en el muelle', 'El farolero cae desde el Muelle de las Linternas. Su cuerpo nunca aparece.'],
        'missing' => ['El mes pasado', 'Tres viajeros desaparecen en el vado', 'Mornevent tacha tres nombres en su cuaderno y no avisa al consejo.'],
        'session1' => ['Sesión 1', 'La tercera linterna sigue apagada', 'Los personajes descubren la señal del Barquero y se les niega el estante de la gran bruma.'],
        'poles_moved' => ['Sesión 2', 'Las pértigas se mueven', 'Si nadie interviene, un cuarto viajero desaparece en las Marcas.'],
        'invasion' => ['Sesión 3', 'Los Ahogados entran en la ciudad', 'Con la cuenta de la bruma en 6, remontan la bahía y caminan hasta la Lonja.'],
        'ending' => ['Final', 'El sello devuelto o roto', 'Devolver el Sello hace caer a Ysane; romperlo libera a la ciudad de todo juramento, y al Hilo Gris de todo límite.'],
    ],
];
