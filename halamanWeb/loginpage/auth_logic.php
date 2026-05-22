<?php
session_start();
require_once '../../koneksi.php';

// Load PHPMailer (Pastikan path ini benar dan file ada)
// Jika menggunakan composer, cukup: require_once '../../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../vendor/phpmailer/phpmailer/src/Exception.php';
require '../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require '../../vendor/phpmailer/phpmailer/src/SMTP.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action === 'send_otp') {
    $email = $_POST['email'] ?? '';

    if (empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Email wajib diisi.']);
        exit;
    }

    // CEK APAKAH EMAIL TERDAFTAR DI TABEL ADMIN
    $stmt = mysqli_prepare($koneksi, "SELECT id FROM admin WHERE email = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) === 0) {
        echo json_encode(['success' => false, 'message' => 'Email admin tidak terdaftar.']);
        mysqli_stmt_close($stmt);
        exit;
    }
    mysqli_stmt_close($stmt);

    // Sederhana: Generate 6 digit angka
    $otp = rand(100000, 999999);
    $_SESSION['temp_otp'] = $otp;
    $_SESSION['temp_email'] = $email;
    $_SESSION['otp_expiry'] = time() + (5 * 60); // Berlaku 5 menit

    // Konfigurasi Email
    $mail = new PHPMailer(true);

    try {
        // Pengaturan Server
        $mail->SMTPDebug = 2; // Aktifkan debug (0 = off, 1 = client, 2 = client & server)
        $mail->Debugoutput = function($str, $level) {
            file_put_contents('mail_log.log', date('Y-m-d H:i:s').' ['.$level.'] '.$str.PHP_EOL, FILE_APPEND);
        };
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // Ganti dengan SMTP provider
        $mail->SMTPAuth   = true;
        $mail->Username   = 'kyiotaro7@gmail.com'; // Ganti dengan email pengirim
        $mail->Password   = 'kgsy cyqq dhnx ndmv'; // Ganti dengan App Password Gmail
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Bypass SSL Verification (Penting untuk XAMPP)
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        // Penerima
        $mail->setFrom('no-reply@permatabiru.com', 'Permata Biru Nusantara');
        $mail->addAddress($email);

        // Konten
        $mail->isHTML(true);
        $mail->Subject = 'Kode OTP Login Admin';
        $mail->Body    = "Kode OTP Anda adalah: <b>$otp</b><br>Kode ini berlaku selama 5 menit.";

        $mail->send();
        echo json_encode(['success' => true, 'message' => 'OTP telah dikirim ke email Anda.']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => "Gagal mengirim email. Mailer Error: {$mail->ErrorInfo}"]);
    }
} 

elseif ($action === 'verify_otp') {
    $otp_input = $_POST['otp'] ?? '';

    if (empty($otp_input)) {
        echo json_encode(['success' => false, 'message' => 'Masukkan kode OTP.']);
        exit;
    }

    if (time() > $_SESSION['otp_expiry']) {
        echo json_encode(['success' => false, 'message' => 'OTP sudah kadaluarsa. Silakan kirim ulang.']);
        exit;
    }

    if ($otp_input == $_SESSION['temp_otp']) {
        // Login Sukses
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_email'] = $_SESSION['temp_email'];
        
        // Hapus temp data
        unset($_SESSION['temp_otp']);
        unset($_SESSION['otp_expiry']);

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Kode OTP salah.']);
    }
}
