<?php
/**
 * deploy.php — Webhook de deploy automático
 * 
 * GitHub envía POST a esta URL al hacer push.
 * Ejecuta git pull para actualizar el código en producción.
 * 
 * Configuración en GitHub:
 *   Settings → Webhooks → Add webhook
 *   URL: https://rmonla.duckdns.org/vcby/deploy.php
 *   Content type: application/json
 *   Secret: (el definido abajo)
 *   Events: Just the push event
 */

// Secreto compartido con GitHub (cambiar en producción)
define('WEBHOOK_SECRET', 'vcby2026deploy');

// Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method not allowed');
}

// Verificar firma de GitHub
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';

if ($signature) {
    $expected = 'sha256=' . hash_hmac('sha256', $payload, WEBHOOK_SECRET);
    if (!hash_equals($expected, $signature)) {
        http_response_code(403);
        die('Invalid signature');
    }
}

// Ejecutar git pull con fix de permisos
$repoDir = dirname(__FILE__);
$output = [];
$returnCode = 0;

chdir($repoDir);

// Intentar arreglar permisos antes de git pull
exec('sudo chown -R www-data:www-data /var/www/vcby/.git 2>&1', $permOutput, $permCode);

// Git pull
exec('git -C /var/www/vcby pull origin main 2>&1', $output, $returnCode);

// Log
$log = date('Y-m-d H:i:s') . " | code=$returnCode | " . implode(' ', $output) . "\n";
file_put_contents(__DIR__ . '/deploy.log', $log, FILE_APPEND | LOCK_EX);

// Si git pull fue exitoso, forzar migración de DB
if ($returnCode === 0) {
    // IMPORTANTE: Forzar checkout del JSON desde el repo (sobrescribe local)
    $checkoutOutput = [];
    $checkoutCode = 0;
    exec('git -C /var/www/vcby checkout origin/main -- data/migration_personas.json 2>&1', $checkoutOutput, $checkoutCode);

    // Ejecutar migración (idempotente: INSERT OR IGNORE)
    $migrateOutput = [];
    $migrateCode = 0;
    chdir($repoDir);
    exec('sudo -u www-data php data/db_import.php 2>&1', $migrateOutput, $migrateCode);

    // Log de migración
    if (!empty($migrateOutput)) {
        $migrateLog = date('Y-m-d H:i:s') . " | DB import: " . implode(' ', $migrateOutput) . "\n";
        file_put_contents(__DIR__ . '/deploy.log', $migrateLog, FILE_APPEND | LOCK_EX);
    }
}

// Respuesta
header('Content-Type: application/json');
echo json_encode([
    'ok' => $returnCode === 0,
    'output' => implode("\n", $output),
    'time' => date('Y-m-d H:i:s')
]);
