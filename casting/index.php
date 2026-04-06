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
// Pre-cargar personajes para SSR (puede fallar en Android/Termux)
try {
    $characters = getCharacters();
} catch (Exception $e) {
    $characters = [];
}
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
        .pj-synopsis { font-size: 0.85em; color: #6b5d4f; font-style: italic; margin-left: 2px; }
        .pj-count { font-size: 0.7em; color: #b0a693; background: rgba(0,0,0,0.04); padding: 2px 6px; border-radius: 8px; }
        .pj-actions { display: flex; gap: 6px; align-items: center; }
        .pj-editable:hover { background: rgba(139, 115, 85, 0.12); border-radius: 4px; padding: 1px 3px; cursor: pointer; }
        .pj-inline-input {
            font-size: inherit; font-family: inherit; border: 1px solid #c4b494;
            border-radius: 4px; padding: 2px 6px; background: #fff;
            outline: none; min-width: 60px; max-width: 100%;
        }
        .pj-inline-input:focus { border-color: #8b7355; box-shadow: 0 0 4px rgba(139,115,85,0.3); }
        .pj-person-actions { display: inline-flex; gap: 4px; align-items: center; margin-left: 8px; }
        .pj-person-apellido { font-weight: bold; }
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
var isReadonly = false;

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
            isReadonly = data.readonly || false;
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
        
        // Readonly (Android): mostrar todos, sin filtro
        if (!isReadonly && !isAdmin && !isEnabled && count === 0) continue;
        
        var disabledClass = (!isEnabled && isAdmin) ? ' pj-disabled' : '';
        html += '<div class="pj-card' + disabledClass + '" id="card-' + idp + '">';
        html += '<div class="pj-header"><div class="pj-info">';
        html += '<span class="pj-idp">' + idp + '</span>';
        if (isAdmin) {
            html += '<span class="pj-name pj-editable" data-edit-type="character" data-idp="' + idp + '" data-field="name" title="Doble-click para editar">' + escHtml(name) + '</span>';
            html += synopsis 
                ? '<span class="pj-synopsis pj-editable" data-edit-type="character" data-idp="' + idp + '" data-field="synopsis" title="Doble-click para editar"> — ' + escHtml(synopsis) + '</span>'
                : '<span class="pj-synopsis pj-editable" data-edit-type="character" data-idp="' + idp + '" data-field="synopsis" title="Doble-click para agregar synopsis"> — (sin synopsis)</span>';
        } else {
            html += '<span class="pj-name">' + escHtml(name) + '</span>';
            if (synopsis) html += '<span class="pj-synopsis"> — ' + escHtml(synopsis) + '</span>';
        }
        if (count > 0) html += '<span class="pj-count">' + count + '</span>';
        html += '</div><div class="pj-actions">';
        
        if (isAdmin) {
            html += '<button class="pj-toggle ' + (isEnabled ? 'enabled' : '') + '" onclick="toggleChar(\'' + idp + '\',' + (isEnabled ? 0 : 1) + ')">';
            html += isEnabled ? '✅ Abierto' : '⬜ Cerrado';
            html += '</button>';
        }
        if (!isReadonly && (isEnabled || isAdmin)) {
            html += '<button class="pj-btn-add" onclick="openSignup(\'' + idp + '\',\'' + escAttr(name) + '\')" title="Postularme">+</button>';
        }
        html += '</div></div>';
        html += '<div class="pj-body">';
        
        if (count === 0) {
            html += '<div class="pj-empty">' + (isEnabled ? 'Sin postulados aún — ¡sé el primero!' : 'Sin postulados') + '</div>';
        } else {
            for (var j = 0; j < posts.length; j++) {
                var p = posts[j];
                html += '<div class="pj-person" data-person-id="' + p.id + '">';
                if (isAdmin) {
                    html += '<span class="pj-person-name pj-editable" data-edit-type="casting" data-id="' + p.id + '" data-field="nombre" title="Doble-click para editar">' + fmtNombre(p.nombre) + '</span>';
                    html += ' <span class="pj-person-apellido pj-editable" data-edit-type="casting" data-id="' + p.id + '" data-field="apellido" title="Doble-click para editar">' + escHtml(p.apellido).toUpperCase() + '</span>';
                    html += '<span class="pj-person-actions">';
                    if (p.telefono) {
                        html += '<span class="pj-person-phone pj-editable" data-edit-type="casting" data-id="' + p.id + '" data-field="telefono" title="Doble-click para editar">📞 ' + escHtml(p.telefono) + '</span> ';
                    }
                    html += '<button class="pj-person-del" onclick="deleteSignup(' + p.id + ')" title="Eliminar">🗑</button>';
                    html += '</span>';
                } else {
                    html += '<span class="pj-person-name">' + fmtNombre(p.nombre) + '</span>';
                    html += ' <span class="pj-person-apellido">' + escHtml(p.apellido).toUpperCase() + '</span>';
                }
                html += '</div>';
            }
        }
        html += '</div></div>';
    }
    
    if (!html) html = '<div class="pj-empty">No hay personajes disponibles aún.</div>';
    document.getElementById('pj-container').innerHTML = html;
    
    // Activar doble-click para edición en modo Director
    if (isAdmin) {
        document.querySelectorAll('.pj-editable').forEach(function(el) {
            el.style.cursor = 'pointer';
            el.addEventListener('dblclick', function(e) {
                e.stopPropagation();
                startInlineEdit(el);
            });
        });
    }
}

function escHtml(s) { var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
function escAttr(s) { return s.replace(/'/g, "\\'").replace(/"/g, '&quot;'); }
function fmtNombre(s) { return escHtml(s).replace(/\b\w/g, function(c) { return c.toUpperCase(); }); }

function startInlineEdit(el) {
    if (el.querySelector('input')) return;
    var raw = el.textContent.replace(/^[\s—📞]+/, '').trim();
    var input = document.createElement('input');
    input.type = 'text';
    input.value = raw;
    input.className = 'pj-inline-input';
    el.textContent = '';
    el.appendChild(input);
    input.focus();
    input.select();
    
    function save() {
        var val = input.value.trim();
        if (!val || val === raw) { loadData(); return; }
        var editType = el.dataset.editType;
        var fd = new URLSearchParams();
        fd.append('key', 'VCBY2026');
        fd.append('value', val);
        fd.append('field', el.dataset.field);
        if (editType === 'character') {
            fd.append('action', 'update_character');
            fd.append('idp', el.dataset.idp);
        } else {
            fd.append('action', 'update_casting');
            fd.append('id', el.dataset.id);
        }
        fetch('api.php', { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(d) { if (d.ok) loadData(); else alert(d.msg); })
            .catch(function() { loadData(); });
    }
    input.addEventListener('blur', save);
    input.addEventListener('keydown', function(ev) {
        if (ev.key === 'Enter') { ev.preventDefault(); input.blur(); }
        if (ev.key === 'Escape') { loadData(); }
    });
}

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
