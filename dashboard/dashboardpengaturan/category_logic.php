<?php
session_start();
require_once '../../koneksi.php';

header('Content-Type: application/json');

if (empty($_SESSION['admin_logged_in'])) {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
    exit;
}

$action = $_REQUEST['action'] ?? '';

if ($action === 'list') {
    $res = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY nama ASC");
    if (!$res) {
        echo json_encode(['success' => false, 'message' => 'Gagal mengambil data kategori: ' . mysqli_error($koneksi)]);
        exit;
    }
    $categories = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $categories[] = $row;
    }
    echo json_encode(['success' => true, 'categories' => $categories]);
}

elseif ($action === 'add') {
    $nama = trim($_POST['nama'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $warna = trim($_POST['warna'] ?? '#000000');

    $stmt = mysqli_prepare($koneksi, "INSERT INTO kategori (nama, slug, warna) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'sss', $nama, $slug, $warna);
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Kategori berhasil ditambahkan.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menambah kategori: ' . mysqli_error($koneksi)]);
    }
    mysqli_stmt_close($stmt);
}

elseif ($action === 'update') {
    $id = $_POST['id'] ?? '';
    $nama = trim($_POST['nama'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $warna = trim($_POST['warna'] ?? '#000000');

    $stmt = mysqli_prepare($koneksi, "UPDATE kategori SET nama = ?, slug = ?, warna = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'sssi', $nama, $slug, $warna, $id);
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Kategori berhasil diperbarui.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal memperbarui kategori: ' . mysqli_error($koneksi)]);
    }
    mysqli_stmt_close($stmt);
}

elseif ($action === 'delete') {
    $id = $_POST['id'] ?? '';
    $stmt = mysqli_prepare($koneksi, "DELETE FROM kategori WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Kategori berhasil dihapus.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menghapus kategori. Pastikan tidak ada artikel yang menggunakan kategori ini.']);
    }
    mysqli_stmt_close($stmt);
}
?>