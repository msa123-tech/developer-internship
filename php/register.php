<?php
header('Content-Type: application/json');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/mongodb_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Only POST request allowed.']);
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$fullName = trim($_POST['full_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');

if ($email === '' || $password === '' || $fullName === '') {
    echo json_encode(['success' => false, 'message' => 'Email, password and full name are required.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email.']);
    exit;
}

$conn = getMysqlConnection();

$checkStmt = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$checkStmt->bind_param('s', $email);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    $checkStmt->close();
    $conn->close();
    echo json_encode(['success' => false, 'message' => 'Email already registered.']);
    exit;
}
$checkStmt->close();

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$insertStmt = $conn->prepare('INSERT INTO users (email, password) VALUES (?, ?)');
$insertStmt->bind_param('ss', $email, $hashedPassword);

if (!$insertStmt->execute()) {
    $insertStmt->close();
    $conn->close();
    echo json_encode(['success' => false, 'message' => 'Registration failed.']);
    exit;
}

$userId = $insertStmt->insert_id;
$insertStmt->close();
$conn->close();

try {
    saveProfile([
        'user_id' => (int) $userId,
        'email' => $email,
        'full_name' => $fullName,
        'phone' => $phone,
        'age' => '',
        'dob' => '',
        'address' => '',
        'created_at' => date('Y-m-d H:i:s')
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => 'Registration successful. Please login.'
]);
