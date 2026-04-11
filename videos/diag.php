<?php
/**
 * Diagnóstico del visor de videos - temporal
 */
require_once '../incs/versionLogs.php';
require_once '../data/db.php';

header('Content-Type: text/html; charset=UTF-8');

$escenas_db = getScenesGrouped();
$json = json_encode(array_values($escenas_db), JSON_UNESCAPED_UNICODE);
$videoCount = 0;
foreach ($escenas_db as $g) {
    foreach ($g['audios'] as $a) {
        if (!empty($a['youtube_video_id'])) $videoCount++;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico Videos</title>
    <style>
        body { background: #1a1a2e; color: #fff; font-family: monospace; padding: 20px; }
        .ok { color: #4caf50; } .err { color: #f44336; } .warn { color: #ff9800; }
        pre { background: #0a0a1e; padding: 10px; border-radius: 8px; overflow: auto; max-height: 300px; font-size: 12px; }
        .video-test { position: relative; width: 100%; max-width: 640px; padding-bottom: 56.25%; background: #000; margin: 20px 0; border: 2px solid #f0e68c; }
        .video-test iframe { position: absolute !important; top: 0; left: 0; width: 100% !important; height: 100% !important; }
        h2 { color: #f0e68c; }
    </style>
</head>
<body>
    <h1>🔍 Diagnóstico Videos VíaCrucis</h1>
    
    <h2>1. Datos PHP</h2>
    <p>Versión: <strong><?= $latestVersion ?></strong></p>
    <p>Grupos cargados: <strong class="<?= count($escenas_db) > 0 ? 'ok' : 'err' ?>"><?= count($escenas_db) ?></strong></p>
    <p>Escenas con YouTube: <strong class="<?= $videoCount > 0 ? 'ok' : 'err' ?>"><?= $videoCount ?></strong></p>
    
    <?php 
    $firstVideo = '';
    foreach ($escenas_db as $g) {
        foreach ($g['audios'] as $a) {
            if (!empty($a['youtube_video_id'])) {
                $firstVideo = $a['youtube_video_id'];
                break 2;
            }
        }
    }
    ?>
    <p>Primer video ID: <strong class="<?= $firstVideo ? 'ok' : 'err' ?>"><?= $firstVideo ?: 'NINGUNO' ?></strong></p>

    <h2>2. Test iframe directo (sin JS)</h2>
    <p>Si ves el video abajo, el problema es del JS. Si NO lo ves, es del navegador/red/CSP.</p>
    <div class="video-test">
        <iframe src="https://www.youtube.com/embed/<?= htmlspecialchars($firstVideo) ?>?autoplay=0" 
                frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
    </div>

    <h2>3. Test YouTube IFrame API</h2>
    <div id="api-test-wrapper" class="video-test">
        <div id="api-test-player"></div>
    </div>
    <p id="api-status" class="warn">⏳ Cargando API de YouTube...</p>

    <h2>4. Datos inyectados (primeras 2 escenas)</h2>
    <pre><?php
    $sample = array_values($escenas_db);
    if (isset($sample[0])) {
        $s = $sample[0];
        $s['audios'] = array_slice($s['audios'], 0, 2);
        echo htmlspecialchars(json_encode($s, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    ?></pre>

    <h2>5. Dimensiones del contenedor</h2>
    <p id="dim-info" class="warn">Cargando...</p>

    <script>
        // Test dimensiones
        const wrapper = document.querySelector('.video-test');
        const rect = wrapper.getBoundingClientRect();
        document.getElementById('dim-info').innerHTML = 
            '<span class="' + (rect.width > 0 && rect.height > 0 ? 'ok' : 'err') + '">' +
            'Width: ' + rect.width.toFixed(0) + 'px, Height: ' + rect.height.toFixed(0) + 'px</span>';
        
        // Test YouTube API
        const tag = document.createElement('script');
        tag.src = 'https://www.youtube.com/iframe_api';
        document.head.appendChild(tag);
        
        window.onYouTubeIframeAPIReady = function() {
            document.getElementById('api-status').innerHTML = '<span class="ok">✅ API cargada</span>';
            try {
                const testPlayer = new YT.Player('api-test-player', {
                    height: '360',
                    width: '640',
                    videoId: '<?= htmlspecialchars($firstVideo) ?>',
                    events: {
                        'onReady': function(e) {
                            document.getElementById('api-status').innerHTML += 
                                '<br><span class="ok">✅ Player creado OK</span>';
                            const iframe = document.querySelector('#api-test-wrapper iframe');
                            if (iframe) {
                                const r = iframe.getBoundingClientRect();
                                document.getElementById('api-status').innerHTML += 
                                    '<br>iframe dims: ' + r.width.toFixed(0) + 'x' + r.height.toFixed(0);
                            }
                        },
                        'onError': function(e) {
                            document.getElementById('api-status').innerHTML += 
                                '<br><span class="err">❌ Error: ' + e.data + '</span>';
                        }
                    }
                });
            } catch(err) {
                document.getElementById('api-status').innerHTML += 
                    '<br><span class="err">❌ Exception: ' + err.message + '</span>';
            }
        };
    </script>
</body>
</html>
