<?php
/**
 * audios/save_changes.php — Guardar cambios del Director via SQLite
 * 
 * POST params:
 *   track_id   - ID del track (ej: "101")
 *   cue_index  - Índice del cue modificado
 *   field      - Campo editado: "text", "character", "startTime", "endTime", "idp"
 *   value      - Nuevo valor
 *   field=_insert → Insertar acotación escénica
 */

// Garantizar salida JSON limpia
error_reporting(0);
ini_set('display_errors', '0');
header('Content-Type: application/json');
ob_start();

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_COMPILE_ERROR])) {
        ob_end_clean();
        echo json_encode(['ok' => false, 'msg' => 'Error PHP: ' . $error['message']]);
    }
});

require __DIR__ . '/../data/db.php';

// Helper: respuesta JSON limpia
function jsonResponse($data) {
    if (ob_get_level()) ob_end_clean();
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

$trackId   = $_POST['track_id'] ?? null;
$cueIndex  = isset($_POST['cue_index']) ? intval($_POST['cue_index']) : null;
$field     = $_POST['field'] ?? null;
$value     = $_POST['value'] ?? null;

// ===== Validaciones =====
if (!$trackId || $cueIndex === null || !$field) {
    jsonResponse(['ok' => false, 'msg' => 'Parámetros incompletos.']);
}

try {
    // ===== Acción especial: Insertar cue =====
    if ($field === '_insert') {
        $text = $value ?? '*(acotación escénica)*';
        $charName = $_POST['character'] ?? 'Música / Ambiente';
        $charIdp = $_POST['idp'] ?? 'P00';
        
        // Calcular startTime
        $prevCue = getCueField($trackId, $cueIndex, 'startTime');
        $nextCue = getCueField($trackId, $cueIndex + 1, 'startTime');
        $prevTime = $prevCue !== null ? (float)$prevCue : 0;
        $nextTime = $nextCue !== null ? (float)$nextCue : $prevTime + 2;
        $newTime = round(($prevTime + $nextTime) / 2, 1);
        
        $newIndex = insertCue($trackId, $cueIndex, [
            'character' => $charName,
            'idp' => $charIdp,
            'startTime' => $newTime,
            'endTime' => $newTime + 2.0,
            'text' => $text
        ]);
        
        jsonResponse([
            'ok' => true,
            'msg' => "Burbuja $charIdp insertada después de cue $cueIndex en track $trackId.",
            'new_index' => $newIndex
        ]);
    }

    // ===== Validar campo =====
    $allowedFields = ['text', 'character', 'startTime', 'endTime', 'idp'];
    if (!in_array($field, $allowedFields)) {
        jsonResponse(['ok' => false, 'msg' => "Campo '$field' no permitido."]);
    }

    // ===== Obtener valor actual =====
    $oldValue = getCueField($trackId, $cueIndex, $field);
    if ($oldValue === null) {
        jsonResponse(['ok' => false, 'msg' => "Track $trackId o cue $cueIndex no encontrado."]);
    }

    // ===== Aplicar cambio =====
    $newValue = ($field === 'startTime' || $field === 'endTime') ? floatval($value) : $value;
    updateCue($trackId, $cueIndex, $field, $newValue);

    jsonResponse([
        'ok' => true,
        'msg' => "Campo '$field' actualizado en track $trackId, cue $cueIndex.",
        'old_value' => $oldValue,
        'new_value' => $newValue
    ]);

} catch (Exception $e) {
    jsonResponse(['ok' => false, 'msg' => 'Error DB: ' . $e->getMessage()]);
}
