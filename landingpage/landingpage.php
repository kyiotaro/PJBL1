<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Permata Biru Nusantara - Portal Laut Indonesia</title>
  <link rel="stylesheet" href="landingpage.css">
  <link rel="stylesheet" href="../assets/templateHalaman/navbar.css">
  <link rel="stylesheet" href="../assets/templateHalaman/footer.css">
</head>
<body>
  <!-- Include navbar -->
  <?php include '../assets/templateHalaman/navbar.php'; ?>


  <section class="hero" style="background-image: url('../assets/background.png')">
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

    <section class="terpopuler" id="terpopuler">
      <h3>Terpopuler</h3>
      <div class="grid">
        <article data-category="biota">
          <img src="../assets/kima.png" alt="Kima Raksasa">
          <h4>Kima Raksasa: Permata Laut yang Terancam</h4>
          <p class="date">16.02.2025</p>
          <a href="../artikelTemplate/artikel.php">Baca selengkapnya →</a>
        </article>
        <article data-category="wisata">
          <img src="../assets/teluk-tomini.png" alt="Teluk Tomini">
          <h4>Teluk Tomini, Permata Laut di Jantung Sulawesi</h4>
          <p class="date">03.06.2025</p>
          <a href="../artikelTemplate/artikel.php">Baca selengkapnya →</a>
        </article>
        <article data-category="biota">
          <img src="../assets/penyubelimbing.png" alt="Penyu Belimbing">
          <h4>Kenapa Penyu Belimbing Disebut Belimbing? Ini Alasannya</h4>
          <p class="date">16.11.2025</p>
          <a href="../artikelTemplate/artikel.php">Baca selengkapnya →</a>
        </article>
      </div>
    </section>

    <section class="artikel-terbaru">
      <h3>Artikel Terbaru</h3>
      <div class="grid">
        <article data-category="biota">
          <img src="../assets/hiupaus.png" alt="Hiu Paus Indonesia Timur">
          <h4>Konservasi Hiu Paus di Indonesia Timur</h4>
          <p class="date">12.08.2025</p>
          <a href="../artikelTemplate/artikel.php">Baca selengkapnya →</a>
        </article>
        <article data-category="geologi">
          <img src="../assets/palung-jawa.png" alt="Palung Jawa">
          <h4>Palung Jawa: Jurang Laut dalam di Selatan Indonesia</h4>
          <p class="date">18.04.2025</p>
          <a href="../artikelTemplate/artikel.php">Baca selengkapnya →</a>
        </article>
        <article data-category="wisata">
          <img src="../assets/pesonalaut.png" alt="Raja Ampat">
          <h4>Pesona Laut Pulau Biak dan Kepulauan Padaido</h4>
          <p class="date">16.11.2025</p>
          <a href="../artikelTemplate/artikel.php">Baca selengkapnya →</a>
        </article>
      </div>

      <div class="tombol-tengah">
        <button class="lihat-lebih">Tampilkan lebih banyak ▼</button>
      </div>

      <div class="grid tambahan">
        <article data-category="wisata">
          <img src="../assets/snorkeling-dengan-penyu.png" alt="Laut Derawan">
          <h4>Snorkeling Bersama Penyu di Laut Derawan</h4>
          <p class="date">16.11.2025</p>
          <a href="#">Baca selengkapnya →</a>
        </article>
        <article data-category="biota">
          <img src="../assets/kima.png" alt="Kima Raksasa">
          <h4>Kima Raksasa: Permata Laut yang Terancam</h4>
          <p class="date">16.02.2025</p>
          <a href="../artikelTemplate/artikel.php">Baca selengkapnya →</a>
        </article>
        <article data-category="konservasi">
          <img src="../assets/terumbu-karang.png" alt="Terumbu Karang Indonesia">
          <h4>Terumbu Karang di Indonesia: Permata Laut Dunia</h4>
          <p class="date">05.05.2025</p>
          <a href="../artikelTemplate/artikel.php">Baca selengkapnya →</a>
        </article>
        <article data-category="wisata">
          <img src="../assets/teluk-tomini.png" alt="Teluk Tomini">
          <h4>Teluk Tomini, Permata Laut di Jantung Sulawesi</h4>
          <p class="date">03.06.2025</p>
          <a href="../artikelTemplate/artikel.php">Baca selengkapnya →</a>
        </article>
        <article data-category="konservasi">
          <img src="../assets/bambu.png" alt="Restorasi Bambu Laut">
          <h4>Strategi Restorasi Bambu Laut di Perairan Wakatobi</h4>
          <p class="date">16.11.2025</p>
          <a href="#">Baca selengkapnya →</a>
        </article>
        <article data-category="konservasi">
          <img src="../assets/tsunami.png" alt="Tsunami">
          <h4>Bagaimana Tsunami Bisa Terjadi?</h4>
          <p class="date">16.11.2025</p>
          <a href="#">Baca selengkapnya →</a>
        </article>
      </div>
    </section>
  </main>

  <!-- Include footer -->
  <?php include '../assets/templateHalaman/footer.php'; ?>
</body>
</html>