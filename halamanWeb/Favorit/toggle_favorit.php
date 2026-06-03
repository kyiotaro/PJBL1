<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../koneksi.php';
require_once __DIR__ . '/../../assets/helpers/favorit_helper.php';

$actor = favoritGetActor();
if ($actor === null) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'liked' => false,
        'message' => 'Silakan login terlebih dahulu untuk menyukai artikel.',
        'require_login' => true,
    ]);
    exit;
}

$artikelId = (int) ($_POST['artikel_id'] ?? $_GET['artikel_id'] ?? 0);
if ($artikelId <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'liked' => false,
        'message' => 'ID artikel tidak valid.',
    ]);
    exit;
}

$checkStmt = mysqli_prepare($koneksi, 'SELECT id, status FROM artikel WHERE id = ? LIMIT 1');
if (!$checkStmt) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'liked' => false,
        'message' => 'Gagal memvalidasi artikel.',
    ]);
    exit;
}

mysqli_stmt_bind_param($checkStmt, 'i', $artikelId);
mysqli_stmt_execute($checkStmt);
$checkResult = mysqli_stmt_get_result($checkStmt);
$artikelRow = $checkResult ? mysqli_fetch_assoc($checkResult) : null;
mysqli_stmt_close($checkStmt);

if (!$artikelRow) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'liked' => false,
        'message' => 'Artikel tidak ditemukan.',
    ]);
    exit;
}

$isAdmin = $actor['type'] === 'admin';
if (!$isAdmin && ($artikelRow['status'] ?? '') !== 'published') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'liked' => false,
        'message' => 'Artikel ini tidak dapat difavoritkan.',
    ]);
    exit;
}

$result = favoritToggle($koneksi, $artikelId, $actor);
$toggleSuccess = !empty($result['success']);

if (!$toggleSuccess) {
    http_response_code(500);
}

echo json_encode([
    'success' => $toggleSuccess,
    'liked' => $result['liked'],
    'message' => $result['message'],
    'artikel_id' => $artikelId,
]);
