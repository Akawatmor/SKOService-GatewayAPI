<section class="section-heading">
    <p class="eyebrow"><?= e(__('admin.apis')) ?></p>
    <h1><?= e($section_label ?? 'API Services') ?></h1>
    <a class="button button-primary mt-3 w-fit" href="<?= e(($route_prefix ?? '/admin/apis') . '/create') ?>">Create Service</a>
</section>

<section class="card">
    <div class="table-like">
        <?php foreach ($services as $service): ?>
            <div class="table-row">
                <div>
                    <strong><?= e($service['name']) ?></strong>
                    <p class="card-copy"><?= e($service['slug']) ?> · <?= e($service['api_type']) ?> · <?= e($service['status']) ?></p>
                </div>
                <div class="flex gap-3">
                    <span class="badge"><?= e((string) $service['endpoint_count']) ?> endpoints</span>
                    <a class="button button-secondary" href="<?= e(($route_prefix ?? '/admin/apis') . '/' . $service['id'] . '/edit') ?>">Edit</a>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if ($services === []): ?><p>No API services defined yet.</p><?php endif; ?>
    </div>
</section>