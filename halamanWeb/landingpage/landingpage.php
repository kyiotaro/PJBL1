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
        $query = mysqli_query($koneksi, "SELECT * FROM Artikel ORDER BY tanggal DESC LIMIT 3");
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
        $query2 = mysqli_query($koneksi, "SELECT * FROM Artikel ORDER BY tanggal DESC LIMIT 3 OFFSET 3");
        while ($artikel = mysqli_fetch_assoc($query2)) {
          include '../../assets/templateHalaman/cardVariant/card1/card1.php';
        }
        ?>
      </div>

      <div class="tombol-tengah">
        <button class="lihat-lebih">Tampilkan lebih banyak ▼</button>
      </div>

      <div class="grid tambahan">
        <?php
        $query3 = mysqli_query($koneksi, "SELECT * FROM Artikel ORDER BY tanggal DESC LIMIT 3 OFFSET 6");
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
      const sectionHeaders = document.querySelectorAll('main section h3');
      const showMoreBtn = document.querySelector('.lihat-lebih');
      const extraGrid = document.querySelector('.grid.tambahan');

      // Initial state: hide extra grid if it exists                                                       
      if (extraGrid) {
        extraGrid.style.display = 'none';
      }

      // "Tampilkan lebih banyak" functionality                                                            
      if (showMoreBtn && extraGrid) {
        showMoreBtn.addEventListener('click', () => {
          if (extraGrid.style.display === 'none') {
            extraGrid.style.display = 'grid';
            showMoreBtn.innerHTML = 'Sembunyikan ▲';
          } else {
            extraGrid.style.display = 'none';
            showMoreBtn.innerHTML = 'Tampilkan lebih banyak ▼';
          }
        });
      }

      // Filter functionality                                                                              
      filterButtons.forEach(button => {
        button.addEventListener('click', () => {
          const filter = button.getAttribute('data-filter');

          // Update active class                                                                           
          filterButtons.forEach(btn => btn.classList.remove('aktif'));
          button.classList.add('aktif');

          if (filter === 'all') {
            // Show everything and reset headers                                                           
            articles.forEach(article => article.style.display = 'block');
            sectionHeaders.forEach(h3 => h3.style.display = 'block');
            if (showMoreBtn) showMoreBtn.parentElement.style.display = 'block';
            if (extraGrid) extraGrid.style.display = 'none';
            if (showMoreBtn) showMoreBtn.innerHTML = 'Tampilkan lebih banyak ▼';
          } else {
            // Hide headers and "show more" when filtering                                                 
            sectionHeaders.forEach(h3 => h3.style.display = 'none');
            if (showMoreBtn) showMoreBtn.parentElement.style.display = 'none';
            if (extraGrid) extraGrid.style.display = 'grid'; // show all grid containers to see matching articles                                                                                                    

            // Filter articles based on data-category                                                      
            articles.forEach(article => {
              const category = article.getAttribute('data-category');
              if (category === filter) {
                article.style.display = 'block';
              } else {
                article.style.display = 'none';
              }
            });
          }
        });
      });
    });                                                                                                    
  </script>
</body>

</html>