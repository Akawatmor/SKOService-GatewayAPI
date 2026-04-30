<?php

$methodClass = strtolower((string) ($endpoint['method'] ?? 'get'));
?>
<details class="endpoint-row">
    <summary>
        <span class="method-pill method-<?= e($methodClass) ?>"><?= e($endpoint['method'] ?? '') ?></span>
        <span class="endpoint-path"><?= e($endpoint['path'] ?? '') ?></span>
        <span class="endpoint-meta"><?= e(localized_text($endpoint, 'description')) ?></span>
        <span class="endpoint-lock"><?= (int) ($endpoint['auth_required'] ?? 0) === 1 ? '🔒' : '🔓' ?></span>
    </summary>
    <div class="endpoint-detail grid gap-4 lg:grid-cols-2">
        <div class="mini-panel">
            <h4><?= e(__('api.request_schema')) ?></h4>
            <pre><code><?= e((string) ($endpoint['request_schema'] ?? '{}')) ?></code></pre>
        </div>
        <div class="mini-panel">
            <h4><?= e(__('api.response_schema')) ?></h4>
            <pre><code><?= e((string) ($endpoint['response_schema'] ?? '{}')) ?></code></pre>
        </div>
        <div class="mini-panel">
            <h4><?= e(__('api.example_request')) ?></h4>
            <pre><code><?= e((string) ($endpoint['example_request'] ?? '')) ?></code></pre>
        </div>
        <div class="mini-panel">
            <h4><?= e(__('api.example_response')) ?></h4>
            <pre><code><?= e((string) ($endpoint['example_response'] ?? '')) ?></code></pre>
        </div>
    </div>
</details>