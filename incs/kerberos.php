<?php
// Verificar si se pasa el parámetro 'key' con el valor 'VCBY2026'
if (!isset($_GET['key']) || $_GET['key'] !== 'VCBY2026') {
    http_response_code(403);
    die('Acceso denegado.');
}
?>
<?php include __DIR__ . '/header.php'; ?>
    <main class="main-content">
        <p>Contenido protegido accesible solo con la clave correcta.</p>
    </main>
<?php include __DIR__ . '/footer.php'; ?>

