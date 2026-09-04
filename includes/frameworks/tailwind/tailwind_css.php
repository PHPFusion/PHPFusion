<?php

defined('IN_FUSION') || exit;

/**
 * Translate PHPFusion's Bootstrap 5 utility vocabulary into Tailwind.
 * Values remain literal so Tailwind can discover them during compilation.
 */
$tailwind_css = [
    'd-none' => 'tw-hidden',
    'd-inline' => 'tw-inline',
    'd-block' => 'tw-block',
    'd-inline-block' => 'tw-inline-block',
    'd-grid' => 'tw-grid',
    'd-inline-grid' => 'tw-inline-grid',
    'd-table' => 'tw-d-table',
    'd-table-row' => 'tw-d-table-row',
    'd-table-cell' => 'tw-d-table-cell',
    'd-flex' => 'tw-flex',
    'd-inline-flex' => 'tw-inline-flex',
    'hstack' => 'tw-flex tw-flex-row tw-items-center',
    'vstack' => 'tw-flex tw-flex-col',
    'position-relative' => 'tw-relative',
    'position-absolute' => 'tw-absolute',
    'position-fixed' => 'tw-fixed',
    'position-sticky' => 'tw-sticky',
    'flex-row' => 'tw-flex-row',
    'flex-row-reverse' => 'tw-flex-row-reverse',
    'flex-column' => 'tw-flex-col',
    'flex-column-reverse' => 'tw-flex-col-reverse',
    'flex-fill' => 'tw-flex-1',
    'flex-wrap' => 'tw-flex-wrap',
    'flex-wrap-reverse' => 'tw-flex-wrap-reverse',
    'flex-nowrap' => 'tw-flex-nowrap',
    'flex-grow-0' => 'tw-grow-0',
    'flex-grow-1' => 'tw-grow',
    'flex-shrink-0' => 'tw-shrink-0',
    'flex-shrink-1' => 'tw-shrink',
    'align-items-start' => 'tw-items-start',
    'align-items-center' => 'tw-items-center',
    'align-items-end' => 'tw-items-end',
    'align-items-baseline' => 'tw-items-baseline',
    'align-items-stretch' => 'tw-items-stretch',
    'align-self-start' => 'tw-self-start',
    'align-self-center' => 'tw-self-center',
    'align-self-end' => 'tw-self-end',
    'align-self-auto' => 'tw-self-auto',
    'align-self-baseline' => 'tw-self-baseline',
    'align-self-stretch' => 'tw-self-stretch',
    'align-content-start' => 'tw-content-start',
    'align-content-center' => 'tw-content-center',
    'align-content-end' => 'tw-content-end',
    'align-content-between' => 'tw-content-between',
    'align-content-around' => 'tw-content-around',
    'align-content-stretch' => 'tw-content-stretch',
    'justify-content-start' => 'tw-justify-start',
    'justify-content-center' => 'tw-justify-center',
    'justify-content-end' => 'tw-justify-end',
    'justify-content-between' => 'tw-justify-between',
    'justify-content-around' => 'tw-justify-around',
    'justify-content-evenly' => 'tw-justify-evenly',
    'order-first' => 'tw-order-first',
    'order-last' => 'tw-order-last',
    'w-25' => 'tw-w-1/4',
    'w-33' => 'tw-w-1/3',
    'w-50' => 'tw-w-1/2',
    'w-66' => 'tw-w-2/3',
    'w-75' => 'tw-w-3/4',
    'w-100' => 'tw-w-full',
    'w-auto' => 'tw-w-auto',
    'mw-100' => 'tw-max-w-full',
    'vw-100' => 'tw-w-screen',
    'min-vw-100' => 'tw-min-w-screen',
    'h-25' => 'tw-h-1/4',
    'h-33' => 'tw-h-1/3',
    'h-50' => 'tw-h-1/2',
    'h-66' => 'tw-h-2/3',
    'h-75' => 'tw-h-3/4',
    'h-100' => 'tw-h-full',
    'h-auto' => 'tw-h-auto',
    'mh-100' => 'tw-max-h-full',
    'vh-100' => 'tw-h-screen',
    'min-vh-100' => 'tw-min-h-screen',
    'overflow-hidden' => 'tw-overflow-hidden',
    'overflow-auto' => 'tw-overflow-auto',
    'overflow-visible' => 'tw-overflow-visible',
    'overflow-scroll' => 'tw-overflow-scroll',
    'overflow-x-auto' => 'tw-overflow-x-auto',
    'overflow-x-hidden' => 'tw-overflow-x-hidden',
    'overflow-x-visible' => 'tw-overflow-x-visible',
    'overflow-x-scroll' => 'tw-overflow-x-scroll',
    'overflow-y-auto' => 'tw-overflow-y-auto',
    'overflow-y-hidden' => 'tw-overflow-y-hidden',
    'overflow-y-visible' => 'tw-overflow-y-visible',
    'overflow-y-scroll' => 'tw-overflow-y-scroll',
    'object-contain' => 'tw-object-contain',
    'object-cover' => 'tw-object-cover',
    'object-fill' => 'tw-object-fill',
    'object-scale' => 'tw-object-scale-down',
    'object-none' => 'tw-object-none',
    'object-fit-contain' => 'tw-object-contain',
    'object-fit-cover' => 'tw-object-cover',
    'object-fit-fill' => 'tw-object-fill',
    'object-fit-scale' => 'tw-object-scale-down',
    'object-fit-none' => 'tw-object-none',
    'object-center' => 'tw-object-center',
    'object-top' => 'tw-object-top',
    'object-bottom' => 'tw-object-bottom',
    'object-start' => 'tw-object-left',
    'object-end' => 'tw-object-right',
    'visible' => 'tw-visible',
    'invisible' => 'tw-invisible',
    'visually-hidden' => 'tw-sr-only',
    'small' => 'tw-text-sm',
    'fs-1' => 'tw-text-5xl',
    'fs-2' => 'tw-text-4xl',
    'fs-3' => 'tw-text-3xl',
    'fs-4' => 'tw-text-2xl',
    'fs-5' => 'tw-text-xl',
    'fs-6' => 'tw-text-base',
    'fw-normal' => 'tw-font-normal',
    'fw-semibold' => 'tw-font-semibold',
    'fw-bold' => 'tw-font-bold',
    'fw-bolder' => 'tw-font-extrabold',
    'fw-light' => 'tw-font-light',
    'fw-lighter' => 'tw-font-extralight',
    'fw-medium' => 'tw-font-medium',
    'fst-italic' => 'tw-italic',
    'fst-normal' => 'tw-not-italic',
    'lh-1' => 'tw-leading-none',
    'lh-sm' => 'tw-leading-tight',
    'lh-base' => 'tw-leading-normal',
    'lh-lg' => 'tw-leading-loose',
    'text-start' => 'tw-text-start',
    'text-center' => 'tw-text-center',
    'text-end' => 'tw-text-end',
    'text-nowrap' => 'tw-whitespace-nowrap',
    'text-wrap' => 'tw-whitespace-normal',
    'text-break' => 'tw-break-words',
    'text-lowercase' => 'tw-lowercase',
    'text-uppercase' => 'tw-uppercase',
    'text-capitalize' => 'tw-capitalize',
    'text-decoration-none' => 'tw-no-underline',
    'text-decoration-underline' => 'tw-underline',
    'text-decoration-line-through' => 'tw-line-through',
    'text-truncate' => 'tw-truncate',
    'text-muted' => 'tw-text-ui-muted-foreground',
    'border' => 'tw-border tw-border-ui-border',
    'border-0' => 'tw-border-0',
    'border-top' => 'tw-border-t tw-border-ui-border',
    'border-top-0' => 'tw-border-t-0',
    'border-end' => 'tw-border-e tw-border-ui-border',
    'border-end-0' => 'tw-border-e-0',
    'border-bottom' => 'tw-border-b tw-border-ui-border',
    'border-bottom-0' => 'tw-border-b-0',
    'border-start' => 'tw-border-s tw-border-ui-border',
    'border-start-0' => 'tw-border-s-0',
    'border-x' => 'tw-border-x tw-border-ui-border',
    'border-y' => 'tw-border-y tw-border-ui-border',
    'rounded' => 'tw-rounded',
    'rounded-0' => 'tw-rounded-none',
    'rounded-1' => 'tw-rounded-sm',
    'rounded-2' => 'tw-rounded-md',
    'rounded-3' => 'tw-rounded-lg',
    'rounded-circle' => 'tw-rounded-full',
    'rounded-pill' => 'tw-rounded-full',
    'shadow' => 'tw-shadow',
    'shadow-sm' => 'tw-shadow-sm',
    'shadow-lg' => 'tw-shadow-lg',
    'shadow-none' => 'tw-shadow-none',
    'user-select-all' => 'tw-select-all',
    'user-select-auto' => 'tw-select-auto',
    'user-select-none' => 'tw-select-none',
    'pe-none' => 'tw-pointer-events-none',
    'pe-auto' => 'tw-pointer-events-auto',
    'cursor-pointer' => 'tw-cursor-pointer',
    'cursor-default' => 'tw-cursor-default',
    'cursor-not-allowed' => 'tw-cursor-not-allowed',
    'bg-surface' => 'tw-bg-ui-card',
    'bg-surface-secondary' => 'tw-bg-ui-muted',
    'bg-surface-tertiary' => 'tw-bg-ui-muted',
    'bg-muted-lt' => 'tw-bg-ui-muted tw-text-ui-muted-foreground',
    'bg-transparent' => 'tw-bg-transparent',
    'bg-light-subtle' => 'tw-bg-ui-muted',
    'page' => 'tw-page',
    'page-wrapper' => 'tw-page-wrapper',
    'page-header' => 'tw-page-header',
    'page-body' => 'tw-page-body',
    'page-title' => 'tw-page-title',
    'container' => 'tw-container',
    'container-fluid' => 'tw-container tw-container-fluid',
    'container-xl' => 'tw-container tw-container-xl',
    'row' => 'tw-row',
    'col' => 'tw-col',
    'card' => 'tw-card',
    'card-header' => 'tw-card-header',
    'card-body' => 'tw-card-body',
    'card-footer' => 'tw-card-footer',
    'card-title' => 'tw-card-title',
    'card-subtitle' => 'tw-card-subtitle',
    'subheader' => 'tw-text-sm tw-font-semibold tw-text-ui-muted-foreground',
    'spacer-xs' => 'tw-rounded-lg tw-border tw-border-ui-border tw-bg-ui-muted tw-p-3',
    'btn' => 'tw-btn',
    'btn-sm' => 'tw-btn tw-btn-sm',
    'btn-lg' => 'tw-btn tw-btn-lg',
    'btn-primary' => 'tw-btn tw-btn-primary',
    'btn-secondary' => 'tw-btn tw-btn-secondary',
    'btn-default' => 'tw-btn tw-btn-default',
    'btn-outline' => 'tw-btn tw-btn-outline',
    'btn-outline-primary' => 'tw-btn tw-btn-outline-primary',
    'btn-outline-secondary' => 'tw-btn tw-btn-outline-secondary',
    'btn-outline-success' => 'tw-btn tw-btn-outline-success',
    'btn-outline-warning' => 'tw-btn tw-btn-outline-warning',
    'btn-outline-danger' => 'tw-btn tw-btn-outline-danger',
    'btn-outline-info' => 'tw-btn tw-btn-outline-info',
    'btn-success' => 'tw-btn tw-btn-success',
    'btn-warning' => 'tw-btn tw-btn-warning',
    'btn-danger' => 'tw-btn tw-btn-danger',
    'btn-info' => 'tw-btn tw-btn-info',
    'btn-link' => 'tw-btn tw-btn-link',
    'btn-group' => 'tw-btn-group',
    'btn-group-sm' => 'tw-btn-group tw-btn-group-sm',
    'btn-group-lg' => 'tw-btn-group tw-btn-group-lg',
    'btn-check' => 'tw-btn-check',
    'dropdown' => 'tw-dropdown',
    'dropdown-toggle' => 'tw-dropdown-toggle',
    'dropdown-menu' => 'tw-dropdown-menu',
    'dropdown-menu-end' => 'tw-end-0',
    'dropdown-item' => 'tw-dropdown-item',
    'dropdown-header' => 'tw-dropdown-header',
    'dropdown-divider' => 'tw-dropdown-divider',
    'form-group' => 'tw-form-group',
    'form-floating' => 'tw-form-floating',
    'form-label' => 'tw-form-label',
    'control-label' => 'tw-form-label',
    'col-form-label' => 'tw-form-label',
    'form-control' => 'tw-form-control',
    'form-control-sm' => 'tw-form-control-sm',
    'form-control-lg' => 'tw-form-control-lg',
    'form-select' => 'tw-form-select',
    'input-group' => 'tw-input-group',
    'input-group-sm' => 'tw-input-group tw-input-group-sm',
    'input-group-lg' => 'tw-input-group tw-input-group-lg',
    'input-group-text' => 'tw-input-group-text',
    'input-group-addon' => 'tw-input-group-addon',
    'input-group-prepend' => 'tw-input-group-prepend',
    'input-group-append' => 'tw-input-group-append',
    'input-group-btn' => 'tw-input-group-btn',
    'form-text' => 'tw-form-text',
    'help-block' => 'tw-form-text',
    'invalid-feedback' => 'tw-invalid-feedback',
    'valid-feedback' => 'tw-valid-feedback',
    'has-error' => 'tw-has-error',
    'is-invalid' => 'tw-is-invalid',
    'is-valid' => 'tw-is-valid',
    'form-check' => 'tw-form-check',
    'form-check-inline' => 'tw-form-check-inline',
    'form-check-input' => 'tw-form-check-input',
    'form-check-label' => 'tw-form-check-label',
    'form-switch' => 'tw-form-switch',
    'form-switch-lg' => 'tw-form-switch tw-form-switch-lg',
    'form-range' => 'tw-form-range',
    'badge' => 'tw-badge',
    'bg-primary-lt' => 'tw-bg-primary-lt',
    'bg-success-lt' => 'tw-bg-success-lt',
    'bg-danger-lt' => 'tw-bg-danger-lt',
    'bg-warning-lt' => 'tw-bg-warning-lt',
    'bg-info-lt' => 'tw-bg-info-lt',
    'avatar' => 'tw-avatar',
    'avatar-xs' => 'tw-avatar tw-avatar-xs',
    'avatar-sm' => 'tw-avatar tw-avatar-sm',
    'avatar-md' => 'tw-avatar tw-avatar-md',
    'avatar-lg' => 'tw-avatar tw-avatar-lg',
    'table' => 'tw-table',
    'table-responsive' => 'tw-table-responsive',
    'table-hover' => 'tw-table-hover',
    'table-striped' => 'tw-table-striped',
    'table-active' => 'tw-table-active',
    'status' => 'tw-status',
    'status-dot' => 'tw-status-dot',
    'status-green' => 'tw-status-green',
    'status-red' => 'tw-status-red',
    'status-yellow' => 'tw-status-yellow',
    'status-blue' => 'tw-status-blue',
    'status-azure' => 'tw-status-blue',
    'status-orange' => 'tw-status-orange',
    'status-gray' => 'tw-status-gray',
];

$spacing_scale = ['0' => '0', '1' => '1', '2' => '2', '3' => '4', '4' => '6', '5' => '8', '6' => '10'];
$breakpoints = ['sm' => 'sm:', 'md' => 'md:', 'lg' => 'lg:', 'xl' => 'xl:', 'xxl' => '2xl:'];
$margin_utilities = ['m', 'mt', 'mb', 'ms', 'me', 'mx', 'my'];
$padding_utilities = ['p', 'pt', 'pb', 'ps', 'pe', 'px', 'py'];

foreach ($spacing_scale as $source_space => $tailwind_space) {
    foreach (array_merge($margin_utilities, $padding_utilities) as $utility) {
        $tailwind_css[$utility.'-'.$source_space] = 'tw-'.$utility.'-'.$tailwind_space;
    }
    foreach (['gap' => 'gap', 'row-gap' => 'gap-y', 'column-gap' => 'gap-x'] as $source => $target) {
        $tailwind_css[$source.'-'.$source_space] = 'tw-'.$target.'-'.$tailwind_space;
    }
    $tailwind_css['w-'.$source_space] = 'tw-w-'.$tailwind_space;
    $tailwind_css['h-'.$source_space] = 'tw-h-'.$tailwind_space;
    $tailwind_css['space-x-'.$source_space] = 'tw-flex tw-gap-'.$tailwind_space;
    $tailwind_css['space-y-'.$source_space] = 'tw-flex tw-flex-col tw-gap-'.$tailwind_space;
    $tailwind_css['divide-x-'.$source_space] = 'tw-divide-x tw-divide-ui-border';
    $tailwind_css['divide-y-'.$source_space] = 'tw-divide-y tw-divide-ui-border';

    foreach ($breakpoints as $breakpoint => $prefix) {
        foreach (array_merge($margin_utilities, $padding_utilities) as $utility) {
            $tailwind_css[$utility.'-'.$breakpoint.'-'.$source_space] = $prefix.'tw-'.$utility.'-'.$tailwind_space;
        }
        foreach (['gap' => 'gap', 'row-gap' => 'gap-y', 'column-gap' => 'gap-x'] as $source => $target) {
            $tailwind_css[$source.'-'.$breakpoint.'-'.$source_space] = $prefix.'tw-'.$target.'-'.$tailwind_space;
        }
    }

    if ($source_space !== '0') {
        foreach ($margin_utilities as $utility) {
            $tailwind_css[$utility.'-n'.$source_space] = '-tw-'.$utility.'-'.$tailwind_space;
            foreach ($breakpoints as $breakpoint => $prefix) {
                $tailwind_css[$utility.'-'.$breakpoint.'-n'.$source_space] = $prefix.'-tw-'.$utility.'-'.$tailwind_space;
            }
        }
    }
}

/*
 * Complete Bootstrap's grid vocabulary for framework_css(). Keep this
 * separate from the responsive utility breakpoint map used below.
 */
$grid_breakpoints = [
    '' => '',
    'xs' => '',
    'sm' => 'sm:',
    'md' => 'md:',
    'lg' => 'lg:',
    'xl' => 'xl:',
    'xxl' => '2xl:',
];
$column_widths = [
    1 => '1/12', 2 => '1/6', 3 => '1/4', 4 => '1/3', 5 => '5/12', 6 => '1/2',
    7 => '7/12', 8 => '2/3', 9 => '3/4', 10 => '5/6', 11 => '11/12', 12 => 'full',
];
foreach ($grid_breakpoints as $source_breakpoint => $tailwind_breakpoint) {
    $source_prefix = $source_breakpoint === '' ? 'col' : 'col-'.$source_breakpoint;
    $mobile_default = $tailwind_breakpoint === '' ? '' : 'tw-w-full ';
    if ($source_breakpoint !== '') {
        $tailwind_css[$source_prefix] = $tailwind_breakpoint === ''
            ? 'tw-col'
            : $mobile_default.$tailwind_breakpoint.'tw-w-auto '.$tailwind_breakpoint.'tw-flex-1';
    }
    $tailwind_css[$source_prefix.'-auto'] = $mobile_default.$tailwind_breakpoint.'tw-w-auto';
    foreach ($column_widths as $column => $width) {
        $tailwind_css[$source_prefix.'-'.$column] =
            $mobile_default.$tailwind_breakpoint.'tw-w-'.$width;
    }
}

foreach ($grid_breakpoints as $source_breakpoint => $tailwind_breakpoint) {
    if ($source_breakpoint === 'xs') {
        continue;
    }
    $source_prefix = $source_breakpoint === '' ? 'offset' : 'offset-'.$source_breakpoint;
    for ($offset = 0; $offset <= 11; $offset++) {
        $source_class = $source_prefix.'-'.$offset;
        // The bundled Bootstrap-compatible flex fallback owns percentage offsets.
        $tailwind_css[$source_class] = $source_class;
    }
}

foreach ($margin_utilities as $utility) {
    $tailwind_css[$utility.'-auto'] = 'tw-'.$utility.'-auto';
    foreach ($breakpoints as $breakpoint => $prefix) {
        $tailwind_css[$utility.'-'.$breakpoint.'-auto'] = $prefix.'tw-'.$utility.'-auto';
    }
}

$responsive_utilities = [
    'd-none' => 'tw-hidden', 'd-inline' => 'tw-inline', 'd-block' => 'tw-block',
    'd-inline-block' => 'tw-inline-block', 'd-grid' => 'tw-grid', 'd-inline-grid' => 'tw-inline-grid',
    'd-table' => 'tw-d-table', 'd-table-row' => 'tw-d-table-row', 'd-table-cell' => 'tw-d-table-cell',
    'd-flex' => 'tw-flex', 'd-inline-flex' => 'tw-inline-flex',
    'flex-row' => 'tw-flex-row', 'flex-row-reverse' => 'tw-flex-row-reverse',
    'flex-column' => 'tw-flex-col', 'flex-column-reverse' => 'tw-flex-col-reverse',
    'flex-fill' => 'tw-flex-1', 'flex-wrap' => 'tw-flex-wrap',
    'flex-wrap-reverse' => 'tw-flex-wrap-reverse', 'flex-nowrap' => 'tw-flex-nowrap',
    'flex-grow-0' => 'tw-grow-0', 'flex-grow-1' => 'tw-grow',
    'flex-shrink-0' => 'tw-shrink-0', 'flex-shrink-1' => 'tw-shrink',
    'justify-content-start' => 'tw-justify-start', 'justify-content-center' => 'tw-justify-center',
    'justify-content-end' => 'tw-justify-end', 'justify-content-between' => 'tw-justify-between',
    'justify-content-around' => 'tw-justify-around', 'justify-content-evenly' => 'tw-justify-evenly',
    'align-items-start' => 'tw-items-start', 'align-items-center' => 'tw-items-center',
    'align-items-end' => 'tw-items-end', 'align-items-baseline' => 'tw-items-baseline',
    'align-items-stretch' => 'tw-items-stretch', 'align-content-start' => 'tw-content-start',
    'align-content-center' => 'tw-content-center', 'align-content-end' => 'tw-content-end',
    'align-content-between' => 'tw-content-between', 'align-content-around' => 'tw-content-around',
    'align-content-stretch' => 'tw-content-stretch', 'align-self-auto' => 'tw-self-auto',
    'align-self-start' => 'tw-self-start', 'align-self-center' => 'tw-self-center',
    'align-self-end' => 'tw-self-end', 'align-self-baseline' => 'tw-self-baseline',
    'align-self-stretch' => 'tw-self-stretch', 'text-start' => 'tw-text-start',
    'text-center' => 'tw-text-center', 'text-end' => 'tw-text-end',
    'float-start' => 'tw-float-start', 'float-end' => 'tw-float-end', 'float-none' => 'tw-float-none',
    'object-fit-contain' => 'tw-object-contain', 'object-fit-cover' => 'tw-object-cover',
    'object-fit-fill' => 'tw-object-fill', 'object-fit-scale' => 'tw-object-scale-down',
    'object-fit-none' => 'tw-object-none',
];

foreach ($breakpoints as $breakpoint => $prefix) {
    foreach ($responsive_utilities as $source => $target) {
        if (str_starts_with($source, 'justify-content-')) {
            $responsive_source = 'justify-content-'.$breakpoint.'-'.substr($source, 16);
        } elseif (str_starts_with($source, 'object-fit-')) {
            $responsive_source = 'object-fit-'.$breakpoint.'-'.substr($source, 11);
        } elseif (preg_match('/^align-(items|content|self)-(.+)$/', $source, $match)) {
            $responsive_source = 'align-'.$match[1].'-'.$breakpoint.'-'.$match[2];
        } else {
            $parts = explode('-', $source, 2);
            $responsive_source = $parts[0].'-'.$breakpoint.'-'.$parts[1];
        }
        $tailwind_css[$responsive_source] = $prefix.$target;
    }
    foreach (['first', 'last', '0', '1', '2', '3', '4', '5'] as $order) {
        $tailwind_css['order-'.$breakpoint.'-'.$order] = $prefix.'tw-order-'.$order;
    }
}

foreach (['0', '1', '2', '3', '4', '5'] as $order) {
    $tailwind_css['order-'.$order] = 'tw-order-'.$order;
}

return $tailwind_css;
