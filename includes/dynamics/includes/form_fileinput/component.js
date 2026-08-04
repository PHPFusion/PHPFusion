/**
 * fusionFileinput
 * Native file selection, previews, accessible validation and core preflight.
 */
(function () {
    'use strict';

    const instances = new WeakMap();
    const imageExtensions = new Set(['avif', 'bmp', 'gif', 'jpeg', 'jpg', 'png', 'webp']);

    function formatBytes(bytes) {
        if (!Number.isFinite(bytes) || bytes < 1) return '0 B';
        const units = ['B', 'KB', 'MB', 'GB', 'TB'];
        const index = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
        const value = bytes / Math.pow(1024, index);
        return `${value.toFixed(index === 0 || value >= 10 ? 0 : 1)} ${units[index]}`;
    }

    function extensionOf(name) {
        const dot = String(name || '').lastIndexOf('.');
        return dot > -1 ? String(name).slice(dot + 1).toLowerCase() : '';
    }

    function replaceTokens(message, tokens) {
        return Object.entries(tokens).reduce(
            (text, [key, value]) => text.replaceAll(`{${key}}`, String(value)),
            String(message || '')
        );
    }

    function icon(kind) {
        if (kind === 'error') {
            return '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 8v5m0 3.25v.01M10.3 4.7 2.9 17.5A2 2 0 0 0 4.6 20h14.8a2 2 0 0 0 1.7-2.5L13.7 4.7a2 2 0 0 0-3.4 0Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>';
        }
        if (kind === 'check') {
            return '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m7 12.5 3.2 3.2L17.5 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>';
        }
        return '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M14 2H7a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7l-5-5Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="M14 2v5h5" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>';
    }

    class FusionFileinput {
        constructor(root, overrides = {}) {
            if (!root || instances.has(root)) return instances.get(root);
            this.root = root;
            this.input = root.querySelector('.fusion-fileinput__native');
            this.zone = root.querySelector('.fusion-fileinput__zone');
            this.filesRegion = root.querySelector('.fusion-fileinput__files');
            this.statusRegion = root.querySelector('.fusion-fileinput__status');
            this.errorRegion = root.closest('.fusion-fileinput-field')?.querySelector('.fusion-fileinput__error');
            this.configNode = root.querySelector('[data-fusion-fileinput-config]');
            this.options = Object.assign({}, this.readConfig(), overrides);
            this.files = [];
            this.initialFiles = Array.isArray(this.options.initialFiles) ? this.options.initialFiles : [];
            this.validation = new Map();
            this.objectUrls = new Set();
            this.abortController = null;
            this.validationPromise = null;
            this.submitAfterValidation = false;
            this.form = this.input?.form || null;
            this.boundSubmit = this.onFormSubmit.bind(this);

            if (!this.input) return;
            instances.set(root, this);
            if (this.root.hasAttribute('data-invalid')) {
                const initialError = this.errorRegion?.textContent?.trim() || 'The selected file could not be uploaded.';
                this.root.dataset.globalError = 'true';
                this.input.setCustomValidity(initialError);
            }
            this.bind();
            this.render();
            this.root.dispatchEvent(new CustomEvent('fusionFileinput:ready', {bubbles: true, detail: {instance: this}}));
        }

        readConfig() {
            try {
                return JSON.parse(this.configNode?.textContent || '{}');
            } catch (error) {
                return {};
            }
        }

        bind() {
            this.input.addEventListener('change', () => this.acceptSelection(this.input.files, 'browse'));
            this.input.addEventListener('blur', (event) => this.emit('blur', {originalEvent: event}));
            this.input.addEventListener('cancel', (event) => this.emit('cancel', {files: [], source: 'picker', originalEvent: event}));
            this.zone.addEventListener('blur', (event) => this.emit('blur', {originalEvent: event}));
            this.zone.addEventListener('dragenter', (event) => this.onDragEnter(event));
            this.zone.addEventListener('dragover', (event) => this.onDragEnter(event));
            this.zone.addEventListener('dragleave', (event) => this.onDragLeave(event));
            this.zone.addEventListener('drop', (event) => this.onDrop(event));
            this.zone.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    this.input.click();
                }
            });
            this.root.addEventListener('click', (event) => {
                const remove = event.target.closest('[data-fusion-file-remove]');
                if (remove) this.cancel(Number(remove.dataset.fusionFileRemove));
            });
            this.form?.addEventListener('submit', this.boundSubmit);

            const media = this.root.closest('.fusion-fileinput-field')?.querySelector('[data-fusion-fileinput-media]');
            media?.addEventListener('click', (event) => {
                const item = event.target.closest('[data-file]');
                if (!item) return;
                media.querySelectorAll('[aria-pressed="true"]').forEach((button) => button.setAttribute('aria-pressed', 'false'));
                item.setAttribute('aria-pressed', 'true');
                const hidden = media.parentElement.querySelector(`#${CSS.escape(this.options.id)}-mediaSelector`);
                if (hidden) hidden.value = item.dataset.file || '';
            });
        }

        onDragEnter(event) {
            event.preventDefault();
            if (this.input.disabled) return;
            if (event.dataTransfer) event.dataTransfer.dropEffect = 'copy';
            this.root.dataset.dragging = 'true';
        }

        onDragLeave(event) {
            if (!this.zone.contains(event.relatedTarget)) delete this.root.dataset.dragging;
        }

        onDrop(event) {
            event.preventDefault();
            delete this.root.dataset.dragging;
            if (this.input.disabled) return;
            const files = event.dataTransfer?.files || [];
            this.acceptSelection(files, 'drop');
            this.emit('drop', {files: Array.from(files), originalEvent: event});
        }

        acceptSelection(fileList, source) {
            const incoming = Array.from(fileList || []);
            if (this.options.multiple) {
                const known = new Set(this.files.map((file) => this.fileKey(file)));
                for (const file of incoming) {
                    if (!known.has(this.fileKey(file))) {
                        this.files.push(file);
                        known.add(this.fileKey(file));
                    }
                }
            } else {
                this.files = incoming.slice(0, 1);
            }
            this.syncInput();
            this.validateLocal();
            this.render();
            this.emit('change', {files: this.files.slice(), source});
            if (!this.hasErrors() && this.files.length && this.options.remoteCheck) {
                this.preflight();
            }
        }

        fileKey(file) {
            return `${file.name}:${file.size}:${file.lastModified}`;
        }

        syncInput() {
            if (typeof DataTransfer !== 'function') return;
            const transfer = new DataTransfer();
            this.files.forEach((file) => transfer.items.add(file));
            this.input.files = transfer.files;
        }

        validateLocal() {
            this.validation.clear();
            const messages = this.options.messages || {};
            if (this.files.length > Number(this.options.maxCount || 1)) {
                const message = replaceTokens(messages.tooMany, {count: this.options.maxCount});
                this.files.slice(Number(this.options.maxCount || 1)).forEach((file) => this.validation.set(this.fileKey(file), message));
            }
            const extensions = new Set((this.options.extensions || []).map((value) => String(value).toLowerCase()));
            for (const file of this.files) {
                const key = this.fileKey(file);
                if (file.size > Number(this.options.maxBytes || 0)) {
                    this.validation.set(key, replaceTokens(messages.tooLarge, {name: file.name, size: formatBytes(this.options.maxBytes)}));
                } else if (extensions.size && !extensions.has(extensionOf(file.name))) {
                    this.validation.set(key, replaceTokens(messages.invalidType, {name: file.name}));
                }
            }
            this.applyValidity();
        }

        async preflight() {
            this.abortRemote(false);
            const controller = new AbortController();
            this.abortController = controller;
            this.root.dataset.state = 'checking';
            this.setStatus(this.options.messages?.checking || 'Checking file safety…', 'checking');
            const body = new FormData();
            this.files.forEach((file) => body.append('files[]', file, file.name));
            body.append('policy', JSON.stringify({
                extensions: this.options.extensions || [],
                types: this.options.types || [],
                maxBytes: Number(this.options.maxBytes || 0),
                maxCount: Number(this.options.maxCount || 1),
                maxWidth: Number(this.options.maxWidth || 0),
                maxHeight: Number(this.options.maxHeight || 0)
            }));
            const formToken = this.form?.querySelector('input[name="fusion_token"]');
            const formId = this.form?.querySelector('input[name="form_id"]');
            if (formToken) body.append('fusion_token', formToken.value);
            if (formId) body.append('form_id', formId.value);

            this.validationPromise = fetch(this.options.remoteCheckUrl, {
                method: 'POST',
                body,
                credentials: 'same-origin',
                headers: {'Accept': 'application/json'},
                signal: controller.signal
            }).then(async (response) => {
                const payload = await response.json().catch(() => null);
                if (!payload) throw new Error(this.options.messages?.network);
                const results = payload.data?.files || [];
                if (!response.ok && !results.length) throw new Error(payload.message || this.options.messages?.network);
                results.forEach((result, index) => {
                    const file = this.files[index];
                    if (!file) return;
                    const key = this.fileKey(file);
                    if (!result.valid) this.validation.set(key, result.message || this.options.messages?.invalidType);
                });
                this.applyValidity();
                this.render();
                if (this.hasErrors()) {
                    this.setStatus(payload.message || 'One or more files need attention.', 'error');
                } else {
                    this.setStatus(payload.message || `${this.files.length} file${this.files.length === 1 ? '' : 's'} ready to upload.`, 'success');
                }
                this.emit('validate', {files: results, valid: !this.hasErrors(), remote: true});
                return !this.hasErrors();
            }).catch((error) => {
                if (error.name === 'AbortError') return false;
                if (this.abortController !== controller) return false;
                if (this.options.remoteRequired) {
                    this.setGlobalError(error.message || this.options.messages?.network);
                } else {
                    this.setStatus(error.message || this.options.messages?.network, 'warning');
                }
                this.emit('error', {error, remote: true});
                return !this.options.remoteRequired;
            }).finally(() => {
                if (this.abortController !== controller) return;
                this.abortController = null;
                this.validationPromise = null;
                if (this.submitAfterValidation) {
                    this.submitAfterValidation = false;
                    if (!this.hasErrors()) this.form?.requestSubmit();
                }
            });
            return this.validationPromise;
        }

        onFormSubmit(event) {
            const submitEvent = this.emit('submit', {files: this.files.slice(), originalEvent: event});
            if (submitEvent.defaultPrevented) {
                event.preventDefault();
                return;
            }
            if (this.validationPromise) {
                event.preventDefault();
                this.submitAfterValidation = true;
                this.setStatus(this.options.messages?.pendingSubmit, 'checking');
                return;
            }
            if ((this.options.required && !this.files.length && !this.initialFiles.length) || this.hasErrors()) {
                event.preventDefault();
                if (!this.files.length) this.setGlobalError(this.options.messages?.required);
                this.input.focus({preventScroll: true});
                this.root.scrollIntoView({behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth', block: 'center'});
            }
        }

        cancel(index = null) {
            const removed = index === null ? this.files.slice() : this.files.splice(index, 1);
            if (index === null) this.files = [];
            this.abortRemote(false);
            this.syncInput();
            this.validateLocal();
            this.render();
            this.emit('cancel', {files: Array.isArray(removed) ? removed : [removed], index});
        }

        clear() {
            this.cancel(null);
        }

        abortRemote(announce = true) {
            if (this.abortController) {
                this.abortController.abort();
                this.abortController = null;
                if (announce) this.emit('cancel', {remote: true});
            }
            this.validationPromise = null;
        }

        hasErrors() {
            return this.validation.size > 0 || this.root.dataset.globalError === 'true';
        }

        setGlobalError(message) {
            this.root.dataset.globalError = 'true';
            this.root.dataset.state = 'error';
            this.root.dataset.invalid = 'true';
            this.input.setAttribute('aria-invalid', 'true');
            this.input.setCustomValidity(String(message || 'Invalid file'));
            if (this.errorRegion) {
                this.errorRegion.textContent = String(message || 'Invalid file');
                this.errorRegion.hidden = false;
            }
            this.setStatus(message, 'error');
        }

        applyValidity() {
            delete this.root.dataset.globalError;
            const firstError = this.validation.values().next().value || '';
            this.input.setCustomValidity(firstError);
            this.root.toggleAttribute('data-invalid', Boolean(firstError));
            if (firstError) {
                this.input.setAttribute('aria-invalid', 'true');
            } else {
                this.input.removeAttribute('aria-invalid');
                if (this.errorRegion) {
                    this.errorRegion.textContent = '';
                    this.errorRegion.hidden = true;
                }
            }
        }

        setStatus(message, kind = '') {
            if (!this.statusRegion) return;
            this.statusRegion.textContent = String(message || '');
            this.statusRegion.dataset.kind = kind;
            this.root.dataset.state = kind === 'error' ? 'error' : (this.files.length ? 'ready' : 'empty');
        }

        render() {
            this.revokeObjectUrls();
            const hasFiles = this.files.length > 0 || this.initialFiles.length > 0;
            this.root.dataset.state = this.validationPromise ? 'checking' : (hasFiles ? (this.hasErrors() ? 'error' : 'ready') : 'empty');
            this.root.dataset.count = String(this.files.length);
            if (!this.filesRegion || !this.options.showThumbnails) return;
            this.filesRegion.hidden = !hasFiles;
            this.filesRegion.replaceChildren();
            this.initialFiles.forEach((file) => this.filesRegion.append(this.createInitialRow(file)));
            this.files.forEach((file, index) => this.filesRegion.append(this.createFileRow(file, index)));
        }

        createInitialRow(file) {
            const row = document.createElement('div');
            row.className = 'fusion-fileinput__file fusion-fileinput__file--initial';
            row.setAttribute('role', 'listitem');
            row.innerHTML = `<span class="fusion-fileinput__preview"></span><span class="fusion-fileinput__meta"><strong></strong><small>Current file</small></span><span class="fusion-fileinput__state" aria-label="Current file">${icon('check')}</span>`;
            row.querySelector('strong').textContent = file.name || 'Current file';
            const preview = row.querySelector('.fusion-fileinput__preview');
            if (file.url && imageExtensions.has(extensionOf(file.name || file.url))) {
                const img = document.createElement('img');
                img.src = file.url;
                img.alt = '';
                preview.append(img);
            } else {
                preview.innerHTML = icon('file');
            }
            return row;
        }

        createFileRow(file, index) {
            const key = this.fileKey(file);
            const error = this.validation.get(key) || '';
            const row = document.createElement('div');
            row.className = `fusion-fileinput__file${error ? ' fusion-fileinput__file--error' : ''}`;
            row.setAttribute('role', 'listitem');
            row.innerHTML = `<span class="fusion-fileinput__preview"></span><span class="fusion-fileinput__meta"><strong></strong><small></small></span><span class="fusion-fileinput__state"></span>${this.options.allowRemove ? '<button type="button" class="fusion-fileinput__remove" aria-label="Remove file"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m7 7 10 10M17 7 7 17" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></button>' : ''}`;
            row.querySelector('strong').textContent = file.name;
            row.querySelector('small').textContent = error || formatBytes(file.size);
            const state = row.querySelector('.fusion-fileinput__state');
            state.innerHTML = error ? icon('error') : icon('check');
            state.setAttribute('aria-label', error ? 'File has an error' : 'File is ready');
            const remove = row.querySelector('.fusion-fileinput__remove');
            if (remove) {
                remove.dataset.fusionFileRemove = String(index);
                remove.setAttribute('aria-label', `Remove ${file.name}`);
            }
            const preview = row.querySelector('.fusion-fileinput__preview');
            if (file.type.startsWith('image/') || imageExtensions.has(extensionOf(file.name))) {
                const url = URL.createObjectURL(file);
                this.objectUrls.add(url);
                const img = document.createElement('img');
                img.src = url;
                img.alt = '';
                img.addEventListener('error', () => { preview.innerHTML = icon('file'); }, {once: true});
                preview.append(img);
            } else {
                preview.innerHTML = icon('file');
            }
            return row;
        }

        revokeObjectUrls() {
            this.objectUrls.forEach((url) => URL.revokeObjectURL(url));
            this.objectUrls.clear();
        }

        emit(name, detail = {}) {
            const eventName = `fusionFileinput:${name}`;
            const payload = Object.assign({instance: this, input: this.input}, detail);
            const event = new CustomEvent(eventName, {bubbles: true, cancelable: true, detail: payload});
            this.root.dispatchEvent(event);
            const callback = this.options[`on${name.charAt(0).toUpperCase()}${name.slice(1)}`];
            if (typeof callback === 'function' && callback.call(this, payload) === false) event.preventDefault();
            return event;
        }

        destroy() {
            this.abortRemote(false);
            this.revokeObjectUrls();
            this.form?.removeEventListener('submit', this.boundSubmit);
            instances.delete(this.root);
        }
    }

    function resolveRoot(target) {
        if (typeof target === 'string') target = document.querySelector(target);
        if (target?.matches?.('[data-fusion-fileinput]')) return target;
        return target?.closest?.('[data-fusion-fileinput]') || target?.parentElement?.closest?.('[data-fusion-fileinput]') || null;
    }

    function create(target, options = {}) {
        const root = resolveRoot(target);
        if (!root) return null;
        const existing = instances.get(root);
        if (existing) {
            Object.assign(existing.options, options);
            return existing;
        }
        return new FusionFileinput(root, options);
    }

    function init(root = document) {
        if (root.matches?.('[data-fusion-fileinput]')) create(root);
        root.querySelectorAll?.('[data-fusion-fileinput]').forEach((element) => create(element));
    }

    window.FusionFileinput = FusionFileinput;
    window.fusionFileinput = create;
    window.fusionFileinput.init = init;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => init(), {once: true});
    } else {
        init();
    }

    new MutationObserver((mutations) => {
        mutations.forEach((mutation) => mutation.addedNodes.forEach((node) => {
            if (node.nodeType === Node.ELEMENT_NODE) init(node);
        }));
    }).observe(document.documentElement, {childList: true, subtree: true});
}());
