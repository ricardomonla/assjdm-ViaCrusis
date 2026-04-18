<?php
/**
 * tools/db_export.php — Exporta datos de la DB local a JSON para migrar a producción
 *
 * Uso: php tools/db_export.php
 * Output: data/migration_personas.json (se commitea al repo)
 */

require_once __DIR__ . '/../data/db.php';

$db = getDB();
ensureSchema();

$export = [
    'version' => '26.12',
    'date' => date('Y-m-d H:i:s'),
    'tables' => []
];

// Exportar roles
$roles = $db->query("SELECT * FROM roles ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$export['tables']['roles'] = $roles;

// Exportar personas
$personas = $db->query("SELECT * FROM personas ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$export['tables']['personas'] = $personas;

// Exportar persona_roles
$persona_roles = $db->query("SELECT * FROM persona_roles ORDER BY persona_id, rol_id")->fetchAll(PDO::FETCH_ASSOC);
$export['tables']['persona_roles'] = $persona_roles;

// Guardar JSON
$jsonPath = __DIR__ . '/../data/migration_personas.json';
file_put_contents($jsonPath, json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "Exportación completada:\n";
echo "  - Roles: " . count($roles) . "\n";
echo "  - Personas: " . count($personas) . "\n";
echo "  - Persona_roles: " . count($persona_roles) . "\n";
echo "  - Archivo: data/migration_personas.json\n";
