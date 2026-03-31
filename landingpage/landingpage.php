<?php include '../koneksi.php'; ?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Permata Biru Nusantara - Portal Laut Indonesia</title>
  <link rel="stylesheet" href="landingpage.css">
  <link rel="stylesheet" href="../assets/templateHalaman/navbar.css">
  <link rel="stylesheet" href="../assets/templateHalaman/footer.css">
  <link rel="stylesheet" href="../assets/templateHalaman/card.css">
</head>
<body>
  <?php include '../assets/templateHalaman/navbar.php'; ?>

  <section class="hero" style="background-image: url('../assets/Foto/background.png')">
    <div class="overlay">
      <h2>Permata Biru Nusantara</h2>
      <p>
        <span class="highlight">Permata Biru Nusantara</span> menghadirkan pesona dan kekayaan laut Indonesia dari sabang sampai merauke.
        Semua tentang laut, dari biodata, budaya, hingga pelestarian, terangkum di sini.
      </p>
    </div>
  </section>

  <main>
    <div class="kategori">
      <button class="filter-btn active" data-filter="all">Semua</button>
      <button class="filter-btn" data-filter="biota">Biota</button>
      <button class="filter-btn" data-filter="wisata">Wisata</button>
      <button class="filter-btn" data-filter="konservasi">Konservasi</button>
      <button class="filter-btn" data-filter="geologi">Geologi</button>
    </div>

    <!-- TERPOPULER (3 artikel pertama) -->
    <section class="terpopuler" id="terpopuler">
      <h3>Terpopuler</h3>
      <div class="grid">
        <?php
          $query = mysqli_query($koneksi, "SELECT * FROM Artikel ORDER BY tanggal DESC LIMIT 3");
          while ($artikel = mysqli_fetch_assoc($query)) {
            include '../assets/templateHalaman/card.php';
          }
        ?>
      </div>
    </section>

    <!-- ARTIKEL TERBARU -->
    <section class="artikel-terbaru">
      <h3>Artikel Terbaru</h3>
      <div class="grid">
        <?php
          $query2 = mysqli_query($koneksi, "SELECT * FROM Artikel ORDER BY tanggal DESC LIMIT 3 OFFSET 3");
          while ($artikel = mysqli_fetch_assoc($query2)) {
            include '../assets/templateHalaman/card.php';
          }
        ?>
      </div>

      <div class="tombol-tengah">
        <button class="lihat-lebih">Tampilkan lebih banyak ▼</button>
      </div>

      <div class="grid tambahan">
        <?php
          $query3 = mysqli_query($koneksi, "SELECT * FROM Artikel ORDER BY tanggal DESC LIMIT 6 OFFSET 6");
          while ($artikel = mysqli_fetch_assoc($query3)) {
            include '../assets/templateHalaman/card.php';
          }
        ?>
      </div>
    </section>
  </main>

  <?php include '../assets/templateHalaman/footer.php'; ?>
</body>
</html>