<?php
session_start();
require_once '../../koneksi.php';
require_once '../../config/mail_config.php';
require_once '../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak valid.']);
    exit;
}

$email = trim($_POST['email'] ?? '');

if ($email === '') {
    echo json_encode(['success' => false, 'message' => 'Email wajib diisi.']);
    exit;
}

// Cek apakah email terdaftar sebagai admin
$stmt = mysqli_prepare($koneksi, "SELECT id, nama FROM admin WHERE email = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 's', $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$admin = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$admin) {
    echo json_encode(['success' => false, 'message' => 'Email tidak terdaftar sebagai admin.']);
    exit;
}

// Hapus OTP lama yang belum dipakai untuk admin ini
$delStmt = mysqli_prepare($koneksi, "DELETE FROM otp_sessions WHERE admin_id = ? AND used = 0");
mysqli_stmt_bind_param($delStmt, 'i', $admin['id']);
mysqli_stmt_execute($delStmt);
mysqli_stmt_close($delStmt);

// Generate kode OTP 6 karakter (huruf + angka)
$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
$kode = '';
for ($i = 0; $i < 6; $i++) {
    $kode .= $chars[random_int(0, strlen($chars) - 1)];
}

// Simpan OTP ke database, expired 15 menit
$expiredAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));
$insStmt = mysqli_prepare($koneksi, "INSERT INTO otp_sessions (admin_id, kode, expired_at) VALUES (?, ?, ?)");
mysqli_stmt_bind_param($insStmt, 'iss', $admin['id'], $kode, $expiredAt);
mysqli_stmt_execute($insStmt);
mysqli_stmt_close($insStmt);

// Simpan email di session sementara untuk halaman verifikasi
$_SESSION['otp_email'] = $email;
$_SESSION['otp_admin_id'] = $admin['id'];

// Kirim email via PHPMailer
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = MAIL_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = MAIL_USERNAME;
    $mail->Password   = MAIL_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = MAIL_PORT;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
    $mail->addAddress($email, $admin['nama']);

    $mail->isHTML(true);
    $mail->Subject = 'Kode Login Admin - Permata Biru Nusantara';
    $mail->Body    = "
        <div style='font-family: Arial, sans-serif; max-width: 480px; margin: auto;'>
            <h2 style='color: #0369A1;'>Permata Biru Nusantara</h2>
            <p>Halo, <strong>{$admin['nama']}</strong>!</p>
            <p>Kode login admin kamu:</p>
            <div style='font-size: 36px; font-weight: bold; letter-spacing: 8px;
                        background: #f0f9ff; border: 2px dashed #0369A1;
                        padding: 20px; text-align: center; border-radius: 10px;
                        color: #0369A1;'>
                {$kode}
            </div>
            <p style='margin-top: 16px; color: #666;'>
                Kode ini berlaku selama <strong>15 menit</strong>.<br>
                Jangan bagikan kode ini kepada siapapun.
            </p>
        </div>
    ";

    $mail->send();
    echo json_encode(['success' => true, 'message' => 'Kode OTP berhasil dikirim ke email kamu.']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Gagal mengirim email: ' . $mail->ErrorInfo]);
}