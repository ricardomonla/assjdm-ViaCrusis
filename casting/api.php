<?php
/**
 * casting/api.php — API REST para Personajes
 * 
 * GET ?director=1        → incluye teléfonos
 * POST action=signup     → postulación pública
 * POST action=delete     → eliminar (requiere key)
 * POST action=toggle     → habilitar/deshabilitar (requiere key)
 * 
 * Fallback Android: si SQLite no está disponible, usa 00_Personajes.md
 */

error_reporting(0);
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache');

require __DIR__ . '/../data/db.php';

define('ADMIN_KEY', 'VCBY2026');

function checkKey() {
    return ($_POST['key'] ?? '') === ADMIN_KEY;
}

/**
 * Fallback: leer personajes de 00_Personajes.md si SQLite no está
 */
function getCharactersFallback() {
    $file = __DIR__ . '/../audios/subs/00_Personajes.md';
    $chars = [];
    if (file_exists($file)) {
        preg_match_all('/\|\s*(P\d+)\s*\|\s*([^|]+?)\s*\|\s*([^|]*?)\s*\|/', file_get_contents($file), $m);
        for ($i = 0; $i < count($m[1]); $i++) {
            $chars[] = [
                'idp' => trim($m[1][$i]),
                'character' => trim($m[2][$i]),
                'synopsis' => trim($m[3][$i])
            ];
        }
    }
    return $chars;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $showPhone = ($_GET['director'] ?? '0') === '1';
        
        // Intentar SQLite, fallback a archivo .md
        $useFallback = false;
        try {
            $characters = getCharacters();
            $castings = getCastingList($showPhone);
            $enabled = getAllCastingEnabled();
        } catch (Exception $e) {
            $useFallback = true;
            $characters = getCharactersFallback();
            $castings = [];
            $enabled = [];
        }
        
        $byIdp = [];
        if (!$useFallback) {
            foreach ($castings as $c) {
                $byIdp[$c['idp']][] = $c;
            }
        }
        
        echo json_encode([
            'ok' => true,
            'characters' => $characters,
            'castings' => $byIdp,
            'enabled' => $enabled,
            'readonly' => $useFallback
        ], JSON_UNESCAPED_UNICODE);
        
    } elseif ($method === 'POST') {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'signup') {
            $idp = trim($_POST['idp'] ?? '');
            $charName = trim($_POST['character_name'] ?? '');
            $nombre = trim($_POST['nombre'] ?? '');
            $apellido = trim($_POST['apellido'] ?? '');
            $telefono = trim($_POST['telefono'] ?? '');
            
            if (!$idp || !$charName || !$nombre || !$apellido || !$telefono) {
                echo json_encode(['ok' => false, 'msg' => 'Todos los campos son obligatorios.']);
                exit;
            }
            
            $id = addCasting($idp, $charName, $nombre, $apellido, $telefono);
            echo json_encode(['ok' => true, 'id' => $id, 'msg' => '¡Postulación registrada!']);
            
        } elseif ($action === 'delete') {
            if (!checkKey()) {
                echo json_encode(['ok' => false, 'msg' => 'No autorizado.']);
                exit;
            }
            $id = intval($_POST['id'] ?? 0);
            deleteCasting($id);
            echo json_encode(['ok' => true, 'msg' => 'Postulación eliminada.']);
            
        } elseif ($action === 'toggle') {
            if (!checkKey()) {
                echo json_encode(['ok' => false, 'msg' => 'No autorizado.']);
                exit;
            }
            $idp = trim($_POST['idp'] ?? '');
            $enabled = intval($_POST['enabled'] ?? 0);
            toggleCastingEnabled($idp, $enabled);
            echo json_encode(['ok' => true, 'msg' => $enabled ? 'Habilitado' : 'Deshabilitado']);
            
        } else {
            echo json_encode(['ok' => false, 'msg' => 'Acción desconocida.']);
        }
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => 'Error: ' . $e->getMessage()]);
}
