<?php
/**
 * Visor de Videos - VíaCrucis 2025
 * Permite seleccionar escenas y navegar al minuto exacto en YouTube
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Videos VíaCrucis 2025</title>
  <link rel="stylesheet" href="../css/videos.css">
</head>
<body>
  <!-- Barra fija de controles -->
  <div class="controls-bar">
    <div class="controls-container">
      <!-- Botones de grupos -->
      <div class="group-buttons">
        <button class="group-btn" data-grupo="0XX" title="Intro / Previa">🎬 0XX</button>
        <button class="group-btn" data-grupo="1XX" title="Primera Parte">✝️ 1XX</button>
        <button class="group-btn" data-grupo="2XX" title="Segunda Parte">🙏 2XX</button>
        <button class="group-btn" data-grupo="3XX" title="Tercera Parte">🕊️ 3XX</button>
      </div>

      <!-- Selector de escenas -->
      <div class="select-wrapper">
        <select id="selector-escenas">
          <option value="">-- Selecciona un grupo primero --</option>
        </select>
      </div>
    </div>
  </div>

  <!-- Contenedor del video -->
  <div class="video-container">
    <div class="video-wrapper">
      <div id="youtube-player"></div>
    </div>
  </div>

  <!-- Configuración de escenas -->
  <script src="../jss/youtube_config.js"></script>
  <!-- Reproductor de YouTube -->
  <script src="../jss/youtube_player.js"></script>
</body>
</html>
