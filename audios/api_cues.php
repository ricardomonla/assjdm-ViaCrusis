<?php
/**
 * audios/api_cues.php — Endpoint REST para leer cues desde SQLite
 * 
 * GET ?track_id=101  → cues de un track
 * GET (sin params)   → todos los cues (equivale a guion_completo.json)
 */

error_reporting(0);
ini_set('display_errors', '0');
header('Content-Type: application/json');
header('Cache-Control: no-cache');

require __DIR__ . '/../data/db.php';

$trackId = $_GET['track_id'] ?? null;

try {
    if ($trackId) {
        $data = getCues($trackId);
        echo json_encode([$trackId => $data], JSON_UNESCAPED_UNICODE);
    } else {
        $data = getAllCues();
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'DB error: ' . $e->getMessage()]);
}
