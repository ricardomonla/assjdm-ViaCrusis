<?php
/**
 * api_scenes.php - Endpoint para guardar metadatos de las escenas (YouTube)
 */
header('Content-Type: application/json');
require_once '../data/db.php';

// Validar que es POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Leer datos
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data || !isset($data['action']) || !isset($data['scene_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
    exit;
}

$action = $data['action'];
$scene_id = $data['scene_id'];

try {
    $db = getDB();
    
    if ($action === 'update_youtube') {
        $videoId = $data['youtube_video_id'] ?? null;
        $timestamp = $data['youtube_timestamp'] ?? null;
        
        if ($videoId === null || $timestamp === null) {
            throw new Exception("Faltan parámetros youtube_video_id o youtube_timestamp");
        }
        
        $stmt = $db->prepare("UPDATE scenes SET youtube_video_id = ?, youtube_timestamp = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$videoId, (int)$timestamp, $scene_id]);
        
        echo json_encode(['success' => true]);
    } else {
        throw new Exception("Acción no reconocida");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
