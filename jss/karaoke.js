// jss/karaoke.js

document.addEventListener('DOMContentLoaded', function() {
    const audioPlayer = document.getElementById('audioPlayer');
    const scriptContainer = document.getElementById('script-container');
    
    if (!audioPlayer || !scriptContainer || !window.audioFileBase) {
        return;
    }

    let scriptData = [];
    let currentActiveIdx = -1;
    let isUserScrolling = false;
    let scrollTimeout;

    // Detectar si el usuario está scrolleando manualmente para no forzar el auto-scroll todo el tiempo
    scriptContainer.addEventListener('scroll', () => {
        isUserScrolling = true;
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(() => {
            isUserScrolling = false;
        }, 3000); // Vuelve al auto-scroll si afloja el scroll por 3 seg
    });

    // Cargar el JSON del guion
    fetch(`../audios/subs/${window.audioFileBase}.json?v=${window.appVersion || Date.now()}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('No hay guion disponible para este audio.');
            }
            return response.json();
        })
        .then(data => {
            if (data.length === 0) {
                throw new Error('El guion está vacío.');
            }
            scriptData = data;
            renderScript(scriptData);
        })
        .catch(error => {
            scriptContainer.innerHTML = `<div class="script-placeholder">${error.message}</div>`;
        });

    function renderScript(data) {
        scriptContainer.innerHTML = ''; // Limpiar
        
        data.forEach((cue, index) => {
            const block = document.createElement('div');
            block.className = 'cue-block cue-inactive';
            block.id = `cue-${index}`;
            
            const characterSpan = document.createElement('span');
            characterSpan.className = 'cue-character';
            characterSpan.textContent = cue.character;
            
            const textSpan = document.createElement('span');
            textSpan.className = 'cue-text';
            textSpan.innerHTML = cue.text; // Soporta <br> si los hay
            
            block.appendChild(characterSpan);
            block.appendChild(textSpan);
            
            // Hacer la linea clickeable
            block.addEventListener('click', () => {
                audioPlayer.currentTime = cue.startTime;
                if(audioPlayer.paused) {
                    audioPlayer.play();
                }
                // Hacemos que la proxima vez sí se auto-centre forzado
                isUserScrolling = false; 
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
            // Entramos en la ventana de tiempo del cue
            // Permitimos que el cue permanezca activo hasta que inicie el siguiente
            const nextStartTime = (i + 1 < scriptData.length) ? scriptData[i + 1].startTime : Infinity;
            
            if (currentTime >= cue.startTime && currentTime < nextStartTime) {
                foundIdx = i;
                break;
            }
        }
        
        // Si cambió el index activo
        if (foundIdx !== currentActiveIdx && foundIdx !== -1) {
            // Desmarcar anterior
            if (currentActiveIdx !== -1) {
                const oldBlock = document.getElementById(`cue-${currentActiveIdx}`);
                if (oldBlock) {
                    oldBlock.classList.remove('cue-active');
                    oldBlock.classList.add('cue-inactive');
                }
            }
            
            // Marcar nuevo
            const newBlock = document.getElementById(`cue-${foundIdx}`);
            if (newBlock) {
                newBlock.classList.remove('cue-inactive');
                newBlock.classList.add('cue-active');
                
                // Hacer auto-scroll si el usuario no esta interactuando de forma manual
                if (!isUserScrolling) {
                    // Calculamos la posición para centrar el bloque en el contenedor
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
