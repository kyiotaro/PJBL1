<?php
error_reporting(0);
ini_set('display_errors', 0);
ob_start();

require_once '../../koneksi.php';
require_once '../../config/api_config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$authHeader = $_SERVER['HTTP_X_N8N_SECRET'] ?? '';
if (!defined('N8N_SECRET') || $authHeader !== N8N_SECRET) {
    http_response_code(401);
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$body = file_get_contents('php://input');
$data = json_decode($body, true);
if (!$data) $data = json_decode(json_decode($body), true);
if (!$data) $data = $_POST;

if (!$data) {
    http_response_code(400);
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Invalid JSON body']);
    exit;
}

$judul        = trim($data['judul']    ?? '');
$slugKategori = trim($data['kategori'] ?? '');
$isi          = trim($data['isi']      ?? '');
$gambar       = trim($data['gambar']   ?? '');
$slug         = trim($data['slug']     ?? '');
$tanggal      = trim($data['tanggal']  ?? date('Y-m-d'));
$topik_id     = isset($data['topik_id']) ? (int)$data['topik_id'] : null;

if ($judul === '' || $slugKategori === '' || $isi === '') {
    http_response_code(422);
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Field judul, kategori, dan isi wajib diisi']);
    exit;
}

if ($slug === '') {
    $slug = trim(strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $judul)), '-');
}

$cekSlug = mysqli_prepare($koneksi, "SELECT id FROM artikel WHERE slug = ? LIMIT 1");
if (!$cekSlug) {
    http_response_code(500);
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'DB prepare error']);
    exit;
}
mysqli_stmt_bind_param($cekSlug, 's', $slug);
mysqli_stmt_execute($cekSlug);
mysqli_stmt_store_result($cekSlug);
if (mysqli_stmt_num_rows($cekSlug) > 0) {
    $slug = $slug . '-' . time();
}
mysqli_stmt_close($cekSlug);

$stmtKat = mysqli_prepare($koneksi, "SELECT id FROM kategori WHERE slug = ? LIMIT 1");
if (!$stmtKat) {
    http_response_code(500);
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'DB prepare error kategori']);
    exit;
}
mysqli_stmt_bind_param($stmtKat, 's', $slugKategori);
mysqli_stmt_execute($stmtKat);
$resKat     = mysqli_stmt_get_result($stmtKat);
$rowKat     = mysqli_fetch_assoc($resKat);
$kategoriId = $rowKat['id'] ?? null;
mysqli_stmt_close($stmtKat);

if (!$kategoriId) {
    http_response_code(422);
    ob_clean();
    echo json_encode(['success' => false, 'error' => "Kategori '$slugKategori' tidak ditemukan"]);
    exit;
}

$stmt = mysqli_prepare($koneksi, "INSERT INTO artikel (judul, kategori_id, tanggal, gambar, isi, slug) VALUES (?, ?, ?, ?, ?, ?)");
if (!$stmt) {
    http_response_code(500);
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'DB prepare insert error']);
    exit;
}
mysqli_stmt_bind_param($stmt, 'sissss', $judul, $kategoriId, $tanggal, $gambar, $isi, $slug);

if (!mysqli_stmt_execute($stmt)) {
    $errMsg = mysqli_stmt_error($stmt);
    mysqli_stmt_close($stmt);
    http_response_code(500);
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'DB error: ' . $errMsg]);
    exit;
}

$newId = mysqli_insert_id($koneksi);
mysqli_stmt_close($stmt);

if ($topik_id !== null) {
    $stmtUsed = mysqli_prepare($koneksi, "UPDATE n8n_topic_pool SET used = 1, used_at = NOW() WHERE id = ?");
    if ($stmtUsed) {
        mysqli_stmt_bind_param($stmtUsed, 'i', $topik_id);
        mysqli_stmt_execute($stmtUsed);
        mysqli_stmt_close($stmtUsed);
    }
}

ob_clean();
echo json_encode([
    'success'    => true,
    'artikel_id' => $newId,
    'slug'       => $slug,
    'judul'      => $judul,
    'message'    => 'Artikel berhasil disimpan'
]);