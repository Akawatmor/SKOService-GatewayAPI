<section class="card">
    <p class="eyebrow"><?= e(__('access_request.kicker')) ?></p>
    <h1 class="card-title"><?= e($accessRequest['api_name']) ?></h1>
    <div class="tag-list mt-4">
        <span><?= e($accessRequest['status']) ?></span>
        <span><?= e($accessRequest['requested_at']) ?></span>
    </div>
    <div class="mini-panel mt-6">
        <h3><?= e(__('access_request.reason')) ?></h3>
        <p><?= e((string) ($accessRequest['reason'] ?? '')) ?></p>
    </div>
    <?php if (!empty($accessRequest['reviewer_note'])): ?>
        <div class="mini-panel mt-4">
            <h3><?= e(__('access_request.reviewer_note')) ?></h3>
            <p><?= e((string) $accessRequest['reviewer_note']) ?></p>
        </div>
    <?php endif; ?>
</section>