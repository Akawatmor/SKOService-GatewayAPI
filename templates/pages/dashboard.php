<section class="section-heading mb-8">
    <p class="eyebrow"><?= e(__('dashboard.kicker')) ?></p>
    <h1><?= e(__('dashboard.title')) ?></h1>
    <p class="hero-copy"><?= e(__('dashboard.copy')) ?></p>
</section>

<?php if ($generatedApiKey !== null): ?>
    <section class="card mb-6">
        <p class="eyebrow"><?= e(__('dashboard.new_api_key')) ?></p>
        <h2 class="card-title"><?= e($generatedApiKey) ?></h2>
        <p class="card-copy"><?= e(__('dashboard.copy_now')) ?></p>
    </section>
<?php endif; ?>

<div class="grid gap-6 lg:grid-cols-3">
    <section class="card lg:col-span-1">
        <p class="eyebrow"><?= e(__('dashboard.profile')) ?></p>
        <h2 class="card-title"><?= e($user['username']) ?></h2>
        <p class="card-copy"><?= e($user['email']) ?></p>
        <div class="tag-list mt-4">
            <span><?= e($user['role_name']) ?></span>
            <span><?= e($user['api_key_prefix'] ?? 'sk_live_****') ?>...</span>
        </div>
        <form class="mt-6" method="post" action="<?= e(url('dashboard/regenerate-api-key')) ?>">
            <?= csrf_field() ?>
            <button class="button button-secondary" type="submit"><?= e(__('button.regenerate_key')) ?></button>
        </form>
    </section>
    <section class="card lg:col-span-2">
        <p class="eyebrow"><?= e(__('dashboard.grants')) ?></p>
        <div class="table-like mt-4">
            <?php foreach ($grants as $grant): ?>
                <div class="table-row"><strong><?= e($grant['api_name']) ?></strong><span><?= e($grant['granted_at']) ?></span></div>
            <?php endforeach; ?>
            <?php if ($grants === []): ?><p><?= e(__('dashboard.no_grants')) ?></p><?php endif; ?>
        </div>
    </section>
</div>

<div class="grid gap-6 mt-6 lg:grid-cols-2">
    <section class="card">
        <p class="eyebrow"><?= e(__('dashboard.requests')) ?></p>
        <div class="table-like mt-4">
            <?php foreach ($requests as $item): ?>
                <a class="table-row" href="<?= e(url('access-request/' . $item['id'])) ?>"><strong><?= e($item['api_name']) ?></strong><span><?= e($item['status']) ?></span></a>
            <?php endforeach; ?>
            <?php if ($requests === []): ?><p><?= e(__('dashboard.no_requests')) ?></p><?php endif; ?>
        </div>
    </section>
    <section class="card">
        <div class="flex items-center justify-between gap-3">
            <p class="eyebrow">Managed APIs</p>
            <a class="button button-secondary" href="<?= e(url('workspace/apis')) ?>">Open Workspace</a>
        </div>
        <div class="table-like mt-4">
            <?php foreach ($managedApis as $service): ?>
                <a class="table-row" href="<?= e(url('workspace/apis/' . $service['id'] . '/edit')) ?>"><strong><?= e($service['name']) ?></strong><span><?= e((string) ($service['endpoint_count'] ?? 0)) ?> endpoints</span></a>
            <?php endforeach; ?>
            <?php if ($managedApis === []): ?><p>No managed APIs yet.</p><?php endif; ?>
        </div>
    </section>
</div>

<div class="grid gap-6 mt-6 lg:grid-cols-1">
    <section class="card">
        <p class="eyebrow"><?= e(__('dashboard.usage')) ?></p>
        <div class="table-like mt-4">
            <?php foreach ($usageLogs as $log): ?>
                <div class="table-row"><strong><?= e($log['api_name'] ?? $log['path']) ?></strong><span><?= e(($log['status_code'] ?? '-') . ' · ' . ($log['response_time_ms'] ?? 0) . ' ms') ?></span></div>
            <?php endforeach; ?>
            <?php if ($usageLogs === []): ?><p><?= e(__('dashboard.no_usage')) ?></p><?php endif; ?>
        </div>
    </section>
</div>