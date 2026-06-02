<?php
session_start();
require_once '../../config/auth_check_user.php';
include '../../koneksi.php';

$id = (int) ($_GET['id'] ?? 0);
$userId = $_SESSION['user_id'];

if ($id > 0) {
    $stmt = mysqli_prepare($koneksi, "UPDATE artikel SET status = 'requested_edit' WHERE id = ? AND author_id = ? AND author_type = 'user' AND status = 'published'");
    mysqli_stmt_bind_param($stmt, 'ii', $id, $userId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

header('Location: dashboard_artikel.php?status=requested');
exit;
