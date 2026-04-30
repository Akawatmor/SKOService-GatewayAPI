<?php if (($pagination['last_page'] ?? 1) > 1): ?>
    <?php $query = $_GET; ?>
    <nav class="pagination-wrap">
        <?php for ($page = 1; $page <= (int) $pagination['last_page']; $page++): ?>
            <?php $query['page'] = $page; ?>
            <a class="<?= (int) $pagination['page'] === $page ? 'is-active' : '' ?>" href="?<?= e(http_build_query($query)) ?>"><?= e((string) $page) ?></a>
        <?php endfor; ?>
    </nav>
<?php endif; ?>