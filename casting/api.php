<?php
/**
 * casting/api.php — API REST para Personajes
 * 
 * GET ?director=1        → incluye teléfonos
 * POST action=signup     → postulación pública
 * POST action=delete     → eliminar (requiere key)
 * POST action=toggle     → habilitar/deshabilitar (requiere key)
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

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $showPhone = ($_GET['director'] ?? '0') === '1';
        $characters = getCharacters();
        $castings = getCastingList($showPhone);
        $enabled = getAllCastingEnabled();
        
        $byIdp = [];
        foreach ($castings as $c) {
            $byIdp[$c['idp']][] = $c;
        }
        
        echo json_encode([
            'ok' => true,
            'characters' => $characters,
            'castings' => $byIdp,
            'enabled' => $enabled
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
