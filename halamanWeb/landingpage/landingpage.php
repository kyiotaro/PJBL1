<?php include '../../koneksi.php'; ?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Permata Biru Nusantara - Portal Laut Indonesia</title>
  <link rel="stylesheet" href="landingpage.css">
  <link rel="stylesheet" href="../../assets/templateHalaman/navbar.css">
  <link rel="stylesheet" href="../../assets/templateHalaman/footer.css">
  <link rel="stylesheet" href="../../assets/templateHalaman/cardVariant/card1/card1.css">
</head>

<body>
  <?php include '../../assets/templateHalaman/navbar.php'; ?>

  <section class="hero" style="background-image: url('../../assets/Foto/ui/background.png')">
    <div class="overlay">
      <h2>Permata Biru Nusantara</h2>
      <p>
        <span class="highlight">Permata Biru Nusantara</span> menghadirkan pesona dan kekayaan laut Indonesia dari
        sabang sampai merauke.
        Semua tentang laut, dari biodata, budaya, hingga pelestarian, terangkum di sini.
      </p>
    </div>
  </section>

  <main>
    <div class="kategori">
      <button class="filter-btn aktif" data-filter="all">Semua</button>
      <button class="filter-btn" data-filter="biota">Biota</button>
      <button class="filter-btn" data-filter="wisata">Wisata</button>
      <button class="filter-btn" data-filter="konservasi">Konservasi</button>
      <button class="filter-btn" data-filter="geologi">Geologi</button>
    </div>

    <section class="terpopuler" id="terpopuler">
      <h3>Terpopuler</h3>
      <div class="grid">
        <?php
        $query = mysqli_query($koneksi, "SELECT * FROM Artikel ORDER BY tanggal DESC LIMIT 6");
        while ($artikel = mysqli_fetch_assoc($query)) {
          include '../../assets/templateHalaman/cardVariant/card1/card1.php';
        }
        ?>
      </div>
    </section>

    <!-- ARTIKEL TERBARU -->
    <section class="artikel-terbaru">
      <h3>Artikel Terbaru</h3>
      <div class="grid">
        <?php
        $query2 = mysqli_query($koneksi, "SELECT * FROM Artikel ORDER BY tanggal DESC LIMIT 6 OFFSET 6");
        while ($artikel = mysqli_fetch_assoc($query2)) {
          include '../../assets/templateHalaman/cardVariant/card1/card1.php';
        }
        ?>
      </div>
    </section>

    <!-- ARTIKEL LAINNYA -->
    <section class="artikel-lainnya">
      <h3>Artikel Lainnya</h3>
      <div class="grid">
        <?php
        $query3 = mysqli_query($koneksi, "SELECT * FROM Artikel ORDER BY tanggal DESC LIMIT 12 OFFSET 12");
        while ($artikel = mysqli_fetch_assoc($query3)) {
          include '../../assets/templateHalaman/cardVariant/card1/card1.php';
        }
        ?>
      </div>
    </section>
  </main>

  <?php include '../../assets/templateHalaman/footer.php'; ?>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const filterButtons = document.querySelectorAll('.filter-btn');
      const articles = document.querySelectorAll('article');
      const sections = document.querySelectorAll('main section');
      const kategoriDiv = document.querySelector('.kategori');

      // Insert filter grid SETELAH div .kategori, bukan prepend ke main
      const filterGrid = document.createElement('div');
      filterGrid.className = 'grid';
      filterGrid.style.display = 'none';
      filterGrid.style.padding = '0 50px 30px';
      kategoriDiv.insertAdjacentElement('afterend', filterGrid);

      filterButtons.forEach(button => {
        button.addEventListener('click', () => {
          const filter = button.getAttribute('data-filter');

          filterButtons.forEach(btn => btn.classList.remove('aktif'));
          button.classList.add('aktif');

          if (filter === 'all') {
            filterGrid.style.display = 'none';
            filterGrid.innerHTML = '';
            sections.forEach(section => section.style.display = 'block');

          } else {
            sections.forEach(section => section.style.display = 'none');

            filterGrid.innerHTML = '';
            filterGrid.style.display = 'grid';

            articles.forEach(article => {
              if (article.getAttribute('data-category') === filter) {
                filterGrid.appendChild(article.cloneNode(true));
              }
            });

            if (filterGrid.children.length === 0) {
              filterGrid.style.display = 'block';
              filterGrid.innerHTML = '<p style="color:#666; padding:20px 0;">Belum ada artikel dalam kategori ini.</p>';
            }
          }
        });
      });
    });                                                                  
  </script>
</body>

</html>