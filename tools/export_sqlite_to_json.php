<?php
/**
 * tools/export_sqlite_to_json.php — Exportar SQLite → guion_completo.json
 * 
 * Sincroniza los datos editados por el Director (SQLite) al JSON estático
 * que usan los celulares/actores vía git pull (modo offline).
 * 
 * Uso CLI:    php tools/export_sqlite_to_json.php
 * Uso Web:    GET /tools/export_sqlite_to_json.php (responde JSON)
 * 
 * Se puede invocar manualmente o integrado al flujo de commit.
 */

$isWeb = php_sapi_name() !== 'cli';
if ($isWeb) {
    header('Content-Type: application/json');
}

// Cargar helper de DB
require __DIR__ . '/../data/db.php';

$jsonFile = __DIR__ . '/../audios/subs/guion_completo.json';

try {
    $db = getDB();
    
    // Leer todos los cues ordenados
    $stmt = $db->query("SELECT track_id, cue_index, character, idp, start_time, end_time, text FROM cues ORDER BY track_id, cue_index");
    $rows = $stmt->fetchAll();
    
    if (empty($rows)) {
        $msg = "⚠ La base de datos está vacía. No se sobreescribe el JSON.";
        if ($isWeb) { echo json_encode(['ok' => false, 'msg' => $msg]); exit; }
        die("$msg\n");
    }
    
    // Armar estructura idéntica al guion_completo.json original
    $guion = [];
    foreach ($rows as $row) {
        $tid = $row['track_id'];
        if (!isset($guion[$tid])) $guion[$tid] = [];
        $guion[$tid][] = [
            'character' => $row['character'],
            'idp'       => $row['idp'],
            'startTime' => (float) $row['start_time'],
            'endTime'   => (float) $row['end_time'],
            'text'      => $row['text']
        ];
    }
    
    // Ordenar tracks numéricamente
    uksort($guion, function($a, $b) {
        return intval($a) - intval($b);
    });
    
    // Backup del JSON anterior (por si acaso)
    if (file_exists($jsonFile)) {
        $backupFile = $jsonFile . '.bak';
        copy($jsonFile, $backupFile);
    }
    
    // Escribir JSON formateado (legible en git diffs)
    $jsonOutput = json_encode($guion, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    file_put_contents($jsonFile, $jsonOutput);
    
    // Estadísticas
    $totalTracks = count($guion);
    $totalCues = count($rows);
    $fileSize = strlen($jsonOutput);
    
    $msg = "✅ Exportación completada: $totalTracks tracks, $totalCues cues ($fileSize bytes)";
    
    if ($isWeb) {
        echo json_encode([
            'ok' => true,
            'msg' => $msg,
            'tracks' => $totalTracks,
            'cues' => $totalCues,
            'file' => basename($jsonFile),
            'size' => $fileSize
        ]);
    } else {
        echo "=== Exportación SQLite → JSON ===\n";
        echo "   Tracks: $totalTracks\n";
        echo "   Cues:   $totalCues\n";
        echo "   Archivo: $jsonFile\n";
        echo "   Tamaño:  $fileSize bytes\n";
        echo "$msg\n";
    }

} catch (Exception $e) {
    $msg = "Error: " . $e->getMessage();
    if ($isWeb) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'msg' => $msg]);
    } else {
        die("❌ $msg\n");
    }
}
