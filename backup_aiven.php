<?php
declare(strict_types=1);

/**
 * Create SQL backup from Aiven DB for phpMyAdmin import.
 * Usage:
 *   php backup_aiven.php
 */

require_once __DIR__ . '/config/db_config.php';

date_default_timezone_set('Asia/Jakarta');

$backupDir = __DIR__ . '/backup';
if (!is_dir($backupDir) && !mkdir($backupDir, 0777, true) && !is_dir($backupDir)) {
    fwrite(STDERR, "Gagal membuat folder backup.\n");
    exit(1);
}

$timestamp = date('Ymd_His');
$outputFile = $backupDir . "/aiven_backup_{$timestamp}.sql";

$conn = mysqli_init();
mysqli_ssl_set($conn, null, null, DB_AIVEN_CA, null, null);

$ok = @mysqli_real_connect(
    $conn,
    DB_AIVEN_HOST,
    DB_AIVEN_USER,
    DB_AIVEN_PASS,
    DB_AIVEN_NAME,
    DB_AIVEN_PORT,
    null,
    MYSQLI_CLIENT_SSL
);

if (!$ok) {
    fwrite(STDERR, "Koneksi Aiven gagal: " . mysqli_connect_error() . "\n");
    exit(1);
}

mysqli_set_charset($conn, 'utf8mb4');

$fh = fopen($outputFile, 'wb');
if ($fh === false) {
    fwrite(STDERR, "Gagal membuat file dump.\n");
    exit(1);
}

fwrite($fh, "-- Aiven SQL Backup\n");
fwrite($fh, "-- Database: " . DB_AIVEN_NAME . "\n");
fwrite($fh, "-- Generated at: " . date('Y-m-d H:i:s') . " Asia/Jakarta\n\n");
fwrite($fh, "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n");
fwrite($fh, "START TRANSACTION;\n");
fwrite($fh, "SET time_zone = \"+00:00\";\n\n");
fwrite($fh, "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n");
fwrite($fh, "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n");
fwrite($fh, "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n");
fwrite($fh, "/*!40101 SET NAMES utf8mb4 */;\n\n");

// Disable foreign key checks so tables can be created in any order during import
fwrite($fh, "SET FOREIGN_KEY_CHECKS = 0;\n\n");

$tablesResult = mysqli_query($conn, "SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
if (!$tablesResult) {
    fclose($fh);
    unlink($outputFile);
    fwrite(STDERR, "Gagal mengambil daftar tabel: " . mysqli_error($conn) . "\n");
    exit(1);
}

$tableNames = [];
while ($row = mysqli_fetch_row($tablesResult)) {
    $tableNames[] = $row[0];
}

foreach ($tableNames as $table) {
    $escapedTable = '`' . str_replace('`', '``', $table) . '`';

    $createRes = mysqli_query($conn, "SHOW CREATE TABLE {$escapedTable}");
    if (!$createRes) {
        fclose($fh);
        unlink($outputFile);
        fwrite(STDERR, "Gagal ambil CREATE TABLE untuk {$table}: " . mysqli_error($conn) . "\n");
        exit(1);
    }

    $createRow = mysqli_fetch_assoc($createRes);
    $createSql = $createRow['Create Table'] ?? '';

    // Convert any ANSI_QUOTES identifiers from Aiven into MySQL backticks for local import.
    $createSql = str_replace('"', '`', $createSql);

    fwrite($fh, "--\n-- Table structure for table {$escapedTable}\n--\n\n");
    fwrite($fh, "DROP TABLE IF EXISTS {$escapedTable};\n");
    fwrite($fh, $createSql . ";\n\n");

    $dataRes = mysqli_query($conn, "SELECT * FROM {$escapedTable}");
    if (!$dataRes) {
        fclose($fh);
        unlink($outputFile);
        fwrite(STDERR, "Gagal ambil data tabel {$table}: " . mysqli_error($conn) . "\n");
        exit(1);
    }

    if (mysqli_num_rows($dataRes) > 0) {
        fwrite($fh, "--\n-- Dumping data for table {$escapedTable}\n--\n\n");
    }

    while ($row = mysqli_fetch_assoc($dataRes)) {
        $columns = array_keys($row);
        $escapedColumns = array_map(
            static fn(string $c): string => '`' . str_replace('`', '``', $c) . '`',
            $columns
        );

        $values = [];
        foreach ($row as $value) {
            if ($value === null) {
                $values[] = 'NULL';
            } else {
                $values[] = "'" . mysqli_real_escape_string($conn, (string) $value) . "'";
            }
        }

        $insertSql = "INSERT INTO {$escapedTable} (" . implode(', ', $escapedColumns) . ") VALUES (" . implode(', ', $values) . ");\n";
        fwrite($fh, $insertSql);
    }

    fwrite($fh, "\n");
}

fwrite($fh, "COMMIT;\n");
fwrite($fh, "SET FOREIGN_KEY_CHECKS = 1;\n");
fwrite($fh, "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\n");
fwrite($fh, "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\n");
fwrite($fh, "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;\n");

fclose($fh);
mysqli_close($conn);

echo "Backup selesai: {$outputFile}\n";
