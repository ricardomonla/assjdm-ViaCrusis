// jss/modal.js — Sistema de Modal Inline (reemplazo de alert/prompt nativos)
// Evita popups del navegador que rompen la armonía del sitio.

(function() {
    'use strict';

    // Crear el contenedor del modal una sola vez
    var overlay = document.createElement('div');
    overlay.id = 'vcby-modal-overlay';
    overlay.className = 'vcby-modal-overlay';
    overlay.innerHTML =
        '<div class="vcby-modal" id="vcby-modal">' +
            '<div class="vcby-modal-icon" id="vcby-modal-icon"></div>' +
            '<div class="vcby-modal-msg" id="vcby-modal-msg"></div>' +
            '<input type="text" class="vcby-modal-input" id="vcby-modal-input" style="display:none" autocomplete="off">' +
            '<div class="vcby-modal-buttons" id="vcby-modal-buttons"></div>' +
        '</div>';
    document.body.appendChild(overlay);

    // Click fuera del modal = cancelar
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) closeModal(null);
    });

    var _resolve = null;

    function openModal() {
        overlay.classList.add('active');
    }

    function closeModal(value) {
        overlay.classList.remove('active');
        if (_resolve) {
            var r = _resolve;
            _resolve = null;
            r(value);
        }
    }

    // ===== API Pública =====

    /**
     * vcbyAlert — Reemplazo de alert()
     * @param {string} msg - Mensaje a mostrar
     * @param {string} [type] - 'success' | 'error' | 'info' (default: 'info')
     * @returns {Promise} - Se resuelve cuando el usuario cierra
     */
    window.vcbyAlert = function(msg, type) {
        type = type || 'info';
        var icons = { success: '✅', error: '❌', info: 'ℹ️' };
        var modal = document.getElementById('vcby-modal');
        modal.className = 'vcby-modal vcby-modal-' + type;
        document.getElementById('vcby-modal-icon').textContent = icons[type] || icons.info;
        document.getElementById('vcby-modal-msg').textContent = msg;
        document.getElementById('vcby-modal-input').style.display = 'none';
        document.getElementById('vcby-modal-buttons').innerHTML =
            '<button class="vcby-modal-btn vcby-modal-btn-ok" id="vcby-modal-ok">Aceptar</button>';

        document.getElementById('vcby-modal-ok').onclick = function() { closeModal(true); };
        openModal();

        return new Promise(function(resolve) { _resolve = resolve; });
    };

    /**
     * vcbyPrompt — Reemplazo de prompt()
     * @param {string} msg - Mensaje/pregunta
     * @param {string} [defaultVal] - Valor por defecto del input
     * @param {string} [icon] - Emoji para el icono (default: '✏️')
     * @returns {Promise<string|null>} - Valor ingresado o null si cancela
     */
    window.vcbyPrompt = function(msg, defaultVal, icon) {
        var modal = document.getElementById('vcby-modal');
        modal.className = 'vcby-modal vcby-modal-info';
        document.getElementById('vcby-modal-icon').textContent = icon || '✏️';
        document.getElementById('vcby-modal-msg').textContent = msg;

        var input = document.getElementById('vcby-modal-input');
        input.style.display = '';
        input.value = defaultVal || '';
        input.type = 'text';

        document.getElementById('vcby-modal-buttons').innerHTML =
            '<button class="vcby-modal-btn vcby-modal-btn-ok" id="vcby-modal-ok">Aceptar</button>' +
            '<button class="vcby-modal-btn vcby-modal-btn-cancel" id="vcby-modal-cancel">Cancelar</button>';

        document.getElementById('vcby-modal-ok').onclick = function() {
            closeModal(input.value);
        };
        document.getElementById('vcby-modal-cancel').onclick = function() {
            closeModal(null);
        };

        // Enter = aceptar, Escape = cancelar
        input.onkeydown = function(e) {
            if (e.key === 'Enter') { e.preventDefault(); closeModal(input.value); }
            if (e.key === 'Escape') { closeModal(null); }
        };

        openModal();
        setTimeout(function() { input.focus(); input.select(); }, 100);

        return new Promise(function(resolve) { _resolve = resolve; });
    };

    /**
     * vcbyConfirm — Reemplazo de confirm()
     * @param {string} msg - Pregunta de confirmación
     * @param {string} [icon] - Emoji (default: '⚠️')
     * @returns {Promise<boolean>}
     */
    window.vcbyConfirm = function(msg, icon) {
        var modal = document.getElementById('vcby-modal');
        modal.className = 'vcby-modal vcby-modal-info';
        document.getElementById('vcby-modal-icon').textContent = icon || '⚠️';
        document.getElementById('vcby-modal-msg').textContent = msg;
        document.getElementById('vcby-modal-input').style.display = 'none';

        document.getElementById('vcby-modal-buttons').innerHTML =
            '<button class="vcby-modal-btn vcby-modal-btn-ok" id="vcby-modal-ok">Sí</button>' +
            '<button class="vcby-modal-btn vcby-modal-btn-cancel" id="vcby-modal-cancel">No</button>';

        document.getElementById('vcby-modal-ok').onclick = function() { closeModal(true); };
        document.getElementById('vcby-modal-cancel').onclick = function() { closeModal(false); };

        openModal();
        return new Promise(function(resolve) { _resolve = resolve; });
    };
    /**
     * vcbyInsertCue — Modal para insertar burbuja con selector de personaje
     * @param {Array} characters - [{idp:'P01',name:'NARRADOR'}, ...]
     * @param {Object} [prevCue] - Cue anterior para opción "Duplicar" {idp, character, text}
     * @returns {Promise<{idp,character,text}|null>}
     */
    window.vcbyInsertCue = function(characters, prevCue) {
        var modal = document.getElementById('vcby-modal');
        modal.className = 'vcby-modal vcby-modal-info';
        document.getElementById('vcby-modal-icon').textContent = '🎭';
        
        // Construir select de personajes
        var msgDiv = document.getElementById('vcby-modal-msg');
        msgDiv.innerHTML = '<div style="text-align:left;font-size:0.9em;margin-bottom:8px;">Insertar línea después:</div>';
        
        var sel = document.createElement('select');
        sel.id = 'vcby-insert-char';
        sel.style.cssText = 'width:100%;padding:8px;margin-bottom:8px;border-radius:6px;border:1px solid #555;background:#2a2520;color:#e8dcc8;font-size:0.95em;';
        (characters || []).forEach(function(c) {
            var opt = document.createElement('option');
            opt.value = c.idp + '|' + c.name;
            opt.textContent = c.idp + ' — ' + c.name;
            if (prevCue && c.idp === prevCue.idp) opt.selected = true;
            sel.appendChild(opt);
        });
        msgDiv.appendChild(sel);
        
        // Input de texto
        var input = document.getElementById('vcby-modal-input');
        input.style.display = '';
        input.value = '';
        input.type = 'text';
        input.placeholder = 'Texto de la línea...';

        // Botones: Duplicar (si hay prevCue) + Insertar + Cancelar
        var btnsHtml = '';
        if (prevCue && prevCue.text) {
            btnsHtml += '<button class="vcby-modal-btn vcby-modal-btn-dup" id="vcby-modal-dup" title="Copiar personaje y texto de la línea anterior">📋 Duplicar</button>';
        }
        btnsHtml += '<button class="vcby-modal-btn vcby-modal-btn-ok" id="vcby-modal-ok">Insertar</button>';
        btnsHtml += '<button class="vcby-modal-btn vcby-modal-btn-cancel" id="vcby-modal-cancel">Cancelar</button>';
        document.getElementById('vcby-modal-buttons').innerHTML = btnsHtml;

        function getResult() {
            var selVal = sel.value.split('|');
            var text = input.value.trim();
            if (!text) return null;
            return { idp: selVal[0], character: selVal[1], text: text };
        }

        // Duplicar: pre-llena campos y hace foco en input para editar
        if (prevCue && prevCue.text) {
            document.getElementById('vcby-modal-dup').onclick = function() {
                sel.value = prevCue.idp + '|' + prevCue.character;
                input.value = prevCue.text.replace(/<[^>]*>/g, ''); // strip HTML
                input.focus();
                input.select();
            };
        }

        document.getElementById('vcby-modal-ok').onclick = function() { closeModal(getResult()); };
        document.getElementById('vcby-modal-cancel').onclick = function() { closeModal(null); };

        input.onkeydown = function(e) {
            if (e.key === 'Enter') { e.preventDefault(); closeModal(getResult()); }
            if (e.key === 'Escape') { closeModal(null); }
        };

        openModal();
        setTimeout(function() { input.focus(); }, 100);

        return new Promise(function(resolve) { _resolve = resolve; });
    };

})();
