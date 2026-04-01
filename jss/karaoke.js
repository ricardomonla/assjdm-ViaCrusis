// jss/karaoke.js

document.addEventListener('DOMContentLoaded', function() {
    const audioPlayer = document.getElementById('audioPlayer');
    const scriptContainer = document.getElementById('script-container');
    
    if (!audioPlayer || !scriptContainer || !window.audioId) {
        return;
    }

    let scriptData = [];
    let currentActiveIdx = -1;
    let isUserScrolling = false;
    let scrollTimeout;

    // Detectar si el usuario está scrolleando manualmente
    scriptContainer.addEventListener('scroll', () => {
        isUserScrolling = true;
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(() => {
            isUserScrolling = false;
        }, 3000); // Vuelve al auto-scroll tras 3s de inactividad de scroll
    });

    // Carga del guion maestro completo
    fetch(`../audios/subs/guion_completo.json?v=${window.appVersion || Date.now()}`)
        .then(response => {
            if (!response.ok) throw new Error('No se pudo cargar el archivo maestro de guiones.');
            return response.json();
        })
        .then(masterData => {
            const currentAudioId = window.audioId.split('_')[0];
            const nextAudioId = window.nextAudioId ? window.nextAudioId.split('_')[0] : "";
            
            // Extraer guion actual
            let currentScript = masterData[currentAudioId] || [];
            scriptData = currentScript.map(cue => ({...cue, isNextAudio: false}));
            
            // Si hay un script siguiente, traerlo también para mostrarlo pegado abajo (preview continuo)
            if (nextAudioId && masterData[nextAudioId]) {
                const nextDataMapped = masterData[nextAudioId].map(cue => ({...cue, isNextAudio: true}));
                scriptData = scriptData.concat(nextDataMapped);
            }
            
            if (scriptData.length === 0) {
                scriptContainer.innerHTML = `<div class="script-placeholder">Pista instrumental o sin diálogos asignados.</div>`;
            } else {
                renderScript(scriptData);
            }
        })
        .catch(error => {
            scriptContainer.innerHTML = `<div class="script-placeholder">${error.message}</div>`;
        });

    let lastTap = 0;
    let pendingClickTimeout = null;

    // Listener global en el contenedor para detectar 'doble tap'
    scriptContainer.addEventListener('click', function(e) {
        const currentTime = new Date().getTime();
        const tapLength = currentTime - lastTap;
        
        if (tapLength < 400 && tapLength > 0) {
            // DOBLE TAP DETECTADO
            // Cancelar salto individual si existiera
            if (pendingClickTimeout) clearTimeout(pendingClickTimeout);
            
            e.preventDefault();
            
            if (audioPlayer.paused) {
                audioPlayer.play();
            } else {
                audioPlayer.pause();
            }
        }
        lastTap = currentTime;
    });

    function renderScript(data) {
        scriptContainer.innerHTML = ''; // Limpiar
        
        data.forEach((cue, index) => {
            const block = document.createElement('div');
            block.className = 'cue-block cue-inactive ' + (cue.isNextAudio ? 'cue-next-audio' : '');
            block.id = `cue-${index}`;
            
            const headerDiv = document.createElement('div');
            headerDiv.className = 'cue-header';
            
            const timeSpan = document.createElement('span');
            timeSpan.className = 'cue-time';
            const m = Math.floor(cue.startTime / 60);
            const s = Math.floor(cue.startTime % 60).toString().padStart(2, '0');
            timeSpan.textContent = `[${m}:${s}]`;
            
            const characterSpan = document.createElement('span');
            characterSpan.className = 'cue-character';
            characterSpan.textContent = cue.character;
            
            headerDiv.appendChild(timeSpan);
            headerDiv.appendChild(characterSpan);
            
            const textDiv = document.createElement('div');
            textDiv.className = 'cue-text';
            textDiv.innerHTML = cue.text;
            
            block.appendChild(headerDiv);
            block.appendChild(textDiv);
            
            block.addEventListener('click', (e) => {
                // Prevenir que el click bubble up e interfiera (opcional, dejamos que suba para doble tap general)
                
                // En lugar de ejecutar inmediato, agendamos el single_click para despues de 400ms.
                // Si ocurre otro tap antes de 400ms, el global del scriptContainer cancelará este timeout.
                if (pendingClickTimeout) clearTimeout(pendingClickTimeout);
                
                pendingClickTimeout = setTimeout(() => {
                    // Acción de click normal:
                    if (cue.isNextAudio) {
                        // Es un texto del próximo audio. Redirigimos sin asco.
                        window.location.href = `play.php?id=${window.nextAudioId}&v=${window.appVersion || Date.now()}`;
                        return;
                    }
                    
                    // Salto temporal dentro del audio actual
                    audioPlayer.currentTime = cue.startTime;
                    if(audioPlayer.paused) {
                        audioPlayer.play();
                    }
                    isUserScrolling = false; 
                }, 400); 
            });
            
            scriptContainer.appendChild(block);
        });
        
        // Arranca a escuchar el tiempo
        audioPlayer.addEventListener('timeupdate', updateKaraoke);
    }
    
    function updateKaraoke() {
        const currentTime = audioPlayer.currentTime;
        let foundIdx = -1;
        
        // Buscar cual es el cue correspondiente
        for (let i = 0; i < scriptData.length; i++) {
            const cue = scriptData[i];
            
            if (cue.isNextAudio) continue; 
            
            const nextStartTime = (i + 1 < scriptData.length && !scriptData[i+1].isNextAudio) 
                                    ? scriptData[i + 1].startTime 
                                    : Infinity;
            
            if (currentTime >= cue.startTime && currentTime < nextStartTime) {
                foundIdx = i;
                break;
            }
        }
        
        // Si cambió el index activo
        if (foundIdx !== currentActiveIdx && foundIdx !== -1) {
            if (currentActiveIdx !== -1) {
                const oldBlock = document.getElementById(`cue-${currentActiveIdx}`);
                if (oldBlock) {
                    oldBlock.classList.remove('cue-active');
                    oldBlock.classList.add('cue-inactive');
                }
            }
            
            const newBlock = document.getElementById(`cue-${foundIdx}`);
            if (newBlock) {
                newBlock.classList.remove('cue-inactive');
                newBlock.classList.add('cue-active');
                
                // Auto-scroll
                if (!isUserScrolling) {
                    const containerHeight = scriptContainer.clientHeight;
                    const blockTop = newBlock.offsetTop;
                    const blockHeight = newBlock.clientHeight;
                    
                    scriptContainer.scrollTo({
                        top: blockTop - (containerHeight / 2) + (blockHeight / 2),
                        behavior: 'smooth'
                    });
                }
            }
            
            currentActiveIdx = foundIdx;
        }
    }
});
