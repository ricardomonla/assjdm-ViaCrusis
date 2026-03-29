# Plan 04: Sub-sitio Editor de Guion Cinematográfico Ligero

> **Estado**: ⏳ En progreso
> **Fecha**: 2026-03-29
> **Servidor**: srvv-nginx-rm (Ruta: `/vcby/docs/guion`)

---

## Progreso General

```text
▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓░░░░░  75% — EN PROGRESO
```

| Fase | Descripción | Estado |
|:---|:---|:---|
| 1 | Prototipado visual con Stitch (Aspecto Cinematográfico) | ✅ |
| 2 | Desarrollo del motor Frontend (Vanilla JS + In-place edit) | ✅ |
| 3 | Parseo de Datos (Consumir Guion del Plan 03) | ✅ |
| 4 | Persistencia / Guardado de Cambios | 📋 |

---

## Objetivo

Desarrollar un sub-sitio ligero alojado en el directorio `docs/guion/` que consuma e interprete los guiones transcritos producidos durante el Plan 03 (`docs/guion/guion-viacrusis-2026.md` o similares). 
Este sub-sitio debe renderizar el texto con el formato visual clásico de un **guion cinematográfico o teatral** (fuente monospace, nombres de personajes centrados, diálogos con sangrías estrictas). 

Debe incorporar características avanzadas y muy rápidas de edición in-place (WYSIWYG): al hacer doble clic en cualquier bloque de parlamento, escena o personaje, dicho bloque se vuelve editable inmediatamente sin cambiar de pantalla. Esto permite corregir el texto "en tiempo real" apreciando exactamente cómo se ve en el producto final. Al salir del bloque (perder el foco), retorna de forma transparente a su modo únicamente visual.

---

## Fase 1: Prototipado visual con Stitch

```text
██████████████████████████████ 100%
```

- [x] Utilizar el servidor MCP de Stitch para generar modelos de pantalla iniciales basados en diseño de software de guionística (ej. tipo Celtx, Final Draft).
- [x] Definir el CSS para imitar papel elegante o formato legible en modo oscuro.
- [x] Ajustar la tipografía estricta (Courier, Courier New u otra monospace premium) y espaciados estándar de guion (sangrías left/right para diálogo, personaje, acotaciones).

**Notas/Hallazgos**: 
- Stitch generó el mockup bajo el proyecto "Script Editor". El diseño incorpora el tema "Final Draft Ivory", con soporte cromático tipo papel Vellum (#FBF9F4) y tipografía Monospace de impacto minimalista.

---

## Fase 2: Desarrollo del motor Frontend (Edición In-Place)

```text
██████████████████████████████ 100%
```

- [x] Construir los archivos base (`docs/guion/index.html` o `.php`).
- [x] Implementar lógica Vanilla JS para interceptar eventos `dblclick` en los contenedores del guion.
- [x] Habilitar el atributo `contenteditable="true"` dinámicamente, asignando el foco al texto seleccionado.
- [x] Capturar el evento `blur` (o la tecla `Escape`) para terminar la edición, repintando el cambio y removiendo el modo editable.

**Notas/Hallazgos**:
- Se generaron `docs/guion/index.php`, `docs/guion/css/style.css` y `docs/guion/js/editor.js`. 
- El sistema de In-Place Editing rastrea elementos válidos (scene-heading, dialogue, etc), manipula the Selection Range para enfocar al final de la línea sutilmente al hacer doble-click, y soporta guardado por "Blur" y cancelación por "Escape".

---

## Fase 3: Parseo de Datos 

```text
██████████████████████████████ 100%
```

- [x] Crear el parser (vía `parser.js`) para captar el Markdown del resultado del Plan 03 (ej. `docs/guion/guion-viacrusis-2026.md`).
- [x] Mapear los nombres en mayúscula a los bordes y márgenes definidos para personajes en formato de guion.
- [x] Parsear `> ` o notas de bloque como *Direction Blocks* o *Action Blocks*.
- [x] Procesar metadatos (ej: IDs `[L1]`, timestamps `[01:10]`) para renderizarlos en un color atenuado (opacity) o como "parenthetical notes" debajo del nombre del personaje.
- [x] **Agrupación en Acordeones**: Detectar estructuras como `## Track XXX` para agrupar visualmente el contenido en menús desplegables (`<details>`), replicando la estructura organizativa (Desfile, Pasión, Calvario, etc.) del sitio principal `index.php`.

**Notas/Hallazgos**:
- Se creó `js/parser.js` integrando Vanilla JS con `fetch` para procesar nativamente en el navegador. 
- Filtra eficientemente delimitadores, anida dinámicamente Escenas (Tracks) dentro de Grupos Padre mediante extracción del primer dígito (`0` = Desfile, `1` = Pasión), e implementa HTML5 `<details>` para organizar scripts gigantes sin sobrecargar visualmente el Vellum canvas.

---

## Fase 4: Persistencia y Exportación

```text
░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░   0%
```

- [ ] Permitir que los arreglos realizados "in-place" afecten a un objeto global en JavaScript.
- [ ] Añadir botón de guardar/descargar (`#export-btn`) que empaquete las correciones para que no se pierdan. (Descarga como un nuevo .md limpio, o envía via AJAX a un micro script PHP de guardado).

**Notas/Hallazgos**:

---

## Resumen de archivos a crear/modificar

| Archivo | Ubicación | Estado |
|:---|:---|:---|
| `docs/plan/04_sitio-guion-cinematografico.md` | `docs/plan/` | ⏳ |
| `index.html` / `index.php` | `docs/guion/` | ✅ |
| `editor.js` | `docs/guion/js/` | ✅ |
| `parser.js` | `docs/guion/js/` | ✅ |
| `script_style.css` | `docs/guion/css/` | ✅ |

## Seguridad
- Proteger con Kerberos/sesión en caso de estar expuesto en NGINX, limitando quién puede sobrescribir el guion madre si se habilita el autoguardado PHP.

## Rollback
- Eliminar el cliente HTML/JS de `docs/guion/` para restaurar.
- Reestablecer el `guion-viacrusis-2026.md` desde una versión anterior del git commit si el autoguardado fue destructivo.
