// jss/karaoke.js

document.addEventListener('DOMContentLoaded', function() {
    const audioPlayer = document.getElementById('audioPlayer');
    const scriptContainer = document.getElementById('script-container');
    
    if (!audioPlayer || !scriptContainer || !window.audioId) {
        return;
    }

    let scriptData = [];
    let currentActiveIdx = -1;

    // Formatear segundos a HH:MM:SS
    function fmtTime(sec) {
        var h = Math.floor(sec / 3600).toString().padStart(2, '0');
        var m = Math.floor((sec % 3600) / 60).toString().padStart(2, '0');
        var s = Math.floor(sec % 60).toString().padStart(2, '0');
        return h + ':' + m + ':' + s;
    }

    // Parsear HH:MM:SS → segundos (acepta también segundos crudos como fallback)
    function parseTime(str) {
        str = (str || '').trim();
        var parts = str.split(':');
        if (parts.length === 3) {
            return parseInt(parts[0]) * 3600 + parseInt(parts[1]) * 60 + parseFloat(parts[2]);
        } else if (parts.length === 2) {
            return parseInt(parts[0]) * 60 + parseFloat(parts[1]);
        }
        return parseFloat(str);
    }
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

    // Carga del guion: inline desde SQLite > fetch JSON estático como fallback
    var _trackId = (window.audioId || '').split('_')[0];
    var _cuePromise = (window.__cueData && typeof window.__cueData === 'object' && Object.keys(window.__cueData).length > 0)
        ? Promise.resolve(window.__cueData)
        : fetch('../audios/subs/guion_completo.json?v=' + Date.now()).then(function(res) { return res.ok ? res.json() : {}; }).catch(function() { return {}; });

    Promise.all([
        _cuePromise,
        fetch('../audios/subs/audio_durations.json?v=' + (window.appVersion || Date.now())).then(function(res) {
            if (!res.ok) return {};
            return res.json();
        }).catch(function() { return {}; }) // Fallback silencioso a vacío
    ])
    .then(([masterData, durationsData]) => {
        const currentAudioId = window.audioId.split('_')[0];
        const nextAudioId = window.nextAudioId ? window.nextAudioId.split('_')[0] : "";
        const prevAudioId = window.prevAudioId ? window.prevAudioId.split('_')[0] : "";
        
        console.log('[VCBY] audioId=' + currentAudioId, 'cues=' + (masterData[currentAudioId]||[]).length);
        
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
        console.error('[VCBY] Error cargando datos:', error);
        scriptContainer.innerHTML = `<div class="script-placeholder">Error: ${error.message}</div>`;
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
        scriptContainer.innerHTML = '';
        
        function getGlobalOffset(targetId) {
            var offset = 0;
            var sortedKeys = Object.keys(durationsData).sort();
            for (var k = 0; k < sortedKeys.length; k++) {
                var key = sortedKeys[k];
                if (key.match(/^[1-4]/)) {
                    if (key === targetId) break;
                    offset += durationsData[key];
                }
            }
            return offset;
        }
        
        // Paso 1: Agrupar cues consecutivos del mismo personaje
        var groups = [];
        var currentGroup = null;
        
        data.forEach(function(cue, index) {
            cue._originalIndex = (cue.cue_index !== undefined) ? cue.cue_index : index;
            
            if (cue.isNextAudio || cue.isPrevAudio) {
                if (currentGroup) { groups.push(currentGroup); currentGroup = null; }
                groups.push({ character: cue.character, idp: cue.idp || '', cues: [cue], isExternal: true });
                return;
            }
            
            var cueIdp = cue.idp || '';
            var lastCueInGroup = currentGroup ? currentGroup.cues[currentGroup.cues.length - 1] : null;
            var timeGoesBack = lastCueInGroup && cue.startTime < lastCueInGroup.startTime;
            if (currentGroup && !currentGroup.isExternal && currentGroup.idp === cueIdp && currentGroup.character === cue.character && !timeGoesBack) {
                currentGroup.cues.push(cue);
            } else {
                if (currentGroup) groups.push(currentGroup);
                currentGroup = { character: cue.character, idp: cueIdp, cues: [cue], isExternal: false };
            }
        });
        if (currentGroup) groups.push(currentGroup);
        
        // Helper: enviar POST a save_changes.php y recargar (preservando posición de audio)
        function postAndReload(fd) {
            fetch((window.apiBase||'') + 'save_changes.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: fd.toString() })
            .then(function(r) { return r.json(); })
            .then(function(d) {
                if (d.ok) {
                    sessionStorage.setItem('vcby_audio_time', audioPlayer.currentTime);
                    sessionStorage.setItem('vcby_audio_paused', audioPlayer.paused ? '1' : '0');
                    location.reload();
                } else { vcbyAlert('Error: ' + (d.msg || ''), 'error'); }
            })
            .catch(function(err) { vcbyAlert('Error: ' + (err.message || err), 'error'); });
        }

        // Insertar NUEVA línea de diálogo genérica (mini "+")
        function createDupLineBtn(cue) {
            var row = document.createElement('div');
            row.className = 'cue-insert-inline';
            row.innerHTML = '<button class="btn-insert-cue" title="Insertar nueva línea aquí">+</button>';
            row.querySelector('.btn-insert-cue').addEventListener('click', function(e) {
                e.stopPropagation();
                var trackId = window.audioId.split('_')[0];
                var newTime = parseFloat((cue.startTime + 0.5).toFixed(1));
                var fd = new URLSearchParams();
                fd.append('track_id', trackId);
                fd.append('cue_index', cue._originalIndex);
                fd.append('field', '_insert');
                fd.append('value', '(nuevo diálogo)');
                fd.append('character', cue.character || '');
                fd.append('idp', cue.idp || 'P00');
                fd.append('startTime', newTime);
                postAndReload(fd);
            });
            return row;
        }

        // Insertar NUEVA burbuja genérica ("+") — personaje P00, texto placeholder
        function createDupGroupBtn(group) {
            var lastCue = group.cues[group.cues.length - 1];
            var row = document.createElement('div');
            row.className = 'cue-insert-row';
            row.innerHTML = '<button class="btn-insert-cue" title="Insertar nueva burbuja aquí">+</button>';
            row.querySelector('.btn-insert-cue').addEventListener('click', function(e) {
                e.stopPropagation();
                var trackId = window.audioId.split('_')[0];
                var newTime = parseFloat((lastCue.startTime + 1.0).toFixed(1));
                var cuesData = [
                    { character: 'NUEVO PERSONAJE', idp: 'P00', startTime: newTime, endTime: 0, text: '(nuevo diálogo)' }
                ];
                var fd = new URLSearchParams();
                fd.append('track_id', trackId);
                fd.append('cue_index', lastCue._originalIndex);
                fd.append('field', '_insert_batch');
                fd.append('cues', JSON.stringify(cuesData));
                postAndReload(fd);
            });
            return row;
        }

        // Eliminar UNA línea de diálogo (🗑 chico, con confirmación)
        function createDelLineBtn(cue) {
            var btn = document.createElement('span');
            btn.className = 'cue-delete-inline';
            btn.innerHTML = '<button class="btn-delete-cue" title="Eliminar esta línea">🗑</button>';
            btn.querySelector('.btn-delete-cue').addEventListener('click', function(e) {
                e.stopPropagation();
                var preview = (cue.text || '').replace(/<[^>]*>/g, '').substring(0, 40);
                vcbyConfirm('¿Eliminar línea?\n"' + preview + '…"').then(function(ok) {
                    if (!ok) return;
                    var trackId = window.audioId.split('_')[0];
                    var fd = new URLSearchParams();
                    fd.append('track_id', trackId);
                    fd.append('cue_index', cue._originalIndex);
                    fd.append('field', '_delete');
                    fd.append('value', '');
                    postAndReload(fd);
                });
            });
            return btn;
        }

        // Eliminar BURBUJA completa (🗑 en header, con confirmación)
        function createDelGroupBtn(group) {
            var btn = document.createElement('button');
            btn.className = 'btn-delete-cue cue-delete-group';
            btn.title = 'Eliminar burbuja (' + group.cues.length + ' líneas)';
            btn.textContent = '🗑';
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                vcbyConfirm('¿Eliminar burbuja completa?\n' + group.character + ' (' + group.cues.length + ' líneas)').then(function(ok) {
                    if (!ok) return;
                    var trackId = window.audioId.split('_')[0];
                    var indices = group.cues.map(function(c) { return c._originalIndex; });
                    var fd = new URLSearchParams();
                    fd.append('track_id', trackId);
                    fd.append('cue_index', indices[0]);
                    fd.append('field', '_delete_batch');
                    fd.append('cue_indices', JSON.stringify(indices));
                    postAndReload(fd);
                });
            });
            return btn;
        }
        
        // Paso 2: Renderizar grupos como burbujas
        groups.forEach(function(group) {
            var groupDiv = document.createElement('div');
            var firstCue = group.cues[0];
            var lastCue = group.cues[group.cues.length - 1];
            
            if (group.isExternal) {
                var extraClass = firstCue.isNextAudio ? 'cue-next-audio' : 'cue-prev-audio';
                groupDiv.className = 'cue-group cue-external-audio ' + extraClass;
                groupDiv.id = 'cue-' + firstCue._originalIndex;
                if (firstCue.idp) groupDiv.setAttribute('data-character-id', firstCue.idp);
                
                var headerExt = document.createElement('div');
                headerExt.className = 'cue-group-header';
                headerExt.innerHTML = '<span class="cue-character">' + firstCue.character + '</span>';
                groupDiv.appendChild(headerExt);
                
                var bodyExt = document.createElement('div');
                bodyExt.className = 'cue-group-body';
                var lineExt = document.createElement('span');
                lineExt.className = 'cue-line';
                lineExt.setAttribute('data-cue-index', firstCue._originalIndex);
                lineExt.innerHTML = firstCue.text;
                bodyExt.appendChild(lineExt);
                groupDiv.appendChild(bodyExt);
                
                groupDiv.addEventListener('click', function() {
                    if (firstCue.isNextAudio) window.location.href = 'play.php?id=' + window.nextAudioId + '&v=' + (window.appVersion || Date.now());
                    else if (firstCue.isPrevAudio) window.location.href = 'play.php?id=' + window.prevAudioId + '&v=' + (window.appVersion || Date.now());
                });
                scriptContainer.appendChild(groupDiv);
                return;
            }
            
            // Grupo normal
            groupDiv.className = 'cue-group';
            groupDiv.id = 'group-' + firstCue._originalIndex;
            if (group.idp) groupDiv.setAttribute('data-character-id', group.idp);
            
            // Header: nombre + IDP + tiempo
            var headerDiv = document.createElement('div');
            headerDiv.className = 'cue-group-header';
            
            var idpHtml = group.idp ? '<span class="cue-idp">' + group.idp + '</span>' : '';
            var cueId = window.audioId.split('_')[0];
            var gOffset = getGlobalOffset(cueId);
            var globalTime = gOffset + firstCue.startTime;
            var gH = Math.floor(globalTime / 3600).toString().padStart(2, '0');
            var gM = Math.floor((globalTime % 3600) / 60).toString().padStart(2, '0');
            var gS = Math.floor(globalTime % 60).toString().padStart(2, '0');
            
            headerDiv.innerHTML = '<span class="cue-time cue-time-range">' + gH + ':' + gM + ':' + gS +
                ' (' + Math.floor(firstCue.startTime) + 's-' + Math.floor(lastCue.startTime) + 's)</span>' +
                '<span class="cue-character">' + idpHtml + group.character + '</span>';
            headerDiv.appendChild(createDelGroupBtn(group));
            
            // Doble-click en nombre de personaje → cambiar personaje de toda la burbuja
            var charSpan = headerDiv.querySelector('.cue-character');
            charSpan.style.cursor = 'pointer';
            charSpan.title = 'Doble-click para cambiar personaje';
            (function(grp, el) {
                el.addEventListener('dblclick', function(e) {
                    e.stopPropagation();
                    if (!window.VCBYPerfiles || !window.VCBYPerfiles.isDirector()) return;
                    var chars = window.__characters || [{idp:'P00',name:'Música / Ambiente'}];
                    vcbySelectCharacter(chars, grp.idp).then(function(result) {
                        if (!result || result.idp === grp.idp) return;
                        var trackId = window.audioId.split('_')[0];
                        var indices = grp.cues.map(function(c) { return c._originalIndex; });
                        var fd = new URLSearchParams();
                        fd.append('track_id', trackId);
                        fd.append('cue_index', indices[0]);
                        fd.append('field', '_update_group_character');
                        fd.append('cue_indices', JSON.stringify(indices));
                        fd.append('character', result.character);
                        fd.append('idp', result.idp);
                        postAndReload(fd);
                    });
                });
            })(group, charSpan);
            
            groupDiv.appendChild(headerDiv);
            
            // Body: lineas como spans
            var bodyDiv = document.createElement('div');
            bodyDiv.className = 'cue-group-body';
            
            group.cues.forEach(function(cue, localIdx) {
                // Time tag con nudge ◂ ▸ (visible solo con time-edit-mode activo)
                var timeTag = document.createElement('span');
                timeTag.className = 'cue-line-time';
                
                var nudgeLeft = document.createElement('span');
                nudgeLeft.className = 'time-nudge';
                nudgeLeft.textContent = '◂';
                nudgeLeft.addEventListener('click', (function(c, container) {
                    return function(e) { e.stopPropagation(); nudgeTime(c, container, -0.1); };
                })(cue, timeTag));
                
                var timeVal = document.createElement('span');
                timeVal.className = 'time-val';
                timeVal.textContent = fmtTime(cue.startTime);
                timeVal.title = 'Click para editar valor';
                timeVal.addEventListener('click', (function(c, tag, valEl) {
                    return function(e) {
                        e.stopPropagation();
                        if (tag.querySelector('input')) return;
                        var oldTime = c.startTime;
                        var input = document.createElement('input');
                        input.type = 'text';
                        input.value = fmtTime(c.startTime);
                        input.className = 'cue-time-input';
                        input.placeholder = 'HH:MM:SS';
                        valEl.textContent = '';
                        valEl.appendChild(input);
                        input.focus();
                        input.select();
                        
                        function applyTime() {
                            var newTime = parseTime(input.value);
                            if (isNaN(newTime) || newTime === oldTime) {
                                valEl.textContent = fmtTime(c.startTime);
                                return;
                            }
                            applyTimeChange(c, newTime, valEl);
                        }
                        input.addEventListener('blur', applyTime);
                        input.addEventListener('keydown', function(ev) {
                            if (ev.key === 'Enter') { ev.preventDefault(); input.blur(); }
                            if (ev.key === 'Escape') { valEl.textContent = fmtTime(c.startTime); }
                        });
                    };
                })(cue, timeTag, timeVal));
                
                var nudgeRight = document.createElement('span');
                nudgeRight.className = 'time-nudge';
                nudgeRight.textContent = '▸';
                nudgeRight.addEventListener('click', (function(c, container) {
                    return function(e) { e.stopPropagation(); nudgeTime(c, container, +0.1); };
                })(cue, timeTag));
                
                timeTag.appendChild(nudgeLeft);
                timeTag.appendChild(timeVal);
                timeTag.appendChild(nudgeRight);
                bodyDiv.appendChild(timeTag);
                
                var lineSpan = document.createElement('span');
                lineSpan.className = 'cue-line cue-line-upcoming';
                lineSpan.id = 'cue-' + cue._originalIndex;
                lineSpan.setAttribute('data-cue-index', cue._originalIndex);
                lineSpan.innerHTML = cue.text;
                
                lineSpan.addEventListener('click', (function(c) {
                    return function() {
                        if (pendingClickTimeout) clearTimeout(pendingClickTimeout);
                        pendingClickTimeout = setTimeout(function() {
                            if (window._stampMode) {
                                // STAMP: fijar startTime de este cue al tiempo actual del audio
                                stampCueTime(c);
                            } else if (window.VCBYPerfiles && window.VCBYPerfiles.isDirector()) {
                                // DIRECTOR: solo posicionar, NO reproducir (evita scroll involuntario)
                                audioPlayer.currentTime = c.startTime;
                            } else {
                                // PUBLICO/ACTOR: saltar y reproducir
                                audioPlayer.currentTime = c.startTime;
                                if (audioPlayer.paused) audioPlayer.play();
                                isUserScrolling = false;
                            }
                        }, 400);
                    };
                })(cue));
                
                bodyDiv.appendChild(lineSpan);
                
                // Mini "+" y "🗑" entre líneas dentro del grupo (insert-mode)
                if (localIdx < group.cues.length - 1) {
                    bodyDiv.appendChild(document.createTextNode(' '));
                    bodyDiv.appendChild(createDupLineBtn(cue));
                }
                bodyDiv.appendChild(createDelLineBtn(cue));
            });
            
            groupDiv.appendChild(bodyDiv);
            scriptContainer.appendChild(groupDiv);
            
            // Director: boton "+" despues de cada grupo (visible solo en insert-mode)
            scriptContainer.appendChild(createDupGroupBtn(group));
        });
        
        audioPlayer.addEventListener('timeupdate', updateKaraoke);
        
        // Restaurar posición de audio tras CRUD reload
        var savedTime = sessionStorage.getItem('vcby_audio_time');
        if (savedTime !== null) {
            audioPlayer.currentTime = parseFloat(savedTime);
            var wasPaused = sessionStorage.getItem('vcby_audio_paused');
            if (wasPaused === '1') audioPlayer.pause();
            sessionStorage.removeItem('vcby_audio_time');
            sessionStorage.removeItem('vcby_audio_paused');
        }
    }
    
    function updateKaraoke() {
        var currentTime = audioPlayer.currentTime;
        var foundIdx = -1;
        
        for (var i = 0; i < scriptData.length; i++) {
            var cue = scriptData[i];
            if (cue.isNextAudio || cue.isPrevAudio) continue;
            
            var nextStartTime = (i + 1 < scriptData.length && !scriptData[i+1].isNextAudio && !scriptData[i+1].isPrevAudio)
                ? scriptData[i + 1].startTime : Infinity;
            
            if (currentTime >= cue.startTime && currentTime < nextStartTime) {
                foundIdx = i;
                break;
            }
        }
        
        if (foundIdx !== currentActiveIdx && foundIdx !== -1) {
            var activeCueIdx = scriptData[foundIdx]._originalIndex;
            
            // Actualizar todas las lineas
            var allLines = scriptContainer.querySelectorAll('.cue-line[data-cue-index]');
            for (var j = 0; j < allLines.length; j++) {
                var line = allLines[j];
                var lineIdx = parseInt(line.getAttribute('data-cue-index'));
                line.classList.remove('cue-line-active', 'cue-line-past', 'cue-line-upcoming');
                
                if (lineIdx < activeCueIdx) {
                    line.classList.add('cue-line-past');
                } else if (lineIdx === activeCueIdx) {
                    line.classList.add('cue-line-active');
                } else {
                    line.classList.add('cue-line-upcoming');
                }
            }
            
            // Actualizar grupos (burbuja activa)
            var allGroups = scriptContainer.querySelectorAll('.cue-group:not(.cue-external-audio)');
            for (var g = 0; g < allGroups.length; g++) {
                var grp = allGroups[g];
                var hasActive = grp.querySelector('.cue-line-active');
                var hasPast = grp.querySelector('.cue-line-past');
                var hasUpcoming = grp.querySelector('.cue-line-upcoming');
                
                grp.classList.remove('cue-group-active', 'cue-group-past', 'cue-group-upcoming');
                if (hasActive) {
                    grp.classList.add('cue-group-active');
                } else if (hasPast && !hasUpcoming) {
                    grp.classList.add('cue-group-past');
                } else if (!hasPast && hasUpcoming) {
                    grp.classList.add('cue-group-upcoming');
                } else if (hasPast) {
                    grp.classList.add('cue-group-past');
                }
            }
            
            // Scroll al span activo
            var activeLine = document.getElementById('cue-' + activeCueIdx);
            if (activeLine && !isUserScrolling) {
                var lineTop = activeLine.offsetTop;
                scriptContainer.scrollTo({
                    top: Math.max(0, lineTop - 60),
                    behavior: 'smooth'
                });
            }
            
            currentActiveIdx = foundIdx;
        }
    }

    // ===== DIRECTOR: Play/Pause =====
    window.togglePlayPause = function() {
        var audio = document.getElementById('audioPlayer');
        var btn = document.getElementById('btn-play-toggle');
        if (!audio) return;
        if (audio.paused) {
            audio.play();
            if (btn) { btn.textContent = '⏸'; btn.classList.add('btn-active'); }
        } else {
            audio.pause();
            if (btn) { btn.textContent = '▶'; btn.classList.remove('btn-active'); }
        }
    };

    // Sync button state with audio events
    (function() {
        var audio = document.getElementById('audioPlayer');
        if (!audio) return;
        audio.addEventListener('play', function() {
            var btn = document.getElementById('btn-play-toggle');
            if (btn) { btn.textContent = '⏸'; btn.classList.add('btn-active'); }
        });
        audio.addEventListener('pause', function() {
            var btn = document.getElementById('btn-play-toggle');
            if (btn) { btn.textContent = '▶'; btn.classList.remove('btn-active'); }
        });
    })();

    // ===== DIRECTOR: Toggle Marcas de Tiempo =====
    window.toggleTimeEdit = function() {
        document.body.classList.toggle('time-edit-mode');
        var btn = document.getElementById('btn-time-toggle');
        if (btn) {
            btn.classList.toggle('btn-active', document.body.classList.contains('time-edit-mode'));
        }
    };

    // ===== DIRECTOR: Stamp Mode (interno, sin UI) =====
    window._stampMode = false;
    window.toggleStampMode = function() {
        window._stampMode = !window._stampMode;
        document.body.classList.toggle('stamp-mode', window._stampMode);
    };

    // ===== DIRECTOR: Toggle Insertar Burbujas =====
    window.toggleInsertMode = function() {
        document.body.classList.toggle('insert-mode');
        var btn = document.getElementById('btn-insert-toggle');
        if (btn) {
            btn.classList.toggle('btn-active', document.body.classList.contains('insert-mode'));
        }
    };

    // ===== DIRECTOR: Helpers de tiempo =====
    function applyTimeChange(cue, newTime, displayEl) {
        cue.startTime = newTime;
        // Actualizar en scriptData buscando por cue_index
        for (var si = 0; si < scriptData.length; si++) {
            if (scriptData[si]._originalIndex === cue._originalIndex) {
                scriptData[si].startTime = newTime;
                break;
            }
        }
        if (displayEl) {
            displayEl.textContent = fmtTime(newTime);
            displayEl.classList.add('cue-time-saved');
            setTimeout(function() { displayEl.classList.remove('cue-time-saved'); }, 800);
        }
        if (!window._pendingTimeChanges) window._pendingTimeChanges = [];
        window._pendingTimeChanges.push({
            cue_index: cue._originalIndex,
            field: 'startTime',
            value: newTime
        });
        showCommitButton();
    }

    function nudgeTime(cue, timeTagEl, delta) {
        var newTime = Math.max(0, parseFloat((cue.startTime + delta).toFixed(1)));
        var valEl = timeTagEl.querySelector('.time-val');
        applyTimeChange(cue, newTime, valEl);
    }

    function stampCueTime(cue) {
        var newTime = parseFloat(audioPlayer.currentTime.toFixed(1));
        var lineEl = document.getElementById('cue-' + cue._originalIndex);
        var valEl = null;
        if (lineEl) {
            var prev = lineEl.previousElementSibling;
            while (prev) {
                if (prev.classList && prev.classList.contains('cue-line-time')) {
                    valEl = prev.querySelector('.time-val');
                    break;
                }
                prev = prev.previousElementSibling;
            }
            lineEl.classList.add('cue-line-stamped');
            setTimeout(function() { lineEl.classList.remove('cue-line-stamped'); }, 600);
        }
        applyTimeChange(cue, newTime, valEl);
    }

    // ===== DIRECTOR: Live time counter =====
    audioPlayer.addEventListener('timeupdate', function() {
        var display = document.getElementById('live-time-display');
        if (display) {
            var t = audioPlayer.currentTime;
            var m = Math.floor(t / 60).toString().padStart(2, '0');
            var s = Math.floor(t % 60).toString().padStart(2, '0');
            var ms = Math.floor((t % 1) * 10);
            display.textContent = m + ':' + s + '.' + ms;
        }
    });

    // ===== DIRECTOR: Edición In-Place =====
    
    // Doble-click en texto de subtitulo para editar (solo Director)
    scriptContainer.addEventListener('dblclick', function(e) {
        if (!window.VCBYPerfiles || !window.VCBYPerfiles.isDirector()) return;
        
        // Buscar el .cue-line mas cercano
        var textEl = e.target.closest('.cue-line');
        if (!textEl) return;
        
        // Ya esta en edicion?
        if (textEl.contentEditable === 'true') return;
        
        // Obtener indice del cue
        var cueIdx = parseInt(textEl.getAttribute('data-cue-index'));
        if (isNaN(cueIdx)) return;
        
        // Verificar que no sea cue externo
        if (textEl.closest('.cue-external-audio')) return;
        
        // Pausar audio durante la edición
        audioPlayer.pause();
        
        // Activar edición
        const originalText = textEl.innerHTML;
        textEl.contentEditable = 'true';
        textEl.classList.add('cue-editing');
        textEl.focus();
        
        // Forzar pegado como texto plano (sin estilos HTML)
        textEl.addEventListener('paste', function pasteHandler(e) {
            e.preventDefault();
            var text = (e.clipboardData || window.clipboardData).getData('text/plain');
            document.execCommand('insertText', false, text);
        });
        
        // Seleccionar todo el texto
        const range = document.createRange();
        range.selectNodeContents(textEl);
        const sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(range);
        
        // Guardar al perder foco o Enter
        function saveEdit() {
            textEl.contentEditable = 'false';
            textEl.classList.remove('cue-editing');
            
            const newText = textEl.innerText.trim();
            if (newText !== originalText.replace(/<[^>]*>/g, '').trim()) {
                // Actualizar en scriptData local
                for (var si = 0; si < scriptData.length; si++) {
                    if (scriptData[si]._originalIndex === cueIdx) {
                        scriptData[si].text = newText;
                        break;
                    }
                }
                
                // Enviar al servidor
                const trackId = window.audioId.split('_')[0];
                const formData = new URLSearchParams();
                formData.append('track_id', trackId);
                formData.append('cue_index', cueIdx);
                formData.append('field', 'text');
                formData.append('value', newText);
                
                fetch((window.apiBase||'') + 'save_changes.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: formData.toString()
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.ok) {
                        // Mostrar feedback breve
                        textEl.style.transition = 'background 0.3s';
                        textEl.style.background = 'rgba(39, 174, 96, 0.15)';
                        setTimeout(function() { textEl.style.background = ''; }, 1000);
                        // Mostrar botón commit
                        showCommitButton();
                    } else {
                        textEl.innerHTML = originalText;
                        vcbyAlert('Error: ' + (data.msg || 'No se pudo guardar'), 'error');
                    }
                })
                .catch(function(err) {
                    textEl.innerHTML = originalText;
                    vcbyAlert('Error: ' + (err.message || err), 'error');
                });
            }
            
            textEl.removeEventListener('blur', saveEdit);
            textEl.removeEventListener('keydown', handleKeys);
        }
        
        function handleKeys(ev) {
            if (ev.key === 'Enter' && !ev.shiftKey) {
                ev.preventDefault();
                textEl.blur();
            }
            if (ev.key === 'Escape') {
                textEl.innerHTML = originalText;
                textEl.blur();
            }
        }
        
        textEl.addEventListener('blur', saveEdit);
        textEl.addEventListener('keydown', handleKeys);
    });
    
    // ===== DIRECTOR: Botón flotante de commit =====
    let pendingEdits = 0;
    
    function showCommitButton() {
        pendingEdits++;
        let commitBar = document.getElementById('director-commit-bar');
        if (!commitBar) {
            commitBar = document.createElement('div');
            commitBar.id = 'director-commit-bar';
            commitBar.className = 'director-commit-bar';
            commitBar.innerHTML = 
                '<span class="commit-count"></span>' +
                '<button class="btn-commit" onclick="directorCommit()">💾 Guardar cambios</button>';
            document.body.appendChild(commitBar);
        }
        commitBar.querySelector('.commit-count').textContent = pendingEdits + ' cambio' + (pendingEdits > 1 ? 's' : '') + ' pendiente' + (pendingEdits > 1 ? 's' : '');
        commitBar.style.display = 'flex';
    }
    
    window.directorCommit = function() {
        var trackId = window.audioId.split('_')[0];
        var pending = window._pendingTimeChanges || [];
        
        if (pending.length === 0) {
            vcbyAlert('No hay cambios pendientes', 'info');
            return;
        }
        
        // Enviar cada cambio pendiente al SQLite
        var chain = Promise.resolve();
        var saved = 0;
        pending.forEach(function(change) {
            chain = chain.then(function() {
                var fd = new URLSearchParams();
                fd.append('track_id', trackId);
                fd.append('cue_index', change.cue_index);
                fd.append('field', change.field);
                fd.append('value', change.value);
                return fetch((window.apiBase||'') + 'save_changes.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: fd.toString()
                }).then(function(r) { return r.json(); })
                .then(function(data) { if (data.ok) saved++; });
            });
        });
        
        chain.then(function() {
            pendingEdits = 0;
            window._pendingTimeChanges = [];
            var bar = document.getElementById('director-commit-bar');
            if (bar) bar.style.display = 'none';
            vcbyAlert('✅ ' + saved + ' cambio' + (saved > 1 ? 's' : '') + ' guardado' + (saved > 1 ? 's' : ''), 'success');
        })
        .catch(function(err) {
            vcbyAlert('Error: ' + (err.message || err), 'error');
        });
    };
});
