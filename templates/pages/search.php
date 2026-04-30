<section class="section-heading mb-8">
    <p class="eyebrow"><?= e(__('search.kicker')) ?></p>
    <h1><?= e(__('search.title')) ?></h1>
    <p class="hero-copy"><?= e(__('search.description')) ?></p>
</section>

<div class="grid gap-6 lg:grid-cols-[300px,1fr]">
    <aside class="card h-fit">
        <form method="get" class="grid gap-4">
            <label class="field">
                <span><?= e(__('search.search')) ?></span>
                <input type="text" name="search" value="<?= e($filters['search']) ?>" placeholder="REST, GraphQL, analytics">
            </label>
            <label class="field">
                <span><?= e(__('search.api_type')) ?></span>
                <select name="api_type">
                    <option value=""><?= e(__('search.all')) ?></option>
                    <?php foreach (['REST', 'GraphQL', 'SOAP', 'WebSocket', 'Webhook', 'XML_RSS', 'gRPC', 'File'] as $value): ?>
                        <option value="<?= e($value) ?>" <?= selected($filters['api_type'], $value) ?>><?= e($value) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="field">
                <span><?= e(__('search.standard')) ?></span>
                <select name="standard">
                    <option value=""><?= e(__('search.all')) ?></option>
                    <?php foreach (['OAS', 'GraphQL_SDL', 'Protobuf', 'AsyncAPI', 'WSDL', 'none'] as $value): ?>
                        <option value="<?= e($value) ?>" <?= selected($filters['standard'], $value) ?>><?= e($value) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="field">
                <span><?= e(__('search.status')) ?></span>
                <select name="status">
                    <option value=""><?= e(__('search.all')) ?></option>
                    <?php foreach (['active', 'inactive', 'experimental'] as $value): ?>
                        <option value="<?= e($value) ?>" <?= selected($filters['status'], $value) ?>><?= e($value) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <?php if (is_authenticated()): ?>
                <label class="field">
                    <span><?= e(__('search.visibility')) ?></span>
                    <select name="visibility">
                        <option value=""><?= e(__('search.all')) ?></option>
                        <option value="public" <?= selected($filters['visibility'], 'public') ?>><?= e(__('search.public')) ?></option>
                        <option value="private" <?= selected($filters['visibility'], 'private') ?>><?= e(__('search.private')) ?></option>
                    </select>
                </label>
            <?php endif; ?>
            <label class="field">
                <span><?= e(__('search.sort')) ?></span>
                <select name="sort">
                    <option value="newest" <?= selected($filters['sort'], 'newest') ?>><?= e(__('search.sort_newest')) ?></option>
                    <option value="name" <?= selected($filters['sort'], 'name') ?>><?= e(__('search.sort_name')) ?></option>
                    <option value="most_used" <?= selected($filters['sort'], 'most_used') ?>><?= e(__('search.sort_usage')) ?></option>
                </select>
            </label>
            <button class="button button-primary" type="submit"><?= e(__('button.apply_filters')) ?></button>
        </form>
    </aside>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-2">
        <?php foreach ($apis as $api): ?>
            <?= component('api-card', ['api' => $api]) ?>
        <?php endforeach; ?>
        <?php if ($apis === []): ?>
            <article class="card empty-state md:col-span-2">
                <p><?= e(__('search.no_results')) ?></p>
            </article>
        <?php endif; ?>
        <div class="md:col-span-2">
            <?= component('pagination', ['pagination' => $pagination]) ?>
        </div>
    </section>
</div>