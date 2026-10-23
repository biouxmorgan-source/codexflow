<?php

// Novedades por versión, de la más reciente a la más antigua. Se muestran en
// «Novedades» (una vez tras cada actualización) y en la página del mismo nombre.
return [

    '0.92.0' => [
        'date' => '2026-10-23',
        'title' => 'Importación con IA: prompt en inglés, idioma a elegir',
        'items' => [
            'El prompt y el skill de Claude de «Preparar la importación con una IA» están ahora en inglés, el idioma que mejor siguen las IA.',
            'Un menú «Idioma del contenido importado» elige el idioma en el que la IA escribe las fichas, las escenas, los títulos de los documentos y la guía (el de la interfaz por defecto). Con el skill también se puede pedir otro idioma al importar.',
            'El Libro de aventuras reconoce las secciones «Lo previsto» y «Lo que pasó» escritas en cualquiera de los idiomas de SagaWyn.',
        ],
    ],

    '0.89.1' => [
        'date' => '2026-10-23',
        'title' => 'Opciones más claras al crear una campaña',
        'items' => [
            'En «Nueva campaña», las listas «Juego» y «Mundo» solo proponen los de una campaña en curso o archivada. Los de una campaña eliminada ya no aparecen; su contenido sigue disponible mediante la búsqueda.',
        ],
    ],

    '0.89.0' => [
        'date' => '2026-10-21',
        'title' => 'Declaración de derechos en cada importación',
        'items' => [
            'Antes de cada importación (archivo CSV o JSON, archivo de campaña, plantilla de juego), una casilla desmarcada por defecto pide certificar que dispones de los derechos necesarios sobre los contenidos importados. Sin ella, el botón sigue gris y el servidor rechaza la importación.',
            'Cada declaración se registra con su fecha, la versión del texto aceptado y el resultado de la importación, sin copia de los archivos. Aparece en «Descargar mis datos».',
            'Las condiciones de uso incluyen una sección «Contenidos de los usuarios y propiedad intelectual», accesible desde cada pantalla de importación.',
        ],
    ],

    '0.88.0' => [
        'date' => '2026-10-21',
        'title' => 'Identificar los accesos gratuitos',
        'items' => [
            'Consola de administración, página Cuentas: una casilla «Acceso gratuito» por cuenta, con una nota opcional (amigo, probador…). Los accesos gratuitos aparecen bajo la dirección, tienen su total en la parte superior y su filtro «Mostrar».',
        ],
    ],

    '0.87.1' => [
        'date' => '2026-10-20',
        'title' => 'Nombres de los puntos legibles en el mapa',
        'items' => [
            'En el tema oscuro, los nombres de los puntos del mapa de campaña aparecían oscuros sobre fondo negro: vuelven a ser blancos, igual que los paneles de iniciativa y de dados en la pantalla de mesa y los botones de página de los PDF.',
        ],
    ],

    '0.87.0' => [
        'date' => '2026-10-20',
        'title' => 'Tiradas guardadas para cada hoja',
        'items' => [
            'En los dados de «Mis hojas», «Guardar esta tirada» guarda una tirada con la hoja: cada personaje tiene sus propias tiradas (Bola de fuego, Ataque…), que se recargan con un toque, se actualizan y se quitan con ×.',
        ],
    ],

    '0.86.2' => [
        'date' => '2026-10-20',
        'title' => '«Reemplazar el archivo» funciona',
        'items' => [
            'El botón «Reemplazar» seguía desactivado tras elegir el nuevo archivo: vuelve a poder pulsarse en cuanto el archivo se ha enviado.',
        ],
    ],

    '0.86.1' => [
        'date' => '2026-10-20',
        'title' => 'Reemplazar un mapa por una imagen de otro tamaño',
        'items' => [
            'Cuando el documento sirve de mapa, «Reemplazar el archivo» muestra el tamaño de la imagen actual y acepta una imagen de otro tamaño. Fichas, plantillas y puntos conservan su posición en píxeles: un aviso indica que se desplazarán sobre el dibujo.',
        ],
    ],

    '0.86.0' => [
        'date' => '2026-10-20',
        'title' => 'Modificar un documento sin borrarlo',
        'items' => [
            'En la página de un documento, «Reemplazar el archivo» pone una nueva versión en lugar de la antigua: el documento conserva su título, ajustes y enlaces, y la nueva versión aparece al instante en escenas, fichas, reglas, mapas, pistas y en los personajes que lo recibieron.',
            'Una imagen que sirve de mapa se reemplaza por otra imagen; fichas, cuadrícula y puntos se quedan en su sitio.',
        ],
    ],

    '0.85.0' => [
        'date' => '2026-10-20',
        'title' => 'Tirar los dados desde la hoja',
        'items' => [
            'En la hoja de un personaje, un pequeño dado junto a cada número y cada contador tira los dados con ese valor: «Tirar por debajo de… (umbral)» o «Sumar… a la tirada (bonificador)». La bandeja de dados se abre lista para tirar y el resultado va a la mesa como siempre.',
            'En la hoja de tu propio personaje, el valor sigue vinculado a la hoja: si cambia, la tirada guardada lo sigue. El DJ también puede tirar desde la hoja de un jugador.',
            '«Mis hojas» tiene sus propios dados: el mismo pequeño dado junto a los valores numéricos y un botón «Dados» al pie de la hoja. El resultado se muestra en la hoja, visible solo para ti.',
        ],
    ],

    '0.84.2' => [
        'date' => '2026-10-20',
        'title' => 'Nuevos mapas para la demo',
        'items' => [
            'La demostración tiene nuevos mapas ilustrados: el pueblo costero para el mapa de campaña y el muelle subterráneo del faro para el submapa. Vuelve a cargarla desde «Mis campañas» para verlos.',
        ],
    ],

    '0.84.1' => [
        'date' => '2026-10-19',
        'title' => 'Más espacio en Mis hojas',
        'items' => [
            'Una hoja guarda ahora hasta 200 bloques, 500 campos y 30 pestañas. «+ Nueva nota» ya no lleva a una página de error: en el límite, un mensaje lo explica.',
        ],
    ],

    '0.84.0' => [
        'date' => '2026-10-19',
        'title' => 'Pestañas en Mis hojas',
        'items' => [
            'En «Editar», «+ Nueva pestaña» divide una hoja en pestañas que nombras (Personaje, Hechizos, Inventario…), ordenas y quitas.',
            'Cada bloque elige su pestaña, y cada pestaña coloca sus bloques en sus propias tres columnas. Una hoja existente conserva todos sus bloques en una primera pestaña.',
        ],
    ],

    '0.83.0' => [
        'date' => '2026-10-19',
        'title' => 'Varias notas y tres columnas en Mis hojas',
        'items' => [
            '«+ Nueva nota» añade un bloque de notas: tantos como quieras (deudas, rumores, contactos…). Tus notas anteriores pasan a un primer bloque «Notas».',
            'Los bloques se colocan ahora en tres columnas, notas incluidas, con «Organizar los bloques».',
        ],
    ],

    '0.82.0' => [
        'date' => '2026-10-19',
        'title' => 'Una demo al día',
        'items' => [
            'La campaña de demostración muestra ahora las últimas novedades: en el mapa de campaña, el faro lleva a un submapa, su sótano, listo para el combate con fichas, dos plantillas de área y un orden de iniciativa. Cada punto enlaza varias fichas y varias escenas.',
            'Dados en la mesa: tiradas preparadas que leen la característica de la ficha, un bono con nombre y las tiradas de la sesión 1, incluida una petición del DJ.',
            'Además: un sonido de «Mis sonidos», una escena «Jugada según lo previsto» y otra con «Lo que pasó», una pista «Mentira», una ficha y una regla de la casa con su versión oficial, y jugadores que completan su propio retrato.',
            'Vuelve a cargar la demostración desde «Mis campañas» para descubrirla: una copia ya cargada se queda como está.',
        ],
    ],

    '0.81.0' => [
        'date' => '2026-10-19',
        'title' => 'Mapa de campaña: zoom, mesa y lugares más completos',
        'items' => [
            'El mapa de campaña se amplía (rueda, + y −, pellizcar en tableta) y se desplaza arrastrando. Consejo: una imagen 4K (3840 × 2160) se mantiene nítida.',
            'Cuando el mapa está en la pantalla de mesa, el submapa abierto desde un punto lo sustituye allí, y el mapa superior vuelve al subir. En el submapa, «Este lugar» mantiene a un clic las escenas y fichas del punto.',
            'Un punto puede enlazarse ahora con varias escenas y varias fichas: el lugar, sus PNJ, todas las escenas que se juegan allí.',
        ],
    ],

    '0.80.0' => [
        'date' => '2026-10-19',
        'title' => 'Bloques, notas y casillas en Mis hojas',
        'items' => [
            'En «Mis hojas», los campos se agrupan en bloques que nombras: Características, Habilidades, Equipo, Hechizos…',
            '«Organizar los bloques»: arrastra un bloque por su asa, o usa las flechas, para colocarlo donde quieras, en una o dos columnas.',
            'Cada campo puede llevar una nota y una casilla para marcar durante la partida: una habilidad superada, un hechizo preparado.',
        ],
    ],

    '0.79.0' => [
        'date' => '2026-10-19',
        'title' => 'Biblioteca de demos',
        'items' => [
            'Nueva «Biblioteca de demos», desde «Mis campañas»: campañas listas para jugar (un mundo, un escenario corto, personajes pregenerados) de las que cargas tu propia copia en un clic.',
            'Cada demo de un autor o una editorial conserva un enlace «Comprar el juego», en la biblioteca y en la parte superior de tu copia.',
            'El precio de Premium aparece en el botón de suscripción de Preferencias: 4,99 € al mes.',
        ],
    ],

    '0.78.0' => [
        'date' => '2026-10-18',
        'title' => 'Lo que pasó, y el libro de aventuras',
        'items' => [
            'Cada escena tiene ahora dos partes: «Lo previsto» y «Lo que pasó». Si la mesa siguió el plan, basta con marcar «Jugada según lo previsto».',
            'Nuevo «Libro de aventuras» en la campaña: las escenas jugadas, escenario por escenario, y luego los resúmenes de las sesiones, para leer, imprimir o guardar en PDF.',
            'Puede añadirse una versión redactada, escrita por ti o por la IA que elijas (texto para copiar, o tu propia clave de API, a tu cargo). El libro sigue reservado al DJ.',
        ],
    ],

    '0.77.0' => [
        'date' => '2026-10-18',
        'title' => 'Organiza tus pantallas a tu manera',
        'items' => [
            'Nuevo botón «Organizar la pantalla» en la página de la campaña y en el modo Sesión: arrastra cada bloque por su asa ⠿, con el ratón o con el dedo, o usa las flechas.',
            'En el modo Sesión, un bloque puede pasar de una columna a otra: la música arriba, las notas a la derecha, como prefieras.',
            '«Ocultar» quita un bloque que no usas, «Mostrar» lo recupera, «Disposición por defecto» lo devuelve todo a su sitio. Esta disposición es solo tuya: jugadores y co-DJ conservan la suya.',
        ],
    ],

    '0.76.0' => [
        'date' => '2026-10-18',
        'title' => 'Tu ficha, incluso sin conexión',
        'items' => [
            'Sin conexión, la ficha de tu personaje sigue siendo utilizable: ajusta tus contadores con − y +, modifica los campos que el DJ te permite y escribe notas.',
            'Todo se guarda en el dispositivo y se envía al volver la conexión, campo por campo: un contador recibe lo que quitaste o añadiste, y lo que el DJ cambió mientras tanto (un secreto revelado, un campo modificado) se conserva.',
            'Tras el envío, un resumen indica lo que se actualizó y señala cualquier campo que el DJ modificó al mismo tiempo que tú.',
        ],
    ],

    '0.75.0' => [
        'date' => '2026-10-18',
        'title' => 'El gran mapa de campaña',
        'items' => [
            'Nuevo «Mapa de campaña»: elige un mapa como vista general y coloca en él puntos de interés, una ciudad, una mazmorra, el lugar de una escena.',
            'Un punto puede abrir un submapa con un clic, que a su vez tiene sus puntos; la ruta de navegación permite volver a la vista general.',
            'Enlazados a una escena, los puntos muestran lo jugado, en curso o por venir; enlazados a una ficha, la abren. Todo queda para el DJ, salvo el nombre de los puntos «Visibles en la mesa», mostrado en la pantalla de mesa.',
        ],
    ],

    '0.74.0' => [
        'date' => '2026-10-18',
        'title' => 'Tus personajes jugados en otro sitio',
        'items' => [
            'Nueva página «Mis hojas»: guarda los personajes que juegas fuera de SagaWyn, en casa de un amigo, en un club o en otro sitio. Solo tú los ves.',
            'Cada personaje tiene su hoja PDF, un retrato, campos libres (textos y contadores que se ajustan con − y + durante la partida) y tus notas, con el nombre del juego y de la mesa para encontrarlo.',
        ],
    ],

    '0.73.0' => [
        'date' => '2026-10-18',
        'title' => 'Una biblioteca de sonidos para todas tus campañas',
        'items' => [
            'Nueva página «Mis sonidos»: tus músicas y ambientes, guardados una sola vez en tu cuenta y añadidos después a las campañas que diriges. El archivo no se copia y solo cuenta una vez en tu espacio.',
            'En la página Sonidos de una campaña: «Desde mi biblioteca» para añadir tus sonidos, «Guardar en mi biblioteca» para reutilizar un sonido en otra parte, y una casilla para guardar una nueva subida directamente en la biblioteca.',
            'Guardar en la biblioteca un sonido ya subido en otra campaña fusiona las dos copias: las escenas conservan su música y se libera espacio.',
        ],
    ],

    '0.72.0' => [
        'date' => '2026-10-18',
        'title' => 'El modo de empleo en todas partes',
        'items' => [
            'El botón «?» llega a los sonidos, el grafo de relaciones, la cronología, las opiniones de los jugadores, el asistente IA, el registro y la hoja de personaje.',
            'En el grafo, la cronología y la hoja de personaje, los jugadores leen un modo de empleo escrito para ellos.',
        ],
    ],

    '0.71.1' => [
        'date' => '2026-10-18',
        'title' => 'Restar o sumar varios puntos de una vez',
        'items' => [
            'Bajo cada contador que el jugador puede modificar (PV, munición, dinero…), un campo «Cantidad»: escribe 8 y pulsa −, y el contador pierde 8 de golpe. Por defecto vale 1.',
        ],
    ],

    '0.71.0' => [
        'date' => '2026-10-18',
        'title' => 'Modo de uso en más pantallas',
        'items' => [
            'El botón «?» aparece también en la ficha (lado del DJ), el modo Sesión, los escenarios y escenas, la importación de archivos y los miembros de la campaña.',
            'La página «Ayuda» explica ahora la tirada en bruto y los bonificadores leídos en la ficha del personaje.',
        ],
    ],

    '0.70.0' => [
        'date' => '2026-10-18',
        'title' => 'Un modo de uso en las pantallas delicadas',
        'items' => [
            'Un botón «?» junto al título de las pantallas que no se explican solas abre su modo de uso en pocas líneas: tipos de ficha, campos del juego, etiquetas, dados, mapas, mando de la pantalla de mesa, pistas e historial de revelaciones.',
            'Cada modo de uso enlaza con la pregunta correspondiente de la página «Ayuda», que se abre directamente en ella.',
        ],
    ],

    '0.69.0' => [
        'date' => '2026-10-18',
        'title' => 'Bonificador y umbral leídos en la ficha',
        'items' => [
            'En los dados, el bonificador puede tomar uno o varios valores de la ficha del personaje (característica, habilidad, cualquier campo número o contador de la zona pública), enteros o en decena, mitad o quinto, además del bonificador libre. El umbral también puede leerse en la ficha. Los valores se leen en el momento de la tirada, en la ficha de cada personaje que tira.',
            'Varios bonificadores con nombre (Arma +2, Herida −1…) se suman en la misma tirada, junto a los valores de la ficha.',
            'Los ejemplos predefinidos desaparecen de la bandeja: conserva tus tiradas guardadas.',
            'Una tirada guardada se elimina con un toque: la × de su etiqueta, o «Eliminar» una vez cargada.',
        ],
    ],

    '0.68.0' => [
        'date' => '2026-10-17',
        'title' => 'Tirada en bruto',
        'items' => [
            'Nueva lectura «En bruto», elegida por defecto: eliges el tipo y el número de dados (1d20 + 2d6…), tiras, y cada dado se muestra tal como cae, sin total, sin umbral ni otra regla.',
        ],
    ],

    '0.67.0' => [
        'date' => '2026-10-17',
        'title' => 'Tus contenidos, tus derechos',
        'items' => [
            'La página «Privacidad y aviso legal» precisa que SagaWyn no es propietario de lo que subes: cada usuario sigue siendo propietario de sus contenidos y responsable de lo que publica.',
            'Nuevas secciones: «Importación y estructuración de contenidos», «Servicios de inteligencia artificial», «Denunciar un contenido ilícito», «Disponibilidad y copias de seguridad» y «Juegos y marcas». Las páginas de importación remiten a ellas.',
        ],
    ],

    '0.66.0' => [
        'date' => '2026-10-17',
        'title' => 'Dados mezclados y tiradas propias',
        'items' => [
            'Una misma tirada puede mezclar varios tipos de dados: toca un d20 y luego dos d6, y se lanza 1d20 + 2d6. Cada tipo tiene su número, que puedes ajustar o quitar.',
            'El umbral se aplica al total («Umbral sobre el total», alcanzado o sin superar) o a cada dado («Umbral por dado»: por ejemplo, todos los dados de 4 o más son éxitos).',
            'Cada persona, jugador o DJ, guarda sus propias tiradas en «Mis tiradas», las recarga con un toque y las actualiza cuando su personaje evoluciona. El DJ conserva además las «Tiradas de la campaña» para toda la mesa.',
        ],
    ],

    '0.65.1' => [
        'date' => '2026-10-17',
        'title' => 'Accesos comprobados en todas partes',
        'items' => [
            'Escribir una dirección a mano nunca abre una campaña, una ficha o un archivo al que no se tiene derecho. Una cuenta que no es miembro recibe ahora «Página no encontrada», como para una dirección que no existe: probar números ni siquiera revela que una campaña existe.',
        ],
    ],

    '0.65.0' => [
        'date' => '2026-10-17',
        'title' => 'Los dados en la mesa',
        'items' => [
            'Nuevo botón «Dados» al pie de cada página de la campaña: el número y el tipo de dados (de 4 a 100 caras, u otro), y cómo leerlos: sumar, alcanzar un umbral, quedar bajo un umbral, contar éxitos, quedarse con los mejores o los más bajos, leer en cifras. Con un bonificador y dados que se vuelven a tirar con el máximo (+) o con el 1 (−).',
            'El DJ pide una tirada a toda la mesa o a algunos personajes, a la vista de todos o en discreto. Se abre una ventana a los jugadores afectados; sin umbral, cada uno indica el suyo y añade su bonificador, y un umbral oculto se queda con el DJ, que ve quién ha tirado qué.',
            'Las tiradas a la mesa ruedan unos segundos en la pantalla de mesa; una tirada discreta solo la ve el DJ. El DJ guarda sus tiradas habituales para toda la mesa, y los dados se desactivan en «Funciones de la campaña».',
        ],
    ],

    '0.64.1' => [
        'date' => '2026-10-17',
        'title' => 'Plantillas medidas como la regla',
        'items' => [
            'Las plantillas se ajustan en la misma unidad que la regla (metros, pies…): el «Tamaño real de una casilla», en Cuadrícula, calibra ambas, sea cual sea el tamaño de las casillas en pantalla.',
            'Un botón «Eliminar» más visible en cada plantilla, y «Eliminar todas las plantillas» al terminar el conjuro.',
            'Cada enlace de «Unirse a distancia» tiene su código QR, para que los jugadores lo escaneen con el móvil.',
        ],
    ],

    '0.64.0' => [
        'date' => '2026-10-17',
        'title' => 'Plantillas de área en el mapa',
        'items' => [
            'Nuevas plantillas en la página del mapa: círculo, cuadrado, rectángulo, cono y línea, medidas en casillas (o en la unidad de la escala). Se arrastran por el mapa, se orientan, se ocultan, y la lista de tokens alcanzados aparece al instante.',
            'Las plantillas visibles aparecen en la pantalla de mesa, bajo los tokens.',
        ],
    ],

    '0.63.0' => [
        'date' => '2026-10-17',
        'title' => 'Mapa de combate compartido',
        'items' => [
            'En la pantalla de mesa compartida, cada jugador mueve la ficha de su personaje. En el mapa, «Movido por» confía otra ficha a un jugador: seguidor, familiar, enemigo controlado…',
            'Nuevo orden de iniciativa, desde el mando o el mapa: PJ y enemigos con su valor, turno actual, rondas. El DJ lo muestra sobre la pantalla cuando quiere; las líneas ocultas se quedan con él, y una fila «También presentes» muestra PNJ u objetos sin iniciativa.',
            '«Unirse a distancia» en el mando: el enlace de la pantalla para los jugadores y un enlace de invitado sin cuenta, de solo lectura y revocable, para quien solo quiere mirar.',
        ],
    ],

    '0.62.0' => [
        'date' => '2026-10-13',
        'title' => 'La comunidad en Discord',
        'items' => [
            'SagaWyn tiene su servidor de Discord: un enlace al pie de cada página y en la página de inicio para hacer preguntas, proponer ideas y conocer a otros directores de juego.',
        ],
    ],

    '0.61.0' => [
        'date' => '2026-10-13',
        'title' => 'Modo demo antes del Kickstarter',
        'items' => [
            'Nuevo modo demo, que se activa en Administración › Planes: cada cuenta nueva recibe el plan Demo, con su propia copia de la campaña de demostración y un aviso que anuncia la próxima campaña de micromecenazgo.',
            'La demostración de una cuenta demo está bloqueada: todo se puede visitar, pero solo se usan la sesión, las notas, las fichas del mapa y la pantalla de mesa. La cuenta también puede crear una pequeña campaña propia (50 MB, 3 miembros, sin IA ni exportación).',
            'Una invitación de un administrador abre una cuenta normal. En «Cuentas», «Desbloquear» pasa una cuenta demo al plan gratuito e inicia su prueba, que la demo no consume.',
        ],
    ],

    '0.60.0' => [
        'date' => '2026-10-13',
        'title' => 'Contenido casero',
        'items' => [
            'Nueva casilla «Contenido casero» en las fichas (PNJ, hechizos, objetos, lugares…): sin marcar, la ficha es oficial. Una insignia «Casero» la señala en la lista y en la ficha, y un filtro Oficial / Casero ordena el universo.',
            'Para una ficha casera o una regla de origen «Casero», la versión oficial sustituida se anota al lado (por ejemplo la iniciativa con 1d10 en vez de 1d20): queda en la zona del DJ, plegada bajo la ficha o la regla.',
            'La lista de reglas se filtra por origen. La exportación y la importación de campaña conservan el contenido casero y la versión oficial.',
        ],
    ],

    '0.59.1' => [
        'date' => '2026-10-13',
        'title' => 'Comprobación de puesta en línea más precisa',
        'items' => [
            'Administración: `sagawyn:check` ya no indica por error un registro en modo depuración cuando LOG_LEVEL vale warning.',
        ],
    ],

    '0.59.0' => [
        'date' => '2026-10-13',
        'title' => 'El logotipo de Autistic Intelligence',
        'items' => [
            'El logotipo de Autistic Intelligence, editor de SagaWyn, aparece ahora al pie de las páginas, en la pantalla de inicio de sesión y en el aviso legal. Sigue el tema claro u oscuro.',
        ],
    ],

    '0.58.0' => [
        'date' => '2026-10-12',
        'title' => 'Una pista puede ser una mentira',
        'items' => [
            'Pistas: nueva naturaleza «Mentira», junto a Rumor, Pista y Verdad, con su filtro. Como las demás naturalezas, solo la ve el DJ: el jugador al que se revela solo lee la pista.',
        ],
    ],

    '0.57.0' => [
        'date' => '2026-10-12',
        'title' => 'Preparar una campaña entera con una IA',
        'items' => [
            'Mis campañas → Importar una campaña: nuevo enlace «Prepara el archivo con una IA». El prompt (o el skill de Claude) hace que su IA lea el libro de rol y cree un archivo .zip: el juego, el mundo, la campaña, las fichas, las escenas y los documentos se crean de una vez.',
        ],
    ],

    '0.56.1' => [
        'date' => '2026-10-11',
        'title' => 'Páginas de juego y mundo legibles en el móvil',
        'items' => [
            'En el móvil, la imagen de un juego o mundo aparece encima del nombre a todo el ancho: el nombre y la descripción ya no quedan aplastados y el botón Editar ya no los tapa.',
        ],
    ],

    '0.56.0' => [
        'date' => '2026-10-11',
        'title' => 'Los secretos pasan a ser pistas',
        'items' => [
            'Los «secretos» de la campaña ahora se llaman «pistas»: página Pistas, panel de fichas, escenas y documentos, modo Sesión, búsqueda, revelaciones y ayuda. Tus datos no cambian.',
        ],
    ],

    '0.54.0' => [
        'date' => '2026-10-11',
        'title' => 'Los jugadores editan su personaje',
        'items' => [
            'Página Campos: «Editable por el jugador» se marca para todo un grupo (características, habilidades…) o para todos los campos públicos con un clic.',
            'Nueva opción en la página Jugadores: el DJ puede dejar que cada jugador edite el retrato, el resumen y la descripción de su personaje. La zona del DJ sigue oculta.',
        ],
    ],

    '0.51.0' => [
        'date' => '2026-10-11',
        'title' => 'Insignia de Fundador y códigos regalo',
        'items' => [
            'Los mecenas de Kickstarter pueden marcarse como Fundador en la consola de administración: una insignia ★ Fundador visible en las preferencias, la lista de miembros y la hoja del personaje.',
            'Los Fundadores tienen almacenamiento adicional sobre su plan, gratuito o premium (500 MB por defecto, ajustable en «Planes»).',
            'Nueva página «Mis recompensas» (desde las preferencias): tu recompensa de Kickstarter, códigos regalo Premium para regalar por enlace, con el nombre de quien usó cada uno, y una casilla para usar un código recibido. Los meses se suman al final del Premium actual.',
        ],
    ],

    '0.50.1' => [
        'date' => '2026-10-11',
        'title' => 'Salud: filtro por rol',
        'items' => [
            'En la pestaña Salud, un filtro por rol de cuenta (sin administradores, administradores, premium, prueba, gratuito).',
            'La campaña de demostración ya no se cuenta en las estadísticas.',
        ],
    ],

    '0.50.0' => [
        'date' => '2026-10-10',
        'title' => 'Salud de la plataforma',
        'items' => [
            'Nueva pestaña Salud en la consola de administración: personas conectadas y número de conexiones por día, semana o mes, jugadores por campaña y por DJ, espacio ocupado (archivos, base, disco) y tiempo de respuesta del servidor hora a hora.',
            'La consola avisa cuando el servidor se ralentiza: más del 5 % de las peticiones por encima de un segundo en 24 horas.',
        ],
    ],

    '0.49.1' => [
        'date' => '2026-10-10',
        'title' => 'Una ayuda más completa',
        'items' => [
            'La ayuda responde a 19 preguntas nuevas: juego, mundo y campaña, la demo, búsqueda y enlaces, etiquetas, escenarios, modo Sesión, campos del juego, reglas, duplicación, intercambios, la marcha de un jugador, el diario, el fin de Premium y, para los jugadores: ficha, notas, mensajes y opinión.',
        ],
    ],

    '0.49.0' => [
        'date' => '2026-10-10',
        'title' => 'La demo muestra todo SagaWyn',
        'items' => [
            'La campaña de demostración usa ahora cada función: un personaje jugador sacado de un pregenerado, con lo que ha recibido (ficha, documento, regla, secreto, objetos) y que el DJ puede «Ver como»; notas de sesión; una lista «Por jugar»; fichas fijadas; campos de referencia, documento y comunes a dos tipos; un adjunto del DJ; secretos de cada tipo; etiquetas de colores; portadas para el juego y el mundo.',
            'Volver a cargar la demo reutiliza el tipo de ficha «Facción» ya creado en lugar de añadir otro.',
        ],
    ],

    '0.48.0' => [
        'date' => '2026-10-10',
        'title' => 'Una demostración más completa',
        'items' => [
            'La campaña de demostración «El Juramento de Pierrecendre» incluye ahora tres ambientes sonoros originales vinculados a las escenas, una primera sesión ya jugada con su resumen y una solicitud de opinión a los jugadores.',
            'Corrección: el fundido entre dos pistas ya no detiene la reproducción en algunos navegadores.',
        ],
    ],

    '0.47.0' => [
        'date' => '2026-10-10',
        'title' => 'LoreMundi pasa a llamarse SagaWyn',
        'items' => [
            'LoreMundi ahora se llama SagaWyn, publicado como siempre por Autistic Intelligence. Every world has a story.',
            'Tus campañas, cuentas y archivos no cambian: las copias hechas con LoreMundi o CodexFlow se siguen importando. Los archivos descargados empiezan ahora por «sagawyn-».',
        ],
    ],

    '0.46.0' => [
        'date' => '2026-10-10',
        'title' => 'Opiniones de los jugadores',
        'items' => [
            'Al final de una sesión o campaña, pide a tus jugadores una nota de 1 a 5 estrellas, lo que les gustó y lo que podría mejorar («Opiniones de los jugadores» en la campaña, o desde la página de una sesión).',
            'Respuestas anónimas o firmadas, a elección del DJ: los jugadores lo saben antes de responder. Ves la media, el reparto de notas y todos los comentarios.',
        ],
    ],

    '0.45.0' => [
        'date' => '2026-10-10',
        'title' => 'Biblioteca de sonido',
        'items' => [
            'Nueva página «Sonidos»: sube tu música y ambientes (mp3, ogg, m4a, wav, flac), ordénalos con etiquetas y vincúlalos a las escenas.',
            'En el modo Sesión, el bloque «Música» propone primero los sonidos de la escena: «Aquí» los reproduce en tu dispositivo, sin cortarse al cambiar de página; «Mesa» los reproduce en la pantalla de mesa, con pausa, bucle y volumen controlados desde la sesión.',
        ],
    ],

    '0.44.0' => [
        'date' => '2026-10-09',
        'title' => 'Listo para publicar',
        'items' => [
            'La administración recibe un aviso en la consola cuando la instalación tiene un punto bloqueante (depuración activa, correo sin configurar, tiempo real sin cifrar…).',
            'La base de datos y los archivos subidos se guardan cada noche en el servidor.',
        ],
    ],

    '0.43.0' => [
        'date' => '2026-10-09',
        'title' => 'Fiabilidad',
        'items' => [
            'Nuevas pruebas automáticas comprueban en un navegador real la actualización sin tiempo real, «Recordarme», el enlace «Ir al contenido», la visualización en el móvil con texto muy grande y una función desactivada por el máster con una página abierta.',
        ],
    ],

    '0.42.0' => [
        'date' => '2026-10-09',
        'title' => 'Descripciones con formato',
        'items' => [
            'Las descripciones de juego, mundo, escenario y documento tienen el mismo editor que las fichas: negrita, cursiva, subtítulos, listas, citas y enlaces [[ ]] a fichas dentro de una campaña.',
        ],
    ],

    '0.41.0' => [
        'date' => '2026-10-09',
        'title' => 'Campos compartidos',
        'items' => [
            'Un campo puede afectar a varios tipos de ficha a la vez, por ejemplo los puntos de vida de personajes y criaturas; las plantillas de juego y los archivos de campos conservan esta elección.',
        ],
    ],

    '0.40.0' => [
        'date' => '2026-10-09',
        'title' => 'Aplicación y seguridad',
        'items' => [
            'La aplicación instalada en el móvil tiene una descripción en tu idioma y un icono adaptado a los iconos redondos de Android.',
            'Sin conexión, las páginas siguen siendo legibles y los botones de edición aparecen en gris hasta que vuelve la red.',
            'El navegador solo acepta ya scripts, imágenes y conexiones procedentes del propio LoreMundi.',
        ],
    ],

    '0.39.0' => [
        'date' => '2026-10-09',
        'title' => 'Cuenta y administración',
        'items' => [
            'Se puede informar de un problema sin cuenta, desde las páginas de inicio de sesión y registro o la ayuda, dejando una dirección para la respuesta.',
            'Las Preferencias muestran la fecha de inicio de la suscripción y, mientras el pago no esté disponible, que el paso a Premium se abrirá pronto.',
            'Consola de administración: días de conexión en lugar de conexiones, fecha de la última conexión y aviso cuando el envío push es imposible o falla.',
        ],
    ],

    '0.38.0' => [
        'date' => '2026-10-09',
        'title' => 'Búsqueda',
        'items' => [
            'La búsqueda también encuentra otras formas francesas de una palabra: «lanternes» encuentra «lanterne», «éteinte» encuentra «éteintes».',
            'La búsqueda desde el inicio también busca en tus mundos y juegos que no están vinculados a ninguna campaña.',
        ],
    ],

    '0.37.0' => [
        'date' => '2026-10-09',
        'title' => 'Jugadores y roles',
        'items' => [
            'Una ficha revelada muestra al jugador sus ilustraciones y archivos públicos, y sus relaciones públicas con fichas que conoce.',
            'Los jugadores tienen un «Hilo de la campaña» en su ficha: sesiones, eventos jugados conocidos por la mesa y mensajes al grupo.',
            'Los co-DJ ven las páginas del juego y del mundo en solo lectura, descargan el archivo y la plantilla del juego, y gestionan los campos si el propietario lo marca en «Miembros». Un co-DJ degradado no conserva las notificaciones recibidas como DJ.',
        ],
    ],

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
