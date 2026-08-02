(function () {
    'use strict';

    if (typeof window.bootstrap === 'undefined' && window.tabler && window.tabler.bootstrap) {
        window.bootstrap = window.tabler.bootstrap;
    }

    document.addEventListener('focusin', function (event) {
        if (event.target.classList.contains('select2-input')) {
            event.stopImmediatePropagation();
        }
    });
}());
