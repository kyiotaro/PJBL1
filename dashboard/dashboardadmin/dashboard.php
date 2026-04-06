<?php
include '../../koneksi.php';

$totalArtikel = 0;
$totalKategori = 0;
$artikelBulanIni = 0;
$updateTerakhir = '-';
$recentArticles = [];
$categoryBreakdown = [];

$totalArtikelQuery = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM Artikel");
if ($totalArtikelQuery && $row = mysqli_fetch_assoc($totalArtikelQuery)) {
    $totalArtikel = (int) $row['total'];
}

$totalKategoriQuery = mysqli_query($koneksi, "SELECT COUNT(DISTINCT kategori) AS total FROM Artikel");
if ($totalKategoriQuery && $row = mysqli_fetch_assoc($totalKategoriQuery)) {
    $totalKategori = (int) $row['total'];
}

$artikelBulanIniQuery = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total FROM Artikel WHERE MONTH(tanggal) = MONTH(CURDATE()) AND YEAR(tanggal) = YEAR(CURDATE())"
);
if ($artikelBulanIniQuery && $row = mysqli_fetch_assoc($artikelBulanIniQuery)) {
    $artikelBulanIni = (int) $row['total'];
}

$updateTerakhirQuery = mysqli_query($koneksi, "SELECT MAX(tanggal) AS terakhir FROM Artikel");
if ($updateTerakhirQuery) {
    $row = mysqli_fetch_assoc($updateTerakhirQuery);

    if (!empty($row['terakhir'])) {
        $updateTerakhir = date('d M Y', strtotime($row['terakhir']));
    }
}

$recentQuery = mysqli_query($koneksi, "SELECT judul, kategori, tanggal FROM Artikel ORDER BY tanggal DESC LIMIT 5");
if ($recentQuery) {
    while ($row = mysqli_fetch_assoc($recentQuery)) {
        $recentArticles[] = $row;
    }
}

$categoryBreakdownQuery = mysqli_query(
    $koneksi,
    "SELECT kategori, COUNT(*) AS total FROM Artikel GROUP BY kategori ORDER BY total DESC, kategori ASC LIMIT 5"
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

<link rel="stylesheet" href="dashboard.css">
<link rel="stylesheet" href="dashboard_admin.css">
</head>
<body>

<div class="user-info">
  <span id="adminEmail"></span>
  <button id="logoutBtn" class="logout-btn">Logout</button>
</div>

<div class="dashboard">
  <aside class="sidebar">
    <div class="logo">
      <img src="/PJBL-main/assets/Foto/brand/logo.png" alt="Logo">
      <h1>Permata Biru Nusantara</h1>
    </div>

    <ul class="nav-menu">
      <li><a href="/PJBL-main/dashboard/dashboardadmin/dashboard.php" class="nav-link active">Dashboard</a></li>
      <li><a href="/PJBL-main/dashboard/dashboardartikel/dashboard_artikel.php" class="nav-link">Artikel</a></li>
      <li><a href="/PJBL-main/dashboard/dashboardpengaturan/dashboard_pengaturan.php" class="nav-link">Pengaturan</a></li>
    </ul>
  </aside>

  <main class="main-content">
    <h1 class="page-title">Dashboard Admin</h1>

    <div class="stats-grid">
      <div class="stat-card">
        <h2><?= $totalArtikel; ?></h2>
        <p>Total Artikel</p>
      </div>
      <div class="stat-card">
        <h2><?= $totalKategori; ?></h2>
        <p>Total Kategori</p>
      </div>
      <div class="stat-card">
        <h2><?= $artikelBulanIni; ?></h2>
        <p>Artikel Bulan Ini</p>
      </div>
      <div class="stat-card">
        <h2 style="font-size: 20px;"><?= htmlspecialchars($updateTerakhir); ?></h2>
        <p>Update Terakhir</p>
      </div>
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
</div>

<script src="/PJBL-main/halamanWeb/loginpage/auth.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  if (!protectAdminPage()) {
    return;
  }

  setupAdminUI();
});
</script>
</body>
</html>urn;
  }

  setupAdminUI();
});
</script>
</body>
</html>