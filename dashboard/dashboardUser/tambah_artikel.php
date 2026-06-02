<?php
session_start();
require_once '../../config/auth_check_user.php';
include '../../koneksi.php';

$userId = $_SESSION['user_id'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul'] ?? '');
    $kategori = trim($_POST['kategori'] ?? '');
    $tanggal = $_POST['tanggal'] ?? '';
    $isi = trim($_POST['isi'] ?? '');
    $slug = trim(strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $judul)), '-');

    if ($slug === '') {
        $slug = 'artikel-' . time();
    }

    $cekSlug = mysqli_prepare($koneksi, "SELECT id FROM artikel WHERE slug = ? LIMIT 1");
    if ($cekSlug) {
        mysqli_stmt_bind_param($cekSlug, 's', $slug);
        mysqli_stmt_execute($cekSlug);
        mysqli_stmt_store_result($cekSlug);
        if (mysqli_stmt_num_rows($cekSlug) > 0) {
            $slug .= '-' . time();
        }
        mysqli_stmt_close($cekSlug);
    }

    $slugKategori = trim($_POST['kategori'] ?? '');
    $stmtKat = mysqli_prepare($koneksi, "SELECT id FROM kategori WHERE slug = ?");
    mysqli_stmt_bind_param($stmtKat, 's', $slugKategori);
    mysqli_stmt_execute($stmtKat);

    if (function_exists('mysqli_stmt_get_result')) {
        $resKat = mysqli_stmt_get_result($stmtKat);
        $rowKat = mysqli_fetch_assoc($resKat);
        $kategoriId = $rowKat['id'] ?? null;
    } else {
        mysqli_stmt_bind_result($stmtKat, $kategoriId);
        mysqli_stmt_fetch($stmtKat);
    }

    mysqli_stmt_close($stmtKat);

    if ($judul === '' || $slugKategori === '' || $tanggal === '' || $isi === '') {
        $error = 'Semua field wajib diisi.';
    } elseif (!$kategoriId) {
        $error = 'Kategori tidak valid.';
    } elseif (!isset($_FILES['gambar']) || $_FILES['gambar']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Silakan upload gambar artikel.';
    } else {
        $uploadDir = '../../assets/Foto/artikel/' . $slugKategori . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $originalName = basename($_FILES['gambar']['name']);
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($extension, $allowedExtensions, true)) {
            $error = 'Format gambar harus JPG, JPEG, PNG, GIF, atau WEBP.';
        } else {
            $baseFileName = $slug !== '' ? $slug : 'artikel-' . time();
            $gambar = $baseFileName . '-' . date('YmdHis') . '.' . $extension;
            $gambarPath = 'artikel/' . $slugKategori . '/' . $gambar;
            $uploadPath = $uploadDir . $gambar;

            if (move_uploaded_file($_FILES['gambar']['tmp_name'], $uploadPath)) {
                $stmt = mysqli_prepare(
                    $koneksi,
                    "INSERT INTO artikel (judul, kategori_id, tanggal, gambar, isi, slug, status, author_id, author_type, Penulis) VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, 'user', ?)"
                );
                $penulis = $_SESSION['user_nama'] ?? 'Kontributor'; // Assuming user_nama is in session, if not we'll use a placeholder
                if (empty($_SESSION['user_nama'])) {
                    // Fetch name if not in session
                    $qUser = mysqli_query($koneksi, "SELECT nama FROM users WHERE id = $userId");
                    $uData = mysqli_fetch_assoc($qUser);
                    $penulis = $uData['nama'] ?? 'Kontributor';
                }
                
                mysqli_stmt_bind_param($stmt, 'sissssis', $judul, $kategoriId, $tanggal, $gambarPath, $isi, $slug, $userId, $penulis);

                if (mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_close($stmt);
                    header('Location: dashboard_artikel.php?status=created');
                    exit;
                }
                mysqli_stmt_close($stmt);
                if (mysqli_errno($koneksi) === 1062) {
                    $error = 'Judul artikel sudah dipakai di sistem. Gunakan judul yang sedikit berbeda.';
                } else {
                    $error = 'Gagal menyimpan artikel. Coba lagi atau hubungi admin.';
                }
            } else {
                $error = 'Gagal upload gambar.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Artikel</title>
    <link rel="stylesheet" href="/PJBL-main/assets/templateHalaman/sidebar/sidebar.css">
    <link rel="stylesheet" href="/PJBL-main/dashboard/dashboardAdmin/dashboardmain/css/dashboard.css">
    <link rel="stylesheet" href="/PJBL-main/dashboard/dashboardAdmin/dashboardartikel/css/tambah_artikel.css">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
</head>
<body class="page-tambah-artikel">

<?php
    $activePage = 'artikel'; 
    include '../../assets/templateHalaman/sidebar/sidebar.php';
?>

<main class="pb-main-content">
        <h1>Tambah Artikel Baru</h1>

        <?php if ($error !== ''): ?>
            <p style="color: #dc2626; margin-bottom: 16px;"><?= htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form class="form-box" id="articleForm" action="" method="POST" enctype="multipart/form-data">
            <label for="judul">Judul Artikel</label>
            <input type="text" id="judul" name="judul" placeholder="Masukkan judul..." value="<?= htmlspecialchars($_POST['judul'] ?? ''); ?>" required>

            <label for="kategori">Kategori</label>
            <select id="kategori" name="kategori" required>
                <option value="">Pilih kategori artikel</option>
                <?php
                $qKat = mysqli_query($koneksi, "SELECT nama, slug FROM kategori ORDER BY nama ASC");
                while ($kat = mysqli_fetch_assoc($qKat)):
                    $selected = (($_POST['kategori'] ?? '') === $kat['slug']) ? 'selected' : '';
                    ?>
                    <option value="<?= htmlspecialchars($kat['slug']); ?>" <?= $selected; ?>>
                        <?= htmlspecialchars($kat['nama']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="tanggal">Tanggal</label>
            <input type="date" id="tanggal" name="tanggal" value="<?= htmlspecialchars($_POST['tanggal'] ?? date('Y-m-d')); ?>" required>

            <label for="gambar">Gambar Artikel</label>
            <div class="file-upload-field">
                <label for="gambar" class="file-upload-label">Pilih Gambar</label>
                <span id="fileName" class="file-upload-name">Belum ada file dipilih</span>
                <input type="file" id="gambar" name="gambar" accept="image/*" required>
            </div>

            <label>Isi Artikel</label>
            <div id="editor" style="height: 300px; margin-bottom: 16px; border-radius: 8px;"></div>
            <input type="hidden" name="isi" id="isi" value="<?= htmlspecialchars($_POST['isi'] ?? ''); ?>">

            <button type="submit" class="submit-btn">Ajukan Artikel</button>
        </form>
    </main>

<script src="/PJBL-main/halamanWeb/loginpage/js/auth.js"></script>
<script src="/PJBL-main/assets/templateHalaman/sidebar/sidebar.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var quill = new Quill('#editor', {
            theme: 'snow',
            placeholder: 'Tulis isi artikel di sini...'
        });

        var isiInput = document.getElementById('isi');
        var fileInput = document.getElementById('gambar');
        var fileName = document.getElementById('fileName');

        if (isiInput.value) {
            quill.root.innerHTML = isiInput.value;
        }

        fileInput.addEventListener('change', function() {
            fileName.textContent = fileInput.files.length > 0 ? fileInput.files[0].name : 'Belum ada file dipilih';
        });

        document.getElementById('articleForm').addEventListener('submit', function(event) {
            var plainText = quill.getText().trim();
            if (!plainText) {
                event.preventDefault();
                alert('Isi artikel tidak boleh kosong.');
                return;
            }
            isiInput.value = quill.root.innerHTML;
        });
    });
</script>

</body>
</html>
