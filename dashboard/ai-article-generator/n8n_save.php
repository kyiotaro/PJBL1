<?php
// Matikan semua error output agar tidak merusak JSON response
error_reporting(0);
ini_set('display_errors', 0);
ob_start(); // Buffer semua output
/**
 * n8n_save.php
 * Endpoint yang dipanggil n8n untuk menyimpan artikel hasil generate ke database.
 * Letakkan di: dashboard/ai-article-generator/n8n_save.php
 */

require_once '../../koneksi.php';
require_once '../../config/api_config.php';

header('Content-Type: application/json');

// ── Hanya terima POST ─────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// ── Verifikasi secret key (didefinisikan di api_config.php) ───────────────────
// Tambahkan baris ini di api_config.php:  define('N8N_SECRET', 'isi-secret-kamu');
$authHeader = $_SERVER['HTTP_X_N8N_SECRET'] ?? '';
if (!defined('N8N_SECRET') || $authHeader !== N8N_SECRET) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// ── Parse body JSON dari n8n ──────────────────────────────────────────────────
$body = file_get_contents('php://input');
$data = json_decode($body, true);

// Kalau json_decode gagal, coba decode lagi (double encoded)
if (!$data) {
    $data = json_decode(json_decode($body), true);
}

// Kalau masih null, coba dari $_POST
if (!$data) {
    $data = $_POST;
}

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON body']);
    exit;
}

// ── Ambil & validasi field ────────────────────────────────────────────────────
$judul        = trim($data['judul']        ?? '');
$slugKategori = trim($data['kategori']     ?? '');
$isi          = trim($data['isi']          ?? '');
$gambar       = trim($data['gambar']       ?? '');
$slug         = trim($data['slug']         ?? '');
$tanggal      = trim($data['tanggal']      ?? date('Y-m-d'));
$topik_id     = isset($data['topik_id']) ? (int)$data['topik_id'] : null;

if ($judul === '' || $slugKategori === '' || $isi === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Field judul, kategori, dan isi wajib diisi']);
    exit;
}

// ── Generate slug jika kosong ─────────────────────────────────────────────────
if ($slug === '') {
    $slug = strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $judul));
    $slug = trim($slug, '-');
}

// Pastikan slug unik: tambah timestamp kalau sudah ada
$cekSlug = mysqli_prepare($koneksi, "SELECT id FROM Artikel WHERE slug = ? LIMIT 1");
mysqli_stmt_bind_param($cekSlug, 's', $slug);
mysqli_stmt_execute($cekSlug);
mysqli_stmt_store_result($cekSlug);
if (mysqli_stmt_num_rows($cekSlug) > 0) {
    $slug = $slug . '-' . time();
}
mysqli_stmt_close($cekSlug);

// ── Cari kategori_id dari slug kategori ──────────────────────────────────────
$stmtKat = mysqli_prepare($koneksi, "SELECT id FROM kategori WHERE slug = ? LIMIT 1");
mysqli_stmt_bind_param($stmtKat, 's', $slugKategori);
mysqli_stmt_execute($stmtKat);
$resKat   = mysqli_stmt_get_result($stmtKat);
$rowKat   = mysqli_fetch_assoc($resKat);
$kategoriId = $rowKat['id'] ?? null;
mysqli_stmt_close($stmtKat);

if (!$kategoriId) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => "Kategori '$slugKategori' tidak ditemukan di database"]);
    exit;
}

// ── Insert artikel ────────────────────────────────────────────────────────────
$stmt = mysqli_prepare(
    $koneksi,
    "INSERT INTO Artikel (judul, kategori_id, tanggal, gambar, isi, slug) VALUES (?, ?, ?, ?, ?, ?)"
);
mysqli_stmt_bind_param($stmt, 'sissss', $judul, $kategoriId, $tanggal, $gambar, $isi, $slug);

if (!mysqli_stmt_execute($stmt)) {
    $errMsg = mysqli_stmt_error($stmt);
    mysqli_stmt_close($stmt);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'DB error: ' . $errMsg]);
    exit;
}

$newId = mysqli_insert_id($koneksi);
mysqli_stmt_close($stmt);

// ── Tandai topik sudah dipakai (jika topik_id dikirim) ───────────────────────
if ($topik_id !== null) {
    $stmtUsed = mysqli_prepare($koneksi, "UPDATE n8n_topic_pool SET used = 1, used_at = NOW() WHERE id = ?");
    if ($stmtUsed) {
        mysqli_stmt_bind_param($stmtUsed, 'i', $topik_id);
        mysqli_stmt_execute($stmtUsed);
        mysqli_stmt_close($stmtUsed);
    }
}

ob_clean(); // Buang semua output sebelumnya yang mungkin merusak JSON

echo json_encode([
    'success'    => true,
    'artikel_id' => $newId,
    'slug'       => $slug,
    'judul'      => $judul,
    'message'    => 'Artikel berhasil disimpan'
]);
