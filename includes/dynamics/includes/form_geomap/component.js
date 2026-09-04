(function () {
    'use strict';

    const initialized = new WeakSet();

    function updateSourceConfig(state, countryValue) {
        let config = {};
        try {
            config = JSON.parse(state.getAttribute('data-dynamics-combobox') || '{}');
        } catch (error) {
            config = {};
        }
        config.remote = config.remote && typeof config.remote === 'object' ? config.remote : {};
        config.remote.params = config.remote.params && typeof config.remote.params === 'object'
            ? config.remote.params
            : {};
        config.remote.params.id = countryValue;
        state.setAttribute('data-dynamics-combobox', JSON.stringify(config));
    }

    function comboboxInstance(state) {
        const api = window.PHPFusionDynamicsCombobox;
        if (!api) {
            return null;
        }
        api.initialize(state);
        return api.getInstance(state);
    }

    function syncState(country, state, resetValue) {
        const countryValue = String(country.value || '');
        const fallback = state.parentElement
            ? state.parentElement.querySelector('[data-geomap-state-fallback]')
            : null;
        updateSourceConfig(state, countryValue);

        const instance = comboboxInstance(state);
        if (instance) {
            instance.config.remote.params = instance.config.remote.params || {};
            instance.config.remote.params.id = countryValue;
            instance.remoteItems = [];
            instance.remoteLoaded = false;
            instance.loadError = false;
            if (resetValue) {
                instance.setValue('');
            }
            instance.close(false);
        } else if (resetValue) {
            state.value = '';
            state.dispatchEvent(new Event('change', {bubbles: true}));
        }

        state.disabled = countryValue === '';
        if (fallback) {
            if (countryValue === '') {
                fallback.setAttribute('name', fallback.getAttribute('data-input-name') || state.name);
            } else {
                fallback.removeAttribute('name');
            }
        }
        if (instance) {
            instance.updateDisabled();
            instance.render();
        }
    }

    function enhance(country) {
        if (initialized.has(country)) {
            return;
        }

        const stateId = country.getAttribute('data-geomap-state-target');
        const state = stateId ? document.getElementById(stateId) : null;
        if (!state) {
            return;
        }

        initialized.add(country);
        syncState(country, state, false);
        country.addEventListener('change', function () {
            syncState(country, state, true);
        });
    }

    function initialize(root) {
        const scope = root && root.querySelectorAll ? root : document;
        if (scope.matches && scope.matches('[data-geomap-country]')) {
            enhance(scope);
        }
        scope.querySelectorAll('[data-geomap-country]').forEach(enhance);
    }

    function boot() {
        initialize(document);
        const observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                mutation.addedNodes.forEach(function (node) {
                    if (node.nodeType === 1) {
                        initialize(node);
                    }
                });
            });
        });
        observer.observe(document.body, {childList: true, subtree: true});
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot, {once: true});
    } else {
        boot();
    }
})();

