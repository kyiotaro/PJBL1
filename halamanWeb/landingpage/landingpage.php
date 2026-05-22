<?php include '../../koneksi.php'; ?>

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

  <?php
  $heroImage = '../../assets/Foto/ui/background.png';
  $heroTitle = 'Permata Biru Nusantara';
  $heroSubtitle = 'Menghadirkan pesona dan kekayaan laut Indonesia dari sabang sampai merauke. Semua tentang laut, dari biodata, budaya, hingga pelestarian, terangkum di sini.';
  include '../../assets/templateHalaman/hero/hero.php';
  ?>

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
          ORDER BY a.tanggal DESC LIMIT 6
        ");
        while ($artikel = mysqli_fetch_assoc($query)) {
          include '../../assets/templateHalaman/cardVariant/card1/card1.php';
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
          ORDER BY a.tanggal DESC LIMIT 6 OFFSET 6
        ");
        while ($artikel = mysqli_fetch_assoc($query2)) {
          include '../../assets/templateHalaman/cardVariant/card1/card1.php';
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
          ORDER BY a.tanggal DESC LIMIT 12 OFFSET 12
        ");
        while ($artikel = mysqli_fetch_assoc($query3)) {
          include '../../assets/templateHalaman/cardVariant/card1/card1.php';
        }
        ?>
      </div>
    </section>
  </main>

  <?php include '../../assets/templateHalaman/footer/footer.php'; ?>

  <script src="js/landingpage.js"></script>
</body>

</html>