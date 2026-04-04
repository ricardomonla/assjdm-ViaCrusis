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
    ");
}

/**
 * Obtener cues de un track
 */
function getCues($trackId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT cue_index, character, idp, start_time, end_time, text FROM cues WHERE track_id = ? ORDER BY cue_index");
    $stmt->execute([$trackId]);
    $rows = $stmt->fetchAll();

    // Formatear como el JSON original
    $cues = [];
    foreach ($rows as $row) {
        $cues[] = [
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
        // Reindexar: mover todos los cues posteriores +1
        $stmt = $db->prepare("UPDATE cues SET cue_index = cue_index + 1 WHERE track_id = ? AND cue_index > ?");
        $stmt->execute([$trackId, $afterIndex]);

        // Insertar nuevo cue
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

        $db->commit();
        return $newIndex;
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
}
