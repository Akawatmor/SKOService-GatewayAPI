<?php

$tags = json_decode((string) ($api['tags'] ?? '[]'), true);
$tags = is_array($tags) ? $tags : [];
$canRequest = is_authenticated() && ((int) ($api['is_public'] ?? 1) === 0);
?>
<article class="card api-card">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <p class="eyebrow"><?= e($api['mode'] ?? 'catalog') ?></p>
            <h3 class="card-title"><?= e($api['name'] ?? '') ?></h3>
            <p class="card-copy"><?= e(localized_text($api, 'description')) ?></p>
        </div>
        <div class="badge-stack">
            <span class="badge badge-type"><?= e($api['api_type'] ?? '') ?></span>
            <span class="badge badge-status"><?= e($api['status'] ?? '') ?></span>
            <span class="badge"><?= e((string) ($api['endpoint_count'] ?? 0)) ?> <?= e(__('api.endpoints')) ?></span>
        </div>
    </div>
    <div class="tag-list mt-4">
        <?php foreach ($tags as $tag): ?>
            <span><?= e((string) $tag) ?></span>
        <?php endforeach; ?>
    </div>
    <div class="mt-6 flex flex-wrap gap-3">
        <a class="button button-primary" href="<?= e(url('api/' . $api['slug'])) ?>"><?= e(__('button.view_details')) ?></a>
        <?php if ($canRequest): ?>
            <form method="post" action="<?= e(url('access-request')) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="api_service_id" value="<?= e((string) $api['id']) ?>">
                <button class="button button-secondary" type="submit"><?= e(__('button.request_access')) ?></button>
            </form>
        <?php elseif (!is_authenticated() && (int) ($api['is_public'] ?? 1) === 0): ?>
            <a class="button button-secondary" href="<?= e(url('login')) ?>"><?= e(__('button.login_for_access')) ?></a>
        <?php endif; ?>
    </div>
</article>