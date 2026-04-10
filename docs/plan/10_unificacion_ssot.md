# Plan 10: Unificación de Datos en Base de Datos (SSOT Dinámico)

> **Estado**: ✅ Completado
> **Módulos Críticos Afectados**: Base de Datos (SQLite), Audios, Videos, Endpoints API.

---

## Progreso General

```text
██████████████████████████████ 100% — FASE 1 (Esquema SQLite)
██████████████████████████████ 100% — FASE 2 (Migración de Datos)
██████████████████████████████ 100% — FASE 3 (Refactorización Backend)
██████████████████████████████ 100% — FASE 4 (Refactorización Frontend y Limpieza)
```

| Fase | Descripción | Estado |
|:---|:---|:---|
| 1 | Extensión del Esquema (`data/db.php`) | ✅ Completado |
| 2 | Script Híbrido de Migración (`tools/`) | ✅ Completado |
| 3 | Refactor del Backend (PHP + API) | ✅ Completado |
| 4 | Refactor Adaptativo del Frontend y Limpieza | ✅ Completado |

---

## El Problema Actual

El flujo de vida de una **Escena** en la página está fragmentado y es estático (requiere tocar código para editarse). Diferentes secciones mantienen su propia "verdad":

1. **La Lista de Audios y Play** (`incs/elementos.php`): Hardcodea la estructura de grupos comerciales y tracks.
2. **Los Videos de YouTube y sus Marcas** (`jss/youtube_config.js`): Hardcodea `videoId` y `timestamp` en JavaScript.
3. **El Visor de Videos** (`videos/index.php`): Hardcodea en HTML los grupos.
4. **Los Subtítulos**: Operan dinámicamente en SQLite (`data/db.php`).

Si el Director cambia el nombre de "La Última Cena" o reasigna un tiempo de YouTube, o se sube una nueva versión de audio, hay que subir commits de código al repo en vez de guardar directamente vía API.

---

## La Solución (SSOT basado en SQLite)

Para lograr un Punto de Verdad Absoluto 100% dinámico, **trasladaremos TODA la estructura de Audios, Grupos y Marcas de Video a la base de datos (SQLite)** existente en `data/db.php`. Esto permitirá construir endpoints (CRUD) para que en un futuro los tiempos y nombres sean editables desde una UI administrativa sin tocar código.

### Fase 1: Extensión del Esquema (`data/db.php`)
Agregaremos nuevas tablas a nuestra base de datos para contener la jerarquía del ViaCrucis:
1. Tabla `groups`: 
   - `id` (ej. '1', '2' o '1XX')
   - `name` (ej. 'La Pasión')
   - `icon` (ej. '⛪')
   - `order_index`
2. Tabla `scenes`:
   - `id` (PK, ej. '101')
   - `group_id` (FK -> groups.id)
   - `order_index` (Numérico)
   - `title` (ej. 'La entrada de Jesús en Jerusalén')
   - `display_name` 
   - `version` (ej. '2503')
   - `audio_filename` (ej. '101_v2503.mp3')
   - `youtube_video_id` (ej. 'ktDtijJMfbo')
   - `youtube_timestamp` (ej. 289)

### Fase 2: Script Híbrido de Migración (`tools/`)
Crearemos un script de migración temporal PHP. El mismo leerá el array histórico actual de `incs/elementos.php` y los JSON integrados de `jss/youtube_config.js` para sembrar (`seed`) de una sola pasada todas las filas en las tablas `groups` y `scenes` de SQLite.

### Fase 3: Refactor del Backend (PHP + API)
- En `data/db.php`: Agregaremos funciones estilo `getAllScenes()`, `getScenesGrouped()`, y `updateScene()`.
- En `incs/functions.php`: Se redirigirá el flujo de `getAudioGroups()` y `getAudioFiles()` para que lean dinámicamente desde `data/db.php`.
- Opcional: Creación de endpoints (ej. `api/scenes.php`) para interactuar vía AJAX/Fetch si es necesario.

### Fase 4: Refactor Adaptativo del Frontend y Limpieza
- **Videos**: Se reconstruirá `videos/index.php` para que itere sus `<select>` consultando `getScenesGrouped()`. La configuración unificada se pasará como variable global `window.VCBY_SCENES = <?php echo json_encode(...) ?>;` permitiendo borrar totalmente `jss/youtube_config.js`.
- **Audios**: Ya que usan la misma cascada de `incs/functions.php`, automáticamente listarán la data generada desde SQLite.
- **Limpieza**: Una vez todo configurado, se extirpará `getMediaGroupsStructure()` de `elementos.php` y se borrará `youtube_config.js`.

---

## Procedimiento (Siguientes Pasos)

1. Abrir `data/db.php` y crear las sentencias SQL en `ensureSchema()`.
2. Crear un archivo temporal de migración cruzada (audios + videos).
3. Modificar `data/db.php` para integrar los métodos de lectura centralizados.
4. Conectar estas funciones PHP con `incs/functions.php`.
5. Transformar y limpiar el frontend.
