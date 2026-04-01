<footer class="footer">
    <p>Versión: <span id="footer-version" class="footer-version"><?= htmlspecialchars($latestVersion) ?></span> | Asociación CAMPS
    <button id="admin-logout" class="admin-logout" onclick="adminLogout()">Salir ↗</button>
    </p>
</footer>

<!-- Modal Admin Login -->
<div id="admin-modal" class="admin-modal-overlay">
    <div class="admin-modal">
        <h3>🔐 Acceso Admin</h3>
        <input type="password" id="admin-key" placeholder="Clave de acceso" autocomplete="off">
        <p id="admin-error" class="admin-error">Clave incorrecta</p>
        <div class="admin-modal-buttons">
            <button class="btn-admin-ok" onclick="adminLogin()">Entrar</button>
            <button class="btn-admin-cancel" onclick="adminModalClose()">Cancelar</button>
        </div>
    </div>
</div>

<script>
// ===== ADMIN SESSION MANAGEMENT =====
const ADMIN_TTL = 30 * 60 * 1000; // 30 minutos en ms

// Verificar sesión al cargar
(function checkAdminSession() {
    const session = localStorage.getItem('vcby_admin');
    if (session) {
        const data = JSON.parse(session);
        if (Date.now() - data.ts < ADMIN_TTL) {
            document.body.classList.add('admin-mode');
        } else {
            localStorage.removeItem('vcby_admin');
        }
    }
})();

// 5 taps en la versión para abrir modal
let tapCount = 0;
let tapTimer = null;
document.getElementById('footer-version').addEventListener('click', function() {
    tapCount++;
    clearTimeout(tapTimer);
    tapTimer = setTimeout(function() { tapCount = 0; }, 1500);
    if (tapCount >= 5) {
        tapCount = 0;
        adminModalOpen();
    }
});

function adminModalOpen() {
    document.getElementById('admin-modal').classList.add('active');
    document.getElementById('admin-key').value = '';
    document.getElementById('admin-error').style.display = 'none';
    setTimeout(function() { document.getElementById('admin-key').focus(); }, 100);
}

function adminModalClose() {
    document.getElementById('admin-modal').classList.remove('active');
}

function adminLogin() {
    const key = document.getElementById('admin-key').value;
    // Validar via fetch al servidor
    fetch('../admin_check.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'key=' + encodeURIComponent(key)
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.ok) {
            localStorage.setItem('vcby_admin', JSON.stringify({ ts: Date.now() }));
            document.body.classList.add('admin-mode');
            adminModalClose();
            location.reload(); // Recargar para mostrar botones de descarga desde PHP
        } else {
            document.getElementById('admin-error').style.display = 'block';
        }
    })
    .catch(function() {
        document.getElementById('admin-error').style.display = 'block';
    });
}

function adminLogout() {
    localStorage.removeItem('vcby_admin');
    document.body.classList.remove('admin-mode');
    location.reload();
}

// Enter key en el input
document.getElementById('admin-key').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') adminLogin();
    if (e.key === 'Escape') adminModalClose();
});

// Click fuera del modal para cerrar
document.getElementById('admin-modal').addEventListener('click', function(e) {
    if (e.target === this) adminModalClose();
});
</script>
</body>
</html>