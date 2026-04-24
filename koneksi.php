<?php
// ── Konfigurasi Aiven (primary) ──
define('DB_AIVEN_HOST', 'mysql-35eca9c7-permata-biru-nusantara.c.aivencloud.com');
define('DB_AIVEN_PORT',  28919);
define('DB_AIVEN_USER', 'avnadmin');
define('DB_AIVEN_PASS', 'AVNS_eo0mnRPLbBMJk4EoM8S');
define('DB_AIVEN_NAME', 'defaultdb');
define('DB_AIVEN_CA',   __DIR__ . '/config/aiven-ca.pem');

// ── Konfigurasi XAMPP lokal (fallback) ──
define('DB_LOCAL_HOST', 'localhost');
define('DB_LOCAL_USER', 'root');
define('DB_LOCAL_PASS', '');
define('DB_LOCAL_NAME', 'permatabirunusantara');

// ── Coba konek ke Aiven dulu ──
$koneksi = mysqli_init();
mysqli_ssl_set($koneksi, null, null, DB_AIVEN_CA, null, null);

$connected = @mysqli_real_connect(
    $koneksi,
    DB_AIVEN_HOST,
    DB_AIVEN_USER,
    DB_AIVEN_PASS,
    DB_AIVEN_NAME,
    DB_AIVEN_PORT,
    null,
    MYSQLI_CLIENT_SSL
);

// ── Kalau Aiven gagal, fallback ke XAMPP ──
if (!$connected) {
    $koneksi = mysqli_connect(
        DB_LOCAL_HOST,
        DB_LOCAL_USER,
        DB_LOCAL_PASS,
        DB_LOCAL_NAME
    );

    if (!$koneksi) {
        die("Koneksi database gagal: " . mysqli_connect_error());
    }
}

mysqli_set_charset($koneksi, 'utf8mb4');
?>