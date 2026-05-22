<?php
/**
 * @var string $sectionTitle
 * @var string $sectionSubtitle
 * @var string $sectionClass
 */
$sectionTitle = $sectionTitle ?? '';
$sectionSubtitle = $sectionSubtitle ?? '';
$sectionClass = $sectionClass ?? '';
?>
<div class="pb-section-header <?= htmlspecialchars($sectionClass) ?>">
  <h2><?= htmlspecialchars($sectionTitle) ?></h2>
  <?php if ($sectionSubtitle): ?>
    <p><?= htmlspecialchars($sectionSubtitle) ?></p>
  <?php endif; ?>
</div>
