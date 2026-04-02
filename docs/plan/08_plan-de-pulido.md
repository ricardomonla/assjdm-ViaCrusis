# Plan 08: Pulido Fino y Enriquecimiento Dramático (v26.7.x)

> **Estado**: ⏳ En progreso
> **Fecha**: 2026-04-02

---

## Progreso General

```text
██░░░░░░░░░░░░░░░░░░░░░░░░░░░░  10% — EN PROGRESO
```

| Fase | Descripción | Estado |
|:---|:---|:---|
| 1 | Sincronía Cronológica de Personajes | ⏳ |
| 2 | Tunning de Precision en Tiempos de Subtítulos | 📋 |
| 3 | Acotaciones Escénicas y Descriptivas Intermedias | 📋 |

---

## Objetivo

Esta etapa de segundo nivel de iteración (pulido fino) está destinada a retocar y rectificar las inexactitudes finas del H.I.T.L inicial y sumar valor descriptivo escénico al guion, garantizando que el JSON maestro quede inmaculado para la reproducción.

## 📋 Lista de Tareas (Roadmap)

## Fase 1: Sincronía Cronológica de Personajes
El catálogo de personajes actual (`00_Personajes.md` e `incs/personajes.php`) fue creciendo orgánicamente a medida que descubríamos personajes en la etapa de transcripción cruda. Ahora es necesario:
- [ ] Reordenar los IDs `PXX` de los personajes de modo que sean estrictamente acordes a su primera línea cronológica en la obra.
- [ ] Actualizar la asignación del `IDP` correspondiente para mantener intacta la capa CSS (colores).
- [ ] Opcional: Revisar y ajustar nombres teatrales/sinopsis.

## Fase 2: Tunning de Precision en Tiempos de Subtítulos
El emparejamiento de los tiempos iniciales (`startTime`) en general es robusto, pero el auto-karaoke exige precisión perfecta para el corte interlineal.
- [ ] Navegar los audios observando la interfaz y ajustando milimétricamente las décimas de segundo (o re-ajustando la duración total) requeridas para lograr el feeling natural.
- [ ] Auditar desvíos menores o "silencios muy largos".

## Fase 3: Acotaciones Escénicas y Descriptivas Intermedias
Inyectar un capa puramente teatral que explique qué ocurre en momentos instrumentales/gestuales para ayudar al director/actor:
- [ ] Incluir filas de texto especial (idealmente usando al personaje de ambiente general o re-mapeando la marca `P00` a una directriz NARRATIVA).
- [ ] Ejemplos: "*(Se escuchan sonidos de latigazos correspondientes a la flagelación)*", "*(La música se vuelve tenue mientras Jesús cae de rodillas)*", entre otros acordes cronológicamente a cada pista del JSON.

---
**Nota para la IA y Operador H.I.T.L:** 
Cualquier avance en este plan se registrará actualizando estos `checkboxes` a marcado (`[x]`). Al cerrarse una iteración de pista/escena se comiteará la versión subsecuente (Ej: `v26.7.2`, `v26.7.3`, etc.).
