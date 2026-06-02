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

// Sorting Logic
$allowedSortColumns = [
    'id' => 'a.id',
    'judul' => 'a.judul',
    'kategori' => 'k.nama',
    'tanggal' => 'a.tanggal'
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

// Count total items for pagination
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
    SELECT a.id, a.judul, a.tanggal, k.nama AS kategori
    FROM artikel a
    LEFT JOIN kategori k ON k.id = a.kategori_id
";
if (!empty($whereClauses)) {
    $sql .= '    WHERE ' . implode(' AND ', $whereClauses) . "\n";
}

// Special case for ID sorting to keep it consistent
if ($sort === 'id') {
    $sql .= "    ORDER BY $orderBy $order\n";
} else {
    $sql .= "    ORDER BY $orderBy $order, a.id DESC\n";
}

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

// Base parameters for pagination and sorting links
$baseParams = [
    'search' => $search,
    'kategori' => $categoryId > 0 ? $categoryId : '',
    'tanggal' => $tanggal,
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
                    <th><a href="<?= getSortUrl('id', $sort, $order, $baseParams); ?>">ID<?= getSortIcon('id', $sort, $order); ?></a></th>
                    <th><a href="<?= getSortUrl('judul', $sort, $order, $baseParams); ?>">Judul Artikel<?= getSortIcon('judul', $sort, $order); ?></a></th>
                    <th><a href="<?= getSortUrl('kategori', $sort, $order, $baseParams); ?>">Kategori<?= getSortIcon('kategori', $sort, $order); ?></a></th>
                    <th><a href="<?= getSortUrl('tanggal', $sort, $order, $baseParams); ?>">Tanggal<?= getSortIcon('tanggal', $sort, $order); ?></a></th>
                    <th><span class="no-sort">Aksi</span></th>
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

        <?php if ($totalPages > 1) : ?>
            <div class="pagination">
                <?php if ($page > 1) : ?>
                    <a href="?<?= http_build_query(array_merge($baseParams, ['page' => $page - 1])); ?>" class="prev">&laquo; Prev</a>
                <?php endif; ?>

                <?php
                $start = max(1, $page - 2);
                $end = min($totalPages, $page + 2);

                if ($start > 1) {
                    echo '<a href="?' . http_build_query(array_merge($baseParams, ['page' => 1])) . '">1</a>';
                    if ($start > 2) echo '<span class="pagination-dots">...</span>';
                }

                for ($i = $start; $i <= $end; $i++) : ?>
                    <a href="?<?= http_build_query(array_merge($baseParams, ['page' => $i])); ?>" class="<?= $i === $page ? 'active' : ''; ?>"><?= $i; ?></a>
                <?php endfor; ?>

                <?php
                if ($end < $totalPages) {
                    if ($end < $totalPages - 1) echo '<span class="pagination-dots">...</span>';
                    echo '<a href="?' . http_build_query(array_merge($baseParams, ['page' => $totalPages])) . '">' . $totalPages . '</a>';
                }
                ?>

                <?php if ($page < $totalPages) : ?>
                    <a href="?<?= http_build_query(array_merge($baseParams, ['page' => $page + 1])); ?>" class="next">Next &raquo;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>

<script src="/PJBL-main/halamanWeb/loginpage/js/auth.js"></script>
<script src="js/dashboard_artikel.js"></script>
<script src="../../assets/templateHalaman/sidebar/sidebar.js"></script>

</body>
</html>