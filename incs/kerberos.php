<?php
// Verificar si se pasa el parámetro 'key' con el valor 'VCBY2026'
if (!isset($_GET['key']) || $_GET['key'] !== 'VCBY2026') {
    header('Location: error.php');
    exit();
}
?>
<?php include 'header.php'; ?>
    <main class="main-content">
        <p>Contenido protegido accesible solo con la clave correcta.</p>
    </main>
<?php include 'footer.php'; ?>

