<?php
/**
 * casting/index.php — Página pública de casting
 * Muestra personajes con postulados y permite anotarse
 */
session_start();
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
require __DIR__ . '/../data/db.php';
$characters = getCharacters();
$castings = getCastingList($isAdmin);

// Agrupar castings por idp
$byIdp = [];
foreach ($castings as $c) {
    $byIdp[$c['idp']][] = $c;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Casting — Via Crusis BY2026</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .casting-page {
            max-width: 700px;
            margin: 0 auto;
            padding: 16px;
            font-family: Georgia, 'Times New Roman', serif;
        }
        .casting-title {
            text-align: center;
            font-size: 1.3em;
            color: #2d2418;
            margin-bottom: 4px;
        }
        .casting-subtitle {
            text-align: center;
            font-size: 0.85em;
            color: #8a7b68;
            margin-bottom: 20px;
        }
        .casting-card {
            background: #fffdf8;
            border: 1px solid rgba(196, 180, 148, 0.4);
            border-radius: 10px;
            margin-bottom: 12px;
            overflow: hidden;
            transition: box-shadow 0.2s ease;
        }
        .casting-card:hover {
            box-shadow: 0 2px 12px rgba(128, 109, 90, 0.15);
        }
        .casting-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 14px;
            background: linear-gradient(135deg, #f5f0e6, #ede6d6);
            border-bottom: 1px solid rgba(196, 180, 148, 0.3);
            cursor: default;
        }
        .casting-char-info {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .casting-idp {
            font-family: 'Courier New', monospace;
            font-size: 0.65em;
            background: rgba(139, 0, 0, 0.08);
            color: #8b0000;
            padding: 2px 5px;
            border-radius: 4px;
            font-weight: bold;
        }
        .casting-char-name {
            font-weight: bold;
            font-variant: small-caps;
            font-size: 1em;
            color: #2d2418;
        }
        .casting-btn-add {
            background: #4a8c3f;
            color: white;
            border: none;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            font-size: 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }
        .casting-btn-add:hover {
            background: #3a7030;
            transform: scale(1.15);
        }
        .casting-body {
            padding: 0;
        }
        .casting-empty {
            padding: 8px 14px;
            color: #b0a693;
            font-style: italic;
            font-size: 0.85em;
        }
        .casting-person {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 7px 14px;
            border-bottom: 1px solid rgba(196, 180, 148, 0.15);
            font-size: 0.9em;
        }
        .casting-person:last-child {
            border-bottom: none;
        }
        .casting-person-name {
            color: #2d2418;
            font-weight: 500;
        }
        .casting-person-phone {
            font-family: 'Courier New', monospace;
            font-size: 0.8em;
            color: #8a7b68;
        }
        .casting-person-delete {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 14px;
            opacity: 0.4;
            transition: all 0.2s;
        }
        .casting-person-delete:hover {
            opacity: 1;
            transform: scale(1.2);
        }
        .casting-count {
            font-size: 0.7em;
            color: #b0a693;
            background: rgba(0,0,0,0.04);
            padding: 2px 6px;
            border-radius: 8px;
            margin-left: 6px;
        }
        /* Modal de signup */
        .casting-modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }
        .casting-modal-overlay.active {
            display: flex;
        }
        .casting-modal {
            background: #fffdf8;
            border-radius: 12px;
            padding: 20px;
            width: 90%;
            max-width: 360px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.25);
            animation: castingSlideIn 0.2s ease;
        }
        @keyframes castingSlideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .casting-modal h3 {
            margin: 0 0 4px;
            font-size: 1.1em;
            color: #2d2418;
        }
        .casting-modal .modal-char {
            font-size: 0.85em;
            color: #8a7b68;
            margin-bottom: 14px;
        }
        .casting-modal label {
            display: block;
            font-size: 0.8em;
            color: #5a4b3a;
            margin-bottom: 3px;
            margin-top: 10px;
        }
        .casting-modal input {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #d4c9b5;
            border-radius: 6px;
            font-size: 0.95em;
            font-family: inherit;
            background: #fff;
            box-sizing: border-box;
        }
        .casting-modal input:focus {
            outline: none;
            border-color: #8b0000;
            box-shadow: 0 0 0 2px rgba(139,0,0,0.1);
        }
        .casting-modal-actions {
            display: flex;
            gap: 8px;
            margin-top: 16px;
            justify-content: flex-end;
        }
        .casting-modal-actions button {
            padding: 8px 16px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 0.9em;
            font-family: inherit;
        }
        .btn-cancel {
            background: #e8e0d0;
            color: #5a4b3a;
        }
        .btn-submit {
            background: #4a8c3f;
            color: white;
            font-weight: 600;
        }
        .btn-submit:hover {
            background: #3a7030;
        }
        .casting-back {
            display: inline-block;
            margin-bottom: 12px;
            color: #8a7b68;
            text-decoration: none;
            font-size: 0.9em;
        }
        .casting-back:hover {
            color: #2d2418;
        }
        .casting-msg {
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 12px;
            font-size: 0.85em;
            display: none;
        }
        .casting-msg.success {
            background: #e8f5e9;
            color: #2e7d32;
            display: block;
        }
        .casting-msg.error {
            background: #fce4ec;
            color: #c62828;
            display: block;
        }
    </style>
</head>
<body>
<header class="header">
    <h1>Via Crusis<br>Barrio Yacampiz - 2026</h1>
</header>

<main class="casting-page">
    <a href="../audios/index.php" class="casting-back">← Volver a audios</a>
    <h2 class="casting-title">🎭 Casting de Personajes</h2>
    <p class="casting-subtitle">Postulate para interpretar un personaje. ¡Todos son bienvenidos!</p>
    
    <div id="casting-msg" class="casting-msg"></div>

    <?php foreach ($characters as $char): 
        $idp = $char['idp'];
        $name = $char['character'];
        $postulados = $byIdp[$idp] ?? [];
        $count = count($postulados);
    ?>
    <div class="casting-card" id="card-<?= $idp ?>">
        <div class="casting-header">
            <div class="casting-char-info">
                <span class="casting-idp"><?= $idp ?></span>
                <span class="casting-char-name"><?= htmlspecialchars($name) ?></span>
                <?php if ($count > 0): ?>
                    <span class="casting-count"><?= $count ?> postulado<?= $count > 1 ? 's' : '' ?></span>
                <?php endif; ?>
            </div>
            <button class="casting-btn-add" onclick="openSignup('<?= $idp ?>', '<?= htmlspecialchars($name, ENT_QUOTES) ?>')" title="Postularme">+</button>
        </div>
        <div class="casting-body">
            <?php if (empty($postulados)): ?>
                <div class="casting-empty">Sin postulados aún</div>
            <?php else: ?>
                <?php foreach ($postulados as $p): ?>
                <div class="casting-person">
                    <span class="casting-person-name"><?= htmlspecialchars($p['nombre'] . ' ' . $p['apellido']) ?></span>
                    <span>
                        <?php if ($isAdmin && isset($p['telefono'])): ?>
                            <span class="casting-person-phone">📞 <?= htmlspecialchars($p['telefono']) ?></span>
                        <?php endif; ?>
                        <?php if ($isAdmin): ?>
                            <button class="casting-person-delete" onclick="deleteCasting(<?= $p['id'] ?>)" title="Eliminar">🗑</button>
                        <?php endif; ?>
                    </span>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</main>

<!-- Modal de signup -->
<div class="casting-modal-overlay" id="signup-modal">
    <div class="casting-modal">
        <h3>Postularme</h3>
        <div class="modal-char" id="modal-char-name"></div>
        <form id="signup-form" onsubmit="submitSignup(event)">
            <input type="hidden" id="signup-idp">
            <input type="hidden" id="signup-char">
            <label for="signup-nombre">Nombre</label>
            <input type="text" id="signup-nombre" required placeholder="Tu nombre" autocomplete="given-name">
            <label for="signup-apellido">Apellido</label>
            <input type="text" id="signup-apellido" required placeholder="Tu apellido" autocomplete="family-name">
            <label for="signup-telefono">Teléfono (privado, solo para coordinación)</label>
            <input type="tel" id="signup-telefono" required placeholder="Ej: 3855-123456" autocomplete="tel">
            <div class="casting-modal-actions">
                <button type="button" class="btn-cancel" onclick="closeSignup()">Cancelar</button>
                <button type="submit" class="btn-submit">Postularme</button>
            </div>
        </form>
    </div>
</div>

<script>
function openSignup(idp, charName) {
    document.getElementById('signup-idp').value = idp;
    document.getElementById('signup-char').value = charName;
    document.getElementById('modal-char-name').textContent = idp + ' — ' + charName;
    document.getElementById('signup-nombre').value = '';
    document.getElementById('signup-apellido').value = '';
    document.getElementById('signup-telefono').value = '';
    document.getElementById('signup-modal').classList.add('active');
    setTimeout(function() { document.getElementById('signup-nombre').focus(); }, 100);
}

function closeSignup() {
    document.getElementById('signup-modal').classList.remove('active');
}

// Cerrar con click fuera
document.getElementById('signup-modal').addEventListener('click', function(e) {
    if (e.target === this) closeSignup();
});

function showMsg(text, type) {
    var el = document.getElementById('casting-msg');
    el.textContent = text;
    el.className = 'casting-msg ' + type;
    setTimeout(function() { el.style.display = 'none'; }, 4000);
}

function submitSignup(e) {
    e.preventDefault();
    var fd = new FormData();
    fd.append('action', 'signup');
    fd.append('idp', document.getElementById('signup-idp').value);
    fd.append('character_name', document.getElementById('signup-char').value);
    fd.append('nombre', document.getElementById('signup-nombre').value.trim());
    fd.append('apellido', document.getElementById('signup-apellido').value.trim());
    fd.append('telefono', document.getElementById('signup-telefono').value.trim());
    
    fetch('api.php', { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.ok) {
                closeSignup();
                showMsg(data.msg, 'success');
                setTimeout(function() { location.reload(); }, 800);
            } else {
                showMsg(data.msg, 'error');
            }
        })
        .catch(function() { showMsg('Error de conexión.', 'error'); });
}

function deleteCasting(id) {
    if (!confirm('¿Eliminar esta postulación?')) return;
    var fd = new FormData();
    fd.append('action', 'delete');
    fd.append('id', id);
    
    fetch('api.php', { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.ok) {
                showMsg(data.msg, 'success');
                setTimeout(function() { location.reload(); }, 500);
            } else {
                showMsg(data.msg, 'error');
            }
        })
        .catch(function() { showMsg('Error de conexión.', 'error'); });
}
</script>
</body>
</html>
