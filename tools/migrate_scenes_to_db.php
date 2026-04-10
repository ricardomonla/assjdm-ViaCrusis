<?php
/**
 * migrate_scenes_to_db.php
 * Siembra la base de datos (scene_groups y scenes)
 * cruzando datos desde incs/elementos.php y jss/youtube_config.js
 */
require_once dirname(__DIR__) . '/data/db.php';
require_once dirname(__DIR__) . '/incs/elementos.php';

function run_ssot_migration() {
    $db = getDB();

    // Asegurar las tablas
    ensureSchema();

    $stmtCheck = $db->query("SELECT count(*) FROM scene_groups");
    if ($stmtCheck->fetchColumn() > 0) {
        return; // Ya está migrado
    }

    // Hardcodeamos los arrays que solían estar en youtube_config.js
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

    // Leemos el origen clásico en PHP
    $groups = getMediaGroupsStructure();

    $db->beginTransaction();

    try {
        $stmtGroup = $db->prepare("INSERT INTO scene_groups (id, name, icon, order_index) VALUES (?, ?, ?, ?)");
        $stmtScene = $db->prepare("INSERT INTO scenes (id, group_id, order_index, version, title, display_name, filename_audio, youtube_video_id, youtube_timestamp) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

        foreach ($groups as $groupId => $group) {
            $realGroupId = $groupId . 'XX'; // '0' -> '0XX'
            
            $stmtGroup->execute([
                $realGroupId,
                $group['name'],
                $group['icon'] ?? '',
                (int)$groupId
            ]);

            foreach ($group['audios'] as $scene) {
                $sceneId = $scene['order']; // ej '101'
                $version = $scene['version']; // ej '2503'
                
                $yt = $ytt_scenes[$sceneId] ?? null;
                $videoId = $yt ? $yt['videoId'] : '';
                $ts = $yt ? (int)$yt['timestamp'] : 0;

                $stmtScene->execute([
                    $sceneId,
                    $realGroupId,
                    (int)$sceneId,
                    $version,
                    $scene['title'],
                    $scene['display_name'],
                    $scene['filename'],
                    $videoId,
                    $ts
                ]);
            }
        }

        $db->commit();
    } catch (Exception $e) {
        $db->rollBack();
        error_log("Error migracion SSOT: " . $e->getMessage());
    }
}

// Permitir ejecución por CLI direcatamente
if (php_sapi_name() === 'cli' && basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"])) {
    echo "Iniciando migración SSOT...\n";
    run_ssot_migration();
    echo "Migración terminada.\n";
}
