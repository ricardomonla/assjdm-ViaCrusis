<?php
require '../incs/functions.php';
require '../incs/versionLogs.php';

// Configuración inicial
$dirMEDIA = 'media';
$id = $_GET['id'] ?? null;
$audioFiles = getAudioFiles($dirMEDIA);

// Buscar el audio por ID
$audio = null;
foreach ($audioFiles as $item) {
    if ($item['id'] === $id) {
        $audio = $item;
        break;
    }
}

// Validación de archivo
if (!$audio || !file_exists($audio['path'])) {
    http_response_code(404);
    die('Archivo no encontrado.');
}

// Navegación entre audios
$currentIndex = array_search($audio, $audioFiles, true);
$prevAudio = $currentIndex > 0 ? $audioFiles[$currentIndex - 1] : null;
$nextAudio = $currentIndex < count($audioFiles) - 1 ? $audioFiles[$currentIndex + 1] : null;

// Determinar si es el último audio
$isLastAudio = $nextAudio === null;
$firstAudioId = $audioFiles[0]['id'] ?? '';

// Generación de contenido
$audio_title = htmlspecialchars($audio['display_name']);
$audio_file = htmlspecialchars($audio['filename']);

include '../incs/header.php';
?>

<main class="main-content">
    <section class="playlist">
        <h2 class="audio-title"><?= $audio_title ?></h2>
        <div class="audio-player-container">
            <audio id="audioPlayer" controls controlsList="nodownload">
                <source src="../serve.php?file=<?= $audio_file ?>" type="audio/mpeg">
                Tu navegador no soporta el elemento de audio.
            </audio>
            
            <div class="audio-navigation">
                <a href="index.php" class="nav-button back-button" title="Volver a la lista completa">
                    ☰
                </a>
                
                <!-- Controles de navegación -->
                <div class="navigation-group">
                    <?php if ($prevAudio): ?>
                    <a href="play.php?id=<?= htmlspecialchars($prevAudio['id']) ?>&v=<?= urlencode($latestVersion) ?>" 
                        class="nav-button prev-button" title="Anterior">
                        ⏮
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($nextAudio): ?>
                    <a href="play.php?id=<?= htmlspecialchars($nextAudio['id']) ?>&v=<?= urlencode($latestVersion) ?>" 
                        class="nav-button next-button" title="Siguiente"
                        data-is-last="false"
                        data-first-audio-id="<?= htmlspecialchars($firstAudioId) ?>">
                        ⏭
                    </a>
                    <?php else: ?>
                    <a href="play.php?id=<?= htmlspecialchars($firstAudioId) ?>&v=<?= urlencode($latestVersion) ?>" 
                        class="nav-button next-button" title="Iniciar nuevamente"
                        data-is-last="true"
                        data-first-audio-id="<?= htmlspecialchars($firstAudioId) ?>">
                        🔁
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Panel Director - Barra superior (solo Director) -->
        <div class="director-toolbar director-only" id="director-panel" style="display:none">
            <div class="dtool-bar">
                <span class="dtool-track-info">
                    <?= htmlspecialchars($audio['id']) ?> (<?= ($currentIndex + 1) ?>/<?= count($audioFiles) ?>)
                </span>
                <div class="dtool-actions">
                    <button class="dtool-btn" id="btn-time-toggle" onclick="toggleTimeEdit()" title="Marcas de tiempo">⏱</button>
                    <button class="dtool-btn" onclick="toggleDirectorNotes()" title="Notas" id="btn-notes-toggle">📝</button>
                    <button class="dtool-btn" onclick="shareTrackWhatsApp()" title="WhatsApp">📲</button>
                </div>
            </div>
            <div id="director-notes-body" class="dtool-notes" style="display:none">
                <textarea id="director-notes" class="dtool-notes-textarea" 
                    placeholder="Notas para esta escena..."
                    rows="2"></textarea>
            </div>
        </div>

        <!-- Contenedor del Guion / Karaoke -->
        <div id="script-container" class="script-container">
            <div class="script-placeholder">Verificando guion...</div>
        </div>
        
        <script>
        // ===== DIRECTOR NOTES =====
        (function() {
            const trackId = '<?= htmlspecialchars($audio['id']) ?>';
            const NOTES_KEY = 'vcby_notes_' + trackId;
            const notesEl = document.getElementById('director-notes');
            
            if (notesEl) {
                // Cargar notas guardadas
                notesEl.value = localStorage.getItem(NOTES_KEY) || '';
                
                // Auto-guardar al escribir (debounced)
                let saveTimer = null;
                notesEl.addEventListener('input', function() {
                    clearTimeout(saveTimer);
                    saveTimer = setTimeout(function() {
                        localStorage.setItem(NOTES_KEY, notesEl.value);
                    }, 500);
                });
            }
        })();
        
        function toggleDirectorNotes() {
            var body = document.getElementById('director-notes-body');
            var btn = document.getElementById('btn-notes-toggle');
            if (body.style.display === 'none') {
                body.style.display = '';
                if (btn) btn.classList.add('btn-active');
            } else {
                body.style.display = 'none';
                if (btn) btn.classList.remove('btn-active');
            }
        }
        
        function shareTrackWhatsApp() {
            const trackId = '<?= htmlspecialchars($audio['id']) ?>';
            const title = '<?= addslashes($audio_title) ?>';
            const notes = document.getElementById('director-notes').value;
            const url = window.location.href;
            let msg = '🎬 VCBY - ' + title + '\n🔗 ' + url;
            if (notes.trim()) {
                msg += '\n\n📝 Notas:\n' + notes;
            }
            window.open('https://wa.me/?text=' + encodeURIComponent(msg), '_blank');
        }
        </script>

        <script>
            window.autoNextEnabled = true;
            window.firstAudioId = '<?= htmlspecialchars($firstAudioId) ?>';
            window.appVersion = '<?= htmlspecialchars($latestVersion) ?>';
            window.audioId = '<?= htmlspecialchars($audio['id']) ?>';
            window.nextAudioId = '<?= $nextAudio ? htmlspecialchars($nextAudio['id']) : "" ?>';
            window.prevAudioId = '<?= $prevAudio ? htmlspecialchars($prevAudio['id']) : "" ?>';
            
            document.addEventListener('DOMContentLoaded', function() {
                var audio = document.getElementById('audioPlayer');
                
                // Recuperar volumen guardado o 1.0 por defecto
                var savedVol = localStorage.getItem('vcby_vol');
                audio.volume = savedVol !== null ? parseFloat(savedVol) : 1.0;
                
                // Escuchar cambios de volumen y persistirlos
                audio.addEventListener('volumechange', function() {
                    localStorage.setItem('vcby_vol', audio.volume);
                });
            });
        </script>
        <script src="../jss/js.js?v=<?= urlencode($latestVersion) ?>"></script>
        <script src="../jss/karaoke.js?v=<?= urlencode($latestVersion) ?>"></script>
    </section>
</main>

<?php include '../incs/footer.php'; ?>