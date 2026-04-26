<div class="faq-list">
  <?php foreach ($faqList as $index => $faq): ?>
    <div class="faq-item" id="faq-<?= $index ?>">
      <button class="faq-question" onclick="toggleFaq('faq-<?= $index ?>')" type="button" aria-expanded="false">
        <h3><?= htmlspecialchars($faq['q']) ?></h3>
        <span class="faq-icon">+</span>
      </button>
      <div class="faq-answer">
        <p><?= htmlspecialchars($faq['a']) ?></p>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<script>
  function toggleFaq(id) {
    const item = document.getElementById(id);
    const isOpen = item.classList.contains('open');

    // Tutup semua yang lain
    document.querySelectorAll('.faq-list .faq-item').forEach(el => {
      el.classList.remove('open');
      el.querySelector('.faq-question').setAttribute('aria-expanded', 'false');
    });

    // Buka yang diklik (kalau sebelumnya tertutup)
    if (!isOpen) {
      item.classList.add('open');
      item.querySelector('.faq-question').setAttribute('aria-expanded', 'true');
    }
  }
</script>