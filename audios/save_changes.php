<?php
/**
 * audios/save_changes.php — Endpoint para guardar cambios del Director
 * 
 * Recibe ediciones de subtítulos via POST, actualiza el v4.0.md correspondiente,
 * recompila el JSON maestro, y opcionalmente ejecuta un git commit local.
 * 
 * POST params:
 *   track_id   - ID del track (ej: "101")
 *   cue_index  - Índice del cue modificado
 *   field      - Campo editado: "text", "character", "startTime"
 *   value      - Nuevo valor
 *   commit_msg - (opcional) Mensaje de commit. Si se envía, se ejecuta git commit.
 */

// Garantizar salida JSON limpia (sin warnings/notices HTML)
error_reporting(0);
ini_set('display_errors', '0');
header('Content-Type: application/json');

// Capturar cualquier output inesperado
ob_start();

// Handler de errores fatales
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_COMPILE_ERROR])) {
        ob_end_clean();
        echo json_encode(['ok' => false, 'msg' => 'Error PHP: ' . $error['message']]);
    }
});

// Validar que sea modo admin/director
@require '../incs/functions.php';

// Helper: respuesta JSON limpia (descarta cualquier warning capturado)
function jsonResponse($data) {
    if (ob_get_level()) ob_end_clean();
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

$trackId   = $_POST['track_id'] ?? null;
$cueIndex  = isset($_POST['cue_index']) ? intval($_POST['cue_index']) : null;
$field     = $_POST['field'] ?? null;
$value     = $_POST['value'] ?? null;
$commitMsg = $_POST['commit_msg'] ?? null;

// ===== Validaciones =====
if (!$trackId || $cueIndex === null || !$field) {
    jsonResponse(['ok' => false, 'msg' => 'Parámetros incompletos.']);
}

$jsonFile = __DIR__ . '/subs/guion_completo.json';
if (!file_exists($jsonFile)) {
    jsonResponse(['ok' => false, 'msg' => 'guion_completo.json no encontrado.']);
}

// ===== Leer JSON =====
$guion = json_decode(file_get_contents($jsonFile), true);
// ===== Acción especial: Insertar cue (Acotación Escénica) =====
if ($field === '_insert') {
    if (!isset($guion[$trackId])) {
        jsonResponse(['ok' => false, 'msg' => "Track $trackId no encontrado."]);
    }
    
    $insertAfter = $cueIndex; // Insertar DESPUÉS de este índice
    $text = $value ?? '*(acotación escénica)*';
    
    // Calcular startTime: promedio entre el cue actual y el siguiente
    $prevTime = isset($guion[$trackId][$insertAfter]) ? $guion[$trackId][$insertAfter]['startTime'] : 0;
    $nextTime = isset($guion[$trackId][$insertAfter + 1]) ? $guion[$trackId][$insertAfter + 1]['startTime'] : $prevTime + 2;
    $newTime = round(($prevTime + $nextTime) / 2, 1);
    
    $newCue = [
        'character' => 'Música / Ambiente',
        'idp' => 'P00',
        'startTime' => $newTime,
        'endTime' => $newTime + 2.0,
        'text' => $text
    ];
    
    // Insertar en el array
    array_splice($guion[$trackId], $insertAfter + 1, 0, [$newCue]);
    
    // Guardar JSON
    file_put_contents($jsonFile, json_encode($guion, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    
    // Sincronizar v4.0.md
    $mdFile = __DIR__ . "/subs/{$trackId}_v4.0.md";
    if (file_exists($mdFile)) {
        rewriteV4FromJson($trackId, $guion[$trackId], $mdFile);
    }
    
    // Commit si se pidió
    $commitResult = null;
    if ($commitMsg) {
        $scriptPath = realpath(__DIR__ . '/../tools/commit_cambios.sh');
        if ($scriptPath && file_exists($scriptPath)) {
            $output = shell_exec("bash $scriptPath " . escapeshellarg($commitMsg) . " 2>&1");
            $commitResult = $output;
        }
    }
    
    jsonResponse([
        'ok' => true,
        'msg' => "Acotación insertada después de cue $insertAfter en track $trackId.",
        'new_index' => $insertAfter + 1,
        'commit' => $commitResult
    ]);
}

// ===== Validación de cue existente =====
if (!isset($guion[$trackId][$cueIndex])) {
    jsonResponse(['ok' => false, 'msg' => "Track $trackId o cue $cueIndex no encontrado."]);
}

// ===== Aplicar cambio =====
$allowedFields = ['text', 'character', 'startTime', 'endTime', 'idp'];
if (!in_array($field, $allowedFields)) {
    jsonResponse(['ok' => false, 'msg' => "Campo '$field' no permitido."]);
}

$oldValue = $guion[$trackId][$cueIndex][$field] ?? null;

if ($field === 'startTime' || $field === 'endTime') {
    $guion[$trackId][$cueIndex][$field] = floatval($value);
} else {
    $guion[$trackId][$cueIndex][$field] = $value;
}

// ===== Guardar JSON =====
file_put_contents($jsonFile, json_encode($guion, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

// ===== Actualizar v4.0.md (sincronizar) =====
$mdFile = __DIR__ . "/subs/{$trackId}_v4.0.md";
if (file_exists($mdFile) && ($field === 'text' || $field === 'character' || $field === 'idp' || $field === 'startTime')) {
    // Reescribir el v4.0.md desde el JSON actualizado
    rewriteV4FromJson($trackId, $guion[$trackId], $mdFile);
}

// ===== Commit (si se pidió) =====
$commitResult = null;
if ($commitMsg) {
    $scriptPath = realpath(__DIR__ . '/../tools/commit_cambios.sh');
    if ($scriptPath && file_exists($scriptPath)) {
        $escapedMsg = escapeshellarg($commitMsg);
        $output = shell_exec("bash $scriptPath $escapedMsg 2>&1");
        $commitResult = $output;
    }
}

jsonResponse([
    'ok' => true,
    'msg' => "Campo '$field' actualizado en track $trackId, cue $cueIndex.",
    'old_value' => $oldValue,
    'new_value' => $guion[$trackId][$cueIndex][$field],
    'commit' => $commitResult
]);

// ===== Helper: Reescribir v4.0.md desde JSON =====
function rewriteV4FromJson($trackId, $cues, $mdFile) {
    $content = file_get_contents($mdFile);
    $lines = explode("\n", $content);
    
    // Encontrar sección ## 2. Subtítulos
    $subStart = null;
    $subEnd = null;
    for ($i = 0; $i < count($lines); $i++) {
        if (preg_match('/^## 2\./i', trim($lines[$i]))) {
            $subStart = $i;
        }
        if ($subStart !== null && $i > $subStart + 2 && preg_match('/^## /i', trim($lines[$i]))) {
            $subEnd = $i;
            break;
        }
    }
    
    if ($subStart === null) return; // No section found
    if ($subEnd === null) $subEnd = count($lines);
    
    // Reconstruir la tabla de subtítulos
    $newTable = [];
    $newTable[] = "## 2. Subtítulos";
    $newTable[] = "";
    $newTable[] = "| MARCA | IDP | SUBTITULO |";
    $newTable[] = "|:---|:---|:---|";
    
    foreach ($cues as $cue) {
        $startTime = $cue['startTime'];
        $hh = str_pad(floor($startTime / 3600), 2, '0', STR_PAD_LEFT);
        $mm = str_pad(floor(($startTime % 3600) / 60), 2, '0', STR_PAD_LEFT);
        $ss = str_pad(floor($startTime % 60), 2, '0', STR_PAD_LEFT);
        $mark = "[{$trackId}.{$hh}.{$mm}.{$ss}]";
        $idp = $cue['idp'] ?? 'P00';
        $text = $cue['text'] ?? '';
        $newTable[] = "| $mark | $idp | $text |";
    }
    $newTable[] = "";
    
    // Reemplazar sección
    $before = array_slice($lines, 0, $subStart);
    $after = ($subEnd < count($lines)) ? array_slice($lines, $subEnd) : [];
    
    $newLines = array_merge($before, $newTable, $after);
    file_put_contents($mdFile, implode("\n", $newLines));
}
