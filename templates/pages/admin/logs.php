<section class="section-heading">
    <p class="eyebrow"><?= e(__('admin.logs')) ?></p>
    <h1>Request Logs</h1>
</section>

<section class="card">
    <div class="table-like">
        <?php foreach ($logs as $log): ?>
            <div class="table-row !items-start flex-col lg:!flex-row">
                <div>
                    <strong><?= e($log['api_name'] ?? $log['path']) ?></strong>
                    <p class="card-copy"><?= e(($log['method'] ?? '') . ' ' . ($log['path'] ?? '')) ?></p>
                    <p class="card-copy"><?= e($log['user_email'] ?? $log['ip_address'] ?? '-') ?></p>
                </div>
                <span><?= e(($log['status_code'] ?? '-') . ' · ' . ($log['response_time_ms'] ?? 0) . ' ms · ' . ($log['created_at'] ?? '')) ?></span>
            </div>
        <?php endforeach; ?>
        <?php if ($logs === []): ?><p>No request logs yet.</p><?php endif; ?>
    </div>
</section>