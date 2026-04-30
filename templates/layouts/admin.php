<?php

$pageTitle = $pageTitle ?? __('admin.title');
$successMessage = flash('success');
$errorMessage = flash('error');
?>
<!doctype html>
<html lang="<?= e(lang()) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0f172a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="color-scheme" content="light dark">
    <title><?= e($pageTitle) ?> | <?= e(__('app.name')) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anuphan:wght@300;400;500;600;700&family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="icon" href="<?= e(asset('icons/app-icon.svg')) ?>" type="image/svg+xml">
    <link rel="icon" href="<?= e(asset('icons/app-icon-192.png')) ?>" sizes="192x192" type="image/png">
    <link rel="apple-touch-icon" sizes="192x192" href="<?= e(asset('icons/app-icon-192.png')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/utilities.css')) ?>">
</head>
<body class="admin-shell">
    <div class="shell admin-grid py-6 lg:py-8">
        <aside class="admin-sidebar">
            <a class="brand-mark" href="<?= e(url('admin')) ?>">
                <span class="brand-kicker">GatewayAPI</span>
                <span class="brand-name"><?= e(__('admin.panel')) ?></span>
            </a>
            <nav class="admin-nav">
                <a href="<?= e(url('admin')) ?>"><?= e(__('admin.dashboard')) ?></a>
                <a href="<?= e(url('admin/apis')) ?>"><?= e(__('admin.apis')) ?></a>
                <a href="<?= e(url('admin/users')) ?>"><?= e(__('admin.users')) ?></a>
                <a href="<?= e(url('admin/access-requests')) ?>"><?= e(__('admin.access_requests')) ?></a>
                <a href="<?= e(url('admin/rate-limits')) ?>"><?= e(__('admin.rate_limits')) ?></a>
                <a href="<?= e(url('admin/logs')) ?>"><?= e(__('admin.logs')) ?></a>
                <a href="<?= e(url('workspace/apis')) ?>"><?= e(__('nav.workspace')) ?></a>
                <a href="<?= e(url()) ?>"><?= e(__('admin.back_site')) ?></a>
            </nav>
            <div class="mt-6">
                <?= component('theme-toggle') ?>
            </div>
        </aside>
        <div class="admin-content">
            <?php if ($successMessage !== null): ?>
                <div class="flash flash-success"><?= e($successMessage) ?></div>
            <?php endif; ?>
            <?php if ($errorMessage !== null): ?>
                <div class="flash flash-error"><?= e($errorMessage) ?></div>
            <?php endif; ?>
            <?= $content ?>
        </div>
    </div>
    <script defer src="<?= e(asset('js/app.js')) ?>"></script>
</body>
</html>