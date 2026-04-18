<?php
/**
 * personas/index.php — Registro público de participantes del ViaCrucis
 */
require_once __DIR__ . '/../data/db.php';
require_once __DIR__ . '/../incs/versionLogs.php';
ensureSchema();

$roles = getRoles();
$personas = getPersonas(true);
$personajesDisponibles = getPersonajesDisponibles();
$latestVersion = $latestVersion ?? '26.12';
?>
<?php include __DIR__ . '/../incs/header.php'; ?>

<main class="personas-main">
    <!-- Lista de participantes -->
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
                                if (!empty($r['personaje'])) {
                                    if ($r['id'] === 'actor') $rolLabel = 'Actor-' . $r['personaje'];
                                    elseif ($r['id'] === 'staff') $rolLabel = $r['personaje'];
                                    elseif ($r['id'] === 'donante') $rolLabel = $r['personaje'];
                                    elseif ($r['id'] === 'otro') $rolLabel = $r['personaje'];
                                }
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
                        <button class="btn-actualizar" onclick="abrirFormInline(<?= $p['id'] ?>, '<?= htmlspecialchars($p['nombre'], ENT_QUOTES) ?>', '<?= htmlspecialchars($p['apellido'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($p['dni'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($p['telefono'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars(json_encode($p['roles']), ENT_QUOTES) ?>')" title="Actualizar datos">📝</button>
                        <button class="btn-delete director-only" onclick="deletePersona(<?= $p['id'] ?>, '<?= htmlspecialchars(addslashes($p['nombre'])) ?>')" title="Eliminar" style="display:none;">🗑️</button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- Info para nuevos participantes -->
    <section class="personas-form-section">
        <h2>👤 ¿Querés participar?</h2>
        <p style="text-align:center;color:#8b7355;margin:20px 0;">
            Acercate a la organización para registrarte o hacé click en "📝" en cualquier participante para editar.
        </p>
    </section>
</main>

<script src="../jss/perfiles.js?v=<?= urlencode($latestVersion) ?>"></script>
<script>
const API = 'api.php';
let editingId = null;
let editingCard = null;

// ===== FORMULARIO INLINE REUTILIZABLE =====
function getFormTemplate() {
    return `
        <div class="inline-form">
            <h4>📝 Editar datos</h4>
            <div class="inline-form-row">
                <input type="text" class="inline-nombre" placeholder="Nombre">
                <input type="text" class="inline-apellido" placeholder="Apellido">
            </div>
            <div class="inline-form-row">
                <input type="text" class="inline-dni" placeholder="DNI">
                <input type="tel" class="inline-telefono" placeholder="Teléfono">
            </div>
            <div class="inline-roles-section">
                <label>Roles:</label>
                <div class="inline-roles-asignados"></div>
                <div class="inline-roles-container">
                    <div class="inline-rol-row">
                        <label class="inline-rol-checkbox">
                            <input type="checkbox" value="donador" onchange="toggleInlineSelector(this, 'donador')">
                            <span>🤝 Donador</span>
                        </label>
                    </div>
                    <div class="inline-rol-row">
                        <label class="inline-rol-checkbox">
                            <input type="checkbox" value="colaborador" onchange="toggleInlineSelector(this, 'colaborador')">
                            <span>🤝 Colaborador</span>
                        </label>
                    </div>
                    <div class="inline-rol-row">
                        <label class="inline-rol-checkbox">
                            <input type="checkbox" value="actor" onchange="toggleInlineSelector(this, 'actor')">
                            <span>🎭 Actor</span>
                        </label>
                        <select class="inline-selector" data-rol="actor" style="display:none;" onchange="addInlineRol(this)">
                            <option value="">-- Personaje --</option>
                            ${window.personajesDisponibles ? window.personajesDisponibles.map(pj => `<option value="${escHtml(pj.nombre)}">${escHtml(pj.nombre)}</option>`).join('') : ''}
                        </select>
                    </div>
                    <div class="inline-rol-row">
                        <label class="inline-rol-checkbox">
                            <input type="checkbox" value="staff" onchange="toggleInlineSelector(this, 'staff')">
                            <span>🔧 Staff</span>
                        </label>
                        <select class="inline-selector" data-rol="staff" style="display:none;" onchange="addInlineRol(this)">
                            <option value="">-- Función --</option>
                            <option value="Logística">Logística</option>
                            <option value="Sonido">Sonido</option>
                            <option value="Vestuario">Vestuario</option>
                            <option value="Escenografía">Escenografía</option>
                        </select>
                    </div>
                    <div class="inline-rol-row">
                        <label class="inline-rol-checkbox">
                            <input type="checkbox" value="otro" onchange="toggleInlineSelector(this, 'otro')">
                            <span>⭐ Otro</span>
                        </label>
                        <input type="text" class="inline-input-otro" placeholder="Escribí el rol..." style="display:none;">
                        <button type="button" class="btn-add-otro" style="display:none;" onclick="addInlineRolOtro(this)">Agregar</button>
                    </div>
                </div>
            </div>
            <div class="inline-form-actions">
                <button type="button" class="btn-save-inline" onclick="guardarFormInline()">💾 Guardar</button>
                <button type="button" class="btn-cancel-inline" onclick="cerrarFormInline()">❌ Cancelar</button>
            </div>
        </div>
    `;
}

function abrirFormInline(id, nombre, apellido, dni, telefono, rolesJson) {
    cerrarFormInline();
    const card = document.querySelector(`.persona-card[data-id="${id}"]`);
    if (!card) return;

    editingId = id;
    editingCard = card;
    card.insertAdjacentHTML('beforeend', getFormTemplate());
    card.classList.add('card-editing');

    const form = card.querySelector('.inline-form');
    form.querySelector('.inline-nombre').value = nombre;
    form.querySelector('.inline-apellido').value = apellido;
    form.querySelector('.inline-dni').value = dni;
    form.querySelector('.inline-telefono').value = telefono;

    const roles = JSON.parse(rolesJson.replace(/&quot;/g, '"'));
    const inlineRoles = [];
    if (roles && roles.length > 0) {
        roles.forEach(r => {
            if (r.personaje) {
                if (r.id === 'actor') {
                    inlineRoles.push({ rol: 'actor', valor: r.personaje, icono: '🎭' });
                } else if (r.id === 'staff') {
                    inlineRoles.push({ rol: 'staff', valor: r.personaje, icono: '🔧' });
                } else if (r.id === 'donante') {
                    if (r.personaje === 'Donador') {
                        inlineRoles.push({ rol: 'donador', valor: 'Donador', icono: '🤝' });
                    } else if (r.personaje === 'Colaborador') {
                        inlineRoles.push({ rol: 'colaborador', valor: 'Colaborador', icono: '🤝' });
                    }
                } else if (r.id === 'otro') {
                    inlineRoles.push({ rol: 'otro', valor: r.personaje, icono: '⭐' });
                }
            } else {
                if (r.id === 'donante') {
                    inlineRoles.push({ rol: 'donador', valor: 'Donador', icono: '🤝' });
                } else if (r.id === 'otro') {
                    inlineRoles.push({ rol: 'otro', valor: r.nombre, icono: '⭐' });
                }
            }
        });
    }

    form.dataset.roles = JSON.stringify(inlineRoles);
    renderInlineRoles(form, inlineRoles);
    form.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function cerrarFormInline() {
    if (editingCard) {
        const form = editingCard.querySelector('.inline-form');
        if (form) form.remove();
        editingCard.classList.remove('card-editing');
    }
    editingId = null;
    editingCard = null;
}

function guardarFormInline() {
    if (!editingCard) return;
    const form = editingCard.querySelector('.inline-form');
    const id = editingId;

    const nombre = form.querySelector('.inline-nombre').value.trim();
    const apellido = form.querySelector('.inline-apellido').value.trim();
    const dni = form.querySelector('.inline-dni').value.trim();
    const telefono = form.querySelector('.inline-telefono').value.trim();
    const roles = JSON.parse(form.dataset.roles || '[]');

    if (!nombre) {
        showInlineMessage(form, 'El nombre es obligatorio', 'error');
        return;
    }

    if (roles.length === 0) {
        showInlineMessage(form, 'Seleccioná al menos un rol', 'error');
        return;
    }

    const body = {
        action: editingId ? 'update' : 'add',
        id: id,
        nombre,
        apellido,
        dni,
        telefono,
        roles: roles.map(r => r.rol),
        personajes: roles.filter(r => r.rol === 'actor').map(r => r.valor),
        staffValores: roles.filter(r => r.rol === 'staff').map(r => r.valor),
        otroValores: roles.filter(r => r.rol === 'otro').map(r => r.valor),
        donadorValor: roles.filter(r => r.rol === 'donador').map(r => r.valor),
        colaboradorValor: roles.filter(r => r.rol === 'colaborador').map(r => r.valor)
    };

    fetch(API, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body)
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            showInlineMessage(form, '✅ Datos actualizados', 'success');
            setTimeout(() => {
                cerrarFormInline();
                loadPersonas();
            }, 500);
        } else {
            showInlineMessage(form, '❌ ' + (data.error || 'Error desconocido'), 'error');
        }
    })
    .catch(() => showInlineMessage(form, '❌ Error de conexión', 'error'));
}

function toggleInlineSelector(checkbox, rolId) {
    const form = checkbox.closest('.inline-form');
    const row = checkbox.closest('.inline-rol-row');
    const selector = row.querySelector('.inline-selector');
    const inputOtro = row.querySelector('.inline-input-otro');
    const btnAddOtro = row.querySelector('.btn-add-otro');

    if (rolId === 'donador' || rolId === 'colaborador') {
        if (checkbox.checked) {
            addInlineRolDirecto(form, rolId);
        }
        return;
    }

    if (selector) {
        selector.style.display = checkbox.checked ? 'inline-block' : 'none';
        if (!checkbox.checked) selector.value = '';
    }
    if (inputOtro) {
        inputOtro.style.display = checkbox.checked ? 'inline-block' : 'none';
        if (!checkbox.checked) inputOtro.value = '';
    }
    if (btnAddOtro) {
        btnAddOtro.style.display = checkbox.checked ? 'inline-block' : 'none';
    }
}

function addInlineRolDirecto(form, rolId) {
    const roles = JSON.parse(form.dataset.roles || '[]');
    const iconos = { donador: '🤝', colaborador: '🤝' };
    const label = rolId.charAt(0).toUpperCase() + rolId.slice(1);

    const yaExiste = roles.some(r => r.rol === rolId);
    if (yaExiste) {
        showInlineMessage(form, 'Ya agregaste ' + label, 'error');
        return;
    }

    roles.push({ rol: rolId, valor: label, icono: iconos[rolId] });
    form.dataset.roles = JSON.stringify(roles);
    renderInlineRoles(form, roles);
}

function addInlineRol(select) {
    const valor = select.value;
    if (!valor) return;

    const rolId = select.dataset.rol;
    const form = select.closest('.inline-form');
    const roles = JSON.parse(form.dataset.roles || '[]');

    const yaExiste = roles.some(r => r.rol === rolId && r.valor === valor);
    if (yaExiste) {
        showInlineMessage(form, 'Ya agregaste este rol', 'error');
        select.value = '';
        return;
    }

    const iconos = { actor: '🎭', staff: '🔧' };
    roles.push({ rol: rolId, valor: valor, icono: iconos[rolId] });
    form.dataset.roles = JSON.stringify(roles);
    renderInlineRoles(form, roles);
    select.value = '';
}

function addInlineRolOtro(btn) {
    const row = btn.closest('.inline-rol-row');
    const input = row.querySelector('.inline-input-otro');
    const valor = input.value.trim();

    if (!valor) {
        const form = btn.closest('.inline-form');
        showInlineMessage(form, 'Escribí un rol', 'error');
        return;
    }

    const form = btn.closest('.inline-form');
    const roles = JSON.parse(form.dataset.roles || '[]');

    const yaExiste = roles.some(r => r.rol === 'otro' && r.valor === valor);
    if (yaExiste) {
        showInlineMessage(form, 'Ya agregaste este rol', 'error');
        input.value = '';
        return;
    }

    roles.push({ rol: 'otro', valor: valor, icono: '⭐' });
    form.dataset.roles = JSON.stringify(roles);
    renderInlineRoles(form, roles);
    input.value = '';
}

function renderInlineRoles(form, roles) {
    const container = form.querySelector('.inline-roles-asignados');
    if (!container) return;

    if (roles.length === 0) {
        container.innerHTML = '<span style="color:#8b7355;font-size:0.85rem;">Seleccioná un rol</span>';
        return;
    }

    container.innerHTML = roles.map((r, i) => {
        let label = r.valor;
        if (r.rol === 'actor') label = 'Actor-' + r.valor;
        return `
        <span class="rol-tag" style="margin:2px;">
            ${r.icono} ${label}
            <span class="remove-tag" onclick="removeInlineRol(this, ${i})">&times;</span>
        </span>
    `}).join('');
}

function removeInlineRol(span, index) {
    const form = span.closest('.inline-form');
    const roles = JSON.parse(form.dataset.roles || '[]');
    roles.splice(index, 1);
    form.dataset.roles = JSON.stringify(roles);
    renderInlineRoles(form, roles);
}

function showInlineMessage(form, msg, type) {
    let msgEl = form.querySelector('.inline-message');
    if (!msgEl) {
        msgEl = document.createElement('div');
        msgEl.className = 'inline-message';
        form.querySelector('.inline-form-actions').before(msgEl);
    }
    msgEl.textContent = msg;
    msgEl.className = 'inline-message ' + type;
    msgEl.style.display = 'block';
    setTimeout(() => { msgEl.style.display = 'none'; }, 4000);
}

// ===== CARGAR LISTA =====
function loadPersonas() {
    const isDir = window.VCBYPerfiles && window.VCBYPerfiles.isDirector();
    const url = isDir ? API + '?action=list&director=1' : API + '?action=list';

    if (!window.personajesDisponibles) {
        fetch(API + '?action=personajes')
            .then(r => r.json())
            .then(data => {
                if (data.ok) {
                    window.personajesDisponibles = data.personajes;
                    loadPersonasList(url);
                }
            });
    } else {
        loadPersonasList(url);
    }
}

function loadPersonasList(url) {
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
                        let label = r.nombre;
                        if (r.personaje) {
                            if (r.id === 'actor') label = 'Actor-' + r.personaje;
                            else if (r.id === 'staff') label = r.personaje;
                            else if (r.id === 'donante') label = r.personaje;
                            else if (r.id === 'otro') label = r.personaje;
                        }
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
                        ${!disabled ? `<button class="btn-actualizar" onclick="abrirFormInline(${p.id}, '${escHtml(p.nombre)}', '${escHtml(p.apellido || '')}', '${escHtml(p.dni || '')}', '${escHtml(p.telefono || '')}', '${JSON.stringify(p.roles).replace(/"/g, '&quot;')}')" title="Actualizar datos">📝</button>` : ''}
                        ${toggleBtn}
                        ${deleteBtn}
                    </div>
                </div>`;
            }).join('');
        });
}

function deletePersona(id, nombre) {
    if (!confirm('¿Eliminar a "' + nombre + '"?')) return;
    fetch(API, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'delete', id: id })
    }).then(r => r.json()).then(data => {
        if (data.ok) loadPersonas();
    });
}

function togglePersona(id) {
    fetch(API, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'toggle', id: id })
    }).then(r => r.json()).then(data => {
        if (data.ok) loadPersonas();
    });
}

function escHtml(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

// Init
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        if (window.VCBYPerfiles && window.VCBYPerfiles.isDirector()) {
            document.querySelectorAll('.director-only').forEach(el => el.style.display = '');
        }
    }, 200);
});
</script>

<?php include __DIR__ . '/../incs/footer.php'; ?>
