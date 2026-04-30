<?php
$tags = json_decode((string) ($service['tags'] ?? '[]'), true);
$tags = is_array($tags) ? $tags : [];
$healthSupported = trim((string) ($service['health_check_path'] ?? '')) !== '';

if (!$healthSupported) {
    foreach ($endpoints as $candidate) {
        if (in_array((string) ($candidate['path'] ?? ''), ['/health', '/healthz', '/status', '/ping'], true)
            && in_array(strtoupper((string) ($candidate['method'] ?? 'GET')), ['GET', 'HEAD'], true)) {
            $healthSupported = true;
            break;
        }
    }
}
?>
<section class="detail-hero card">
    <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <p class="eyebrow"><?= e(__('api.detail')) ?></p>
            <h1 class="hero-title !text-4xl"><?= e($service['name']) ?></h1>
            <p class="hero-copy"><?= e(localized_text($service, 'description')) ?></p>
            <div class="tag-list mt-4">
                <span><?= e($service['api_type']) ?></span>
                <span><?= e($service['mode']) ?></span>
                <span><?= e($service['standard'] ?: 'none') ?></span>
                <span><?= e($service['version']) ?></span>
                <?php foreach ($tags as $tag): ?>
                    <span><?= e((string) $tag) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="detail-actions">
            <?php if (!$hasAccess && is_authenticated()): ?>
                <form method="post" action="<?= e(url('access-request')) ?>" class="grid gap-3">
                    <?= csrf_field() ?>
                    <input type="hidden" name="api_service_id" value="<?= e((string) $service['id']) ?>">
                    <textarea name="reason" rows="3" placeholder="<?= e(__('api.access_reason')) ?>"></textarea>
                    <button class="button button-primary" type="submit"><?= e(__('button.request_access')) ?></button>
                </form>
            <?php elseif (!$hasAccess): ?>
                <a class="button button-primary" href="<?= e(url('login')) ?>"><?= e(__('button.login_for_access')) ?></a>
            <?php endif; ?>
            <?php if ($service['api_type'] === 'File' && $snapshots !== []): ?>
                <a class="button button-secondary" href="<?= e(url('api/' . $service['slug'] . '/snapshot/' . $snapshots[0]['filename'])) ?>"><?= e(__('button.download_snapshot')) ?></a>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php if ($hasAccess): ?>
    <?php if ($healthSupported): ?>
        <section class="card health-card" data-health-card data-health-url="<?= e(url('api/' . $service['slug'] . '/health')) ?>">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="eyebrow"><?= e(__('api.health')) ?></p>
                    <h2 class="card-title"><?= e(__('api.health_title')) ?></h2>
                    <p class="card-copy"><?= e(__('api.health_copy')) ?></p>
                </div>
                <button
                    class="button button-secondary"
                    type="button"
                    data-health-trigger
                    data-idle-label="<?= e(__('button.run_health_check')) ?>"
                    data-loading-label="<?= e(__('button.checking')) ?>"
                ><?= e(__('button.run_health_check')) ?></button>
            </div>
            <div class="health-grid mt-6">
                <div class="mini-panel">
                    <h4><?= e(__('api.health_status')) ?></h4>
                    <p class="health-status is-idle" data-health-status><?= e((string) ($service['last_health_status_code'] ?? __('api.health_unknown'))) ?></p>
                </div>
                <div class="mini-panel">
                    <h4><?= e(__('api.health_response_time')) ?></h4>
                    <p data-health-time><?= e((string) ($service['last_health_response_time_ms'] ?? 0)) ?> ms</p>
                </div>
                <div class="mini-panel">
                    <h4><?= e(__('api.health_checked_at')) ?></h4>
                    <p data-health-checked><?= e((string) ($service['last_health_checked_at'] ?? __('api.health_not_checked'))) ?></p>
                </div>
                <div class="mini-panel">
                    <h4><?= e(__('api.health_target')) ?></h4>
                    <p data-health-target><?= e((string) (($service['health_check_method'] ?? 'GET') . ' ' . ($service['health_check_path'] ?? '/auto'))) ?></p>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?= component('try-it-out', ['service' => $service, 'endpoints' => $endpoints]) ?>

    <section class="section-block">
        <div class="section-heading">
            <p class="eyebrow"><?= e(__('api.endpoints')) ?></p>
            <h2><?= e(__('api.endpoints_title')) ?></h2>
        </div>
        <div class="grid gap-3">
            <?php foreach ($endpoints as $endpoint): ?>
                <?= component('endpoint-row', ['endpoint' => $endpoint]) ?>
            <?php endforeach; ?>
        </div>
    </section>

    <?php if ($service['api_type'] === 'File'): ?>
        <section class="card mt-6">
            <div class="section-heading mb-4">
                <p class="eyebrow"><?= e(__('api.snapshots')) ?></p>
                <h2><?= e(__('api.snapshots_title')) ?></h2>
            </div>
            <div class="grid gap-3">
                <?php foreach ($snapshots as $snapshot): ?>
                    <div class="snapshot-row">
                        <div>
                            <h4><?= e($snapshot['filename']) ?></h4>
                            <p><?= e($snapshot['mime_type']) ?> · <?= e((string) $snapshot['size_bytes']) ?> bytes</p>
                        </div>
                        <a class="button button-secondary" href="<?= e(url('api/' . $service['slug'] . '/snapshot/' . $snapshot['filename'])) ?>"><?= e(__('button.download')) ?></a>
                    </div>
                <?php endforeach; ?>
                <?php if ($snapshots === []): ?>
                    <p><?= e(__('api.no_snapshots')) ?></p>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>

    <?= component('schema-viewer', ['schema' => $schema]) ?>
<?php else: ?>
    <section class="card empty-state">
        <h2><?= e(__('api.gated_title')) ?></h2>
        <p><?= e(__('api.gated_copy')) ?></p>
    </section>
<?php endif; ?>