<?php
/**
 * @var string $heroImage Path to the hero image
 * @var string $heroTitle Main heading text
 * @var string $heroSubtitle Subtitle or description text
 * @var string $heroClass Additional CSS classes for the section
 */
$heroImage = $heroImage ?? '';
$heroTitle = $heroTitle ?? 'Permata Biru Nusantara';
$heroSubtitle = $heroSubtitle ?? '';
$heroClass = $heroClass ?? '';
?>
<section class="pb-hero <?= htmlspecialchars($heroClass) ?>">
  <?php if ($heroImage): ?>
    <img src="<?= htmlspecialchars($heroImage) ?>" alt="<?= htmlspecialchars($heroTitle) ?>" class="pb-hero-img">
  <?php endif; ?>
  <div class="pb-hero-overlay">
    <div class="pb-hero-content">
      <h1><?= $heroTitle ?></h1>
      <?php if ($heroSubtitle): ?>
        <p><?= $heroSubtitle ?></p>
      <?php endif; ?>
    </div>
  </div>
</section>
