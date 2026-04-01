<?php
require_once __DIR__ . '/elementos.php';

function getBaseURL() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    return $protocol . "://" . $host . $path;
}

function getAudioFiles($dirMEDIA = 'media') {
    $files = [];
    $groups = getMediaGroupsStructure();
    
    foreach ($groups as $group) {
        foreach ($group['audios'] as $audio) {
            $audio['path'] = $dirMEDIA . '/' . $audio['filename'];
            $files[] = $audio;
        }
    }
    
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

function getAudioGroups($audioFiles = null) {
    // La estructura base ahora trae los audios predefinidos
    return getMediaGroupsStructure();
}