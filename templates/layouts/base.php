<?php

$pageTitle = $pageTitle ?? __('app.name');
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
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="color-scheme" content="light dark">
    <title><?= e($pageTitle) ?> | <?= e(__('app.name')) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anuphan:wght@300;400;500;600;700&family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="icon" href="<?= e(asset('icons/app-icon.svg')) ?>" type="image/svg+xml">
    <link rel="icon" href="<?= e(asset('icons/app-icon-192.png')) ?>" sizes="192x192" type="image/png">
    <link rel="apple-touch-icon" sizes="192x192" href="<?= e(asset('icons/app-icon-192.png')) ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.11.1/styles/github.min.css">
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/utilities.css')) ?>">
</head>
<body class="site-shell">
    <div class="orb orb-a"></div>
    <div class="orb orb-b"></div>
    <header class="site-header">
        <div class="shell flex items-center justify-between gap-6 py-5">
            <a class="brand-mark" href="<?= e(url()) ?>">
                <span class="brand-kicker">SKOService</span>
                <span class="brand-name"><?= e(__('app.name')) ?></span>
            </a>
            <nav class="main-nav" data-mobile-nav>
                <a href="<?= e(url()) ?>"><?= e(__('nav.home')) ?></a>
                <a href="<?= e(url('search')) ?>"><?= e(__('nav.search')) ?></a>
                <?php if (is_authenticated()): ?>
                    <a href="<?= e(url('dashboard')) ?>"><?= e(__('nav.dashboard')) ?></a>
                    <a href="<?= e(url('workspace/apis')) ?>"><?= e(__('nav.workspace')) ?></a>
                    <?php if (is_admin()): ?>
                        <a href="<?= e(url('admin')) ?>"><?= e(__('nav.admin')) ?></a>
                    <?php endif; ?>
                <?php endif; ?>
            </nav>
            <div class="flex items-center gap-3">
                <?= component('lang-toggle') ?>
                <?= component('theme-toggle') ?>
                <?php if (is_authenticated()): ?>
                    <form method="post" action="<?= e(url('logout')) ?>" class="inline-flex">
                        <?= csrf_field() ?>
                        <button class="button button-secondary" type="submit"><?= e(__('nav.logout')) ?></button>
                    </form>
                <?php else: ?>
                    <a class="button button-ghost" href="<?= e(url('login')) ?>"><?= e(__('nav.login')) ?></a>
                    <a class="button button-primary" href="<?= e(url('register')) ?>"><?= e(__('nav.register')) ?></a>
                <?php endif; ?>
                <button class="nav-toggle lg:hidden" type="button" data-mobile-toggle>
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>
    </header>

    <main class="shell py-6 lg:py-10">
        <?php if ($successMessage !== null): ?>
            <div class="flash flash-success"><?= e($successMessage) ?></div>
        <?php endif; ?>
        <?php if ($errorMessage !== null): ?>
            <div class="flash flash-error"><?= e($errorMessage) ?></div>
        <?php endif; ?>
        <?= $content ?>
    </main>

    <footer class="site-footer">
        <div class="shell flex flex-col gap-4 py-8 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="footer-title"><?= e(__('footer.title')) ?></p>
                <p class="footer-copy"><?= e(__('footer.copy')) ?></p>
            </div>
            <div class="footer-links">
                <a href="<?= e(url('search')) ?>"><?= e(__('footer.catalog')) ?></a>
                <a href="<?= e(url('admin')) ?>"><?= e(__('footer.admin')) ?></a>
                <a href="https://github.com" target="_blank" rel="noreferrer">GitHub</a>
            </div>
        </div>
    </footer>

    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.11.1/highlight.min.js"></script>
    <script defer src="<?= e(asset('js/app.js')) ?>"></script>
    <script defer src="<?= e(asset('js/try-it-out.js')) ?>"></script>
</body>
</html>