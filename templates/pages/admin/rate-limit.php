<section class="section-heading">
    <p class="eyebrow"><?= e(__('admin.rate_limits')) ?></p>
    <h1>Rate Limit Rules</h1>
</section>

<div class="grid gap-6 lg:grid-cols-2">
    <section class="card">
        <div class="table-like">
            <?php foreach ($rateLimits as $limit): ?>
                <div class="table-row">
                    <div>
                        <strong><?= e($limit['api_name'] ?? 'Global') ?></strong>
                        <p class="card-copy"><?= e($limit['role_name'] ?? 'All Roles') ?></p>
                    </div>
                    <span><?= e((string) $limit['requests_per_minute']) ?>/min · <?= e((string) $limit['requests_per_day']) ?>/day</span>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <section class="card">
        <form class="grid gap-4" method="post" action="<?= e(url('admin/rate-limits')) ?>">
            <?= csrf_field() ?>
            <label class="field">
                <span>API Service</span>
                <select name="api_service_id">
                    <option value="">Global</option>
                    <?php foreach ($services as $service): ?>
                        <option value="<?= e((string) $service['id']) ?>"><?= e($service['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="field">
                <span>Role</span>
                <select name="role_id">
                    <option value="">All Roles</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= e((string) $role['id']) ?>"><?= e($role['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="field"><span>Requests Per Minute</span><input type="number" name="requests_per_minute" value="60"></label>
            <label class="field"><span>Requests Per Day</span><input type="number" name="requests_per_day" value="1000"></label>
            <button class="button button-primary" type="submit">Add Rule</button>
        </form>
    </section>
</div>