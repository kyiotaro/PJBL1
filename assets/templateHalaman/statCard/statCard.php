<?php
/**
 * @var string $statValue
 * @var string $statLabel
 * @var string $statIcon Optional icon SVG path or class
 */
$statValue = $statValue ?? '0';
$statLabel = $statLabel ?? 'Label';
$statIcon = $statIcon ?? '';
?>
<div class="pb-stat-card">
  <?php if ($statIcon): ?>
    <div class="pb-stat-icon">
      <?= $statIcon ?>
    </div>
  <?php endif; ?>
  <div class="pb-stat-info">
    <h3><?= htmlspecialchars($statValue) ?></h3>
    <p><?= htmlspecialchars($statLabel) ?></p>
  </div>
</div>
