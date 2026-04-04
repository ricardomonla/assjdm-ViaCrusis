<?php
/**
 * casting/api.php — API REST para Personajes
 * 
 * GET                    → lista personajes y postulaciones
 * POST action=signup     → nueva postulación
 * POST action=delete     → eliminar postulación (Director)
 * POST action=toggle     → habilitar/deshabilitar personaje (Director)
 */

error_reporting(0);
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache');

require __DIR__ . '/../data/db.php';

function isDirector() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $isAdmin = isDirector();
        $characters = getCharacters();
        $castings = getCastingList($isAdmin);
        $enabled = getAllCastingEnabled();
        
        $byIdp = [];
        foreach ($castings as $c) {
            $byIdp[$c['idp']][] = $c;
        }
        
        echo json_encode([
            'ok' => true,
            'characters' => $characters,
            'castings' => $byIdp,
            'enabled' => $enabled,
            'isDirector' => $isAdmin
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
            
            // Verificar que el personaje esté habilitado
            if (!isCastingEnabled($idp) && !isDirector()) {
                echo json_encode(['ok' => false, 'msg' => 'Este personaje no está abierto para postulaciones.']);
                exit;
            }
            
            $id = addCasting($idp, $charName, $nombre, $apellido, $telefono);
            echo json_encode(['ok' => true, 'id' => $id, 'msg' => '¡Postulación registrada!']);
            
        } elseif ($action === 'delete') {
            if (!isDirector()) {
                echo json_encode(['ok' => false, 'msg' => 'No autorizado.']);
                exit;
            }
            $id = intval($_POST['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['ok' => false, 'msg' => 'ID inválido.']);
                exit;
            }
            deleteCasting($id);
            echo json_encode(['ok' => true, 'msg' => 'Postulación eliminada.']);
            
        } elseif ($action === 'toggle') {
            if (!isDirector()) {
                echo json_encode(['ok' => false, 'msg' => 'No autorizado.']);
                exit;
            }
            $idp = trim($_POST['idp'] ?? '');
            $enabled = intval($_POST['enabled'] ?? 0);
            if (!$idp) {
                echo json_encode(['ok' => false, 'msg' => 'IDP requerido.']);
                exit;
            }
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
