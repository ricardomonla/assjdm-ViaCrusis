// jss/perfiles.js — Gestión de Perfiles (Público / Director)
// Parte de Plan 08, Fase 4.1

(function() {
    'use strict';

    const PERFIL_KEY = 'vcby_perfil';
    const DIRECTOR_TTL = 60 * 60 * 1000; // 60 minutos en ms
    const ADMIN_KEY_LEGACY = 'vcby_admin'; // Backward compat

    // ===== API Pública =====

    window.VCBYPerfiles = {
        isDirector: isDirector,
        getPerfil: getPerfil,
        activarDirector: activarDirector,
        cerrarSesion: cerrarSesion
    };

    // ===== Core =====

    function getPerfil() {
        const session = localStorage.getItem(PERFIL_KEY);
        if (!session) return 'publico';

        try {
            const data = JSON.parse(session);
            if (Date.now() - data.ts < DIRECTOR_TTL && data.perfil === 'director') {
                return 'director';
            } else if (data.perfil === 'director') {
                // TTL expirado
                localStorage.removeItem(PERFIL_KEY);
                return 'publico';
            }
            return data.perfil || 'publico';
        } catch (e) {
            localStorage.removeItem(PERFIL_KEY);
            return 'publico';
        }
    }

    function isDirector() {
        return getPerfil() === 'director';
    }

    function activarDirector() {
        localStorage.setItem(PERFIL_KEY, JSON.stringify({
            perfil: 'director',
            ts: Date.now()
        }));
        // Backward compat: también setear el legacy para admin-only CSS
        localStorage.setItem(ADMIN_KEY_LEGACY, JSON.stringify({ ts: Date.now() }));
        aplicarPerfil();
    }

    function cerrarSesion() {
        localStorage.removeItem(PERFIL_KEY);
        localStorage.removeItem(ADMIN_KEY_LEGACY);
        aplicarPerfil();
        location.reload();
    }

    // ===== UI =====

    function aplicarPerfil() {
        const perfil = getPerfil();

        if (perfil === 'director') {
            document.body.classList.add('director-mode', 'admin-mode');
            document.body.classList.remove('public-mode');
        } else {
            document.body.classList.add('public-mode');
            document.body.classList.remove('director-mode', 'admin-mode');
        }

        // Actualizar indicador visual en el header
        actualizarIndicadorHeader(perfil);
        // Actualizar botón logout
        actualizarLogout(perfil);
    }

    function actualizarIndicadorHeader(perfil) {
        let indicator = document.getElementById('perfil-indicator');
        
        if (perfil === 'director') {
            if (!indicator) {
                indicator = document.createElement('span');
                indicator.id = 'perfil-indicator';
                indicator.className = 'perfil-indicator';
                const header = document.querySelector('.header h1');
                if (header) header.appendChild(indicator);
            }
            indicator.textContent = ' 🎬';
            indicator.title = 'Modo Director activo';
            indicator.style.display = 'inline';
        } else {
            if (indicator) indicator.style.display = 'none';
        }
    }

    function actualizarLogout(perfil) {
        const btn = document.getElementById('admin-logout');
        if (btn) {
            if (perfil === 'director') {
                btn.style.display = 'inline-block';
                btn.textContent = '🎬 Salir';
                // Re-bindear onclick para usar nuestro cerrarSesion
                btn.onclick = function() { cerrarSesion(); };
            } else {
                btn.style.display = 'none';
            }
        }
    }

    // ===== Inicialización automática =====

    // Aplicar perfil inmediatamente (antes de DOMContentLoaded para evitar flash)
    aplicarPerfil();

    // Re-aplicar después del DOM completo (para indicadores que necesitan el header)
    document.addEventListener('DOMContentLoaded', function() {
        aplicarPerfil();
    });

})();
