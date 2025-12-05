// Función de fade out (se mantiene igual)
function fadeOutAudio(audioElement, callback) {
    const fadeDuration = 800;
    const fadeSteps = 20;
    const stepTime = fadeDuration / fadeSteps;
    const initialVolume = audioElement.volume;
    const stepDecrease = initialVolume / fadeSteps;
    
    const originalValues = {
        volume: audioElement.volume,
        playbackRate: audioElement.playbackRate
    };

    let stepsCompleted = 0;
    
    const fadeInterval = setInterval(() => {
        if (stepsCompleted < fadeSteps) {
            audioElement.volume = Math.max(0, audioElement.volume - stepDecrease);
            stepsCompleted++;
        } else {
            clearInterval(fadeInterval);
            audioElement.pause();
            audioElement.volume = originalValues.volume;
            audioElement.playbackRate = originalValues.playbackRate;
            
            if (typeof callback === 'function') {
                setTimeout(callback, 100);
            }
        }
    }, stepTime);
}

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
            
            // Aplicar fade out solo para navegación manual
            fadeOutAudio(audio, () => {
                window.location.href = targetUrl;
            });
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
                window.location.href = 'play.php?id=' + nextButton.dataset.firstAudioId;
            } else if (nextButton && window.autoNextEnabled) {
                // Transición inmediata sin fade
                window.location.href = nextButton.href;
            }
        }, 500);
    });
    
    // Inicializar volumen
    audio.volume = 1.0;
}

document.addEventListener('DOMContentLoaded', initAudioPlayer);