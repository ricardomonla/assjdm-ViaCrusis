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
| 1 | Generación de Transcripciones y Tiempos | 📋 |
| 2 | Integración del formato de datos (VTT/JSON) | 📋 |
| 3 | Maquetado UI del contendor Karaoke | 📋 |
| 4 | Motor JavaScript de sincronización y auto-scroll | 📋 |

---

## Objetivo

Transformar la experiencia de reproducción de audios del ViaCrucis (actualmente estática) en una experiencia dinámica de "Karaoke". El usuario podrá leer el guion o los diálogos sincronizados en tiempo real a medida que el audio avanza, resaltando la línea o palabra actual, con soporte para tocar un diálogo y saltar a ese instante del audio.

---

## Fase 1: Generación de Transcripciones y Tiempos

```text
░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░   0%
```

- [ ] Procesar los 34 audios `.mp3` utilizando un motor de IA (ej. **Whisper local** o la **API de Groq** que soporta Whisper-large-v3).
- [ ] Extraer las transcripciones con marcas de tiempo (timestamps) exactas por cada frase o segmento.
- [ ] Exportar estos datos a un formato estandarizado, idealmente **WebVTT (`.vtt`)** por su soporte nativo en HTML5, o **JSON** si se requiere control estructural fino.
- [ ] Almacenar los archivos generados en una ruta accesible, ej: `audios/subs/` (con nombres idénticos al MP3, ej: `000_v2502.vtt`).

**Notas**: Es fundamental decidir si queremos resaltar "línea por línea" (más fácil) o "palabra por palabra" (requiere timestamp a nivel de palabra en Whisper).

---

## Fase 2: Integración del formato de datos

```text
░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░   0%
```

- [ ] Definir el mecanismo de entrega: Aprovechar la etiqueta `<track>` de HTML5 dentro de `<audio>` o cargar los datos asíncronamente vía `fetch()` en JS.
- [ ] Si usamos VTT protegido, actualizar `serve.php` para que también sirva archivos `.vtt` previniendo descargas directas no autorizadas.
- [ ] Modificar la función `getAudioFiles` en `incs/functions.php` o armar la ruta dinámica en `play.php` para vincular automáticamente cada track MP3 con su archivo de subtítulos.

---

## Fase 3: Maquetado UI del contendor Karaoke

```text
░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░   0%
```

- [ ] Incorporar un contenedor `<div id="karaoke-container">` en la plantilla de `play.php`, situado debajo de los controles del reproductor.
- [ ] Diseñar el estilo base por CSS en `style.css`: un área con límite de altura `max-height` y deslizamiento `overflow-y: auto`.
- [ ] Definir el diseño de las clases de estado: 
      - `.cue`: Texto normal o apagado.
      - `.cue-active`: Texto destacado (ej. más grande, color brillante o neón, font-weight bold) para indicar la posición en tiempo real.
      - `.cue-past`: Texto ya reproducido (opcionalmente atenuado).

---

## Fase 4: Motor JavaScript de sincronización y auto-scroll

```text
░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░   0%
```

- [ ] Programar un script que parsee el archivo de subtítulos y genere los elementos HTML (`<span>` o `<div>`) por cada línea de diálogo.
- [ ] Conectar un event_listener al `timeupdate` de la etiqueta `<audio>` para determinar qué línea (cue) está reproduciéndose en base a `currentTime`.
- [ ] Aplicar dinámicamente la clase `.cue-active` retirando las anteriores.
- [ ] Implementar la función de **auto-scroll suave** (`scrollIntoView({behavior: 'smooth'})`) para asegurar que el diálogo actual siempre esté en el centro de la pantalla del dispositivo.
- [ ] (Avanzado/Opcional) Hacer cada línea "clickeable". Si el usuario toca un texto, inyectar el `startTime` de esa línea en `audio.currentTime` produciendo un salto instantáneo.

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

