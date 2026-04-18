<?php
/**
 * Historial de Versiones — ViaCrucis Bahía Yokavil
 *
 * Formato:  AÑ.NN  (año 2 dígitos . número secuencial)
 * Ejemplos: 26.11, 26.12, 27.1
 *
 * Reglas:
 *   1. Cada versión es un release completo: planificado, implementado,
 *      testeado en Docker y verificado en producción.
 *   2. No se crean sub-versiones (X.Y.Z). Si hay un bug post-deploy,
 *      se abre una nueva versión (X.Y+1) con su propio ciclo.
 *   3. El bump se hace SOLO después de pasar todos los tests.
 *
 * Flujo: Plan → Implementar → Testear (Docker) → Deploy (bump+push+scp DB) → Verificar (prod)
 */

$versionLogs = [
    '26.12' => [
        'date' => '2026-04-17',
        'changes' => [
            'NUEVO MÓDULO: Personas — registro público de participantes (/personas/).',
            'Backend SQLite: tablas personas, roles, persona_roles con CRUD completo.',
            'Siembra automática: personajes del guion → personas placeholder (Feature 4).',
            'Gestión Director: editar, eliminar, toggle enabled/disabled por persona.',
            'Visibilidad pública: filtro enabled=1, badge ⚠️ para datos incompletos.',
            'Integración: link "Personas" en audios/index.php reemplaza a "Personajes".',
        ]
    ],
    '26.11' => [
        'date' => '2026-04-17',
        'changes' => [
            'Fade-out per-track: switch configurable por el Director, persistido en SQLite (track_config).',
            'Audio preload: precarga del siguiente track con <link rel="prefetch"> para transición fluida.',
            'Selector de escena: combo/select por grupo (0XX, 1XX, 2XX, 3XX) en el reproductor.',
            'Entorno de test Docker con réplica fiel de producción (DB incluida).',
            'Reorganización del historial de versiones: consolidación de sub-releases.',
        ]
    ],
    '26.10' => [
        'date' => '2026-04-11',
        'changes' => [
            'PLAN 10: Unificación de datos con SQLite como SSOT dinámico.',
            'Tablas scene_groups y scenes en db.php con siembra automática.',
            'Visor de videos y selectores construidos 100% desde SQLite.',
            'Fallback para Termux/Android: SQLite → scenes_backup.json → datos hardcodeados.',
            'Video wrapper refactorizado: welcome-message como overlay (fix YouTube IFrame API).',
            'Reproductor YouTube revertido al flujo original estable.',
            'SSOT: eliminado fallback hardcodeado, respaldo es SQLite → JSON.',
            'Tool: generate_json_seed.php para regenerar backup JSON.',
        ]
    ],
    '26.9' => [
        'date' => '2026-04-10',
        'changes' => [
            'NUEVO MÓDULO: Visor de Videos de YouTube integrado (/videos/).',
            'Configuración centralizada de escenas y tiempos (youtube_config.js).',
            'Reproductor sincronizado con salto automático por escena.',
            'Fix: carga de video predeterminada al ingresar sin URL limpia.',
        ]
    ],
    '26.8' => [
        'date' => '2026-04-05',
        'changes' => [
            'ARQUITECTURA: Backend migrado de JSON+git a SQLite (ACID, sin dependencia de git).',
            'Datos inline: PHP inyecta cues de SQLite directo en el HTML (sin fetch).',
            'CRUD completo de cues: duplicar, eliminar, editar con doble-click.',
            'Director toolbar: modo marcaje 🎯, nudge ±0.1s, contador en vivo, timestamps HH:MM:SS.',
            'Karaoke rediseñado: burbujas agrupadas por personaje con resaltado interno.',
            'Sistema de perfiles: Público (actores) y Director (edición avanzada).',
            'PERSONAJES: página de casting con postulación abierta y toggle por personaje.',
            'Edición inline en Personajes: nombre, synopsis, datos de actor.',
            'Modal inline (vcbyModal): reemplazo de alert/prompt nativos.',
            'Commit remoto desde interfaz web + auto-sync JSON tras edición.',
            'Inserción de burbujas con selector de personaje y acotaciones escénicas (P90).',
            'Compatibilidad Android/Termux: fallback automático, start_termux.sh.',
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
        'date' => '2025-03-30',
        'changes' => [
            'Rediseño completo del reproductor de audio.',
            'Navegación inferior unificada (Volver/Anterior/Siguiente).',
            'Efectos visuales: onda en botones, pulsante WhatsApp, feedback visual, transiciones.',
            'Compatibilidad con modo oscuro, aceleración hardware, correcciones Firefox/Safari.',
        ]
    ],
    '25.4' => [
        'date' => '2025-03-24',
        'changes' => [
            'Modifica La Negación, agrega música al inicio.',
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
            'Genera Video Viral 2025.',
            'Upload Todo Unido 1º y 3º parte.',
            'Actualiza nueva key.',
        ],
    ],
    '25.2' => [
        'date' => '2025-03-16',
        'changes' => [
            'Actualiza valores de Títulos a Viacrucis 2025.',
        ],
    ],
    '25.1' => [
        'date' => '2025-03-10',
        'changes' => [
            'Migración al nuevo formato de versionado (de 5.6 a 25.1).',
        ],
    ],
    '5.6' => [
        'date' => '2025-03-25',
        'changes' => [
            'Reorganización de carpetas: separación de configuración Docker y página web.',
            'Optimización Docker para usar última versión de php:samba.',
        ],
    ],
    '5.5' => [
        'date' => '2024-12-22',
        'changes' => [
            'Reorganización de archivos de src/ a raíz del sitio.',
            'Herramienta "Ver Versiones" (tools/verVersiones.php).',
            'Botones Anterior/Siguiente en play.php.',
            'Headers y footers separados (include/header.php, include/footer.php).',
            'Creación del historial de versiones (versionLogs.php).',
            'Seguridad: kerberos.php + index.php por directorio.',
        ],
    ],
    '5.4' => [
        'date' => '2024-12-15',
        'changes' => [
            'Versión inicial del proyecto.',
            'Funcionalidad básica para listar y reproducir archivos .mp3.',
            'Ocultación de direcciones físicas mediante serve.php.',
            'Diseño moderno con JavaScript interactivo.',
        ],
    ],
];

// Ordena las claves del array en orden descendente
uksort($versionLogs, 'version_compare');
$latestVersion = array_key_last($versionLogs);
$latestDetails = $versionLogs[$latestVersion];
?>
