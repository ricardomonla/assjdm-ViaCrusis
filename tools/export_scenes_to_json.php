<?php
require_once dirname(__DIR__) . '/data/db.php';
$db = getDB();

$scenes = [];
$stmt = $db->query("SELECT * FROM scenes ORDER BY order_index");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $scenes[] = $row;
}
$groups = [];
$stmtGroups = $db->query("SELECT * FROM scene_groups ORDER BY order_index");
while ($row = $stmtGroups->fetch(PDO::FETCH_ASSOC)) {
    $groups[] = $row;
}

$dump = [
    'groups' => $groups,
    'scenes' => $scenes
];

file_put_contents(dirname(__DIR__) . '/data/scenes_backup.json', json_encode($dump, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "Exported scenes and groups to data/scenes_backup.json\n";
