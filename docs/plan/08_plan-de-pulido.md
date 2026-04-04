# Plan 08: Pulido Fino y Enriquecimiento Dramático (v26.8.x)

> **Estado**: ⏳ En progreso
> **Fecha**: 2026-04-02
> **Ultima actualizacion**: 2026-04-04 18:55

---

## Progreso General

```text
██████████████████████████░░░░  85% - EN PROGRESO
```

| Fase | Descripción | Estado |
|:---|:---|:---|
| 1 | Sincronía Cronológica de Personajes | ⏳ (80%) |
| 2 | Tunning de Tiempos de Subtítulos | ⏳ (herramientas ✅, auditoría pendiente) |
| 3 | ~~Acotaciones Escénicas~~ → absorbida por Fase 7 | ✅ |
| 4 | Sistema de Perfiles (Público / Director) | ✅ |
| 5 | Burbujas Agrupadas (Karaoke UX) | ✅ |
| 6 | Arquitectura de Datos (SQLite Backend) | ✅ |
| 7 | Inserción de Burbujas (Cues por Personaje) | ✅ |
| 8 | CRUD Completo (Duplicar/Eliminar/Editar) | ✅ |
| 9 | Personajes y Casting | ✅ |

---

## Objetivo

Retocar y rectificar las inexactitudes finas del H.I.T.L inicial y sumar valor descriptivo escénico al guion, garantizando que la base de datos quede inmaculada para la reproducción karaoke.

## Fases Activas (pendientes)

### Fase 1: Sincronía Cronológica de Personajes
- [x] Reordenar IDs `PXX` por orden cronológico. *(v26.8.2)*
- [x] Actualizar IDP en 24 archivos v4.0.md. *(Commit f28f2f4)*
- [x] Agregar P26 (MUJERES JERUSALEN).
- [ ] Crear `v4.0.md` para tracks pendientes: **301, 302, 306** (3 de 27).
- [ ] Recompilar JSON integrando los 3 tracks faltantes.
- [ ] Sincronizar `docs/personajes.md` y `guion/Guion-vcby.md`.

### Fase 2: Tunning de Tiempos de Subtítulos
- [x] Modo Marcaje 🎯, Nudge ◂▸ ±0.1s, contador live. *(v26.8.7)*
- [ ] Auditoría: navegar todos los audios ajustando décimas de segundo.
- [ ] Detectar y corregir "silencios muy largos".

## Fases Completadas (resumen)

### Fase 9: Personajes y Casting ✅ *(v26.8.23)*
Página `/casting/` con listado de personajes desde SQLite. Postulación pública (nombre, apellido, teléfono). Director habilita/deshabilita personajes con toggle. Teléfonos privados (solo Director). Synopsis visible. Link en lista de audios.

### Fase 8: CRUD Completo ✅ *(v26.8.19 — v26.8.22)*
Duplicar burbuja/línea (+), eliminar (🗑), editar texto (doble-click), cambiar personaje (doble-click nombre). Pegado texto plano. Preservación de audio. Reindex con negativos temporales. AUTO-SYNC SQLite→JSON. Fallback Android/Termux.

### Fase 7: Inserción de Burbujas ✅ *(v26.8.18)*
Toggle ➕ en toolbar. Botones "+" entre burbujas. Modal `vcbyInsertCue` con dropdown P00-P99 + texto. Persistencia SQLite con reindexación automática.

### Fase 6: Arquitectura de Datos (SQLite Backend) ✅ *(v26.8.15 — v26.8.17)*
SQLite en `/var/www/vcby-data/vcby.db` (fuera del repo). CRUD: `data/db.php`. API: `api_cues.php`. Datos inline en HTML (PHP → SQLite → JS). Deploy-proof.

### Fase 5: Burbujas Agrupadas ✅ *(v26.8.4 — v26.8.8)*
Agrupación por personaje+IDP, resaltado interno progresivo, scroll suave al span activo, toolbar Director compacta, modales inline (`vcbyModal`).

### Fase 4: Sistema de Perfiles ✅ *(v26.8.3 — v26.8.4)*
Multi-perfil Público/Director, IDP técnicos, colores por personaje, edición in-place, panel de notas, WhatsApp Director-only, commit remoto.

---

### Bitácora de Progreso
| Versión | Fecha | Descripción |
|:---|:---|:---|
| v26.8.2 | 2026-04-02 | Fase 1: reordenamiento cronológico, P26 agregado. |
| v26.8.3 | 2026-04-02 | Fase 4.1: perfiles, compilador v4, JSON con IDP (24 tracks/601 cues). |
| v26.8.4 | 2026-04-03 | Fase 4 COMPLETADA: notas, edición in-place, colores por personaje. |
| v26.8.7 | 2026-04-03 | Fase 2: Stamp 🎯, Nudge ◂▸, contador live, applyTimeChange. |
| v26.8.8 | 2026-04-03 | Modal inline (vcbyModal), Director click sin auto-play. |
| v26.8.9 | 2026-04-03 | Director remoto: commit+push a GitHub desde la web. |
| v26.8.15 | 2026-04-03 | **ARQUITECTURA**: SQLite backend, API REST, datos inmunes a deploys. |
| v26.8.17 | 2026-04-04 | Datos inline: SQLite → PHP → HTML → JS. VPN no actualiza caché. |
| v26.8.18 | 2026-04-04 | **INSERTAR BURBUJAS**: Toggle ➕, selector P00-P99, persistencia SQLite. |
| v26.8.19 | 2026-04-04 | **DUPLICADOR SERIAL**: + grande/chico, batch insert, reindex infalible. |
| v26.8.21 | 2026-04-04 | **CRUD COMPLETO**: editar, cambiar personaje, paste plano, fix índices. |
| v26.8.22 | 2026-04-04 | Android fallback, auto-sync JSON, export tool. |
| v26.8.23 | 2026-04-04 | **PERSONAJES**: página casting, toggle Director, synopsis, link audios. |
