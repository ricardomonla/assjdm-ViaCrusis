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

- [x] 1. (Antigravity AI): Generar documento `v0.1.md` para cada pista, que incluye una tabla "Personajes" completa con su `IP` y `System_Synopsis`,  dejando una tabla vacía llamada "Subtítulos" con cabecera `MARCA | IP | SUBTITULO`.
- [x] 2. (Ruby + APIs Groq): Ejecutar `genera_subs_v1.0.rb [ID]`. Este script lee `v0.1.md`, sube el audio a Whisper-Large-v3, obtiene tiempos exactos, y se los pasa a LLaMA 3.3 70B para que complete el markdown devolviendo el archivo lleno como `v1.0.md`.
- [ ] 3. (Director / Humano): Revisar y auditar la tabla en `v1.0.md`, ajustar `IP` incorrectos deducidos por la máquina, corregir tildes, comas o palabras, y finalmente grabar el progreso con el nombre `v1.1.md` (Ready for prod).
- [ ] 4. (Compilador Automático): Script final que trague todos los `.md` aprobados (`v1.1`) y los parsee inyectando los arrays definitivos en `guion_completo.json`.
- [ ] 5. Procesar los 34 audios y completar el repositorio con calidad humana del 100%.

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

