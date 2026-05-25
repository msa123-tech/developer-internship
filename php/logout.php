<?php
header('Content-Type: application/json');

require_once __DIR__ . '/redis_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Only POST request allowed.']);
    exit;
}

$token = trim($_POST['token'] ?? '');

if ($token === '') {
    echo json_encode(['success' => false, 'message' => 'Session token is required.']);
    exit;
}

try {
    deleteSession($token);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Logged out successfully.']);
