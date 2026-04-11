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
    
    <!-- Panel Director - Sólo visible para admin (perfiles.js) -->
    <div class="director-toolbar director-only" id="director-video-panel" style="display:none; margin-bottom: 0;">
        <div class="dtool-bar" style="justify-content: center; gap: 15px; border-radius: 12px 12px 0 0;">
            <span class="dtool-track-info" style="color:#ecc971;">🎬 Modo Director</span>
            <div class="dtool-actions">
                <button class="dtool-btn" onclick="vcbyVideoDirector.setStamp()" title="Marcar tiempo actual">🎯 Fijar Timestamp</button>
                <button class="dtool-btn" onclick="vcbyVideoDirector.nudge(-1)" title="-1 Segundo">◂ -1s</button>
                <button class="dtool-btn" onclick="vcbyVideoDirector.nudge(1)" title="+1 Segundo">+1s ▸</button>
                <button class="dtool-btn" onclick="vcbyVideoDirector.editId()" title="Cambiar Video ID">🔗 Editar ID</button>
            </div>
            <span id="save-status" style="font-size: 0.8rem; color:#8fbc8f;"></span>
        </div>
    </div>

    <div id="welcome-message" style="text-align: center; padding: 40px; background: rgba(255, 255, 255, 0.05); border-radius: 12px; border: 2px dashed rgba(240, 230, 140, 0.3); margin-bottom: 20px;">
      <h2 style="color: #f0e68c; margin-bottom: 15px;">🎬 Selecciona una escena</h2>
      <p style="color: #ccc; font-size: 1.1rem; max-width: 600px; margin: 0 auto; line-height: 1.6;">Utiliza los botones de la barra superior para elegir una parte del Via Crucis y luego selecciona la escena que deseas visualizar.</p>
    </div>
    <div class="video-wrapper" id="video-wrapper" style="display: none;">
      <div id="youtube-player"></div>
    </div>
  </div>

  <!-- Configuración de escenas Inyectada Dinámicamente desde SQLite -->
  <script>
    window.apiBase = '../'; // Para llamadas AJAX a la raíz u otros sitios si cruzamos dominio
    window.VCBY_SCENES_DB = <?= json_encode(array_values($escenas_db), JSON_UNESCAPED_UNICODE) ?>;
  </script>
  
  <script src="../jss/perfiles.js?v=<?= urlencode($latestVersion ?? '1.0') ?>"></script>
  <script src="../jss/js.js?v=<?= urlencode($latestVersion ?? '1.0') ?>"></script>
  <script src="../jss/modal.js?v=<?= urlencode($latestVersion ?? '1.0') ?>"></script>
  <script src="../jss/youtube_player.js?v=<?= urlencode($latestVersion ?? '1.0') ?>"></script>
  
  <!-- Script específico para Director en modo Video -->
  <script>
    const vcbyVideoDirector = {
        getActScene: function() {
            const selector = document.getElementById('selector-escenas');
            return selector ? selector.value : null;
        },
        saveConfig: async function(sceneId, videoid, ts) {
            document.getElementById('save-status').innerText = 'Guardando...';
            try {
                const res = await fetch('api_scenes.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        action: 'update_youtube',
                        scene_id: sceneId,
                        youtube_video_id: videoid,
                        youtube_timestamp: ts
                    })
                });
                const data = await res.json();
                if(data.success) {
                    document.getElementById('save-status').innerText = '✅ Guardado';
                    // Actualizar en el array global
                    const target = window.ESCENAS_YOUTUBE.find(x => x.id === sceneId);
                    if (target) {
                        target.videoId = videoid;
                        target.timestamp = parseInt(ts);
                    }
                    setTimeout(() => { document.getElementById('save-status').innerText = ''; }, 2000);
                } else {
                    vcbyAlert('Error: ' + data.error, 'error');
                }
            } catch (err) {
                vcbyAlert('Error de red al guardar: ' + err, 'error');
            }
        },
        setStamp: function() {
            const sceneId = this.getActScene();
            const p = window.vcbyGetPlayer ? window.vcbyGetPlayer() : null;
            if(!sceneId || !p || !p.getCurrentTime) return vcbyAlert('No hay escena o player listo.', 'error');
            const target = window.ESCENAS_YOUTUBE.find(x => x.id === sceneId);
            if(!target) return;
            const currentTime = Math.floor(p.getCurrentTime());
            this.saveConfig(sceneId, target.videoId, currentTime);
        },
        nudge: function(secs) {
            const sceneId = this.getActScene();
            if(!sceneId) return;
            const target = window.ESCENAS_YOUTUBE.find(x => x.id === sceneId);
            if(!target) return;
            let newTs = (target.timestamp || 0) + secs;
            if(newTs < 0) newTs = 0;
            this.saveConfig(sceneId, target.videoId, newTs);
            const p = window.vcbyGetPlayer ? window.vcbyGetPlayer() : null;
            if (p && p.seekTo) {
                p.seekTo(newTs, true);
            }
        },
        editId: async function() {
            const sceneId = this.getActScene();
            if(!sceneId) return;
            const target = window.ESCENAS_YOUTUBE.find(x => x.id === sceneId);
            if(!target) return;
            const newId = await vcbyPrompt('YouTube Video ID (ej: ktDtijJMfbo):', target.videoId);
            if(newId !== null && newId.trim() !== '') {
                this.saveConfig(sceneId, newId.trim(), target.timestamp);
                window.location.reload();
            }
        }
    };
  </script>
</body>
</html>
