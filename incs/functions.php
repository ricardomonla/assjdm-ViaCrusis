<?php
function getBaseURL() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    return $protocol . "://" . $host . $path;
}

function getAudioFiles($dirMEDIA = 'media') {
    $files = [];
    $audioFiles = glob($dirMEDIA . '/*.mp3');
    
    foreach ($audioFiles as $filePath) {
        $filename = basename($filePath);
        
        if (!preg_match('/^(\d{3})_v(\d{4})_(.+)\.mp3$/', $filename, $matches)) {
            continue;
        }
        
        $order = $matches[1];
        $version = $matches[2];
        $title = str_replace('_', ' ', $matches[3]);
        
        $files[] = [
            'filename' => $filename,
            'display_name' => sprintf("%s %s", $order, ucfirst($title)),
            'id' => sprintf("%s_v%s", $order, $version),
            'order' => $order,
            'version' => $version,
            'title' => ucfirst($title),
            'path' => $filePath
        ];
    }
    
    usort($files, function($a, $b) {
        return strcmp($a['order'], $b['order']);
    });
    
    return $files;
}

function getAudioById($id, $audioFiles) {
    foreach ($audioFiles as $audio) {
        if ($audio['id'] == $id) {
            return $audio;
        }
    }
    return null;
}

function getAudioGroups($audioFiles) {
    $groups = [
        '0' => ['name' => 'Desfile', 'icon' => '🎭', 'audios' => []],
        '1' => ['name' => 'La Pasión', 'icon' => '⛪', 'audios' => []],
        '2' => ['name' => 'Calvario', 'icon' => '✝️', 'audios' => []],
        '3' => ['name' => 'Crucifixión', 'icon' => '🕊️', 'audios' => []],
    ];
    
    foreach ($audioFiles as $audio) {
        $prefix = substr($audio['order'], 0, 1);
        if (isset($groups[$prefix])) {
            $groups[$prefix]['audios'][] = $audio;
        }
    }
    
    return $groups;
}