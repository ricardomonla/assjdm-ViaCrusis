// jss/js.js — Reproductor de audio con Fade-Out per-track

function initAudioPlayer() {
    const audio = document.getElementById('audioPlayer');
    const autoplayMessage = document.getElementById('autoplayMessage');
    const nextButton = document.querySelector('.next-button');
    const isLastAudio = nextButton && nextButton.dataset.isLast === 'true';
    const fadeToggle = document.getElementById('fadeout-toggle');

    // ===== FADE-OUT: Estado per-track desde servidor =====
    // window.__trackFadeOut es inyectado por PHP desde SQLite
    let trackFadeOut = window.__trackFadeOut === 1;

    function isFadeEnabled() {
        return trackFadeOut;
    }

    // Inicializar estado visual del toggle
    if (fadeToggle) {
        // Detectar modo Director leyendo localStorage directamente
        // (la clase body 'director-mode' puede no estar seteada aún)
        var perfilData = null;
        try { perfilData = JSON.parse(localStorage.getItem('vcby_perfil')); } catch(e) {}
        const isDirector = perfilData && perfilData.perfil === 'director';

        if (isDirector) {
            // Director: toggle interactivo
            if (isFadeEnabled()) fadeToggle.classList.add('active');
            fadeToggle.style.cursor = 'pointer';
            fadeToggle.addEventListener('click', function() {
                trackFadeOut = !trackFadeOut;
                fadeToggle.classList.toggle('active', trackFadeOut);

                // Persistir en SQLite via API
                const trackBase = (window.audioId || '').split('_')[0];
                fetch('save_changes.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'track_id=' + encodeURIComponent(trackBase) +
                          '&field=_fade_out' +
                          '&value=' + (trackFadeOut ? '1' : '0')
                });
            });
        } else {
            // Público: indicador visual siempre visible, no editable
            fadeToggle.style.cursor = 'default';
            if (isFadeEnabled()) {
                fadeToggle.innerHTML = '<span class="fadeout-toggle-label" style="color:#4a3e31;font-weight:600;">Fade ON</span>';
            } else {
                fadeToggle.innerHTML = '<span class="fadeout-toggle-label" style="color:#c4b494;opacity:0.6;">🔒 Fade OFF</span>';
            }
        }
    }

    // ===== FADE-OUT: Desvanecimiento progresivo =====
    let fadeInterval = null;

    function fadeOutAndNavigate(url) {
        // Si ya hay un fade en curso, ignorar
        if (fadeInterval) return;

        // Flag para que el listener volumechange de play.php NO guarde vol=0
        window._fadeInProgress = true;

        const savedVolume = audio.volume;
        const duration = 2000; // 2 segundos
        const steps = 40;     // 40 pasos = cada 50ms
        const stepTime = duration / steps;
        const volumeStep = savedVolume / steps;
        let currentStep = 0;

        fadeInterval = setInterval(function() {
            currentStep++;
            audio.volume = Math.max(0, savedVolume - (volumeStep * currentStep));

            if (currentStep >= steps) {
                clearInterval(fadeInterval);
                fadeInterval = null;
                // Restaurar volumen original en localStorage antes de navegar
                localStorage.setItem('vcby_vol', savedVolume);
                window.location.href = url;
            }
        }, stepTime);
    }

    function navigateWithFade(url) {
        if (isFadeEnabled()) {
            fadeOutAndNavigate(url);
        } else {
            window.location.href = url;
        }
    }

    // ===== Botones de navegación =====
    document.querySelectorAll('.nav-button').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            navigateWithFade(this.href);
        });
    });

    // ===== Selector de escena (combo de grupo) =====
    const sceneSelector = document.getElementById('sceneSelector');
    if (sceneSelector) {
        sceneSelector.addEventListener('change', function() {
            var versionQuery = window.appVersion ? '&v=' + encodeURIComponent(window.appVersion) : '';
            var url = 'play.php?id=' + encodeURIComponent(this.value) + versionQuery;
            navigateWithFade(url);
        });
    }

    // ===== Autoplay =====
    const playPromise = audio.play();
    
    if (playPromise !== undefined) {
        playPromise.then(_ => {
            if (autoplayMessage) autoplayMessage.style.display = 'none';
        }).catch(error => {
            if (autoplayMessage) autoplayMessage.style.display = 'block';
            audio.controls = true;
        });
    }
    
    // ===== Evento ended: auto-next con fade condicional =====
    audio.addEventListener('ended', function() {
        const playerContainer = document.querySelector('.audio-player-container');
        playerContainer.classList.add('ended');
        
        setTimeout(() => {
            playerContainer.classList.remove('ended');
            
            if (isLastAudio && window.autoNextEnabled) {
                var versionQuery = window.appVersion ? '&v=' + encodeURIComponent(window.appVersion) : '';
                var url = 'play.php?id=' + nextButton.dataset.firstAudioId + versionQuery;
                navigateWithFade(url);
            } else if (nextButton && window.autoNextEnabled) {
                navigateWithFade(nextButton.href);
            }
        }, 500);
    });
    
    // El volumen inicial es manejado nativamente en play.php mediante localStorage
}

document.addEventListener('DOMContentLoaded', initAudioPlayer);