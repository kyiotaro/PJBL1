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
$otpSessionKeys = ['temp_otp', 'temp_email', 'temp_user_id', 'temp_user_type', 'temp_user_level', 'otp_expiry'];
$isDebugMode = true; // Force debug mode for troubleshooting

function writeOtpDebugLog($message)
{
    global $isDebugMode;
    if (!$isDebugMode) {
        return;
    }
    @file_put_contents(__DIR__ . '/email_debug.log', date('c') . ' ' . $message . PHP_EOL, FILE_APPEND);
}

function tableExists($conn, $tableName)
{
    $stmt = mysqli_prepare($conn, "SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ? LIMIT 1");
    if (!$stmt) {
        return false;
    }
    mysqli_stmt_bind_param($stmt, 's', $tableName);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $exists = $result && mysqli_num_rows($result) > 0;
    mysqli_stmt_close($stmt);
    return $exists;
}

function getSmtpSettings($conn)
{
    $smtp = [];
    if (!tableExists($conn, 'pengaturan')) {
        return $smtp;
    }

    $qSmtp = mysqli_query($conn, "SELECT kunci, nilai FROM pengaturan WHERE kunci LIKE 'smtp_%'");
    if ($qSmtp === false) {
        return $smtp;
    }

    while ($row = mysqli_fetch_assoc($qSmtp)) {
        $smtp[$row['kunci']] = $row['nilai'];
    }
    return $smtp;
}

function clearOtpSession()
{
    global $otpSessionKeys;
    foreach ($otpSessionKeys as $key) {
        unset($_SESSION[$key]);
    }
}

if ($action === 'send_otp') {
    $email = $_POST['email'] ?? '';

    if (empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Email wajib diisi.']);
        exit;
    }

    writeOtpDebugLog("TRACE: send_otp request received for $email");

    // Cek user di tabel users dulu (lebih aman karena pasti dipakai register).
    $user_type = null;
    $user_level = null;
    $user_id = null;

    $stmt = mysqli_prepare($koneksi, "SELECT id, level FROM users WHERE email = ? LIMIT 1");
    if (!$stmt) {
        $dberr = mysqli_error($koneksi);
        writeOtpDebugLog("DB PREPARE ERROR (users): $dberr");
        echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan database. Silakan coba lagi.']);
        exit;
    }
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $user_level = $row['level'];
        $user_type = (strtolower((string)$user_level) === 'admin') ? 'admin' : 'user';
        $user_id = $row['id'];
    }
    mysqli_stmt_close($stmt);

    // Jika belum ditemukan di users, coba cek admin (hanya jika tabel admin tersedia).
    if (!$user_type) {
        if (tableExists($koneksi, 'admin')) {
            $stmt = mysqli_prepare($koneksi, "SELECT id FROM admin WHERE email = ? LIMIT 1");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 's', $email);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                if ($row = mysqli_fetch_assoc($result)) {
                    $user_type = 'admin';
                    $user_level = 'admin';
                    $user_id = $row['id'];
                }
                mysqli_stmt_close($stmt);
            }
        }
    }

    // Email tidak ditemukan
    if (!$user_type) {
        echo json_encode(['success' => false, 'message' => 'Email tidak terdaftar.']);
        exit;
    }

    // Generate OTP
    $otp = random_int(100000, 999999);
    $_SESSION['temp_otp'] = $otp;
    $_SESSION['temp_email'] = $email;
    $_SESSION['temp_user_id'] = $user_id;
    $_SESSION['temp_user_type'] = $user_type;
    $_SESSION['temp_user_level'] = $user_level;
    $_SESSION['otp_expiry'] = time() + (5 * 60); // Berlaku 5 menit

    // Ambil konfigurasi SMTP, fallback aman saat tabel pengaturan belum tersedia.
    $smtp = getSmtpSettings($koneksi);

    // Konfigurasi Email
    $mail = new PHPMailer(true);
    set_time_limit(60); // Tingkatkan ke 60 detik

    try {
        $mail->SMTPDebug = 3;
        $mailDebugLogs = [];
        $mail->Debugoutput = function($str, $level) use (&$mailDebugLogs) {
            $mailDebugLogs[] = date('H:i:s') . " [$level] " . trim($str);
        };

        // Jika konfigurasi SMTP tidak diisi, coba gunakan mail() (isMail)
        $smtp_user = $smtp['smtp_user'] ?? '';
        $smtp_host = $smtp['smtp_host'] ?? 'smtp.gmail.com';

        if (!empty($smtp_user)) {
            // Pengaturan Server SMTP
            $mail->isSMTP();
            
            // Force IPv4 if using gmail to avoid handshake timeouts
            if ($smtp_host === 'smtp.gmail.com') {
                $mail->Host = gethostbyname('smtp.gmail.com');
            } else {
                $mail->Host = $smtp_host;
            }

            $mail->SMTPAuth   = true;
            $mail->Username   = $smtp_user;
            $mail->Password   = $smtp['smtp_pass'] ?? '';
            
            // Port 587 with STARTTLS is usually more compatible than 465 SSL
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->Timeout    = 20;
            $mail->SMTPKeepAlive = false;
            $mail->SMTPAutoTLS = true;
            // Bypass SSL Verification jika diperlukan
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
        } else {
            // Fallback ke PHP mail() jika SMTP belum dikonfigurasi
            $mail->isMail();
        }

        // Penerima
        $fromEmail = $smtp['smtp_user'] ?? 'no-reply@permatabiru.com';
        $mail->setFrom($fromEmail, 'Permata Biru Nusantara');
        $mail->addAddress($email);

        // Konten
        $mail->isHTML(true);
        $mail->Subject = 'Kode OTP Login';
        $mail->Body    = "Kode OTP Anda adalah: <b>$otp</b><br>Kode ini berlaku selama 5 menit.";

        $startTime = microtime(true);
        writeOtpDebugLog("TRACE: before mail->send() to $email via " . ($mail->Mailer == 'smtp' ? "SMTP (" . $mail->Host . ":" . $mail->Port . ")" : "mail()"));

        $mail->send();

        $duration = round(microtime(true) - $startTime, 2);
        writeOtpDebugLog("TRACE: after mail->send() - Duration: {$duration}s");
        writeOtpDebugLog("SUCCESS: " . json_encode($mailDebugLogs));

        echo json_encode(['success' => true, 'message' => 'OTP telah dikirim ke email Anda.', 'duration' => $duration]);
    } catch (Exception $e) {
        $err = $e->getMessage();
        $mailerErr = $mail->ErrorInfo ?? '';
        writeOtpDebugLog("ERROR: " . json_encode(['exception' => $err, 'mailer' => $mailerErr, 'debug' => $mailDebugLogs]));
        echo json_encode([
            'success' => false, 
            'message' => 'Gagal mengirim email OTP. Silakan coba lagi.',
            'error_detail' => $err,
            'mailer_info' => $mailerErr,
            'debug' => $mailDebugLogs
        ]);
    }
} 

elseif ($action === 'verify_otp') {
    $otp_input = $_POST['otp'] ?? '';

    if (empty($otp_input)) {
        echo json_encode(['success' => false, 'message' => 'Masukkan kode OTP.']);
        exit;
    }

    foreach ($otpSessionKeys as $key) {
        if (!isset($_SESSION[$key])) {
            clearOtpSession();
            echo json_encode([
                'success' => false,
                'message' => 'Sesi OTP tidak ditemukan. Silakan kirim ulang OTP.'
            ]);
            exit;
        }
    }

    if (time() > $_SESSION['otp_expiry']) {
        clearOtpSession();
        echo json_encode(['success' => false, 'message' => 'OTP sudah kadaluarsa. Silakan kirim ulang.']);
        exit;
    }

    if ($otp_input == $_SESSION['temp_otp']) {
        // Login Sukses
        $email = $_SESSION['temp_email'];
        $user_id = $_SESSION['temp_user_id'];
        $user_type = $_SESSION['temp_user_type'];
        $user_level = $_SESSION['temp_user_level'];

        if ($user_type === 'admin') {
            unset($_SESSION['user_logged_in'], $_SESSION['user_email'], $_SESSION['user_id'], $_SESSION['user_level']);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_email'] = $email;
            $_SESSION['admin_id'] = $user_id;
        } else {
            unset($_SESSION['admin_logged_in'], $_SESSION['admin_email'], $_SESSION['admin_id']);
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_level'] = $user_level;
        }

        // Hapus temp data
        clearOtpSession();

        echo json_encode([
            'success' => true,
            'user_type' => $user_type,
            'user_level' => $user_level
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Kode OTP salah.']);
    }
}

elseif ($action === 'login_password') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Email dan Password wajib diisi.']);
        exit;
    }

    $user_type = null;
    $user_level = null;
    $user_id = null;
    $hashed_password = null;

    // Cek di tabel users
    $stmt = mysqli_prepare($koneksi, "SELECT id, level, password FROM users WHERE email = ? LIMIT 1");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($row = mysqli_fetch_assoc($result)) {
            $user_level = $row['level'];
            $user_type = (strtolower((string)$user_level) === 'admin') ? 'admin' : 'user';
            $user_id = $row['id'];
            $hashed_password = $row['password'] ?? '';
        }
        mysqli_stmt_close($stmt);
    }

    // Cek di tabel admin jika tidak ditemukan di users atau password kosong
    if (!$user_type || empty($hashed_password)) {
        if (tableExists($koneksi, 'admin')) {
            $stmt = mysqli_prepare($koneksi, "SELECT id, password FROM admin WHERE email = ? LIMIT 1");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 's', $email);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                if ($row = mysqli_fetch_assoc($result)) {
                    $user_type = 'admin';
                    $user_level = 'admin';
                    $user_id = $row['id'];
                    $hashed_password = $row['password'] ?? '';
                }
                mysqli_stmt_close($stmt);
            }
        }
    }

    if (!$user_type) {
        echo json_encode(['success' => false, 'message' => 'Email tidak terdaftar.']);
        exit;
    }

    if (empty($hashed_password)) {
        echo json_encode(['success' => false, 'message' => 'Akun ini belum memiliki password. Silakan login menggunakan OTP terlebih dahulu.']);
        exit;
    }

    if (password_verify($password, $hashed_password)) {
        // Login Sukses
        if ($user_type === 'admin') {
            unset($_SESSION['user_logged_in'], $_SESSION['user_email'], $_SESSION['user_id'], $_SESSION['user_level']);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_email'] = $email;
            $_SESSION['admin_id'] = $user_id;
        } else {
            unset($_SESSION['admin_logged_in'], $_SESSION['admin_email'], $_SESSION['admin_id']);
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_level'] = $user_level;
        }

        echo json_encode([
            'success' => true,
            'user_type' => $user_type,
            'user_level' => $user_level
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Password salah.']);
    }
}
?>
