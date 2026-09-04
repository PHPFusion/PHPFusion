/** Keep floating group labels aligned with input text after prepended addons. */
(() => {
    'use strict';
    const tracked = new WeakSet();
    const align = field => {
        const input = field.querySelector(':scope > .dynamics-form-text__group > .dynamics-form-text__input');
        if (!input || !field.getClientRects().length) return;
        const box = field.getBoundingClientRect();
        const control = input.getBoundingClientRect();
        const style = getComputedStyle(input);
        const rtl = style.direction === 'rtl';
        const inset = rtl
            ? box.right - control.right + parseFloat(style.paddingRight)
            : control.left - box.left + parseFloat(style.paddingLeft);
        const value = `${Math.max(0, inset)}px`;
        if (field.style.getPropertyValue('--dynamics-form-text-label-inset') !== value) {
            field.style.setProperty('--dynamics-form-text-label-inset', value);
        }
    };
    const observe = scope => {
        const fields = [...scope.querySelectorAll('.dynamics-form-text.form-floating')];
        if (scope.matches?.('.dynamics-form-text.form-floating')) fields.push(scope);
        fields.forEach(field => {
            if (tracked.has(field)) return;
            const group = field.querySelector(':scope > .dynamics-form-text__group');
            if (!group) return;
            tracked.add(field);
            const observer = new ResizeObserver(() => {
                if (!field.isConnected) { observer.disconnect(); tracked.delete(field); return; }
                align(field);
            });
            observer.observe(field);
            [...group.children].forEach(child => observer.observe(child));
            align(field);
        });
    };
    const start = () => {
        observe(document);
        new MutationObserver(records => records.forEach(record => record.addedNodes.forEach(node => {
            if (node.nodeType === 1) observe(node);
        }))).observe(document.body, {childList: true, subtree: true});
    };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start, {once: true});
    else start();
})();