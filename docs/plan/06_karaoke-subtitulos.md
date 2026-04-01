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

## Fase 4: Flujo H.I.T.L. (Human In The Loop) Groq a Markdown

```text
████░░░░░░░░░░░░░░░░░░░░░░░░░░  10%
```

- [x] 1. Crear script conversacional MVP (`genera_subs_v0.1.rb`) que envía el MP3 a Whisper y escupe **directamente** el MD con **Tabla Personajes** con ID (`P01...`) y **Tabla Subtítulos** (Tiempos + Texto extraído) con la celda `IDPERSONAJE` vacía. (Ej probado con: `101_v0.1.md`).
- [ ] 2. Pasar a Llama 3.3 (Groq) este MD `v0.1.md` + Guión Original para que deduzca por contexto y relle las celdas vacías (`101_v1.0.md`).
- [ ] 3. **Intervención Humana Diaria**: Revisar `101_v1.0.md`, corregir IDs erróneos (e.g. `P05` en vez de `P03`), afinar transcripciones o agregar acotaciones. Guardar como `101_v1.1.md`.
- [ ] 4. Escribir script compilador definitivo que trague los `.md` finales aprobados (`v1.1`) y los empaquete nativamente en el `guion_completo.json`.
- [ ] 5. Procesar el bloque entero de los 34 audios y completar el repositorio de subtítulos exactos.

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

