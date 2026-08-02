(() => {
    'use strict';

    const bootstrapApi = () => window.bootstrap || window.tabler?.bootstrap;

    document.querySelectorAll('.needs-validation').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    document.addEventListener('click', (event) => {
        const navigation = event.target.closest('.btnNext, .btnPrevious');
        if (!navigation) return;

        const root = navigation.closest('.nav-wrapper');
        const tabs = Array.from(root?.querySelectorAll('[data-bs-toggle="tab"], [data-bs-toggle="pill"]') || []);
        const api = bootstrapApi();
        if (!tabs.length || !api?.Tab) return;

        event.preventDefault();
        const current = Math.max(0, tabs.findIndex((tab) =>
            tab.classList.contains('active') || tab.getAttribute('aria-selected') === 'true'
        ));
        const direction = navigation.classList.contains('btnPrevious') ? -1 : 1;
        api.Tab.getOrCreateInstance(tabs[(current + direction + tabs.length) % tabs.length]).show();
    });

    const updateCollapseLabel = (event, expanded) => {
        if (!event.target.id) return;
        document.querySelectorAll('[data-label-expand][data-label-close][data-bs-target]').forEach((trigger) => {
            if (trigger.getAttribute('data-bs-target') === '#'+event.target.id) {
                trigger.textContent = expanded
                    ? (trigger.dataset.labelClose || 'Close')
                    : (trigger.dataset.labelExpand || 'Expand');
            }
        });
    };
    document.addEventListener('shown.bs.collapse', (event) => updateCollapseLabel(event, true));
    document.addEventListener('hidden.bs.collapse', (event) => updateCollapseLabel(event, false));

    window.addEventListener('load', () => {
        const api = bootstrapApi();
        if (!api?.Modal) return;

        document.querySelectorAll('.modal[data-fusion-modal-trigger]').forEach((modal) => {
            let triggers = [];
            try {
                triggers = document.querySelectorAll(modal.dataset.fusionModalTrigger);
            } catch (error) {
                return;
            }
            triggers.forEach((trigger) => {
                trigger.addEventListener('click', (event) => {
                    event.preventDefault();
                    api.Modal.getOrCreateInstance(modal).show();
                });
            });
        });

        document.querySelectorAll('.modal[data-fusion-modal-auto-show="true"]').forEach((modal) => {
            api.Modal.getOrCreateInstance(modal).show();
        });
    });
})();
