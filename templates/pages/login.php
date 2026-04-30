<section class="auth-grid">
    <div class="hero-card hero-main">
        <p class="eyebrow"><?= e(__('auth.login')) ?></p>
        <h1 class="hero-title"><?= e(__('auth.login_title')) ?></h1>
        <p class="hero-copy"><?= e(__('auth.login_copy')) ?></p>
    </div>
    <form class="card form-stack" method="post" action="<?= e(url('login')) ?>">
        <?= csrf_field() ?>
        <label class="field">
            <span><?= e(__('auth.email')) ?></span>
            <input type="email" name="email" value="<?= e((string) old('email')) ?>" required>
        </label>
        <label class="field">
            <span><?= e(__('auth.password')) ?></span>
            <input type="password" name="password" required>
        </label>
        <button class="button button-primary" type="submit"><?= e(__('auth.login')) ?></button>
        <p class="text-sm"><?= e(__('auth.no_account')) ?> <a href="<?= e(url('register')) ?>"><?= e(__('nav.register')) ?></a></p>
    </form>
</section>