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
  <div class="videos-container">
    <header class="videos-header">
      <h1>🎬 VíaCrucis 2025 - Videos</h1>
      <p>Selecciona una escena para ver el video en el minuto exacto</p>
    </header>

    <div class="scene-selector-container">
      <label for="selector-escenas">📍 Seleccionar Escena:</label>
      <select id="selector-escenas">
        <option value="">-- Elige una escena --</option>
      </select>
    </div>

    <div class="video-wrapper">
      <div id="youtube-player"></div>
    </div>

    <div class="scene-info">
      <h3>ℹ️ Cómo usar</h3>
      <p>Selecciona una escena del menú desplegable y el video se cargará automáticamente en el momento exacto.</p>
    </div>
  </div>

  <!-- Configuración de escenas -->
  <script src="../jss/youtube_config.js"></script>
  <!-- Reproductor de YouTube -->
  <script src="../jss/youtube_player.js"></script>
</body>
</html>
