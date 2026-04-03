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

})();
