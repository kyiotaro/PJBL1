<?php
session_start();
require_once '../../koneksi.php';

// Load PHPMailer
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

    // CEK EMAIL DI TABEL ADMIN ATAU USERS
    $user_type = null;
    $user_level = null;

    // Cek di tabel admin
    $stmt = mysqli_prepare($koneksi, "SELECT id FROM admin WHERE email = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $user_type = 'admin';
        $user_level = 'admin';
    }
    mysqli_stmt_close($stmt);

    // Jika tidak ditemukan di admin, cek di tabel users
    if (!$user_type) {
        $stmt = mysqli_prepare($koneksi, "SELECT level FROM users WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $user_type = 'user';
            $user_level = $row['level']; // 'user' atau 'admin'
        }
        mysqli_stmt_close($stmt);
    }

    // Email tidak ditemukan
    if (!$user_type) {
        echo json_encode(['success' => false, 'message' => 'Email tidak terdaftar.']);
        exit;
    }

    // Generate OTP
    $otp = rand(100000, 999999);
    $_SESSION['temp_otp'] = $otp;
    $_SESSION['temp_email'] = $email;
    $_SESSION['temp_user_type'] = $user_type;
    $_SESSION['temp_user_level'] = $user_level;
    $_SESSION['otp_expiry'] = time() + (5 * 60); // Berlaku 5 menit

    // Ambil Konfigurasi SMTP dari Database
    $qSmtp = mysqli_query($koneksi, "SELECT kunci, nilai FROM pengaturan WHERE kunci LIKE 'smtp_%'");
    $smtp = [];
    while ($row = mysqli_fetch_assoc($qSmtp)) {
        $smtp[$row['kunci']] = $row['nilai'];
    }

    // Konfigurasi Email
    $mail = new PHPMailer(true);

    try {
        // Pengaturan Server
        $mail->isSMTP();
        $mail->Host       = $smtp['smtp_host'] ?? 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtp['smtp_user'] ?? '';
        $mail->Password   = $smtp['smtp_pass'] ?? '';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $smtp['smtp_port'] ?? 587;
        // Bypass SSL Verification
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
        $mail->Subject = 'Kode OTP Login';
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
        $email = $_SESSION['temp_email'];
        $user_type = $_SESSION['temp_user_type'];
        $user_level = $_SESSION['temp_user_level'];

        if ($user_type === 'admin') {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_email'] = $email;
        } else {
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_level'] = $user_level;
        }

        // Hapus temp data
        unset($_SESSION['temp_otp']);
        unset($_SESSION['temp_email']);
        unset($_SESSION['temp_user_type']);
        unset($_SESSION['temp_user_level']);
        unset($_SESSION['otp_expiry']);

        echo json_encode([
            'success' => true,
            'user_type' => $user_type,
            'user_level' => $user_level
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Kode OTP salah.']);
    }
}
?>
