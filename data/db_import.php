<?php
/**
 * data/db_import.php — Importa datos migrados desde JSON (ejecutado por deploy.php)
 *
 * Se ejecuta automáticamente después del git pull en el webhook.
 * Importa las tablas: roles, personas, persona_roles
 */

require_once __DIR__ . '/../data/db.php';

$db = getDB();
ensureSchema();

$jsonPath = __DIR__ . '/migration_personas.json';

if (!file_exists($jsonPath)) {
    error_log("[db_import] No hay archivo de migración: $jsonPath");
    return;
}

$json = file_get_contents($jsonPath);
$export = json_decode($json, true);

if (!$export) {
    error_log("[db_import] JSON inválido: $jsonPath");
    return;
}

$imported = [];

// 1. Importar roles (INSERT OR IGNORE)
if (!empty($export['tables']['roles'])) {
    $stmt = $db->prepare("INSERT OR IGNORE INTO roles (id, nombre, icono) VALUES (?, ?, ?)");
    $count = 0;
    foreach ($export['tables']['roles'] as $rol) {
        $stmt->execute([$rol['id'], $rol['nombre'], $rol['icono']]);
        $count++;
    }
    $imported['roles'] = $count;
}

// 2. Importar personas (INSERT OR REPLACE - sobrescribe IDs existentes)
if (!empty($export['tables']['personas'])) {
    $stmt = $db->prepare("
        INSERT OR REPLACE INTO personas (id, nombre, apellido, dni, telefono, enabled, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $count = 0;
    foreach ($export['tables']['personas'] as $p) {
        $stmt->execute([
            $p['id'],
            $p['nombre'],
            $p['apellido'],
            $p['dni'],
            $p['telefono'],
            $p['enabled'] ?? 1,
            $p['created_at'] ?? date('Y-m-d H:i:s')
        ]);
        $count++;
    }
    $imported['personas'] = $count;
}

// 3. Importar persona_roles (INSERT OR IGNORE)
if (!empty($export['tables']['persona_roles'])) {
    $stmt = $db->prepare("
        INSERT OR IGNORE INTO persona_roles (persona_id, rol_id, personaje)
        VALUES (?, ?, ?)
    ");
    $count = 0;
    foreach ($export['tables']['persona_roles'] as $pr) {
        $stmt->execute([$pr['persona_id'], $pr['rol_id'], $pr['personaje'] ?? '']);
        $count++;
    }
    $imported['persona_roles'] = $count;
}

// Log de importación
$log = date('Y-m-d H:i:s') . " | Migración 26.12: " . json_encode($imported) . "\n";
file_put_contents(__DIR__ . '/import.log', $log, FILE_APPEND | LOCK_EX);

echo "[db_import] Migración completada: " . json_encode($imported) . "\n";

// NOTA: No se renombra el archivo para permitir migraciones idempotentes.
// INSERT OR IGNORE evita duplicados en ejecuciones futuras.
