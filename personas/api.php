<?php
/**
 * personas/api.php — API REST para gestión de personas y roles
 *
 * GET  ?action=list        → Lista personas enabled (público)
 * GET  ?action=list&director=1 → Lista TODAS las personas (Director)
 * GET  ?action=roles       → Lista catálogo de roles
 * GET  ?action=status      → Estado del sistema (migración, counts, version)
 * POST action=add          → Agrega persona (público)
 * POST action=update       → Actualiza persona
 * POST action=delete       → Elimina persona (Director)
 * POST action=toggle       → Toggle enabled/disabled (Director)
 */

require_once __DIR__ . '/../data/db.php';

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];

// ── GET ──────────────────────────────────────────────
if ($method === 'GET') {
    $action = $_GET['action'] ?? '';

    if ($action === 'list') {
        $director = ($_GET['director'] ?? '0') === '1';
        echo json_encode(['ok' => true, 'personas' => getPersonas(!$director)]);
        exit;
    }

    if ($action === 'roles') {
        echo json_encode(['ok' => true, 'roles' => getRoles()]);
        exit;
    }

    if ($action === 'status') {
        $db = getDB();
        ensureSchema();

        // Contar registros
        $personasCount = (int)$db->query("SELECT COUNT(*) FROM personas")->fetchColumn();
        $rolesCount = (int)$db->query("SELECT COUNT(*) FROM roles")->fetchColumn();
        $personaRolesCount = (int)$db->query("SELECT COUNT(*) FROM persona_roles")->fetchColumn();

        // Verificar si hay datos migrados
        $hasRealData = $personasCount > 6; // Si hay más de los 6 placeholders básicos

        // Verificar archivo de migración importado
        $migrationFile = __DIR__ . '/../data/migration_personas.json.imported';
        $migrationDone = file_exists($migrationFile);

        // Última actualización
        $lastPerson = $db->query("SELECT created_at FROM personas ORDER BY id DESC LIMIT 1")->fetchColumn();

        echo json_encode([
            'ok' => true,
            'status' => 'healthy',
            'version' => '26.12',
            'counts' => [
                'personas' => $personasCount,
                'roles' => $rolesCount,
                'persona_roles' => $personaRolesCount
            ],
            'migration' => [
                'done' => $migrationDone,
                'has_real_data' => $hasRealData
            ],
            'last_person_created' => $lastPerson,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit;
    }

    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Acción no válida']);
    exit;
}

// ── POST ─────────────────────────────────────────────
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }

    $action = $input['action'] ?? '';

    // Agregar persona (público)
    if ($action === 'add') {
        $nombre = trim($input['nombre'] ?? '');
        $apellido = trim($input['apellido'] ?? '');
        $dni = trim($input['dni'] ?? '');
        $telefono = trim($input['telefono'] ?? '');
        $roles = $input['roles'] ?? [];

        if (empty($nombre)) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'El nombre es obligatorio']);
            exit;
        }

        try {
            $id = addPersona($nombre, $apellido, $dni, $telefono);
            if (!empty($roles) && is_array($roles)) {
                setPersonaRoles($id, $roles);
            }
            $persona = getPersonaById($id);
            echo json_encode(['ok' => true, 'persona' => $persona]);
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'UNIQUE') !== false) {
                http_response_code(409);
                echo json_encode(['ok' => false, 'error' => 'Ya existe una persona con ese nombre y apellido']);
            } else {
                http_response_code(500);
                echo json_encode(['ok' => false, 'error' => 'Error al guardar: ' . $e->getMessage()]);
            }
        }
        exit;
    }

    // Actualizar persona (Director)
    if ($action === 'update') {
        $id = intval($input['id'] ?? 0);
        $nombre = trim($input['nombre'] ?? '');
        $apellido = trim($input['apellido'] ?? '');
        $dni = trim($input['dni'] ?? '');
        $telefono = trim($input['telefono'] ?? '');
        $roles = $input['roles'] ?? [];

        if (!$id || empty($nombre)) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'ID y nombre son obligatorios']);
            exit;
        }

        try {
            updatePersona($id, $nombre, $apellido, $dni, $telefono);
            if (is_array($roles)) {
                setPersonaRoles($id, $roles);
            }
            $persona = getPersonaById($id);
            echo json_encode(['ok' => true, 'persona' => $persona]);
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'UNIQUE') !== false) {
                http_response_code(409);
                echo json_encode(['ok' => false, 'error' => 'Ya existe otra persona con ese nombre y apellido']);
            } else {
                http_response_code(500);
                echo json_encode(['ok' => false, 'error' => 'Error al actualizar']);
            }
        }
        exit;
    }

    // Eliminar persona (Director)
    if ($action === 'delete') {
        $id = intval($input['id'] ?? 0);
        if (!$id) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'ID requerido']);
            exit;
        }
        deletePersona($id);
        echo json_encode(['ok' => true]);
        exit;
    }

    // Toggle enabled (Director)
    if ($action === 'toggle') {
        $id = intval($input['id'] ?? 0);
        if (!$id) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'ID requerido']);
            exit;
        }
        $enabled = togglePersonaEnabled($id);
        echo json_encode(['ok' => true, 'id' => $id, 'enabled' => $enabled]);
        exit;
    }

    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Acción no válida']);
    exit;
}

http_response_code(405);
echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
