<?php

$uri = $_SERVER['REQUEST_URI'] ?? '/';
$parts = parse_url($uri);
$path = $parts['path'] ?? '/';
parse_str($parts['query'] ?? '', $query);
?>
<div class="lang-toggle">
    <?php foreach (['th' => 'TH', 'en' => 'EN'] as $code => $label): ?>
        <?php $query['lang'] = $code; ?>
        <a class="<?= lang() === $code ? 'is-active' : '' ?>" href="<?= e($path . '?' . http_build_query($query)) ?>"><?= e($label) ?></a>
    <?php endforeach; ?>
</div>