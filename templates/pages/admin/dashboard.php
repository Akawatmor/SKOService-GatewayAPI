<section class="section-heading">
    <p class="eyebrow"><?= e(__('admin.dashboard')) ?></p>
    <h1><?= e(__('admin.title')) ?></h1>
</section>

<div class="stats-strip">
    <article><span><?= e((string) $stats['total_apis']) ?></span><p>Total APIs</p></article>
    <article><span><?= e((string) $stats['total_endpoints']) ?></span><p>Total Endpoints</p></article>
    <article><span><?= e((string) $stats['total_users']) ?></span><p>Total Users</p></article>
</div>

<div class="grid gap-6 lg:grid-cols-2">
    <section class="card">
        <p class="eyebrow">Pending Requests</p>
        <div class="table-like mt-4">
            <?php foreach ($pendingRequests as $item): ?>
                <div class="table-row"><strong><?= e($item['api_name']) ?></strong><span><?= e($item['user_email']) ?></span></div>
            <?php endforeach; ?>
            <?php if ($pendingRequests === []): ?><p>No pending access requests.</p><?php endif; ?>
        </div>
    </section>
    <section class="card">
        <p class="eyebrow">Recent Logs</p>
        <div class="table-like mt-4">
            <?php foreach ($recentLogs as $log): ?>
                <div class="table-row"><strong><?= e($log['api_name'] ?? $log['path']) ?></strong><span><?= e(($log['status_code'] ?? '-') . ' · ' . ($log['response_time_ms'] ?? 0) . ' ms') ?></span></div>
            <?php endforeach; ?>
            <?php if ($recentLogs === []): ?><p>No logs yet.</p><?php endif; ?>
        </div>
    </section>
</div>