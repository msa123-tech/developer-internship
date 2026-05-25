<?php
header('Content-Type: application/json');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/redis_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Only POST request allowed.']);
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    echo json_encode(['success' => false, 'message' => 'Email and password are required.']);
    exit;
}

$conn = getMysqlConnection();

$stmt = $conn->prepare('SELECT id, email, password FROM users WHERE email = ? LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
$conn->close();

if (!$user || !password_verify($password, $user['password'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
    exit;
}

$token = bin2hex(random_bytes(32));

try {
    saveSession($token, [
        'user_id' => (int) $user['id'],
        'email' => $user['email']
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => 'Login successful.',
    'token' => $token,
    'user' => [
        'id' => (int) $user['id'],
        'email' => $user['email']
    ]
]);
