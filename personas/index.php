<?php
/**
 * personas/index.php — Registro público de participantes del ViaCrucis
 */
require_once __DIR__ . '/../data/db.php';
require_once __DIR__ . '/../incs/versionLogs.php';
ensureSchema();

$roles = getRoles();
$personas = getPersonas(true); // Solo enabled para render inicial
$latestVersion = $latestVersion ?? '1.0';
?>
<?php include __DIR__ . '/../incs/header.php'; ?>

<main class="personas-main">
    <!-- PRIMERO: Lista de participantes -->
    <section class="personas-list-section">
        <h2>📋 Participantes (<span id="personas-count"><?= count($personas) ?></span>)</h2>
        <div id="personas-list" class="personas-list">
            <?php if (empty($personas)): ?>
                <p class="empty-message">Aún no hay participantes registrados. ¡Sé el primero!</p>
            <?php else: ?>
                <?php foreach ($personas as $p): 
                    $faltaDni = empty($p['dni']);
                    $faltaTel = empty($p['telefono']);
                    $faltaRoles = empty($p['roles']);
                    $incompleto = $faltaDni || $faltaTel || $faltaRoles;
                ?>
                <div class="persona-card <?= $incompleto ? 'persona-incompleta' : '' ?>" data-id="<?= $p['id'] ?>">
                    <div class="persona-info">
                        <div class="persona-header-row">
                            <strong class="persona-name"><?php
                                if (!empty($p['apellido'])) {
                                    echo htmlspecialchars(mb_strtoupper($p['apellido'])) . ', ' . htmlspecialchars($p['nombre']);
                                } else {
                                    echo htmlspecialchars($p['nombre']);
                                }
                            ?></strong>
                            <?php if ($incompleto): ?>
                            <span class="badge-incompleto" title="Faltan datos">⚠️</span>
                            <?php endif; ?>
                        </div>
                        <div class="persona-roles">
                            <?php foreach ($p['roles'] as $r): 
                                $rolLabel = $r['nombre'];
                                if (!empty($r['personaje'])) $rolLabel = 'Actor-' . $r['personaje'];
                            ?>
                            <span class="rol-badge"><?= $r['icono'] ?> <?= htmlspecialchars($rolLabel) ?></span>
                            <?php endforeach; ?>
                            <?php if ($faltaRoles): ?>
                            <span class="rol-badge rol-none">Sin rol asignado</span>
                            <?php endif; ?>
                        </div>
                        <?php if ($incompleto): ?>
                        <div class="persona-faltantes">
                            <?php if ($faltaDni): ?><span class="falta-item">DNI</span><?php endif; ?>
                            <?php if ($faltaTel): ?><span class="falta-item">Teléfono</span><?php endif; ?>
                            <?php if ($faltaRoles): ?><span class="falta-item">Roles</span><?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="persona-buttons">
                        <button class="btn-actualizar" onclick="editPersona(<?= $p['id'] ?>)" title="Actualizar datos">📝</button>
                        <button class="btn-delete director-only" onclick="deletePersona(<?= $p['id'] ?>, '<?= htmlspecialchars(addslashes($p['nombre'])) ?>')" title="Eliminar" style="display:none;">🗑️</button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- SEGUNDO: Formulario de registro / edición -->
    <section class="personas-form-section" id="form-section">
        <h2 id="form-title">👤 Nuevo Participante</h2>
        <form id="persona-form" class="personas-form">
            <input type="hidden" id="persona-id" value="">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="persona-nombre">Nombre *</label>
                    <input type="text" id="persona-nombre" placeholder="Tu nombre" required>
                </div>
                <div class="form-group">
                    <label for="persona-apellido">Apellido</label>
                    <input type="text" id="persona-apellido" placeholder="Tu apellido">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="persona-dni">DNI</label>
                    <input type="text" id="persona-dni" placeholder="Documento">
                </div>
                <div class="form-group">
                    <label for="persona-telefono">Teléfono</label>
                    <input type="tel" id="persona-telefono" placeholder="Ej: 3833-000000">
                </div>
            </div>
            
            <div class="form-group">
                <label>¿En qué participás?</label>
                <div class="roles-checkboxes" id="roles-container">
                    <?php foreach ($roles as $rol): ?>
                    <label class="rol-checkbox">
                        <input type="checkbox" name="roles[]" value="<?= htmlspecialchars($rol['id']) ?>">
                        <span class="rol-label"><?= $rol['icono'] ?> <?= htmlspecialchars($rol['nombre']) ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-registrar" id="btn-submit">✅ REGISTRARME</button>
                <button type="button" class="btn-cancelar" id="btn-cancel" style="display:none;" onclick="cancelEdit()">❌ Cancelar</button>
            </div>
            
            <div id="form-message" class="form-message"></div>
        </form>
    </section>
</main>

<script src="../jss/perfiles.js?v=<?= urlencode($latestVersion) ?>"></script>
<script>
// ===== PERSONAS MODULE =====

const API = 'api.php';

// ── Cargar lista (AJAX) ──
function loadPersonas() {
    const isDir = window.VCBYPerfiles && window.VCBYPerfiles.isDirector();
    const url = isDir ? API + '?action=list&director=1' : API + '?action=list';
    
    fetch(url)
        .then(r => r.json())
        .then(data => {
            if (!data.ok) return;
            const list = document.getElementById('personas-list');
            const count = document.getElementById('personas-count');
            count.textContent = data.personas.length;
            
            if (data.personas.length === 0) {
                list.innerHTML = '<p class="empty-message">Aún no hay participantes registrados. ¡Sé el primero!</p>';
                return;
            }
            
            const isDir = window.VCBYPerfiles && window.VCBYPerfiles.isDirector();
            
            list.innerHTML = data.personas.map(p => {
                const nombre = p.apellido 
                    ? escHtml(p.apellido.toUpperCase()) + ', ' + escHtml(p.nombre)
                    : escHtml(p.nombre);
                const faltaDni = !p.dni;
                const faltaTel = !p.telefono;
                const faltaRoles = !p.roles || p.roles.length === 0;
                const incompleto = faltaDni || faltaTel || faltaRoles;
                const disabled = p.enabled === 0 || p.enabled === '0';
                
                const rolesHtml = (p.roles && p.roles.length > 0) 
                    ? p.roles.map(r => {
                        const label = r.personaje ? 'Actor-' + r.personaje : r.nombre;
                        return `<span class="rol-badge">${r.icono} ${escHtml(label)}</span>`;
                    }).join('')
                    : '<span class="rol-badge rol-none">Sin rol asignado</span>';
                
                let faltantesHtml = '';
                if (incompleto) {
                    const items = [];
                    if (faltaDni) items.push('<span class="falta-item">DNI</span>');
                    if (faltaTel) items.push('<span class="falta-item">Teléfono</span>');
                    if (faltaRoles) items.push('<span class="falta-item">Roles</span>');
                    faltantesHtml = `<div class="persona-faltantes">${items.join('')}</div>`;
                }
                
                const toggleBtn = isDir 
                    ? `<button class="btn-toggle" onclick="togglePersona(${p.id})" title="${disabled ? 'Activar' : 'Desactivar'}">${disabled ? '🚫' : '👁️'}</button>`
                    : '';
                
                const deleteBtn = isDir 
                    ? `<button class="btn-delete" onclick="deletePersona(${p.id}, '${escHtml(p.nombre)}')" title="Eliminar">🗑️</button>` 
                    : '';
                
                const cardClass = disabled ? 'persona-card persona-disabled' : 
                    (incompleto ? 'persona-card persona-incompleta' : 'persona-card');
                
                return `<div class="${cardClass}" data-id="${p.id}">
                    <div class="persona-info">
                        <div class="persona-header-row">
                            <strong class="persona-name">${nombre}</strong>
                            ${disabled ? '<span class="badge-disabled" title="Oculto al público">🚫</span>' : ''}
                            ${!disabled && incompleto ? '<span class="badge-incompleto" title="Faltan datos">⚠️</span>' : ''}
                        </div>
                        <div class="persona-roles">${rolesHtml}</div>
                        ${faltantesHtml}
                    </div>
                    <div class="persona-buttons">
                        ${!disabled ? `<button class="btn-actualizar" onclick="editPersona(${p.id})" title="Actualizar datos">📝</button>` : ''}
                        ${toggleBtn}
                        ${deleteBtn}
                    </div>
                </div>`;
            }).join('');
        });
}

// ── Enviar formulario ──
document.getElementById('persona-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const id = document.getElementById('persona-id').value;
    const nombre = document.getElementById('persona-nombre').value.trim();
    const apellido = document.getElementById('persona-apellido').value.trim();
    const dni = document.getElementById('persona-dni').value.trim();
    const telefono = document.getElementById('persona-telefono').value.trim();
    const roles = Array.from(document.querySelectorAll('input[name="roles[]"]:checked')).map(cb => cb.value);
    
    if (!nombre) {
        showMessage('El nombre es obligatorio', 'error');
        return;
    }
    
    const action = id ? 'update' : 'add';
    const body = { action, nombre, apellido, dni, telefono, roles };
    if (id) body.id = parseInt(id);
    
    fetch(API, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body)
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            showMessage(id ? '✅ Datos actualizados' : '✅ ¡Registro exitoso!', 'success');
            resetForm();
            loadPersonas();
        } else {
            showMessage('❌ ' + (data.error || 'Error desconocido'), 'error');
        }
    })
    .catch(() => showMessage('❌ Error de conexión', 'error'));
});

// ── Editar persona (todos pueden actualizar sus datos) ──
function editPersona(id) {
    fetch(API + '?action=list')
        .then(r => r.json())
        .then(data => {
            const p = data.personas.find(x => x.id == id);
            if (!p) return;
            
            document.getElementById('persona-id').value = p.id;
            document.getElementById('persona-nombre').value = p.nombre;
            document.getElementById('persona-apellido').value = p.apellido || '';
            document.getElementById('persona-dni').value = p.dni || '';
            document.getElementById('persona-telefono').value = p.telefono || '';
            
            document.querySelectorAll('input[name="roles[]"]').forEach(cb => {
                cb.checked = p.roles.some(r => r.id === cb.value);
            });
            
            document.getElementById('form-title').textContent = '📝 Actualizar datos de ' + p.nombre;
            document.getElementById('btn-submit').textContent = '💾 GUARDAR CAMBIOS';
            document.getElementById('btn-cancel').style.display = '';
            
            document.getElementById('form-section').scrollIntoView({ behavior: 'smooth' });
        });
}

// ── Eliminar persona (solo Director) ──
function deletePersona(id, nombre) {
    if (!confirm('¿Eliminar a "' + nombre + '"? Esta acción no se puede deshacer.')) return;
    
    fetch(API, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'delete', id: id })
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            showMessage('✅ Persona eliminada', 'success');
            loadPersonas();
        }
    });
}

// ── Toggle enabled (Director) ──
function togglePersona(id) {
    fetch(API, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'toggle', id: id })
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            showMessage(data.enabled ? '👁️ Persona activada' : '🚫 Persona desactivada', 'success');
            loadPersonas();
        }
    });
}

// ── Cancelar edición ──
function cancelEdit() {
    resetForm();
    document.querySelector('.personas-list-section').scrollIntoView({ behavior: 'smooth' });
}

function resetForm() {
    document.getElementById('persona-form').reset();
    document.getElementById('persona-id').value = '';
    document.getElementById('form-title').textContent = '👤 Nuevo Participante';
    document.getElementById('btn-submit').textContent = '✅ REGISTRARME';
    document.getElementById('btn-cancel').style.display = 'none';
}

// ── Helpers ──
function showMessage(msg, type) {
    const el = document.getElementById('form-message');
    el.textContent = msg;
    el.className = 'form-message ' + type;
    el.style.display = 'block';
    setTimeout(() => { el.style.display = 'none'; }, 4000);
}

function escHtml(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

// ── Init: detectar Director para mostrar botones ──
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        if (window.VCBYPerfiles && window.VCBYPerfiles.isDirector()) {
            document.querySelectorAll('.director-only').forEach(el => el.style.display = '');
        }
    }, 200);
});
</script>

<?php include __DIR__ . '/../incs/footer.php'; ?>
