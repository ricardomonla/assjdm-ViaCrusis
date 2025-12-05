<?php
require 'incs/functions.php';

$dirMEDIA = 'media';
$file = $_GET['file'] ?? '';

// Validar estructura del nombre de archivo
if (!preg_match('/^\d{3}_v\d{4}_.+\.mp3$/', $file)) {
    http_response_code(403);
    die('Formato de archivo no válido.');
}

$filePath = realpath($dirMEDIA . '/' . $file);

// Validaciones de seguridad
if (!$filePath || !file_exists($filePath) || pathinfo($filePath, PATHINFO_EXTENSION) !== 'mp3') {
    http_response_code(404);
    die('Archivo no encontrado.');
}

if (strpos($filePath, realpath($dirMEDIA)) !== 0) {
    http_response_code(403);
    die('Acceso denegado.');
}

// Configurar headers para soportar range requests
$fileSize = filesize($filePath);
$fileTime = filemtime($filePath);

header('Content-Type: audio/mpeg');
header('Accept-Ranges: bytes');
header('Content-Length: ' . $fileSize);
header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $fileTime) . ' GMT');
header('Cache-Control: no-cache');

// Manejar range requests (para saltar en el audio)
if (isset($_SERVER['HTTP_RANGE'])) {
    $range = $_SERVER['HTTP_RANGE'];
    $range = str_replace('bytes=', '', $range);
    $range = explode('-', $range);
    
    $start = (int)$range[0];
    $end = $fileSize - 1;
    
    if (!empty($range[1])) {
        $end = (int)$range[1];
    }
    
    $length = $end - $start + 1;
    
    header('HTTP/1.1 206 Partial Content');
    header('Content-Range: bytes ' . $start . '-' . $end . '/' . $fileSize);
    header('Content-Length: ' . $length);
    
    // Leer y enviar la parte solicitada del archivo
    $file = fopen($filePath, 'rb');
    fseek($file, $start);
    
    $remaining = $length;
    while ($remaining > 0) {
        $chunk = min(8192, $remaining);
        echo fread($file, $chunk);
        $remaining -= $chunk;
        flush();
    }
    
    fclose($file);
} else {
    // Enviar el archivo completo si no hay range request
    readfile($filePath);
}
exit();
?>