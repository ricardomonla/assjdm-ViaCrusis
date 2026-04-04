<?php
header('Content-Type: text/plain; charset=utf-8');
require __DIR__ . '/../data/db.php';

$db = getDB();

// 1. Encontrar y limpiar cues fantasma en TODOS los tracks
$ghosts = $db->query("SELECT track_id, cue_index, idp, substr(text,1,40) as preview FROM cues WHERE cue_index > 9999 ORDER BY track_id, cue_index");
$ghostRows = $ghosts->fetchAll(PDO::FETCH_ASSOC);

echo "=== FANTASMAS (cue_index > 9999) ===\n";
if (count($ghostRows) === 0) {
    echo "Ninguno encontrado. Todo limpio.\n";
} else {
    foreach ($ghostRows as $r) {
        printf("  track=%s idx=%d idp=%s text=%s\n", $r['track_id'], $r['cue_index'], $r['idp'], $r['preview']);
    }
    $db->exec("DELETE FROM cues WHERE cue_index > 9999");
    echo "--- " . count($ghostRows) . " fantasmas eliminados ---\n";
}

// 2. Verificar huecos en índices de cada track
$tracks = $db->query("SELECT DISTINCT track_id FROM cues ORDER BY track_id");
$fixed = 0;
foreach ($tracks->fetchAll(PDO::FETCH_COLUMN) as $tid) {
    $stmt = $db->prepare("SELECT id, cue_index FROM cues WHERE track_id = ? ORDER BY cue_index ASC");
    $stmt->execute([$tid]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $needsFix = false;
    foreach ($rows as $i => $r) {
        if ((int)$r['cue_index'] !== $i) { $needsFix = true; break; }
    }
    
    if ($needsFix) {
        $db->beginTransaction();
        foreach ($rows as $i => $r) {
            $db->exec("UPDATE cues SET cue_index = -" . ($i + 1) . " WHERE id = " . $r['id']);
        }
        foreach ($rows as $i => $r) {
            $db->exec("UPDATE cues SET cue_index = $i WHERE id = " . $r['id']);
        }
        $db->commit();
        echo "\nTrack $tid: reindexado (" . count($rows) . " cues, antes tenía huecos)\n";
        $fixed++;
    }
}

echo "\n=== RESUMEN: $fixed tracks corregidos ===\n";
echo "Listo.\n";
