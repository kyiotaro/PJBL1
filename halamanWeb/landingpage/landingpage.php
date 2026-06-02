<?php include '../../koneksi.php'; ?>
<?php
$artikelHasStatus = false;
$artikelHasTanggal = false;
$statusCheck = mysqli_query($koneksi, "SHOW COLUMNS FROM artikel LIKE 'status'");
if ($statusCheck && mysqli_num_rows($statusCheck) > 0) {
  $artikelHasStatus = true;
}
$tanggalCheck = mysqli_query($koneksi, "SHOW COLUMNS FROM artikel LIKE 'tanggal'");
if ($tanggalCheck && mysqli_num_rows($tanggalCheck) > 0) {
  $artikelHasTanggal = true;
}

$publishedFilter = $artikelHasStatus ? "WHERE a.status = 'published'" : "";
$orderBy = $artikelHasTanggal ? "ORDER BY a.tanggal DESC, a.id DESC" : "ORDER BY a.id DESC";
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Permata Biru Nusantara - Portal Laut Indonesia</title>
  <link rel="stylesheet" href="css/landingpage.css">
  <link rel="stylesheet" href="../../assets/templateHalaman/navbar/navbar.css">
  <link rel="stylesheet" href="../../assets/templateHalaman/footer/footer.css">
  <link rel="stylesheet" href="../../assets/templateHalaman/cardVariant/card1/card1.css">
  <link rel="stylesheet" href="../../assets/templateHalaman/hero/hero.css">
  <link rel="stylesheet" href="../../assets/templateHalaman/sectionHeader/sectionHeader.css">
</head>

<body>
  <?php include '../../assets/templateHalaman/navbar/navbar.php'; ?>

  <section class="hero" style="background-image: url('../../assets/Foto/ui/background.png')">
    <div class="overlay">
      <h2>Permata Biru Nusantara</h2>
      <p>
        Permata Biru Nusantara menghadirkan pesona dan kekayaan laut Indonesia dari
        sabang sampai merauke.
        Semua tentang laut, dari biodata, budaya, hingga pelestarian, terangkum di sini.
      </p>
    </div>
  </section>

  <main>
    <div class="kategori">
      <button class="filter-btn aktif" data-filter="all">Semua</button>
      <?php
      $qFilter = mysqli_query($koneksi, "SELECT nama, slug FROM kategori ORDER BY nama ASC");
      while ($f = mysqli_fetch_assoc($qFilter)):
      ?>
        <button class="filter-btn" data-filter="<?= htmlspecialchars($f['slug']); ?>">
          <?= htmlspecialchars($f['nama']); ?>
        </button>
      <?php endwhile; ?>
    </div>

    <section class="terpopuler" id="terpopuler">
      <?php 
      $sectionTitle = 'Terpopuler';
      include '../../assets/templateHalaman/sectionHeader/sectionHeader.php';
      ?>
      <div class="grid">
        <?php
        $query = mysqli_query($koneksi, "
          SELECT a.*, k.slug AS kategori 
          FROM artikel a 
          LEFT JOIN kategori k ON k.id = a.kategori_id 
          $publishedFilter
          $orderBy LIMIT 6
        ");
        if ($query) {
          while ($artikel = mysqli_fetch_assoc($query)) {
            include '../../assets/templateHalaman/cardVariant/card1/card1.php';
          }
        }
        ?>
      </div>
    </section>

    <!-- ARTIKEL TERBARU -->
    <section class="artikel-terbaru">
      <?php 
      $sectionTitle = 'Artikel Terbaru';
      include '../../assets/templateHalaman/sectionHeader/sectionHeader.php';
      ?>
      <div class="grid">
        <?php
        $query2 = mysqli_query($koneksi, "
          SELECT a.*, k.slug AS kategori 
          FROM artikel a 
          LEFT JOIN kategori k ON k.id = a.kategori_id 
          $publishedFilter
          $orderBy LIMIT 6 OFFSET 6
        ");
        if ($query2) {
          while ($artikel = mysqli_fetch_assoc($query2)) {
            include '../../assets/templateHalaman/cardVariant/card1/card1.php';
          }
        }
        ?>
      </div>
    </section>

    <!-- ARTIKEL LAINNYA -->
    <section class="artikel-lainnya">
      <?php 
      $sectionTitle = 'Artikel Lainnya';
      include '../../assets/templateHalaman/sectionHeader/sectionHeader.php';
      ?>
      <div class="grid">
        <?php
        $query3 = mysqli_query($koneksi, "
          SELECT a.*, k.slug AS kategori 
          FROM artikel a 
          LEFT JOIN kategori k ON k.id = a.kategori_id 
          $publishedFilter
          $orderBy LIMIT 12 OFFSET 12
        ");
        if ($query3) {
          while ($artikel = mysqli_fetch_assoc($query3)) {
            include '../../assets/templateHalaman/cardVariant/card1/card1.php';
          }
        }
        ?>
      </div>
    </section>
  </main>

  <?php include '../../assets/templateHalaman/footer/footer.php'; ?>

  <script src="js/landingpage.js"></script>
</body>

</html>