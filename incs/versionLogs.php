<?php
$versionLogs = [
    '26.8.27' => [
        'date' => '2026-04-05',
        'changes' => [
            'Personajes funciona en Android/Termux: fallback a 00_Personajes.md si SQLite no existe.',
            'Modo readonly: en Android se ve la lista completa sin botones de postulación.',
        ]
    ],
    '26.8.26' => [
        'date' => '2026-04-05',
        'changes' => [
            'Nuevo personaje P90 ESCENA — notas de escena del Director como burbujas.',
            'Botón + burbuja inserta ESCENA por defecto (antes NUEVO PERSONAJE).',
            'P90 disponible en dropdown de cambio de personaje y en Personajes/Casting.',
        ]
    ],
    '26.8.25' => [
        'date' => '2026-04-05',
        'changes' => [
            'Edición de marcas de tiempo en formato HH:MM:SS.d (input + display consistente).',
            'parseTime() acepta HH:MM:SS.d, MM:SS y segundos crudos como fallback.',
            'Fix nudge ◂▸: decimal visible para reflejar cambios ±0.1s.',
        ]
    ],
    '26.8.24' => [
        'date' => '2026-04-05',
        'changes' => [
            'Marcas de tiempo en formato HH:MM:SS en modo Director (antes segundos crudos).',
            'Botón + inserta genérico: burbuja P00 "NUEVO PERSONAJE" / línea "(nuevo diálogo)".',
            'Timestamp del nuevo cue auto-incrementado (+0.5s línea, +1.0s burbuja).',
            'Ya no duplica el elemento anterior al insertar.',
        ]
    ],
    '26.8.23' => [
        'date' => '2026-04-04',
        'changes' => [
            'PERSONAJES: página pública de casting (/casting/) con postulación abierta.',
            'Director habilita/deshabilita personajes para postulación (toggle ✅/⬜).',
            'Teléfonos de actores privados: visible solo para Director.',
            'Personajes listados desde SQLite (fuente de verdad), no del .md estático.',
            'Synopsis de cada personaje visible en la tarjeta.',
            'Link 🎭 Personajes en la lista de audios (centrado, paleta armónica).',
            'Director detectado via localStorage (consistente con perfiles.js).',
            'Fix desfasaje de resaltado: highlight usa cue_index de DB, no posición de array.',
        ]
    ],
    '26.8.22' => [
        'date' => '2026-04-04',
        'changes' => [
            'Fix Android/Termux: try/catch en SQLite — fallback automático a JSON estático.',
            'Fix JS: guard null en autoplayMessage (eliminado del HTML).',
            'AUTO-SYNC: save_changes.php regenera guion_completo.json tras cada edición del Director.',
            'start_termux.sh: descarga JSON fresco del servidor via curl al arrancar (sin depender de git push).',
            'Nuevo tool: export_sqlite_to_json.php (CLI + web) para sincronizar SQLite → JSON.',
        ]
    ],
    '26.8.21' => [
        'date' => '2026-04-04',
        'changes' => [
            'CRUD COMPLETO: Duplicar (+), Eliminar (🗑), Editar (doble-click).',
            'Cambiar personaje de burbuja: doble-click en nombre → selector → actualiza todo el grupo.',
            'Pegado fuerza texto plano (sin HTML/estilos de otras páginas).',
            'cue_index real de DB como referencia (no posición de array) — fix "cue no encontrado".',
            'Audio preserva posición y estado play/pause tras operaciones CRUD.',
            'Burbujas pasadas legibles (opacity 0.75-0.8, sin blur).',
            'Limpieza DB: eliminados cues fantasma (idx>9999), reindexado secuencial.',
        ]
    ],
    '26.8.20' => [
        'date' => '2026-04-04',
        'changes' => [
            'DUPLICADOR SERIAL: + grande=duplica burbuja completa, + chico=duplica línea.',
            'Batch insert backend (insertBatchCues) para duplicar grupos enteros.',
            'Reindex infalible con índices negativos temporales (sin colisiones UNIQUE).',
            'Agrupación inteligente: rompe grupo cuando tiempos retroceden.',
        ]
    ],
    '26.8.19' => [
        'date' => '2026-04-04',
        'changes' => [
            'Inserción intralínea: mini "+" entre diálogos dentro de cada burbuja.',
            'Play/Pause ▶⏸ en toolbar Director. Quita stamp 🎯 y nav ⏮⏭.',
            'Toggles solo cambian color (sin checkmarks ✓).',
        ]
    ],
    '26.8.18' => [
        'date' => '2026-04-04',
        'changes' => [
            'INSERTAR BURBUJAS: Toggle ➕ en toolbar Director muestra/oculta botones de inserción.',
            'Selector de personaje (P00-P26) al insertar nueva burbuja + texto.',
            'Backend: save_changes.php acepta character/idp para inserción flexible.',
        ]
    ],
    '26.8.17' => [
        'date' => '2026-04-04',
        'changes' => [
            'ARQUITECTURA: Datos inline — PHP inyecta cues de SQLite directo en el HTML.',
            'Eliminado fetch para carga inicial: datos viajan SQLite → PHP → HTML → JS.',
            'Fallback a API y luego JSON estático si SQLite no disponible.',
        ]
    ],
    '26.8.16' => [
        'date' => '2026-04-03',
        'changes' => [
            'Fix: API carga solo el track actual (rápido), evita timeout y fallback al JSON viejo.',
            'Los datos se leen/escriben directo de SQLite, sin caché intermedia.',
        ]
    ],
    '26.8.15' => [
        'date' => '2026-04-03',
        'changes' => [
            'ARQUITECTURA: Backend migrado de JSON+git a SQLite (ACID, sin dependencia de git).',
            'Nuevo: data/db.php (capa de acceso SQLite), audios/api_cues.php (API REST).',
            'save_changes.php reescrito: escritura directa a SQLite, sin commit/push.',
            'Karaoke: carga datos desde api_cues.php con fallback a JSON estático.',
            'DB location: /var/www/vcby-data/vcby.db (fuera del repo, inmune a deploys).',
        ]
    ],
    '26.8.14' => [
        'date' => '2026-04-03',
        'changes' => [
            'Fix: commit_cambios.sh ahora hace git pull --rebase antes de commit (evita sobreescritura por push desde PC).',
            'Flujo: stash edits → pull rebase → pop edits → commit → push.',
        ]
    ],
    '26.8.13' => [
        'date' => '2026-04-03',
        'changes' => [
            'Fix: save_changes.php blindado — suprime warnings PHP, output buffer, jsonResponse() helper.',
            'Garantiza respuesta JSON limpia sin importar errores internos de PHP.',
        ]
    ],
    '26.8.12' => [
        'date' => '2026-04-03',
        'changes' => [
            'Fix: URLs relativas restauradas (funciona en local y producción).',
            'Fix: Errores de conexión ahora muestran detalle real del fallo.',
            'apiBase configurable: vacío=relativo, override para cross-origin.',
        ]
    ],
    '26.8.11' => [
        'date' => '2026-04-03',
        'changes' => [
            'Fix: API URLs absolutas — save/commit/login siempre van a rmonla.duckdns.org (funciona desde VPN, localhost, etc.).',
        ]
    ],
    '26.8.10' => [
        'date' => '2026-04-03',
        'changes' => [
            'UX: Modal de perfiles simplificado — 5 taps va directo a pedir clave Director (sin paso intermedio Público).',
            'UX: Si ya es Director, 5 taps ofrece "Salir del modo Director".',
            'Fix: Permisos del servidor restaurados post-deploy para edición remota.',
        ]
    ],
    '26.8.9' => [
        'date' => '2026-04-03',
        'changes' => [
            'Director remoto: commit + push a GitHub desde la web (edición desde cualquier dispositivo).',
            'Servidor: permisos www-data configurados para escritura en subs/ y git.',
            'Deploy: post-pull restaura permisos automáticamente.',
        ]
    ],
    '26.8.8' => [
        'date' => '2026-04-03',
        'changes' => [
            'Modal inline (vcbyModal): reemplazo total de alert/prompt nativos por modales integrados al sitio.',
            'Director: click en línea solo posiciona audio sin auto-play (evita scroll involuntario al editar).',
            'Nuevo jss/modal.js: vcbyAlert (success/error/info), vcbyPrompt, vcbyConfirm — API Promise-based.',
        ]
    ],
    '26.8.7' => [
        'date' => '2026-04-03',
        'changes' => [
            'Director: Modo Marcaje (🎯) — tap en línea durante reproducción fija su startTime al instante actual.',
            'Director: Nudge ◂/▸ por frase — ajuste ±0.1s con un click sin abrir editor.',
            'Director: Contador en vivo del tiempo de reproducción en la toolbar.',
            'Director: Stamp mode activa time-edit-mode automáticamente.',
            'Refactor: applyTimeChange centralizado para stamp/nudge/edit manual.',
        ]
    ],
    '26.8.6' => [
        'date' => '2026-04-03',
        'changes' => [
            'Toolbar Director compacta: barra superior con iconos toggle.',
            'Edicion de tiempos inline por frase (toggle activable).',
            'Modelo offline-first: cambios locales + batch save al commitear.',
            'Panel de notas desplegable minimalista.',
            'Fase 5 COMPLETADA: burbujas agrupadas + toolbar + tiempos.',
        ]
    ],
    '26.8.5' => [
        'date' => '2026-04-03',
        'changes' => [
            'Rediseno karaoke: burbujas agrupadas por personaje (Fase 5).',
            'Lineas consecutivas del mismo personaje en una sola burbuja.',
            'Resaltado interno: frase activa iluminada, pasadas atenuadas.',
            'Scroll suave centrado en la linea activa dentro de la burbuja.',
            'Edicion in-place adaptada al nuevo layout agrupado.',
        ]
    ],
    '26.8.4' => [
        'date' => '2026-04-03',
        'changes' => [
            'Fase 4 Perfiles COMPLETADA.',
            'Acotaciones escenicas: boton + entre lineas para insertar P00 (Director).',
            'Fix: panel Director oculto correctamente en modo Publico (doble proteccion CSS+JS).',
            'Colores por personaje visibles en ambos perfiles.',
        ]
    ],
    '26.8.3' => [
        'date' => '2026-04-02',
        'changes' => [
            'Sistema de Perfiles dual: Público (actores) y Director (edición avanzada).',
            'Modal de selección de perfil con autenticación para Director.',
            'Panel de Notas de Dirección por track (auto-guardado localStorage).',
            'Edición in-place de subtítulos: doble-click para editar, Enter para guardar.',
            'Endpoint save_changes.php: actualiza JSON y v4.0.md en servidor.',
            'Commit local desde interfaz web: barra flotante + tools/commit_cambios.sh.',
            'Colores por personaje en subtítulos (ambos perfiles). IDP badges y tiempos locales (Director).',
            'Nuevo compilador compilar_json_v4.py: lee v4.0.md directamente con campo IDP.',
            'WhatsApp Director-only: compartir track con notas incluidas.',
        ]
    ],
    '26.8.2' => [
        'date' => '2026-04-02',
        'changes' => [
            'Serie 200 completa: Audios 201 a 207 sellados como v4.0.md tras revisión humana.',
            'Nuevo personaje P26: MUJERES JERUSALEN incorporado al catálogo maestro.',
            'Corrección de formato en tablas Markdown (columnas P00 normalizadas).',
            'Fix regex en reordenar_personajes.rb para soportar IDs provisorios (P??).',
            'Seguridad: Eliminado .candado.key, migrado a daemon en memoria RAM (UNIX Socket).'
        ]
    ],
    '26.8.1' => [
        'date' => '2026-04-02',
        'changes' => [
            'Inicio formal del Plan 08. Ejecución automatizada de refactorización de cronología de IDs de personajes.'
        ]
    ],
    '26.7' => [
        'date' => '2026-04-02',
        'changes' => [
            'Apertura oficial de la Fase de Pulido y Revisión Fina. Creación del documento planificador (docs/plan).',
            'Conclusión del Plan 07: Entorno Móvil/Android Offline totalmente validado.',
            'Transición de visibilidad: Pase del repositorio a Público y simplificación del flujo git-pull HTTPS.',
            'Sanitización del repositorio: Eliminación de docker-compose y archivos legacy de infraestructura local.',
            'Nuevo script de arranque rápido y atajos nativos en Android (start_termux.sh).'
        ]
    ],
    '26.6' => [
        'date' => '2026-04-02',
        'changes' => [
            'HITO CRÍTICO: Compilación de subtítulos HITL completada al 100% (Pistas 101 a 403) en mega-JSON maestro.',
            'Implementación de Reproductor Web tipo Karaoke: autoscroll inteligente, UI teatral, historial dinámico y caché.',
            'Automatización IA: Pipeline estructurado de 5 fases usando Whisper (transcripción) y LLaMA 3.3 (deducción MD).',
            'Catálogo Literario: Consolidación semiótica de personajes (25 roles) e ingesta canónica en el script principal.',
            'UX y System Triage: Persistencia de volumen vía localStorage, corrección del footer sticky y CSS responsive.'
        ]
    ],
    '26.5' => [
        'date' => '2026-03-29',
        'changes' => [
            'IA: Transcripción automática de 34 audios y etiquetado de 18 personajes con Groq (Llama 3.3).',
            'Gestor: Nuevo sistema `api_key_rotator` con encriptación AES-256 rotativo para evitar límites de API.',
            'Guion: Generación de v1.0 por la IA, seguida de revisión y corrección humana para publicar `Guion-vcby2026_v1.1.md`.',
            'Documentación: Creación del `personajes.md` detallando las intervenciones por escena.',
            'Update: Inclusión de nuevo borrador y reorganización en el README principal.'
        ]
    ],
    '26.4' => [
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
