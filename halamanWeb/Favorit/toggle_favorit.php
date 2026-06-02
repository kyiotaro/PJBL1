<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../koneksi.php';
require_once __DIR__ . '/../../assets/helpers/favorit_helper.php';

$actor = favoritGetActor();
// #region agent log
$__dbgLog = dirname(__DIR__, 2) . '/debug-34f9b0.log';
$__dbgWrite = static function (string $hypothesisId, string $location, string $message, array $data = []) use ($__dbgLog): void {
    file_put_contents($__dbgLog, json_encode([
        'sessionId' => '34f9b0',
        'hypothesisId' => $hypothesisId,
        'location' => $location,
        'message' => $message,
        'data' => $data,
        'timestamp' => (int) round(microtime(true) * 1000),
    ], JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND | LOCK_EX);
};
$__dbgWrite('B', 'toggle_favorit.php:entry', 'toggle request', [
    'hasActor' => $actor !== null,
    'actorType' => $actor['type'] ?? null,
    'actorId' => $actor['id'] ?? null,
    'sessionFlags' => [
        'user_logged_in' => !empty($_SESSION['user_logged_in']),
        'admin_logged_in' => !empty($_SESSION['admin_logged_in']),
    ],
    'artikelId' => (int) ($_POST['artikel_id'] ?? $_GET['artikel_id'] ?? 0),
]);
// #endregion
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
if ($checkStmt) {
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
}

$result = favoritToggle($koneksi, $artikelId, $actor);

// #region agent log
$__toggleFailed = preg_match('/^(Gagal|Sistem favorit)/u', $result['message'] ?? '');
$__dbgWrite('A', 'toggle_favorit.php:result', 'toggle result', [
    'liked' => $result['liked'] ?? null,
    'message' => $result['message'] ?? null,
    'responseSuccessAlwaysTrue' => true,
    'internalFailure' => $__toggleFailed,
    'mysqliErr' => mysqli_error($koneksi) ?: null,
]);
// #endregion

echo json_encode([
    'success' => true,
    'liked' => $result['liked'],
    'message' => $result['message'],
    'artikel_id' => $artikelId,
]);
