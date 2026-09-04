(() => {
    'use strict';
    if (window.PHPFusionFieldHelp) return;
    window.PHPFusionFieldHelp = true;
    let active, popup, positioner, timer, sequence = 0;
    const selector = '[data-dynamics-help]';
    function close() {
        clearTimeout(timer);
        if (active && popup) {
            const ids = (active.getAttribute('aria-describedby') || '').split(/\s+/).filter(id => id && id !== popup.id);
            if (ids.length) active.setAttribute('aria-describedby', ids.join(' '));
            else active.removeAttribute('aria-describedby');
        }
        positioner?.destroy();
        popup?.remove();
        active = popup = positioner = null;
    }
    function show(trigger) {
        clearTimeout(timer);
        if (active === trigger) return;
        close();
        active = trigger;
        trigger.removeAttribute('title');
        popup = document.createElement('div');
        popup.id = `dynamics-help-${++sequence}`;
        popup.className = 'dynamics-field-help__popup';
        popup.setAttribute('role', 'tooltip');
        popup.textContent = trigger.dataset.dynamicsHelp;
        document.body.append(popup);
        trigger.setAttribute('aria-describedby', [trigger.getAttribute('aria-describedby'), popup.id].filter(Boolean).join(' '));
        if (window.Popper) {
            positioner = window.Popper.createPopper(trigger, popup, {
                placement: 'top', strategy: 'fixed', modifiers: [
                    {name: 'offset', options: {offset: [0, 8]}},
                    {name: 'preventOverflow', options: {padding: 12}}
                ]
            });
        } else {
            const rect = trigger.getBoundingClientRect();
            popup.style.left = `${Math.max(12, Math.min(rect.left, innerWidth - popup.offsetWidth - 12))}px`;
            popup.style.top = `${Math.max(12, Math.min(rect.bottom + 8, innerHeight - popup.offsetHeight - 12))}px`;
        }
        popup.addEventListener('pointerenter', () => clearTimeout(timer));
        popup.addEventListener('pointerleave', scheduleClose);
    }
    function scheduleClose() {
        clearTimeout(timer);
        timer = setTimeout(() => {
            if (active !== document.activeElement) close();
        }, 150);
    }
    document.addEventListener('pointerover', event => {
        const trigger = event.target.closest(selector);
        if (trigger) show(trigger);
    });
    document.addEventListener('pointerout', event => {
        if (event.target.closest(selector)) scheduleClose();
    });
    document.addEventListener('focusin', event => {
        const trigger = event.target.closest(selector);
        if (trigger) show(trigger);
        else close();
    });
    document.addEventListener('focusout', event => {
        if (event.target.closest(selector)) scheduleClose();
    });
    document.addEventListener('click', event => {
        const trigger = event.target.closest(selector);
        if (trigger) {
            event.preventDefault(); // A help button inside a label must not toggle its field.
            show(trigger);
        } else if (!popup?.contains(event.target)) close();
    });
    document.addEventListener('keydown', event => { if (event.key === 'Escape') close(); });
    window.addEventListener('resize', close);
    window.addEventListener('scroll', close, true);
})();
