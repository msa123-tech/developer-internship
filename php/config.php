<?php
// MySQL connection settings
define('DB_HOST', 'mongodb+srv://msa652285_db_user:<db_password>@arjun-0.5e3hoow.mongodb.net/');
define('DB_NAME', 'arjun_task_db');
define('DB_USER', 'msa652285_db_user');
define('DB_PASS', 'OaZCMHtBwEzbK7GP');

function getMysqlConnection()
{
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
        exit;
    }

    $conn->set_charset('utf8mb4');
    return $conn;
}
