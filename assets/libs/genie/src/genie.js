(function (window, document) {
    'use strict';

    const instances = new WeakMap();
    const choiceValues = new WeakMap();
    const phrases = [
        'Fulfilling wishes',
        'Polishing the lamp',
        'Recalibrating cosmic sensors',
        'Channeling cosmic insight',
        'Consulting the archives',
        'Weaving magic into syntax'
    ];

    function namedCallback(name) {
        if (!name) return null;
        return name.split('.').reduce(function (value, key) {
            return value && value[key];
        }, window);
    }

    function editor(id) {
        return window.PHPFusionEditors && id ? window.PHPFusionEditors[id] : null;
    }

    function readEditor(id) {
        const instance = editor(id);
        if (instance && instance.storage && instance.storage.markdown) {
            return instance.storage.markdown.getMarkdown();
        }
        const textarea = document.getElementById(id);
        return textarea ? textarea.value : '';
    }

    function writeEditor(id, value) {
        const instance = editor(id);
        const textarea = document.getElementById(id);
        if (instance && instance.commands) {
            instance.commands.setContent(value || '');
        }
        if (textarea) {
            textarea.value = value || '';
            textarea.dispatchEvent(new Event('input', {bubbles: true}));
            textarea.dispatchEvent(new Event('change', {bubbles: true}));
        }
    }

    function messageFrom(response, fallback) {
        return response && (response.error || response.errors || response.message)
            ? (response.error || response.errors || response.message)
            : fallback;
    }

    function normalizeChoices(response) {
        let values = [];
        if (response && response.results) {
            values = Array.isArray(response.results) ? response.results : [response.results];
        } else if (response && response.improved_text) {
            values = [response.improved_text];
        }

        const choices = [];
        values.forEach(function (value) {
            if (typeof value === 'string') {
                value.split('----').forEach(function (part) {
                    if (part.trim()) choices.push({label: part.trim(), value: part.trim()});
                });
                return;
            }
            if (value && typeof value === 'object') {
                const label = value.label || value.text || value.title || value.value || '';
                const raw = value.value !== undefined ? value.value : (value.raw !== undefined ? value.raw : value);
                if (String(label).trim()) {
                    choices.push({
                        label: String(label).trim(),
                        value: typeof raw === 'string' ? raw : JSON.stringify(raw)
                    });
                }
            }
        });
        return choices;
    }

    function updateToken(response, payload, root) {
        if (!response || !response.fusion_token) return;
        const formId = response.form_id || payload.form_id || '';
        const localFormId = root ? root.querySelector('[data-genie-form-id]') : null;
        const localToken = root ? root.querySelector('[data-genie-fusion-token]') : null;
        if (localFormId && localToken && localFormId.value === formId) {
            localToken.value = response.fusion_token;
            return;
        }
        let selector = 'input[name="fusion_token"]';
        if (formId) {
            const formIdInput = Array.from(document.querySelectorAll('input[name="form_id"]')).find(function (input) {
                return input.value === formId;
            });
            if (formIdInput && formIdInput.form) {
                const token = formIdInput.form.querySelector('input[name="fusion_token"]');
                if (token) token.value = response.fusion_token;
                return;
            }
        }
        const token = document.querySelector(selector);
        if (token) token.value = response.fusion_token;
    }

    class GenieUIController {
        constructor(root) {
            this.root = root;
            this.config = JSON.parse(root.getAttribute('data-genie-config') || '{}');
            this.trigger = document.getElementById(this.config.triggerId);
            this.response = document.getElementById(this.config.responseId);
            this.status = root.querySelector('.genie-ui-status');
            this.timer = null;
            this.originalLabel = this.config.buttonLabel || 'Genie, improve this';
            this.bind();
        }

        bind() {
            if (this.trigger) {
                this.trigger.addEventListener('click', () => this.submit(false));
            }
            const close = this.root.querySelector('.genie-ui-close');
            if (close) close.addEventListener('click', () => this.close());
        }

        callback(name, context) {
            const callback = namedCallback(name);
            return typeof callback === 'function' ? callback(context) : undefined;
        }

        payload() {
            const config = this.config;
            const fields = Object.assign({}, config.fields || {});
            if (!Object.keys(fields).length) fields[config.field] = config.editorId;
            let payload = {};
            Object.keys(fields).forEach(function (field) {
                payload[field] = readEditor(fields[field]);
            });
            payload = Object.assign(payload, config.data || {});
            const extra = this.callback(config.payloadCallback, this.context({payload: payload}));
            if (extra && typeof extra === 'object') payload = Object.assign(payload, extra);
            payload.field = config.field;
            payload.command = config.command;
            if (config.namespace) payload.namespace = config.namespace;
            if (config.taskKey) payload.task_key = config.taskKey;
            const localFormId = this.root.querySelector('[data-genie-form-id]');
            const localToken = this.root.querySelector('[data-genie-fusion-token]');
            if (!payload.form_id && localFormId) payload.form_id = localFormId.value;
            if (!payload.fusion_token && localToken) payload.fusion_token = localToken.value;
            if (!payload.fusion_token) {
                const token = document.querySelector('input[name="fusion_token"]');
                if (token) payload.fusion_token = token.value;
            }
            return payload;
        }

        context(extra) {
            return Object.assign({
                root: this.root,
                response: this.response,
                textarea: document.getElementById(this.config.editorId),
                config: this.config,
                controller: this
            }, extra || {});
        }

        start() {
            if (!this.trigger) return;
            this.trigger.disabled = true;
            this.trigger.classList.add('is-loading');
            const label = this.trigger.querySelector('.genie-ui-trigger-label');
            if (label) label.textContent = 'Granting wishes';
            let index = 0;
            if (this.status) this.status.textContent = phrases[index];
            this.timer = window.setInterval(() => {
                index = (index + 1) % phrases.length;
                if (this.status) this.status.textContent = phrases[index];
            }, 4000);
        }

        stop() {
            if (this.timer) window.clearInterval(this.timer);
            this.timer = null;
            if (this.status) this.status.textContent = '';
            if (!this.trigger) return;
            this.trigger.disabled = false;
            this.trigger.classList.remove('is-loading');
            const label = this.trigger.querySelector('.genie-ui-trigger-label');
            if (label) label.textContent = this.originalLabel;
        }

        async submit(retried) {
            const payload = this.payload();
            this.start();
            let response = {};
            let request;
            try {
                request = await window.fetch(this.config.endpoint, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8', 'X-Requested-With': 'XMLHttpRequest'},
                    body: new URLSearchParams(payload).toString(),
                    credentials: 'same-origin'
                });
                response = await request.json();
                updateToken(response, payload, this.root);
                if (!request.ok) {
                    if (!retried && request.status === 403 && response.error === 'Security protection prevented access.' && response.fusion_token) {
                        this.callback(this.config.responseCallback, this.context({response: response, payload: payload, request: request}));
                        this.stop();
                        return this.submit(true);
                    }
                    throw new Error(messageFrom(response, 'Genie could not complete the request.'));
                }
                this.callback(this.config.responseCallback, this.context({response: response, payload: payload, request: request}));
                this.render(response);
            } catch (error) {
                this.stop();
                this.callback(this.config.responseCallback, this.context({response: response, payload: payload, request: request, error: error}));
                window.alert(error.message || 'Genie ran into an unexpected problem. Please try again.');
            }
        }

        render(response) {
            const choices = normalizeChoices(response);
            if (!choices.length) {
                this.stop();
                window.alert(messageFrom(response, "Genie couldn't formulate suggestions from the current details."));
                return;
            }
            const box = this.response.querySelector('.genie-ui-choices');
            box.replaceChildren();
            choices.forEach((choice) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'btn genie-ui-choice text-start';
                const icon = document.createElement('span');
                icon.className = 'genie-ui-choice-icon';
                icon.setAttribute('aria-hidden', 'true');
                icon.textContent = '+';
                const copy = document.createElement('span');
                copy.className = 'genie-ui-choice-copy';
                if (window.DynamicsMarkdownFilter && typeof window.DynamicsMarkdownFilter.render === 'function') {
                    copy.innerHTML = window.DynamicsMarkdownFilter.render(choice.label);
                } else copy.textContent = choice.label;
                button.append(icon, copy);
                choiceValues.set(button, choice.value);
                button.addEventListener('click', () => this.select(button));
                box.append(button);
            });
            this.response.hidden = false;
            this.stop();
            const first = box.querySelector('button');
            if (first) first.focus();
        }

        select(button) {
            const value = choiceValues.get(button) || '';
            if (this.config.applySelection !== false) writeEditor(this.config.editorId, value);
            this.callback(this.config.selectionCallback, this.context({value: value, choice: button}));
            this.close();
        }

        close() {
            if (this.response) this.response.hidden = true;
            this.stop();
            if (this.trigger) this.trigger.focus();
        }
    }

    function scan(scope) {
        const root = scope || document;
        const candidates = [];
        if (root.matches && root.matches('[data-genie-config]')) candidates.push(root);
        if (root.querySelectorAll) candidates.push.apply(candidates, root.querySelectorAll('[data-genie-config]'));
        candidates.forEach(function (candidate) {
            if (instances.has(candidate)) return;
            try {
                instances.set(candidate, new GenieUIController(candidate));
            } catch (error) {
                console.error('GenieUI could not initialize:', error);
            }
        });
    }

    window.GenieUI = {
        scan: scan,
        getEditor: editor,
        readEditor: readEditor,
        writeEditor: writeEditor
    };

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', () => scan(document));
    else scan(document);
    new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            mutation.addedNodes.forEach(function (node) {
                if (node.nodeType === 1) scan(node);
            });
        });
    }).observe(document.documentElement, {childList: true, subtree: true});
})(window, document);
