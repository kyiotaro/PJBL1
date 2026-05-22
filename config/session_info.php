<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['loggedIn' => false]);
    exit;
}

echo json_encode([
    'loggedIn' => true,
    'email'    => $_SESSION['admin_email'] ?? '',
    'nama'     => $_SESSION['admin_nama']  ?? '',
    'id'       => $_SESSION['admin_id']    ?? null,
]);
