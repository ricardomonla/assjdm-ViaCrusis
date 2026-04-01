# Plan 06: Implementación de Subtítulos Sincronizados (Estilo Karaoke)

> **Estado**: 📋 Planificado
> **Fecha**: 2026-04-01
> **Servidor**: srvv-nginx-rm

---

## Progreso General

```text
░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░   0% — PLANIFICADO
```

| Fase | Descripción | Estado |
|:---|:---|:---|
| 1 | Arquitectura y UI Base | ✅ |
| 2 | Motor JavaScript y Sincronización Continua | ✅ |
| 3 | Paleta Semántica y Tiempos Globales | ✅ |
| 4 | Extracción Base de Transcripciones | 📋 |
| 5 | Procesamiento IA Transcripción Sincronizada (Groq) | 📋 |

---

## Objetivo

Transformar la experiencia de reproducción de audios del ViaCrucis (actualmente estática) en una experiencia dinámica de "Karaoke". El usuario podrá leer el guion o los diálogos sincronizados en tiempo real a medida que el audio avanza, resaltando la línea o palabra actual, con soporte para tocar un diálogo y saltar a ese instante del audio.

---

## Fase 1, 2 y 3: Implementación Arquitectónica (COMPLETADAS)

```text
██████████████████████████████ 100%
```

- [x] Generación del formato escalable (`guion_completo.json`).
- [x] Motor interactivo `karaoke.js` (scroll dinámico, continuos previews de +/- 1 diálogo).
- [x] Interacción Táctil (doble tap play/pause, saltos a tiempo exacto).
- [x] Diseño estético teatral (alineación personajes, márgenes).
- [x] Cálculo Matemático de _Tiempo Global_ iterando `ffprobe` sobre duraciones.
- [x] Diferenciación Tonal Semántica (Pasado Azul, Inactivo Blanco, Próximo Naranja).

---

## Fase 4: Transcripción Cruda de Audios con Groq (EN PROCESO)

```text
████░░░░░░░░░░░░░░░░░░░░░░░░░░  10%
```

- [x] (A) Procesar el primer audio (`101_v2503.mp3`) utilizando un script conectado a **Groq** (`whisper-large-v3`).
- [x] (B) Extraer el texto crudo en segmentos JSON indicando exactamente su parámetro `start` y `end`.
- [ ] Extraer iterativamente el raw json de los 33 audios que restan a sus archivos `*_raw.json`.

---

## Fase 5: Flujo H.I.T.L. (Human In The Loop) con Mapeador Semántico MD

```text
████░░░░░░░░░░░░░░░░░░░░░░░░░░  10%
```

- [x] 1. Crear el borrador `v0.1.md` de cada pista. Incluirá **Tabla Personajes** con ID (`P01...`) y **Tabla Subtítulos** (Tiempos + Texto extraído) con la celda `IDPERSONAJE` vacía. (Ej: `101_v0.1.md`).
- [ ] 2. Pasar a Llama 3.3 (Groq) este borrador + Guión Original para que deduzca por contexto y relle las celdas vacías (`101_v1.0.md`).
- [ ] 3. **Intervención Humana Diaria**: El director revisa `101_v1.0.md`, corrige ID asignados erróneos (e.g. `P05` en vez de `P03`), elimina transcripciones fallidas o edita los subtítulos. Guarda como `101_v1.1.md`.
- [ ] 4. Escribir script compilador definitivo que trague el `.md` final aprobado (`v1.1`) y lo empaquete nativamente en el `guion_completo.json`.

---

## Resumen de archivos esperados a modificar/crear

| Archivo | Ubicación | Estado |
|:---|:---|:---|
| `docs/plan/06_karaoke-subtitulos.md` | Raíz | ✅ |
| `audios/subs/*.vtt` | Backend | 📋 |
| `audios/play.php` | Backend HTML | 📋 |
| `css/style.css` | Frontend CSS | 📋 |
| `jss/karaoke.js` | Frontend JS | 📋 |
| `serve.php` (si aplica) | Backend PHP | 📋 |

