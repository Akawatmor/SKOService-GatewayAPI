<?php if ($endpoints !== []): ?>
    <?php
    $endpointCatalog = array_values(array_map(static function (array $endpoint): array {
        return [
            'id' => (string) $endpoint['id'],
            'method' => (string) $endpoint['method'],
            'path' => (string) $endpoint['path'],
            'description' => localized_text($endpoint, 'description'),
            'auth_required' => (int) ($endpoint['auth_required'] ?? 0) === 1,
            'request_schema' => (string) ($endpoint['request_schema'] ?? '{}'),
            'response_schema' => (string) ($endpoint['response_schema'] ?? '{}'),
            'example_request' => (string) ($endpoint['example_request'] ?? ''),
            'example_response' => (string) ($endpoint['example_response'] ?? ''),
        ];
    }, $endpoints));
    $firstEndpoint = $endpointCatalog[0];
    ?>
    <section
        class="card try-shell"
        id="try-it-out"
        data-try-shell
        data-auth-required-label="<?= e(__('api.auth_required_label')) ?>"
        data-auth-optional-label="<?= e(__('api.auth_optional_label')) ?>"
        data-submit-idle-label="<?= e(__('button.send')) ?>"
        data-submit-loading-label="<?= e(__('button.sending')) ?>"
    >
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="eyebrow"><?= e(__('api.try_it_out')) ?></p>
                <h3 class="card-title"><?= e(__('api.try_it_out_title')) ?></h3>
                <p class="card-copy"><?= e(__('api.try_helper')) ?></p>
            </div>
            <div class="badge-stack">
                <span class="badge"><?= e(__('api.upstream_hidden')) ?></span>
                <a class="button button-secondary" href="#api-specification"><?= e(__('api.view_spec')) ?></a>
            </div>
        </div>

        <div class="try-layout mt-6">
            <form class="try-it-out-form try-builder" data-try-it-out action="<?= e(url('api/' . $service['slug'] . '/try')) ?>" method="post">
                <?= csrf_field() ?>
                <div class="try-builder__header">
                    <label class="field">
                        <span><?= e(__('api.endpoint')) ?></span>
                        <select name="endpoint_id" data-endpoint-select>
                            <?php foreach ($endpointCatalog as $endpoint): ?>
                                <option value="<?= e($endpoint['id']) ?>"><?= e($endpoint['method'] . ' ' . $endpoint['path']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <input name="method" type="hidden" value="<?= e($firstEndpoint['method']) ?>" data-method-input>
                    <div class="try-builder__quick-actions">
                        <button class="button button-secondary" type="button" data-load-example><?= e(__('button.load_example')) ?></button>
                        <button class="button button-secondary" type="button" data-clear-request><?= e(__('button.clear_request')) ?></button>
                    </div>
                </div>

                <div class="endpoint-spotlight">
                    <span class="method-pill method-<?= e(strtolower($firstEndpoint['method'])) ?>" data-selected-method-pill><?= e($firstEndpoint['method']) ?></span>
                    <div class="endpoint-spotlight__body">
                        <strong data-selected-path><?= e($firstEndpoint['path']) ?></strong>
                        <p class="card-copy" data-selected-description><?= e($firstEndpoint['description']) ?></p>
                    </div>
                    <span class="badge" data-selected-auth><?= e($firstEndpoint['auth_required'] ? __('api.auth_required_label') : __('api.auth_optional_label')) ?></span>
                </div>

                <label class="field">
                    <span><?= e(__('api.request_body')) ?></span>
                    <textarea name="request_body" rows="12" data-request-body></textarea>
                </label>

                <details class="advanced-panel">
                    <summary><?= e(__('api.advanced_options')) ?></summary>
                    <div class="advanced-panel__body">
                        <label class="field">
                            <span><?= e(__('api.headers_json')) ?></span>
                            <textarea name="headers" rows="5" data-request-headers>{}</textarea>
                        </label>
                        <label class="field">
                            <span><?= e(__('api.query_json')) ?></span>
                            <textarea name="query_params" rows="5" data-request-query>{}</textarea>
                        </label>
                    </div>
                </details>

                <button class="button button-primary" type="submit" data-submit-request><?= e(__('button.send')) ?></button>
            </form>

            <aside class="try-reference">
                <div class="mini-panel">
                    <h4><?= e(__('api.selected_endpoint')) ?></h4>
                    <pre data-selected-request-schema><?= e($firstEndpoint['request_schema']) ?></pre>
                </div>
                <div class="mini-panel">
                    <h4><?= e(__('api.response_example')) ?></h4>
                    <pre data-selected-response-example><?= e($firstEndpoint['example_response']) ?></pre>
                </div>
                <div class="mini-panel">
                    <h4><?= e(__('api.response_schema')) ?></h4>
                    <pre data-selected-response-schema><?= e($firstEndpoint['response_schema']) ?></pre>
                </div>
            </aside>
        </div>

        <div class="try-output hidden" data-try-output>
            <div class="grid gap-4 lg:grid-cols-3">
                <div class="mini-panel"><h4><?= e(__('api.status_code')) ?></h4><p data-output-status>200</p></div>
                <div class="mini-panel"><h4><?= e(__('api.response_time')) ?></h4><p data-output-time>0 ms</p></div>
                <div class="mini-panel"><h4><?= e(__('api.headers')) ?></h4><pre data-output-headers>{}</pre></div>
            </div>
            <div class="mini-panel mt-4">
                <h4><?= e(__('api.response_body')) ?></h4>
                <pre data-output-body>{}</pre>
            </div>
        </div>

        <script type="application/json" data-endpoint-catalog><?= json_encode($endpointCatalog, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
    </section>
<?php endif; ?>