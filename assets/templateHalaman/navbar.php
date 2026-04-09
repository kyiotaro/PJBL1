<!-- navbar.php -->
<header class="site-header">
  <div class="logo">
    <a href="/PJBL-main/halamanWeb/landingpage/landingpage.php">
      <img src="/PJBL-main/assets/Foto/brand/logo.png" alt="Logo">
    </a>
    <h1>Permata Biru Nusantara</h1>
  </div>
  
  <form action="/PJBL-main/halamanWeb/hasilPencarian/hasilPencarian.php" method="GET" class="search-bar">
    <input type="text" name="query" placeholder="Cari artikel..." value="<?= isset($_GET['query']) ? htmlspecialchars($_GET['query']) : '' ?>">

    <button type="submit">
      <img src="/PJBL-main/assets/Foto/ui/search-icon.png" alt="Search">
    </button>
  </form>

  <nav class="nav-links">
    <a href="/PJBL-main/halamanWeb/landingpage/landingpage.php#terpopuler">Artikel</a>
    <a href="/PJBL-main/halamanWeb/landingpage/landingpage.php#terpopuler">Terpopuler</a>
    <a href="/PJBL-main/halamanWeb/tentangpage/tentang.php">Tentang</a>
  </nav>
</header>