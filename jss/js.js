// Función de fade out eliminada a petición del usuario para no reiniciar volumen.

function initAudioPlayer() {
    const audio = document.getElementById('audioPlayer');
    const autoplayMessage = document.getElementById('autoplayMessage');
    const nextButton = document.querySelector('.next-button');
    const isLastAudio = nextButton && nextButton.dataset.isLast === 'true';

    // Configurar eventos solo para botones de navegación
    document.querySelectorAll('.nav-button').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const targetUrl = this.href;
            
            // Navegación directa sin desvanecimiento (fade out)
            window.location.href = targetUrl;
        });
    });

    // Configurar autoplay
    const playPromise = audio.play();
    
    if (playPromise !== undefined) {
        playPromise.then(_ => {
            autoplayMessage.style.display = 'none';
        }).catch(error => {
            autoplayMessage.style.display = 'block';
            audio.controls = true;
        });
    }
    
    // Evento ended SIN FADE para auto-next
    audio.addEventListener('ended', function() {
        const playerContainer = document.querySelector('.audio-player-container');
        playerContainer.classList.add('ended');
        
        setTimeout(() => {
            playerContainer.classList.remove('ended');
            
            if (isLastAudio && window.autoNextEnabled) {
                // Transición inmediata sin fade
                var versionQuery = window.appVersion ? '&v=' + encodeURIComponent(window.appVersion) : '';
                window.location.href = 'play.php?id=' + nextButton.dataset.firstAudioId + versionQuery;
            } else if (nextButton && window.autoNextEnabled) {
                // Transición inmediata sin fade
                window.location.href = nextButton.href;
            }
        }, 500);
    });
    
    // El volumen inicial es manejado nativamente en play.php mediante localStorage
}

document.addEventListener('DOMContentLoaded', initAudioPlayer);