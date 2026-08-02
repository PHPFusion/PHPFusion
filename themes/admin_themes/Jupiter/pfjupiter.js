$(document).on('click', '.section-view', function(event) {
    event.preventDefault();
    const trigger = $(this);
    const section = trigger.closest('li[data-section]');
    const container = trigger.siblings('ul.menu-container');

    if (container.length) {
        if (container.is(':visible')) {
            container.stop(true, true).slideUp(160);
            section.removeClass('is-open');
            trigger.attr('aria-expanded', 'false');
        } else {
            container.stop(true, true).slideDown(160);
            section.addClass('is-open');
            trigger.attr('aria-expanded', 'true');
        }
    }
});

(() => {
    const palette = document.getElementById('jupiter-command-palette');
    const searchInput = document.getElementById('jupiter-command-input');
    const emptyState = document.getElementById('jupiter-command-empty');
    const accountMenu = document.getElementById('jupiter-admin-account');
    const accountTrigger = document.querySelector('[data-action="account-menu"]');
    const mobileNav = document.querySelector('.pf-admin .pf-nav');
    const mobileNavTrigger = document.querySelector('[data-action="mobile-nav"]');
    const searchTriggers = document.querySelectorAll('[data-action="search"]');
    let lastFocusedElement = null;
    let activeResultIndex = -1;

    const isEditableTarget = (target) => {
        return target instanceof HTMLElement &&
            (target.matches('input, textarea, select') || target.isContentEditable);
    };

    const getVisibleResults = () => {
        if (!palette) {
            return [];
        }

        return Array.from(palette.querySelectorAll('.jupiter-command-item')).filter((item) => !item.hidden);
    };

    const setActiveResult = (index) => {
        const items = getVisibleResults();
        items.forEach((item) => item.classList.remove('is-active'));

        if (!items.length) {
            activeResultIndex = -1;
            return;
        }

        activeResultIndex = (index + items.length) % items.length;
        items[activeResultIndex].classList.add('is-active');
        items[activeResultIndex].scrollIntoView({block: 'nearest'});
    };

    const filterResults = () => {
        if (!palette || !searchInput) {
            return;
        }

        const query = searchInput.value.trim().toLocaleLowerCase();
        let visibleCount = 0;

        palette.querySelectorAll('.jupiter-command-item').forEach((item) => {
            const isMatch = !query || (item.dataset.search || '').includes(query);
            item.hidden = !isMatch;
            item.classList.remove('is-active');
            visibleCount += isMatch ? 1 : 0;
        });

        activeResultIndex = -1;
        emptyState?.classList.toggle('is-visible', visibleCount === 0);
    };

    const openSearch = (trigger) => {
        if (!palette || !searchInput) {
            return;
        }

        lastFocusedElement = trigger || document.activeElement;
        palette.classList.add('is-open');
        palette.setAttribute('aria-hidden', 'false');
        searchTriggers.forEach((item) => item.setAttribute('aria-expanded', 'true'));
        document.body.style.overflow = 'hidden';
        searchInput.value = '';
        filterResults();
        window.setTimeout(() => searchInput.focus(), 0);
    };

    const closeSearch = () => {
        if (!palette || !palette.classList.contains('is-open')) {
            return;
        }

        palette.classList.remove('is-open');
        palette.setAttribute('aria-hidden', 'true');
        searchTriggers.forEach((item) => item.setAttribute('aria-expanded', 'false'));
        document.body.style.overflow = '';
        lastFocusedElement?.focus();
    };

    const closeAccountMenu = (restoreFocus = false) => {
        if (!accountMenu || !accountTrigger) {
            return;
        }

        accountMenu.classList.remove('is-open');
        accountMenu.setAttribute('aria-hidden', 'true');
        accountTrigger.setAttribute('aria-expanded', 'false');
        if (restoreFocus) {
            accountTrigger.focus();
        }
    };

    document.addEventListener('click', (event) => {
        const searchTrigger = event.target.closest('[data-action="search"]');
        if (searchTrigger) {
            event.preventDefault();
            openSearch(searchTrigger);
            return;
        }

        if (event.target.closest('[data-action="close-search"]') ||
            (event.target === palette && palette?.classList.contains('is-open'))) {
            closeSearch();
            return;
        }

        if (event.target.closest('[data-action="account-menu"]')) {
            event.preventDefault();
            const shouldOpen = !accountMenu?.classList.contains('is-open');
            closeAccountMenu();
            if (shouldOpen && accountMenu && accountTrigger) {
                accountMenu.classList.add('is-open');
                accountMenu.setAttribute('aria-hidden', 'false');
                accountTrigger.setAttribute('aria-expanded', 'true');
                accountMenu.querySelector('a')?.focus();
            }
            return;
        }

        if (event.target.closest('[data-action="mobile-nav"]')) {
            event.preventDefault();
            const shouldOpen = !mobileNav?.classList.contains('is-mobile-open');
            mobileNav?.classList.toggle('is-mobile-open', shouldOpen);
            mobileNavTrigger?.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
            mobileNavTrigger?.setAttribute('aria-label', shouldOpen ? 'Close navigation' : 'Open navigation');
            return;
        }

        if (accountMenu?.classList.contains('is-open') &&
            !event.target.closest('#jupiter-admin-account')) {
            closeAccountMenu();
        }
    });

    searchInput?.addEventListener('input', filterResults);
    searchInput?.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowDown') {
            event.preventDefault();
            setActiveResult(activeResultIndex + 1);
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            setActiveResult(activeResultIndex - 1);
        } else if (event.key === 'Enter') {
            const items = getVisibleResults();
            const item = activeResultIndex >= 0 ? items[activeResultIndex] : items[0];
            if (item) {
                event.preventDefault();
                item.click();
            }
        }
    });

    document.addEventListener('keydown', (event) => {
        if ((event.ctrlKey || event.metaKey) && event.key.toLocaleLowerCase() === 'k') {
            event.preventDefault();
            openSearch(document.activeElement);
            return;
        }

        if (event.key === '/' && !event.ctrlKey && !event.metaKey && !event.altKey &&
            !isEditableTarget(event.target)) {
            event.preventDefault();
            openSearch(document.activeElement);
            return;
        }

        if (event.key === 'Escape') {
            if (palette?.classList.contains('is-open')) {
                event.preventDefault();
                closeSearch();
            } else if (accountMenu?.classList.contains('is-open')) {
                event.preventDefault();
                closeAccountMenu(true);
            } else if (mobileNav?.classList.contains('is-mobile-open')) {
                event.preventDefault();
                mobileNav.classList.remove('is-mobile-open');
                mobileNavTrigger?.setAttribute('aria-expanded', 'false');
                mobileNavTrigger?.setAttribute('aria-label', 'Open navigation');
                mobileNavTrigger?.focus();
            }
        }
    });
})();

$(document).on('click', 'button[data-toggle="sidex"]', function(event) {
   event.preventDefault();
   let side_body = $(this).closest('.pf-side').find('.pf-side-body'),
       span = $(this).children('span');
    if (side_body.is(':visible')) {
        span.text('Expand');
        side_body.slideUp();
    } else {
        span.text('Close');
        side_body.slideDown();
    }
});

/* FusionJupiter 404 Logo*/
const animate_fusionjupiter404 = function() {
    let select = s => document.querySelector(s),  selectAll = s =>  document.querySelectorAll(s);

    gsap.set('svg', {
        visibility: 'visible'
    })

    let svgns = "http://www.w3.org/2000/svg";
    let container  = select("#container");
    let twoPi = Math.PI * 2;

    for (let i = 0; i < 25; i++) {
        createCircle();
    }

    function createCircle() {

        var circle = document.createElementNS(svgns, "circle");
        container.appendChild(circle);

        var radius = Math.random() < 0.35 ? gsap.utils.random(-50, 40) : gsap.utils.random(-50, 50);


        gsap.set(circle, {
            attr: { r: gsap.utils.random(5, 12), cx: "50%", cy: 170},
            x: gsap.utils.random(-twoPi, twoPi),
            y: gsap.utils.random(-twoPi, twoPi)
        });

        let swarmTl = gsap.timeline();
        swarmTl.to(circle,  {
            duration:gsap.utils.random(2, 6),
            x: "+=" + twoPi,
            repeat: -1,
            modifiers: {
                x: gsap.utils.unitize(x => (Math.cos(x) * radius), 'px')
            },
            ease: 'none'
        })
            .to(circle, {
                duration: 2,
                y: "+=" + twoPi,
                repeat: -1,
                modifiers: {
                    y: gsap.utils.unitize(y => (Math.sin(y) * radius), 'px')
                },
                ease: 'none'
            }, 0);
    }

    gsap.set('#reflection', {
        scaleY: -1,
        y: 210,
        opacity: 0.12
    })

    gsap.to('#gridBox, #ring', {
        duration: 0.061,
        opacity: 'random(0.64, 0.97)',
        ease: 'sine.inOut',
        repeatRefresh: true,
        repeat: -1
    })

    gsap.to('.gridBox', {
        attr: {
            y: gsap.utils.wrap(['+=40', '+=20', 0])
        },
        ease: 'sine.inOut',
        repeat: -1,
        yoyo: true,
        duration: 1.4,
    })

    gsap.to('#ring', {
        scale: 1.25,
        transformOrigin: '50% 50%',
        ease: 'sine.inOut',
        repeat: -1,
        yoyo: true,
        duration: 1.4,
    })
    gsap.globalTimeline.timeScale(0.75)
}
