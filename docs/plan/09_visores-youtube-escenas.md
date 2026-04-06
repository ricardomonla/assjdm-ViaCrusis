# 09 - Visor de YouTube con Selector de Escenas

## Objetivo

Integrar los videos de YouTube del VíaCrucis 2025 en un visor que permita seleccionar la escena desde un `<select>` y navegue automáticamente al minuto exacto donde inicia cada escena.

## Requisitos

1. **Base de datos de escenas**: Mapeo de escena → (videoId, timestamp)
2. **UI de selector**: `<select>` con lista de escenas numeradas
3. **Visor embebido**: iframe de YouTube con API para control de tiempo
4. **Navegación automática**: Al seleccionar, cargar video y saltar al minuto

## Estructura de Datos

```javascript
const ESCENAS_YOUTUBE = [
  { id: "101", nombre: "La entrada de Jesús en Jerusalén", videoId: "VIDEO_ID", timestamp: 0 },
  { id: "102", nombre: "El Trato de Judas y Caifás", videoId: "VIDEO_ID", timestamp: 120 },
  // ... más escenas
];
```

## Implementación

### 1. Archivo de configuración de videos

**`jss/youtube_config.js`**
- Exportar constante `ESCENAS_YOUTUBE` con mapeo escena → video + timestamp

### 2. Página del visor

**`videos/index.php`**
- HTML con:
  - `<select id="selector-escenas">` poblado dinámicamente
  - `<div id="youtube-player">` para el iframe
- CSS para layout responsivo

### 3. Lógica JavaScript

**`jss/youtube_player.js`**
- Cargar YouTube IFrame API
- Generar opciones del select desde `ESCENAS_YOUTUBE`
- Manejar `onchange` del select → `player.loadVideoById(videoId, timestamp)`
- Actualizar select cuando el video cambia de sección (opcional)

## URL de YouTube con timestamp

Formato: `https://www.youtube.com/embed/{videoId}?start={seconds}`

Ejemplo: `https://www.youtube.com/embed/dQw4w9WgXcQ?start=120`

## Pasos de Implementación

- [x] Recopilar URLs de YouTube del VíaCrucis 2025
- [x] Extraer videoId y timestamp de inicio para cada escena
- [x] Crear `jss/youtube_config.js` con el mapeo
- [x] Crear `videos/index.php` con la estructura HTML
- [x] Crear `jss/youtube_player.js` con la lógica del player
- [ ] Integrar en navegación principal (agregar enlace "Videos")

## URLs de YouTube

| Grupo | Video ID | URL |
|-------|----------|-----|
| 0XX | `0nxVUTRmb_w` | https://youtu.be/0nxVUTRmb_w |
| 1XX | `ktDtijJMfbo` | https://youtu.be/ktDtijJMfbo |
| 2XX | `GPZE-uxt0LQ` | https://youtu.be/GPZE-uxt0LQ |
| 3XX | `a0LB3VWQstw` | https://youtu.be/a0LB3VWQstw |

## Consideraciones

- **API de YouTube**: Requiere cargar `https://www.youtube.com/iframe_api`
- **Mobile-friendly**: iframe responsivo con aspect-ratio 16:9
- **Accesibilidad**: labels en select, keyboard navigation

## Archivos a Crear

| Archivo | Propósito |
|---------|-----------|
| `jss/youtube_config.js` | Mapeo de escenas a videos YouTube |
| `jss/youtube_player.js` | Lógica del reproductor |
| `videos/index.php` | Página del visor |
| `css/videos.css` | Estilos del visor (opcional) |
