<?php
/**
 * data/db.php — Conexión SQLite para datos del guion
 * 
 * La DB vive FUERA del repo git para que los deploys no la toquen.
 * Ruta: /var/www/vcby-data/vcby.db
 */

function getDB() {
    static $db = null;
    if ($db !== null) return $db;

    $dbDir = '/var/www/vcby-data';
    $dbFile = $dbDir . '/vcby.db';

    // Crear directorio si no existe
    if (!is_dir($dbDir)) {
        @mkdir($dbDir, 0775, true);
        @chown($dbDir, 'www-data');
    }

    $isNew = !file_exists($dbFile);
    
    $db = new PDO("sqlite:$dbFile", null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // WAL mode para mejor concurrencia
    $db->exec("PRAGMA journal_mode=WAL");
    $db->exec("PRAGMA foreign_keys=ON");

    if ($isNew) {
        createSchema($db);
    }

    return $db;
}

function createSchema($db) {
    $db->exec("
        CREATE TABLE IF NOT EXISTS cues (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            track_id TEXT NOT NULL,
            cue_index INTEGER NOT NULL,
            character TEXT DEFAULT '',
            idp TEXT DEFAULT 'P00',
            start_time REAL DEFAULT 0,
            end_time REAL DEFAULT 0,
            text TEXT DEFAULT '',
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(track_id, cue_index)
        );

        CREATE INDEX IF NOT EXISTS idx_cues_track ON cues(track_id);
        CREATE INDEX IF NOT EXISTS idx_cues_track_index ON cues(track_id, cue_index);

        CREATE TABLE IF NOT EXISTS casting (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            idp TEXT NOT NULL,
            character_name TEXT NOT NULL,
            nombre TEXT NOT NULL,
            apellido TEXT NOT NULL,
            telefono TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        CREATE INDEX IF NOT EXISTS idx_casting_idp ON casting(idp);

        CREATE TABLE IF NOT EXISTS scene_groups (
            id TEXT PRIMARY KEY,
            name TEXT NOT NULL,
            icon TEXT,
            order_index INTEGER DEFAULT 0
        );

        CREATE TABLE IF NOT EXISTS scenes (
            id TEXT PRIMARY KEY,
            group_id TEXT NOT NULL,
            order_index INTEGER DEFAULT 0,
            version TEXT DEFAULT '',
            title TEXT NOT NULL,
            display_name TEXT DEFAULT '',
            filename_audio TEXT DEFAULT '',
            youtube_video_id TEXT DEFAULT '',
            youtube_timestamp INTEGER DEFAULT 0,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (group_id) REFERENCES scene_groups(id)
        );
    ");
}

// Asegurar schema en DBs existentes (agrega tablas faltantes)
function ensureSchema() {
    $db = getDB();
    $db->exec("
        CREATE TABLE IF NOT EXISTS casting (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            idp TEXT NOT NULL,
            character_name TEXT NOT NULL,
            nombre TEXT NOT NULL,
            apellido TEXT NOT NULL,
            telefono TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        CREATE INDEX IF NOT EXISTS idx_casting_idp ON casting(idp);
        
        CREATE TABLE IF NOT EXISTS casting_enabled (
            idp TEXT PRIMARY KEY,
            enabled INTEGER DEFAULT 0
        );

        CREATE TABLE IF NOT EXISTS character_meta (
            idp TEXT PRIMARY KEY,
            name TEXT DEFAULT '',
            synopsis TEXT DEFAULT '',
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS scene_groups (
            id TEXT PRIMARY KEY,
            name TEXT NOT NULL,
            icon TEXT,
            order_index INTEGER DEFAULT 0
        );

        CREATE TABLE IF NOT EXISTS scenes (
            id TEXT PRIMARY KEY,
            group_id TEXT NOT NULL,
            order_index INTEGER DEFAULT 0,
            version TEXT DEFAULT '',
            title TEXT NOT NULL,
            display_name TEXT DEFAULT '',
            filename_audio TEXT DEFAULT '',
            youtube_video_id TEXT DEFAULT '',
            youtube_timestamp INTEGER DEFAULT 0,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (group_id) REFERENCES scene_groups(id)
        );
        CREATE TABLE IF NOT EXISTS track_config (
            track_id TEXT PRIMARY KEY,
            fade_out INTEGER DEFAULT 0
        );

        CREATE TABLE IF NOT EXISTS personas (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nombre TEXT NOT NULL,
            apellido TEXT DEFAULT '',
            dni TEXT DEFAULT '',
            telefono TEXT DEFAULT '',
            enabled INTEGER DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(nombre, apellido)
        );

        CREATE TABLE IF NOT EXISTS roles (
            id TEXT PRIMARY KEY,
            nombre TEXT NOT NULL,
            icono TEXT DEFAULT ''
        );

        CREATE TABLE IF NOT EXISTS persona_roles (
            persona_id INTEGER NOT NULL,
            rol_id TEXT NOT NULL,
            personaje TEXT DEFAULT '',
            PRIMARY KEY (persona_id, rol_id),
            FOREIGN KEY (persona_id) REFERENCES personas(id) ON DELETE CASCADE,
            FOREIGN KEY (rol_id) REFERENCES roles(id)
        );
    ");

    // Migración: agregar columna personaje si no existe
    $cols = $db->query("PRAGMA table_info(persona_roles)")->fetchAll();
    $hasPersonaje = false;
    foreach ($cols as $col) { if ($col['name'] === 'personaje') $hasPersonaje = true; }
    if (!$hasPersonaje) {
        $db->exec("ALTER TABLE persona_roles ADD COLUMN personaje TEXT DEFAULT ''");
    }

    // Migración: agregar columna enabled a personas si no existe
    $colsP = $db->query("PRAGMA table_info(personas)")->fetchAll();
    $hasEnabled = false;
    foreach ($colsP as $col) { if ($col['name'] === 'enabled') $hasEnabled = true; }
    if (!$hasEnabled) {
        $db->exec("ALTER TABLE personas ADD COLUMN enabled INTEGER DEFAULT 1");
    }

    // Siembra de roles si tabla está vacía
    $count = $db->query("SELECT COUNT(*) FROM roles")->fetchColumn();
    if ($count == 0) {
        $db->exec("
            INSERT INTO roles (id, nombre, icono) VALUES
            ('actor',     'Actor/Actriz',          '🎭'),
            ('staff',     'Staff',                 '🔧'),
            ('sonido',    'Sonido/Técnica',        '🎵'),
            ('donante',   'Donante/Colaborador',   '🤝'),
            ('logistica', 'Logística',             '🚚'),
            ('otro',      'Otro',                  '⭐')
        ");
    }

    // F4: Siembra automática — personajes del guion → personas placeholder
    $personasCount = $db->query("SELECT COUNT(*) FROM personas")->fetchColumn();
    if ($personasCount == 0) {
        $cuesExist = $db->query("SELECT COUNT(*) FROM sqlite_master WHERE name='cues'")->fetchColumn();
        if ($cuesExist) {
            $chars = $db->query("
                SELECT DISTINCT idp, character FROM cues 
                WHERE character != '' AND idp != 'P00' 
                ORDER BY idp
            ")->fetchAll(PDO::FETCH_ASSOC);
            
            $stmtP = $db->prepare("INSERT OR IGNORE INTO personas (nombre) VALUES (?)");
            $stmtR = $db->prepare("INSERT OR IGNORE INTO persona_roles (persona_id, rol_id, personaje) VALUES (?, 'actor', ?)");
            foreach ($chars as $c) {
                $stmtP->execute([$c['character']]);
                $id = $db->lastInsertId();
                if ($id) $stmtR->execute([$id, $c['character']]);
            }
        }
    }
}

// =====================================================
// PERSONAS — CRUD
// =====================================================

/**
 * Agregar persona nueva. Devuelve el ID.
 */
function addPersona($nombre, $apellido = '', $dni = '', $telefono = '') {
    $db = getDB();
    ensureSchema();
    $stmt = $db->prepare("INSERT INTO personas (nombre, apellido, dni, telefono) VALUES (?, ?, ?, ?)");
    $stmt->execute([trim($nombre), trim($apellido), trim($dni), trim($telefono)]);
    return $db->lastInsertId();
}

/**
 * Obtener todas las personas con sus roles.
 * @param bool $onlyEnabled — true = solo enabled (público), false = todas (director)
 */
function getPersonas($onlyEnabled = true) {
    $db = getDB();
    ensureSchema();
    $sql = "SELECT * FROM personas";
    if ($onlyEnabled) $sql .= " WHERE enabled = 1";
    $sql .= " ORDER BY (apellido = '' OR apellido IS NULL), apellido, nombre";
    $personas = $db->query($sql)->fetchAll();
    foreach ($personas as &$p) {
        $stmt = $db->prepare("
            SELECT r.id, r.nombre, r.icono, pr.personaje 
            FROM persona_roles pr JOIN roles r ON pr.rol_id = r.id 
            WHERE pr.persona_id = ? ORDER BY r.nombre
        ");
        $stmt->execute([$p['id']]);
        $p['roles'] = $stmt->fetchAll();
    }
    return $personas;
}

/**
 * Toggle enabled/disabled para una persona.
 */
function togglePersonaEnabled($id) {
    $db = getDB();
    ensureSchema();
    $db->prepare("UPDATE personas SET enabled = CASE WHEN enabled = 1 THEN 0 ELSE 1 END WHERE id = ?")->execute([$id]);
    $stmt = $db->prepare("SELECT enabled FROM personas WHERE id = ?");
    $stmt->execute([$id]);
    return (int)$stmt->fetchColumn();
}

/**
 * Obtener persona por ID con roles.
 */
function getPersonaById($id) {
    $db = getDB();
    ensureSchema();
    $stmt = $db->prepare("SELECT * FROM personas WHERE id = ?");
    $stmt->execute([$id]);
    $persona = $stmt->fetch();
    if ($persona) {
        $stmt2 = $db->prepare("
            SELECT r.id, r.nombre, r.icono, pr.personaje 
            FROM persona_roles pr JOIN roles r ON pr.rol_id = r.id 
            WHERE pr.persona_id = ?
        ");
        $stmt2->execute([$id]);
        $persona['roles'] = $stmt2->fetchAll();
    }
    return $persona;
}

/**
 * Actualizar persona.
 */
function updatePersona($id, $nombre, $apellido = '', $dni = '', $telefono = '') {
    $db = getDB();
    ensureSchema();
    $stmt = $db->prepare("UPDATE personas SET nombre = ?, apellido = ?, dni = ?, telefono = ? WHERE id = ?");
    $stmt->execute([trim($nombre), trim($apellido), trim($dni), trim($telefono), $id]);
}

/**
 * Eliminar persona y sus roles (CASCADE).
 */
function deletePersona($id) {
    $db = getDB();
    ensureSchema();
    $stmt = $db->prepare("DELETE FROM personas WHERE id = ?");
    $stmt->execute([$id]);
}

/**
 * Agregar rol a persona (ignora duplicados).
 */
function addPersonaRol($personaId, $rolId) {
    $db = getDB();
    $stmt = $db->prepare("INSERT OR IGNORE INTO persona_roles (persona_id, rol_id) VALUES (?, ?)");
    $stmt->execute([$personaId, trim($rolId)]);
}

/**
 * Reemplazar todos los roles de una persona.
 * @param array $roles Lista de role IDs
 * @param array $personajes Lista de nombres de personajes (solo para rol 'actor')
 */
function setPersonaRoles($personaId, $roles, $personajes = [], $staffValores = [], $otroValores = []) {
    $db = getDB();
    ensureSchema();

    // Borrar roles existentes
    $db->prepare("DELETE FROM persona_roles WHERE persona_id = ?")->execute([$personaId]);

    $stmt = $db->prepare("INSERT INTO persona_roles (persona_id, rol_id, personaje) VALUES (?, ?, ?)");

    // Actor: un registro por personaje
    if (in_array('actor', $roles) && !empty($personajes)) {
        foreach ($personajes as $pj) {
            if (!empty($pj)) {
                $stmt->execute([$personaId, 'actor', trim($pj)]);
            }
        }
    }

    // Staff: un registro por función (Logística, Sonido, Vestuario, Escenografía)
    if (in_array('staff', $roles) && !empty($staffValores)) {
        foreach ($staffValores as $funcion) {
            if (!empty($funcion)) {
                $stmt->execute([$personaId, 'staff', trim($funcion)]);
            }
        }
    }

    // Otro: un registro por cada valor de texto libre
    if (in_array('otro', $roles) && !empty($otroValores)) {
        foreach ($otroValores as $valor) {
            if (!empty($valor)) {
                $stmt->execute([$personaId, 'otro', trim($valor)]);
            }
        }
    }

    // Donador y Colaborador: un registro cada uno (sin personaje)
    if (in_array('donador', $roles)) {
        $stmt->execute([$personaId, 'donador', '']);
    }
    if (in_array('colaborador', $roles)) {
        $stmt->execute([$personaId, 'colaborador', '']);
    }
}

/**
 * Obtener catálogo de roles disponibles.
 */
function getRoles() {
    $db = getDB();
    ensureSchema();
    return $db->query("SELECT * FROM roles ORDER BY nombre")->fetchAll();
}

/**
 * Obtener personajes disponibles desde cues (placeholders sin datos reales)
 * Retorna personajes que aún no tienen una persona real asignada.
 */
function getPersonajesDisponibles() {
    $db = getDB();
    ensureSchema();

    // Obtener todos los personajes únicos de cues (excluyendo P00)
    $placeholders = $db->query("
        SELECT DISTINCT character as nombre, idp as codigo
        FROM cues
        WHERE character != '' AND idp != 'P00'
        ORDER BY character
    ")->fetchAll();

    // Obtener personajes que ya tienen persona real (nombre != personaje)
    $ocupados = $db->query("
        SELECT DISTINCT pr.personaje
        FROM persona_roles pr
        JOIN personas p ON pr.persona_id = p.id
        WHERE pr.personaje != '' AND (p.nombre != pr.personaje OR p.apellido != '')
    ")->fetchAll(PDO::FETCH_COLUMN);

    // Filtrar solo los disponibles
    $disponibles = [];
    foreach ($placeholders as $pj) {
        if (!in_array($pj['nombre'], $ocupados)) {
            $disponibles[] = $pj;
        }
    }

    return $disponibles;
}

/**
 * Buscar placeholder por nombre exacto del personaje
 */
function getPlaceholderByPersonaje($personaje) {
    $db = getDB();
    ensureSchema();

    // Buscar persona que sea placeholder de ese personaje
    $stmt = $db->prepare("
        SELECT p.*, pr.personaje, pr.rol_id
        FROM personas p
        JOIN persona_roles pr ON p.id = pr.persona_id
        WHERE p.nombre = ? AND p.apellido = '' AND pr.personaje = ?
    ");
    $stmt->execute([$personaje, $personaje]);
    return $stmt->fetch();
}

/**
 * Obtener configuración fade_out de un track (0=off, 1=on)
 */
function getTrackFadeOut($trackId) {
    $db = getDB();
    ensureSchema();
    $stmt = $db->prepare("SELECT fade_out FROM track_config WHERE track_id = ?");
    $stmt->execute([$trackId]);
    $row = $stmt->fetch();
    return $row ? (int)$row['fade_out'] : 0; // Default: sin fade
}

/**
 * Setear configuración fade_out de un track
 */
function setTrackFadeOut($trackId, $enabled) {
    $db = getDB();
    ensureSchema();
    $val = $enabled ? 1 : 0;
    $stmt = $db->prepare("INSERT INTO track_config (track_id, fade_out) VALUES (?, ?) ON CONFLICT(track_id) DO UPDATE SET fade_out = ?");
    $stmt->execute([$trackId, $val, $val]);
}

/**
 * Verificar si un personaje está habilitado para postulaciones
 */
function isCastingEnabled($idp) {
    $db = getDB();
    ensureSchema();
    $stmt = $db->prepare("SELECT enabled FROM casting_enabled WHERE idp = ?");
    $stmt->execute([$idp]);
    $row = $stmt->fetch();
    return $row && $row['enabled'] == 1;
}

/**
 * Toggle habilitación de personaje
 */
function toggleCastingEnabled($idp, $enabled) {
    $db = getDB();
    ensureSchema();
    $stmt = $db->prepare("INSERT INTO casting_enabled (idp, enabled) VALUES (?, ?) ON CONFLICT(idp) DO UPDATE SET enabled = ?");
    $stmt->execute([$idp, $enabled ? 1 : 0, $enabled ? 1 : 0]);
}

/**
 * Obtener todos los estados de habilitación
 */
function getAllCastingEnabled() {
    $db = getDB();
    ensureSchema();
    $stmt = $db->query("SELECT idp, enabled FROM casting_enabled");
    $result = [];
    foreach ($stmt->fetchAll() as $row) {
        $result[$row['idp']] = (int)$row['enabled'];
    }
    return $result;
}

/**
 * Synopsis de cada personaje
 */
function getCharacterSynopsis() {
    return [
        'P00' => 'Efectos de sonido o música de fondo',
        'P01' => 'Voz omnisciente',
        'P02' => 'Multitud, voces de la gente',
        'P03' => 'Condenado, líder de rebeldes',
        'P04' => 'Discípulo impulsivo',
        'P05' => 'El Mesías',
        'P06' => 'Jefe del Templo/Sacerdote conspirador',
        'P07' => 'Discípulo traidor',
        'P08' => 'Coro de varias voces de los apóstoles',
        'P09' => 'Discípulo amado',
        'P10' => 'Supremo Sacerdote del Templo',
        'P11' => 'Miembro del Sanedrín, simpatizante de Jesús',
        'P12' => 'Guardia Romano',
        'P13' => 'Guardia Romano',
        'P14' => 'Gobernador Romano',
        'P15' => 'Esposa de Pilatos',
        'P16' => 'Tetrarca de Galilea',
        'P17' => 'Esposa de Herodes',
        'P18' => 'Soldados romanos',
        'P19' => 'Madre de Jesús',
        'P20' => 'Ladrón crucificado, burlesco',
        'P21' => 'Ladrón crucificado, humilde',
        'P22' => 'Ser celestial en el sepulcro',
        'P23' => 'Ser celestial en el sepulcro',
        'P24' => 'Discípula en la tumba',
        'P25' => 'Mujer que enjuga el rostro de Jesús',
        'P26' => 'Mujeres de Jerusalén',
        'P90' => 'Notas de escena del Director',
        'P99' => 'Canción popular',
    ];
}

/**
 * Obtener personajes únicos de la DB (fuente de verdad) + synopsis
 */
function getCharacters() {
    $db = getDB();
    $stmt = $db->query("SELECT DISTINCT idp, character FROM cues WHERE idp != '' ORDER BY idp");
    $rows = $stmt->fetchAll();
    $synopsis = getCharacterSynopsis();
    $meta = getCharacterMeta();
    foreach ($rows as &$row) {
        $idp = $row['idp'];
        // Overrides de character_meta tienen prioridad
        if (isset($meta[$idp]['name']) && $meta[$idp]['name'] !== '') {
            $row['character'] = $meta[$idp]['name'];
        }
        if (isset($meta[$idp]['synopsis']) && $meta[$idp]['synopsis'] !== '') {
            $row['synopsis'] = $meta[$idp]['synopsis'];
        } else {
            $row['synopsis'] = $synopsis[$idp] ?? '';
        }
    }
    return $rows;
}

/**
 * Obtener postulaciones de casting
 */
function getCastingList($includePhone = false) {
    $db = getDB();
    ensureSchema();
    if ($includePhone) {
        $stmt = $db->query("SELECT id, idp, character_name, nombre, apellido, telefono, created_at FROM casting ORDER BY idp, created_at");
    } else {
        $stmt = $db->query("SELECT id, idp, character_name, nombre, apellido, created_at FROM casting ORDER BY idp, created_at");
    }
    return $stmt->fetchAll();
}

/**
 * Agregar postulación
 */
function addCasting($idp, $charName, $nombre, $apellido, $telefono) {
    $db = getDB();
    ensureSchema();
    $stmt = $db->prepare("INSERT INTO casting (idp, character_name, nombre, apellido, telefono) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$idp, $charName, $nombre, $apellido, $telefono]);
    return $db->lastInsertId();
}

/**
 * Eliminar postulación (solo Director)
 */
function deleteCasting($id) {
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM casting WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->rowCount();
}

/**
 * Actualizar metadatos de personaje (nombre, synopsis)
 */
function updateCharacterMeta($idp, $field, $value) {
    $db = getDB();
    ensureSchema();
    $allowed = ['name', 'synopsis'];
    if (!in_array($field, $allowed)) return false;
    $stmt = $db->prepare("INSERT INTO character_meta (idp, $field) VALUES (?, ?) ON CONFLICT(idp) DO UPDATE SET $field = ?, updated_at = CURRENT_TIMESTAMP");
    $stmt->execute([$idp, $value, $value]);
    return true;
}

/**
 * Obtener overrides de character_meta
 */
function getCharacterMeta() {
    $db = getDB();
    ensureSchema();
    $stmt = $db->query("SELECT idp, name, synopsis FROM character_meta");
    $result = [];
    foreach ($stmt->fetchAll() as $row) {
        $result[$row['idp']] = $row;
    }
    return $result;
}

/**
 * Actualizar datos de postulación (solo Director)
 */
function updateCasting($id, $field, $value) {
    $db = getDB();
    $allowed = ['nombre', 'apellido', 'telefono'];
    if (!in_array($field, $allowed)) return false;
    $stmt = $db->prepare("UPDATE casting SET $field = ? WHERE id = ?");
    $stmt->execute([$value, $id]);
    return $stmt->rowCount();
}

/**
 * Obtener cues de un track
 */
function getCues($trackId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT cue_index, character, idp, start_time, end_time, text FROM cues WHERE track_id = ? ORDER BY cue_index");
    $stmt->execute([$trackId]);
    $rows = $stmt->fetchAll();

    // Formatear como el JSON original (incluir cue_index real para CRUD)
    $cues = [];
    foreach ($rows as $row) {
        $cues[] = [
            'cue_index' => (int) $row['cue_index'],
            'character' => $row['character'],
            'idp' => $row['idp'],
            'startTime' => (float) $row['start_time'],
            'endTime' => (float) $row['end_time'],
            'text' => $row['text']
        ];
    }
    return $cues;
}

/**
 * Obtener TODOS los cues (para el endpoint completo)
 */
function getAllCues() {
    $db = getDB();
    $stmt = $db->query("SELECT track_id, cue_index, character, idp, start_time, end_time, text FROM cues ORDER BY track_id, cue_index");
    $rows = $stmt->fetchAll();

    $guion = [];
    foreach ($rows as $row) {
        $tid = $row['track_id'];
        if (!isset($guion[$tid])) $guion[$tid] = [];
        $guion[$tid][] = [
            'character' => $row['character'],
            'idp' => $row['idp'],
            'startTime' => (float) $row['start_time'],
            'endTime' => (float) $row['end_time'],
            'text' => $row['text']
        ];
    }
    return $guion;
}

/**
 * Actualizar un campo de un cue
 */
function updateCue($trackId, $cueIndex, $field, $value) {
    $db = getDB();
    $columnMap = [
        'text' => 'text',
        'character' => 'character',
        'idp' => 'idp',
        'startTime' => 'start_time',
        'endTime' => 'end_time'
    ];

    $col = $columnMap[$field] ?? null;
    if (!$col) return false;

    $stmt = $db->prepare("UPDATE cues SET $col = ?, updated_at = datetime('now') WHERE track_id = ? AND cue_index = ?");
    return $stmt->execute([$value, $trackId, $cueIndex]);
}

/**
 * Obtener valor actual de un campo
 */
function getCueField($trackId, $cueIndex, $field) {
    $db = getDB();
    $columnMap = [
        'text' => 'text',
        'character' => 'character',
        'idp' => 'idp',
        'startTime' => 'start_time',
        'endTime' => 'end_time'
    ];

    $col = $columnMap[$field] ?? null;
    if (!$col) return null;

    $stmt = $db->prepare("SELECT $col FROM cues WHERE track_id = ? AND cue_index = ?");
    $stmt->execute([$trackId, $cueIndex]);
    $row = $stmt->fetch();
    return $row ? $row[$col] : null;
}

/**
 * Insertar cue (acotación escénica)
 */
function insertCue($trackId, $afterIndex, $cueData) {
    $db = getDB();
    
    $db->beginTransaction();
    try {
        // Paso 1: Negar índices afectados (imposible colisionar con positivos)
        $db->exec("UPDATE cues SET cue_index = -(cue_index + 1) WHERE track_id = " . $db->quote($trackId) . " AND cue_index > $afterIndex");
        
        // Paso 2: Insertar nuevo cue
        $newIndex = $afterIndex + 1;
        $stmt = $db->prepare("INSERT INTO cues (track_id, cue_index, character, idp, start_time, end_time, text) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $trackId,
            $newIndex,
            $cueData['character'] ?? 'Música / Ambiente',
            $cueData['idp'] ?? 'P00',
            $cueData['startTime'] ?? 0,
            $cueData['endTime'] ?? 0,
            $cueData['text'] ?? ''
        ]);

        // Paso 3: Restaurar negativos con offset +1
        $db->exec("UPDATE cues SET cue_index = (-cue_index) - 1 + 1 WHERE track_id = " . $db->quote($trackId) . " AND cue_index < 0");

        $db->commit();
        return $newIndex;
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
}

/**
 * Insertar múltiples cues de golpe (para duplicar burbujas completas)
 */
function insertBatchCues($trackId, $afterIndex, $cuesArray) {
    $db = getDB();
    $count = count($cuesArray);
    if ($count === 0) return 0;
    
    $db->beginTransaction();
    try {
        // Paso 1: Negar índices afectados
        $db->exec("UPDATE cues SET cue_index = -(cue_index + 1) WHERE track_id = " . $db->quote($trackId) . " AND cue_index > $afterIndex");

        // Paso 2: Insertar cada cue nuevo
        $stmt = $db->prepare("INSERT INTO cues (track_id, cue_index, character, idp, start_time, end_time, text) VALUES (?, ?, ?, ?, ?, ?, ?)");
        for ($i = 0; $i < $count; $i++) {
            $c = $cuesArray[$i];
            $stmt->execute([
                $trackId,
                $afterIndex + 1 + $i,
                $c['character'] ?? '',
                $c['idp'] ?? 'P00',
                $c['startTime'] ?? 0,
                $c['endTime'] ?? 0,
                $c['text'] ?? ''
            ]);
        }

        // Paso 3: Restaurar negativos con offset +count
        $db->exec("UPDATE cues SET cue_index = (-cue_index) - 1 + $count WHERE track_id = " . $db->quote($trackId) . " AND cue_index < 0");

        $db->commit();
        return $count;
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
}

/**
 * Eliminar un cue y reindexar los posteriores (-1)
 */
function deleteCue($trackId, $cueIndex) {
    $db = getDB();
    
    $db->beginTransaction();
    try {
        // Eliminar
        $stmt = $db->prepare("DELETE FROM cues WHERE track_id = ? AND cue_index = ?");
        $stmt->execute([$trackId, $cueIndex]);
        
        if ($stmt->rowCount() === 0) throw new Exception("Cue $cueIndex no encontrado.");
        
        // Reindexar: negar posteriores, luego restaurar con -1
        $db->exec("UPDATE cues SET cue_index = -(cue_index + 1) WHERE track_id = " . $db->quote($trackId) . " AND cue_index > $cueIndex");
        $db->exec("UPDATE cues SET cue_index = (-cue_index) - 1 - 1 WHERE track_id = " . $db->quote($trackId) . " AND cue_index < 0");
        
        $db->commit();
        return true;
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
}

/**
 * Eliminar múltiples cues consecutivos (para borrar burbuja completa)
 */
function deleteBatchCues($trackId, $cueIndices) {
    $db = getDB();
    $count = count($cueIndices);
    if ($count === 0) return 0;
    
    sort($cueIndices);
    $minIndex = $cueIndices[0];
    
    $db->beginTransaction();
    try {
        // Eliminar todos los cues indicados
        $placeholders = implode(',', array_fill(0, $count, '?'));
        $stmt = $db->prepare("DELETE FROM cues WHERE track_id = ? AND cue_index IN ($placeholders)");
        $params = array_merge([$trackId], $cueIndices);
        $stmt->execute($params);
        
        // Reindexar: negar posteriores al mínimo, restaurar con -count
        $maxIndex = max($cueIndices);
        $db->exec("UPDATE cues SET cue_index = -(cue_index + 1) WHERE track_id = " . $db->quote($trackId) . " AND cue_index > $maxIndex");
        $db->exec("UPDATE cues SET cue_index = (-cue_index) - 1 - $count WHERE track_id = " . $db->quote($trackId) . " AND cue_index < 0");
        
        $db->commit();
        return $count;
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
}

/**
 * Obtener la estructura SSOT agrupada desde la base de datos (reemplaza a getMediaGroupsStructure)
 */
function getScenesGrouped() {
    try {
        $db = getDB();
        ensureSchema();

        $stmt = $db->query("SELECT id, name, icon, order_index FROM scene_groups ORDER BY order_index ASC");
        $groups = $stmt->fetchAll();

        if (empty($groups)) {
            // Fallback: Si no hay grupos, ejecutar la migración al vuelo para no romper producción
            require_once dirname(__DIR__) . '/tools/migrate_scenes_to_db.php';
            run_ssot_migration();
            $stmt = $db->query("SELECT id, name, icon, order_index FROM scene_groups ORDER BY order_index ASC");
            $groups = $stmt->fetchAll();
        }

        $result = [];
        foreach ($groups as $group) {
            $gid = $group['id'];
            $cleanId = (string)$group['order_index'];
            
            $result[$cleanId] = [
                'group_id' => $gid,
                'name' => $group['name'],
                'icon' => $group['icon'],
                'audios' => []
            ];

            // Obtener escenas de este grupo
            $stmt2 = $db->prepare("SELECT * FROM scenes WHERE group_id = ? ORDER BY order_index ASC");
            $stmt2->execute([$gid]);
            $scenes = $stmt2->fetchAll();

            foreach ($scenes as $scene) {
                $result[$cleanId]['audios'][] = [
                    'id' => $scene['id'] . '_v' . $scene['version'],
                    'scene_id' => $scene['id'],
                    'order' => $scene['id'],
                    'version' => $scene['version'],
                    'title' => $scene['title'],
                    'display_name' => $scene['display_name'],
                    'filename' => $scene['filename_audio'],
                    'youtube_video_id' => $scene['youtube_video_id'],
                    'youtube_timestamp' => (int) $scene['youtube_timestamp']
                ];
            }
        }

        return $result;
    } catch (Exception $e) {
        // Fallback a JSON para entornos sin soporte SQLite (ej. Android/Termux)
        $jsonFile = dirname(__DIR__) . '/data/scenes_backup.json';
        if (file_exists($jsonFile)) {
            $data = json_decode(file_get_contents($jsonFile), true);
            $result = [];
            foreach ($data['groups'] as $group) {
                $gid = $group['id'];
                $cleanId = (string)$group['order_index'];
                $result[$cleanId] = [
                    'group_id' => $gid,
                    'name' => $group['name'],
                    'icon' => $group['icon'],
                    'audios' => []
                ];
                
                // Filtrar escenas de este grupo
                $groupScenes = array_filter($data['scenes'], function($s) use ($gid) { return $s['group_id'] === $gid; });
                foreach ($groupScenes as $scene) {
                    $result[$cleanId]['audios'][] = [
                        'id' => $scene['id'] . '_v' . $scene['version'],
                        'scene_id' => $scene['id'],
                        'order' => $scene['id'],
                        'version' => $scene['version'],
                        'title' => $scene['title'],
                        'display_name' => $scene['display_name'],
                        'filename' => $scene['filename_audio'],
                        'youtube_video_id' => $scene['youtube_video_id'],
                        'youtube_timestamp' => (int) $scene['youtube_timestamp']
                    ];
                }
            }
            return $result;
        }

        // Sin JSON disponible, retornar vacío (no ocultamos error en producción)
        error_log("VCBY FALLBACK: No SQLite ni JSON disponible.");
        return [];
    }
}
