<section class="auth-grid">
    <div class="hero-card hero-main">
        <p class="eyebrow"><?= e(__('auth.register')) ?></p>
        <h1 class="hero-title"><?= e(__('auth.register_title')) ?></h1>
        <p class="hero-copy"><?= e(__('auth.register_copy')) ?></p>
    </div>
    <form class="card form-stack" method="post" action="<?= e(url('register')) ?>">
        <?= csrf_field() ?>
        <label class="field">
            <span><?= e(__('auth.username')) ?></span>
            <input type="text" name="username" value="<?= e((string) old('username')) ?>" required>
        </label>
        <label class="field">
            <span><?= e(__('auth.email')) ?></span>
            <input type="email" name="email" value="<?= e((string) old('email')) ?>" required>
        </label>
        <label class="field">
            <span><?= e(__('auth.password')) ?></span>
            <input type="password" name="password" required>
        </label>
        <label class="field">
            <span><?= e(__('auth.confirm_password')) ?></span>
            <input type="password" name="confirm_password" required>
        </label>
        <button class="button button-primary" type="submit"><?= e(__('auth.register')) ?></button>
        <p class="text-sm"><?= e(__('auth.has_account')) ?> <a href="<?= e(url('login')) ?>"><?= e(__('nav.login')) ?></a></p>
    </form>
</section>