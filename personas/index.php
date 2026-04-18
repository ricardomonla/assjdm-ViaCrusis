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
                <div class="roles-checkboxes" id="roles-container">
                    <?php foreach ($roles as $rol): ?>
                    <div class="rol-checkbox-row">
                        <label class="rol-checkbox">
                            <input type="checkbox" name="roles[]" value="<?= htmlspecialchars($rol['id']) ?>" data-rol="<?= htmlspecialchars($rol['id']) ?>">
                            <span class="rol-label"><?= $rol['icono'] ?> <?= htmlspecialchars($rol['nombre']) ?></span>
                        </label>
                        <?php if ($rol['id'] === 'actor'): ?>
                        <!-- Selector inline para Actor -->
                        <div class="personaje-inline" id="personaje-inline" style="display:none; margin-left: 15px;">
                            <select id="personaje-select">
                                <option value="">-- Personaje --</option>
                                <?php foreach ($personajesDisponibles as $pj): ?>
                                <option value="<?= htmlspecialchars($pj['nombre']) ?>"
                                        <?= ($personajeSeleccionado === $pj['nombre']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($pj['nombre']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="button" class="btn-add-personaje" onclick="addPersonajeField()" title="Agregar otro personaje">➕</button>
                        </div>
                        <?php endif; ?>
                    </div>
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

// ===== SELECTOR DE PERSONAJES =====

// Mostrar/ocultar selector de personajes según rol Actor
function updatePersonajeSelector() {
    const esActor = document.querySelector('input[name="roles[]"][value="actor"]')?.checked;
    const inline = document.getElementById('personaje-inline');

    if (esActor && inline) {
        inline.style.display = 'flex';
    } else if (inline) {
        inline.style.display = 'none';
    }
}

// Agregar campo de personaje adicional
function addPersonajeField() {
    const container = document.getElementById('personajes-multiples');
    const select = document.getElementById('personaje-select');
    const opcionesDisponibles = Array.from(select.options).filter(o => o.value && !o.disabled);

    if (opcionesDisponibles.length === 0) {
        showMessage('No hay más personajes disponibles', 'error');
        return;
    }

    const nuevoSelect = select.cloneNode(true);
    nuevoSelect.id = 'personaje-select-' + Date.now();
    nuevoSelect.name = 'personajes[]';
    nuevoSelect.className = 'personaje-select-multiple';
    nuevoSelect.value = '';

    container.appendChild(nuevoSelect);
}

// Verificar si viene con personaje de URL y precargar placeholder
async function checkPersonajeFromURL() {
    const personajeUrl = document.getElementById('personaje-oculto')?.value;
    if (!personajeUrl) return;

    // Seleccionar el personaje en el combo
    const select = document.getElementById('personaje-select');
    if (select) {
        select.value = personajeUrl;
        select.dispatchEvent(new Event('change'));
    }

    // Verificar si existe placeholder
    try {
        const res = await fetch(API + '?action=placeholder&personaje=' + encodeURIComponent(personajeUrl));
        const data = await res.json();

        if (data.ok && data.placeholder) {
            // Es placeholder → completar datos automáticamente
            document.getElementById('persona-nombre').value = data.placeholder.nombre;
            document.getElementById('persona-apellido').value = data.placeholder.apellido || '';
            document.getElementById('persona-dni').value = data.placeholder.dni || '';
            document.getElementById('persona-telefono').value = data.placeholder.telefono || '';
            document.getElementById('persona-id').value = data.placeholder.id;

            document.getElementById('form-title').textContent = '📝 Completar datos de ' + personajeUrl;
            document.getElementById('btn-submit').textContent = '💾 GUARDAR CAMBIOS';
            document.getElementById('btn-cancel').style.display = '';

            showMessage('✅ Placeholder encontrado - completá los datos del actor', 'success');
        }
    } catch (e) {
        console.error('Error checking placeholder:', e);
    }
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

// ── Enviar formulario ──
document.getElementById('persona-form').addEventListener('submit', function(e) {
    e.preventDefault();

    const id = document.getElementById('persona-id').value;
    const nombre = document.getElementById('persona-nombre').value.trim();
    const apellido = document.getElementById('persona-apellido').value.trim();
    const dni = document.getElementById('persona-dni').value.trim();
    const telefono = document.getElementById('persona-telefono').value.trim();
    const roles = Array.from(document.querySelectorAll('input[name="roles[]"]:checked')).map(cb => cb.value);

    // Obtener personajes seleccionados (puede haber múltiples)
    const personajes = [];
    const selectPrincipal = document.getElementById('personaje-select');
    if (selectPrincipal && selectPrincipal.value) {
        personajes.push(selectPrincipal.value);
    }
    document.querySelectorAll('.personaje-select-multiple').forEach(sel => {
        if (sel.value) personajes.push(sel.value);
    });

    if (!nombre) {
        showMessage('El nombre es obligatorio', 'error');
        return;
    }

    const action = id ? 'update' : 'add';
    const body = { action, nombre, apellido, dni, telefono, roles, personajes };
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

// ── Editar persona (inline en la card) ──
function editPersona(id) {
    const card = document.querySelector(`.persona-card[data-id="${id}"]`);
    if (!card) return;

    // Marcar card como editando
    document.querySelectorAll('.card-editing').forEach(c => c.classList.remove('card-editing'));
    card.classList.add('card-editing');

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

            // Cargar personajes si es actor
            const personajes = p.roles.filter(r => r.personaje).map(r => r.personaje);
            const selectPrincipal = document.getElementById('personaje-select');
            if (selectPrincipal && personajes.length > 0) {
                selectPrincipal.value = personajes[0];
                // Agregar selects adicionales para más personajes
                for (let i = 1; i < personajes.length; i++) {
                    addPersonajeField();
                    const nuevosSelects = document.querySelectorAll('.personaje-select-multiple');
                    if (nuevosSelects[i-1]) {
                        nuevosSelects[i-1].value = personajes[i];
                    }
                }
            }

            document.getElementById('form-title').textContent = '📝 Actualizar datos de ' + p.nombre;
            document.getElementById('btn-submit').textContent = '💾 GUARDAR CAMBIOS';
            document.getElementById('btn-cancel').style.display = '';

            // Scroll suave hasta la card (no hasta el form)
            card.scrollIntoView({ behavior: 'smooth', block: 'center' });
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

    // Escuchar cambios en roles para mostrar selector de personaje
    document.querySelectorAll('input[name="roles[]"]').forEach(cb => {
        cb.addEventListener('change', updatePersonajeSelector);
    });

    // Inicializar selector
    updatePersonajeSelector();

    // Verificar si viene personaje de URL
    checkPersonajeFromURL();
});
</script>

<?php include __DIR__ . '/../incs/footer.php'; ?>
