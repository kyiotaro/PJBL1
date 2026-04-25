<?php
/**
 * n8n_topic.php
 * Endpoint yang dipanggil n8n di awal workflow untuk mengambil 1 topik random
 * dari pool yang belum pernah di-generate.
 * Letakkan di: dashboard/ai-article-generator/n8n_topic.php
 */

require_once '../../koneksi.php';
require_once '../../config/api_config.php';

header('Content-Type: application/json');

// ── Verifikasi secret key ─────────────────────────────────────────────────────
$authHeader = $_SERVER['HTTP_X_N8N_SECRET'] ?? '';
if (!defined('N8N_SECRET') || $authHeader !== N8N_SECRET) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// ── Ambil 1 topik random yang belum dipakai ───────────────────────────────────
$result = mysqli_query($koneksi, "
    SELECT id, topik, kategori 
    FROM n8n_topic_pool 
    WHERE used = 0 
    ORDER BY RAND() 
    LIMIT 1
");

if (!$result || mysqli_num_rows($result) === 0) {
    // Semua topik sudah terpakai — reset semua biar bisa dipakai lagi
    mysqli_query($koneksi, "UPDATE n8n_topic_pool SET used = 0, used_at = NULL");

    $result = mysqli_query($koneksi, "
        SELECT id, topik, kategori 
        FROM n8n_topic_pool 
        ORDER BY RAND() 
        LIMIT 1
    ");

    if (!$result || mysqli_num_rows($result) === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Topic pool kosong. Isi dulu tabel n8n_topic_pool.']);
        exit;
    }
}

$row = mysqli_fetch_assoc($result);

echo json_encode([
    'success'  => true,
    'topik_id' => (int) $row['id'],
    'topik'    => $row['topik'],
    'kategori' => $row['kategori'],
]);
