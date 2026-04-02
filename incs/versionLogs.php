<?php
$versionLogs = [
    '26.6.36' => [
        'date' => '2026-04-02',
        'changes' => [
            'Pista 304 (12ºE Jesús muere en la cruz) compilada con éxito usando el nuevo Flujo H.I.T.L. Optimizado.'
        ]
    ],
    '26.6.35' => [
        'date' => '2026-04-02',
        'changes' => [
            'Procesada la pista 303 (11ºE Jesús clavado en la cruz). Incorporado P24 (Soldados). Guardada en maestro.'
        ]
    ],
    '26.6.34' => [
        'date' => '2026-04-02',
        'changes' => [
            'Procesada la pista 111. Ajuste dramático en la condena, flagelación y coronación, guardada en maestro.'
        ]
    ],
    '26.6.33' => [
        'date' => '2026-04-02',
        'changes' => [
            'Procesada la pista 110. Correcciones filológicas en Herodes y Herodías y guardada en maestro.'
        ]
    ],
    '26.6.32' => [
        'date' => '2026-04-02',
        'changes' => [
            'Procesada la pista 108 y 109. Completado el pase filológico, y ambas listas para web.'
        ]
    ],
    '26.6.31' => [
        'date' => '2026-04-02',
        'changes' => [
            'Procesada pista 107 con IA generativa, pase filológico, compilada al JSON global exitosamente.'
        ]
    ],
    '26.6.30' => [
        'date' => '2026-04-01',
        'changes' => [
            'Implementado versión dinámica de pipeline AI (v1 a v4). Personajes globalizados en 00_Personajes.md. Pistas 105 y 106 compiladas exitosamente con nuevo refactor.'
        ]
    ],
    '26.6.29' => [
        'date' => '2026-04-01',
        'changes' => [
            'Proceso H.I.T.L Transcripción 105: Se inyectó gramática poética en `105_v1.1.md`. Se recompiló master incorporando la pista 105 a la capa de UI.'
        ]
    ],
    '26.6.28' => [
        'date' => '2026-04-01',
        'changes' => [
            'Proceso H.I.T.L Transcripción 104: Se pulió ortográfica y gramaticalmente `104_v1.1.md` de acuerdo al "Guion-vcby2026_Editado". Se recompiló exitosamente el maestro y se insertó la cuarta pista completa (Audio 104) en el sistema.'
        ]
    ],
    '26.6.27' => [
        'date' => '2026-04-01',
        'changes' => [
            'Mejora UX Karaoke: Modificación de `karaoke.js` para que el autoscroll alinee el subtítulo activo en la parte superior (en lugar de centrarlo). Se agregó un nuevo estilo `cue-past` en `style.css` que difumina (blur) y reduce la opacidad fuertemente del texto ya reproducido, generando un efecto visual donde lo pasado queda "oculto" arriba.'
        ]
    ],
    '26.6.26' => [
        'date' => '2026-04-01',
        'changes' => [
            'Refinado Filológico Global (101, 102, 103): Se reescribieron los guiones v1.1 para inyectar ortografía, acentuación y signos de puntuación propios del documento semiótico base. Se enlazó esto con una recompilación exitosa en `guion_completo.json` habilitando textos puros y dramatúrgicos en el renderizado final del reproductor.'
        ]
    ],
    '26.6.25' => [
        'date' => '2026-04-01',
        'changes' => [
            'Flujo H.I.T.L. (Pistas 103, 104, 105): Se recompiló el `guion_completo.json` para inyectar en caliente los progresos de revisión del archivo `103_v1.1.md`. Adicionalmente, se generaron las plantillas esqueleto (v0.1) de transcripciones para El Monte de los Olivos (104) y La Entrega (105).'
        ]
    ],
    '26.6.24' => [
        'date' => '2026-04-01',
        'changes' => [
            'Sincronización de Base de Datos Estática (`incs/elementos.php`): Se forzó el push y despliegue del catálogo de títulos de audios actualizado localmente. Esto soluciona la discrepancia observada en la lista del reproductor, descartando el antiguo título `Adeje16-Aguas-Monedas` en favor de `La Última Cena + Monedas de Judas`.'
        ]
    ],
    '26.6.23' => [
        'date' => '2026-04-01',
        'changes' => [
            'Scripts Globales (`jss/js.js`): Se retiró a nivel nuclear la función legacy `fadeOutAudio`. Este script reducía interactivamente el canal del volumen a `0.0` antes de redirigir, lo que entraba en grave conflicto con la persistencia `localStorage`, obligando al código a hardcodear en el último mili-segundo `audio.volume = 1.0` pisando por ende los ajustes del usuario y causando explosiones auditivas en la pista subsecuente.'
        ]
    ],
    '26.6.22' => [
        'date' => '2026-04-01',
        'changes' => [
            'Audio Player (`audios/play.php`): Incorporación de estado persistente del volumen de reproducción usando el entorno nativo `localStorage`. Ahora el usuario puede calibrar el volumen a su preferencia (ej. 50%) y el sistema retendrá el nivel exacto al cambiar a reproducciones subsecuentes.'
        ]
    ],
    '26.6.21' => [
        'date' => '2026-04-01',
        'changes' => [
            'System Triage: Detectado y reparado fallo de sincronización en el Webhook de CI/CD del servidor de producción Proxmox (`srvv-nginx-rm`) ocasionado por conflictos locales, obligando a un `git reset --hard`.',
            'CSS Core: Removida la regla `overflow-x: hidden;` de la etiqueta `body`, ya que los navegadores modernos inhabilitan la propiedad `position: sticky` de los elementos hijos (en este caso el Footer) a nivel del Viewport cuando el contenedor padre presenta restricciones de desbordamiento horizontal. Footer estabilizado en Desktop y Móvil.'
        ]
    ],
    '26.6.20' => [
        'date' => '2026-04-01',
        'changes' => [
            'Componente Footer (`css/style.css`): Añadida la regla estricta `position: sticky; bottom: 0;` en el contenedor del footer garantizando su persistencia visible y evitando que quede colapsado/desbordado por el contenido excesivo del flexbox envoltorio, especialmente en modo móvil.',
            'Núcleo Guion (`guion/js/parser.js`): Refactorización del renderizador de Vellum. Se inyectó una caché de lectura secuencial de personajes (`lastCharacterGlobal`) calcando la UX lograda en el karaoke, agrupando visualmente de inmediato monólogos que posean intervenciones de acotaciones intermedias, resultando en un Markdown traducido a Libreto más limpio.'
        ]
    ],
    '26.6.19' => [
        'date' => '2026-04-01',
        'changes' => [
            'FrontEnd Playback `css/style.css`: Fijada corrección de propiedad estática CSS `position: relative` para el contenedor primario del V-DOM solucionando los desbordamientos del offsetTop que rompían el motor de smooth scrolling, logrando que la frase en reproducción se ancle centradamente.',
            'FrontEnd Karaoke `jss/karaoke.js`: Refactor del script de redibujado; implementada retención de caché (`lastCharacter`) que verifica y purga visualmente el nombre del personaje si este habla en más de dos cuadros continuos. Esto otorga al guion visual simetría de Párrafo Teatral Puro (UX).'
        ]
    ],
    '26.6.18' => [
        'date' => '2026-04-01',
        'changes' => [
            'IA Protocol: Incorporado parche de idioma (`language: es`) en la API de Whisper (`groq_client.rb`) para suprimir las alucinaciones bilingües causadas por altos niveles de música instrumental de fondo en los registros legacy.'
        ]
    ],
    '26.6.17' => [
        'date' => '2026-04-01',
        'changes' => [
            'IA Protocol: Refactorizado Whisper/LLaMA parser. Se confía en la fila pre-cargada del v0.1.md para los gaps iniciales, optimizando el control humano total y previniendo inyecciones ciegas de código Ruby.'
        ]
    ],
    '26.6.16' => [
        'date' => '2026-04-01',
        'changes' => [
            'IA Protocol: Compilador Definitivo `compilador_v1.1.rb` construido. Consolidada la fase de ingesta de los flujos M.D a guion_completo.json automatizando parsheo de IDPs, startTime y endTime.',
            'Scripting: Se procesaron satisfactoriamente las escenas 101 y 102 compiladas en su versión final v1.1 hacia producción.'
        ]
    ],
    '26.6.15' => [
        'date' => '2026-04-01',
        'changes' => [
            'IA Protocol: Refinada regla de sintaxis para Tabla MD, introduciendo `IDP` en lugar del ambiguo `IP`.',
            'IA Protocol: Incorporación estricta de regla Zero-Gap para LLaMA 3.3. Si Whisper reporta primer diálogo después de 00:00, LLaMA inyectará matemáticamente la fila inicial de P00 (Música/Ambiente) preservando la interactividad del DOM al milisegundo.'
        ]
    ],
    '26.6.14' => [
        'date' => '2026-04-01',
        'changes' => [
            'IA Integration: Consolidación Final del flujo HITL. Antigravity genera manualmente el template base v0.1.md con IP de personajes.',
            'Scripting: genera_subs_v1.0.rb unifica la extracción vía Whisper de los tiempos, leyendo el template v0.1.md para mapear personajes vacíos y volcarlos en v1.0.md vía LLaMA 3.3.'
        ]
    ],
    '26.6.13' => [
        'date' => '2026-04-01',
        'changes' => [
            'IA Integration: Consolidación del flujo Human-in-the-Loop (HITL) basando la intervención mediante formato Markdown Tabular (v0.1.md -> v1.0.md -> v1.1.md) para revisión teatral humana directa.',
            'Scripting: Creación de genera_subs_v0.1.rb para transcripción directa de MP3 a Markdown y genera_subs_v1.0.rb para mapeo de personajes semántico en Markdown con Groq LLaMA 3.3.'
        ]
    ],
    '26.6.12' => [
        'date' => '2026-04-01',
        'changes' => [
            'IA Integration: Creación de scripts en Ruby interactuando con Groq API whisper-large-v3 para desgrabar audios crudos.',
            'IA Integration: Inclusión de script mapeador con Llama 3.3 70B para inyectar personajes del guion antiguo sobre marcas de tiempo exactas (MVP exitoso con Pista 101).'
        ]
    ],
    '26.6.11' => [
        'date' => '2026-04-01',
        'changes' => [
            'Estilo Teatral: Formateo definitivo del timestamp en HH:MM:SS para tiempo global absoluto, añadiendo el sufijo [ID] de la pista.'
        ]
    ],
    '26.6.10' => [
        'date' => '2026-04-01',
        'changes' => [
            'Tooling: Creación de scripts para calcular duraciones exactas de cada audio con FFprobe en audio_durations.json.',
            'Cálculo UI: Implementación de sistema dual de tiempo (Global y Relativo) en pantalla (Ej: [108.0.56] y T+ 35:45).'
        ]
    ],
    '26.6.9' => [
        'date' => '2026-04-01',
        'changes' => [
            'Lógica: Acotamiento restrictivo del contexto contiguo al *último* diálogo previo y el *primer* diálogo posterior.'
        ]
    ],
    '26.6.8' => [
        'date' => '2026-04-01',
        'changes' => [
            'UX Tonal: Distinción visual semántica. Fondos azul pálido para pasado, crema para presente inactivo y naranja cálido para futuro.'
        ]
    ],
    '26.6.7' => [
        'date' => '2026-04-01',
        'changes' => [
            'Estilo Teatral UI: Inclusión dinámica del Timestamp matemáticamente calculado para cada bloque [mm:ss].'
        ]
    ],
    '26.6.6' => [
        'date' => '2026-04-01',
        'changes' => [
            'Estilo Teatral UI: Rediseño completo del bloque. El personaje se sitúa a la derecha y el diálogo cae a una nueva línea con sangría de 20px.'
        ]
    ],
    '26.6.5' => [
        'date' => '2026-04-01',
        'changes' => [
            'Logic UI: Carga y renderizado automático contiguo por encima de los diálogos del audio previo tocado. Se utiliza `prevAudioId` nativo.'
        ]
    ],
    '26.6.4' => [
        'date' => '2026-04-01',
        'changes' => [
            'Hotfix: Corrección de crash silencioso en la UI solucionando variables obsoletas y limpieza asimétrica de la clave `ID_vXXXX` a solo `ID`.'
        ]
    ],
    '26.6.3' => [
        'date' => '2026-04-01',
        'changes' => [
            'Refactor: Unificación de +30 archivos de transcripción a un único mega-json `guion_completo.json` y purga del FileSystem local.'
        ]
    ],
    '26.6.2' => [
        'date' => '2026-04-01',
        'changes' => [
            'Interacción Táctil: Lógica manual de tap simple para viajar en la línea de tiempo y *doble tap* continuo global para Pausar/Reproducir.'
        ]
    ],
    '26.6.1' => [
        'date' => '2026-04-01',
        'changes' => [
            'MVP Karaoke Auto-scroll: Inyección del primer contendor interactivo Script Container basado en timeupdate event de HTML5.'
        ]
    ],
    '26.6.0' => [
        'date' => '2026-04-01',
        'changes' => [
            'Tooling Plan 06: Primer borrador del script conversor en Python exportando Markdown Markdown-To-Objects para audios.'
        ]
    ],
    '26.5.0' => [
        'date' => '2026-03-29',
        'changes' => [
            'IA: Transcripción automática de 34 audios y etiquetado de 18 personajes con Groq (Llama 3.3).',
            'Gestor: Nuevo sistema `api_key_rotator` con encriptación AES-256 rotativo para evitar límites de API.',
            'Guion: Generación de v1.0 por la IA, seguida de revisión y corrección humana para publicar `Guion-vcby2026_v1.1.md`.',
            'Documentación: Creación del `personajes.md` detallando las intervenciones por escena.',
            'Update: Inclusión de nuevo borrador y reorganización en el README principal.'
        ]
    ],
    '26.4.0' => [
        'date' => '2026-03-28',
        'changes' => [
            'Fix: Forzar modo claro (color-scheme: light only) — colores consistentes entre dispositivos.',
            'Fix: Alineación izquierda de items de audio — consistente en móvil y escritorio.',
            'Feature: Botón admin oculto (5 taps en versión del footer).',
            'Feature: Sesión admin con TTL 30 min + botón logout.',
            'Feature: Modo admin controla descarga y navegación sin parámetro URL.',
            'Nuevo: admin_check.php para validación de clave.',
            'Refactor: index.php y play.php simplificados, sin dependencia de ?key.',
        ]
    ],
    '26.3' => [
        'date' => '2026-03-28',
        'changes' => [
            'Deploy automático: webhook GitHub → deploy.php → git pull.',
            'Seguridad: NGINX restringe deploy.php a IPs de GitHub.',
            'Documentación: hallazgos de acceso al servidor en contexto IA.',
        ]
    ],
    '26.2' => [
        'date' => '2026-03-28',
        'changes' => [
            'Documentación del proyecto: archivo de contexto IA (docs/contexto/IA.md).',
            'Ficha de nodo del servidor (docs/hosting/srvv-nginx-rm.md).',
            'Despliegue en servidor NGINX propio con HTTPS (rmonla.duckdns.org/vcby/).',
        ]
    ],
    '26.1' => [
        'date' => '2026-03-27',
        'changes' => [
            'Actualización de año 2025 a 2026 en todos los archivos.',
            'Nueva key de acceso: VCV2026.',
            'Acceso público sin key: muestra lista de audios sin botones de descarga.',
            'Títulos de audio en color más oscuro (#2d2418).',
        ]
    ],
    '25.5' => [
        'date' => '2025-03-30', // Actualizar con fecha de hoy
        'changes' => [
            'Rediseño completo del reproductor de audio',
            'Implementación de navegación inferior unificada (Volver/Anterior/Siguiente)',
            'Mejoras en el sistema responsive para móviles',
            'Nuevos efectos visuales y microinteracciones:',
            '   - Efecto "onda" en botones al hacer hover',
            '   - Animación pulsante para icono de WhatsApp',
            '   - Feedback visual al completar reproducción',
            '   - Transición suave al cambiar entre audios',
            'Rediseño del header con título simplificado',
            'Reubicación de la versión al footer',
            'Control de reproducción auto-ajustable al 100% del ancho',
            'Compatibilidad con modo oscuro del sistema',
            'Optimización de rendimiento para animaciones CSS',
            'Corrección del sistema de range requests para navegación en pistas',
            'Mejoras en la accesibilidad táctil para móviles',
            'Integración de will-change para aceleración hardware',
            'Correcciones específicas para navegadores (Firefox, Safari)'
        ]
    ],
    '25.4' => [
        'date' => '2025-03-24',
        'changes' => [
            'Modifica La Negacion, agrega música al inicio.',
            'Modifica La Entrega, agrega música al final.',
            'Modifica Última Cena, agrega Aguas+Monedas-tiempos.',
            'Agrega en tools el script para renombrar los archivos usando base de datos.',
            ],
        ],

    '25.3' => [
        'date' => '2025-03-24',
        'changes' => [
            'Renombra archivos de audio 1ra parte a la forma XXXv25-X_...',
            'Actualiza Ultima Cena y agrega Lavado de pies.',
            'Genera Video Viral 2025',
            'Upload Todo Unido 1º y 3º parte',
            'Actualiza nueva key.',
            ],
        ],
    '25.2' => [
        'date' => '2025-03-16',
        'changes' => [
            'Actualiza valores de Titulos a Viacrusus 2025.',
            ],
        ],
    '25.1' => [
        'date' => '2025-03-10',
        'changes' => [
            'Se genera y actualiza a la nueva notacion de versionado del 5.6 a 25.1.',
            ],
        ],
    '5.6' => [
        'date' => '2025-03-25',
        'changes' => [
            'www/: Se re organiza carpetas para separar la configuración docker con la página.',
            'docker-compose: Se optimiza para que tome la ultima version de php:samba.',
            ],
        ],
    '5.5.5' => [
        'date' => '2025-02-08',
        'changes' => [
            'Test de nueva bdAudios.',
            ],
        ],
    '5.5.4' => [
        'date' => '2025-01-23',
        'changes' => [
            'Mejora la visualización del código del array audioFiles.',
            'Traslada el archivo versionLogs al directorio includes.',
            'Crea kerberos.php para prevenir la exposición accidental de archivos del directorio.',
            'Modifica y añade archivos index.php en cada directorio para reforzar la seguridad.',
            ],
        ],
    '5.5.3' => [
            'date' => '2024-12-22',
            'changes' => [
                'Reorganización de archivos y directorios, trasladándolos de la carpeta "src" a la raíz del sitio.',
                'Creación de la herramienta "Ver Versiones" (tools/verVersiones.php) para mostrar el historial de cambios.',
                'Incorporación de los botones "Anterior" y "Siguiente" en (play.php) para una navegación más intuitiva.',
                'Modificación de estilos para mejorar la apariencia de los botones (css/style.css).',
                'Separación de las cabeceras y pies de página para incluirlos de forma independiente (include/header.php y include/footer.php).',
                'Actualización de index.php y play.php para utilizar include/header.php e include/footer.php.'
            ],
        ],
    '5.5.2' => [
        'date' => '2024-12-22',
        'changes' => [
            'Actualización de cambios y detalles de versiones.',
            'Creación del *Historial de Versiones* (versionLogs.php).',
            'Actualización del audio "PCVBY1005_v2.mp3", agregando música de peregrinación de ángeles para mejorar el final.',
            'Modificación de IDs, nombres de archivos de audio y nombres para mostrar (audioFiles.php).',
        ],
    ],
    '5.5.1' => [
        'date' => '2024-12-22',
        'changes' => [
            ' FALTA CARAGAR',
        ],
    ],
    '5.4.3' => [
        'date' => '2024-12-21',
        'changes' => [
            'Mejoras significativas en la apariencia de la interfaz.',
            'Uso de JavaScript para una experiencia de usuario más interactiva.',
            'Diseño moderno con estilos avanzados.',
        ],
    ],
    '5.4.2' => [
        'date' => '2024-12-19',
        'changes' => [
            'Mejoras en la seguridad del sistema.',
            'Ocultación de la dirección física de los archivos .mp3 mediante el uso de serve.php.',
            'Validación de la existencia de archivos antes de servirlos.',
        ],
    ],
    '5.4.1' => [
        'date' => '2024-12-15',
        'changes' => [
            'Versión inicial del proyecto.',
            'Funcionalidad básica para listar y reproducir archivos .mp3.',
        ],
    ],
];

// Ordena las claves del array en orden descendente
uksort($versionLogs, 'version_compare');
$latestVersion = array_key_last($versionLogs);
$latestDetails = $versionLogs[$latestVersion];
?>
