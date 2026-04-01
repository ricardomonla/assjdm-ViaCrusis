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

    // Carga paralela del guion maestro completo y las duraciones calculadas con ffprobe
    Promise.all([
        fetch(`../audios/subs/guion_completo.json?v=${window.appVersion || Date.now()}`).then(res => {
            if (!res.ok) throw new Error('No se pudo cargar el archivo maestro de guiones.');
            return res.json();
        }),
        fetch(`../audios/subs/audio_durations.json?v=${window.appVersion || Date.now()}`).then(res => {
            if (!res.ok) return {}; // Falla silenciosa y devuelve duraciones vacías si no existe
            return res.json();
        })
    ])
    .then(([masterData, durationsData]) => {
        const currentAudioId = window.audioId.split('_')[0];
        const nextAudioId = window.nextAudioId ? window.nextAudioId.split('_')[0] : "";
        const prevAudioId = window.prevAudioId ? window.prevAudioId.split('_')[0] : "";
        
        // Sumamos absolutamente todos los delays anteriores filtrando por el prefijo, sin contar tracks 00X (Desfile)
        function getGlobalOffset(targetId) {
            let offset = 0;
            const sortedKeys = Object.keys(durationsData).sort();
            for (const key of sortedKeys) {
                // Solo sumar si es parte de la obra principal (arranca con 1, 2, 3, 4)
                if (key.match(/^[1-4]/)) {
                    if (key === targetId) break;
                    offset += durationsData[key];
                }
            }
            return offset;
        }
            
            // Extraer guion actual
            let currentScript = masterData[currentAudioId] || [];
            scriptData = currentScript.map(cue => ({...cue, isNextAudio: false, isPrevAudio: false}));
            
            // Si hay un script anterior, traer SOLO el último para mostrar en el tope
            if (prevAudioId && masterData[prevAudioId]) {
                const prevData = masterData[prevAudioId];
                const lastItems = prevData.slice(-1);
                const prevDataMapped = lastItems.map(cue => ({...cue, isPrevAudio: true, isNextAudio: false}));
                scriptData = prevDataMapped.concat(scriptData);
            }
            
            // Si hay un script siguiente, traer SOLO el primero para mostrarlo pegado abajo
            if (nextAudioId && masterData[nextAudioId]) {
                const nextData = masterData[nextAudioId];
                const firstItems = nextData.slice(0, 1);
                const nextDataMapped = firstItems.map(cue => ({...cue, isNextAudio: true, isPrevAudio: false}));
                scriptData = scriptData.concat(nextDataMapped);
            }
            
        if (scriptData.length === 0) {
            scriptContainer.innerHTML = `<div class="script-placeholder">Pista instrumental o sin diálogos asignados.</div>`;
        } else {
            renderScript(scriptData, durationsData);
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

    function renderScript(data, durationsData) {
        scriptContainer.innerHTML = ''; // Limpiar
        
        // Función interna auxiliar
        function getGlobalOffset(targetId) {
            let offset = 0;
            const sortedKeys = Object.keys(durationsData).sort();
            for (const key of sortedKeys) {
                if (key.match(/^[1-4]/)) {
                    if (key === targetId) break;
                    offset += durationsData[key];
                }
            }
            return offset;
        }
        
        let lastCharacter = null;
        
        data.forEach((cue, index) => {
            const block = document.createElement('div');
            let extraClass = '';
            if (cue.isNextAudio) extraClass = 'cue-external-audio cue-next-audio';
            if (cue.isPrevAudio) extraClass = 'cue-external-audio cue-prev-audio';
            block.className = 'cue-block cue-inactive ' + extraClass;
            block.id = `cue-${index}`;
            
            const headerDiv = document.createElement('div');
            headerDiv.className = 'cue-header';
            
            const timeSpan = document.createElement('span');
            timeSpan.className = 'cue-time';
            
            // Identificar qué ID fuente es el cue:
            let cueId = window.audioId.split('_')[0];
            if (cue.isNextAudio && window.nextAudioId) cueId = window.nextAudioId.split('_')[0];
            if (cue.isPrevAudio && window.prevAudioId) cueId = window.prevAudioId.split('_')[0];
            
            // Tiempo Global: Offset Sumado + cue.startTime
            const gOffset = getGlobalOffset(cueId);
            const globalTime = gOffset + cue.startTime;
            const globalH = Math.floor(globalTime / 3600).toString().padStart(2, '0');
            const globalM = Math.floor((globalTime % 3600) / 60).toString().padStart(2, '0');
            const globalS = Math.floor(globalTime % 60).toString().padStart(2, '0');
            
            timeSpan.innerHTML = `<strong>${globalH}:${globalM}:${globalS}</strong> <span style="opacity:0.5; font-size: 0.9em; margin-left: 6px;">[${cueId}]</span>`;
            
            const characterSpan = document.createElement('span');
            characterSpan.className = 'cue-character';
            
            // Lógica de Agrupación de Interlocutores
            if (cue.character === lastCharacter && !cue.isNextAudio && !cue.isPrevAudio) {
                // Si es el mismo de la línea anterior que no escriba nada
                characterSpan.textContent = '';
                // Optional: Quitar el border bottom del header para dar sensación de continuidad
                headerDiv.style.borderBottom = 'none';
                headerDiv.style.marginBottom = '2px';
                headerDiv.style.paddingBottom = '0px';
            } else {
                characterSpan.textContent = cue.character;
            }
            
            // Solo actualizamos lastCharacter si no es un preview de external audio (para no romper la cadena natural)
            if (!cue.isNextAudio && !cue.isPrevAudio) {
                lastCharacter = cue.character;
            }
            
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
                        window.location.href = `play.php?id=${window.nextAudioId}&v=${window.appVersion || Date.now()}`;
                        return;
                    }
                    if (cue.isPrevAudio) {
                        window.location.href = `play.php?id=${window.prevAudioId}&v=${window.appVersion || Date.now()}`;
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
            
            if (cue.isNextAudio || cue.isPrevAudio) continue; 
            
            const nextStartTime = (i + 1 < scriptData.length && !scriptData[i+1].isNextAudio && !scriptData[i+1].isPrevAudio) 
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
