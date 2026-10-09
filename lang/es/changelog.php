<?php

// Novedades por versión, de la más reciente a la más antigua. Se muestran en
// «Novedades» (una vez tras cada actualización) y en la página del mismo nombre.
return [

    '0.36.0' => [
        'date' => '2026-10-09',
        'title' => 'Comodidad del DJ y de la sesión',
        'items' => [
            'Las páginas de un PDF mostrado en la mesa se pasan desde el mando, desde la página del documento o con las flechas de la pantalla del DJ, y los jugadores que siguen la pantalla pasan con ella. El visor PDF indica «página n / N» y permite ir a una página.',
            'En el modo Sesión, «Evento jugado» añade el texto escrito a la cronología, vinculado a la sesión y la escena en curso.',
            'Un personaje puede devolverse a cualquiera de sus antiguos jugadores que vuelva a la campaña, que recupera sus propios intercambios privados con el DJ, sin los de los jugadores intermedios.',
            'El estado de una ficha aparece bajo su título; los pregenerados (etiqueta «pregenerado») aparecen primero en «Nuevo personaje»; los contadores de la página Etiquetas listan los elementos; una demo cargada varias veces numera su campaña, juego y mundo; el rol espectador indica que ve la pantalla de mesa aunque no esté compartida.',
        ],
    ],

    '0.35.0' => [
        'date' => '2026-10-09',
        'title' => 'Correcciones de la prueba de aceptación final',
        'items' => [
            'La página de una sesión tiene un resumen escrito por el DJ y muestra los acontecimientos jugados y todo lo revelado o dado durante la sesión.',
            'El historial de una ficha guarda también sus relaciones, archivos adjuntos, etiquetas y su estado en la campaña. La página de un mundo muestra su historia, y la de un juego sus tipos de ficha.',
            'Una función desactivada por el DJ o por el plan también lo está en las páginas que quedaron abiertas, y el modo Sesión se abre cuando los mapas están desactivados.',
            'Correcciones: cantidad anunciada en un intercambio, casilla sí/no nunca rellenada, página «no encontrada» traducida, páginas legibles en el móvil con texto grande, conexiones contadas una vez, ayuda actualizada.',
        ],
    ],

    '0.34.0' => [
        'date' => '2026-10-08',
        'title' => 'Funciones por campaña',
        'items' => [
            'En la página de la campaña, el DJ marca las funciones que necesita su mesa: pantalla de mesa, mapas, intercambios entre jugadores, grafo, cronología, asistente de IA. Una función desmarcada desaparece para todos sin borrar nada; vuelve en cuanto se marca de nuevo.',
        ],
    ],

    '0.33.0' => [
        'date' => '2026-10-08',
        'title' => 'Retoques',
        'items' => [
            'La búsqueda muestra el campo de la ficha que contiene la palabra encontrada, con su nombre.',
            'Grafo: los nombres y etiquetas que se solapan se desplazan u ocultan; al pasar sobre una ficha vuelven a aparecer.',
            '«Revelar o dar»: una casilla «Todos los personajes activos» marca toda la mesa de una vez.',
            'Un jugador retirado y vuelto a invitar recupera su antiguo personaje con un clic, desde la página Personajes.',
            'Sin tiempo real (servidor Reverb ausente o caído), la campana, los mensajes y las fichas se actualizan cada 30 segundos.',
        ],
    ],

    '0.32.0' => [
        'date' => '2026-10-08',
        'title' => 'PDF y documentos',
        'items' => [
            'Los PDF se abren en un visor integrado, igual en ordenador, tableta y móvil, con zoom y descarga.',
            'En la pantalla de mesa, un PDF se muestra página a página, ajustado a la pantalla; las flechas pasan las páginas.',
            'La hoja de personaje conserva su nombre de archivo original.',
            '«Usado por» indica el escenario de cada escena.',
            'Las páginas de un juego y de un mundo pueden tener una imagen.',
        ],
    ],

    '0.31.0' => [
        'date' => '2026-10-08',
        'title' => 'Funciones Premium ✦',
        'items' => [
            'Una pequeña estrella ✦ señala las funciones Premium. Cuando el plan del propietario de una campaña no las incluye, siguen visibles, en gris, con una explicación.',
            'Al terminar una prueba o una suscripción no se borra nada: campañas, mapas, mensajes y archivos se quedan; solo se apagan las funciones ✦.',
            'El administrador puede regalar un periodo (Navidad…) en el que las cuentas gratuitas tienen todas las funciones Premium.',
        ],
    ],

    '0.30.0' => [
        'date' => '2026-10-08',
        'title' => 'Escritura y enlaces',
        'items' => [
            'Los campos «texto largo» y las notas del DJ de las reglas tienen el editor enriquecido y los enlaces [[ ]].',
            'En su ficha, el jugador ve los textos largos con formato y enlaces a las fichas que conoce su personaje.',
            'La nota rápida de sesión propone fichas en cuanto escribes «[[».',
            'Las copias se numeran («copia 2», «copia 3») y una importación nunca reutiliza el nombre de un juego, mundo o campaña que ya tengas.',
        ],
    ],

    '0.29.0' => [
        'date' => '2026-10-08',
        'title' => 'Seguridad de la cuenta',
        'items' => [
            'Una nueva dirección de correo solo se adopta tras pulsar el enlace que recibe; después se avisa a la dirección anterior.',
            'Los intentos en los formularios de cuenta se cuentan por formulario y por dirección de correo, y la página de espera indica cuántos segundos esperar.',
            'Solo los scripts de LoreMundi pueden ejecutarse en sus páginas.',
            'La eliminación de la cuenta indica que tus mensajes se borran.',
        ],
    ],

    '0.28.0' => [
        'date' => '2026-10-08',
        'title' => 'Correos con los colores de LoreMundi',
        'items' => [
            'Los correos (contraseña olvidada, cambio de dirección) llevan el logotipo y los colores de LoreMundi.',
            'Cada correo se envía en el idioma del destinatario, aunque lo envíe el administrador.',
        ],
    ],

    '0.27.0' => [
        'date' => '2026-10-08',
        'title' => 'Un escaparate público',
        'items' => [
            'Una página de inicio presenta LoreMundi a los visitantes y a los buscadores, en los 8 idiomas.',
            'La ayuda se lee sin cuenta y gana un apartado «Tu cuenta»: planes, correo, contraseña, autenticación en dos pasos, datos.',
        ],
    ],

    '0.26.0' => [
        'date' => '2026-10-08',
        'title' => 'Correcciones de la prueba de aceptación v0.25.0',
        'items' => [
            'Una página que sigue abierta vuelve a comprobar sus permisos en cada acción: un jugador retirado o un co-DJ degradado ya no recibe nada nuevo.',
            'El jugador al que se confía un personaje ya no lee la conversación privada del jugador anterior con el DJ.',
            'Los personajes pregenerados de la campaña de demostración aparecen en «Nuevo personaje».',
            'Autenticación en dos pasos: los códigos de recuperación funcionan al iniciar sesión, y el administrador puede retirarla de una cuenta bloqueada.',
            'Nuevos iconos de LoreMundi (pestaña, aplicación instalada, notificaciones).',
            'Pantalla de mesa compartida legible en el móvil; cabeceras de fichas y páginas corregidas en móvil y tableta, también con texto grande.',
            'Correcciones: notificaciones push sin errores, botones de la ventana «recibido», referencias a fichas tras un cambio de nombre, cantidad de un intercambio, regla de medir de los mapas, aviso «Sin conexión», mensajes de error traducidos, enlace «Ir al contenido».',
        ],
    ],

    '0.25.0' => [
        'date' => '2026-10-08',
        'title' => 'Importar un libro de juego con una IA',
        'items' => [
            'En «Importar», «Prepara los archivos con una IA» te da un prompt para pegar en la IA que elijas junto con el PDF de un juego o escenario: prepara los archivos de importación (campos, fichas, reglas, escenas) y una guía paso a paso. El prompt también existe como skill de Claude.',
        ],
    ],

    '0.24.0' => [
        'date' => '2026-10-08',
        'title' => 'Seguridad y datos personales',
        'items' => [
            '«Mi cuenta», en Preferencias: cambia tu nombre, tu correo (se avisa a la dirección anterior) y tu contraseña.',
            'Verificación en dos pasos opcional, en Preferencias: un código de una aplicación de tu teléfono, con códigos de recuperación.',
            '«Mis datos», en Preferencias: descarga lo que LoreMundi guarda sobre ti o elimina tu cuenta.',
            'Contraseñas de al menos 10 caracteres con letras y cifras; cambiar la tuya cierra la sesión en tus otros dispositivos. La consola de administración vuelve a pedir la contraseña.',
            'Nueva página «Privacidad y aviso legal».',
            'Un jugador retirado de la campaña, o convertido en espectador, ya no juega su personaje y ya no recibe nada de lo que se le revela. Se han reforzado otras comprobaciones de permisos en el servidor.',
        ],
    ],

    '0.23.0' => [
        'date' => '2026-10-08',
        'title' => 'CodexFlow pasa a llamarse LoreMundi',
        'items' => [
            'CodexFlow ahora se llama LoreMundi, publicado por Autistic Intelligence. Every world has a story.',
            'Tus campañas, cuentas y archivos no cambian: las copias hechas con CodexFlow se siguen importando. Los archivos descargados empiezan ahora por «loremundi-».',
        ],
    ],

    '0.22.0' => [
        'date' => '2026-10-08',
        'title' => 'Copia de seguridad completa',
        'items' => [
            'El propietario puede descargar una copia de seguridad completa: el archivo de la campaña con la mesa (personajes, lo que recibieron, sesiones, notas, mensajes y registro), sin notas «Solo yo» ni direcciones de correo. Al importar, los personajes vuelven sin jugador, listos para asignarse.',
        ],
    ],

    '0.21.0' => [
        'date' => '2026-10-08',
        'title' => 'Búsqueda desde el inicio',
        'items' => [
            'Fuera de una campaña, la barra de búsqueda busca en todas tus campañas, sus mundos y sus juegos, cada una con tus permisos: todo como DJ, lo que conoce tu personaje como jugador.',
        ],
    ],

    '0.20.0' => [
        'date' => '2026-10-08',
        'title' => '«Citado en» completo, duplicación sin estados',
        'items' => [
            '«Citado en» muestra también la cronología, los secretos y los campos de otras fichas que mencionan la ficha.',
            'Una campaña duplicada parte de las fichas originales: el estado «muerto» o «prisionero» ya no se copia, salvo que marques la casilla para conservarlo.',
        ],
    ],

    '0.19.0' => [
        'date' => '2026-10-08',
        'title' => 'Secretos y nuevos campos',
        'items' => [
            'Cada secreto tiene un tipo (rumor, pista o verdad) y un estado que depende de quién lo conoce: oculto, parcial o revelado. Puedes filtrar los secretos por ambos.',
            'Tres nuevos tipos de campo: enlace web, archivo (un documento de la campaña) y referencia a otra ficha, que sigue enlazada aunque la ficha cambie de nombre.',
            'Una campaña duplicada conserva los enlaces entre sus fichas copiadas.',
        ],
    ],

    '0.18.0' => [
        'date' => '2026-10-08',
        'title' => 'Nuevo personaje, Mis campañas, páginas de juego y de mundo',
        'items' => [
            'Cuando un jugador recibe un nuevo personaje, el DJ marca lo que pasa del anterior: conocimientos, informaciones, documentos y reglas se copian, los objetos cambian de manos.',
            'Mis campañas: botón «Retomar», fecha de la última sesión y las campañas archivadas aparte.',
            'Cada juego y cada mundo tiene su página: descripción, campañas, reglas, documentos, campos o fichas reutilizables.',
        ],
    ],

    '0.17.0' => [
        'date' => '2026-10-08',
        'title' => 'Editor enriquecido y notas de los jugadores',
        'items' => [
            'Los textos largos (descripciones, notas del DJ, escenas, reglas, cronología, notas de los jugadores) tienen un editor con negrita, cursiva, subtítulos, listas y citas; «[[» sigue proponiendo fichas para enlazar.',
            'Los jugadores enlazan sus notas a las fichas que conoce su personaje, y solo a esas.',
            'La página de una sesión muestra también las notas que tomaron los jugadores durante ella, salvo las que se guardan para sí.',
        ],
    ],

    '0.16.0' => [
        'date' => '2026-10-08',
        'title' => 'Suscripción Premium y prueba gratuita',
        'items' => [
            'Pasa a Premium desde «Preferencias»: pago mensual o anual protegido por Stripe, con facturas y cancelación en el portal de Stripe. El Premium dura hasta el final del periodo pagado.',
            'Prueba gratuita: seis semanas con todas las funciones, a partir de tu primera campaña como DJ. Un jugador que nunca es DJ no la empieza. La duración se ajusta en la consola de administración.',
            'Una pestaña «Evoluciones» en la consola de administración para llevar la hoja de ruta de la plataforma.',
        ],
    ],

    '0.15.0' => [
        'date' => '2026-10-08',
        'title' => 'Consola de administración y planes',
        'items' => [
            'Una consola de administración: las cuentas con su plan, las fechas de suscripción, el almacenamiento usado, si tienen clave de IA, las campañas y los inicios de sesión, sin datos personales. El administrador fija el plan de cada cuenta y puede enviar un enlace para restablecer la contraseña, sin verla nunca.',
            'Tres planes: administrador, premium y gratuito. El almacenamiento, el número de campañas y las funciones del plan gratuito se ajustan en la consola; jugar, ser co-DJ o espectador nunca cuenta.',
            'El backlog reúne los problemas notificados, los errores y las mejoras, con estado, prioridad y versión de corrección; ahí se guardan también las pruebas de aceptación versión tras versión. Tu plan aparece en «Preferencias».',
        ],
    ],

    '0.14.0' => [
        'date' => '2026-10-08',
        'title' => 'Tras la prueba de aceptación: intercambios validados por el DJ',
        'items' => [
            'Por defecto, el DJ valida los intercambios entre jugadores: el objeto o el conocimiento solo cambia de manos cuando se acepta. Una casilla en «Personajes de los jugadores» permite autorizarlos directamente.',
            'Mando a distancia: en el último elemento de la escena, «Siguiente» pasa a «Terminar» y vacía la pantalla.',
            'Diario más legible para los objetos validados, mensajes más claros en «Reportar un problema» y una sola forma de tratarte en cada idioma.',
        ],
    ],

    '0.13.1' => [
        'date' => '2026-10-08',
        'title' => 'Correcciones de la prueba de aceptación V1',
        'items' => [
            'La búsqueda del DJ también encuentra secretos, información y objetos entregados, notas compartidas de los jugadores y etiquetas de escena.',
            'Duplicar una campaña copia sus secretos y su cronología preparada; un enlace a una ficha abierto en sesión se muestra en un panel lateral, sin salir de la sesión.',
            'Páginas de error traducidas, correo de contraseña en tu idioma, modo Sesión y Documentos legibles en el móvil, un menú para los enlaces ocultos en pantallas pequeñas.',
            'Un personaje en reposo y un objeto validado por el DJ ya no pueden ser modificados por el jugador; la regla temporal del mapa se borra sola.',
        ],
    ],

    '0.13.0' => [
        'date' => '2026-10-08',
        'title' => 'Tu propia IA, sin copiar y pegar',
        'items' => [
            'En «Preferencias» puedes guardar una clave de API a tu nombre de Claude (Anthropic), ChatGPT (OpenAI) o Le Chat (Mistral). El asistente IA ofrece entonces «Analizar directamente»: las propuestas llegan sin copiar y pegar.',
            'El proveedor factura las llamadas a tu cuenta. La clave se cifra, nunca se vuelve a mostrar ni se exporta, y el modo «texto para pegar» sigue siendo gratuito.',
        ],
    ],

    '0.12.0' => [
        'date' => '2026-10-08',
        'title' => 'Un asistente IA, sin suscripción',
        'items' => [
            'Nueva herramienta «Asistente IA» en la campaña: LoreMundi prepara un texto con las notas de la sesión y el contexto de la campaña, para pegarlo en la IA que prefieras. Su respuesta, pegada de vuelta, se convierte en propuestas: resumen, eventos jugados, relaciones, estados, notas de campaña, revelaciones.',
            'Cada propuesta se acepta, se modifica o se rechaza. Nada cambia en la campaña sin ti, y las relaciones o estados propuestos son propios de la campaña, sin tocar el mundo compartido.',
        ],
    ],

    '0.11.1' => [
        'date' => '2026-10-08',
        'title' => 'La demostración en tu idioma',
        'items' => [
            'La campaña de demostración existe en los ocho idiomas de la interfaz. Se carga en el tuyo, o en el que elijas junto al botón.',
        ],
    ],

    '0.11.0' => [
        'date' => '2026-10-08',
        'title' => 'Una campaña de demostración',
        'items' => [
            'Campaña de demostración que se carga con un clic desde «Mis campañas»: un juego inventado, «Brume & Serment», y una trama completa de tres sesiones, con fichas, retratos, relaciones, mapa, secretos, reglas, cronología y pregenerados.',
            'Los botoncitos con icono de la página de campaña ya no se mueven al pasar el ratón: el nombre aparece en una etiqueta flotante, por encima del resto.',
            'Las etiquetas se escriben igual en todas partes, y una ficha ofrece «Añadir una etiqueta» justo bajo su título.',
        ],
    ],

    '0.10.0' => [
        'date' => '2026-10-07',
        'title' => 'Una página de campaña más clara',
        'items' => [
            'La página de campaña ordenada: el modo Sesión como banda, cuatro zonas de preparación y el resto de herramientas como botones pequeños con icono.',
            'Ambiente de la pantalla de mesa, a elegir desde el mando: Noche, Pergamino, Pizarra o Grimorio.',
        ],
    ],

    '0.9.0' => [
        'date' => '2026-10-07',
        'title' => 'Llevarse la campaña',
        'items' => [
            'Exportar una campaña entera en un archivo .zip: juego, mundo, fichas, escenarios, documentos, mapas, secretos, cronología y archivos.',
            'Importar un archivo desde «Mis campañas»: recrea la campaña, para ti o para otro DJ.',
            'Plantillas de juego compartibles: tipos de ficha, campos, etiquetas y reglas, sin contenido de campaña.',
        ],
    ],

    '0.8.0' => [
        'date' => '2026-10-07',
        'title' => 'El grafo y la cronología',
        'items' => [
            'Grafo de relaciones: todas las fichas relacionadas, o la red alrededor de una ficha, con profundidad y filtro por tipo.',
            '«Ver como» en el grafo: la red tal como la conoce un personaje. Los jugadores acceden desde su personaje.',
            'Cronología: historia del mundo, acontecimientos previstos y jugados, con fechas libres como «Día 3».',
            'Un acontecimiento jugado anotado durante la sesión queda vinculado a la sesión y a la escena en curso.',
        ],
    ],

    '0.7.0' => [
        'date' => '2026-10-07',
        'title' => 'Alrededor de la mesa',
        'items' => [
            'Mapas: una imagen en la pantalla de mesa que amplías y mueves, con una cuadrícula opcional y una escala.',
            'Tokens opcionales, vinculados a fichas (nombre y retrato): moverlos, cambiar su tamaño, mostrarlos u ocultarlos a los jugadores.',
            'Regla temporal: traza una línea y la distancia aparece en casillas o en metros.',
            'Mando a distancia: desde tu teléfono, vacía la pantalla, pasa al siguiente elemento de la escena, controla el mapa.',
            'Jugadores: lo que el DJ te revela o te da aparece al instante, sin pasar por las notificaciones.',
        ],
    ],

    '0.6.0' => [
        'date' => '2026-10-07',
        'title' => 'La memoria de la campaña',
        'items' => [
            'Secretos: una información aparte, vinculada a fichas, escenas o documentos, que se revela con un clic a un personaje o a toda la mesa.',
            'Historial de revelaciones: quién supo qué, cuándo, en qué sesión y en qué escena; cada revelación se puede deshacer.',
            '«Ver como»: el DJ ve la campaña exactamente como un personaje, en solo lectura.',
            '«Citado en» muestra también las reglas y las notas de sesión que mencionan una ficha.',
            'Relaciones: la inversa («trabaja para» / «emplea a») se rellena sola.',
        ],
    ],

    '0.5.0' => [
        'date' => '2026-10-07',
        'title' => 'Ordenar mejor, entre varios',
        'items' => [
            'Una página «Etiquetas» para renombrar, colorear, fusionar y eliminar tus etiquetas; las escenas también tienen etiquetas.',
            '«Duplicar» una ficha, un escenario o toda una campaña, para volver a jugar con otra mesa.',
            'Nuevos roles: co-DJ, que prepara y dirige contigo, y espectador, que mira la pantalla de mesa.',
            '«Mostrar en la mesa» desde una ficha, un retrato, una ilustración, un documento o una regla.',
            'Modo Sesión: mostrar una ficha o una regla con un clic, ver la escena siguiente, tecla N para tomar notas.',
        ],
    ],

    '0.4.1' => [
        'date' => '2026-10-07',
        'title' => 'Ayuda e informes de problemas',
        'items' => [
            'Una página «Ayuda» responde a las preguntas más frecuentes, tanto para el DJ como para los jugadores.',
            '«Informar de un problema», al pie de cada página, envía tu mensaje al equipo junto con la página afectada.',
        ],
    ],

    '0.4.0' => [
        'date' => '2026-10-07',
        'title' => 'Todos los idiomas',
        'items' => [
            'La interfaz habla francés, inglés, alemán, español, italiano, portugués, neerlandés y polaco.',
            'El idioma sigue al del navegador; cada uno puede elegirlo en «Preferencias».',
            'Las notificaciones llegan en el idioma de quien las recibe.',
            'El tema oscuro se mantiene al pasar de una página a otra.',
            'Tras editar una ficha desde la página Personajes, vuelves directamente a ella.',
        ],
    ],

    '0.3.0' => [
        'date' => '2026-10-07',
        'title' => 'El vínculo vivo',
        'items' => [
            'Mensajería entre el DJ y sus jugadores, y panel «Conversación» siempre a mano (grupo y privado).',
            'Notificaciones: revelaciones, objetos recibidos, mensajes, con un contador en la cabecera.',
            'Todo se actualiza en directo: mensajes, contadores, revelaciones, sin recargar la página.',
            'LoreMundi se instala como una aplicación; la ficha del personaje se puede leer sin conexión; notificaciones en el dispositivo.',
            'Pantalla de mesa: mapas, imágenes, fichas y anuncios en la tele o el proyector, y compartida con los jugadores si el DJ quiere.',
            'Los personajes se dan objetos entre sí y se transmiten lo que saben.',
            'Los jugadores anotan sus conocimientos y añaden sus objetos; el DJ valida los objetos.',
            'Tema oscuro, color de acento y tamaño del texto en «Preferencias».',
        ],
    ],

    '0.2.0' => [
        'date' => '2026-10-07',
        'title' => 'Los jugadores',
        'items' => [
            'Invitaciones de los jugadores mediante enlace.',
            'Personajes de los jugadores: ficha, hoja PDF, contadores (PV, magia, munición…) y campos modificables por el jugador.',
            'Revelaciones y «Dar»: conocimientos, posesiones, documentos y reglas.',
            'Espacio del jugador: notas privadas o compartidas, intenciones «Por jugar», diario del personaje.',
            'Historial de cambios: quién, qué, cuándo, antes y después.',
        ],
    ],

    '0.1.0' => [
        'date' => '2026-10-06',
        'title' => 'El DJ en solitario',
        'items' => [
            'Mundos, campañas y fichas con zona pública y zona del DJ, enlaces [[ ]] entre fichas.',
            'Campos libres por juego, tipos de ficha, importación CSV/JSON.',
            'Escenarios, escenas, reglas y biblioteca de documentos.',
            'Modo Sesión: escena en curso, fichas útiles, notas rápidas, «Por jugar» y fichas fijadas.',
            'Búsqueda global.',
        ],
    ],

];
