<section class="hero-grid">
    <div class="hero-card hero-main">
        <p class="eyebrow"><?= e(__('home.kicker')) ?></p>
        <h1 class="hero-title"><?= e(__('home.title')) ?></h1>
        <p class="hero-copy"><?= e(__('home.description')) ?></p>
        <div class="mt-8 flex flex-wrap gap-3">
            <a class="button button-primary" href="<?= e(url('search')) ?>"><?= e(__('button.explore_catalog')) ?></a>
            <?php if (!is_authenticated()): ?>
                <a class="button button-secondary" href="<?= e(url('register')) ?>"><?= e(__('button.get_started')) ?></a>
            <?php endif; ?>
        </div>
    </div>
    <div class="hero-card hero-aside">
        <p class="eyebrow"><?= e(__('home.workflow')) ?></p>
        <ol class="step-list">
            <li><strong>1.</strong> <?= e(__('home.step_find')) ?></li>
            <li><strong>2.</strong> <?= e(__('home.step_request')) ?></li>
            <li><strong>3.</strong> <?= e(__('home.step_use')) ?></li>
        </ol>
    </div>
</section>

<section class="section-block">
    <div class="section-heading">
        <p class="eyebrow"><?= e(__('home.support')) ?></p>
        <h2><?= e(__('home.support_title')) ?></h2>
    </div>
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <?php foreach ($supportMatrix as $item): ?>
            <article class="card">
                <h3 class="card-title"><?= e($item['type']) ?></h3>
                <p class="card-copy"><?= e($item['description']) ?></p>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="stats-strip">
    <article><span><?= e((string) $stats['total_apis']) ?></span><p><?= e(__('stats.total_apis')) ?></p></article>
    <article><span><?= e((string) $stats['total_endpoints']) ?></span><p><?= e(__('stats.total_endpoints')) ?></p></article>
    <article><span><?= e((string) $stats['developers']) ?></span><p><?= e(__('stats.developers')) ?></p></article>
</section>

<section class="section-block">
    <div class="section-heading">
        <p class="eyebrow"><?= e(__('home.featured')) ?></p>
        <h2><?= e(__('home.featured_title')) ?></h2>
    </div>
    <div class="grid gap-4 lg:grid-cols-2 xl:grid-cols-3">
        <?php foreach ($featuredApis as $api): ?>
            <?= component('api-card', ['api' => $api]) ?>
        <?php endforeach; ?>
    </div>
</section>