(function () {
  function initLikeButtons(root) {
    var scope = root || document;
    scope.querySelectorAll('.like-btn[data-artikel-id]').forEach(function (btn) {
      if (btn.dataset.likeBound === '1') {
        return;
      }
      btn.dataset.likeBound = '1';

      btn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();

        if (btn.disabled || btn.classList.contains('is-loading')) {
          return;
        }

        var artikelId = btn.getAttribute('data-artikel-id');
        var toggleUrl = btn.getAttribute('data-toggle-url') || '/PJBL-main/halamanWeb/Favorit/toggle_favorit.php';
        var loginUrl = btn.getAttribute('data-login-url') || '/PJBL-main/halamanWeb/loginpage/signin.php';

        btn.classList.add('is-loading');
        btn.disabled = true;

        var body = new URLSearchParams();
        body.set('artikel_id', artikelId);

        fetch(toggleUrl, {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          credentials: 'same-origin',
          body: body.toString(),
        })
          .then(function (res) {
            return res.json().then(function (data) {
              return { ok: res.ok, status: res.status, data: data };
            });
          })
          .then(function (result) {
            var data = result.data || {};

            if (result.status === 401 || data.require_login) {
              var goLogin = confirm((data.message || 'Silakan login terlebih dahulu.') + '\n\nBuka halaman login?');
              if (goLogin) {
                window.location.href = loginUrl;
              }
              return;
            }

            if (!result.ok || !data.success) {
              alert(data.message || 'Gagal memperbarui favorit.');
              return;
            }

            btn.classList.toggle('is-liked', !!data.liked);
            btn.setAttribute('aria-pressed', data.liked ? 'true' : 'false');
            btn.setAttribute('aria-label', data.liked ? 'Hapus dari favorit' : 'Tambah ke favorit');
            btn.setAttribute('title', data.liked ? 'Hapus dari favorit' : 'Tambah ke favorit');

            btn.dispatchEvent(
              new CustomEvent('like:toggled', {
                bubbles: true,
                detail: { liked: !!data.liked, artikelId: parseInt(artikelId, 10), message: data.message },
              })
            );
          })
          .catch(function () {
            alert('Koneksi gagal. Coba lagi.');
          })
          .finally(function () {
            btn.classList.remove('is-loading');
            btn.disabled = false;
          });
      });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      initLikeButtons();
    });
  } else {
    initLikeButtons();
  }

  window.pbInitLikeButtons = initLikeButtons;
})();
