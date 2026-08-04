(function () {
    'use strict';

    const setExpanded = (trigger, expanded) => {
        trigger.setAttribute('aria-expanded', expanded ? 'true' : 'false');
    };

    const addState = (element, state) => element?.classList.add(state, `tw-${state}`);
    const removeState = (element, state) => element?.classList.remove(state, `tw-${state}`);
    const toggleState = (element, state, force) => {
        element?.classList.toggle(state, force);
        element?.classList.toggle(`tw-${state}`, force);
    };
    const toggleOpenState = (element, open) => {
        toggleState(element, 'show', open);
        element?.classList.toggle('is-active', open);
        element?.classList.toggle('is-open', open);
    };

    /*
     * Community framework compatibility
     *
     * Preserve source classes and add PHPFusion's canonical tw-* aliases from
     * the parsed DOM. Never rewrite HTML strings and never require a template
     * author to call framework_css(). Unknown classes remain untouched.
     */
    const builtinAliases = {
        // Page shells and grids shared by Bootstrap/Tabler, Bulma and Foundation.
        page: 'tw-page',
        'page-wrapper': 'tw-page-wrapper',
        'page-header': 'tw-page-header',
        'page-body': 'tw-page-body',
        'page-title': 'tw-page-title',
        container: 'tw-container',
        'container-fluid': 'tw-container tw-container-fluid',
        'container-xl': 'tw-container tw-container-xl',
        row: 'tw-row',
        columns: 'tw-row',
        'grid-x': 'tw-row',
        'grid-margin-x': 'tw-row-gap-x',
        'grid-margin-y': 'tw-row-gap-y',
        col: 'tw-col',
        column: 'tw-col',
        cell: 'tw-col',

        // Surfaces.
        card: 'tw-card',
        box: 'tw-card',
        panel: 'tw-card tw-panel',
        'card-header': 'tw-card-header',
        'card-body': 'tw-card-body',
        'card-content': 'tw-card-body',
        'card-footer': 'tw-card-footer',
        'card-title': 'tw-card-title',
        'card-subtitle': 'tw-card-subtitle',
        'panel-heading': 'tw-card-header tw-panel-heading',
        'panel-body': 'tw-card-body tw-panel-body',
        'panel-footer': 'tw-card-footer tw-panel-footer',

        // Buttons and compact actions.
        btn: 'tw-btn',
        button: 'tw-btn',
        'btn-primary': 'tw-btn tw-btn-primary',
        'btn-secondary': 'tw-btn tw-btn-secondary',
        'btn-default': 'tw-btn tw-btn-default',
        'btn-success': 'tw-btn tw-btn-success',
        'btn-warning': 'tw-btn tw-btn-warning',
        'btn-danger': 'tw-btn tw-btn-danger',
        'btn-info': 'tw-btn tw-btn-info',
        'btn-link': 'tw-btn tw-btn-link',
        'btn-outline': 'tw-btn tw-btn-outline',
        'btn-outline-primary': 'tw-btn tw-btn-outline-primary',
        'btn-outline-secondary': 'tw-btn tw-btn-outline-secondary',
        'btn-outline-success': 'tw-btn tw-btn-outline-success',
        'btn-outline-warning': 'tw-btn tw-btn-outline-warning',
        'btn-outline-danger': 'tw-btn tw-btn-outline-danger',
        'btn-outline-info': 'tw-btn tw-btn-outline-info',
        'btn-sm': 'tw-btn-sm',
        'btn-lg': 'tw-btn-lg',
        tiny: 'tw-btn-sm',
        small: 'tw-text-sm',
        'btn-group': 'tw-btn-group',
        'button-group': 'tw-btn-group',
        'btn-close': 'tw-btn-close',

        // Badges, labels and Tabler light variants.
        badge: 'tw-badge',
        tag: 'tw-badge',
        label: 'tw-badge tw-badge-label',
        'badge-primary': 'tw-badge tw-badge-primary',
        'badge-secondary': 'tw-badge tw-badge-secondary',
        'badge-success': 'tw-badge tw-badge-success',
        'badge-warning': 'tw-badge tw-badge-warning',
        'badge-danger': 'tw-badge tw-badge-destructive',
        'badge-info': 'tw-badge tw-badge-info',
        'bg-primary-lt': 'tw-bg-primary-lt',
        'bg-success-lt': 'tw-bg-success-lt',
        'bg-danger-lt': 'tw-bg-danger-lt',
        'bg-warning-lt': 'tw-bg-warning-lt',
        'bg-info-lt': 'tw-bg-info-lt',

        // Avatars and status indicators.
        avatar: 'tw-avatar',
        'avatar-xs': 'tw-avatar tw-avatar-xs',
        'avatar-sm': 'tw-avatar tw-avatar-sm',
        'avatar-md': 'tw-avatar tw-avatar-md',
        'avatar-lg': 'tw-avatar tw-avatar-lg',
        status: 'tw-status',
        'status-dot': 'tw-status-dot',
        'status-green': 'tw-status-green',
        'status-red': 'tw-status-red',
        'status-yellow': 'tw-status-yellow',
        'status-blue': 'tw-status-blue',
        'status-azure': 'tw-status-blue',
        'status-orange': 'tw-status-orange',
        'status-gray': 'tw-status-gray',

        // Forms across Bootstrap/Tabler and common Bulma/Foundation markup.
        field: 'tw-form-group',
        'form-group': 'tw-form-group',
        'form-label': 'tw-form-label',
        'control-label': 'tw-form-label',
        'col-form-label': 'tw-form-label',
        'form-control': 'tw-form-control',
        'form-control-sm': 'tw-form-control-sm',
        'form-control-lg': 'tw-form-control-lg',
        'form-select': 'tw-form-select',
        textarea: 'tw-form-control',
        'input-group': 'tw-input-group',
        'input-group-field': 'tw-form-control',
        'input-group-label': 'tw-input-group-text',
        'input-group-button': 'tw-input-group-btn',
        'input-group-text': 'tw-input-group-text',
        'input-group-addon': 'tw-input-group-addon',
        'input-group-prepend': 'tw-input-group-prepend',
        'input-group-append': 'tw-input-group-append',
        'input-group-btn': 'tw-input-group-btn',
        'form-text': 'tw-form-text',
        'help-text': 'tw-form-text',
        'help-block': 'tw-form-text',
        'invalid-feedback': 'tw-invalid-feedback',
        'valid-feedback': 'tw-valid-feedback',
        'form-check': 'tw-form-check',
        'form-check-inline': 'tw-form-check-inline',
        'form-check-input': 'tw-form-check-input',
        'form-check-label': 'tw-form-check-label',
        'form-switch': 'tw-form-switch',
        'form-switch-lg': 'tw-form-switch tw-form-switch-lg',
        'form-range': 'tw-form-range',

        // Tables and lists.
        table: 'tw-table',
        'table-responsive': 'tw-table-responsive',
        'table-hover': 'tw-table-hover',
        'table-striped': 'tw-table-striped',
        'table-active': 'tw-table-active',
        'list-group': 'tw-list-group',
        'list-group-item': 'tw-list-group-item',

        // Feedback.
        callout: 'tw-alert',
        notification: 'tw-alert',
        'alert-primary': 'tw-alert-primary',
        'alert-success': 'tw-alert-success',
        'alert-warning': 'tw-alert-warning',
        'alert-danger': 'tw-alert-destructive',
        'alert-info': 'tw-alert-info',
        progress: 'tw-progress-track',
        'progress-bar': 'tw-progress-indicator',

        // Navigation and interactive primitives.
        dropdown: 'tw-dropdown',
        'dropdown-toggle': 'tw-dropdown-toggle',
        'dropdown-menu': 'tw-dropdown-menu',
        'dropdown-pane': 'tw-dropdown-menu',
        'dropdown-item': 'tw-dropdown-item',
        'dropdown-header': 'tw-dropdown-header',
        'dropdown-divider': 'tw-dropdown-divider',
        modal: 'tw-modal',
        reveal: 'tw-modal tw-foundation-reveal',
        'modal-background': 'tw-modal-backdrop',
        'modal-card': 'tw-modal-dialog tw-modal-content',
        'modal-card-head': 'tw-modal-header',
        'modal-card-body': 'tw-modal-body',
        'modal-card-foot': 'tw-modal-footer',
        'modal-close': 'tw-btn-close',
        'modal-backdrop': 'tw-modal-backdrop',
        'modal-dialog': 'tw-modal-dialog',
        'modal-content': 'tw-modal-content',
        'modal-header': 'tw-modal-header',
        'modal-body': 'tw-modal-body',
        'modal-footer': 'tw-modal-footer',
        'modal-title': 'tw-modal-title',
        'modal-sm': 'tw-modal-sm',
        'modal-lg': 'tw-modal-lg',
        'modal-xl': 'tw-modal-xl',
        collapse: 'tw-collapse',
        offcanvas: 'tw-offcanvas',
        'offcanvas-start': 'tw-offcanvas-start',
        'offcanvas-end': 'tw-offcanvas-end',
        'offcanvas-top': 'tw-offcanvas-top',
        'offcanvas-bottom': 'tw-offcanvas-bottom',
        'offcanvas-backdrop': 'tw-offcanvas-backdrop',
        'offcanvas-header': 'tw-offcanvas-header',
        'offcanvas-body': 'tw-offcanvas-body',
        'offcanvas-title': 'tw-offcanvas-title',
        tabs: 'tw-tabs',
        'nav-tabs': 'tw-nav-tabs',
        'nav-pills': 'tw-nav-pills',
        'nav-item': 'tw-nav-item',
        'nav-link': 'tw-nav-link',
        'tab-content': 'tw-tab-content',
        'tab-pane': 'tw-tab-pane',
        pagination: 'tw-pagination',
        'page-item': 'tw-page-item',
        'page-link': 'tw-page-link',

        // State and common utilities. Original classes are always retained.
        active: 'tw-active',
        'is-active': 'tw-active',
        show: 'tw-show',
        open: 'tw-show',
        collapsed: 'tw-collapsed',
        disabled: 'tw-disabled',
        'is-hidden': 'tw-hidden',
        hide: 'tw-hidden',
        'd-none': 'tw-hidden',
        'd-inline': 'tw-inline',
        'd-block': 'tw-block',
        'd-inline-block': 'tw-inline-block',
        'd-grid': 'tw-grid',
        'd-inline-grid': 'tw-inline-grid',
        'd-table': 'tw-d-table',
        'd-table-row': 'tw-d-table-row',
        'd-table-cell': 'tw-d-table-cell',
        'd-flex': 'tw-flex',
        'd-inline-flex': 'tw-inline-flex',
        'is-flex': 'tw-flex',
        hstack: 'tw-flex tw-flex-row tw-items-center',
        vstack: 'tw-flex tw-flex-col',
        'flex-row': 'tw-flex-row',
        'flex-row-reverse': 'tw-flex-row-reverse',
        'flex-column': 'tw-flex-col',
        'flex-column-reverse': 'tw-flex-col-reverse',
        'flex-fill': 'tw-flex-1',
        'flex-wrap': 'tw-flex-wrap',
        'flex-wrap-reverse': 'tw-flex-wrap-reverse',
        'flex-nowrap': 'tw-flex-nowrap',
        'flex-grow-0': 'tw-grow-0',
        'flex-grow-1': 'tw-grow',
        'flex-shrink-0': 'tw-shrink-0',
        'flex-shrink-1': 'tw-shrink',
        'align-items-start': 'tw-items-start',
        'align-items-center': 'tw-items-center',
        'align-items-end': 'tw-items-end',
        'align-items-baseline': 'tw-items-baseline',
        'align-items-stretch': 'tw-items-stretch',
        'align-content-start': 'tw-content-start',
        'align-content-center': 'tw-content-center',
        'align-content-end': 'tw-content-end',
        'align-content-between': 'tw-content-between',
        'align-content-around': 'tw-content-around',
        'align-content-stretch': 'tw-content-stretch',
        'align-self-auto': 'tw-self-auto',
        'align-self-start': 'tw-self-start',
        'align-self-center': 'tw-self-center',
        'align-self-end': 'tw-self-end',
        'align-self-baseline': 'tw-self-baseline',
        'align-self-stretch': 'tw-self-stretch',
        'justify-content-start': 'tw-justify-start',
        'justify-content-center': 'tw-justify-center',
        'justify-content-end': 'tw-justify-end',
        'justify-content-between': 'tw-justify-between',
        'justify-content-around': 'tw-justify-around',
        'justify-content-evenly': 'tw-justify-evenly',
        'order-first': 'tw-order-first',
        'order-last': 'tw-order-last',
        'position-relative': 'tw-relative',
        'position-absolute': 'tw-absolute',
        'position-fixed': 'tw-fixed',
        'position-sticky': 'tw-sticky',
        'top-0': 'tw-top-0',
        'top-50': 'tw-top-1/2',
        'top-100': 'tw-top-full',
        'bottom-0': 'tw-bottom-0',
        'bottom-50': 'tw-bottom-1/2',
        'bottom-100': 'tw-bottom-full',
        'start-0': 'tw-start-0',
        'start-50': 'tw-start-1/2',
        'start-100': 'tw-start-full',
        'end-0': 'tw-end-0',
        'end-50': 'tw-end-1/2',
        'end-100': 'tw-end-full',
        'w-25': 'tw-w-1/4',
        'w-33': 'tw-w-1/3',
        'w-50': 'tw-w-1/2',
        'w-66': 'tw-w-2/3',
        'w-75': 'tw-w-3/4',
        'w-100': 'tw-w-full',
        'w-auto': 'tw-w-auto',
        'mw-100': 'tw-max-w-full',
        'vw-100': 'tw-w-screen',
        'min-vw-100': 'tw-min-w-screen',
        'h-25': 'tw-h-1/4',
        'h-33': 'tw-h-1/3',
        'h-50': 'tw-h-1/2',
        'h-66': 'tw-h-2/3',
        'h-75': 'tw-h-3/4',
        'h-100': 'tw-h-full',
        'h-auto': 'tw-h-auto',
        'mh-100': 'tw-max-h-full',
        'vh-100': 'tw-h-screen',
        'min-vh-100': 'tw-min-h-screen',
        'overflow-hidden': 'tw-overflow-hidden',
        'overflow-auto': 'tw-overflow-auto',
        'overflow-visible': 'tw-overflow-visible',
        'overflow-scroll': 'tw-overflow-scroll',
        'overflow-x-auto': 'tw-overflow-x-auto',
        'overflow-x-hidden': 'tw-overflow-x-hidden',
        'overflow-x-visible': 'tw-overflow-x-visible',
        'overflow-x-scroll': 'tw-overflow-x-scroll',
        'overflow-y-auto': 'tw-overflow-y-auto',
        'overflow-y-hidden': 'tw-overflow-y-hidden',
        'overflow-y-visible': 'tw-overflow-y-visible',
        'overflow-y-scroll': 'tw-overflow-y-scroll',
        'object-contain': 'tw-object-contain',
        'object-cover': 'tw-object-cover',
        'object-fill': 'tw-object-fill',
        'object-scale': 'tw-object-scale-down',
        'object-none': 'tw-object-none',
        'object-fit-contain': 'tw-object-contain',
        'object-fit-cover': 'tw-object-cover',
        'object-fit-fill': 'tw-object-fill',
        'object-fit-scale': 'tw-object-scale-down',
        'object-fit-none': 'tw-object-none',
        'object-center': 'tw-object-center',
        'object-top': 'tw-object-top',
        'object-bottom': 'tw-object-bottom',
        'object-start': 'tw-object-left',
        'object-end': 'tw-object-right',
        visible: 'tw-visible',
        invisible: 'tw-invisible',
        'visually-hidden': 'tw-sr-only',
        small: 'tw-text-sm',
        'fs-1': 'tw-text-5xl',
        'fs-2': 'tw-text-4xl',
        'fs-3': 'tw-text-3xl',
        'fs-4': 'tw-text-2xl',
        'fs-5': 'tw-text-xl',
        'fs-6': 'tw-text-base',
        'text-start': 'tw-text-start',
        'text-center': 'tw-text-center',
        'text-end': 'tw-text-end',
        'text-wrap': 'tw-whitespace-normal',
        'text-nowrap': 'tw-whitespace-nowrap',
        'text-break': 'tw-break-words',
        'text-lowercase': 'tw-lowercase',
        'text-uppercase': 'tw-uppercase',
        'text-capitalize': 'tw-capitalize',
        'text-decoration-none': 'tw-no-underline',
        'text-decoration-underline': 'tw-underline',
        'text-decoration-line-through': 'tw-line-through',
        'text-truncate': 'tw-truncate',
        'text-muted': 'tw-text-ui-muted-foreground',
        'has-text-centered': 'tw-text-center',
        'has-text-right': 'tw-text-end',
        'has-text-left': 'tw-text-start',
        'fw-normal': 'tw-font-normal',
        'fw-light': 'tw-font-light',
        'fw-lighter': 'tw-font-extralight',
        'fw-medium': 'tw-font-medium',
        'fw-semibold': 'tw-font-semibold',
        'fw-bold': 'tw-font-bold',
        'fw-bolder': 'tw-font-extrabold',
        'fst-italic': 'tw-italic',
        'fst-normal': 'tw-not-italic',
        'lh-1': 'tw-leading-none',
        'lh-sm': 'tw-leading-tight',
        'lh-base': 'tw-leading-normal',
        'lh-lg': 'tw-leading-loose',
        'user-select-all': 'tw-select-all',
        'user-select-auto': 'tw-select-auto',
        'user-select-none': 'tw-select-none',
        'pe-none': 'tw-pointer-events-none',
        'pe-auto': 'tw-pointer-events-auto',
        'cursor-pointer': 'tw-cursor-pointer',
        'cursor-default': 'tw-cursor-default',
        'cursor-not-allowed': 'tw-cursor-not-allowed',
        'bg-surface': 'tw-bg-ui-card',
        'bg-surface-secondary': 'tw-bg-ui-muted',
        'bg-surface-tertiary': 'tw-bg-ui-muted',
        'bg-muted-lt': 'tw-bg-ui-muted tw-text-ui-muted-foreground',
        'bg-transparent': 'tw-bg-transparent',
        border: 'tw-border tw-border-ui-border',
        'border-0': 'tw-border-0',
        'border-top': 'tw-border-t tw-border-ui-border',
        'border-top-0': 'tw-border-t-0',
        'border-end': 'tw-border-e tw-border-ui-border',
        'border-end-0': 'tw-border-e-0',
        'border-bottom': 'tw-border-b tw-border-ui-border',
        'border-bottom-0': 'tw-border-b-0',
        'border-start': 'tw-border-s tw-border-ui-border',
        'border-start-0': 'tw-border-s-0',
        'border-x': 'tw-border-x tw-border-ui-border',
        'border-y': 'tw-border-y tw-border-ui-border',
        rounded: 'tw-rounded',
        'rounded-0': 'tw-rounded-none',
        'rounded-1': 'tw-rounded-sm',
        'rounded-2': 'tw-rounded-md',
        'rounded-3': 'tw-rounded-lg',
        'rounded-circle': 'tw-rounded-full',
        'rounded-pill': 'tw-rounded-full',
        shadow: 'tw-shadow',
        'shadow-sm': 'tw-shadow-sm',
        'shadow-lg': 'tw-shadow-lg',
        'shadow-none': 'tw-shadow-none',
    };

    const adapterProfiles = [
        {name: 'phpfusion-community', classes: builtinAliases},
    ];
    const processedAliases = new WeakMap();
    const aliasOptOutAttribute = 'data-fusion-no-framework-aliases';
    const pendingRoots = new Set();
    let flushQueued = false;

    const addAliases = (target, value) => {
        if (Array.isArray(value)) {
            value.forEach((entry) => addAliases(target, entry));
            return;
        }
        String(value || '').split(/\s+/).filter(Boolean).forEach((className) => target.add(className));
    };

    const responsivePrefix = (dialect) => ({
        xs: '', sm: 'sm:', md: 'md:', lg: 'lg:', xl: 'xl:', xxl: '2xl:',
        small: '', medium: 'md:', large: 'lg:',
        mobile: '', tablet: 'md:', desktop: 'lg:', widescreen: 'xl:', fullhd: '2xl:',
    })[dialect];

    // Bootstrap 0-4 and the bundled Tabler 5-6 scale expressed as Tailwind units.
    const spacingScale = {0: '0', 1: '1', 2: '2', 3: '4', 4: '6', 5: '8', 6: '10'};
    const responsiveUtility = (breakpoint, utility) => {
        const prefix = breakpoint ? responsivePrefix(breakpoint) : '';
        return prefix === undefined ? null : `${prefix}${utility}`;
    };

    const utilityAliases = {
        display: {
            none: 'tw-hidden', inline: 'tw-inline', block: 'tw-block',
            'inline-block': 'tw-inline-block', grid: 'tw-grid', 'inline-grid': 'tw-inline-grid',
            table: 'tw-d-table', 'table-row': 'tw-d-table-row', 'table-cell': 'tw-d-table-cell',
            flex: 'tw-flex', 'inline-flex': 'tw-inline-flex',
        },
        flex: {
            row: 'tw-flex-row', 'row-reverse': 'tw-flex-row-reverse',
            column: 'tw-flex-col', 'column-reverse': 'tw-flex-col-reverse', fill: 'tw-flex-1',
            wrap: 'tw-flex-wrap', 'wrap-reverse': 'tw-flex-wrap-reverse', nowrap: 'tw-flex-nowrap',
            'grow-0': 'tw-grow-0', 'grow-1': 'tw-grow', 'shrink-0': 'tw-shrink-0', 'shrink-1': 'tw-shrink',
        },
        justify: {
            start: 'tw-justify-start', center: 'tw-justify-center', end: 'tw-justify-end',
            between: 'tw-justify-between', around: 'tw-justify-around', evenly: 'tw-justify-evenly',
        },
        items: {
            start: 'tw-items-start', center: 'tw-items-center', end: 'tw-items-end',
            baseline: 'tw-items-baseline', stretch: 'tw-items-stretch',
        },
        content: {
            start: 'tw-content-start', center: 'tw-content-center', end: 'tw-content-end',
            between: 'tw-content-between', around: 'tw-content-around', stretch: 'tw-content-stretch',
        },
        self: {
            auto: 'tw-self-auto', start: 'tw-self-start', center: 'tw-self-center', end: 'tw-self-end',
            baseline: 'tw-self-baseline', stretch: 'tw-self-stretch',
        },
    };

    const addResponsiveColumn = (aliases, breakpoint, span) => {
        const prefix = responsivePrefix(breakpoint);
        const numericSpan = Number(span);
        if (prefix === undefined || numericSpan < 1 || numericSpan > 12) return;
        aliases.add('tw-col-span-12');
        aliases.add(`${prefix}tw-col-span-${numericSpan}`);
    };

    const resolveContextualAliases = (element, tokens, aliases) => {
        const has = (token) => tokens.has(token);
        const tag = element.tagName?.toLowerCase() || '';

        tokens.forEach((token) => {
            let match = token.match(/^col(?:-(xs|sm|md|lg|xl|xxl))?-(\d{1,2})$/);
            if (match) addResponsiveColumn(aliases, match[1] || 'xs', match[2]);

            if (has('cell')) {
                match = token.match(/^(small|medium|large)-(\d{1,2})$/);
                if (match) addResponsiveColumn(aliases, match[1], match[2]);
            }

            if (has('column')) {
                const defaultBulmaBreakpoint = element.parentElement?.classList.contains('is-mobile')
                    ? 'mobile'
                    : 'tablet';
                const fractions = {
                    full: 12, half: 6, 'one-third': 4, 'two-thirds': 8,
                    'one-quarter': 3, 'three-quarters': 9,
                };
                match = token.match(/^is-(full|half|one-third|two-thirds|one-quarter|three-quarters)(?:-(mobile|tablet|desktop|widescreen|fullhd))?$/);
                if (match) addResponsiveColumn(aliases, match[2] || defaultBulmaBreakpoint, fractions[match[1]]);
                match = token.match(/^is-(\d{1,2})(?:-(mobile|tablet|desktop|widescreen|fullhd))?$/);
                if (match) addResponsiveColumn(aliases, match[2] || defaultBulmaBreakpoint, match[1]);
            }

            match = token.match(/^(m|mt|mb|ms|me|mx|my|p|pt|pb|ps|pe|px|py)(?:-(sm|md|lg|xl|xxl))?-(auto|n?[0-6])$/);
            if (match) {
                const [, utility, breakpoint, sourceValue] = match;
                const isPadding = utility.startsWith('p');
                const isNegative = sourceValue.startsWith('n');
                const numericValue = sourceValue.replace(/^n/, '');
                if ((!isPadding || (!isNegative && sourceValue !== 'auto')) && (sourceValue === 'auto' || spacingScale[numericValue] !== undefined)) {
                    const value = sourceValue === 'auto' ? 'auto' : spacingScale[numericValue];
                    const target = `${isNegative ? '-' : ''}tw-${utility}-${value}`;
                    const alias = responsiveUtility(breakpoint, target);
                    if (alias) aliases.add(alias);
                }
            }

            match = token.match(/^(gap|row-gap|column-gap)(?:-(sm|md|lg|xl|xxl))?-([0-6])$/);
            if (match) {
                const utility = {gap: 'gap', 'row-gap': 'gap-y', 'column-gap': 'gap-x'}[match[1]];
                const alias = responsiveUtility(match[2], `tw-${utility}-${spacingScale[match[3]]}`);
                if (alias) aliases.add(alias);
            }

            match = token.match(/^(w|h)-([0-6])$/);
            if (match) aliases.add(`tw-${match[1]}-${spacingScale[match[2]]}`);

            match = token.match(/^space-(x|y)-([0-6])$/);
            if (match) {
                aliases.add('tw-flex');
                if (match[1] === 'y') aliases.add('tw-flex-col');
                aliases.add(`tw-gap-${spacingScale[match[2]]}`);
            }

            match = token.match(/^divide-(x|y)(?:-[0-6])?$/);
            if (match) addAliases(aliases, `tw-divide-${match[1]} tw-divide-ui-border`);

            match = token.match(/^d(?:-(sm|md|lg|xl|xxl))?-(none|inline|block|inline-block|grid|inline-grid|table|table-row|table-cell|flex|inline-flex)$/);
            if (match) {
                const alias = responsiveUtility(match[1], utilityAliases.display[match[2]]);
                if (alias) aliases.add(alias);
            }

            match = token.match(/^flex(?:-(sm|md|lg|xl|xxl))?-(row|row-reverse|column|column-reverse|fill|wrap|wrap-reverse|nowrap|grow-[01]|shrink-[01])$/);
            if (match) {
                const alias = responsiveUtility(match[1], utilityAliases.flex[match[2]]);
                if (alias) aliases.add(alias);
            }

            match = token.match(/^justify-content(?:-(sm|md|lg|xl|xxl))?-(start|center|end|between|around|evenly)$/);
            if (match) {
                const alias = responsiveUtility(match[1], utilityAliases.justify[match[2]]);
                if (alias) aliases.add(alias);
            }

            match = token.match(/^align-(items|content|self)(?:-(sm|md|lg|xl|xxl))?-(auto|start|center|end|baseline|between|around|stretch)$/);
            if (match) {
                const group = {items: 'items', content: 'content', self: 'self'}[match[1]];
                const target = utilityAliases[group][match[3]];
                const alias = target ? responsiveUtility(match[2], target) : null;
                if (alias) aliases.add(alias);
            }

            match = token.match(/^order(?:-(sm|md|lg|xl|xxl))?-(first|last|[0-5])$/);
            if (match) {
                const order = match[2] === 'first' || match[2] === 'last' ? match[2] : match[2];
                const alias = responsiveUtility(match[1], `tw-order-${order}`);
                if (alias) aliases.add(alias);
            }

            match = token.match(/^text(?:-(sm|md|lg|xl|xxl))?-(start|center|end)$/);
            if (match) {
                const alias = responsiveUtility(match[1], `tw-text-${match[2]}`);
                if (alias) aliases.add(alias);
            }

            match = token.match(/^float(?:-(sm|md|lg|xl|xxl))?-(start|end|none)$/);
            if (match) {
                const float = {start: 'start', end: 'end', none: 'none'}[match[2]];
                const alias = responsiveUtility(match[1], `tw-float-${float}`);
                if (alias) aliases.add(alias);
            }

            match = token.match(/^object-fit(?:-(sm|md|lg|xl|xxl))?-(contain|cover|fill|scale|none)$/);
            if (match) {
                const fit = {contain: 'contain', cover: 'cover', fill: 'fill', scale: 'scale-down', none: 'none'}[match[2]];
                const alias = responsiveUtility(match[1], `tw-object-${fit}`);
                if (alias) aliases.add(alias);
            }
        });

        const buttonLike = has('btn') || has('button') || tag === 'button';
        if (buttonLike) {
            aliases.add('tw-btn');
            if (has('small') || has('is-small')) aliases.add('tw-btn-sm');
            if (has('large') || has('is-large')) aliases.add('tw-btn-lg');
            if (has('primary') || has('is-primary')) aliases.add('tw-btn-primary');
            if (has('secondary') || has('is-secondary')) aliases.add('tw-btn-secondary');
            if (has('success') || has('is-success')) aliases.add('tw-btn-success');
            if (has('warning') || has('is-warning')) aliases.add('tw-btn-warning');
            if (has('alert') || has('danger') || has('is-danger')) aliases.add('tw-btn-danger');
            if (has('info') || has('is-info')) aliases.add('tw-btn-info');
            if (has('hollow') || has('is-outlined') || has('is-light')) aliases.add('tw-btn-outline');
        }

        const badgeLike = has('badge') || has('tag') || has('label');
        if (badgeLike) {
            if (has('primary') || has('is-primary')) aliases.add('tw-badge-primary');
            if (has('secondary') || has('is-secondary')) aliases.add('tw-badge-secondary');
            if (has('success') || has('is-success')) aliases.add('tw-badge-success');
            if (has('warning') || has('is-warning')) aliases.add('tw-badge-warning');
            if (has('alert') || has('danger') || has('is-danger')) aliases.add('tw-badge-destructive');
            if (has('info') || has('is-info')) aliases.add('tw-badge-info');
        }

        const alertLike = has('alert') || has('callout') || has('notification');
        if (alertLike && !buttonLike) {
            aliases.add('tw-alert');
            if (has('primary') || has('is-primary')) aliases.add('tw-alert-primary');
            if (has('success') || has('is-success')) aliases.add('tw-alert-success');
            if (has('warning') || has('is-warning')) aliases.add('tw-alert-warning');
            if (has('alert') || has('danger') || has('is-danger')) aliases.add('tw-alert-destructive');
            if (has('info') || has('is-info')) aliases.add('tw-alert-info');
        }

        if (tag === 'select' || has('form-select')) aliases.add('tw-form-select');
        if ((tag === 'input' && has('input')) || tag === 'textarea') aliases.add('tw-form-control');
    };

    const resolveAliases = (element, tokens) => {
        const aliases = new Set();
        adapterProfiles.forEach((profile) => {
            tokens.forEach((token) => addAliases(aliases, profile.classes?.[token]));
            if (typeof profile.resolve === 'function') {
                profile.resolve(element, new Set(tokens), (value) => addAliases(aliases, value));
            }
        });
        resolveContextualAliases(element, new Set(tokens), aliases);
        return aliases;
    };

    const adaptElement = (element, force = false) => {
        if (!(element instanceof Element)) return;
        const previous = processedAliases.get(element);
        if (element.hasAttribute(aliasOptOutAttribute)) {
            previous?.owned.forEach((className) => element.classList.remove(className));
            processedAliases.set(element, {signature: `${element.tagName}|framework-alias-opt-out`, owned: new Set()});
            return;
        }
        const sourceTokens = Array.from(element.classList).filter(
            (className) => !className.startsWith('tw-') && !className.startsWith('-tw-') &&
                !className.includes(':tw-') && !className.includes(':-tw-')
        );
        const signature = `${element.tagName}|${sourceTokens.join(' ')}`;
        if (!force && previous?.signature === signature) return;

        previous?.owned.forEach((className) => element.classList.remove(className));
        const resolved = resolveAliases(element, sourceTokens);
        const owned = new Set();
        resolved.forEach((className) => {
            if (!element.classList.contains(className)) {
                element.classList.add(className);
                owned.add(className);
            }
        });
        processedAliases.set(element, {signature, owned});
    };

    const adaptTree = (root, force = false) => {
        if (root instanceof Element && (root.hasAttribute('class') || root.matches('select'))) {
            adaptElement(root, force);
        }
        root.querySelectorAll?.('[class], select').forEach((element) => adaptElement(element, force));
    };

    const flushAliases = () => {
        flushQueued = false;
        const roots = Array.from(pendingRoots);
        pendingRoots.clear();
        roots.forEach((root) => adaptTree(root));
    };

    const queueAliasRoot = (root) => {
        if (!(root instanceof Element)) return;
        pendingRoots.add(root);
        if (!flushQueued) {
            flushQueued = true;
            queueMicrotask(flushAliases);
        }
    };

    const observeCompatibilityAliases = () => {
        adaptTree(document.documentElement);
        new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'attributes') {
                    queueAliasRoot(mutation.target);
                    return;
                }
                mutation.addedNodes.forEach(queueAliasRoot);
            });
        }).observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class', aliasOptOutAttribute],
            childList: true,
            subtree: true,
        });
    };

    window.FusionUI = Object.assign(window.FusionUI || {}, {
        adapt(root = document) {
            adaptTree(root, true);
        },
        registerAdapter(name, definition) {
            if (!name || !definition || typeof definition !== 'object') return false;
            adapterProfiles.push({name: String(name), classes: definition.classes || {}, resolve: definition.resolve});
            adaptTree(document.documentElement, true);
            return true;
        },
    });

    const closeMenus = (except) => {
        document.querySelectorAll('[data-tailwind-menu]:not([hidden])').forEach((menu) => {
            if (menu !== except) {
                menu.hidden = true;
                const trigger = document.querySelector('[aria-controls="' + menu.id + '"]');
                if (trigger) setExpanded(trigger, false);
            }
        });
    };

    const closeCommunityDropdowns = (except) => {
        document.querySelectorAll('.tw-dropdown-menu.tw-show, .dropdown-menu.show, .dropdown-pane.is-open').forEach((menu) => {
            if (menu === except) return;
            toggleOpenState(menu, false);
            const wrapper = menu.closest('.tw-dropdown, .dropdown') || menu.parentElement;
            const trigger = wrapper?.querySelector('[data-bs-toggle="dropdown"], [data-toggle="dropdown"], [data-dropdown], [aria-haspopup="true"]');
            if (trigger) setExpanded(trigger, false);
            wrapper?.classList.remove('tw-active');
        });
    };

    const closeModal = (modal) => {
        if (!modal) return;
        modal.hidden = true;
        document.documentElement.classList.remove('tw-overflow-hidden');
        const triggerId = modal.dataset.returnFocus;
        if (triggerId) document.getElementById(triggerId)?.focus();
    };

    const openModal = (modal, trigger) => {
        if (!modal) return;
        if (trigger) {
            if (!trigger.id) trigger.id = 'tailwind-modal-trigger-' + Date.now();
            modal.dataset.returnFocus = trigger.id;
        }
        modal.hidden = false;
        document.documentElement.classList.add('tw-overflow-hidden');
        modal.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])')?.focus();
    };

    const targetFrom = (trigger) => {
        const legacyToggle = trigger?.getAttribute('data-toggle');
        const foundationTarget = legacyToggle && !['modal', 'collapse', 'dropdown', 'tab', 'pill', 'offcanvas'].includes(legacyToggle)
            ? legacyToggle
            : '';
        const selector = trigger?.dataset.bsTarget
            || trigger?.getAttribute('data-target')
            || trigger?.getAttribute('data-open')
            || trigger?.getAttribute('aria-controls')
            || foundationTarget
            || trigger?.getAttribute('href');
        if (!selector) return null;
        if (!selector.startsWith('#')) return document.getElementById(selector);
        try {
            return document.querySelector(selector);
        } catch (error) {
            return null;
        }
    };

    const modalOptions = (element, options = {}) => {
        const dataBackdrop = element?.getAttribute('data-bs-backdrop') ?? element?.getAttribute('data-backdrop');
        const dataKeyboard = element?.getAttribute('data-bs-keyboard') ?? element?.getAttribute('data-keyboard');
        return {
            backdrop: dataBackdrop === 'static' ? 'static' : dataBackdrop === 'false' ? false : true,
            keyboard: dataKeyboard !== 'false',
            ...options,
        };
    };

    const dispatchBootstrapEvent = (element, name, relatedTarget, cancelable = false) => {
        const event = new CustomEvent(name, {
            bubbles: true,
            cancelable,
            detail: {relatedTarget},
        });
        Object.defineProperty(event, 'relatedTarget', {value: relatedTarget, enumerable: true});
        element.dispatchEvent(event);

        let jqueryPrevented = false;
        if (window.jQuery?.fn) {
            const jqueryEvent = window.jQuery.Event(name, {relatedTarget});
            window.jQuery(element).trigger(jqueryEvent);
            jqueryPrevented = jqueryEvent.isDefaultPrevented();
        }
        return !event.defaultPrevented && !jqueryPrevented;
    };

    const dispatchModalEvent = (element, name, relatedTarget) => dispatchBootstrapEvent(
        element,
        name,
        relatedTarget,
        name === 'show.bs.modal' || name === 'hide.bs.modal'
    );

    const offcanvasOptions = (element, options = {}) => {
        const dataBackdrop = element?.getAttribute('data-bs-backdrop') ?? element?.getAttribute('data-backdrop');
        const dataKeyboard = element?.getAttribute('data-bs-keyboard') ?? element?.getAttribute('data-keyboard');
        const dataScroll = element?.getAttribute('data-bs-scroll') ?? element?.getAttribute('data-scroll');
        return {
            backdrop: dataBackdrop === 'static' ? 'static' : dataBackdrop === 'false' ? false : true,
            keyboard: dataKeyboard !== 'false',
            scroll: dataScroll === 'true',
            ...options,
        };
    };

    const dispatchOffcanvasEvent = (element, name, relatedTarget) => dispatchBootstrapEvent(
        element,
        name,
        relatedTarget,
        name === 'show.bs.offcanvas' || name === 'hide.bs.offcanvas'
    );

    class TailwindModal {
        static instances = new WeakMap();
        constructor(element, options = {}) {
            this.element = element;
            this.options = modalOptions(element, options);
            this.backdrop = null;
            this.isShown = element.hasAttribute('data-tailwind-modal')
                ? !element.hidden
                : element.classList.contains('show') || element.classList.contains('tw-show');
            this.handleSurfaceClick = (event) => {
                if (event.target === this.element && this.options.backdrop !== 'static' && this.options.backdrop !== false) {
                    this.hide();
                }
            };
            this.element.addEventListener('click', this.handleSurfaceClick);
            TailwindModal.instances.set(element, this);
        }
        show(trigger) {
            if (this.isShown || !dispatchModalEvent(this.element, 'show.bs.modal', trigger)) return;
            this.isShown = true;
            if (!this.element.hasAttribute('data-tailwind-modal') && this.options.backdrop !== false && !this.backdrop) {
                this.backdrop = document.createElement('div');
                this.backdrop.className = 'modal-backdrop tw-modal-backdrop fade tw-fade show tw-show';
                this.backdrop.addEventListener('click', () => {
                    if (this.options.backdrop !== 'static') this.hide();
                });
                document.body.appendChild(this.backdrop);
            }
            toggleOpenState(this.element, true);
            this.element.style.display = 'block';
            this.element.hidden = false;
            this.element.setAttribute('aria-modal', 'true');
            this.element.removeAttribute('aria-hidden');
            openModal(this.element, trigger);
            dispatchModalEvent(this.element, 'shown.bs.modal', trigger);
        }
        hide() {
            if (!this.isShown || !dispatchModalEvent(this.element, 'hide.bs.modal')) return;
            this.isShown = false;
            toggleOpenState(this.element, false);
            this.element.style.display = 'none';
            this.element.setAttribute('aria-hidden', 'true');
            this.element.removeAttribute('aria-modal');
            this.backdrop?.remove();
            this.backdrop = null;
            closeModal(this.element);
            dispatchModalEvent(this.element, 'hidden.bs.modal');
        }
        toggle(trigger) {
            if (this.isShown) this.hide();
            else this.show(trigger);
        }
        dispose() {
            this.backdrop?.remove();
            this.backdrop = null;
            this.element.removeEventListener('click', this.handleSurfaceClick);
            TailwindModal.instances.delete(this.element);
        }
        static getInstance(element) {
            return element ? TailwindModal.instances.get(element) || null : null;
        }
        static getOrCreateInstance(element, options = {}) {
            if (!element) return null;
            const instance = TailwindModal.getInstance(element) || new TailwindModal(element, options);
            instance.options = modalOptions(element, {...instance.options, ...options});
            return instance;
        }
    }

    class TailwindTab {
        static instances = new WeakMap();
        constructor(element) {
            this.element = element;
            TailwindTab.instances.set(element, this);
        }
        show() {
            const target = targetFrom(this.element);
            const list = this.element.closest('.tw-nav, .nav, [role="tablist"]');
            list?.querySelectorAll('.tw-active, .active, [aria-selected="true"]').forEach((item) => {
                removeState(item, 'active');
                item.setAttribute('aria-selected', 'false');
            });
            addState(this.element, 'active');
            this.element.setAttribute('aria-selected', 'true');
            const content = target?.closest('.tw-tab-content, .tab-content');
            content?.querySelectorAll('.tw-tab-pane, .tab-pane').forEach((panel) => {
                removeState(panel, 'show');
                removeState(panel, 'active');
            });
            addState(target, 'show');
            addState(target, 'active');
            target?.removeAttribute('hidden');
            this.element.dispatchEvent(new CustomEvent('shown.bs.tab', {bubbles: true}));
        }
        static getInstance(element) {
            return element ? TailwindTab.instances.get(element) || null : null;
        }
        static getOrCreateInstance(element) {
            return element ? TailwindTab.getInstance(element) || new TailwindTab(element) : null;
        }
    }

    const activateTailwindTab = (tab, options = {}) => {
        const {focus = true, persist = true} = options;
        const tablist = tab?.closest('[role="tablist"]');
        const panel = document.getElementById(tab?.getAttribute('aria-controls') || '');
        if (!tablist || !panel) return;

        tablist.querySelectorAll('[role="tab"]').forEach((item) => {
            const selected = item === tab;
            item.setAttribute('aria-selected', selected ? 'true' : 'false');
            item.setAttribute('data-state', selected ? 'active' : 'inactive');
            item.tabIndex = selected ? 0 : -1;
        });

        document.querySelectorAll('[role="tabpanel"][data-fusion-tab-panel]').forEach((item) => {
            if (item.dataset.fusionTabGroup === panel.dataset.fusionTabGroup) {
                item.hidden = item !== panel;
            }
        });

        if (persist && tablist.dataset.fusionTabsRemember) {
            try {
                window.localStorage.setItem(tablist.dataset.fusionTabsRemember, panel.id);
            } catch (error) {
                // Storage may be unavailable in privacy-restricted contexts.
            }
        }

        if (focus) tab.focus();
        tab.dispatchEvent(new CustomEvent('shown.bs.tab', {bubbles: true}));
    };

    const restoreTailwindTabs = () => {
        document.querySelectorAll('[role="tablist"][data-fusion-tabs-remember]').forEach((tablist) => {
            let saved = '';
            try {
                saved = window.localStorage.getItem(tablist.dataset.fusionTabsRemember) || '';
            } catch (error) {
                return;
            }
            if (saved === '') return;
            const tab = Array.from(tablist.querySelectorAll('[role="tab"]')).find(
                (item) => item.getAttribute('aria-controls') === saved
            );
            if (tab) activateTailwindTab(tab, {focus: false, persist: false});
        });
    };

    class TailwindDropdown {
        static instances = new WeakMap();
        constructor(element) {
            this.element = element;
            TailwindDropdown.instances.set(element, this);
        }
        menu() {
            const target = targetFrom(this.element);
            if (target?.matches('.tw-dropdown-menu, .dropdown-menu, .dropdown-pane')) return target;
            return this.element.nextElementSibling?.matches('.tw-dropdown-menu, .dropdown-menu, .dropdown-pane')
                ? this.element.nextElementSibling
                : this.element.parentElement?.querySelector('.tw-dropdown-menu, .dropdown-menu, .dropdown-pane');
        }
        show() {
            closeCommunityDropdowns(this.menu());
            toggleOpenState(this.menu(), true);
            this.element.closest('.tw-dropdown, .dropdown')?.classList.add('tw-active');
            setExpanded(this.element, true);
        }
        hide() {
            toggleOpenState(this.menu(), false);
            this.element.closest('.tw-dropdown, .dropdown')?.classList.remove('tw-active');
            setExpanded(this.element, false);
        }
        toggle() {
            this.menu()?.classList.contains('show') ? this.hide() : this.show();
        }
        static getInstance(element) {
            return element ? TailwindDropdown.instances.get(element) || null : null;
        }
        static getOrCreateInstance(element) {
            return element ? TailwindDropdown.getInstance(element) || new TailwindDropdown(element) : null;
        }
    }

    class TailwindToast {
        static instances = new WeakMap();
        constructor(element, options = {}) {
            this.element = element;
            this.options = options;
            TailwindToast.instances.set(element, this);
        }
        show() {
            toggleOpenState(this.element, true);
            this.element.style.display = '';
            if (this.options.autohide !== false) {
                window.setTimeout(() => this.hide(), this.options.delay || 5000);
            }
        }
        hide() {
            toggleOpenState(this.element, false);
            this.element.dispatchEvent(new CustomEvent('hidden.bs.toast', {bubbles: true}));
        }
        static getInstance(element) {
            return element ? TailwindToast.instances.get(element) || null : null;
        }
        static getOrCreateInstance(element, options = {}) {
            return element ? TailwindToast.getInstance(element) || new TailwindToast(element, options) : null;
        }
    }

    class TailwindOffcanvas {
        static instances = new WeakMap();
        constructor(element, options = {}) {
            this.element = element;
            this.options = offcanvasOptions(element, options);
            this.backdrop = null;
            this.trigger = null;
            this.originalRole = element.getAttribute('role');
            this.isShown = element.classList.contains('show') || element.classList.contains('tw-show');
            TailwindOffcanvas.instances.set(element, this);
        }
        show(trigger) {
            if (this.isShown || !dispatchOffcanvasEvent(this.element, 'show.bs.offcanvas', trigger)) return;

            const open = document.querySelector('.tw-offcanvas.tw-show, .offcanvas.show');
            if (open && open !== this.element) TailwindOffcanvas.getOrCreateInstance(open)?.hide();

            this.isShown = true;
            this.trigger = trigger || this.trigger;
            if (trigger) {
                if (!trigger.id) trigger.id = 'tailwind-offcanvas-trigger-' + Date.now();
                this.element.dataset.returnFocus = trigger.id;
                setExpanded(trigger, true);
            }
            if (this.options.backdrop !== false && !this.backdrop) {
                this.backdrop = document.createElement('div');
                this.backdrop.className = 'offcanvas-backdrop tw-offcanvas-backdrop fade tw-fade show tw-show';
                this.backdrop.addEventListener('click', () => {
                    if (this.options.backdrop === 'static') {
                        dispatchOffcanvasEvent(this.element, 'hidePrevented.bs.offcanvas');
                    } else {
                        this.hide();
                    }
                });
                document.body.appendChild(this.backdrop);
            }
            toggleOpenState(this.element, true);
            this.element.hidden = false;
            this.element.style.visibility = 'visible';
            this.element.setAttribute('aria-modal', 'true');
            this.element.setAttribute('role', 'dialog');
            this.element.removeAttribute('aria-hidden');
            if (!this.options.scroll) document.documentElement.classList.add('tw-overflow-hidden');
            this.element.querySelector('button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])')?.focus();
            dispatchOffcanvasEvent(this.element, 'shown.bs.offcanvas', trigger);
        }
        hide() {
            if (!this.isShown || !dispatchOffcanvasEvent(this.element, 'hide.bs.offcanvas')) return;
            this.isShown = false;
            toggleOpenState(this.element, false);
            this.element.hidden = true;
            this.element.style.visibility = '';
            this.element.setAttribute('aria-hidden', 'true');
            this.element.removeAttribute('aria-modal');
            if (this.originalRole == null) this.element.removeAttribute('role');
            else this.element.setAttribute('role', this.originalRole);
            this.backdrop?.remove();
            this.backdrop = null;
            if (!document.querySelector('.tw-modal.tw-show, .modal.show, .tw-offcanvas.tw-show, .offcanvas.show')) {
                document.documentElement.classList.remove('tw-overflow-hidden');
            }
            setExpanded(this.trigger, false);
            const returnTarget = this.trigger || document.getElementById(this.element.dataset.returnFocus || '');
            returnTarget?.focus();
            dispatchOffcanvasEvent(this.element, 'hidden.bs.offcanvas');
        }
        toggle(trigger) {
            if (this.isShown) this.hide();
            else this.show(trigger);
        }
        dispose() {
            this.backdrop?.remove();
            this.backdrop = null;
            TailwindOffcanvas.instances.delete(this.element);
        }
        static getInstance(element) {
            return element ? TailwindOffcanvas.instances.get(element) || null : null;
        }
        static getOrCreateInstance(element, options = {}) {
            if (!element) return null;
            const instance = TailwindOffcanvas.getInstance(element) || new TailwindOffcanvas(element, options);
            instance.options = offcanvasOptions(element, {...instance.options, ...options});
            return instance;
        }
    }

    window.bootstrap = window.bootstrap || {};
    window.bootstrap.Modal = window.bootstrap.Modal || TailwindModal;
    window.bootstrap.Tab = window.bootstrap.Tab || TailwindTab;
    window.bootstrap.Dropdown = window.bootstrap.Dropdown || TailwindDropdown;
    window.bootstrap.Toast = window.bootstrap.Toast || TailwindToast;
    window.bootstrap.Offcanvas = window.bootstrap.Offcanvas || TailwindOffcanvas;

    const installJQueryModalAdapter = () => {
        const jquery = window.jQuery;
        if (!jquery?.fn || typeof jquery.fn.modal === 'function') return;
        const previousModal = jquery.fn.modal;

        jquery.fn.modal = function (option, relatedTarget) {
            return this.each(function () {
                const options = option && typeof option === 'object' ? option : {};
                const Modal = window.bootstrap?.Modal || TailwindModal;
                const instance = typeof Modal.getOrCreateInstance === 'function'
                    ? Modal.getOrCreateInstance(this, options)
                    : new Modal(this, options);

                if (typeof option === 'string') {
                    if (typeof instance[option] === 'function') instance[option](relatedTarget);
                    return;
                }
                if (options.show !== false) instance.show(relatedTarget);
            });
        };
        jquery.fn.modal.Constructor = window.bootstrap?.Modal || TailwindModal;
        jquery.fn.modal.noConflict = () => {
            const adapter = jquery.fn.modal;
            jquery.fn.modal = previousModal;
            return adapter;
        };
    };

    const installJQueryOffcanvasAdapter = () => {
        const jquery = window.jQuery;
        if (!jquery?.fn || typeof jquery.fn.offcanvas === 'function') return;
        const previousOffcanvas = jquery.fn.offcanvas;

        jquery.fn.offcanvas = function (option, relatedTarget) {
            return this.each(function () {
                const options = option && typeof option === 'object' ? option : {};
                const Offcanvas = window.bootstrap?.Offcanvas || TailwindOffcanvas;
                const instance = typeof Offcanvas.getOrCreateInstance === 'function'
                    ? Offcanvas.getOrCreateInstance(this, options)
                    : new Offcanvas(this, options);

                if (typeof option === 'string') {
                    if (typeof instance[option] === 'function') instance[option](relatedTarget);
                    return;
                }
                if (options.show === true) instance.show(relatedTarget);
            });
        };
        jquery.fn.offcanvas.Constructor = window.bootstrap?.Offcanvas || TailwindOffcanvas;
        jquery.fn.offcanvas.noConflict = () => {
            const adapter = jquery.fn.offcanvas;
            jquery.fn.offcanvas = previousOffcanvas;
            return adapter;
        };
    };

    installJQueryModalAdapter();
    installJQueryOffcanvasAdapter();
    window.addEventListener('load', installJQueryModalAdapter, {once: true});
    window.addEventListener('load', installJQueryOffcanvasAdapter, {once: true});

    const syncResponsiveMenus = () => {
        const desktop = window.matchMedia('(min-width: 1024px)').matches;
        document.querySelectorAll('[data-tailwind-responsive-menu]').forEach((menu) => {
            if (desktop) {
                menu.hidden = false;
            } else {
                const trigger = document.querySelector('[aria-controls="' + menu.id + '"]');
                menu.hidden = trigger?.getAttribute('aria-expanded') !== 'true';
            }
        });
    };

    document.addEventListener('click', (event) => {
        for (const modal of document.querySelectorAll('[data-tailwind-modal-trigger]')) {
            try {
                const configuredTrigger = event.target.closest(modal.dataset.tailwindModalTrigger);
                if (configuredTrigger) {
                    event.preventDefault();
                    TailwindModal.getOrCreateInstance(modal)?.show(configuredTrigger);
                    return;
                }
            } catch (error) {
                // Ignore an invalid project-supplied selector without breaking other controls.
            }
        }

        const legacyModalTrigger = event.target.closest('[data-bs-toggle="modal"], [data-toggle="modal"], [data-open]');
        if (legacyModalTrigger) {
            const modal = targetFrom(legacyModalTrigger);
            if (!modal?.matches('.tw-modal, .modal, .reveal, [data-tailwind-modal]')) return;
            event.preventDefault();
            TailwindModal.getOrCreateInstance(modal)?.show(legacyModalTrigger);
            return;
        }

        const legacyModalClose = event.target.closest('[data-bs-dismiss="modal"], [data-dismiss="modal"], [data-close]');
        const legacyModal = legacyModalClose?.closest('.tw-modal, .modal, .reveal, [data-tailwind-modal]');
        if (legacyModalClose && legacyModal) {
            TailwindModal.getOrCreateInstance(legacyModal)?.hide();
            return;
        }

        const legacyCollapse = event.target.closest('[data-bs-toggle="collapse"], [data-toggle="collapse"]');
        if (legacyCollapse) {
            event.preventDefault();
            const panel = targetFrom(legacyCollapse);
            if (!panel) return;
            const open = !panel.classList.contains('show');
            toggleOpenState(panel, open);
            toggleState(legacyCollapse, 'collapsed', !open);
            setExpanded(legacyCollapse, open);
            return;
        }

        const legacyTab = event.target.closest('[data-bs-toggle="tab"], [data-bs-toggle="pill"], [data-toggle="tab"], [data-toggle="pill"]');
        if (legacyTab) {
            event.preventDefault();
            TailwindTab.getOrCreateInstance(legacyTab).show();
            return;
        }

        const legacyDropdown = event.target.closest('[data-bs-toggle="dropdown"], [data-toggle="dropdown"], [data-dropdown]');
        if (legacyDropdown) {
            event.preventDefault();
            TailwindDropdown.getOrCreateInstance(legacyDropdown).toggle();
            return;
        }

        const legacyOffcanvasTrigger = event.target.closest('[data-bs-toggle="offcanvas"], [data-toggle="offcanvas"]');
        if (legacyOffcanvasTrigger) {
            event.preventDefault();
            if (legacyOffcanvasTrigger.matches(':disabled, [aria-disabled="true"], .disabled, .tw-disabled')) return;
            const offcanvas = targetFrom(legacyOffcanvasTrigger);
            if (!offcanvas?.matches('.tw-offcanvas, .offcanvas')) return;
            TailwindOffcanvas.getOrCreateInstance(offcanvas)?.toggle(legacyOffcanvasTrigger);
            return;
        }

        const legacyOffcanvasClose = event.target.closest('[data-bs-dismiss="offcanvas"], [data-dismiss="offcanvas"]');
        if (legacyOffcanvasClose) {
            event.preventDefault();
            TailwindOffcanvas.getOrCreateInstance(legacyOffcanvasClose.closest('.tw-offcanvas, .offcanvas'))?.hide();
            return;
        }

        const foundationToggle = event.target.closest('[data-toggle]:not([data-toggle="modal"]):not([data-toggle="collapse"]):not([data-toggle="dropdown"]):not([data-toggle="tab"]):not([data-toggle="pill"]):not([data-toggle="offcanvas"])');
        if (foundationToggle) {
            const target = targetFrom(foundationToggle);
            if (target?.matches('.tw-dropdown-menu, .dropdown-menu, .dropdown-pane')) {
                event.preventDefault();
                TailwindDropdown.getOrCreateInstance(foundationToggle).toggle();
                return;
            }
            if (target?.matches('.tw-modal, .modal, .reveal')) {
                event.preventDefault();
                TailwindModal.getOrCreateInstance(target)?.show(foundationToggle);
                return;
            }
            if (target) {
                event.preventDefault();
                const open = !target.classList.contains('tw-show') && !target.classList.contains('is-active');
                toggleOpenState(target, open);
                setExpanded(foundationToggle, open);
                return;
            }
        }

        const legacyAlertClose = event.target.closest('[data-bs-dismiss="alert"], [data-dismiss="alert"], [data-close]');
        if (legacyAlertClose) {
            legacyAlertClose.closest('.tw-alert, .alert, .callout, .notification')?.remove();
            return;
        }

        const menuTrigger = event.target.closest('[data-tailwind-menu-trigger]');
        if (menuTrigger) {
            event.preventDefault();
            const menu = document.getElementById(menuTrigger.getAttribute('aria-controls'));
            if (!menu) return;
            const open = menu.hidden;
            closeMenus(menu);
            menu.hidden = !open;
            setExpanded(menuTrigger, open);
            return;
        }

        const collapseTrigger = event.target.closest('[data-fusion-collapse-trigger], [data-tailwind-collapse-trigger]');
        if (collapseTrigger) {
            const panel = document.getElementById(collapseTrigger.getAttribute('aria-controls'));
            if (!panel) return;
            const open = panel.hidden;
            const group = panel.dataset.fusionCollapseGroup;

            if (open && group) {
                document.querySelectorAll('[data-fusion-collapse-panel]').forEach((item) => {
                    if (item !== panel && item.dataset.fusionCollapseGroup === group) {
                        item.hidden = true;
                        const trigger = document.querySelector(
                            '[data-fusion-collapse-trigger][aria-controls="' + item.id + '"]'
                        );
                        if (trigger) {
                            setExpanded(trigger, false);
                            const label = trigger.querySelector('[data-fusion-collapse-label]');
                            if (label) label.textContent = trigger.dataset.labelExpand || 'Expand';
                        }
                    }
                });
            }

            panel.hidden = !open;
            setExpanded(collapseTrigger, open);
            const label = collapseTrigger.querySelector('[data-fusion-collapse-label]');
            if (label) {
                label.textContent = open
                    ? (collapseTrigger.dataset.labelClose || 'Close')
                    : (collapseTrigger.dataset.labelExpand || 'Expand');
            }
            panel.dispatchEvent(new CustomEvent(open ? 'shown.bs.collapse' : 'hidden.bs.collapse', {bubbles: true}));
            return;
        }

        const tab = event.target.closest('[data-fusion-tab]');
        if (tab) {
            event.preventDefault();
            activateTailwindTab(tab);
            return;
        }

        const tabNavigation = event.target.closest('[data-fusion-tab-previous], [data-fusion-tab-next]');
        if (tabNavigation) {
            const root = tabNavigation.closest('[data-fusion-tabs-root]');
            const tabs = Array.from(root?.querySelectorAll('[role="tab"]:not([disabled])') || []);
            if (!tabs.length) return;
            const current = Math.max(0, tabs.findIndex((item) => item.getAttribute('aria-selected') === 'true'));
            const direction = tabNavigation.hasAttribute('data-fusion-tab-previous') ? -1 : 1;
            activateTailwindTab(tabs[(current + direction + tabs.length) % tabs.length]);
            return;
        }

        const modalTrigger = event.target.closest('[data-tailwind-modal-open]');
        if (modalTrigger) {
            const modal = document.getElementById(modalTrigger.dataset.tailwindModalOpen);
            TailwindModal.getOrCreateInstance(modal)?.show(modalTrigger);
            return;
        }

        const modalClose = event.target.closest('[data-tailwind-modal-close]');
        if (modalClose) {
            TailwindModal.getOrCreateInstance(modalClose.closest('[data-tailwind-modal]'))?.hide();
            return;
        }

        const noticeClose = event.target.closest('[data-tailwind-notice-close]');
        if (noticeClose) {
            noticeClose.closest('[role="alert"], [role="status"]')?.remove();
            return;
        }

        if (!event.target.closest('[data-tailwind-menu]')) closeMenus();
        if (!event.target.closest('.tw-dropdown, .dropdown, .dropdown-pane')) closeCommunityDropdowns();
    });

    document.addEventListener('keydown', (event) => {
        const dropdownTrigger = event.target.closest?.('[data-bs-toggle="dropdown"], [data-toggle="dropdown"], [data-dropdown]');
        if (dropdownTrigger && ['ArrowDown', 'ArrowUp', 'Home', 'End'].includes(event.key)) {
            event.preventDefault();
            const instance = TailwindDropdown.getOrCreateInstance(dropdownTrigger);
            instance.show();
            const items = Array.from(instance.menu()?.querySelectorAll(
                '.tw-dropdown-item:not(.tw-disabled), .dropdown-item:not(.disabled), a:not([aria-disabled="true"]), button:not([disabled])'
            ) || []);
            const last = event.key === 'ArrowUp' || event.key === 'End';
            items[last ? items.length - 1 : 0]?.focus();
            return;
        }

        const dropdownItem = event.target.closest?.('.tw-dropdown-menu a, .tw-dropdown-menu button, .dropdown-menu a, .dropdown-menu button, .dropdown-pane a, .dropdown-pane button');
        if (dropdownItem && ['ArrowDown', 'ArrowUp', 'Home', 'End', 'Escape'].includes(event.key)) {
            event.preventDefault();
            const menu = dropdownItem.closest('.tw-dropdown-menu, .dropdown-menu, .dropdown-pane');
            const items = Array.from(menu.querySelectorAll(
                '.tw-dropdown-item:not(.tw-disabled), .dropdown-item:not(.disabled), a:not([aria-disabled="true"]), button:not([disabled])'
            ));
            const wrapper = menu.closest('.tw-dropdown, .dropdown') || menu.parentElement;
            const trigger = wrapper?.querySelector('[data-bs-toggle="dropdown"], [data-toggle="dropdown"], [data-dropdown], [aria-haspopup="true"]');
            if (event.key === 'Escape') {
                closeCommunityDropdowns();
                trigger?.focus();
                return;
            }
            const current = Math.max(0, items.indexOf(dropdownItem));
            const next = event.key === 'Home'
                ? 0
                : event.key === 'End'
                    ? items.length - 1
                    : (current + (event.key === 'ArrowUp' ? -1 : 1) + items.length) % items.length;
            items[next]?.focus();
            return;
        }

        const currentTab = event.target.closest?.('[data-fusion-tab][role="tab"]');
        if (currentTab && ['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) {
            const tabs = Array.from(
                currentTab.closest('[role="tablist"]')?.querySelectorAll('[role="tab"]:not([disabled])') || []
            );
            if (tabs.length) {
                event.preventDefault();
                const current = tabs.indexOf(currentTab);
                const next = event.key === 'Home'
                    ? 0
                    : event.key === 'End'
                        ? tabs.length - 1
                        : (current + (event.key === 'ArrowLeft' ? -1 : 1) + tabs.length) % tabs.length;
                activateTailwindTab(tabs[next]);
            }
            return;
        }

        const modal = document.querySelector('[data-tailwind-modal]:not([hidden]):not([aria-hidden="true"]), .modal.show, .tw-modal.tw-show');
        const offcanvas = document.querySelector('.tw-offcanvas.tw-show, .offcanvas.show');
        if (event.key === 'Escape') {
            closeMenus();
            closeCommunityDropdowns();
            const modalInstance = TailwindModal.getOrCreateInstance(modal);
            if (modalInstance?.options.keyboard !== false) modalInstance.hide();
            if (!modal && offcanvas) {
                const instance = TailwindOffcanvas.getOrCreateInstance(offcanvas);
                if (instance?.options.keyboard === false) {
                    dispatchOffcanvasEvent(offcanvas, 'hidePrevented.bs.offcanvas');
                } else {
                    instance?.hide();
                }
            }
            return;
        }

        const focusScope = modal || offcanvas;
        if (event.key === 'Tab' && focusScope) {
            const focusable = Array.from(focusScope.querySelectorAll('button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'));
            if (!focusable.length) return;
            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }
    });

    const initializeCompatibility = () => {
        try {
            observeCompatibilityAliases();
            syncResponsiveMenus();
            restoreTailwindTabs();
            if (document.querySelector('[data-tailwind-modal]:not([hidden])')) {
                document.documentElement.classList.add('tw-overflow-hidden');
            }
        } finally {
            window.FusionUI?.reveal?.();
        }
    };

    window.addEventListener('resize', syncResponsiveMenus);
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeCompatibility, {once: true});
    } else {
        initializeCompatibility();
    }
})();
