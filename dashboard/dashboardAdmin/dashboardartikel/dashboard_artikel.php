<?php
require_once '../../../config/auth_check.php';
include '../../../koneksi.php';

// Handle Actions
if (isset($_GET['approve'])) {
    $id = (int) $_GET['approve'];
    mysqli_query($koneksi, "UPDATE artikel SET status = 'published' WHERE id = $id AND author_type = 'admin'");
    header('Location: dashboard_artikel.php?status=approved');
    exit;
}

if (isset($_GET['reject'])) {
    $id = (int) $_GET['reject'];
    mysqli_query($koneksi, "UPDATE artikel SET status = 'rejected' WHERE id = $id AND author_type = 'admin'");
    header('Location: dashboard_artikel.php?status=rejected');
    exit;
}

if (isset($_GET['allow_edit'])) {
    $id = (int) $_GET['allow_edit'];
    mysqli_query($koneksi, "UPDATE artikel SET status = 'pending' WHERE id = $id AND author_type = 'admin'");
    header('Location: dashboard_artikel.php?status=edit_allowed');
    exit;
}

if (isset($_GET['hapus'])) {
    $id = (int) ($_GET['hapus'] ?? 0);

    if ($id > 0) {
        $stmt = mysqli_prepare($koneksi, "DELETE FROM artikel WHERE id = ? AND author_type = 'admin'");
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
} elseif ($status === 'approved') {
    $statusMessage = 'Artikel telah disetujui.';
} elseif ($status === 'rejected') {
    $statusMessage = 'Artikel telah ditolak.';
} elseif ($status === 'edit_allowed') {
    $statusMessage = 'Izin edit diberikan kepada penulis.';
}

$articles = [];
$categories = [];
$search = trim($_GET['search'] ?? '');
$categoryId = (int) ($_GET['kategori'] ?? 0);
$tanggal = trim($_GET['tanggal'] ?? '');
$filterStatus = $_GET['filter_status'] ?? '';

$categoryQuery = mysqli_query($koneksi, "SELECT id, nama FROM kategori ORDER BY nama ASC");
if ($categoryQuery) {
    while ($row = mysqli_fetch_assoc($categoryQuery)) {
        $categories[] = $row;
    }
}

$whereClauses = ["a.author_type = 'admin'"];
$params = [];
$types = '';

if ($search !== '') {
    $whereClauses[] = "(a.judul LIKE ? OR k.nama LIKE ? OR a.Penulis LIKE ?)";
    $searchParam = '%' . $search . '%';
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= 'sss';
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

if ($filterStatus !== '') {
    $whereClauses[] = 'a.status = ?';
    $params[] = $filterStatus;
    $types .= 's';
}

// Sorting Logic
$allowedSortColumns = [
    'id' => 'a.id',
    'judul' => 'a.judul',
    'kategori' => 'k.nama',
    'tanggal' => 'a.tanggal',
    'status' => 'a.status',
    'penulis' => 'a.Penulis'
];

$sort = $_GET['sort'] ?? 'tanggal';
$order = strtoupper($_GET['order'] ?? 'DESC');

if (!array_key_exists($sort, $allowedSortColumns)) {
    $sort = 'tanggal';
}
if ($order !== 'ASC' && $order !== 'DESC') {
    $order = 'DESC';
}

$orderBy = $allowedSortColumns[$sort];

// Pagination Logic
$limit = 20;
$page = (int) ($_GET['page'] ?? 1);
if ($page < 1) $page = 1;

$countSql = "SELECT COUNT(*) as total FROM artikel a LEFT JOIN kategori k ON k.id = a.kategori_id";
if (!empty($whereClauses)) {
    $countSql .= ' WHERE ' . implode(' AND ', $whereClauses);
}

$stmtCount = mysqli_prepare($koneksi, $countSql);
if ($stmtCount) {
    if ($types !== '') {
        $bindParams = [];
        $bindParams[] = &$types;
        foreach ($params as $key => $value) {
            $bindParams[] = &$params[$key];
        }
        call_user_func_array('mysqli_stmt_bind_param', array_merge([$stmtCount], $bindParams));
    }
    mysqli_stmt_execute($stmtCount);
    $resCount = mysqli_stmt_get_result($stmtCount);
    $rowCount = mysqli_fetch_assoc($resCount);
    $totalItems = $rowCount['total'] ?? 0;
    mysqli_stmt_close($stmtCount);
} else {
    $totalItems = 0;
}

$totalPages = ceil($totalItems / $limit);
if ($page > $totalPages && $totalPages > 0) $page = $totalPages;
$offset = ($page - 1) * $limit;

$sql = "
    SELECT a.id, a.judul, a.tanggal, a.status, a.Penulis, a.author_type, k.nama AS kategori
    FROM artikel a
    LEFT JOIN kategori k ON k.id = a.kategori_id
";
if (!empty($whereClauses)) {
    $sql .= '    WHERE ' . implode(' AND ', $whereClauses) . "\n";
}

$sql .= "    ORDER BY $orderBy $order, a.id DESC\n";
$sql .= "    LIMIT ? OFFSET ?\n";

$stmt = mysqli_prepare($koneksi, $sql);
if ($stmt) {
    $newTypes = $types . 'ii';
    $newParams = $params;
    $newParams[] = $limit;
    $newParams[] = $offset;

    $bindParams = [];
    $bindParams[] = &$newTypes;
    foreach ($newParams as $key => $value) {
        $bindParams[] = &$newParams[$key];
    }
    call_user_func_array('mysqli_stmt_bind_param', array_merge([$stmt], $bindParams));

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $articles[] = $row;
    }
    mysqli_stmt_close($stmt);
}

$baseParams = [
    'search' => $search,
    'kategori' => $categoryId > 0 ? $categoryId : '',
    'tanggal' => $tanggal,
    'filter_status' => $filterStatus,
    'sort' => $sort,
    'order' => $order
];

function getSortUrl($column, $currentSort, $currentOrder, $params) {
    $newOrder = ($column === $currentSort && $currentOrder === 'DESC') ? 'ASC' : 'DESC';
    return '?' . http_build_query(array_merge($params, ['sort' => $column, 'order' => $newOrder, 'page' => 1]));
}

function getSortIcon($column, $currentSort, $currentOrder) {
    if ($column !== $currentSort) return '';
    return $currentOrder === 'ASC' ? ' <span class="sort-icon">▲</span>' : ' <span class="sort-icon">▼</span>';
}

function getStatusBadgeClass($status) {
    switch ($status) {
        case 'published': return 'badge-success';
        case 'pending': return 'badge-warning';
        case 'rejected': return 'badge-danger';
        case 'requested_edit': return 'badge-info';
        default: return 'badge-secondary';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Artikel</title>
    <link rel="stylesheet" href="/PJBL-main/assets/templateHalaman/sidebar/sidebar.css">
    <link rel="stylesheet" href="/PJBL-main/dashboard/dashboardAdmin/dashboardmain/css/dashboard.css">
    <link rel="stylesheet" href="css/dashboard_artikel.css?v=2">
    <style>
        .search-form { grid-template-columns: 1fr 150px 150px 150px auto !important; }
    </style>
</head>
<body>

<?php
    $activePage = 'artikel'; 
    include '../../../assets/templateHalaman/sidebar/sidebar.php';
?>

<main class="pb-main-content">
        <h1>Manajemen Artikel</h1>

        <?php if ($statusMessage !== '') : ?>
            <p style="margin-bottom: 16px; color: #0369A1; font-weight: 600;"><?= htmlspecialchars($statusMessage); ?></p>
        <?php endif; ?>

        <div class="dashboard-actions">
            <a href="tambah_artikel.php" class="add-btn">+ Tambah Artikel</a>
            <form action="dashboard_artikel.php" method="GET" class="search-form">
                <input type="text" name="search" placeholder="Cari judul, kategori, atau penulis..." value="<?= htmlspecialchars($search); ?>">
                <select name="kategori">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($categories as $cat) : ?>
                        <option value="<?= (int) $cat['id']; ?>" <?= $categoryId === (int) $cat['id'] ? 'selected' : ''; ?>><?= htmlspecialchars($cat['nama']); ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="filter_status">
                    <option value="">Semua Status</option>
                    <option value="pending" <?= $filterStatus === 'pending' ? 'selected' : ''; ?>>Menunggu</option>
                    <option value="published" <?= $filterStatus === 'published' ? 'selected' : ''; ?>>Disetujui</option>
                    <option value="rejected" <?= $filterStatus === 'rejected' ? 'selected' : ''; ?>>Ditolak</option>
                    <option value="requested_edit" <?= $filterStatus === 'requested_edit' ? 'selected' : ''; ?>>Minta Edit</option>
                </select>
                <input type="date" name="tanggal" value="<?= htmlspecialchars($tanggal); ?>">
                <button type="submit">Filter</button>
            </form>
        </div>

        <table class="article-table">
            <thead>
                <tr>
                    <th><a href="<?= getSortUrl('id', $sort, $order, $baseParams); ?>">ID<?= getSortIcon('id', $sort, $order); ?></a></th>
                    <th><a href="<?= getSortUrl('judul', $sort, $order, $baseParams); ?>">Judul Artikel<?= getSortIcon('judul', $sort, $order); ?></a></th>
                    <th><a href="<?= getSortUrl('penulis', $sort, $order, $baseParams); ?>">Penulis<?= getSortIcon('penulis', $sort, $order); ?></a></th>
                    <th><a href="<?= getSortUrl('kategori', $sort, $order, $baseParams); ?>">Kategori<?= getSortIcon('kategori', $sort, $order); ?></a></th>
                    <th><a href="<?= getSortUrl('tanggal', $sort, $order, $baseParams); ?>">Tanggal<?= getSortIcon('tanggal', $sort, $order); ?></a></th>
                    <th><a href="<?= getSortUrl('status', $sort, $order, $baseParams); ?>">Status<?= getSortIcon('status', $sort, $order); ?></a></th>
                    <th><span class="no-sort">Aksi</span></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($articles)) : ?>
                    <?php foreach ($articles as $row) : ?>
                        <tr>
                            <td><?= (int) $row['id']; ?></td>
                            <td><?= htmlspecialchars($row['judul']); ?></td>
                            <td><?= htmlspecialchars($row['Penulis']); ?> (<?= ucfirst($row['author_type']); ?>)</td>
                            <td><?= htmlspecialchars(ucfirst($row['kategori'])); ?></td>
                            <td><?= htmlspecialchars($row['tanggal']); ?></td>
                            <td><span class="badge <?= getStatusBadgeClass($row['status']); ?>"><?= ucfirst(str_replace('_', ' ', $row['status'])); ?></span></td>
                            <td class="table-actions">
                                <?php if ($row['status'] === 'pending') : ?>
                                    <a href="?approve=<?= $row['id'] ?>" class="action-btn approve" onclick="return confirm('Setujui artikel ini?')">Setujui</a>
                                    <a href="?reject=<?= $row['id'] ?>" class="action-btn reject" onclick="return confirm('Tolak artikel ini?')">Tolak</a>
                                <?php elseif ($row['status'] === 'requested_edit') : ?>
                                    <a href="?allow_edit=<?= $row['id'] ?>" class="action-btn allow" onclick="return confirm('Izinkan penulis mengedit artikel ini?')">Izinkan Edit</a>
                                <?php endif; ?>
                                
                                <a href="edit_artikel.php?id=<?= (int) $row['id']; ?>" class="action-btn edit">Edit</a>
                                <a href="?hapus=<?= (int) $row['id']; ?>" class="action-btn delete" onclick="return confirm('Yakin hapus artikel ini?')">Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="7">Belum ada artikel yang tersimpan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if ($totalPages > 1) : ?>
            <div class="pagination">
                <?php if ($page > 1) : ?>
                    <a href="?<?= http_build_query(array_merge($baseParams, ['page' => $page - 1])); ?>" class="prev">&laquo; Prev</a>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                    <a href="?<?= http_build_query(array_merge($baseParams, ['page' => $i])); ?>" class="<?= $i === $page ? 'active' : ''; ?>"><?= $i; ?></a>
                <?php endfor; ?>
                <?php if ($page < $totalPages) : ?>
                    <a href="?<?= http_build_query(array_merge($baseParams, ['page' => $page + 1])); ?>" class="next">Next &raquo;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>

<script src="/PJBL-main/halamanWeb/loginpage/js/auth.js"></script>
<script src="/PJBL-main/assets/templateHalaman/sidebar/sidebar.js"></script>

</body>
</html>
