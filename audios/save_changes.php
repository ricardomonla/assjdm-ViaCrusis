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
    // Si la operación fue exitosa, regenerar guion_completo.json
    if (!empty($data['ok'])) {
        exportGuionJSON();
    }
    if (ob_get_level()) ob_end_clean();
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Regenerar guion_completo.json desde SQLite (mantiene sync para modo offline/Android)
function exportGuionJSON() {
    try {
        $db = getDB();
        $stmt = $db->query("SELECT track_id, character, idp, start_time, end_time, text FROM cues ORDER BY track_id, cue_index");
        $rows = $stmt->fetchAll();
        if (empty($rows)) return;
        
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
        uksort($guion, function($a, $b) { return intval($a) - intval($b); });
        
        $jsonFile = __DIR__ . '/subs/guion_completo.json';
        file_put_contents($jsonFile, json_encode($guion, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    } catch (Exception $e) {
        // Fallo silencioso — no impide que el save principal funcione
    }
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
        
        // Usar startTime del frontend si viene, sino calcular
        if (!empty($_POST['startTime'])) {
            $newTime = round((float)$_POST['startTime'], 1);
        } else {
            $prevCue = getCueField($trackId, $cueIndex, 'startTime');
            $nextCue = getCueField($trackId, $cueIndex + 1, 'startTime');
            $prevTime = $prevCue !== null ? (float)$prevCue : 0;
            $nextTime = $nextCue !== null ? (float)$nextCue : $prevTime + 2;
            $newTime = round(($prevTime + $nextTime) / 2, 1);
        }
        
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

    // ===== Acción especial: Duplicar grupo completo =====
    if ($field === '_insert_batch') {
        $cuesJson = $_POST['cues'] ?? '[]';
        $cuesArray = json_decode($cuesJson, true);
        if (!is_array($cuesArray) || count($cuesArray) === 0) {
            jsonResponse(['ok' => false, 'msg' => 'Sin cues para insertar.']);
        }
        
        $count = insertBatchCues($trackId, $cueIndex, $cuesArray);
        
        jsonResponse([
            'ok' => true,
            'msg' => "Grupo duplicado: $count líneas insertadas después de cue $cueIndex.",
            'count' => $count
        ]);
    }

    // ===== Acción especial: Eliminar línea =====
    if ($field === '_delete') {
        deleteCue($trackId, $cueIndex);
        jsonResponse([
            'ok' => true,
            'msg' => "Línea $cueIndex eliminada de track $trackId."
        ]);
    }

    // ===== Acción especial: Eliminar grupo completo =====
    if ($field === '_delete_batch') {
        $indicesJson = $_POST['cue_indices'] ?? '[]';
        $indices = json_decode($indicesJson, true);
        if (!is_array($indices) || count($indices) === 0) {
            jsonResponse(['ok' => false, 'msg' => 'Sin índices para eliminar.']);
        }
        
        $count = deleteBatchCues($trackId, $indices);
        jsonResponse([
            'ok' => true,
            'msg' => "Grupo eliminado: $count líneas borradas de track $trackId.",
            'count' => $count
        ]);
    }

    // ===== Acción especial: Cambiar personaje de grupo completo =====
    if ($field === '_update_group_character') {
        $indicesJson = $_POST['cue_indices'] ?? '[]';
        $indices = json_decode($indicesJson, true);
        $newChar = $_POST['character'] ?? '';
        $newIdp = $_POST['idp'] ?? '';
        
        if (!is_array($indices) || count($indices) === 0 || !$newChar) {
            jsonResponse(['ok' => false, 'msg' => 'Datos incompletos.']);
        }
        
        $db = getDB();
        $placeholders = implode(',', array_fill(0, count($indices), '?'));
        $stmt = $db->prepare("UPDATE cues SET character = ?, idp = ? WHERE track_id = ? AND cue_index IN ($placeholders)");
        $params = array_merge([$newChar, $newIdp, $trackId], $indices);
        $stmt->execute($params);
        
        jsonResponse([
            'ok' => true,
            'msg' => "Personaje actualizado a $newIdp ($newChar) en " . count($indices) . " líneas."
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
