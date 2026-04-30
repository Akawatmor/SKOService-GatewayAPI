document.addEventListener('DOMContentLoaded', () => {
    const root = document.documentElement;
    const nav = document.querySelector('[data-mobile-nav]');
    const toggle = document.querySelector('[data-mobile-toggle]');
    const themeButtons = [...document.querySelectorAll('[data-theme-toggle]')];

    const setTheme = (theme) => {
        root.dataset.theme = theme;

        try {
            localStorage.setItem('gatewayapi-theme', theme);
        } catch (error) {
            console.error(error);
        }

        const meta = document.querySelector('meta[name="theme-color"]');
        if (meta) {
            meta.setAttribute('content', theme === 'dark' ? '#0f172a' : '#f6efe7');
        }

        themeButtons.forEach((button) => {
            const label = theme === 'dark' ? button.dataset.labelLight : button.dataset.labelDark;
            const labelNode = button.querySelector('[data-theme-label]');
            const iconNode = button.querySelector('[data-theme-icon]');

            button.setAttribute('aria-pressed', String(theme === 'dark'));
            if (labelNode) {
                labelNode.textContent = label || '';
            }
            if (iconNode) {
                iconNode.textContent = theme === 'dark' ? '☀' : '☾';
            }
        });
    };

    let preferredTheme = 'light';
    try {
        preferredTheme = localStorage.getItem('gatewayapi-theme')
            || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    } catch (error) {
        preferredTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    setTheme(preferredTheme);

    themeButtons.forEach((button) => {
        button.addEventListener('click', () => {
            setTheme(root.dataset.theme === 'dark' ? 'light' : 'dark');
        });
    });

    if (nav && toggle) {
        toggle.addEventListener('click', () => {
            nav.classList.toggle('is-open');
        });
    }

    if (window.hljs) {
        document.querySelectorAll('pre code').forEach((block) => {
            window.hljs.highlightElement(block);
        });
    }

    document.querySelectorAll('[data-copy-target]').forEach((button) => {
        button.addEventListener('click', async () => {
            const target = document.getElementById(button.getAttribute('data-copy-target'));
            if (!target) {
                return;
            }

            const text = target.textContent || '';
            try {
                await navigator.clipboard.writeText(text);
                button.textContent = 'Copied';
                window.setTimeout(() => {
                    button.textContent = 'Copy';
                }, 1200);
            } catch (error) {
                console.error(error);
            }
        });
    });

    document.querySelectorAll('[data-health-card]').forEach((card) => {
        const button = card.querySelector('[data-health-trigger]');
        const status = card.querySelector('[data-health-status]');
        const time = card.querySelector('[data-health-time]');
        const checked = card.querySelector('[data-health-checked]');
        const target = card.querySelector('[data-health-target]');
        const url = card.getAttribute('data-health-url');

        if (!button || !url) {
            return;
        }

        button.addEventListener('click', async () => {
            const idleLabel = button.dataset.idleLabel || 'Run Health Check';
            const loadingLabel = button.dataset.loadingLabel || 'Checking...';
            button.disabled = true;
            button.textContent = loadingLabel;
            if (status) {
                status.textContent = '...';
                status.className = 'health-status is-checking';
            }

            try {
                const response = await fetch(url, {
                    headers: { Accept: 'application/json' },
                    credentials: 'same-origin',
                });
                const payload = await response.json();

                if (status) {
                    status.textContent = payload.status_code || payload.message || response.status;
                    status.className = `health-status ${payload.healthy ? 'is-healthy' : 'is-unhealthy'}`;
                }
                if (time) {
                    time.textContent = `${payload.response_time_ms || 0} ms`;
                }
                if (checked) {
                    checked.textContent = payload.checked_at || new Date().toISOString();
                }
                if (target && payload.target) {
                    target.textContent = `${payload.target.method} ${payload.target.path}`;
                }
            } catch (error) {
                if (status) {
                    status.textContent = error instanceof Error ? error.message : 'failed';
                    status.className = 'health-status is-unhealthy';
                }
            } finally {
                button.disabled = false;
                button.textContent = idleLabel;
            }
        });
    });

    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js').catch((error) => {
                console.error(error);
            });
        });
    }
});