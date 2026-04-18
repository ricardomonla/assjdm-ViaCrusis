<?php
/**
 * personas/index.php — Registro público de participantes del ViaCrucis
 *
 * Feature: Selector de personajes para actores
 * - Si viene de personaje placeholder → precarga datos
 * - Si se registra como actor → muestra selector de personajes disponibles
 */
require_once __DIR__ . '/../data/db.php';
require_once __DIR__ . '/../incs/versionLogs.php';
ensureSchema();

$roles = getRoles();
$personas = getPersonas(true); // Solo enabled para render inicial
$personajesDisponibles = getPersonajesDisponibles();

// Verificar si viene con personaje preseleccionado (de URL)
$personajeSeleccionado = $_GET['personaje'] ?? '';
$placeholderData = null;
if ($personajeSeleccionado) {
    $placeholderData = getPlaceholderByPersonaje($personajeSeleccionado);
}

$latestVersion = $latestVersion ?? '26.12';
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
                        <button class="btn-actualizar" onclick="toggleEditForm(<?= $p['id'] ?>, '<?= htmlspecialchars($p['nombre'], ENT_QUOTES) ?>', '<?= htmlspecialchars($p['apellido'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($p['dni'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($p['telefono'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars(json_encode($p['roles']), ENT_QUOTES) ?>')" title="Actualizar datos">📝</button>
                        <button class="btn-delete director-only" onclick="deletePersona(<?= $p['id'] ?>, '<?= htmlspecialchars(addslashes($p['nombre'])) ?>')" title="Eliminar" style="display:none;">🗑️</button>
                    </div>
                    <!-- Formulario inline (oculto por defecto) -->
                    <div class="inline-form" id="form-<?= $p['id'] ?>" style="display:none;">
                        <h4>📝 Editar datos</h4>
                        <input type="hidden" class="inline-id" value="<?= $p['id'] ?>">
                        <div class="inline-form-row">
                            <input type="text" class="inline-nombre" placeholder="Nombre" value="<?= htmlspecialchars($p['nombre']) ?>">
                            <input type="text" class="inline-apellido" placeholder="Apellido" value="<?= htmlspecialchars($p['apellido'] ?? '') ?>">
                        </div>
                        <div class="inline-form-row">
                            <input type="text" class="inline-dni" placeholder="DNI" value="<?= htmlspecialchars($p['dni'] ?? '') ?>">
                            <input type="tel" class="inline-telefono" placeholder="Teléfono" value="<?= htmlspecialchars($p['telefono'] ?? '') ?>">
                        </div>
                        <div class="inline-roles-section">
                            <label>Roles:</label>
                            <div class="inline-roles-asignados"></div>
                            <div class="inline-roles-container">
                                <!-- Donador -->
                                <div class="inline-rol-row">
                                    <label class="inline-rol-checkbox">
                                        <input type="checkbox" value="donador" onchange="toggleInlineSelector(this, 'donador')">
                                        <span>🤝 Donador</span>
                                    </label>
                                </div>
                                <!-- Colaborador -->
                                <div class="inline-rol-row">
                                    <label class="inline-rol-checkbox">
                                        <input type="checkbox" value="colaborador" onchange="toggleInlineSelector(this, 'colaborador')">
                                        <span>🤝 Colaborador</span>
                                    </label>
                                </div>
                                <!-- Actor -->
                                <div class="inline-rol-row">
                                    <label class="inline-rol-checkbox">
                                        <input type="checkbox" value="actor" onchange="toggleInlineSelector(this, 'actor')">
                                        <span>🎭 Actor</span>
                                    </label>
                                    <select class="inline-selector" data-rol="actor" style="display:none;" onchange="addInlineRol(this)">
                                        <option value="">-- Personaje --</option>
                                        <?php foreach ($personajesDisponibles as $pj): ?>
                                        <option value="<?= htmlspecialchars($pj['nombre']) ?>"><?= htmlspecialchars($pj['nombre']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <!-- Staff -->
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
                                <!-- Otro -->
                                <div class="inline-rol-row">
                                    <label class="inline-rol-checkbox">
                                        <input type="checkbox" value="otro" onchange="toggleInlineSelector(this, 'otro')">
                                        <span>⭐ Otro</span>
                                    </label>
                                    <input type="text" class="inline-input-otro" placeholder="Escribí el rol..." style="display:none;padding:6px 10px;border:1px solid #c9b896;border-radius:4px;font-size:0.85rem;">
                                    <button type="button" class="btn-add-otro" style="display:none;padding:6px 12px;background:#806d5a;color:#fff;border:none;border-radius:4px;cursor:pointer;" onclick="addInlineRolOtro(this)">Agregar</button>
                                </div>
                            </div>
                        </div>
                        <div class="inline-form-actions">
                            <button type="button" class="btn-save-inline" onclick="saveInlineEdit(<?= $p['id'] ?>)">💾 Guardar</button>
                            <button type="button" class="btn-cancel-inline" onclick="cancelInlineEdit(<?= $p['id'] ?>)">❌ Cancelar</button>
                        </div>
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
            <input type="hidden" id="personaje-oculto" value="<?= htmlspecialchars($personajeSeleccionado) ?>">

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

                <!-- Lista de roles asignados (tags/badges) -->
                <div id="roles-asignados" class="roles-asignados">
                    <p class="empty-hint" style="color:#8b7355;font-size:0.9rem;">
                        Seleccioná un rol para agregarlo a tu participación.
                    </p>
                </div>

                <div class="roles-checkboxes" id="roles-container" style="margin-top:15px;">
                    <!-- Donador -->
                    <div class="rol-checkbox-row">
                        <label class="rol-checkbox">
                            <input type="checkbox" value="donador" onchange="toggleSelector('donador')">
                            <span class="rol-label">🤝 Donador</span>
                        </label>
                    </div>
                    <!-- Colaborador -->
                    <div class="rol-checkbox-row">
                        <label class="rol-checkbox">
                            <input type="checkbox" value="colaborador" onchange="toggleSelector('colaborador')">
                            <span class="rol-label">🤝 Colaborador</span>
                        </label>
                    </div>
                    <!-- Actor -->
                    <div class="rol-checkbox-row">
                        <label class="rol-checkbox">
                            <input type="checkbox" value="actor" onchange="toggleSelector('actor')">
                            <span class="rol-label">🎭 Actor</span>
                        </label>
                        <div class="personaje-inline" id="selector-actor" style="display:none;">
                            <select id="select-actor" onchange="agregarRol('actor', this)">
                                <option value="">-- Personaje --</option>
                                <?php foreach ($personajesDisponibles as $pj): ?>
                                <option value="<?= htmlspecialchars($pj['nombre']) ?>"><?= htmlspecialchars($pj['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <!-- Staff -->
                    <div class="rol-checkbox-row">
                        <label class="rol-checkbox">
                            <input type="checkbox" value="staff" onchange="toggleSelector('staff')">
                            <span class="rol-label">🔧 Staff</span>
                        </label>
                        <div class="personaje-inline" id="selector-staff" style="display:none;">
                            <select id="select-staff" onchange="agregarRol('staff', this)">
                                <option value="">-- Función --</option>
                                <option value="Logística">Logística</option>
                                <option value="Sonido">Sonido</option>
                                <option value="Vestuario">Vestuario</option>
                                <option value="Escenografía">Escenografía</option>
                            </select>
                        </div>
                    </div>
                    <!-- Otro -->
                    <div class="rol-checkbox-row">
                        <label class="rol-checkbox">
                            <input type="checkbox" value="otro" onchange="toggleSelector('otro')">
                            <span class="rol-label">⭐ Otro</span>
                        </label>
                        <div class="personaje-inline" id="selector-otro" style="display:none;">
                            <input type="text" id="input-otro" placeholder="Escribí el rol..." style="padding:6px 10px;border:1px solid #c9b896;border-radius:4px;font-size:0.9rem;">
                            <button type="button" onclick="agregarRolOtro()" style="padding:6px 12px;background:#806d5a;color:#fff;border:none;border-radius:4px;cursor:pointer;">Agregar</button>
                        </div>
                    </div>
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

// Roles asignados (tags)
let rolesAsignados = [];

// Toggle selector por rol
function toggleSelector(rolId) {
    const checkbox = document.querySelector(`input[value="${rolId}"]`);
    const selector = document.getElementById(`selector-${rolId}`);

    if (checkbox && selector) {
        selector.style.display = checkbox.checked ? 'flex' : 'none';

        // Donador y Colaborador se agregan directo
        if (checkbox.checked && (rolId === 'donador' || rolId === 'colaborador')) {
            agregarRolDirecto(rolId);
        }

        if (!checkbox.checked) {
            // Limpiar selects/inputs
            const select = document.getElementById(`select-${rolId}`);
            if (select) select.value = '';
            const input = document.getElementById(`input-${rolId}`);
            if (input) input.value = '';
        }
    }
}

// Agregar rol directo (Donador/Colaborador)
function agregarRolDirecto(rolId) {
    const iconos = { donador: '🤝', colaborador: '🤝' };
    const label = rolId.charAt(0).toUpperCase() + rolId.slice(1);

    const yaExiste = rolesAsignados.some(r => r.rol === rolId);
    if (yaExiste) {
        showMessage('Ya agregaste ' + label, 'error');
        return;
    }

    rolesAsignados.push({ rol: rolId, valor: label, icono: iconos[rolId] });
    renderRolesAsignados();
}

// Agregar rol como tag (Actor/Staff)
function agregarRol(rolId, selectEl) {
    const valor = selectEl.value;
    if (!valor) return;

    // Verificar duplicados
    const yaExiste = rolesAsignados.some(r => r.rol === rolId && r.valor === valor);
    if (yaExiste) {
        showMessage('Ya agregaste este rol', 'error');
        selectEl.value = '';
        return;
    }

    const iconos = { actor: '🎭', staff: '🔧' };
    const label = rolId === 'actor' ? 'Actor-' + valor : valor;
    rolesAsignados.push({ rol: rolId, valor: valor, icono: iconos[rolId] });
    renderRolesAsignados();

    selectEl.value = '';
}

// Agregar rol Otro (texto libre)
function agregarRolOtro() {
    const input = document.getElementById('input-otro');
    const valor = input.value.trim();
    if (!valor) {
        showMessage('Escribí un rol', 'error');
        return;
    }

    const yaExiste = rolesAsignados.some(r => r.rol === 'otro' && r.valor === valor);
    if (yaExiste) {
        showMessage('Ya agregaste este rol', 'error');
        input.value = '';
        return;
    }

    rolesAsignados.push({ rol: 'otro', valor: valor, icono: '⭐' });
    renderRolesAsignados();
    input.value = '';
}

// Renderizar tags
function renderRolesAsignados() {
    const container = document.getElementById('roles-asignados');
    if (!container) return;

    if (rolesAsignados.length === 0) {
        container.innerHTML = '<p class="empty-hint" style="color:#8b7355;font-size:0.9rem;">Seleccioná un rol para agregarlo a tu participación.</p>';
        return;
    }

    container.innerHTML = rolesAsignados.map((r, i) => {
        let label = r.valor;
        if (r.rol === 'actor') label = 'Actor-' + r.valor;
        if (r.rol === 'donador' || r.rol === 'colaborador') label = r.valor;
        return `
        <span class="rol-tag">
            ${r.icono} ${label}
            <span class="remove-tag" onclick="eliminarRol(${i})">&times;</span>
        </span>
    `}).join('');
}

// Eliminar rol
function eliminarRol(index) {
    rolesAsignados.splice(index, 1);
    renderRolesAsignados();
}

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

// ── Enviar formulario (sistema de tags) ──
document.getElementById('persona-form').addEventListener('submit', function(e) {
    e.preventDefault();

    const id = document.getElementById('persona-id').value;
    const nombre = document.getElementById('persona-nombre').value.trim();
    const apellido = document.getElementById('persona-apellido').value.trim();
    const dni = document.getElementById('persona-dni').value.trim();
    const telefono = document.getElementById('persona-telefono').value.trim();

    if (!nombre) {
        showMessage('El nombre es obligatorio', 'error');
        return;
    }

    if (rolesAsignados.length === 0) {
        showMessage('Seleccioná al menos un rol (Actor, Staff, etc.)', 'error');
        return;
    }

    const action = id ? 'update' : 'add';
    const body = {
        action,
        nombre,
        apellido,
        dni,
        telefono,
        roles: rolesAsignados.map(r => r.rol),
        personajes: rolesAsignados.filter(r => r.rol === 'actor').map(r => r.valor),
        staffValores: rolesAsignados.filter(r => r.rol === 'staff').map(r => r.valor),
        otroValores: rolesAsignados.filter(r => r.rol === 'otro').map(r => r.valor)
    };
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

// ── Toggle formulario inline ──
function toggleEditForm(id, nombre, apellido, dni, telefono, rolesJson) {
    const form = document.getElementById(`form-${id}`);
    const isVisible = form.style.display !== 'none';

    // Cerrar todos los formularios
    document.querySelectorAll('.inline-form').forEach(f => f.style.display = 'none');

    if (!isVisible) {
        // Abrir este formulario
        form.style.display = 'block';

        // Cargar datos
        form.querySelector('.inline-nombre').value = nombre;
        form.querySelector('.inline-apellido').value = apellido;
        form.querySelector('.inline-dni').value = dni;
        form.querySelector('.inline-telefono').value = telefono;

        // Cargar roles
        const roles = JSON.parse(rolesJson.replace(/&quot;/g, '"'));
        const inlineRoles = [];
        if (roles && roles.length > 0) {
            roles.forEach(r => {
                if (r.personaje) {
                    inlineRoles.push({ rol: 'actor', valor: r.personaje, icono: '🎭' });
                } else if (r.id === 'staff') {
                    const valor = r.nombre.replace('Staff-', '').replace('Staff', '');
                    if (valor) inlineRoles.push({ rol: 'staff', valor: valor, icono: '🔧' });
                } else if (r.id === 'donador') {
                    inlineRoles.push({ rol: 'donador', valor: 'Donador', icono: '🤝' });
                } else if (r.id === 'colaborador') {
                    inlineRoles.push({ rol: 'colaborador', valor: 'Colaborador', icono: '🤝' });
                } else if (r.id === 'otro') {
                    inlineRoles.push({ rol: 'otro', valor: r.nombre, icono: '⭐' });
                }
            });
        }

        // Guardar roles en el form
        form.dataset.roles = JSON.stringify(inlineRoles);
        renderInlineRoles(form, inlineRoles);

        form.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

// ── Toggle selector inline ──
function toggleInlineSelector(checkbox, rolId) {
    const row = checkbox.closest('.inline-rol-row');
    const selector = row.querySelector('.inline-selector');
    const inputOtro = row.querySelector('.inline-input-otro');
    const btnAddOtro = row.querySelector('.btn-add-otro');

    if (rolId === 'donador' || rolId === 'colaborador') {
        // Se agregan directo al check
        if (checkbox.checked) {
            addInlineRolDirecto(checkbox, rolId);
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

// ── Agregar rol directo inline (Donador/Colaborador) ──
function addInlineRolDirecto(checkbox, rolId) {
    const form = checkbox.closest('.inline-form');
    const roles = JSON.parse(form.dataset.roles || '[]');
    const iconos = { donador: '🤝', colaborador: '🤝' };
    const label = rolId.charAt(0).toUpperCase() + rolId.slice(1);

    const yaExiste = roles.some(r => r.rol === rolId);
    if (yaExiste) {
        showInlineMessage(form, 'Ya agregaste ' + label, 'error');
        checkbox.checked = false;
        return;
    }

    roles.push({ rol: rolId, valor: label, icono: iconos[rolId] });
    form.dataset.roles = JSON.stringify(roles);
    renderInlineRoles(form, roles);
}

// ── Agregar rol inline ──
function addInlineRol(select) {
    const valor = select.value;
    if (!valor) return;

    const rolId = select.dataset.rol;
    const form = select.closest('.inline-form');
    const roles = JSON.parse(form.dataset.roles || '[]');

    // Verificar duplicados
    const yaExiste = roles.some(r => r.rol === rolId && r.valor === valor);
    if (yaExiste) {
        showInlineMessage(form, 'Ya agregaste este rol', 'error');
        select.value = '';
        return;
    }

    const iconos = { actor: '🎭', staff: '🔧', sonido: '🎵', donante: '🤝', logistica: '🚚', otro: '⭐' };
    roles.push({ rol: rolId, valor: valor, icono: iconos[rolId] || '⭐' });
    form.dataset.roles = JSON.stringify(roles);
    renderInlineRoles(form, roles);
    select.value = '';
}

// ── Agregar rol Otro inline ──
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

// ── Renderizar roles inline ──
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

// ── Eliminar rol inline ──
function removeInlineRol(span, index) {
    const form = span.closest('.inline-form');
    const roles = JSON.parse(form.dataset.roles || '[]');
    roles.splice(index, 1);
    form.dataset.roles = JSON.stringify(roles);
    renderInlineRoles(form, roles);
}

// ── Mensaje inline ──
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

// ── Guardar edición inline ──
function saveInlineEdit(id) {
    const form = document.getElementById(`form-${id}`);
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

    fetch(API, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'update',
            id: id,
            nombre,
            apellido,
            dni,
            telefono,
            roles: roles.map(r => r.rol),
            personajes: roles.filter(r => r.rol === 'actor').map(r => r.valor),
            staffValores: roles.filter(r => r.rol === 'staff').map(r => r.valor),
            otroValores: roles.filter(r => r.rol === 'otro').map(r => r.valor)
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            showInlineMessage(form, '✅ Datos actualizados', 'success');
            // Cerrar formulario primero
            cancelInlineEdit(id);
            // Recargar lista después de 300ms
            setTimeout(() => loadPersonas(), 300);
        } else {
            showInlineMessage(form, '❌ ' + (data.error || 'Error desconocido'), 'error');
        }
    })
    .catch(err => {
        console.error('Error:', err);
        showInlineMessage(form, '❌ Error de conexión', 'error');
    });
}

// ── Cancelar edición inline ──
function cancelInlineEdit(id) {
    const form = document.getElementById(`form-${id}`);
    if (form) form.style.display = 'none';
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
    rolesAsignados = [];
    renderRolesAsignados();
    // Ocultar todos los selectores inline
    document.querySelectorAll('.personaje-inline').forEach(sel => sel.style.display = 'none');
    document.querySelectorAll('input[name="roles_check[]"]').forEach(cb => cb.checked = false);
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
