<footer class="footer">
    <p>Versión: <span id="footer-version" class="footer-version"><?= htmlspecialchars($latestVersion) ?></span> | Asociación CAMPS
    <button id="admin-logout" class="admin-logout">🎬 Salir</button>
    </p>
</footer>

<div id="perfil-modal" class="admin-modal-overlay">
    <div class="admin-modal">
        <!-- Director: Opcion de salir -->
        <div id="perfil-step-logout" class="perfil-step" style="display: none;">
            <h3>🎬 Modo Director</h3>
            <p style="color:#4a3e31; margin: 10px 0;">Sesión activa. ¿Cerrar modo Director?</p>
            <div class="admin-modal-buttons">
                <button class="btn-admin-ok" onclick="perfilSelectPublico()">Salir del modo Director</button>
                <button class="btn-admin-cancel" onclick="perfilModalClose()">Cancelar</button>
            </div>
        </div>

        <!-- Público: Directo a clave de Director -->
        <div id="perfil-step-key" class="perfil-step" style="display: none;">
            <h3>🔐 Acceso Director</h3>
            <input type="password" id="perfil-key" placeholder="Clave de acceso" autocomplete="off">
            <p id="perfil-error" class="admin-error">Clave incorrecta</p>
            <div class="admin-modal-buttons">
                <button class="btn-admin-ok" onclick="perfilLoginDirector()">Entrar</button>
                <button class="btn-admin-cancel" onclick="perfilModalClose()">Cancelar</button>
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
    document.getElementById('perfil-error').style.display = 'none';

    if (window.VCBYPerfiles.isDirector()) {
        // Ya es Director → ofrecer salir
        document.getElementById('perfil-step-logout').style.display = '';
        document.getElementById('perfil-step-key').style.display = 'none';
    } else {
        // Es Público → directo a pedir clave
        document.getElementById('perfil-step-logout').style.display = 'none';
        document.getElementById('perfil-step-key').style.display = '';
        document.getElementById('perfil-key').value = '';
        setTimeout(function() { document.getElementById('perfil-key').focus(); }, 100);
    }
}

function perfilModalClose() {
    document.getElementById('perfil-modal').classList.remove('active');
}

function perfilSelectPublico() {
    window.VCBYPerfiles.cerrarSesion();
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