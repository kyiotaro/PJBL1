<?php
require_once '../../config/auth_check.php';
include '../../koneksi.php';

if (isset($_GET['hapus'])) {
    $id = (int) ($_GET['hapus'] ?? 0);

    if ($id > 0) {
        $stmt = mysqli_prepare($koneksi, "DELETE FROM artikel WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    header('Location: dashboard_artikel.php?status=deleted');
    exit;
}

$statusMessage = '';
$status = $_GET['status'] ?? '';

if ($status === 'created') {
    $statusMessage = 'Artikel berhasil ditambahkan.';
} elseif ($status === 'updated') {
    $statusMessage = 'Artikel berhasil diperbarui.';
} elseif ($status === 'deleted') {
    $statusMessage = 'Artikel berhasil dihapus.';
}

$articles = [];
$categories = [];
$search = trim($_GET['search'] ?? '');
$categoryId = (int) ($_GET['kategori'] ?? 0);
$tanggal = trim($_GET['tanggal'] ?? '');

$categoryQuery = mysqli_query($koneksi, "SELECT id, nama FROM kategori ORDER BY nama ASC");
if ($categoryQuery) {
    while ($row = mysqli_fetch_assoc($categoryQuery)) {
        $categories[] = $row;
    }
}

$whereClauses = [];
$params = [];
$types = '';

if ($search !== '') {
    $whereClauses[] = "(a.judul LIKE ? OR k.nama LIKE ?)";
    $searchParam = '%' . $search . '%';
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= 'ss';
}

if ($categoryId > 0) {
    $whereClauses[] = 'a.kategori_id = ?';
    $params[] = $categoryId;
    $types .= 'i';
}

if ($tanggal !== '') {
    $whereClauses[] = 'a.tanggal = ?';
    $params[] = $tanggal;
    $types .= 's';
}

$sql = "
    SELECT a.id, a.judul, a.tanggal, k.nama AS kategori
    FROM artikel a
    LEFT JOIN kategori k ON k.id = a.kategori_id
";
if (!empty($whereClauses)) {
    $sql .= '    WHERE ' . implode(' AND ', $whereClauses) . "\n";
}
$sql .= "    ORDER BY a.tanggal DESC, a.id DESC\n";

$stmt = mysqli_prepare($koneksi, $sql);
if ($stmt) {
    if ($types !== '') {
        $bindParams = [];
        $bindParams[] = &$types;
        foreach ($params as $key => $value) {
            $bindParams[] = &$params[$key];
        }
        call_user_func_array('mysqli_stmt_bind_param', array_merge([$stmt], $bindParams));
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $articles[] = $row;
    }
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Artikel</title>
    <link rel="stylesheet" href="../../assets/templateHalaman/sidebar/sidebar.css">
    <link rel="stylesheet" href="../dashboardadmin/css/dashboard.css">
    <link rel="stylesheet" href="css/dashboard_artikel.css">
</head>
<body>

<?php
    $activePage = 'artikel'; 
    include '../../assets/templateHalaman/sidebar/sidebar.php';
?>

<main class="pb-main-content">
        <h1>Manajemen Artikel</h1>

        <?php if ($statusMessage !== '') : ?>
            <p style="margin-bottom: 16px; color: #0369A1; font-weight: 600;"><?= htmlspecialchars($statusMessage); ?></p>
        <?php endif; ?>

        <div class="dashboard-actions">
            <a href="tambah_artikel.php" class="add-btn">+ Tambah Artikel</a>
            <form action="dashboard_artikel.php" method="GET" class="search-form">
                <input type="text" name="search" placeholder="Cari artikel..." value="<?= htmlspecialchars($search); ?>">
                <select name="kategori">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($categories as $cat) : ?>
                        <option value="<?= (int) $cat['id']; ?>" <?= $categoryId === (int) $cat['id'] ? 'selected' : ''; ?>><?= htmlspecialchars($cat['nama']); ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="date" name="tanggal" value="<?= htmlspecialchars($tanggal); ?>">
                <button type="submit">Filter</button>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Judul Artikel</th>
                    <th>Kategori</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($articles)) : ?>
                    <?php foreach ($articles as $row) : ?>
                        <tr>
                            <td><?= (int) $row['id']; ?></td>
                            <td><?= htmlspecialchars($row['judul']); ?></td>
                            <td><?= htmlspecialchars(ucfirst($row['kategori'])); ?></td>
                            <td><?= htmlspecialchars($row['tanggal']); ?></td>
                            <td>
                                <a href="edit_artikel.php?id=<?= (int) $row['id']; ?>" class="action-btn edit">Edit</a>
                                <a href="?hapus=<?= (int) $row['id']; ?>" class="action-btn delete" onclick="return confirm('Yakin hapus artikel ini?')">Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="5">Belum ada artikel yang tersimpan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>

<script src="/PJBL-main/halamanWeb/loginpage/js/auth.js"></script>
<script src="js/dashboard_artikel.js"></script>
<script src="../../assets/templateHalaman/sidebar/sidebar.js"></script>

</body>
</html>