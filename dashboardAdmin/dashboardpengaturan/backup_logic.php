<?php
session_start();
require_once '../../koneksi.php';
require_once '../../config/db_config.php';

// Pastikan hanya admin yang bisa akses
if (empty($_SESSION['admin_logged_in'])) {
    die("Akses ditolak.");
}

/**
 * Fungsi sederhana untuk mengekspor database ke file .sql
 */
function backupDatabase($host, $user, $pass, $name, $port, $ca_path = null) {
    $conn = mysqli_init();
    
    if ($ca_path) {
        mysqli_ssl_set($conn, null, null, $ca_path, null, null);
        $connected = mysqli_real_connect($conn, $host, $user, $pass, $name, $port, null, MYSQLI_CLIENT_SSL);
    } else {
        $connected = mysqli_real_connect($conn, $host, $user, $pass, $name, $port);
    }

    if (!$connected) {
        return false;
    }

    $tables = array();
    $result = mysqli_query($conn, "SHOW TABLES");
    while ($row = mysqli_fetch_row($result)) {
        $tables[] = $row[0];
    }

    $return = "-- Backup Database: $name\n";
    $return .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
    $return .= "SET NAMES utf8mb4;\n";
    $return .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

    foreach ($tables as $table) {
        $result = mysqli_query($conn, "SELECT * FROM `$table` ");
        $num_fields = mysqli_num_fields($result);

        $return .= "DROP TABLE IF EXISTS `$table`;";
        $row2 = mysqli_fetch_row(mysqli_query($conn, "SHOW CREATE TABLE `$table`"));
        
        // Perbaikan: Ganti double quotes dengan backticks agar kompatibel dengan MariaDB/MySQL lokal
        $createTableSql = str_replace('"', '`', $row2[1]);
        $return .= "\n\n" . $createTableSql . ";\n\n";

        while ($row = mysqli_fetch_row($result)) {
            $return .= "INSERT INTO `$table` VALUES(";
            for ($j = 0; $j < $num_fields; $j++) {
                if (isset($row[$j])) {
                    $val = mysqli_real_escape_string($conn, $row[$j]);
                    $return .= "'" . $val . "'";
                } else {
                    $return .= 'NULL';
                }
                if ($j < ($num_fields - 1)) {
                    $return .= ',';
                }
            }
            $return .= ");\n";
        }
        $return .= "\n\n";
    }

    $return .= "\nSET FOREIGN_KEY_CHECKS = 1;\n";
    mysqli_close($conn);
    return $return;
}

// Lakukan backup dari Aiven
$backupData = backupDatabase(DB_AIVEN_HOST, DB_AIVEN_USER, DB_AIVEN_PASS, DB_AIVEN_NAME, DB_AIVEN_PORT, DB_AIVEN_CA);

if ($backupData === false) {
    die("Gagal menghubungkan ke database Aiven untuk backup.");
}

// Simpan ke file lokal agar bisa didownload
$fileName = 'backup_aiven_' . date('Y-m-d_H-i-s') . '.sql';
$filePath = '../../backups/' . $fileName;

if (!is_dir('../../backups')) {
    mkdir('../../backups', 0777, true);
}

file_put_contents($filePath, $backupData);

// Set header untuk download otomatis
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename=' . basename($fileName));
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . strlen($backupData));
echo $backupData;
exit;
