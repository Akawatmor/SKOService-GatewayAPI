<section class="section-heading">
    <p class="eyebrow"><?= e(__('admin.access_requests')) ?></p>
    <h1>Pending Access Requests</h1>
</section>

<section class="card">
    <div class="table-like">
        <?php foreach ($requests as $item): ?>
            <form class="table-row !items-start flex-col lg:!flex-row" method="post" action="<?= e(url('admin/access-requests/' . $item['id'] . '/review')) ?>">
                <?= csrf_field() ?>
                <div class="min-w-0">
                    <strong><?= e($item['api_name']) ?></strong>
                    <p class="card-copy"><?= e($item['user_email']) ?></p>
                    <p class="card-copy"><?= e((string) $item['reason']) ?></p>
                </div>
                <div class="grid gap-2 w-full lg:w-80">
                    <textarea name="reviewer_note" rows="2" placeholder="Reviewer note"></textarea>
                    <div class="flex gap-2">
                        <button class="button button-primary" type="submit" name="status" value="approved">Approve</button>
                        <button class="button button-secondary" type="submit" name="status" value="rejected">Reject</button>
                    </div>
                </div>
            </form>
        <?php endforeach; ?>
        <?php if ($requests === []): ?><p>No pending requests.</p><?php endif; ?>
    </div>
</section>