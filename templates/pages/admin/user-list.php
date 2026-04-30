<section class="section-heading">
    <p class="eyebrow"><?= e(__('admin.users')) ?></p>
    <h1>User Management</h1>
</section>

<section class="card">
    <div class="table-like">
        <?php foreach ($users as $item): ?>
            <form class="table-row" method="post" action="<?= e(url('admin/users/' . $item['id'] . '/role')) ?>">
                <?= csrf_field() ?>
                <div>
                    <strong><?= e($item['username']) ?></strong>
                    <p class="card-copy"><?= e($item['email']) ?></p>
                </div>
                <div class="flex items-center gap-3">
                    <select name="role_id">
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= e((string) $role['id']) ?>" <?= selected((string) $item['role_id'], (string) $role['id']) ?>><?= e($role['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="button button-secondary" type="submit">Update</button>
                </div>
            </form>
        <?php endforeach; ?>
    </div>
</section>