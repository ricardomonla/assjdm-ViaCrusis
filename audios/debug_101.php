<?php
header('Content-Type: text/plain; charset=utf-8');
require __DIR__ . '/../data/db.php';

$db = getDB();

echo "=== ANTES ===\n";
$stmt = $db->query("SELECT cue_index, idp, character, start_time, substr(text,1,50) as preview FROM cues WHERE track_id = '101' ORDER BY cue_index");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
    printf("idx=%5d  idp=%-4s  t=%.1f  %s\n", $r['cue_index'], $r['idp'], $r['start_time'], $r['preview']);
}

// 1. Eliminar cue fantasma con index > 9999
$db->exec("DELETE FROM cues WHERE track_id = '101' AND cue_index > 9999");
echo "\n--- Eliminados cues con index > 9999 ---\n";

// 2. Reindexar secuencialmente (0,1,2,3,...)
$stmt = $db->query("SELECT id, cue_index FROM cues WHERE track_id = '101' ORDER BY cue_index ASC");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$db->beginTransaction();
// Primero negar todos
foreach ($rows as $i => $r) {
    $db->exec("UPDATE cues SET cue_index = -" . ($i + 1) . " WHERE id = " . $r['id']);
}
// Luego asignar secuencial
foreach ($rows as $i => $r) {
    $db->exec("UPDATE cues SET cue_index = $i WHERE id = " . $r['id']);
}
$db->commit();

echo "\n=== DESPUÉS (reindexado) ===\n";
$stmt = $db->query("SELECT cue_index, idp, character, start_time, substr(text,1,50) as preview FROM cues WHERE track_id = '101' ORDER BY cue_index");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
    printf("idx=%3d  idp=%-4s  t=%.1f  %s\n", $r['cue_index'], $r['idp'], $r['start_time'], $r['preview']);
}

echo "\nListo. Track 101 limpio.\n";
