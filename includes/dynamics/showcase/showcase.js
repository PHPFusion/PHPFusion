(() => {
    'use strict';
    document.addEventListener('click', event => {
        const button = event.target.closest('[data-showcase-theme]');
        if (!button) return;
        document.documentElement.dataset.bsTheme = button.dataset.showcaseTheme;
        document.querySelectorAll('[data-showcase-theme]').forEach(item => {
            item.setAttribute('aria-pressed', String(item === button));
        });
    });
})();

// Interactive addon example: the username and selected domain remain separate values.
(() => {
    const close = (root, focus = false) => {
        root.querySelector('.dropdown-menu').hidden = true;
        root.querySelector('.dropdown-menu').classList.remove('show');
        const toggle = root.querySelector('[data-showcase-domain-toggle]');
        toggle.setAttribute('aria-expanded', 'false');
        if (focus) toggle.focus();
    };
    const open = root => {
        const menu = root.querySelector('.dropdown-menu');
        menu.hidden = false; menu.classList.add('show');
        root.querySelector('[data-showcase-domain-toggle]').setAttribute('aria-expanded', 'true');
    };
    document.addEventListener('click', event => {
        const toggle = event.target.closest('[data-showcase-domain-toggle]');
        const option = event.target.closest('[data-showcase-domain]');
        if (toggle) {
            event.preventDefault();
            const root = toggle.closest('.dynamics-showcase__domain');
            toggle.getAttribute('aria-expanded') === 'true' ? close(root) : open(root);
        } else if (option) {
            event.preventDefault();
            const root = option.closest('.dynamics-showcase__domain');
            root.querySelector('[data-domain-label]').textContent = option.dataset.showcaseDomain;
            const input = root.querySelector('[data-domain-value]');
            input.value = option.dataset.showcaseDomain;
            input.dispatchEvent(new Event('change', {bubbles: true}));
            root.querySelectorAll('[data-showcase-domain]').forEach(item => {
                if (item === option) item.setAttribute('aria-current', 'true'); else item.removeAttribute('aria-current');
            });
            close(root, true);
        }
        document.querySelectorAll('.dynamics-showcase__domain').forEach(root => { if (!root.contains(event.target)) close(root); });
    });
    document.addEventListener('keydown', event => {
        const root = event.target.closest('.dynamics-showcase__domain');
        if (!root) return;
        const toggle = root.querySelector('[data-showcase-domain-toggle]');
        if (event.key === 'Escape') { event.preventDefault(); close(root, true); }
        else if (event.key === ' ' && event.target === toggle) { event.preventDefault(); toggle.click(); }
        else if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            event.preventDefault(); open(root);
            const items = [...root.querySelectorAll('[data-showcase-domain]')];
            const index = items.indexOf(event.target);
            const next = index < 0 ? (event.key === 'ArrowDown' ? 0 : items.length - 1) : (index + (event.key === 'ArrowDown' ? 1 : -1) + items.length) % items.length;
            items[next].focus();
        }
    });
    document.addEventListener('focusin', event => {
        document.querySelectorAll('.dynamics-showcase__domain').forEach(root => { if (!root.contains(event.target)) close(root); });
    });
})();