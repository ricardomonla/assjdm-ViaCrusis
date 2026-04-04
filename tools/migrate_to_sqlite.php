<?php
/**
 * tools/migrate_to_sqlite.php — Migrar guion_completo.json a SQLite
 * 
 * Uso: php tools/migrate_to_sqlite.php [ruta_json]
 * Default: audios/subs/guion_completo.json
 */

// Cargar helper de DB
require __DIR__ . '/../data/db.php';

$jsonFile = $argv[1] ?? __DIR__ . '/../audios/subs/guion_completo.json';

if (!file_exists($jsonFile)) {
    die("ERROR: No se encuentra $jsonFile\n");
}

echo "=== Migración JSON → SQLite ===\n";
echo "Fuente: $jsonFile\n";

$guion = json_decode(file_get_contents($jsonFile), true);
if (!$guion) {
    die("ERROR: No se pudo leer/parsear el JSON\n");
}

$db = getDB();

// Limpiar tabla si ya tiene datos (re-migración)
$existing = $db->query("SELECT COUNT(*) as c FROM cues")->fetch();
if ($existing['c'] > 0) {
    echo "⚠ La tabla ya tiene {$existing['c']} registros. Limpiando...\n";
    $db->exec("DELETE FROM cues");
}

// Insertar todos los cues
$stmt = $db->prepare("INSERT INTO cues (track_id, cue_index, character, idp, start_time, end_time, text) VALUES (?, ?, ?, ?, ?, ?, ?)");

$totalTracks = 0;
$totalCues = 0;

$db->beginTransaction();

foreach ($guion as $trackId => $cues) {
    $totalTracks++;
    foreach ($cues as $idx => $cue) {
        $stmt->execute([
            $trackId,
            $idx,
            $cue['character'] ?? '',
            $cue['idp'] ?? 'P00',
            (float) ($cue['startTime'] ?? 0),
            (float) ($cue['endTime'] ?? 0),
            $cue['text'] ?? ''
        ]);
        $totalCues++;
    }
}

$db->commit();

echo "✅ Migración completada:\n";
echo "   Tracks: $totalTracks\n";
echo "   Cues:   $totalCues\n";
echo "   DB:     /var/www/vcby-data/vcby.db\n";

// Verificación
$verify = $db->query("SELECT COUNT(*) as c FROM cues")->fetch();
echo "   Registros en DB: {$verify['c']}\n";

// Muestra ejemplo
$sample = $db->query("SELECT track_id, cue_index, start_time, text FROM cues LIMIT 3")->fetchAll();
echo "\n   Muestra:\n";
foreach ($sample as $s) {
    echo "   [{$s['track_id']}][{$s['cue_index']}] t={$s['start_time']}s \"{$s['text']}\"\n";
}
