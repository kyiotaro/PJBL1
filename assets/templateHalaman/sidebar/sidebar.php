<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$activePage = $activePage ?? 'dashboard';

$isAdmin = !empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
$isUser = !empty($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;

$navItems = [];

if ($isAdmin) {
    $navItems = [
        'dashboard' => [
            'label' => 'Dashboard',
            'href'  => '/PJBL-main/dashboard/dashboardAdmin/dashboardmain/dashboard.php',
            'icon'  => '<path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>',
        ],
        'artikel' => [
            'label' => 'Artikel',
            'href'  => '/PJBL-main/dashboard/dashboardAdmin/dashboardartikel/dashboard_artikel.php',
            'icon'  => '<path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>',
        ],
        'inbox' => [
            'label' => 'Inbox',
            'href'  => '/PJBL-main/dashboard/dashboardAdmin/dashboardinbox/dashboard_inbox.php',
            'icon'  => '<path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>',
        ],
        //'pengaturan' => [
        //    'label' => 'Pengaturan',
        //    'href'  => '/PJBL-main/dashboard/dashboardAdmin/dashboardpengaturan/dashboard_pengaturan.php',
        //    'icon'  => '<path d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l2.03-1.58c.18-.14.23-.41.12-.61l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.05.3-.09.63-.09.94s.02.64.07.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"/>',
        //],
    ];
} elseif ($isUser) {
    $navItems = [
        'dashboard' => [
            'label' => 'Dashboard',
            'href'  => '/PJBL-main/dashboard/dashboardUser/dashboard.php',
            'icon'  => '<path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>',
        ],
        'artikel' => [
            'label' => 'Artikel Saya',
            'href'  => '/PJBL-main/dashboard/dashboardUser/dashboard_artikel.php',
            'icon'  => '<path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>',
        ],
    ];
}

$userEmail = $isAdmin ? ($_SESSION['admin_email'] ?? 'admin@example.com') : ($_SESSION['user_email'] ?? 'user@example.com');
$userRole = $isAdmin ? 'Administrator' : 'Kontributor';
$userInitial = strtoupper(substr($userEmail, 0, 1));
?>

<aside class="pb-sidebar" id="pbSidebar">

  <!-- Toggle button -->
  <button class="pb-sidebar-toggle" id="pbSidebarToggle" title="Toggle sidebar" type="button">
    <svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18">
      <path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/>
    </svg>
  </button>

  <!-- Brand -->
  <div class="pb-sidebar-brand">
    <a href="/PJBL-main/halamanWeb/landingpage/landingpage.php" class="pb-brand-link">
      <div class="pb-brand-icon">
        <img src="/PJBL-main/assets/Foto/brand/logo.png" alt="Logo" width="22" height="22">
      </div>
      <div class="pb-brand-text">
        <span class="pb-brand-name">Permata Biru</span>
        <span class="pb-brand-sub">Nusantara</span>
      </div>
    </a>
  </div>

  <!-- Nav -->
  <nav class="pb-sidebar-nav">
    <p class="pb-nav-label">Menu</p>
    <ul>
      <?php foreach ($navItems as $key => $item): ?>
        <li>
          <a href="<?= $item['href'] ?>"
             class="pb-nav-link <?= ($activePage === $key) ? 'active' : '' ?>"
             title="<?= $item['label'] ?>">
            <span class="pb-nav-icon">
              <svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18">
                <?= $item['icon'] ?>
              </svg>
            </span>
            <span class="pb-nav-label-text"><?= $item['label'] ?></span>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </nav>

  <!-- Footer: user info + logout -->
  <div class="pb-sidebar-footer">
    <div class="pb-admin-card">
      <div class="pb-admin-avatar" id="adminInitial"><?= $userInitial ?></div>
      <div class="pb-admin-info">
        <p id="adminEmail" class="pb-admin-email"><?= htmlspecialchars($userEmail) ?></p>
        <span class="pb-admin-role" id="adminRole"><?= $userRole ?></span>
      </div>
    </div>
    <button id="logoutBtn" class="pb-logout-btn" type="button" title="Logout">
      <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16">
        <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
      </svg>
      <span class="pb-logout-text">Logout</span>
    </button>
  </div>

</aside>

<script>
  (function() {
    const theme = localStorage.getItem('theme');
    if (theme === 'dark') {
      document.documentElement.setAttribute('data-theme', 'dark');
    }
  })();
</script>
