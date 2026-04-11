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
    ");
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
        
        // Ultimate Fallback - Si no hay JSON y no hay SQLite, cargamos del PHP estático hardcoded
        require_once dirname(__DIR__) . '/incs/elementos.php';
        $groups = getMediaGroupsStructure();
        $ytt_scenes = [
            "000" => ["videoId" => "0nxVUTRmb_w", "timestamp" => 0],
            "001" => ["videoId" => "0nxVUTRmb_w", "timestamp" => 175],
            "002" => ["videoId" => "0nxVUTRmb_w", "timestamp" => 368],
            "003" => ["videoId" => "0nxVUTRmb_w", "timestamp" => 596],
            "004" => ["videoId" => "0nxVUTRmb_w", "timestamp" => 859],
            "005" => ["videoId" => "0nxVUTRmb_w", "timestamp" => 956],
            "006" => ["videoId" => "0nxVUTRmb_w", "timestamp" => 1266],
            "101" => ["videoId" => "ktDtijJMfbo", "timestamp" => 0],
            "102" => ["videoId" => "ktDtijJMfbo", "timestamp" => 182],
            "103" => ["videoId" => "ktDtijJMfbo", "timestamp" => 289],
            "104" => ["videoId" => "ktDtijJMfbo", "timestamp" => 873],
            "105" => ["videoId" => "ktDtijJMfbo", "timestamp" => 1193],
            "106" => ["videoId" => "ktDtijJMfbo", "timestamp" => 1273],
            "107" => ["videoId" => "ktDtijJMfbo", "timestamp" => 1333],
            "108" => ["videoId" => "ktDtijJMfbo", "timestamp" => 1546],
            "109" => ["videoId" => "ktDtijJMfbo", "timestamp" => 1709],
            "110" => ["videoId" => "ktDtijJMfbo", "timestamp" => 2007],
            "111" => ["videoId" => "ktDtijJMfbo", "timestamp" => 2422],
            "201" => ["videoId" => "GPZE-uxt0LQ", "timestamp" => 0],
            "202" => ["videoId" => "GPZE-uxt0LQ", "timestamp" => 546],
            "203" => ["videoId" => "GPZE-uxt0LQ", "timestamp" => 885],
            "204" => ["videoId" => "GPZE-uxt0LQ", "timestamp" => 1267],
            "205" => ["videoId" => "GPZE-uxt0LQ", "timestamp" => 1464],
            "206" => ["videoId" => "GPZE-uxt0LQ", "timestamp" => 1772],
            "207" => ["videoId" => "GPZE-uxt0LQ", "timestamp" => 1952],
            "301" => ["videoId" => "a0LB3VWQstw", "timestamp" => 0],
            "302" => ["videoId" => "a0LB3VWQstw", "timestamp" => 131],
            "303" => ["videoId" => "a0LB3VWQstw", "timestamp" => 173],
            "304" => ["videoId" => "a0LB3VWQstw", "timestamp" => 728],
            "305" => ["videoId" => "a0LB3VWQstw", "timestamp" => 814],
            "306" => ["videoId" => "a0LB3VWQstw", "timestamp" => 923]
        ];

        $result = [];
        foreach ($groups as $groupId => $group) {
            $cleanId = (string)$groupId;
            $result[$cleanId] = [
                'group_id' => $groupId . 'XX',
                'name' => $group['name'],
                'icon' => $group['icon'] ?? '',
                'audios' => []
            ];
            foreach ($group['audios'] as $scene) {
                $sceneId = $scene['order'];
                $yt = $ytt_scenes[$sceneId] ?? null;
                $result[$cleanId]['audios'][] = [
                    'id' => $scene['id'], 
                    'scene_id' => $sceneId,
                    'order' => $sceneId,
                    'version' => $scene['version'],
                    'title' => $scene['title'],
                    'display_name' => $scene['display_name'],
                    'filename' => $scene['filename'],
                    'youtube_video_id' => $yt ? $yt['videoId'] : '',
                    'youtube_timestamp' => $yt ? (int)$yt['timestamp'] : 0
                ];
            }
        }
        return $result;
    }
}
