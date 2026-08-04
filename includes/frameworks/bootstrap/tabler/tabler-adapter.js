(function () {
    'use strict';

    if (typeof window.bootstrap === 'undefined' && window.tabler && window.tabler.bootstrap) {
        window.bootstrap = window.tabler.bootstrap;
    }
}());
