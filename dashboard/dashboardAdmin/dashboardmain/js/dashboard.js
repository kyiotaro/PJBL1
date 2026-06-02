document.addEventListener('DOMContentLoaded', async () => {
  const session = await setupAdminUI();
  if (!session) {
    return;
  }
});
