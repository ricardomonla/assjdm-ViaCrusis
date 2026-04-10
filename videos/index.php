<?php
/**
 * Visor de Videos - VíaCrucis 2025
 * Permite seleccionar escenas y navegar al minuto exacto en YouTube
 */
require_once '../incs/versionLogs.php';
require_once '../data/db.php';
$escenas_db = getScenesGrouped();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Videos VíaCrucis 2025</title>
  <link rel="stylesheet" href="../css/videos.css?v=<?= urlencode($latestVersion ?? '1.0') ?>">
</head>
<body>
  <!-- Barra fija de controles -->
  <div class="controls-bar">
    <div class="controls-container">
      <!-- Botón volver a audios -->
      <a href="../audios/" class="btn-back-audios" title="Volver a Audios">
        🔙 Audios
      </a>

      <!-- Selector de Grupos -->
      <div class="select-wrapper">
        <select id="selector-grupos">
          <option value="">-- Selecciona un grupo --</option>
          <?php foreach ($escenas_db as $group): ?>
            <option value="<?= htmlspecialchars($group['group_id']) ?>">
                <?= htmlspecialchars($group['icon'] . ' ' . $group['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
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
    <div id="welcome-message" style="text-align: center; margin-top: 60px; padding: 40px; background: rgba(255, 255, 255, 0.05); border-radius: 12px; border: 2px dashed rgba(240, 230, 140, 0.3);">
      <h2 style="color: #f0e68c; margin-bottom: 15px;">🎬 Selecciona una escena</h2>
      <p style="color: #ccc; font-size: 1.1rem; max-width: 600px; margin: 0 auto; line-height: 1.6;">Utiliza los botones de la barra superior para elegir una parte del Via Crucis y luego selecciona la escena que deseas visualizar.</p>
    </div>
    <div class="video-wrapper" id="video-wrapper" style="display: none;">
      <div id="youtube-player"></div>
    </div>
  </div>

  <!-- Configuración de escenas Inyectada Dinámicamente desde SQLite -->
  <script>
    window.VCBY_SCENES_DB = <?= json_encode(array_values($escenas_db), JSON_UNESCAPED_UNICODE) ?>;
  </script>
  <!-- <script src="../jss/youtube_config.js"></script> YA NO SE USA -->
  
  <!-- Reproductor de YouTube -->
  <script src="../jss/youtube_player.js?v=<?= urlencode($latestVersion ?? '1.0') ?>"></script>
</body>
</html>
