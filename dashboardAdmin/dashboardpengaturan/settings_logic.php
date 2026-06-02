<?php
session_start();
require_once '../../koneksi.php';

header('Content-Type: application/json');

if (empty($_SESSION['admin_logged_in'])) {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'update_profile') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $currentEmail = $_SESSION['admin_email'];

    if (empty($nama) || empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Nama dan Email wajib diisi.']);
        exit;
    }

    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($koneksi, "UPDATE admin SET nama = ?, email = ?, password = ? WHERE email = ?");
        mysqli_stmt_bind_param($stmt, 'ssss', $nama, $email, $hashedPassword, $currentEmail);
    } else {
        $stmt = mysqli_prepare($koneksi, "UPDATE admin SET nama = ?, email = ? WHERE email = ?");
        mysqli_stmt_bind_param($stmt, 'sss', $nama, $email, $currentEmail);
    }

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['admin_email'] = $email;
        echo json_encode(['success' => true, 'message' => 'Profil berhasil diperbarui.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal memperbarui profil: ' . mysqli_error($koneksi)]);
    }
    mysqli_stmt_close($stmt);
} 

elseif ($action === 'update_settings') {
    $settings = $_POST['settings'] ?? [];
    $success = true;
    $errors = [];

    foreach ($settings as $kunci => $nilai) {
        $stmt = mysqli_prepare($koneksi, "INSERT INTO pengaturan (kunci, nilai) VALUES (?, ?) ON DUPLICATE KEY UPDATE nilai = ?");
        mysqli_stmt_bind_param($stmt, 'sss', $kunci, $nilai, $nilai);
        if (!mysqli_stmt_execute($stmt)) {
            $success = false;
            $errors[] = "Gagal menyimpan $kunci";
        }
        mysqli_stmt_close($stmt);
    }

    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Pengaturan berhasil disimpan.']);
    } else {
        echo json_encode(['success' => false, 'message' => implode(", ", $errors)]);
    }
}

elseif ($action === 'get_settings') {
    $res = mysqli_query($koneksi, "SELECT kunci, nilai FROM pengaturan");
    $settings = [];
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $settings[$row['kunci']] = $row['nilai'];
        }
    }

    // Also get current admin profile
    $email = $_SESSION['admin_email'] ?? '';
    $profile = null;
    if ($email) {
        $stmt = mysqli_prepare($koneksi, "SELECT nama, email FROM admin WHERE email = ?");
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $resProfile = mysqli_stmt_get_result($stmt);
        if ($resProfile) {
            $profile = mysqli_fetch_assoc($resProfile);
        }
        mysqli_stmt_close($stmt);
    }

    echo json_encode(['success' => true, 'settings' => $settings, 'profile' => $profile]);
}

elseif ($action === 'list_backups') {
    $backupDir = '../../backups/';
    $backups = [];
    if (is_dir($backupDir)) {
        $files = scandir($backupDir, SCANDIR_SORT_DESCENDING);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..' && str_ends_with($file, '.sql')) {
                $backups[] = [
                    'name' => $file,
                    'size' => round(filesize($backupDir . $file) / 1024, 2) . ' KB',
                    'date' => date('Y-m-d H:i:s', filemtime($backupDir . $file))
                ];
            }
        }
    }
    echo json_encode(['success' => true, 'backups' => $backups]);
}

elseif ($action === 'delete_backup') {
    $fileName = $_POST['fileName'] ?? '';
    $filePath = '../../backups/' . basename($fileName);

    if (file_exists($filePath)) {
        unlink($filePath);
        echo json_encode(['success' => true, 'message' => 'Backup berhasil dihapus.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'File tidak ditemukan.']);
    }
}

elseif ($action === 'restore_db') {
    if (!isset($_FILES['backup_file']) || $_FILES['backup_file']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Gagal mengunggah file.']);
        exit;
    }

    $tmpFile = $_FILES['backup_file']['tmp_name'];
    $sql = file_get_contents($tmpFile);

    // Basic safety check
    if (stripos($sql, 'DROP TABLE') === false && stripos($sql, 'CREATE TABLE') === false && stripos($sql, 'INSERT INTO') === false) {
        echo json_encode(['success' => false, 'message' => 'File SQL tidak valid.']);
        exit;
    }

    // Multi-query execution
    if (mysqli_multi_query($koneksi, $sql)) {
        // Clear multi results
        do { if ($result = mysqli_store_result($koneksi)) { mysqli_free_result($result); } } while (mysqli_next_result($koneksi));
        echo json_encode(['success' => true, 'message' => 'Database berhasil dipulihkan.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal memulihkan database: ' . mysqli_error($koneksi)]);
    }
}
?>