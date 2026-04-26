<!-- navbar.php -->
<header class="site-header">
  <div class="logo">
    <a href="/PJBL-main/halamanWeb/landingpage/landingpage.php">
      <img src="/PJBL-main/assets/Foto/brand/logo.png" alt="Logo">
    </a>
    <h1>Permata Biru Nusantara</h1>
  </div>
  
  <form action="/PJBL-main/halamanWeb/hasilPencarian/hasilPencarian.php" method="GET" class="pb-search-bar">
    <div class="pb-input-container">
      <svg class="pb-search-icon" width="18" height="18" viewBox="0 0 24 24">
        <path fill="none" d="M0 0h24v24H0z"/>
        <path d="M15.5 14h-.79l-.28-.27A6.518 6.518 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
      </svg>
      <input class="pb-input" type="text" name="query" placeholder="Cari artikel..."
        value="<?= isset($_GET['query']) ? htmlspecialchars($_GET['query']) : '' ?>">
    </div>
    <div class="pb-divider"></div>
    <button type="button" class="pb-mic-btn" id="micBtn" aria-label="Pencarian suara">
      <svg class="pb-mic-icon" width="16" height="16" viewBox="0 0 384 512">
        <path d="M192 0C139 0 96 43 96 96V256c0 53 43 96 96 96s96-43 96-96V96c0-53-43-96-96-96zM64 216c0-13.3-10.7-24-24-24s-24 10.7-24 24v40c0 89.1 66.2 162.7 152 174.4V464H120c-13.3 0-24 10.7-24 24s10.7 24 24 24h72 72c13.3 0 24-10.7 24-24s-10.7-24-24-24H216V430.4c85.8-11.7 152-85.3 152-174.4V216c0-13.3-10.7-24-24-24s-24 10.7-24 24v40c0 70.7-57.3 128-128 128s-128-57.3-128-128V216z"/>
      </svg>
    </button>
  </form>

  <nav class="nav-links">
    <a href="/PJBL-main/halamanWeb/landingpage/landingpage.php#terpopuler">Artikel</a>
    <a href="/PJBL-main/halamanWeb/landingpage/landingpage.php#terpopuler">Terpopuler</a>
    <a href="/PJBL-main/halamanWeb/tentangpage/tentang.php">Tentang</a>
  </nav>
</header>