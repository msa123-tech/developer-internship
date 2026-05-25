<?php
header('Content-Type: application/json');

require_once __DIR__ . '/redis_config.php';
require_once __DIR__ . '/mongodb_config.php';

$token = trim($_POST['token'] ?? $_GET['token'] ?? '');

if ($token === '') {
    echo json_encode(['success' => false, 'message' => 'Session token is required.']);
    exit;
}

try {
    $session = getSession($token);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}

if (!$session) {
    echo json_encode(['success' => false, 'message' => 'Session expired. Please login again.']);
    exit;
}

$userId = (int) $session['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $fullName = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $age = trim($_POST['age'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($fullName === '') {
        echo json_encode(['success' => false, 'message' => 'Full name is required.']);
        exit;
    }

    if ($age !== '' && (!ctype_digit($age) || (int) $age < 1 || (int) $age > 120)) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid age (1-120).']);
        exit;
    }

    try {
        updateProfile($userId, [
            'full_name' => $fullName,
            'phone' => $phone,
            'age' => $age !== '' ? (int) $age : '',
            'dob' => $dob,
            'address' => $address,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }

    echo json_encode(['success' => true, 'message' => 'Profile updated successfully.']);
    exit;
}

try {
    $profile = findProfileByUserId($userId);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}

if (!$profile) {
    echo json_encode(['success' => false, 'message' => 'Profile not found.']);
    exit;
}

echo json_encode([
    'success' => true,
    'profile' => [
        'user_id' => $profile->user_id,
        'email' => $profile->email,
        'full_name' => $profile->full_name,
        'phone' => $profile->phone ?? '',
        'age' => $profile->age ?? '',
        'dob' => $profile->dob ?? '',
        'address' => $profile->address ?? ''
    ]
]);
