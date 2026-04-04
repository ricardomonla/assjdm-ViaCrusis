<?php
header('Content-Type: text/plain; charset=utf-8');
require __DIR__ . '/../data/db.php';

$db = getDB();
$stmt = $db->prepare("SELECT cue_index, idp, character, start_time, substr(text,1,60) as preview FROM cues WHERE track_id = '101' ORDER BY cue_index");
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "=== Track 101: " . count($rows) . " cues ===\n\n";
foreach ($rows as $r) {
    printf("idx=%3d  idp=%-4s  char=%-20s  t=%.1f  text=%s\n", 
        $r['cue_index'], $r['idp'], $r['character'], $r['start_time'], $r['preview']);
}
