<?php
session_start();
require_once '../../koneksi.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action === 'register') {
    $nama = $_POST['nama'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // Validasi
    if (empty($nama) || empty($email) || empty($password) || empty($password_confirm)) {
        echo json_encode(['success' => false, 'message' => 'Semua field wajib diisi.']);
        exit;
    }

    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => 'Password minimal 6 karakter.']);
        exit;
    }

    if ($password !== $password_confirm) {
        echo json_encode(['success' => false, 'message' => 'Password tidak cocok.']);
        exit;
    }

    // CEK EMAIL SUDAH TERDAFTAR
    $stmt = mysqli_prepare($koneksi, "SELECT id FROM users WHERE email = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        echo json_encode(['success' => false, 'message' => 'Email sudah terdaftar.']);
        mysqli_stmt_close($stmt);
        exit;
    }
    mysqli_stmt_close($stmt);

    // Hash password
    $password_hashed = password_hash($password, PASSWORD_BCRYPT);

    // INSERT USER DENGAN LEVEL DEFAULT "user"
    $stmt = mysqli_prepare($koneksi, "INSERT INTO users (nama, email, password, level, created_at) VALUES (?, ?, ?, 'user', NOW())");
    mysqli_stmt_bind_param($stmt, 'sss', $nama, $email, $password_hashed);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Pendaftaran berhasil! Silakan login.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal melakukan pendaftaran. Silakan coba lagi.']);
    }
    mysqli_stmt_close($stmt);
}
?>
