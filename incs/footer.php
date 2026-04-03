<footer class="footer">
    <p>Versión: <span id="footer-version" class="footer-version"><?= htmlspecialchars($latestVersion) ?></span> | Asociación CAMPS
    <button id="admin-logout" class="admin-logout">🎬 Salir</button>
    </p>
</footer>

<!-- Modal de Perfiles (Público / Director) -->
<div id="perfil-modal" class="admin-modal-overlay">
    <div class="admin-modal">
        <!-- Paso 1: Elegir Perfil -->
        <div id="perfil-step-choose" class="perfil-step">
            <h3>👤 Elegir Perfil</h3>
            <div class="perfil-options">
                <button class="btn-perfil-option btn-perfil-publico" onclick="perfilSelectPublico()">
                    <span class="perfil-option-icon">👥</span>
                    <span class="perfil-option-label">Público</span>
                    <span class="perfil-option-desc">Escuchar y ver subtítulos</span>
                </button>
                <button class="btn-perfil-option btn-perfil-director" onclick="perfilShowDirectorKey()">
                    <span class="perfil-option-icon">🎬</span>
                    <span class="perfil-option-label">Director</span>
                    <span class="perfil-option-desc">Editar, anotar y gestionar</span>
                </button>
            </div>
            <div class="admin-modal-buttons" style="margin-top: 12px;">
                <button class="btn-admin-cancel" onclick="perfilModalClose()">Cancelar</button>
            </div>
        </div>

        <!-- Paso 2: Clave de Director -->
        <div id="perfil-step-key" class="perfil-step" style="display: none;">
            <h3>🔐 Acceso Director</h3>
            <input type="password" id="perfil-key" placeholder="Clave de acceso" autocomplete="off">
            <p id="perfil-error" class="admin-error">Clave incorrecta</p>
            <div class="admin-modal-buttons">
                <button class="btn-admin-ok" onclick="perfilLoginDirector()">Entrar</button>
                <button class="btn-admin-cancel" onclick="perfilBackToChoose()">Volver</button>
            </div>
        </div>
    </div>
</div>

<script src="../jss/perfiles.js?v=<?= urlencode($latestVersion ?? '1.0') ?>"></script>
<script>
// ===== PERFIL MODAL MANAGEMENT =====

// 5 taps en la versión para abrir modal de perfiles
let tapCount = 0;
let tapTimer = null;
document.getElementById('footer-version').addEventListener('click', function() {
    tapCount++;
    clearTimeout(tapTimer);
    tapTimer = setTimeout(function() { tapCount = 0; }, 1500);
    if (tapCount >= 5) {
        tapCount = 0;
        perfilModalOpen();
    }
});

function perfilModalOpen() {
    document.getElementById('perfil-modal').classList.add('active');
    document.getElementById('perfil-step-choose').style.display = '';
    document.getElementById('perfil-step-key').style.display = 'none';
    document.getElementById('perfil-error').style.display = 'none';
}

function perfilModalClose() {
    document.getElementById('perfil-modal').classList.remove('active');
}

function perfilSelectPublico() {
    // Público no requiere clave, simplemente cerrar sesión director si hubiera
    window.VCBYPerfiles.cerrarSesion();
}

function perfilShowDirectorKey() {
    document.getElementById('perfil-step-choose').style.display = 'none';
    document.getElementById('perfil-step-key').style.display = '';
    document.getElementById('perfil-key').value = '';
    document.getElementById('perfil-error').style.display = 'none';
    setTimeout(function() { document.getElementById('perfil-key').focus(); }, 100);
}

function perfilBackToChoose() {
    document.getElementById('perfil-step-key').style.display = 'none';
    document.getElementById('perfil-step-choose').style.display = '';
}

function perfilLoginDirector() {
    const key = document.getElementById('perfil-key').value;
    fetch('../admin_check.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'key=' + encodeURIComponent(key)
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.ok) {
            window.VCBYPerfiles.activarDirector();
            perfilModalClose();
            location.reload();
        } else {
            document.getElementById('perfil-error').style.display = 'block';
        }
    })
    .catch(function() {
        document.getElementById('perfil-error').style.display = 'block';
    });
}

// Enter/Escape keys en el input de clave
document.getElementById('perfil-key').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') perfilLoginDirector();
    if (e.key === 'Escape') perfilModalClose();
});

// Click fuera del modal para cerrar
document.getElementById('perfil-modal').addEventListener('click', function(e) {
    if (e.target === this) perfilModalClose();
});
</script>
</body>
</html>