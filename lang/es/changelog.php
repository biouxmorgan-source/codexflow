<?php

// Novedades por versión, de la más reciente a la más antigua. Se muestran en
// «Novedades» (una vez tras cada actualización) y en la página del mismo nombre.
return [

    '0.24.0' => [
        'date' => '2026-10-08',
        'title' => 'Seguridad y datos personales',
        'items' => [
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
