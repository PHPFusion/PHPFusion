/**
 * Canonical optimistic media preview for AJAX uploads.
 *
 * begin(file)    Show the selected local file immediately.
 * commit(data)   Keep that local preview as the last confirmed screen state.
 * rollback(data) Restore the last confirmed screen state after an error.
 *
 * A successful response URL is deliberately not applied to the target. The
 * newly selected File remains the visual authority until a page refresh. This
 * avoids relative API response URLs and browser caching from replacing a valid
 * local preview with the wrong image.
 */
(function () {
    'use strict';

    const instances = new WeakMap();

    function capture(target) {
        return {
            source: target.getAttribute('src'),
			sourceSet: target.getAttribute('srcset'),
            hidden: target.hidden,
            displayNone: target.classList.contains('d-none')
        };
    }

    function restore(target, state) {
        if (state.source === null || state.source === '') {
            target.removeAttribute('src');
        } else {
            target.setAttribute('src', state.source);
        }
		if (state.sourceSet === null || state.sourceSet === '') {
			target.removeAttribute('srcset');
		} else {
			target.setAttribute('srcset', state.sourceSet);
		}
        target.hidden = state.hidden;
        target.classList.toggle('d-none', state.displayNone);
    }

    class FusionAjaxUploadPreview {
        constructor(target, options = {}) {
            if (!(target instanceof Element)) {
                throw new TypeError('An image or media element is required for an AJAX upload preview.');
            }

            this.target = target;
            this.options = Object.assign({
                onBegin: null,
                onCommit: null,
				onRollback: null,
				onReset: null
            }, options);
            this.confirmedState = capture(target);
            this.confirmedObjectUrl = null;
            this.pendingObjectUrl = null;
            this.pendingFile = null;
            this.lastResult = null;
        }

        begin(file) {
            if (!(file instanceof File)) {
                throw new TypeError('A selected File is required for an AJAX upload preview.');
            }

            if (this.pendingObjectUrl) {
                this.rollback({reason: 'replaced'});
            }

            this.pendingFile = file;
            this.pendingObjectUrl = URL.createObjectURL(file);
			this.target.removeAttribute('srcset');
            this.target.setAttribute('src', this.pendingObjectUrl);
            this.target.hidden = false;
            this.target.classList.remove('d-none');

            const detail = this.detail('begin', {file});
            this.call('onBegin', detail);
            this.emit('begin', detail);

            return this.pendingObjectUrl;
        }

        commit(result = null) {
            if (!this.pendingObjectUrl) {
                return false;
            }

            if (this.confirmedObjectUrl && this.confirmedObjectUrl !== this.pendingObjectUrl) {
                URL.revokeObjectURL(this.confirmedObjectUrl);
            }

            this.confirmedObjectUrl = this.pendingObjectUrl;
            this.confirmedState = capture(this.target);
            this.pendingObjectUrl = null;
            this.pendingFile = null;
            this.lastResult = result;

            const detail = this.detail('commit', {result});
            this.call('onCommit', detail);
            this.emit('commit', detail);

            return true;
        }

        rollback(reason = null) {
            if (!this.pendingObjectUrl) {
                return false;
            }

            const rejectedUrl = this.pendingObjectUrl;
            restore(this.target, this.confirmedState);
            this.pendingObjectUrl = null;
            this.pendingFile = null;
            URL.revokeObjectURL(rejectedUrl);

            const detail = this.detail('rollback', {reason});
            this.call('onRollback', detail);
            this.emit('rollback', detail);

            return true;
        }

		reset(source, result = null) {
			if (this.pendingObjectUrl) {
				URL.revokeObjectURL(this.pendingObjectUrl);
			}
			if (this.confirmedObjectUrl) {
				URL.revokeObjectURL(this.confirmedObjectUrl);
			}

			this.pendingObjectUrl = null;
			this.confirmedObjectUrl = null;
			this.pendingFile = null;
			this.lastResult = result;
			this.target.removeAttribute('srcset');
			if (source === null || source === '') {
				this.target.removeAttribute('src');
			} else {
				this.target.setAttribute('src', String(source));
				this.target.hidden = false;
				this.target.classList.remove('d-none');
			}
			this.confirmedState = capture(this.target);

			const detail = this.detail('reset', {result});
			this.call('onReset', detail);
			this.emit('reset', detail);

			return true;
		}

        currentSource() {
            return this.target.getAttribute('src');
        }

        hasPending() {
            return this.pendingObjectUrl !== null;
        }

        destroy() {
            if (this.pendingObjectUrl) {
                URL.revokeObjectURL(this.pendingObjectUrl);
            }
            if (this.confirmedObjectUrl) {
                URL.revokeObjectURL(this.confirmedObjectUrl);
            }
            this.pendingObjectUrl = null;
            this.confirmedObjectUrl = null;
            this.pendingFile = null;
            instances.delete(this.target);
        }

        detail(state, extra = {}) {
            return Object.assign({
                state,
                instance: this,
                target: this.target,
                source: this.currentSource()
            }, extra);
        }

        call(name, detail) {
            if (typeof this.options[name] === 'function') {
                this.options[name].call(this, detail);
            }
        }

        emit(state, detail) {
            this.target.dispatchEvent(new CustomEvent(`fusionAjaxUploadPreview:${state}`, {
                bubbles: true,
                detail
            }));
        }
    }

    function create(target, options = {}) {
        if (typeof target === 'string') {
            target = document.querySelector(target);
        }
        if (!(target instanceof Element)) {
            return null;
        }

        const existing = instances.get(target);
        if (existing) {
            Object.assign(existing.options, options);
            return existing;
        }

        const instance = new FusionAjaxUploadPreview(target, options);
        instances.set(target, instance);
        return instance;
    }

    function createGroup(targets, options = {}) {
        if (typeof targets === 'string') {
            targets = document.querySelectorAll(targets);
        }

        const group = Array.from(targets || [])
            .map((target) => create(target, options))
            .filter(Boolean);

        return {
            begin(file) {
                group.forEach((preview) => preview.begin(file));
                return this;
            },
            commit(result = null) {
                return group.reduce((committed, preview) => preview.commit(result) || committed, false);
            },
            rollback(reason = null) {
                return group.reduce((rolledBack, preview) => preview.rollback(reason) || rolledBack, false);
            },
			reset(source, result = null) {
				group.forEach((preview) => preview.reset(source, result));
				return group.length > 0;
			},
            hasPending() {
                return group.some((preview) => preview.hasPending());
            },
            destroy() {
                group.forEach((preview) => preview.destroy());
            },
            instances: group
        };
    }

    window.FusionAjaxUploadPreview = FusionAjaxUploadPreview;
    window.fusionAjaxUploadPreview = create;
	window.fusionAjaxUploadPreview.group = createGroup;
}());
