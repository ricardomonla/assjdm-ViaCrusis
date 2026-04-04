<?php
/**
 * casting/index.php — Página de Personajes
 * - Público: ve personajes habilitados y postulados
 * - Director: ve todos, toggle habilitar/deshabilitar, teléfonos, eliminar
 * 
 * Director se detecta client-side via VCBYPerfiles (localStorage).
 * Los datos se cargan via JS para que el Director vea todo.
 */
require __DIR__ . '/../data/db.php';
// Pre-cargar personajes para SSR (sin info de Director, eso va por JS)
$characters = getCharacters();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personajes — Via Crusis BY2026</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .pj-page { max-width: 700px; margin: 0 auto; padding: 16px; font-family: Georgia, 'Times New Roman', serif; }
        .pj-title { text-align: center; font-size: 1.3em; color: #2d2418; margin-bottom: 4px; }
        .pj-subtitle { text-align: center; font-size: 0.85em; color: #8a7b68; margin-bottom: 20px; }
        .pj-card {
            background: #fffdf8;
            border: 1px solid rgba(196, 180, 148, 0.4);
            border-radius: 10px;
            margin-bottom: 12px;
            overflow: hidden;
            transition: box-shadow 0.2s ease;
        }
        .pj-card:hover { box-shadow: 0 2px 12px rgba(128, 109, 90, 0.15); }
        .pj-card.pj-disabled { opacity: 0.45; background: #f5f3ef; }
        .pj-card.pj-disabled:hover { opacity: 0.7; }
        .pj-header {
            display: flex; justify-content: space-between; align-items: center;
            padding: 10px 14px;
            background: linear-gradient(135deg, #f5f0e6, #ede6d6);
            border-bottom: 1px solid rgba(196, 180, 148, 0.3);
        }
        .pj-info { display: flex; align-items: center; gap: 8px; flex: 1; }
        .pj-idp {
            font-family: 'Courier New', monospace; font-size: 0.65em;
            background: rgba(139, 0, 0, 0.08); color: #8b0000;
            padding: 2px 5px; border-radius: 4px; font-weight: bold;
        }
        .pj-name { font-weight: bold; font-variant: small-caps; font-size: 1em; color: #2d2418; }
        .pj-synopsis { font-size: 0.75em; color: #a09080; font-style: italic; margin-left: 2px; }
        .pj-count { font-size: 0.7em; color: #b0a693; background: rgba(0,0,0,0.04); padding: 2px 6px; border-radius: 8px; }
        .pj-actions { display: flex; gap: 6px; align-items: center; }
        .pj-btn-add {
            background: #4a8c3f; color: white; border: none;
            border-radius: 50%; width: 28px; height: 28px;
            font-size: 16px; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: all 0.2s ease;
        }
        .pj-btn-add:hover { background: #3a7030; transform: scale(1.15); }
        .pj-toggle {
            background: none; border: 1px solid #d4c9b5;
            border-radius: 6px; padding: 3px 8px;
            cursor: pointer; font-size: 0.75em; color: #8a7b68; transition: all 0.2s;
        }
        .pj-toggle.enabled { background: #e8f5e9; border-color: #4caf50; color: #2e7d32; }
        .pj-toggle:hover { transform: scale(1.05); }
        .pj-body { padding: 0; }
        .pj-empty { padding: 8px 14px; color: #b0a693; font-style: italic; font-size: 0.85em; }
        .pj-person {
            display: flex; justify-content: space-between; align-items: center;
            padding: 7px 14px; border-bottom: 1px solid rgba(196, 180, 148, 0.15); font-size: 0.9em;
        }
        .pj-person:last-child { border-bottom: none; }
        .pj-person-name { color: #2d2418; font-weight: 500; }
        .pj-person-phone { font-family: 'Courier New', monospace; font-size: 0.8em; color: #8a7b68; text-decoration: none; }
        .pj-person-del { background: none; border: none; cursor: pointer; font-size: 14px; opacity: 0.4; transition: all 0.2s; }
        .pj-person-del:hover { opacity: 1; transform: scale(1.2); }
        /* Modal */
        .pj-overlay {
            display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;
        }
        .pj-overlay.active { display: flex; }
        .pj-modal {
            background: #fffdf8; border-radius: 12px; padding: 20px;
            width: 90%; max-width: 360px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.25); animation: pjSlide 0.2s ease;
        }
        @keyframes pjSlide { from { transform: translateY(-20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .pj-modal h3 { margin: 0 0 4px; font-size: 1.1em; color: #2d2418; }
        .pj-modal .modal-sub { font-size: 0.85em; color: #8a7b68; margin-bottom: 14px; }
        .pj-modal label { display: block; font-size: 0.8em; color: #5a4b3a; margin: 10px 0 3px; }
        .pj-modal input {
            width: 100%; padding: 8px 10px; border: 1px solid #d4c9b5; border-radius: 6px;
            font-size: 0.95em; font-family: inherit; background: #fff; box-sizing: border-box;
        }
        .pj-modal input:focus { outline: none; border-color: #8b0000; box-shadow: 0 0 0 2px rgba(139,0,0,0.1); }
        .pj-modal-btns { display: flex; gap: 8px; margin-top: 16px; justify-content: flex-end; }
        .pj-modal-btns button { padding: 8px 16px; border-radius: 6px; border: none; cursor: pointer; font-size: 0.9em; font-family: inherit; }
        .pj-btn-cancel { background: #e8e0d0; color: #5a4b3a; }
        .pj-btn-submit { background: #4a8c3f; color: white; font-weight: 600; }
        .pj-btn-submit:hover { background: #3a7030; }
        .pj-back { display: inline-block; margin-bottom: 12px; color: #8a7b68; text-decoration: none; font-size: 0.9em; }
        .pj-back:hover { color: #2d2418; }
        .pj-msg { padding: 10px; border-radius: 6px; margin-bottom: 12px; font-size: 0.85em; display: none; }
        .pj-msg.success { background: #e8f5e9; color: #2e7d32; display: block; }
        .pj-msg.error { background: #fce4ec; color: #c62828; display: block; }
    </style>
</head>
<body>
<header class="header">
    <h1>Via Crusis<br>Barrio Yacampiz - 2026</h1>
</header>

<main class="pj-page">
    <a href="../audios/index.php" class="pj-back">← Volver a audios</a>
    <h2 class="pj-title">🎭 Personajes</h2>
    <p class="pj-subtitle">Postulate para interpretar un personaje. ¡Todos son bienvenidos!</p>
    
    <div id="pj-msg" class="pj-msg"></div>
    <div id="pj-container">Cargando personajes...</div>
</main>

<!-- Modal -->
<div class="pj-overlay" id="signup-modal">
    <div class="pj-modal">
        <h3>Postularme</h3>
        <div class="modal-sub" id="modal-char"></div>
        <form id="signup-form" onsubmit="submitSignup(event)">
            <input type="hidden" id="s-idp">
            <input type="hidden" id="s-char">
            <label>Nombre</label>
            <input type="text" id="s-nombre" required placeholder="Tu nombre" autocomplete="given-name">
            <label>Apellido</label>
            <input type="text" id="s-apellido" required placeholder="Tu apellido" autocomplete="family-name">
            <label>Teléfono (privado, solo para coordinación)</label>
            <input type="tel" id="s-telefono" required placeholder="Ej: 3855-123456" autocomplete="tel">
            <div class="pj-modal-btns">
                <button type="button" class="pj-btn-cancel" onclick="closeSignup()">Cancelar</button>
                <button type="submit" class="pj-btn-submit">Postularme</button>
            </div>
        </form>
    </div>
</div>

<!-- Scripts -->
<script src="../jss/perfiles.js"></script>
<script>
var isAdmin = false;

// Detectar Director desde localStorage (mismo sistema que el resto del sitio)
(function() {
    try {
        var data = JSON.parse(localStorage.getItem('vcby_perfil') || '{}');
        if (data.perfil === 'director' && Date.now() - data.ts < 3600000) {
            isAdmin = true;
        }
    } catch(e) {}
})();

function loadPersonajes() {
    var url = 'api.php?director=' + (isAdmin ? '1' : '0');
    fetch(url)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.ok) { document.getElementById('pj-container').textContent = 'Error.'; return; }
            renderPersonajes(data.characters, data.castings, data.enabled);
        })
        .catch(function() { document.getElementById('pj-container').textContent = 'Error de conexión.'; });
}

function renderPersonajes(characters, castings, enabled) {
    var html = '';
    for (var i = 0; i < characters.length; i++) {
        var ch = characters[i];
        var idp = ch.idp;
        var name = ch.character;
        var synopsis = ch.synopsis || '';
        var isEnabled = enabled[idp] == 1;
        var posts = castings[idp] || [];
        var count = posts.length;
        
        // Público: solo ver habilitados o con postulados
        if (!isAdmin && !isEnabled && count === 0) continue;
        
        var disabledClass = (!isEnabled && isAdmin) ? ' pj-disabled' : '';
        html += '<div class="pj-card' + disabledClass + '" id="card-' + idp + '">';
        html += '<div class="pj-header"><div class="pj-info">';
        html += '<span class="pj-idp">' + idp + '</span>';
        html += '<span class="pj-name">' + escHtml(name) + '</span>';
        if (synopsis) html += '<span class="pj-synopsis"> — ' + escHtml(synopsis) + '</span>';
        if (count > 0) html += '<span class="pj-count">' + count + '</span>';
        html += '</div><div class="pj-actions">';
        
        if (isAdmin) {
            html += '<button class="pj-toggle ' + (isEnabled ? 'enabled' : '') + '" onclick="toggleChar(\'' + idp + '\',' + (isEnabled ? 0 : 1) + ')">';
            html += isEnabled ? '✅ Abierto' : '⬜ Cerrado';
            html += '</button>';
        }
        if (isEnabled || isAdmin) {
            html += '<button class="pj-btn-add" onclick="openSignup(\'' + idp + '\',\'' + escAttr(name) + '\')" title="Postularme">+</button>';
        }
        html += '</div></div>';
        html += '<div class="pj-body">';
        
        if (count === 0) {
            html += '<div class="pj-empty">' + (isEnabled ? 'Sin postulados aún — ¡sé el primero!' : 'Sin postulados') + '</div>';
        } else {
            for (var j = 0; j < posts.length; j++) {
                var p = posts[j];
                html += '<div class="pj-person">';
                html += '<span class="pj-person-name">' + escHtml(p.nombre + ' ' + p.apellido) + '</span><span>';
                if (isAdmin && p.telefono) {
                    html += '<a href="tel:' + escAttr(p.telefono) + '" class="pj-person-phone">📞 ' + escHtml(p.telefono) + '</a> ';
                }
                if (isAdmin) {
                    html += '<button class="pj-person-del" onclick="deleteSignup(' + p.id + ')" title="Eliminar">🗑</button>';
                }
                html += '</span></div>';
            }
        }
        html += '</div></div>';
    }
    
    if (!html) html = '<div class="pj-empty">No hay personajes disponibles aún.</div>';
    document.getElementById('pj-container').innerHTML = html;
}

function escHtml(s) { var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
function escAttr(s) { return s.replace(/'/g, "\\'").replace(/"/g, '&quot;'); }

function openSignup(idp, name) {
    document.getElementById('s-idp').value = idp;
    document.getElementById('s-char').value = name;
    document.getElementById('modal-char').textContent = idp + ' — ' + name;
    document.getElementById('s-nombre').value = '';
    document.getElementById('s-apellido').value = '';
    document.getElementById('s-telefono').value = '';
    document.getElementById('signup-modal').classList.add('active');
    setTimeout(function() { document.getElementById('s-nombre').focus(); }, 100);
}
function closeSignup() { document.getElementById('signup-modal').classList.remove('active'); }
document.getElementById('signup-modal').addEventListener('click', function(e) { if (e.target === this) closeSignup(); });

function showMsg(text, type) {
    var el = document.getElementById('pj-msg');
    el.textContent = text; el.className = 'pj-msg ' + type;
    setTimeout(function() { el.style.display = 'none'; }, 4000);
}

function submitSignup(e) {
    e.preventDefault();
    var fd = new FormData();
    fd.append('action', 'signup');
    fd.append('idp', document.getElementById('s-idp').value);
    fd.append('character_name', document.getElementById('s-char').value);
    fd.append('nombre', document.getElementById('s-nombre').value.trim());
    fd.append('apellido', document.getElementById('s-apellido').value.trim());
    fd.append('telefono', document.getElementById('s-telefono').value.trim());
    fetch('api.php', { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(d) { if (d.ok) { closeSignup(); showMsg(d.msg, 'success'); loadPersonajes(); } else { showMsg(d.msg, 'error'); } })
        .catch(function() { showMsg('Error de conexión.', 'error'); });
}

function deleteSignup(id) {
    if (!confirm('¿Eliminar esta postulación?')) return;
    var fd = new FormData();
    fd.append('action', 'delete');
    fd.append('id', id);
    fd.append('key', 'VCBY2026');
    fetch('api.php', { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(d) { if (d.ok) { showMsg(d.msg, 'success'); loadPersonajes(); } else { showMsg(d.msg, 'error'); } })
        .catch(function() { showMsg('Error de conexión.', 'error'); });
}

function toggleChar(idp, enabled) {
    var fd = new FormData();
    fd.append('action', 'toggle');
    fd.append('idp', idp);
    fd.append('enabled', enabled);
    fd.append('key', 'VCBY2026');
    fetch('api.php', { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(d) { if (d.ok) { loadPersonajes(); } else { showMsg(d.msg, 'error'); } })
        .catch(function() { showMsg('Error de conexión.', 'error'); });
}

// Cargar al inicio
loadPersonajes();
</script>
</body>
</html>
