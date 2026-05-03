<?php
session_start();
require_once '../../koneksi.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak valid.']);
    exit;
}

$kode      = strtoupper(trim($_POST['kode'] ?? ''));
$adminId   = $_SESSION['otp_admin_id'] ?? null;

if (!$adminId || $kode === '') {
    echo json_encode(['success' => false, 'message' => 'Sesi tidak valid. Silakan login ulang.']);
    exit;
}

// Cek OTP: harus cocok, belum dipakai, dan belum expired
$stmt = mysqli_prepare($koneksi, "
    SELECT id FROM otp_sessions 
    WHERE admin_id = ? AND kode = ? AND used = 0 AND expired_at > NOW()
    LIMIT 1
");
mysqli_stmt_bind_param($stmt, 'is', $adminId, $kode);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$otp = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$otp) {
    echo json_encode(['success' => false, 'message' => 'Kode salah atau sudah kadaluarsa.']);
    exit;
}

// Tandai OTP sudah dipakai
$updateStmt = mysqli_prepare($koneksi, "UPDATE otp_sessions SET used = 1 WHERE id = ?");
mysqli_stmt_bind_param($updateStmt, 'i', $otp['id']);
mysqli_stmt_execute($updateStmt);
mysqli_stmt_close($updateStmt);

// Ambil data admin
$adminStmt = mysqli_prepare($koneksi, "SELECT id, email, nama FROM admin WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($adminStmt, 'i', $adminId);
mysqli_stmt_execute($adminStmt);
$adminResult = mysqli_stmt_get_result($adminStmt);
$admin = mysqli_fetch_assoc($adminResult);
mysqli_stmt_close($adminStmt);

// Buat PHP session login
$_SESSION['admin_logged_in'] = true;
$_SESSION['admin_id']        = $admin['id'];
$_SESSION['admin_email']     = $admin['email'];
$_SESSION['admin_nama']      = $admin['nama'];

// Hapus session OTP sementara
unset($_SESSION['otp_email'], $_SESSION['otp_admin_id']);

echo json_encode(['success' => true, 'redirect' => '/PJBL-main/dashboard/dashboardadmin/dashboard.php']);