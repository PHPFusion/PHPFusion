(function () {
    'use strict';

    const register = () => {
        if (!window.FusionAdminPage) return;
        window.FusionAdminPage.register('live-url-preview', (element) => {
            const form = document.getElementById(element.dataset.form);
            const output = element.querySelector('[data-url-output]');
            if (!form || !output) return;
            const update = () => {
                const value = (name) => String(form.elements.namedItem(name)?.value || '');
                const port = value('site_port');
                const path = value('site_path');
                output.textContent = `${value('site_protocol')}://${value('site_host')}${port ? `:${port}` : ''}${path.startsWith('/') ? path : `/${path}`}`;
            };
            form.addEventListener('input', update);
            form.addEventListener('change', update);
            update();
        });
        window.FusionAdminPage.mount();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', register, {once: true});
    } else {
        register();
    }
})();
