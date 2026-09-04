(function () {
    'use strict';

    var mobileQuery = window.matchMedia('(max-width: 767px)');
    var menuSelector = '.fusion-navbar .dropdown-menu-mega';

    function clearPosition(menu) {
        menu.style.removeProperty('--fusion-mega-top');
        menu.style.removeProperty('--fusion-mega-left');
        menu.style.removeProperty('--fusion-mega-width');
    }

    function positionMegaMenu(menu) {
        if (!menu) return;
        if (mobileQuery.matches) {
            clearPosition(menu);
            return;
        }

        var navbar = menu.closest('.fusion-navbar');
        if (!navbar) return;
        var navbarRect = navbar.getBoundingClientRect();
        var gutter = 16;
        var width = Math.min(1120, Math.max(0, window.innerWidth - (gutter * 2)));
        var left = Math.max(gutter, (window.innerWidth - width) / 2);

        menu.style.setProperty('--fusion-mega-top', Math.max(0, navbarRect.bottom) + 'px');
        menu.style.setProperty('--fusion-mega-left', left + 'px');
        menu.style.setProperty('--fusion-mega-width', width + 'px');
    }

    function positionOpenMegaMenus() {
        document.querySelectorAll(menuSelector + '.show').forEach(positionMegaMenu);
    }

    function closeNestedMenus(container, except) {
        if (!container) return;
        container.querySelectorAll('.dropend > .dropdown-menu.show').forEach(function (menu) {
            if (menu === except || (except && menu.contains(except))) return;
            menu.classList.remove('show');
            var trigger = menu.previousElementSibling;
            if (trigger) trigger.setAttribute('aria-expanded', 'false');
        });
    }

    document.addEventListener('click', function (event) {
        var trigger = event.target.closest(
            '.fusion-navbar [data-bs-toggle="dropdown"], .fusion-navbar [data-fusion-dropdown-trigger]'
        );
        var menu = trigger && trigger.nextElementSibling;
        var nestedDropdown = trigger && trigger.closest('.dropend');

        if (nestedDropdown && menu && menu.matches('.dropdown-menu')) {
            event.preventDefault();
            event.stopPropagation();
            var open = !menu.classList.contains('show');
            closeNestedMenus(nestedDropdown.closest('.dropdown-menu'), menu);
            menu.classList.toggle('show', open);
            trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
            return;
        }

        if (menu && menu.matches('.dropdown-menu-mega')) {
            positionMegaMenu(menu);
        }
    }, true);

    document.addEventListener('show.bs.dropdown', function (event) {
        var dropdown = event.target.closest('.dropdown') || event.target;
        positionMegaMenu(dropdown.querySelector(':scope > .dropdown-menu-mega'));
    });
    document.addEventListener('shown.bs.dropdown', positionOpenMegaMenus);
    document.addEventListener('hidden.bs.dropdown', function (event) {
        closeNestedMenus(event.target.closest('.dropdown'));
    });
    window.addEventListener('resize', positionOpenMegaMenus);
    window.addEventListener('scroll', positionOpenMegaMenus, {passive: true});
}());
