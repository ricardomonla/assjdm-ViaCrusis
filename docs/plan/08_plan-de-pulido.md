# Plan 08: Pulido Fino y Enriquecimiento Dramático (v26.7.x)

> **Estado**: ⏳ En progreso
> **Fecha**: 2026-04-02
> **Ultima actualizacion**: 2026-04-04 01:00

---

## Progreso General

```text
█████████████████████░░░░░░░░░  65% - EN PROGRESO
```

| Fase | Descripción | Estado |
|:---|:---|:---|
| 1 | Sincronía Cronológica de Personajes | ⏳ (80%) |
| 2 | Tunning de Precision en Tiempos de Subtítulos | ⏳ (herramientas ✅, auditoría pendiente) |
| 3 | Acotaciones Escénicas y Descriptivas Intermedias | 📋 |
| 4 | Sistema de Perfiles (Publico / Director) | ✅ |
| 5 | Burbujas Agrupadas por Personaje (Karaoke UX) | ✅ |
| 6 | Arquitectura de Datos (SQLite Backend) | ✅ |

---

## Objetivo

Esta etapa de segundo nivel de iteración (pulido fino) está destinada a retocar y rectificar las inexactitudes finas del H.I.T.L inicial y sumar valor descriptivo escénico al guion, garantizando que el JSON maestro quede inmaculado para la reproducción.

## 📋 Lista de Tareas (Roadmap)

## Fase 1: Sincronía Cronológica de Personajes
El catálogo de personajes actual (`audios/subs/00_Personajes.md`) fue creciendo orgánicamente a medida que descubríamos personajes en la etapa de transcripción cruda. Ahora es necesario:
- [x] Reordenar los IDs `PXX` de los personajes de modo que sean estrictamente acordes a su primera línea cronológica en la obra. *(Script `tools/reordenar_personajes.rb` ejecutado — v26.8.2)*
- [x] Actualizar la asignación del `IDP` correspondiente en los 24 archivos v4.0.md existentes. *(Commit f28f2f4)*
- [x] Agregar personaje P26 (MUJERES JERUSALEN) al catálogo.
- [ ] Crear `v4.0.md` para tracks pendientes: **301, 302, 306** (3 de 27).
- [ ] Recompilar `guion_completo.json` integrando los 3 tracks faltantes con IDP actualizados.
- [ ] Sincronizar `docs/personajes.md` y `guion/Guion-vcby.md` con el catálogo actual.

## Fase 2: Tunning de Precision en Tiempos de Subtítulos
El emparejamiento de los tiempos iniciales (`startTime`) en general es robusto, pero el auto-karaoke exige precisión perfecta para el corte interlineal.
- [x] Implementar Modo Marcaje (🎯 Stamp): tap en línea durante reproducción fija startTime al instante actual del audio.
- [x] Implementar Nudge ◂/▸: ajuste ±0.1s por frase sin abrir editor.
- [x] Contador en vivo del tiempo de reproducción en la toolbar Director.
- [ ] Navegar los audios observando la interfaz y ajustando milimétricamente las décimas de segundo requeridas para lograr el feeling natural.
- [ ] Auditar desvíos menores o "silencios muy largos".

## Fase 3: Acotaciones Escénicas y Descriptivas Intermedias
Inyectar un capa puramente teatral que explique qué ocurre en momentos instrumentales/gestuales para ayudar al director/actor:
- [ ] Incluir filas de texto especial (idealmente usando al personaje de ambiente general o re-mapeando la marca `P00` a una directriz NARRATIVA).
- [ ] Ejemplos: "*(Se escuchan sonidos de latigazos correspondientes a la flagelación)*", "*(La música se vuelve tenue mientras Jesús cae de rodillas)*", entre otros acordes cronológicamente a cada pista del JSON.

## Fase 4: Sistema de Perfiles (Público / Director)
La interfaz actual tiene un solo punto de acceso sin diferenciación de roles. Se requiere un sistema dual:
- [x] **4.1 Infraestructura**: Extender modal de login a multi-perfil (Público/Director), crear `jss/perfiles.js`, agregar clases CSS `.director-mode`/`.director-only`. ✅ *Compilador v4 con IDP, JSON recompilado (24 tracks/601 cues)*.
- [x] **4.2 Enriquecimiento Visual**: IDP técnicos visibles para Director, colores por personaje, panel de notas por track. ✅ *Panel colapsable con notas localStorage + WhatsApp Director-only*.
- [x] **4.3 Edición In-Place**: Doble-click en subtítulos para editar, guardar cambios vía `tools/commit_cambios.sh` (git commit local). ✅ *save_changes.php + barra flotante de commit*.
- [x] **4.4 Acotaciones Escenicas**: Insertar filas P00 entre lineas, persistencia via commit. ✅ *Boton + entre cues, save_changes.php con accion _insert*.

> **Perfil Público (Actores)**: Solo escuchar audio + ver subtítulos karaoke. Sin edición, sin WhatsApp, sin IDs técnicos.
> **Perfil Director**: Todo lo anterior + edición, notas, ajuste de tiempos, acotaciones, commit local.
> **Documento de diseño completo**: Ver propuesta detallada en la conversación de IA (2026-04-02).

## Fase 5: Burbujas Agrupadas por Personaje (Karaoke UX)
La interfaz actual muestra un bloque separado por cada marca de tiempo, resultando visualmente pesada. Se propone agrupar las lineas consecutivas del mismo personaje en una sola burbuja, y avanzar el resaltado karaoke *dentro* de esa burbuja.
- [x] **5.1 Agrupacion**: Renderizar una sola burbuja por cada bloque consecutivo del mismo `idp`. Dentro, cada linea es un `<span>` con su propio `data-cue-index`. ✅
- [x] **5.2 Resaltado Interno**: Al avanzar el audio, el span activo se ilumina y los anteriores/posteriores se atenuan, sin cambiar de burbuja. ✅
- [x] **5.3 Scroll Suave**: El autoscroll se ajusta al span activo dentro de la burbuja, no a la burbuja entera. ✅
- [x] **5.4 Toolbar Director**: Barra compacta superior con toggles (Tiempos/Notas/WhatsApp). ✅
- [x] **5.5 Edicion de Tiempos**: Tags inline editables por frase, modelo offline-first con batch save al commitear. ✅

### Fase 6: Arquitectura de Datos (SQLite Backend) ✅
- [x] **6.1 SQLite DB**: Base de datos en `/var/www/vcby-data/vcby.db` (fuera del repo git). ACID, concurrencia segura.
- [x] **6.2 Capa de acceso**: `data/db.php` con funciones CRUD: `getCues()`, `updateCue()`, `insertCue()`, `getAllCues()`.
- [x] **6.3 API REST**: `audios/api_cues.php` sirve los cues como JSON desde SQLite (reemplaza lectura directa del JSON estático).
- [x] **6.4 save_changes.php**: Reescrito para usar SQLite. Sin dependencia de git/commit/push. Escritura instantánea.
- [x] **6.5 Migración**: `tools/migrate_to_sqlite.php` importa guion_completo.json → SQLite (24 tracks, 601 cues).
- [x] **6.6 Independencia de deploy**: Los datos viven fuera del repo. Git push/pull no afectan los datos del Director.

### Fase 7: Inserción de Burbujas (Cues por Personaje) ⏳
Permitir al Director agregar nuevas líneas de diálogo/acotaciones antes o después de cualquier cue existente:
- [x] **7.1 UI Insertar**: Toggle ➕ en toolbar Director. Botones "+" entre burbujas (y antes de la primera). Visible solo con toggle activo. ✅
- [x] **7.2 Selector de Personaje**: Modal `vcbyInsertCue` con dropdown P00-P99 + campo de texto. ✅
- [x] **7.3 Persistencia SQLite**: `save_changes.php` acepta character/idp, `insertCue()` reindexación automática. ✅
- [ ] **7.4 Recarga live**: Tras insertar, recargar datos inline y re-renderizar sin recarga completa de página.

---
**Nota para la IA y Operador H.I.T.L:** 
Cualquier avance en este plan se registrará actualizando estos `checkboxes` a marcado (`[x]`). Al cerrarse una iteración de pista/escena se comiteará la versión subsecuente (Ej: `v26.7.2`, `v26.7.3`, etc.).

### Bitácora de Progreso
| Versión | Fecha | Descripción |
|:---|:---|:---|
| v26.8.2 | 2026-04-02 | Serie 200 completa (201-207 v4.0). Script de reordenamiento cronológico ejecutado en 24 pistas. P26 MUJERES JERUSALEN agregado. |
| v26.8.3 | 2026-04-02 | Fase 4.1 completada: `jss/perfiles.js`, modal dual en `footer.php`, CSS director-mode, karaoke.js con IDP/data-attrs, compilador `compilar_json_v4.py`, JSON recompilado con `idp` (24 tracks, 601 cues). |
| v26.8.4 | 2026-04-03 | Fase 4 COMPLETADA: Panel notas, edicion in-place, commit local, acotaciones P00 insertables. Colores por personaje para ambos perfiles. |
| v26.8.7 | 2026-04-03 | Fase 2 herramientas: Modo Marcaje (🎯 stamp), Nudge ◂▸ ±0.1s, contador live en toolbar. applyTimeChange centralizado. |
| v26.8.8 | 2026-04-03 | Modal inline (vcbyModal): reemplazo total de alert/prompt nativos. Director click sin auto-play. |
| v26.8.9 | 2026-04-03 | Director remoto: commit+push a GitHub desde la web. Edición desde cualquier dispositivo. |
| v26.8.15 | 2026-04-03 | **ARQUITECTURA**: Backend migrado de JSON+git a SQLite. DB fuera del repo, datos inmunes a deploys. API REST (`api_cues.php`). |
| v26.8.17 | 2026-04-04 | Datos inline: PHP inyecta cues de SQLite directo en HTML. Fallback a JSON si SQLite no disponible. Nota: VPN no actualiza caché, usar dominio directo. |
| v26.8.18 | 2026-04-04 | **INSERTAR BURBUJAS**: Toggle ➕ en toolbar, selector de personaje P00-P99, botón + antes/después de cada grupo, persistencia SQLite. |
