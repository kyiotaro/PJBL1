<?php
require_once __DIR__ . '/config/db_config.php';

function testAivenConnection(): array
{
    $conn = mysqli_init();
    if (!$conn) {
        return [
            'name' => 'Aiven (Utama)',
            'status' => false,
            'error' => 'Gagal inisialisasi mysqli.',
            'host' => DB_AIVEN_HOST . ':' . DB_AIVEN_PORT,
            'database' => DB_AIVEN_NAME,
            'latency_ms' => null,
            'server_info' => null,
        ];
    }

    mysqli_ssl_set($conn, null, null, DB_AIVEN_CA, null, null);
    $start = microtime(true);
    $connected = @mysqli_real_connect(
        $conn,
        DB_AIVEN_HOST,
        DB_AIVEN_USER,
        DB_AIVEN_PASS,
        DB_AIVEN_NAME,
        DB_AIVEN_PORT,
        null,
        MYSQLI_CLIENT_SSL
    );
    $latency = round((microtime(true) - $start) * 1000, 2);

    if (!$connected) {
        return [
            'name' => 'Aiven (Utama)',
            'status' => false,
            'error' => mysqli_connect_error() ?: 'Koneksi ditolak.',
            'host' => DB_AIVEN_HOST . ':' . DB_AIVEN_PORT,
            'database' => DB_AIVEN_NAME,
            'latency_ms' => $latency,
            'server_info' => null,
        ];
    }

    @mysqli_set_charset($conn, 'utf8mb4');
    $pingOk = @mysqli_ping($conn);
    $serverInfo = mysqli_get_server_info($conn);
    mysqli_close($conn);

    return [
        'name' => 'Aiven (Utama)',
        'status' => $pingOk,
        'error' => $pingOk ? null : 'Koneksi tersambung tapi ping gagal.',
        'host' => DB_AIVEN_HOST . ':' . DB_AIVEN_PORT,
        'database' => DB_AIVEN_NAME,
        'latency_ms' => $latency,
        'server_info' => $serverInfo,
    ];
}

function testLocalConnection(): array
{
    $start = microtime(true);
    $conn = @mysqli_connect(
        DB_LOCAL_HOST,
        DB_LOCAL_USER,
        DB_LOCAL_PASS,
        DB_LOCAL_NAME
    );
    $latency = round((microtime(true) - $start) * 1000, 2);

    if (!$conn) {
        return [
            'name' => 'Local phpMyAdmin (Cadangan)',
            'status' => false,
            'error' => mysqli_connect_error() ?: 'Koneksi ditolak.',
            'host' => DB_LOCAL_HOST,
            'database' => DB_LOCAL_NAME,
            'latency_ms' => $latency,
            'server_info' => null,
        ];
    }

    @mysqli_set_charset($conn, 'utf8mb4');
    $pingOk = @mysqli_ping($conn);
    $serverInfo = mysqli_get_server_info($conn);
    mysqli_close($conn);

    return [
        'name' => 'Local phpMyAdmin (Cadangan)',
        'status' => $pingOk,
        'error' => $pingOk ? null : 'Koneksi tersambung tapi ping gagal.',
        'host' => DB_LOCAL_HOST,
        'database' => DB_LOCAL_NAME,
        'latency_ms' => $latency,
        'server_info' => $serverInfo,
    ];
}

$results = [
    testAivenConnection(),
    testLocalConnection(),
];

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Status Database</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; background: #f8fafc; color: #0f172a; }
        .card { background: #fff; border-radius: 10px; padding: 18px; box-shadow: 0 4px 14px rgba(15, 23, 42, 0.08); max-width: 900px; margin: 0 auto; }
        h1 { margin-top: 0; font-size: 24px; }
        table { width: 100%; border-collapse: collapse; margin-top: 14px; }
        th, td { text-align: left; padding: 10px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        th { background: #f1f5f9; }
        .ok { color: #166534; font-weight: 700; }
        .bad { color: #991b1b; font-weight: 700; }
        .meta { margin-top: 12px; color: #334155; font-size: 14px; }
        code { background: #f1f5f9; padding: 2px 6px; border-radius: 6px; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Status Koneksi Database</h1>
        <p>Halaman ini melakukan tes koneksi untuk database utama (Aiven) dan cadangan (phpMyAdmin lokal).</p>

        <table>
            <thead>
                <tr>
                    <th>Database</th>
                    <th>Status</th>
                    <th>Host</th>
                    <th>Nama DB</th>
                    <th>Latency</th>
                    <th>Server</th>
                    <th>Detail Error</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($results as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['name']); ?></td>
                    <td class="<?= $row['status'] ? 'ok' : 'bad'; ?>">
                        <?= $row['status'] ? 'TERHUBUNG' : 'GAGAL'; ?>
                    </td>
                    <td><code><?= htmlspecialchars($row['host']); ?></code></td>
                    <td><?= htmlspecialchars($row['database']); ?></td>
                    <td><?= $row['latency_ms'] !== null ? htmlspecialchars((string)$row['latency_ms']) . ' ms' : '-'; ?></td>
                    <td><?= $row['server_info'] ? htmlspecialchars($row['server_info']) : '-'; ?></td>
                    <td><?= $row['error'] ? htmlspecialchars($row['error']) : '-'; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <p class="meta">
            Waktu cek: <strong><?= date('Y-m-d H:i:s'); ?></strong>
        </p>
    </div>
</body>
</html>
