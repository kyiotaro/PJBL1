<?php
require_once __DIR__ . '/config/db_config.php';

// If running on localhost or CLI, prefer local DB first
$isLocal = (php_sapi_name() === 'cli') || in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1']);

if ($isLocal) {
    $koneksi = mysqli_connect(
        DB_LOCAL_HOST,
        DB_LOCAL_USER,
        DB_LOCAL_PASS,
        DB_LOCAL_NAME
    );

    if (!$koneksi) {
        // fallback to Aiven if local fails
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

        if (!$connected) {
            die("Koneksi database gagal: " . mysqli_connect_error());
        }
    }
} else {
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
}

mysqli_set_charset($koneksi, 'utf8mb4');
?>