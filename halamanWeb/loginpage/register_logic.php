<?php
session_start();
require_once '../../koneksi.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action === 'register') {
    $nama = $_POST['nama'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validasi
    if (empty($nama) || empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Semua field wajib diisi.']);
        exit;
    }

    // Auto-fix table: tambahkan kolom password jika belum ada
    @mysqli_query($koneksi, "ALTER TABLE users ADD COLUMN password VARCHAR(255) AFTER email");

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

    // INSERT USER DENGAN LEVEL DEFAULT "user"
    $level = 'user';
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($koneksi, "INSERT INTO users (nama, email, password, level, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . mysqli_error($koneksi)]);
        exit;
    }
    
    mysqli_stmt_bind_param($stmt, 'ssss', $nama, $email, $hashed_password, $level);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Pendaftaran berhasil! Silakan login.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal melakukan pendaftaran: ' . mysqli_stmt_error($stmt)]);
    }
    mysqli_stmt_close($stmt);
}
?>
