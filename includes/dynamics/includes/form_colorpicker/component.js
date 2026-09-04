/** Dynamics Color Picker. Original vanilla-JS implementation; no runtime dependencies. */
(function () {
    'use strict';
    const clamp = (n, min = 0, max = 1) => Math.max(min, Math.min(max, n));
    const round = (n, places = 2) => Number(n.toFixed(places));
    const hex = n => Math.round(n).toString(16).padStart(2, '0').toUpperCase();
    function hsvToRgb({h, s, v, a = 1}) {
        const f = n => { const k = (n + h / 60) % 6; return Math.round(255 * (v - v * s * Math.max(0, Math.min(k, 4 - k, 1)))); };
        return {r: f(5), g: f(3), b: f(1), a};
    }
    function rgbToHsv({r, g, b, a = 1}) {
        r /= 255; g /= 255; b /= 255;
        const max = Math.max(r, g, b), min = Math.min(r, g, b), d = max - min;
        let h = 0;
        if (d) h = (max === r ? (g - b) / d : max === g ? (b - r) / d + 2 : (r - g) / d + 4) * 60;
        return {h: (h + 360) % 360, s: max ? d / max : 0, v: max, a};
    }
    function hslToRgb(h, s, l, a = 1) {
        const v = l + s * Math.min(l, 1 - l);
        return hsvToRgb({h: (h % 360 + 360) % 360, s: v ? 2 * (1 - l / v) : 0, v, a});
    }
    function parseColor(raw) {
        const value = String(raw).trim();
        let match = /^#?([\da-f]{3,4}|[\da-f]{6}|[\da-f]{8})$/i.exec(value);
        if (match) {
            let digits = match[1];
            if (digits.length < 5) digits = [...digits].map(c => c + c).join('');
            return {r: parseInt(digits.slice(0, 2), 16), g: parseInt(digits.slice(2, 4), 16), b: parseInt(digits.slice(4, 6), 16), a: digits.length === 8 ? parseInt(digits.slice(6), 16) / 255 : 1};
        }
        match = /^(rgba?|hsla?)\(([^)]+)\)$/i.exec(value);
        if (match) {
            const parts = match[2].trim().split(/[\s,\/]+/);
            if ((parts.length === 3 || parts.length === 4) && parts.every(p => /^[-+]?(?:\d*\.)?\d+(?:%|deg)?$/.test(p))) {
                const a = parts[3] === undefined ? 1 : clamp(parseFloat(parts[3]) / (parts[3].endsWith('%') ? 100 : 1));
                if (match[1].toLowerCase().startsWith('rgb') && parts.slice(0, 3).every(p => !p.endsWith('deg'))) {
                    const [r, g, b] = parts.slice(0, 3).map(p => Math.round(clamp(parseFloat(p) / (p.endsWith('%') ? 100 : 255)) * 255));
                    return {r, g, b, a};
                }
                if (match[1].toLowerCase().startsWith('hsl') && parts[1].endsWith('%') && parts[2].endsWith('%')) return hslToRgb(parseFloat(parts[0]), clamp(parseFloat(parts[1]) / 100), clamp(parseFloat(parts[2]) / 100), a);
            }
        }
        // Resolve concrete CSS colors into sRGB; reject context-dependent values.
        if (typeof document !== 'undefined' && value && !/var\(|currentcolor|inherit|initial|unset|revert/i.test(value) && CSS.supports('color', value)) {
            const canvas = document.createElement('canvas'); canvas.width = canvas.height = 1;
            const ctx = canvas.getContext('2d', {willReadFrequently: true});
            ctx.fillStyle = value; ctx.fillRect(0, 0, 1, 1);
            const [r, g, b, a] = ctx.getImageData(0, 0, 1, 1).data;
            return {r, g, b, a: a / 255};
        }
        return null;
    }
    function formatColor(color, type = 'HEX', includeAlpha = true) {
        const {r, g, b} = color, a = includeAlpha ? round(color.a, 3) : 1;
        if (type === 'HEX') return '#' + hex(r) + hex(g) + hex(b) + (a < 1 ? hex(color.a * 255) : '');
        if (type === 'RGB') return a < 1 ? `rgba(${r}, ${g}, ${b}, ${a})` : `rgb(${r}, ${g}, ${b})`;
        if (type === 'CSS') return `rgb(${r} ${g} ${b}${a < 1 ? ` / ${a}` : ''})`;
        const hsv = rgbToHsv(color), l = hsv.v * (1 - hsv.s / 2);
        const s = l === 0 || l === 1 ? 0 : (hsv.v - l) / Math.min(l, 1 - l);
        return `hsl(${round(hsv.h, 1)} ${round(s * 100, 1)}% ${round(l * 100, 1)}%${a < 1 ? ` / ${a}` : ''})`;
    }
    if (typeof module !== 'undefined' && module.exports) module.exports = {parseColor, formatColor, hsvToRgb, rgbToHsv};
    if (typeof document === 'undefined') return;
    if (window.DynamicsColorPicker) { window.DynamicsColorPicker.init(document); return; }
    const instances = new WeakMap();
    let active = null;
    class ColorPicker {
        constructor(root) {
            this.root = root;
            const find = part => root.querySelector(`[data-color-${part}]`);
            this.input = find('value'); this.trigger = root.querySelector('.dynamics-colorpicker__trigger');
            this.label = this.trigger.getAttribute('aria-label');
            this.panel = root.querySelector('.dynamics-colorpicker__panel');
            this.palette = find('palette'); this.cursor = find('cursor'); this.hue = find('hue');
            this.alpha = find('alpha'); this.opacity = find('opacity'); this.text = find('text');
            this.type = find('type'); this.eye = find('eye'); this.status = find('status');
            this.format = root.dataset.format;
            this.initialValue = this.input.value;
            const color = parseColor(this.input.value);
            this.state = rgbToHsv(color || {r: 113, g: 113, b: 123, a: 1});
            this.empty = !this.input.value.trim(); this.render(false);
            if (color) this.input.value = formatColor(color, this.format);
            else if (!this.empty) {
                this.root.querySelector('[data-color-summary]').textContent = this.input.value;
                this.root.style.setProperty('--picker-color', 'transparent');
                this.invalid('Enter a valid color.');
            }
            this.trigger.addEventListener('click', () => this.opened ? this.close(true) : this.open());
            this.panel.addEventListener('toggle', event => { if (event.newState === 'closed') this.afterClose(); });
            this.palette.addEventListener('pointerdown', event => {
                if (event.button !== 0) return;
                event.preventDefault(); this.palette.focus(); this.palette.setPointerCapture(event.pointerId); this.move(event);
            });
            this.palette.addEventListener('pointermove', event => { if (this.palette.hasPointerCapture(event.pointerId)) this.move(event); });
            this.palette.addEventListener('pointerup', event => {
                if (this.palette.hasPointerCapture(event.pointerId)) this.palette.releasePointerCapture(event.pointerId);
                this.emit('change');
            });
            this.palette.addEventListener('keydown', event => {
                if (!['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End'].includes(event.key)) return;
                event.preventDefault(); const step = event.shiftKey ? .1 : .01;
                if (event.key === 'ArrowLeft') this.state.s = clamp(this.state.s - step);
                if (event.key === 'ArrowRight') this.state.s = clamp(this.state.s + step);
                if (event.key === 'ArrowUp') this.state.v = clamp(this.state.v + step);
                if (event.key === 'ArrowDown') this.state.v = clamp(this.state.v - step);
                if (event.key === 'Home') this.state.s = 0;
                if (event.key === 'End') this.state.s = 1;
                this.commit(true);
            });
            this.hue.addEventListener('input', () => { this.state.h = Number(this.hue.value); this.commit(); });
            this.alpha.addEventListener('input', () => { this.state.a = Number(this.alpha.value) / 100; this.commit(); });
            [this.hue, this.alpha].forEach(slider => slider.addEventListener('change', () => this.emit('change')));
            this.opacity.addEventListener('input', () => {
                if (this.opacity.value === '' || !this.opacity.validity.valid) return;
                this.state.a = Number(this.opacity.value) / 100; this.commit();
            });
            this.opacity.addEventListener('change', () => { this.opacity.value = Math.round(this.state.a * 100); this.emit('change'); });
            this.text.addEventListener('change', () => this.readText());
            this.text.addEventListener('keydown', event => { if (event.key === 'Enter') { event.preventDefault(); this.readText(); } });
            if (this.type) this.type.addEventListener('change', () => { this.format = this.type.value; if (this.empty) this.render(false); else this.commit(true); });
            this.eye.disabled = !window.EyeDropper || !window.isSecureContext;
            this.eye.title = this.eye.disabled ? 'Screen picking requires a supported browser on HTTPS or localhost.' : 'Pick color from screen';
            this.eye.addEventListener('click', async () => {
                this.eye.disabled = true;
                try {
                    const result = await new window.EyeDropper().open();
                    this.state = rgbToHsv({...parseColor(result.sRGBHex), a: this.state.a}); this.commit(true);
                } catch (error) { if (error.name !== 'AbortError') this.status.textContent = 'Screen picking failed. Try again or enter a color.'; }
                finally { this.eye.disabled = false; if (this.opened) this.eye.focus(); }
            });
            this.input.addEventListener('change', () => { if (!this.emitting) this.setValue(this.input.value, false); });
            this.root.closest('form')?.addEventListener('reset', () => {
                // Hidden inputs update their defaultValue when .value changes.
                // Restore the captured server value rather than relying on native reset.
                setTimeout(() => { this.format = root.dataset.format; if (this.type) this.type.value = this.format; this.setValue(this.initialValue, false); this.close(false); }, 0);
            });
        }
        emit(name) { this.emitting = true; this.input.dispatchEvent(new Event(name, {bubbles: true})); this.emitting = false; }
        invalid(message) { this.text.setAttribute('aria-invalid', 'true'); this.trigger.setAttribute('aria-invalid', 'true'); this.status.textContent = message; }
        readText() {
            const parsed = parseColor(this.text.value);
            if (!parsed) { this.invalid('Enter a valid HEX, RGB, CSS or HSL color.'); return; }
            // Alpha has its own field; explicit alpha in pasted text takes precedence.
            const explicitAlpha = parsed.a < 1 || /^#?(?:[\da-f]{4}|[\da-f]{8})$/i.test(this.text.value.trim()) || /\/|rgba\(|hsla\(/i.test(this.text.value);
            if (!explicitAlpha) parsed.a = this.state.a;
            this.state = rgbToHsv(parsed); this.commit(true);
        }
        setValue(value, notify = true) {
            const color = parseColor(value);
            if (!String(value).trim()) { this.empty = true; this.input.value = ''; this.text.removeAttribute('aria-invalid'); this.trigger.removeAttribute('aria-invalid'); this.status.textContent = ''; this.render(false); if (notify) { this.emit('input'); this.emit('change'); } return true; }
            if (!color) { this.invalid('Enter a valid color.'); return false; }
            this.state = rgbToHsv(color); this.commit(notify, notify); return true;
        }
        commit(change = false, notify = true) {
            this.empty = false; this.text.removeAttribute('aria-invalid'); this.trigger.removeAttribute('aria-invalid'); this.status.textContent = '';
            this.render(true); if (notify) this.emit('input'); if (change && notify) this.emit('change');
        }
        render(write) {
            const color = hsvToRgb(this.state), serialized = formatColor(color, this.format);
            this.root.style.setProperty('--picker-color', this.empty ? 'transparent' : serialized);
            this.panel.style.setProperty('--picker-hue', `hsl(${this.state.h} 100% 50%)`);
            this.panel.style.setProperty('--picker-opaque', formatColor(color, 'HEX', false));
            this.root.querySelector('[data-color-format]').textContent = this.format;
            this.root.querySelector('[data-color-summary]').textContent = this.empty ? this.root.dataset.placeholder : serialized;
            this.trigger.setAttribute('aria-label', `${this.label}: ${this.format} ${this.empty ? this.root.dataset.placeholder : serialized}`);
            this.hue.value = this.state.h; this.alpha.value = this.state.a * 100; this.opacity.value = Math.round(this.state.a * 100);
            this.text.value = formatColor(color, this.format, false); this.text.setAttribute('aria-label', `${this.format} color value`);
            this.cursor.style.left = `${this.state.s * 100}%`; this.cursor.style.top = `${(1 - this.state.v) * 100}%`;
            this.palette.setAttribute('aria-valuenow', Math.round(this.state.s * 100));
            this.palette.setAttribute('aria-valuetext', `Saturation ${Math.round(this.state.s * 100)}%, brightness ${Math.round(this.state.v * 100)}%`);
            if (write) this.input.value = serialized;
        }
        move(event) {
            const rect = this.palette.getBoundingClientRect();
            this.state.s = clamp((event.clientX - rect.left) / rect.width); this.state.v = 1 - clamp((event.clientY - rect.top) / rect.height); this.commit();
        }
        position() {
            if (!this.opened) return;
            const rect = this.trigger.getBoundingClientRect(), pad = 8, width = Math.min(300, window.innerWidth - pad * 2);
            this.panel.style.width = `${width}px`; this.panel.style.maxHeight = `${window.innerHeight - pad * 2}px`;
            const height = this.panel.getBoundingClientRect().height, below = rect.bottom + pad;
            this.panel.style.left = `${clamp(rect.left, pad, window.innerWidth - width - pad)}px`;
            this.panel.style.top = `${Math.max(pad, below + height <= window.innerHeight - pad ? below : rect.top - height - pad)}px`;
        }
        open() {
            if (this.trigger.disabled) return;
            if (active && active !== this) active.close(false);
            active = this; this.opened = true; this.panel.hidden = false;
            if (this.panel.showPopover) this.panel.showPopover();
            this.trigger.setAttribute('aria-expanded', 'true'); this.position(); this.palette.focus({preventScroll: true});
        }
        afterClose() { this.opened = false; this.panel.hidden = true; this.trigger.setAttribute('aria-expanded', 'false'); if (active === this) active = null; }
        close(focus) { if (this.panel.hidePopover && this.panel.matches(':popover-open')) this.panel.hidePopover(); this.afterClose(); if (focus) this.trigger.focus({preventScroll: true}); }
    }
    function init(scope = document) {
        const roots = [...scope.querySelectorAll('[data-colorpicker]')]; if (scope.matches?.('[data-colorpicker]')) roots.unshift(scope);
        roots.forEach(root => { if (!instances.has(root)) instances.set(root, new ColorPicker(root)); });
    }
    window.DynamicsColorPicker = {init, setValue(root, value) { init(root); return instances.get(root)?.setValue(value); }, getValue(root) { return instances.get(root)?.input.value; }};
    document.addEventListener('pointerdown', event => { if (active && !active.root.contains(event.target)) active.close(false); });
    document.addEventListener('keydown', event => { if (active && event.key === 'Escape') { event.preventDefault(); active.close(true); } }, true);
    document.addEventListener('focusin', event => { if (active && !active.root.contains(event.target)) active.close(false); });
    window.addEventListener('resize', () => active?.position()); window.addEventListener('scroll', () => active?.position(), true);
    const start = () => {
        init(); new MutationObserver(records => {
            records.forEach(record => record.addedNodes.forEach(node => { if (node.nodeType === 1) init(node); }));
            if (active && !active.root.isConnected) active.close(false);
        }).observe(document.body, {childList: true, subtree: true});
    };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start, {once: true}); else start();
})();

