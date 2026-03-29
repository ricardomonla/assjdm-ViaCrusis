# Plan 03: Transcripción de Audios → Guion Completo

> **Estado**: ✅ Completado
> **Fecha**: 2026-03-29

---

## Issues / Objetivos

| # | Objetivo | Detalle |
|:---|:---|:---|
| 1 | **Transcribir los 34 MP3s** | Extraer texto hablado de cada audio |
| 2 | **Identificar personajes** | Quién habla en cada diálogo (Jesús, Pilatos, Narrador, etc.) |
| 3 | **Organizar en escenas** | Armar guion estructurado por escena |
| 4 | **Guion final** | Documento completo con escenas, personajes y diálogos |
| 5 | **Desplegables por grupo** | Agrupar audios en acordeones: Desfile, La Pasión, Calvario, Crucifixión |

## Progreso General

```
██████████████████████████████ 100% — COMPLETADO
```

| Fase | Descripción | Estado |
|:---|:---|:---|
| 1 | Instalar herramienta de transcripción (Usando Groq API) | ✅ |
| 2 | Agrupar audios en desplegables (mejora visual) | ✅ |
| 3 | Transcribir los 34 audios | ✅ |
| 4 | Identificar personajes y escenas | ✅ |
| 5 | Generar guion final | ✅ |

---

## Inventario de Audios

34 archivos MP3 en `media/`, organizados en 4 actos:

### Desfile — 0XX (Muestra de actores)
| # | Archivo | Escena |
|:---|:---|:---|
| 000 | Desfile Reflexión Final | Reflexión introductoria |
| 001 | Desfile Pueblo | Entrada del pueblo |
| 002 | Desfile Apóstoles | Entrada de apóstoles |
| 003 | Desfile Pilatos y Soldados | Entrada de Pilatos |
| 004 | Desfile Curas | Entrada de los sacerdotes |
| 005 | Desfile Herodes y Bailarinas | Entrada de Herodes |
| 006 | Desfile Diablo Suspenso | Entrada del diablo |

### La Pasión — 1XX
| # | Archivo | Escena |
|:---|:---|:---|
| 101 | La entrada de Jesús en Jerusalén | |
| 102 | El Trato de Judas y Caifás | |
| 103 | La Última Cena | |
| 104 | La oración en el Monte de los Olivos | |
| 105 | La Entrega | |
| 106 | Las negaciones de Pedro | |
| 107 | El juicio en el Sanedrín | |
| 108 | La Culpa de Judas | |
| 109 | El lavado de las manos de Pilatos | |
| 110 | Jesús Ante Herodes | |
| 111 | 1ºE Jesús es condenado a muerte | |

### Calvario — 2XX
| # | Archivo | Escena |
|:---|:---|:---|
| 201 | 2ºE Jesús carga con la cruz | |
| 202 | 3ºE Jesús cae por primera vez | |
| 203 | 4ºE Jesús se encuentra con su madre | |
| 204 | 5ºE Simón de Cirene ayuda | |
| 205 | 6ºE La Verónica | |
| 206 | 7ºE Jesús cae por segunda vez | |
| 207 | 8ºE Jesús consuela a las mujeres | |

### Crucifixión — 3XX
| # | Archivo | Escena |
|:---|:---|:---|
| 301 | 9ºE Jesús cae por tercera vez | |
| 302 | 10ºE Jesús es despojado | |
| 303 | 11ºE Jesús es clavado en la cruz | |
| 304 | 12ºE Jesús muere en la cruz | |
| 305 | 13ºE Jesús bajado de la cruz | |
| 306 | Jesús es llevado por las calles | |

### La Resurrección — 4XX
| # | Archivo | Escena |
|:---|:---|:---|
| 401 | 14ºE Jesús en el sepulcro | |
| 402 | El Sepulcro | |
| 403 | La Resurrección | |

---

## Fase 1: Instalar herramienta de transcripción

```
██████████████████████████████ 100%
```

- [x] Verificar si Whisper (OpenAI) está disponible localmente
- [x] Instalar `whisper` o usar API externa (Groq whisper-large-v3)
- [x] Test con un audio corto (ej: 004 Desfile Curas, 1 MB)

**Herramienta elegida**: **Groq API** (`whisper-large-v3`) — modelo de transcripción de voz que:
- Funciona vía API remota (No congela el CPU del entorno de Antigen).
- Es ultra-rápida.
- Identifica timestamps exactos.

**Notas/Hallazgos**:
- El procesamiento local (`faster-whisper`) conllevaba interbloqueos graves debido al consumo en entorno virtual. Se migró definitivamente el proyecto a usar la API externa de Groq mediante el script `tools/transcribir_groq.py`.

---

## Fase 2: Transcribir los 34 audios

```
██████████████████████████████ 100%
```

- [x] Transcribir Desfile — 0XX (7 audios)
- [x] Transcribir La Pasión — 1XX (11 audios)
- [x] Transcribir Calvario — 2XX (7 audios)
- [x] Transcribir Crucifixión — 3XX (9 audios)
- [x] Guardar transcripciones en `docs/guion/transcripciones/NNN_nombre.txt`

**Output esperado por archivo**:
```
[00:00] Texto crudo dicho por una voz sin etiquetar...
```

**Notas/Hallazgos**:
- Se toparon límites de Rate Limit (HTTP 429) tras ~30 archivos. 
- Se solventó usando pausas automáticas, tras lo cual se lograron las 34 transcripciones satisfactoriamente con 0 errores en `docs/guion/transcripciones/`.

---

## Fase 3: Identificar personajes y escenas

```
██████████████████████████████ 100%
```

- [x] Revisar transcripciones y etiquetar personajes
- [x] Crear lista de personajes con sus apariciones
- [x] Verificar continuidad de escenas entre audios
- [x] Generar `docs/guion/personajes.md`

**Personajes esperados**:
Narrador, Jesús, María, Pedro, Judas, Pilatos, Herodes, Caifás, Simón de Cirene, Verónica...

**Notas/Hallazgos**:
- Se generaron con éxito los 34 txt etiquetados en `docs/guion/etiquetados/`.
- Se compiló con un script unificado las apariciones de cada personaje, listando exitosamente 18 entidades en la tabla de personajes final.

**Herramienta de Automatización**: Se utilizará `tools/etiquetar_personajes.py` el cual delega la petición a la IA usando el gestor transversal de APIs seguro del repositorio (Llama3-70b con rotación automática).

---

## Fase 4: Generar guion final

```
██████████████████████████████ 100%
```

- [x] Compilar todas las transcripciones en orden
- [x] Formatear como guion teatral (personaje: diálogo)
- [x] Agregar acotaciones de escena (música, efectos, acciones)
- [x] Generar `docs/guion/guion-viacrusis-2026.md`
- [x] Revisión final

**Formato del guion**:
```markdown
## ACTO 1 — PASIÓN

### Escena 1: La entrada de Jesús en Jerusalén

**[Música de entrada]**

NARRADOR: (voz solemne)
Y así entró Jesús en la ciudad de Jerusalén...

PUEBLO: (coro)
¡Hosanna! ¡Bendito el que viene en nombre del Señor!

JESÚS:
La paz sea con ustedes...
```

---

## Resumen de archivos a crear

| Archivo | Descripción |
|:---|:---:|
| `docs/guion/transcripciones/*.txt` | 34 transcripciones individuales (crudo) |
| `docs/guion/etiquetados/*.txt` | 34 transcripciones (etiquetado IA) |
| `docs/guion/personajes.md` | Lista de personajes y apariciones |
| `docs/guion/guion-viacrusis-2026.md` | Guion final completo |

## Requisitos

- Python 3.x con `whisper` o `faster-whisper`
- Modelo `large-v3` (~3 GB) para mejor precisión en español
- ~120 MB de audio total a procesar
- Estimado: ~30-60 min de procesamiento total

## Rollback

No aplica — este plan no modifica el sitio, solo genera documentación nueva.
