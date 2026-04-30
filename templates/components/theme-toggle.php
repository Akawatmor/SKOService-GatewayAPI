<button
    class="theme-toggle"
    type="button"
    data-theme-toggle
    data-label-dark="<?= e(__('theme.dark_mode')) ?>"
    data-label-light="<?= e(__('theme.light_mode')) ?>"
    aria-live="polite"
>
    <span class="theme-toggle__icon" data-theme-icon aria-hidden="true">◐</span>
    <span class="theme-toggle__label" data-theme-label><?= e(__('theme.dark_mode')) ?></span>
</button>