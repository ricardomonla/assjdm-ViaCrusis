<?php
/**
 * Admin Key Validator
 * Recibe POST con 'key' y responde JSON.
 */
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false]);
    exit;
}

$key = $_POST['key'] ?? '';
$valid = ($key === 'VCBY2026');

echo json_encode(['ok' => $valid]);
