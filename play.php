<?php
require 'incs/functions.php';
require 'incs/versionLogs.php';

// Configuración inicial
$dirMEDIA = 'media';
$id = $_GET['id'] ?? null;
$hideNavButtons = isset($_GET['wa']) && $_GET['wa'] == '1'; // Definir la variable aquí
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

// Generación de botones de navegación
$buttons = [];
if (!$hideNavButtons) {
    if ($prevAudio) {
        $buttons[] = sprintf(
            '<a href="play.php?id=%s" 
                class="nav-button prev-button">
                ⟵ Anterior
            </a>',
            htmlspecialchars($prevAudio['id'])
        );
    }

    $buttons[] = sprintf(
        '<a href="index.php?key=VCV2025" 
            class="nav-button back-button" 
            title="Volver a la lista completa">
            ← Volver
        </a>'
    );

    if ($nextAudio) {
        $buttons[] = sprintf(
            '<a href="play.php?id=%s" 
                class="nav-button next-button" 
                data-is-last="%s" 
                data-first-audio-id="%s">
                Siguiente ⟶
            </a>',
            htmlspecialchars($nextAudio['id']),
            $isLastAudio ? 'true' : 'false',
            htmlspecialchars($firstAudioId)
        );
    } else {
        $buttons[] = sprintf(
            '<a href="play.php?id=%s" 
                class="nav-button next-button" 
                data-is-last="true" 
                data-first-audio-id="%s">
                Iniciar nuevamente
            </a>',
            htmlspecialchars($firstAudioId),
            htmlspecialchars($firstAudioId)
        );
    }
}

$htmlBOTONEs = !$hideNavButtons ? sprintf('<div class="audio-navigation">%s</div>', implode('', $buttons)) : '';

// Incluir el archivo JavaScript externo
$javascriptCode = '';
if (!$hideNavButtons) {
    $javascriptCode = sprintf(
        <<<'HTML'
        <script>
            // Variable global para controlar el autoNext
            window.autoNextEnabled = true;
            window.firstAudioId = '%s';
            
            // Configurar volumen inicial
            document.addEventListener('DOMContentLoaded', function() {
                const audio = document.getElementById('audioPlayer');
                audio.volume = 1.0; // Volumen máximo al inicio
            });
        </script>
        <script src="jss/js.js"></script>
        HTML,
        htmlspecialchars($firstAudioId)
    );
}

// Construcción del HTML final
$htmlMAIN = sprintf(
    <<<'HTML'
    <main class="main-content">
        <section class="playlist">
            <h2 class="audio-title">%s</h2>
            <div class="audio-player-container">
                <audio id="audioPlayer" controls controlsList="nodownload">
                    <source src="serve.php?file=%s" type="audio/mpeg">
                    Tu navegador no soporta el elemento de audio.
                </audio>
                %s
            </div>
            %s
            %s
        </section>
    </main>
    HTML,
    $audio_title,
    $audio_file,
    (!$hideNavButtons ? '<div id="autoplayMessage" class="autoplay-message"><p>La reproducción automática está bloqueada. Por favor haz clic en el botón de play.</p></div>' : ''),
    $htmlBOTONEs,
    $javascriptCode
);

// Salida final
include 'incs/header.php';
echo $htmlMAIN;
include 'incs/footer.php';
?>