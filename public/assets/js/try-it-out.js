document.addEventListener('DOMContentLoaded', () => {
    const prettyJson = (value, fallback = '') => {
        if (!value) {
            return fallback;
        }

        try {
            const parsed = JSON.parse(value);
            return JSON.stringify(parsed, null, 2);
        } catch (error) {
            return value;
        }
    };

    document.querySelectorAll('[data-try-shell]').forEach((shell) => {
        const form = shell.querySelector('[data-try-it-out]');
        const endpointSelect = shell.querySelector('[data-endpoint-select]');
        const methodInput = shell.querySelector('[data-method-input]');
        const output = shell.querySelector('[data-try-output]');
        const bodyField = shell.querySelector('[data-request-body]');
        const headersField = shell.querySelector('[data-request-headers]');
        const queryField = shell.querySelector('[data-request-query]');
        const loadExampleButton = shell.querySelector('[data-load-example]');
        const clearRequestButton = shell.querySelector('[data-clear-request]');
        const submitButton = shell.querySelector('[data-submit-request]');
        const catalogNode = shell.querySelector('[data-endpoint-catalog]');
        const authRequiredLabel = shell.dataset.authRequiredLabel || 'Authentication required';
        const authOptionalLabel = shell.dataset.authOptionalLabel || 'Authentication optional';
        const submitIdleLabel = shell.dataset.submitIdleLabel || 'Send Request';
        const submitLoadingLabel = shell.dataset.submitLoadingLabel || 'Sending...';

        if (!form || !endpointSelect || !methodInput || !catalogNode) {
            return;
        }

        const catalog = JSON.parse(catalogNode.textContent || '[]');
        const endpointMap = new Map(catalog.map((item) => [item.id, item]));
        const selectedMethodPill = shell.querySelector('[data-selected-method-pill]');
        const selectedPath = shell.querySelector('[data-selected-path]');
        const selectedDescription = shell.querySelector('[data-selected-description]');
        const selectedAuth = shell.querySelector('[data-selected-auth]');
        const selectedRequestSchema = shell.querySelector('[data-selected-request-schema]');
        const selectedResponseSchema = shell.querySelector('[data-selected-response-schema]');
        const selectedResponseExample = shell.querySelector('[data-selected-response-example]');

        const hydrateEndpoint = (endpointId, forceBody = false) => {
            const endpoint = endpointMap.get(endpointId);
            if (!endpoint) {
                return;
            }

            methodInput.value = endpoint.method;

            if (selectedMethodPill) {
                selectedMethodPill.textContent = endpoint.method;
                selectedMethodPill.className = `method-pill method-${endpoint.method.toLowerCase()}`;
            }
            if (selectedPath) {
                selectedPath.textContent = endpoint.path;
            }
            if (selectedDescription) {
                selectedDescription.textContent = endpoint.description || '';
            }
            if (selectedAuth) {
                selectedAuth.textContent = endpoint.auth_required ? authRequiredLabel : authOptionalLabel;
            }
            if (selectedRequestSchema) {
                selectedRequestSchema.textContent = prettyJson(endpoint.request_schema, '{}');
            }
            if (selectedResponseSchema) {
                selectedResponseSchema.textContent = prettyJson(endpoint.response_schema, '{}');
            }
            if (selectedResponseExample) {
                selectedResponseExample.textContent = prettyJson(endpoint.example_response, '');
            }
            if (bodyField && (forceBody || bodyField.value.trim() === '')) {
                bodyField.value = prettyJson(endpoint.example_request, '');
            }
        };

        hydrateEndpoint(endpointSelect.value, true);

        endpointSelect.addEventListener('change', () => {
            hydrateEndpoint(endpointSelect.value, true);
        });

        loadExampleButton?.addEventListener('click', () => {
            hydrateEndpoint(endpointSelect.value, true);
        });

        clearRequestButton?.addEventListener('click', () => {
            if (bodyField) {
                bodyField.value = '';
            }
            if (headersField) {
                headersField.value = '{}';
            }
            if (queryField) {
                queryField.value = '{}';
            }
        });

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const formData = new FormData(form);

            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = submitLoadingLabel;
            }

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: { Accept: 'application/json' },
                    credentials: 'same-origin',
                });
                const payload = await response.json();

                if (!output) {
                    return;
                }

                output.classList.remove('hidden');
                output.querySelector('[data-output-status]').textContent = String(payload.status_code || response.status);
                output.querySelector('[data-output-time]').textContent = `${payload.response_time_ms || 0} ms`;
                output.querySelector('[data-output-headers]').textContent = JSON.stringify(payload.response_headers || {}, null, 2);
                output.querySelector('[data-output-body]').textContent = payload.response_body || payload.message || '';
            } catch (error) {
                if (output) {
                    output.classList.remove('hidden');
                    output.querySelector('[data-output-status]').textContent = 'error';
                    output.querySelector('[data-output-time]').textContent = '0 ms';
                    output.querySelector('[data-output-headers]').textContent = '{}';
                    output.querySelector('[data-output-body]').textContent = error instanceof Error ? error.message : 'Unknown error';
                }
            } finally {
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.textContent = submitIdleLabel;
                }
            }
        });
    });
});