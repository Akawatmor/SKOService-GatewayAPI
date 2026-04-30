<?php if ($schema['exists'] ?? false): ?>
    <details class="card spec-panel" id="api-specification">
        <summary class="spec-panel__summary">
            <div>
                <p class="eyebrow"><?= e(__('api.schema')) ?></p>
                <h3 class="card-title"><?= e((string) ($schema['filename'] ?? 'schema')) ?></h3>
            </div>
            <span class="button button-secondary spec-panel__button"><?= e(__('api.view_spec')) ?></span>
        </summary>
        <div class="spec-panel__body">
            <div class="flex items-center justify-between gap-3">
                <p class="card-copy"><?= e(__('api.spec_helper')) ?></p>
                <button class="button button-secondary" type="button" data-copy-target="schema-content"><?= e(__('button.copy')) ?></button>
            </div>
            <pre id="schema-content" class="code-block mt-4"><code class="language-<?= e((string) ($schema['language'] ?? 'text')) ?>"><?= e((string) ($schema['content'] ?? '')) ?></code></pre>
        </div>
    </details>
<?php else: ?>
    <section class="card empty-state">
        <p><?= e(__('api.no_schema')) ?></p>
    </section>
<?php endif; ?>