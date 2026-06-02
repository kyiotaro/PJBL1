<?php
require_once '../../../config/auth_check.php';
include '../../../koneksi.php';

$totalArtikel = 0;
$totalKategori = 0;
$artikelBulanIni = 0;
$updateTerakhir = '-';
$recentArticles = [];
$categoryBreakdown = [];

$totalArtikelQuery = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM artikel");
if ($totalArtikelQuery && $row = mysqli_fetch_assoc($totalArtikelQuery)) {
    $totalArtikel = (int) $row['total'];
}

$totalKategoriQuery = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM kategori");
if ($totalKategoriQuery && $row = mysqli_fetch_assoc($totalKategoriQuery)) {
    $totalKategori = (int) $row['total'];
}

$artikelBulanIniQuery = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total FROM artikel WHERE MONTH(tanggal) = MONTH(CURDATE()) AND YEAR(tanggal) = YEAR(CURDATE())"
);
if ($artikelBulanIniQuery && $row = mysqli_fetch_assoc($artikelBulanIniQuery)) {
    $artikelBulanIni = (int) $row['total'];
}

$updateTerakhirQuery = mysqli_query($koneksi, "SELECT MAX(tanggal) AS terakhir FROM artikel");
if ($updateTerakhirQuery) {
    $row = mysqli_fetch_assoc($updateTerakhirQuery);

    if (!empty($row['terakhir'])) {
        $updateTerakhir = date('d M Y', strtotime($row['terakhir']));
    }
}

$recentQuery = mysqli_query($koneksi, "
    SELECT a.judul, k.nama AS kategori, a.tanggal
    FROM artikel a
    LEFT JOIN kategori k ON k.id = a.kategori_id
    ORDER BY a.tanggal DESC LIMIT 5
");
if ($recentQuery) {
    while ($row = mysqli_fetch_assoc($recentQuery)) {
        $recentArticles[] = $row;
    }
}

$categoryBreakdownQuery = mysqli_query(
    $koneksi,
    "SELECT k.nama AS kategori, COUNT(a.id) AS total
     FROM kategori k
     LEFT JOIN artikel a ON k.id = a.kategori_id
     GROUP BY k.id
     ORDER BY total DESC, kategori ASC LIMIT 5"
);
if ($categoryBreakdownQuery) {
    while ($row = mysqli_fetch_assoc($categoryBreakdownQuery)) {
        $categoryBreakdown[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Admin</title>

<link rel="stylesheet" href="/PJBL-main/assets/templateHalaman/sidebar/sidebar.css">
<link rel="stylesheet" href="/PJBL-main/assets/templateHalaman/statCard/statCard.css">
<link rel="stylesheet" href="css/dashboard_admin.css">
</head>
<body>

<?php
  $activePage = 'dashboard';
  include '../../../assets/templateHalaman/sidebar/sidebar.php';
?>

<main class="pb-main-content">
    <h1 class="page-title">Dashboard Admin</h1>

    <div class="stats-grid">
      <?php
      $statValue = $totalArtikel;
      $statLabel = 'Total Artikel';
      $statIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>';
      include '../../../assets/templateHalaman/statCard/statCard.php';

      $statValue = $totalKategori;
      $statLabel = 'Total Kategori';
      $statIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>';
      include '../../../assets/templateHalaman/statCard/statCard.php';

      $statValue = $artikelBulanIni;
      $statLabel = 'Artikel Bulan Ini';
      $statIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>';
      include '../../../assets/templateHalaman/statCard/statCard.php';

      $statValue = $updateTerakhir;
      $statLabel = 'Update Terakhir';
      $statIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>';
      include '../../../assets/templateHalaman/statCard/statCard.php';
      ?>
    </div>

    <div class="card">
      <h3>Artikel Terbaru</h3>

      <?php if (!empty($recentArticles)) : ?>
        <?php foreach ($recentArticles as $article) : ?>
          <div class="article-item">
            <div class="article-info">
              <h4><?= htmlspecialchars($article['judul']); ?></h4>
              <p class="meta">
                <?= date('d M Y', strtotime($article['tanggal'])); ?> • <?= htmlspecialchars(ucfirst($article['kategori'])); ?>
              </p>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else : ?>
        <p class="meta">Belum ada artikel yang tersimpan.</p>
      <?php endif; ?>
    </div>

    <div class="card">
      <h3>Ringkasan Kategori</h3>

      <?php if (!empty($categoryBreakdown)) : ?>
        <?php foreach ($categoryBreakdown as $summary) : ?>
          <div class="article-item">
            <div class="article-info">
              <h4><?= htmlspecialchars(ucfirst($summary['kategori'])); ?></h4>
              <p class="meta"><?= (int) $summary['total']; ?> artikel tersedia</p>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else : ?>
        <p class="meta">Data kategori belum tersedia.</p>
      <?php endif; ?>
    </div>
  </main>

<script src="/PJBL-main/halamanWeb/loginpage/js/auth.js"></script>
<script src="js/dashboard.js"></script>
<script src="/PJBL-main/assets/templateHalaman/sidebar/sidebar.js"></script>
</body>
</html>
