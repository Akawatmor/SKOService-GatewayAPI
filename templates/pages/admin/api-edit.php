<?php
$isEditing = is_array($service);
$tagValue = $isEditing ? implode(', ', json_decode((string) $service['tags'], true) ?: []) : '';
$routePrefix = $route_prefix ?? '/admin/apis';
?>
<section class="section-heading">
    <p class="eyebrow"><?= e(__('admin.apis')) ?></p>
    <h1><?= e($section_label ?? ($isEditing ? 'Edit API Service' : 'Create API Service')) ?></h1>
</section>

<section class="card">
    <form class="grid gap-4" method="post" action="<?= e($isEditing ? $routePrefix . '/' . $service['id'] . '/update' : $routePrefix) ?>">
        <?= csrf_field() ?>
        <div class="grid gap-4 lg:grid-cols-2">
            <label class="field"><span>Name</span><input name="name" value="<?= e((string) ($service['name'] ?? '')) ?>" required></label>
            <label class="field"><span>Slug</span><input name="slug" value="<?= e((string) ($service['slug'] ?? '')) ?>" required></label>
            <label class="field"><span>Mode</span><select name="mode"><?php foreach (['catalog', 'proxy'] as $value): ?><option value="<?= e($value) ?>" <?= selected($service['mode'] ?? 'catalog', $value) ?>><?= e($value) ?></option><?php endforeach; ?></select></label>
            <label class="field"><span>API Type</span><select name="api_type"><?php foreach (['REST', 'GraphQL', 'SOAP', 'Webhook', 'WebSocket', 'XML_RSS', 'gRPC', 'File'] as $value): ?><option value="<?= e($value) ?>" <?= selected($service['api_type'] ?? 'REST', $value) ?>><?= e($value) ?></option><?php endforeach; ?></select></label>
            <label class="field"><span>Standard</span><select name="standard"><?php foreach (['OAS', 'GraphQL_SDL', 'Protobuf', 'AsyncAPI', 'WSDL', 'none'] as $value): ?><option value="<?= e($value) ?>" <?= selected($service['standard'] ?? 'none', $value) ?>><?= e($value) ?></option><?php endforeach; ?></select></label>
            <label class="field"><span>Status</span><select name="status"><?php foreach (['active', 'inactive', 'experimental'] as $value): ?><option value="<?= e($value) ?>" <?= selected($service['status'] ?? 'active', $value) ?>><?= e($value) ?></option><?php endforeach; ?></select></label>
            <label class="field"><span>Version</span><input name="version" value="<?= e((string) ($service['version'] ?? '1.0')) ?>"></label>
            <label class="field"><span>Base URL</span><input name="base_url" value="<?= e((string) ($service['base_url'] ?? '')) ?>"></label>
            <label class="field"><span>Health Check Method</span><select name="health_check_method"><?php foreach (['GET', 'HEAD'] as $value): ?><option value="<?= e($value) ?>" <?= selected($service['health_check_method'] ?? 'GET', $value) ?>><?= e($value) ?></option><?php endforeach; ?></select></label>
            <label class="field"><span>Health Check Path</span><input name="health_check_path" value="<?= e((string) ($service['health_check_path'] ?? '')) ?>" placeholder="/health"></label>
        </div>
        <label class="field"><span>Description (TH)</span><textarea name="description_th" rows="4"><?= e((string) ($service['description_th'] ?? '')) ?></textarea></label>
        <label class="field"><span>Description (EN)</span><textarea name="description_en" rows="4"><?= e((string) ($service['description_en'] ?? '')) ?></textarea></label>
        <label class="field"><span>Tags (comma separated)</span><input name="tags" value="<?= e($tagValue) ?>"></label>
        <label class="field"><span><input type="checkbox" name="is_public" value="1" <?= checked((int) ($service['is_public'] ?? 1) === 1) ?>> Public API</span></label>
        <button class="button button-primary w-fit" type="submit">Save Service</button>
    </form>
    <?php if ($isEditing): ?>
        <form class="mt-4" method="post" action="<?= e($routePrefix . '/' . $service['id'] . '/delete') ?>">
            <?= csrf_field() ?>
            <button class="button button-secondary" type="submit">Delete Service</button>
        </form>
    <?php endif; ?>
</section>

<?php if ($isEditing): ?>
    <div class="grid gap-6 mt-6 xl:grid-cols-2">
        <section class="card">
            <p class="eyebrow">Endpoints</p>
            <form class="grid gap-3 mt-4" method="post" action="<?= e($routePrefix . '/' . $service['id'] . '/endpoints') ?>">
                <?= csrf_field() ?>
                <div class="grid gap-3 lg:grid-cols-2">
                    <label class="field"><span>Method</span><input name="method" value="GET"></label>
                    <label class="field"><span>Path</span><input name="path" placeholder="/users/{id}"></label>
                </div>
                <label class="field"><span>Description (TH)</span><textarea name="description_th" rows="2"></textarea></label>
                <label class="field"><span>Description (EN)</span><textarea name="description_en" rows="2"></textarea></label>
                <label class="field"><span>Request Schema</span><textarea name="request_schema" rows="3">{}</textarea></label>
                <label class="field"><span>Response Schema</span><textarea name="response_schema" rows="3">{}</textarea></label>
                <label class="field"><span>Example Request</span><textarea name="example_request" rows="3"></textarea></label>
                <label class="field"><span>Example Response</span><textarea name="example_response" rows="3"></textarea></label>
                <label class="field"><span><input type="checkbox" name="auth_required" value="1"> Requires Auth</span></label>
                <button class="button button-primary" type="submit">Add Endpoint</button>
            </form>
            <div class="table-like mt-6">
                <?php foreach ($endpoints as $endpoint): ?>
                    <div class="table-row">
                        <div><strong><?= e($endpoint['method'] . ' ' . $endpoint['path']) ?></strong><p class="card-copy"><?= e(localized_text($endpoint, 'description')) ?></p></div>
                        <form method="post" action="<?= e($routePrefix . '/' . $service['id'] . '/endpoints/' . $endpoint['id'] . '/delete') ?>">
                            <?= csrf_field() ?>
                            <button class="button button-secondary" type="submit">Delete</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="grid gap-6">
            <section class="card">
                <p class="eyebrow">Schema File</p>
                <form class="grid gap-3 mt-4" method="post" enctype="multipart/form-data" action="<?= e($routePrefix . '/' . $service['id'] . '/schema') ?>">
                    <?= csrf_field() ?>
                    <label class="field"><span>Schema File Upload</span><input type="file" name="schema_file"></label>
                    <label class="field"><span>Schema Filename</span><input name="schema_filename" value="<?= e((string) ($service['schema_path'] ?? $service['slug'] . '.txt')) ?>"></label>
                    <label class="field"><span>Or Paste Schema Content</span><textarea name="schema_content" rows="8"></textarea></label>
                    <button class="button button-primary" type="submit">Save Schema</button>
                </form>
            </section>
            <section class="card">
                <p class="eyebrow">Snapshots</p>
                <form method="post" action="<?= e($routePrefix . '/' . $service['id'] . '/snapshots/generate') ?>">
                    <?= csrf_field() ?>
                    <button class="button button-primary" type="submit">Generate Snapshot</button>
                </form>
                <div class="table-like mt-4">
                    <?php foreach ($snapshots as $snapshot): ?>
                        <div class="table-row"><strong><?= e($snapshot['filename']) ?></strong><span><?= e((string) $snapshot['size_bytes']) ?> bytes</span></div>
                    <?php endforeach; ?>
                    <?php if ($snapshots === []): ?><p>No snapshots yet.</p><?php endif; ?>
                </div>
            </section>
            <?php if ((string) ($service['health_check_path'] ?? '') !== '' || (int) ($service['last_health_checked_at'] ?? 0) !== 0): ?>
                <section class="card">
                    <p class="eyebrow">Health Check</p>
                    <div class="table-like mt-4">
                        <div class="table-row"><strong>Target</strong><span><?= e(($service['health_check_method'] ?? 'GET') . ' ' . ($service['health_check_path'] ?? '/health')) ?></span></div>
                        <div class="table-row"><strong>Last Status</strong><span><?= e((string) ($service['last_health_status_code'] ?? 'never')) ?></span></div>
                        <div class="table-row"><strong>Last Response Time</strong><span><?= e((string) ($service['last_health_response_time_ms'] ?? 0)) ?> ms</span></div>
                        <div class="table-row"><strong>Last Checked</strong><span><?= e((string) ($service['last_health_checked_at'] ?? 'not checked')) ?></span></div>
                    </div>
                </section>
            <?php endif; ?>
        </section>
    </div>
<?php endif; ?>