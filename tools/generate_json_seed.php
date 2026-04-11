<?php
/**
 * generate_json_seed.php
 * Genera data/scenes_backup.json desde los datos semilla (elementos.php + youtube hardcoded)
 * para garantizar que el fallback JSON exista siempre en el repositorio.
 * 
 * Uso: php tools/generate_json_seed.php
 * 
 * En producción con DB activa, usar tools/export_scenes_to_json.php para exportar
 * datos actualizados desde SQLite.
 */
require_once dirname(__DIR__) . '/incs/elementos.php';

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

$mediaGroups = getMediaGroupsStructure();

$groups = [];
$scenes = [];

foreach ($mediaGroups as $groupId => $group) {
    $realGroupId = $groupId . 'XX';
    
    $groups[] = [
        'id' => $realGroupId,
        'name' => $group['name'],
        'icon' => $group['icon'] ?? '',
        'order_index' => (int)$groupId
    ];

    foreach ($group['audios'] as $audio) {
        $sceneId = $audio['order'];
        $yt = $ytt_scenes[$sceneId] ?? null;
        
        $scenes[] = [
            'id' => $sceneId,
            'group_id' => $realGroupId,
            'order_index' => (int)$sceneId,
            'version' => $audio['version'],
            'title' => $audio['title'],
            'display_name' => $audio['display_name'],
            'filename_audio' => $audio['filename'],
            'youtube_video_id' => $yt ? $yt['videoId'] : '',
            'youtube_timestamp' => $yt ? (int)$yt['timestamp'] : 0
        ];
    }
}

$dump = [
    'generated_at' => date('Y-m-d H:i:s'),
    'source' => 'seed (elementos.php + youtube hardcoded)',
    'groups' => $groups,
    'scenes' => $scenes
];

$outFile = dirname(__DIR__) . '/data/scenes_backup.json';
file_put_contents($outFile, json_encode($dump, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "✅ Generated: $outFile\n";
echo "   Groups: " . count($groups) . "\n";
echo "   Scenes: " . count($scenes) . "\n";
