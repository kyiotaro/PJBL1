<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

$isAdmin = !empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
$isUser = !empty($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;

if (!$isAdmin && !$isUser) {
    http_response_code(401);
    echo json_encode(['loggedIn' => false]);
    exit;
}

if ($isAdmin) {
    echo json_encode([
        'loggedIn'  => true,
        'user_type' => 'admin',
        'email'     => $_SESSION['admin_email'] ?? '',
        'id'        => $_SESSION['admin_id']    ?? null,
        'role'      => 'Administrator'
    ]);
} else {
    echo json_encode([
        'loggedIn'  => true,
        'user_type' => 'user',
        'email'     => $_SESSION['user_email'] ?? '',
        'id'        => $_SESSION['user_id']    ?? null,
        'role'      => 'Kontributor'
    ]);
}
