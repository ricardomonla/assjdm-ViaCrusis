# Plan 08: Pulido Fino y Enriquecimiento Dramático (v26.7.x)

> **Estado**: ⏳ En progreso
> **Fecha**: 2026-04-02
> **Última actualización**: 2026-04-02 22:03

---

## Progreso General

```text
█████████░░░░░░░░░░░░░░░░░░░░░  30% — EN PROGRESO
```

| Fase | Descripción | Estado |
|:---|:---|:---|
| 1 | Sincronía Cronológica de Personajes | ⏳ (80%) |
| 2 | Tunning de Precision en Tiempos de Subtítulos | 📋 |
| 3 | Acotaciones Escénicas y Descriptivas Intermedias | 📋 |
| 4 | Sistema de Perfiles (Público / Director) | ⏳ (4.1-4.3 ✅) |

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
- [ ] Navegar los audios observando la interfaz y ajustando milimétricamente las décimas de segundo (o re-ajustando la duración total) requeridas para lograr el feeling natural.
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
- [ ] **4.4 Acotaciones Escénicas**: Insertar filas P00 entre líneas, persistencia vía commit.

> **Perfil Público (Actores)**: Solo escuchar audio + ver subtítulos karaoke. Sin edición, sin WhatsApp, sin IDs técnicos.
> **Perfil Director**: Todo lo anterior + edición, notas, ajuste de tiempos, acotaciones, commit local.
> **Documento de diseño completo**: Ver propuesta detallada en la conversación de IA (2026-04-02).

---
**Nota para la IA y Operador H.I.T.L:** 
Cualquier avance en este plan se registrará actualizando estos `checkboxes` a marcado (`[x]`). Al cerrarse una iteración de pista/escena se comiteará la versión subsecuente (Ej: `v26.7.2`, `v26.7.3`, etc.).

### Bitácora de Progreso
| Versión | Fecha | Descripción |
|:---|:---|:---|
| v26.8.2 | 2026-04-02 | Serie 200 completa (201-207 v4.0). Script de reordenamiento cronológico ejecutado en 24 pistas. P26 MUJERES JERUSALEN agregado. |
| v26.8.3 | 2026-04-02 | Fase 4.1 completada: `jss/perfiles.js`, modal dual en `footer.php`, CSS director-mode, karaoke.js con IDP/data-attrs, compilador `compilar_json_v4.py`, JSON recompilado con `idp` (24 tracks, 601 cues). |
