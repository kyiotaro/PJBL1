document.addEventListener('DOMContentLoaded', () => {
  if (!protectAdminPage()) {
    return;
  }

  setupAdminUI();
});