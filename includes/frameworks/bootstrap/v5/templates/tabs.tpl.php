<?php

defined('IN_FUSION') || exit;

/**
 * Adapt the shared tab helper contract to PHPFusion's Bootstrap/Tabler tab renderer.
 */
function bootstrap_render_tabs(array $options = []): string
{
    if (!class_exists('FusionTabs')) {
        return '';
    }

    $part = (string)($options['part'] ?? 'header');

    if ($part === 'header') {
        $id = (string)($options['id'] ?? 'custom-tabs');
        $tabs = FusionTabs::getInstance($id);
        if (!empty($options['remember'])) {
            $tabs->setRemember(TRUE);
        }

        return $tabs->openTab(
            (array)($options['tabs'] ?? []),
            (string)($options['active_id'] ?? ''),
            $id,
            $options['link'] ?? FALSE,
            $options['class'] ?? NULL,
            (string)($options['getname'] ?? 'section'),
            (array)($options['cleanup_get'] ?? []),
            (string)($options['wrapper_class'] ?? ''),
            (string)($options['wrapper_header_class'] ?? ''),
            (string)($options['wrapper_body_class'] ?? ''),
            (int)($options['max_tabs'] ?? 0),
            !array_key_exists('has_wrapper', $options) || !empty($options['has_wrapper']),
            (string)($options['header_action'] ?? '')
        );
    }

    if ($part === 'openbody') {
        $group = (string)($options['group_id'] ?? 'custom-tabs');

        return FusionTabs::getInstance($group)->openTabBody(
            (string)($options['id'] ?? ''),
            $options['active_id'] ?? NULL,
            (string)($options['key'] ?? 'section')
        );
    }

    if ($part === 'openwrapper') {
        $group = (string)($options['group_id'] ?? 'custom-tabs');

        return FusionTabs::getInstance($group)->openTabWarapper(
            $group,
            (string)($options['class'] ?? '')
        );
    }

    if ($part === 'closewrapper') {
        return "</div>\n</div>\n<!--End wrapper-->";
    }

    if ($part === 'closebody') {
        return (new FusionTabs())->closeTabBody();
    }

    if ($part === 'footer') {
        return (new FusionTabs())->closeTab([
            'tab_nav' => !empty($options['tab_nav']),
            'has_wrapper' => !array_key_exists('has_wrapper', $options) || !empty($options['has_wrapper']),
        ]);
    }

    return '';
}
