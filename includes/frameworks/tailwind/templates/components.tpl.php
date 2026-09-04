<?php

defined('IN_FUSION') || exit;

function tailwind_escape(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function tailwind_class(string $base, mixed $additional = ''): string
{
    $additional = preg_replace('/[^a-zA-Z0-9_:\-\[\]\/.% ]/', '', (string)$additional);

    return trim($base.' '.(function_exists('framework_css') ? framework_css($additional) : $additional));
}

function tailwind_nav_has_destination(array $item): bool
{
    if (array_key_exists('link_has_destination', $item)) {
        return !empty($item['link_has_destination']);
    }

    $url = trim((string)($item['link_url'] ?? ''));
    return $url !== '' && $url !== '#';
}

function tailwind_nav_content(array $item, bool $show_description = false): string
{
    $html = ($item['link_icon'] ?? '').'<span>'.($item['link_name'] ?? '').'</span>';
    if ($show_description && !empty($item['link_description'])) {
        $html .= '<span class="tw-text-xs tw-font-normal tw-leading-5 tw-text-ui-muted-foreground">'.
            tailwind_escape($item['link_description']).'</span>';
    }

    return $html;
}

function tailwind_nav_attributes(array $item, string $key = 'link_attr'): string
{
    $href = (string)($item['link_url'] ?? '#');
    $attributes = trim((string)($item[$key] ?? ''));
    if ($attributes === '' || !preg_match('/\bhref\s*=/', $attributes)) {
        $attributes = 'href="'.tailwind_escape($href).'" '.$attributes;
    }

    return trim((string)preg_replace('/\s*data-bs-toggle="dropdown"/', '', $attributes));
}

function tailwind_nav_link(array $item, string $extra_class = '', bool $show_description = false): string
{
    $base = 'tw-flex tw-min-h-11 tw-w-full tw-items-center tw-gap-2 tw-rounded-lg tw-px-3 tw-py-2 tw-text-sm tw-font-medium tw-text-ui-card-foreground tw-transition-colors hover:tw-bg-ui-accent focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-ui-ring motion-reduce:tw-transition-none';
    if ($show_description) {
        $base .= ' tw-flex-col tw-items-start';
    }

    return '<a class="'.tailwind_class(trim($base.' '.$extra_class), $item['link_child_class'] ?? 'dropdown-item').'" '.
        tailwind_nav_attributes($item, 'link_child_attr').'>'.tailwind_nav_content($item, $show_description).'</a>';
}

function tailwind_render_mega_menu(array $parent, array $items, string $menu_id): string
{
    $children = array_values(array_filter(
        (array)($items[(int)$parent['link_id']] ?? []),
        static fn($row): bool => is_array($row) && empty($row['separator'])
    ));
    $column_count = min(3, max(1, count($children)));
    $control_id = $menu_id.'-'.(int)$parent['link_id'];
    $html = '<ul id="'.tailwind_escape($control_id).'" class="tw-nav-dropdown-menu tw-nav-mega-menu tw-rounded-xl tw-border tw-border-ui-border tw-bg-ui-card tw-text-ui-card-foreground tw-shadow-menu" data-tailwind-menu data-tailwind-mega-menu style="--fusion-mega-columns:'.$column_count.'" hidden>';

    foreach ($children as $child) {
        $html .= '<li class="tw-nav-menu-column" role="none">';
        $html .= '<div class="tw-nav-menu-heading tw-flex tw-items-center tw-gap-2 tw-text-xs tw-font-semibold tw-text-ui-muted-foreground">'.
            tailwind_nav_content($child).'</div>';
        if (tailwind_nav_has_destination($child)) {
            $html .= tailwind_nav_link($child, 'tw-nav-menu-parent-link', true);
        }
        if (!empty($child['link_child'])) {
            $html .= '<ul class="tw-nav-menu-list">'.
                tailwind_render_nav_items((int)$child['link_id'], $items, $menu_id, 1).'</ul>';
        }
        $html .= '</li>';
    }

    return $html.'</ul>';
}

function tailwind_render_nav_items(int $parent, array $items, string $menu_id, int $depth = 0): string
{
    $html = '';

    foreach ((array)($items[$parent] ?? []) as $item) {
        if (!empty($item['separator'])) {
            $html .= '<li class="tw-my-1 tw-border-t tw-border-ui-border" role="separator"></li>';
            continue;
        }

        $id = (string)($item['link_id'] ?? uniqid('nav-', FALSE));
        $children = !empty($item['link_child']) || !empty($items[(int)$id]);
        $name = tailwind_nav_content($item);
        $href = (string)($item['link_url'] ?? '#');
        $attributes = tailwind_nav_attributes($item);

        $structural_class = 'tw-relative'.($children && $depth > 0 ? ' tw-nav-dropend' : '');
        $html .= '<li class="'.tailwind_class($structural_class, $item['li_class'] ?? '').'">';
        if (!empty($item['link_content'])) {
            $html .= $item['link_content'];
        } elseif ($children) {
            $control_id = $menu_id.'-'.$id;
            $drop_style = strtolower(trim((string)($item['link_drop_style'] ?? 'default')));
            $is_mega = $drop_style === 'mega';
            $html .= '<a class="tw-flex tw-min-h-11 tw-w-full tw-cursor-pointer tw-items-center tw-gap-2 tw-rounded-lg tw-px-3 tw-py-2 tw-text-left tw-text-sm tw-font-medium tw-text-ui-card-foreground tw-transition-colors hover:tw-bg-ui-accent focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-ui-ring motion-reduce:tw-transition-none" '.$attributes.' data-tailwind-menu-trigger aria-controls="'.tailwind_escape($control_id).'">'.$name.($item['link_caret'] ?? '').'</a>';
            if ($is_mega) {
                $html .= tailwind_render_mega_menu($item, $items, $menu_id);
            } else {
                $html .= '<ul id="'.tailwind_escape($control_id).'" class="tw-nav-dropdown-menu tw-min-w-56 tw-rounded-xl tw-border tw-border-ui-border tw-bg-ui-card tw-text-ui-card-foreground tw-shadow-menu" data-tailwind-menu hidden>';
                if (tailwind_nav_has_destination($item)) {
                    $html .= '<li class="tw-nav-menu-parent" role="none">'.tailwind_nav_link($item).'</li>';
                }
                $html .= tailwind_render_nav_items((int)$item['link_id'], $items, $menu_id, $depth + 1).'</ul>';
            }
        } elseif ($href !== '') {
            $html .= '<a class="'.tailwind_class('tw-flex tw-min-h-11 tw-items-center tw-gap-2 tw-rounded-lg tw-px-3 tw-py-2 tw-text-sm tw-font-medium tw-text-ui-card-foreground tw-transition-colors hover:tw-bg-ui-accent focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-ui-ring motion-reduce:tw-transition-none', $item['link_class'] ?? '').'" '.$attributes.'>'.$name.($item['link_caret'] ?? '').'</a>';
        } else {
            $html .= '<span class="tw-flex tw-min-h-11 tw-items-center tw-gap-2 tw-px-3 tw-py-2 tw-text-sm tw-font-medium tw-text-ui-muted-foreground">'.$name.'</span>';
        }
        $html .= '</li>';
    }

    return $html;
}

function tailwind_render_navbar(array $info): string
{
    $id = tailwind_escape($info['id'] ?? 'tailwind-navbar');
    $container = !empty($info['container']) ? 'tw-mx-auto tw-w-full tw-max-w-7xl tw-px-4' : 'tw-w-full tw-px-4';
    $brand = (string)($info['navbar_header'] ?? '');
    if ($brand === '' && !empty($info['show_header'])) {
        $brand_content = !empty($info['show_banner']) ? ($info['banner'] ?? '') : tailwind_escape(fusion_get_settings('sitename'));
        $brand_href = (string)($info['navbar_link'] ?? BASEDIR.fusion_get_settings('opening_page'));
        $brand = '<a class="tw-inline-flex tw-min-h-11 tw-items-center tw-font-semibold tw-text-ui-foreground focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-ui-ring" href="'.tailwind_escape($brand_href).'">'.$brand_content.'</a>';
    }
    $primary = (array)($info['primary_callback_nav'] ?? []);
    $secondary = (array)($info['secondary_callback_nav'] ?? []);

    $html = '<nav id="'.$id.'" class="'.tailwind_class('tw-fusion-navbar tw-relative tw-z-40 tw-w-full tw-border-b tw-border-ui-border tw-bg-ui-card tw-text-ui-card-foreground', $info['navbar_class'] ?? '').'" aria-label="'.tailwind_escape($info['aria_label'] ?? 'Primary navigation').'">';
    $html .= '<div class="'.$container.'"><div class="tw-flex tw-min-h-16 tw-items-center tw-justify-between tw-gap-3">';
    $html .= $brand.($info['custom_header'] ?? '');
    if (!empty($info['responsive'])) {
        $html .= '<button type="button" class="tw-inline-flex tw-min-h-11 tw-min-w-11 tw-cursor-pointer tw-items-center tw-justify-center tw-rounded-lg tw-border tw-border-ui-border tw-bg-ui-card tw-text-ui-foreground hover:tw-bg-ui-accent focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-ui-ring lg:tw-hidden" data-tailwind-collapse-trigger aria-controls="'.$id.'-menu" aria-expanded="false" aria-label="Toggle navigation"><span aria-hidden="true">☰</span></button>';
        $html .= '<div id="'.$id.'-menu" class="tw-absolute tw-inset-x-0 tw-top-full tw-border-b tw-border-ui-border tw-bg-ui-card tw-p-3 lg:tw-static lg:tw-flex lg:tw-flex-1 lg:tw-items-center lg:tw-border-0 lg:tw-bg-transparent lg:tw-p-0" data-tailwind-responsive-menu hidden>';
    } else {
        $html .= '<div id="'.$id.'-menu" class="tw-static tw-flex tw-flex-1 tw-items-center">';
    }
    $html .= ($info['html_pre_content'] ?? '');
    $html .= '<ul class="'.tailwind_class('tw-flex tw-flex-col tw-gap-1 lg:tw-flex-row lg:tw-items-center', $info['nav_class'] ?? '').'">'.tailwind_render_nav_items(0, $primary, $id.'-primary').'</ul>';
    $html .= ($info['html_content'] ?? '');
    if ($secondary) {
        $html .= '<ul class="'.tailwind_class('tw-mt-2 tw-flex tw-flex-col tw-gap-1 lg:tw-ml-auto lg:tw-mt-0 lg:tw-flex-row lg:tw-items-center', $info['additional_nav_class'] ?? '').'">'.tailwind_render_nav_items(0, $secondary, $id.'-secondary').'</ul>';
    }
    if (!empty($info['language_switcher']) && function_exists('fusion_get_language_switch')) {
        $languages = fusion_get_language_switch();
        if (count($languages) > 1) {
            $current_key = defined('LANGUAGE') ? LANGUAGE : array_key_first($languages);
            $current = $languages[$current_key] ?? reset($languages);
            $language_id = $id.'-languages';
            $html .= '<div class="tw-relative lg:tw-ml-auto"><button type="button" class="tw-inline-flex tw-min-h-11 tw-cursor-pointer tw-items-center tw-gap-2 tw-rounded-lg tw-px-3 tw-py-2 tw-text-sm tw-font-medium hover:tw-bg-ui-accent focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-ui-ring" data-tailwind-menu-trigger aria-controls="'.$language_id.'" aria-expanded="false"><img class="tw-size-5 tw-rounded-sm" src="'.tailwind_escape($current['language_icon_s'] ?? '').'" alt="">'.tailwind_escape($current['language_name'] ?? $current_key).'</button><ul id="'.$language_id.'" class="tw-absolute tw-right-0 tw-z-50 tw-mt-1 tw-min-w-52 tw-rounded-xl tw-border tw-border-ui-border tw-bg-ui-card tw-p-1 tw-shadow-menu" data-tailwind-menu hidden>';
            foreach ($languages as $language) {
                $html .= '<li><a class="tw-flex tw-min-h-11 tw-items-center tw-gap-2 tw-rounded-lg tw-px-3 tw-py-2 tw-text-sm hover:tw-bg-ui-accent focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-ui-ring" href="'.tailwind_escape($language['language_link'] ?? '#').'"><img class="tw-size-5 tw-rounded-sm" src="'.tailwind_escape($language['language_icon_s'] ?? '').'" alt="">'.tailwind_escape($language['language_name'] ?? '').'</a></li>';
            }
            $html .= '</ul></div>';
        }
    }
    if (!empty($info['searchbar'])) {
        $search_input = $info['search_input'] ?? '<label class="tw-sr-only" for="'.$id.'-search">Search</label><input id="'.$id.'-search" name="stext" type="search" class="tw-min-h-11 tw-w-full tw-rounded-lg tw-border tw-border-ui-input tw-bg-ui-card tw-px-3 tw-py-2 tw-text-base tw-text-ui-foreground focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-ui-ring" placeholder="Search">';
        $html .= '<form class="tw-mt-2 lg:tw-ml-auto lg:tw-mt-0" role="search" method="post" action="'.tailwind_escape($info['search_uri'] ?? BASEDIR.'search.php?stype=all').'">'.$search_input.'</form>';
    }
    $html .= ($info['html_post_content'] ?? '').'</div></div></div></nav>';

    return $html;
}

function tailwind_input_attributes(string $name, array $options, string $type = 'text'): string
{
    $attributes = [
        'type' => $type,
        'name' => $name,
        'id' => $options['input_id'] ?? $name,
    ];

    foreach (['placeholder', 'autocomplete', 'min', 'max', 'step', 'pattern'] as $attribute) {
        if (isset($options[$attribute]) && $options[$attribute] !== '') {
            $attributes[$attribute] = $options[$attribute];
        }
    }
    if (!empty($options['required'])) $attributes['required'] = 'required';
    if (!empty($options['deactivate']) || !empty($options['disabled'])) $attributes['disabled'] = 'disabled';
    if (!empty($options['readonly'])) $attributes['readonly'] = 'readonly';

    foreach ((array)($options['data'] ?? []) as $key => $value) {
        $attributes['data-'.preg_replace('/[^a-z0-9_-]/i', '', (string)$key)] = $value;
    }

    $html = '';
    foreach ($attributes as $key => $value) {
        $html .= ' '.tailwind_escape($key);
        if ($value !== $key) $html .= '="'.tailwind_escape($value).'"';
    }

    return $html;
}

function tailwind_render_dynamic_ui(array $args): string
{
    $name = (string)($args['input_name'] ?? '');
    $label = (string)($args['input_label'] ?? '');
    $value = $args['input_value'] ?? '';
    $options = (array)($args['input_options'] ?? []);
    $type = (string)($options['template_type'] ?? $options['type'] ?? 'text');
    $id = (string)($options['input_id'] ?? $name);
    $field_class = tailwind_class('tw-mb-4 tw-w-full', $options['class'] ?? '');
    $control_class = tailwind_class('tw-min-h-11 tw-w-full tw-rounded-lg tw-border tw-border-ui-input tw-bg-ui-card tw-px-3 tw-py-2 tw-text-base tw-text-ui-foreground tw-shadow-sm tw-outline-none tw-transition-colors placeholder:tw-text-ui-muted-foreground focus-visible:tw-border-ui-ring focus-visible:tw-ring-2 focus-visible:tw-ring-ui-ring/25 disabled:tw-cursor-not-allowed disabled:tw-opacity-50 motion-reduce:tw-transition-none', $options['inner_class'] ?? '');
    $required = !empty($options['required']) ? '<span class="tw-ml-1 tw-text-ui-destructive" aria-hidden="true">*</span>' : '';
    $description_id = !empty($options['ext_tip']) ? $id.'-description' : '';

    if ($type === 'hidden') {
        return '<input'.tailwind_input_attributes($name, $options, 'hidden').' value="'.tailwind_escape($value).'">';
    }

    $html = '<div id="'.tailwind_escape($id).'-field" class="'.$field_class.'">';

    if ($type === 'checkbox') {
        $checked = !empty($value) ? ' checked' : '';
        $html .= '<label class="tw-flex tw-min-h-11 tw-cursor-pointer tw-items-start tw-gap-3 tw-text-sm tw-font-medium tw-text-ui-foreground">';
        $html .= '<input'.tailwind_input_attributes($name, $options, 'checkbox').' value="'.tailwind_escape($options['value'] ?? '1').'" class="tw-mt-1 tw-size-4 tw-rounded tw-border-ui-input tw-text-ui-primary focus-visible:tw-ring-2 focus-visible:tw-ring-ui-ring"'.$checked.'>';
        $html .= '<span>'.$label.$required;
        if ($description_id) $html .= '<span id="'.tailwind_escape($description_id).'" class="tw-mt-1 tw-block tw-font-normal tw-text-ui-muted-foreground">'.$options['ext_tip'].'</span>';
        $html .= '</span></label>';
    } elseif ($type === 'button') {
        $html .= '<button'.tailwind_input_attributes($name, $options, (string)($options['button_type'] ?? 'submit')).' value="'.tailwind_escape($value).'" class="'.tailwind_class('tw-inline-flex tw-min-h-11 tw-cursor-pointer tw-items-center tw-justify-center tw-rounded-lg tw-bg-ui-primary tw-px-4 tw-py-2 tw-text-sm tw-font-semibold tw-text-ui-primary-foreground tw-transition-colors hover:tw-opacity-90 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-ui-ring disabled:tw-cursor-not-allowed disabled:tw-opacity-50 motion-reduce:tw-transition-none', $options['inner_class'] ?? '').'">'.$label.'</button>';
    } else {
        if ($label !== '') {
            $html .= '<label for="'.tailwind_escape($id).'" class="tw-mb-2 tw-block tw-text-sm tw-font-medium tw-text-ui-foreground">'.$label.$required.'</label>';
        }

        if ($type === 'textarea') {
            $html .= '<textarea name="'.tailwind_escape($name).'" id="'.tailwind_escape($id).'" class="'.$control_class.'" rows="'.max(3, (int)($options['rows'] ?? 5)).'"'.($description_id ? ' aria-describedby="'.tailwind_escape($description_id).'"' : '').'>'.tailwind_escape($value).'</textarea>';
        } elseif ($type === 'dropdown' || $type === 'select') {
            $html .= '<select name="'.tailwind_escape($name).'" id="'.tailwind_escape($id).'" class="'.$control_class.'"'.($description_id ? ' aria-describedby="'.tailwind_escape($description_id).'"' : '').'>';
            foreach ((array)($options['options'] ?? []) as $option_value => $option_label) {
                $selected = (string)$option_value === (string)$value ? ' selected' : '';
                $html .= '<option value="'.tailwind_escape($option_value).'"'.$selected.'>'.tailwind_escape($option_label).'</option>';
            }
            $html .= '</select>';
        } else {
            $input_type = in_array($type, ['text', 'number', 'password', 'email', 'url', 'search', 'tel', 'date', 'time', 'color'], TRUE) ? $type : 'text';
            $html .= '<input'.tailwind_input_attributes($name, $options, $input_type).' value="'.tailwind_escape($value).'" class="'.$control_class.'"'.($description_id ? ' aria-describedby="'.tailwind_escape($description_id).'"' : '').'>';
        }

        if ($description_id) {
            $html .= '<p id="'.tailwind_escape($description_id).'" class="tw-mt-2 tw-text-sm tw-leading-6 tw-text-ui-muted-foreground">'.$options['ext_tip'].'</p>';
        }
    }

    if (!empty($options['error_text'])) {
        $html .= '<p class="tw-mt-2 tw-text-sm tw-text-ui-destructive" role="alert">'.$options['error_text'].'</p>';
    }

    return $html.'</div>';
}

function tailwind_semantic_tone(mixed $class, string $fallback = 'secondary'): string
{
    $value = strtolower((string)$class);

    return match (TRUE) {
        str_contains($value, 'danger'), str_contains($value, 'destructive'), str_contains($value, 'error') => 'destructive',
        str_contains($value, 'warning') => 'warning',
        str_contains($value, 'success') => 'success',
        str_contains($value, 'info') => 'info',
        str_contains($value, 'primary') => 'primary',
        default => $fallback,
    };
}

function tailwind_tone_class(string $component, string $tone): string
{
    $classes = [
        'alert' => [
            'primary' => 'tw-alert-primary',
            'secondary' => 'tw-alert-secondary',
            'success' => 'tw-alert-success',
            'warning' => 'tw-alert-warning',
            'info' => 'tw-alert-info',
            'destructive' => 'tw-alert-destructive',
        ],
        'badge' => [
            'primary' => 'tw-badge-primary',
            'secondary' => 'tw-badge-secondary',
            'success' => 'tw-badge-success',
            'warning' => 'tw-badge-warning',
            'info' => 'tw-badge-info',
            'destructive' => 'tw-badge-destructive',
        ],
        'progress' => [
            'primary' => 'tw-progress-indicator-primary',
            'success' => 'tw-progress-indicator-success',
            'warning' => 'tw-progress-indicator-warning',
            'info' => 'tw-progress-indicator-info',
            'destructive' => 'tw-progress-indicator-destructive',
        ],
    ];

    return $classes[$component][$tone] ?? $classes[$component]['secondary'] ?? $classes[$component]['primary'] ?? '';
}

function tailwind_render_alert(array $input): string
{
    $options = (array)($input['options'] ?? []);
    $tone = tailwind_semantic_tone($options['class'] ?? 'alert-danger', 'destructive');
    $role = $tone === 'destructive' ? 'alert' : 'status';
    $html = '<div class="'.tailwind_class('tw-alert '.tailwind_tone_class('alert', $tone), $options['class'] ?? '').'" role="'.$role.'">';
    $html .= '<div class="tw-alert-content">'.($input['title'] ?? '').'</div>';

    if (!empty($options['dismiss'])) {
        $html .= '<button type="button" class="tw-alert-dismiss" data-tailwind-notice-close aria-label="'.
            tailwind_escape($options['close_label'] ?? 'Close').'">&times;</button>';
    }

    return $html.'</div>';
}

function tailwind_render_badge(array $input): string
{
    $options = (array)($input['options'] ?? []);
    $tone = tailwind_semantic_tone($options['class'] ?? '', 'secondary');
    $kind = ($input['kind'] ?? 'badge') === 'label' ? ' tw-badge-label' : '';

    return '<span class="'.tailwind_class('tw-badge '.tailwind_tone_class('badge', $tone).$kind, $options['class'] ?? '').'">'.
        ($options['icon'] ?? '').($input['label'] ?? '').'</span>';
}

function tailwind_render_progress(array $input): string
{
    $values = $input['value'] ?? 0;
    $title = $input['title'] ?? NULL;
    $options = (array)($input['options'] ?? []);
    $maximum = max(0.00001, (float)($options['max_unit'] ?? 100));
    $height = (string)($options['height'] ?? '20px');
    $width = (string)($options['width'] ?? '100%');
    $height = preg_match('/^\d+(?:\.\d+)?(?:px|rem|em|%)$/', $height) ? $height : '20px';
    $width = preg_match('/^\d+(?:\.\d+)?(?:px|rem|em|%|vw)$/', $width) ? $width : '100%';

    $display_value = static function(float $value) use ($options, $maximum): string {
        if (!empty($options['disabled'])) return '&#x221e;';
        if (!empty($options['as_unit'])) return tailwind_escape($value.' / '.$maximum);
        if (!empty($options['as_star'])) {
            $rating = round((($value / $maximum) * 5) * 2) / 2;
            return tailwind_escape(rtrim(rtrim(number_format($rating, 1, '.', ''), '0'), '.').' / 5');
        }

        return tailwind_escape($value.(!empty($options['as_percent']) ? '%' : ''));
    };
    $tone_for = static function(float $percent, int $index = 0) use ($options): string {
        if (!empty($options['bar_class'])) {
            $classes = (array)$options['bar_class'];
            return tailwind_semantic_tone($classes[$index] ?? reset($classes), 'primary');
        }
        if (!empty($options['reverse'])) {
            return $percent > 80 ? 'destructive' : ($percent > 60 ? 'warning' : ($percent > 40 ? 'info' : 'success'));
        }

        return $percent > 80 ? 'success' : ($percent > 60 ? 'info' : ($percent > 40 ? 'warning' : 'destructive'));
    };

    $root_class = tailwind_class('tw-progress-root'.(!empty($options['inline']) ? ' tw-progress-inline' : ''), $options['class'] ?? '');
    $html = '<div class="'.$root_class.'" style="--tw-progress-width:'.tailwind_escape($width).'">';

    if (is_array($values)) {
        $total = !empty($options['stacked']) ? (float)array_sum($values) : $maximum;
        $segments = '';
        $legend = '';
        $index = 0;
        foreach ($values as $key => $raw_value) {
            $value = (float)$raw_value;
            $percent = $total > 0 ? max(0, min(100, ($value / $total) * 100)) : 0;
            $segment_title = !empty($options['stacked'])
                ? ucfirst((string)$key)
                : (is_array($title) ? (string)($title[$index] ?? '') : ucfirst((string)$key));
            $tone = $tone_for($percent, $index);
            $tooltip = !empty($options['bar_tooltip'])
                ? ' title="'.tailwind_escape($segment_title.': '.$display_value($value)).'"'
                : '';
            $tone_class = tailwind_tone_class('progress', $tone);
            $segments .= '<span class="tw-progress-indicator '.$tone_class.'" style="width:'.
                tailwind_escape($percent).'%"'.$tooltip.'></span>';
            $legend .= '<span class="tw-progress-legend-item"><span class="tw-progress-legend-swatch '.$tone_class.
                '"></span><span>'.$segment_title.' <span class="tw-progress-legend-value">('.
                tailwind_escape($value).')</span></span></span>';
            $index++;
        }
        if (empty($options['hide_info'])) {
            $main_title = is_array($title) ? 'Progress' : (string)$title;
            $html .= '<div class="tw-progress-meta"><span class="'.tailwind_class('tw-progress-label', $options['label_class'] ?? '').'">'.
                $main_title.'</span><span class="tw-progress-legend">'.$legend.'</span></div>';
        }
        $html .= '<div class="'.tailwind_class('tw-progress-track', $options['progress_class'] ?? '').'" style="height:'.
            tailwind_escape($height).'" role="group" aria-label="'.tailwind_escape(is_array($title) ? 'Progress' : ($title ?: 'Progress')).'">'.
            $segments.'</div>';
    } else {
        $value = (float)$values;
        $percent = max(0, min(100, ($value / $maximum) * 100));
        $visible_value = $display_value($value);
        $tone = $tone_for($percent);
        if (empty($options['hide_info']) && $title !== NULL && $title !== '') {
            $html .= '<div class="tw-progress-meta"><span class="'.tailwind_class('tw-progress-label', $options['label_class'] ?? '').'"'.
                (!empty($options['title_tooltip']) ? ' title="'.tailwind_escape($options['title_tooltip']).'"' : '').'>'.
                $title.'</span><span class="tw-progress-value">'.$visible_value.'</span></div>';
        }
        $html .= '<div class="'.tailwind_class('tw-progress-track', $options['progress_class'] ?? '').'" style="height:'.
            tailwind_escape($height).'" role="progressbar" aria-valuenow="'.tailwind_escape($percent).
            '" aria-valuemin="0" aria-valuemax="100" aria-label="'.tailwind_escape($title ?: 'Progress').'">';
        $html .= '<span class="'.tailwind_class('tw-progress-indicator '.tailwind_tone_class('progress', $tone), $options['bar_class'] ?? '').
            '" style="width:'.tailwind_escape($percent).'%"'.
            (!empty($options['bar_tooltip']) ? ' title="'.$visible_value.'"' : '').'></span></div>';
        if (!empty($options['as_star']) && empty($options['hide_info']) && ($title === NULL || $title === '')) {
            $html .= '<div class="tw-progress-rating">'.$visible_value.'</div>';
        }
    }

    return $html.'</div>';
}

function tailwind_render_collapse(array $options): string
{
    $callback = (string)($options['callback'] ?? '');
    $id = tailwind_escape($options['id'] ?? 'tailwind');

    if ($callback === 'opencollapse') {
        return '<div id="'.$id.'-accordion" class="'.tailwind_class('tw-accordion', $options['class'] ?? '').'" data-fusion-accordion data-tailwind-accordion>';
    }
    if ($callback === 'closecollapse') return '</div>';
    if ($callback === 'closecollapsebody') return '</div></div>';
    if ($callback !== 'opencollapsebody') return '';

    $active = !empty($options['active']);
    $panel_id = $id.'-collapse';
    $group_id = tailwind_escape($options['group_id'] ?? '');
    $heading = max(2, min(6, (int)($options['heading_size'] ?? 3)));
    $custom_header = (string)($options['custom_header'] ?? '');
    $type = (string)($options['type'] ?? '');
    $expanded = $active ? 'true' : 'false';
    $html = '<div class="'.tailwind_class('tw-accordion-item', $options['class'] ?? '').'">';

    if ($custom_header !== '') {
        $html .= $custom_header;
    } elseif ($type === 'admin_header') {
        $html .= '<div class="tw-accordion-admin-header"><div class="tw-accordion-title">'.$options['title'].'</div>';
        $html .= '<button type="button" class="tw-accordion-admin-trigger" data-fusion-collapse-trigger data-tailwind-collapse-trigger '.
            'aria-controls="'.$panel_id.'" aria-expanded="'.$expanded.'" '.
            'data-label-expand="Expand" data-label-close="Close">'.
            '<span data-fusion-collapse-label data-tailwind-collapse-label>'.($active ? 'Close' : 'Expand').'</span></button></div>';
    } else {
        $html .= '<h'.$heading.' class="tw-accordion-heading">';
        $html .= '<button type="button" class="tw-accordion-trigger" data-fusion-collapse-trigger data-tailwind-collapse-trigger '.
            'aria-controls="'.$panel_id.'" aria-expanded="'.$expanded.'">';
        $html .= '<span>'.$options['title'].'</span>';
        $html .= '<svg class="tw-accordion-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" '.
            'stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">'.
            '<path d="m9 18 6-6-6-6"></path></svg></button></h'.$heading.'>';
    }

    $html .= '<div id="'.$panel_id.'" class="tw-accordion-content" data-fusion-collapse-panel data-tailwind-collapse-panel'.
        ($group_id !== '' ? ' data-fusion-collapse-group="'.$group_id.'" data-tailwind-collapse-group="'.$group_id.'"' : '').
        ($active ? '' : ' hidden').'>';

    return $html;
}

function tailwind_render_tabs(array $options): string
{
    static $groups = [];

    $config = (array)($options['tabs'] ?? []);
    $part = (string)($options['part'] ?? 'header');
    $id = tailwind_escape($options['id'] ?? 'tailwind-tabs');
    $html = '';

    if ($part === 'header') {
        $titles = (array)($config['title'] ?? []);
        $tab_ids = (array)($config['id'] ?? []);
        $active_id = (string)($options['active_id'] ?? '');
        if ($active_id === '' && isset($options['active']) && isset($tab_ids[(int)$options['active']])) {
            $active_id = (string)$tab_ids[(int)$options['active']];
        }
        if ($active_id === '' && $tab_ids !== []) {
            $active_id = (string)reset($tab_ids);
        }

        $link = $options['link'] ?? FALSE;
        $link_mode = !empty($link);
        $has_wrapper = !array_key_exists('has_wrapper', $options) || !empty($options['has_wrapper']);
        $remember = !empty($options['remember']) && !$link_mode;
        $groups[$id] = [
            'active_id' => $active_id,
            'has_wrapper' => $has_wrapper,
            'link_mode' => $link_mode,
        ];

        $root_class = tailwind_class('tw-tabs-root', $options['wrapper_class'] ?? '');
        $header_class = tailwind_class('tw-tabs-header', $options['wrapper_header_class'] ?? '');
        $body_class = tailwind_class('tw-tabs-body', $options['wrapper_body_class'] ?? '');
        $html .= '<div class="'.$root_class.'" data-fusion-tabs-root="'.$id.'" data-tailwind-tabs-root="'.$id.'">';
        $html .= '<div class="'.$header_class.'"><div class="tw-tabs-list-viewport">';
        $html .= '<div id="'.$id.'" class="'.tailwind_class('tw-tabs-list', $options['class'] ?? '').'" '.
            'role="tablist" aria-label="'.tailwind_escape($options['aria_label'] ?? 'Sections').'"'.
            ($remember ? ' data-fusion-tabs-remember="tab_js-'.$id.'" data-tailwind-tabs-remember="tab_js-'.$id.'"' : '').'>';

        $tab_groups = (array)($config['group'] ?? []);
        $group_titles = (array)($config['group_title'] ?? []);
        $rendered_groups = [];

        foreach ((array)($config['title'] ?? []) as $key => $title) {
            $group_key = trim((string)($tab_groups[$key] ?? ''));
            if ($group_key !== '') {
                if (isset($rendered_groups[$group_key])) {
                    continue;
                }

                $rendered_groups[$group_key] = TRUE;
                $child_keys = [];
                foreach ($titles as $child_key => $_child_title) {
                    if (trim((string)($tab_groups[$child_key] ?? '')) === $group_key) {
                        $child_keys[] = $child_key;
                    }
                }
                if ($child_keys === []) {
                    continue;
                }

                $selected_key = NULL;
                foreach ($child_keys as $child_key) {
                    if ((string)($tab_ids[$child_key] ?? '') === $active_id) {
                        $selected_key = $child_key;
                        break;
                    }
                }
                $control_key = $selected_key ?? $child_keys[0];
                $control_tab_id = tailwind_escape($tab_ids[$control_key] ?? 'tab-'.$control_key);
                $group_active = $selected_key !== NULL;
                $group_slug = trim((string)preg_replace('/[^a-z0-9_-]+/i', '-', strtolower($group_key)), '-');
                if ($group_slug === '') {
                    $group_slug = 'group-'.$key;
                }
                $proxy_id = 'tab-'.$id.'-group-'.$group_slug;
                $menu_id = $id.'-group-'.$group_slug.'-menu';
                $group_title = $group_titles[$group_key] ?? $group_key;
                $trigger_title = $group_active ? ($titles[$control_key] ?? $group_title) : $group_title;

                $html .= '<div class="tw-relative" role="presentation">';
                $html .= '<a id="'.$proxy_id.'" class="dropdown-toggle tw-tabs-trigger" href="#'.$control_tab_id.'" role="tab" '.
                    'data-state="'.($group_active ? 'active' : 'inactive').'" aria-selected="'.($group_active ? 'true' : 'false').'" '.
                    'tabindex="'.($group_active ? '0' : '-1').'" data-fusion-tab-child="tab-'.$control_tab_id.'" '.
                    'data-tailwind-menu-trigger data-tailwind-menu-target="'.$menu_id.'" '.
                    'aria-haspopup="menu" aria-controls="'.$control_tab_id.'" aria-expanded="false">'.
                    '<span data-fusion-tab-group-label>'.$trigger_title.'</span></a>';
                $html .= '<template data-fusion-tab-group-default>'.$group_title.'</template>';
                $html .= '<div id="'.$menu_id.'" class="tw-dropdown-menu tw-min-w-56 tw-p-1" data-tailwind-menu role="menu" hidden>';

                foreach ($child_keys as $child_key) {
                    $child_id = tailwind_escape($tab_ids[$child_key] ?? 'tab-'.$child_key);
                    $child_selected = $child_id === $active_id;
                    $child_class = tailwind_class(
                        'dropdown-item tw-dropdown-item tw-w-full tw-text-left',
                        $config['class'][$child_key] ?? ''
                    );
                    $child_title = $titles[$child_key];

                    if ($link_mode) {
                        $getname = (string)($options['getname'] ?? 'section');
                        $cleanup_get = (array)($options['cleanup_get'] ?? []);
                        $get_array = array_values(array_unique(array_merge([$getname], $cleanup_get)));
                        $link_url = (string)$link;
                        if ($link === TRUE && function_exists('clean_request')) {
                            $keep_filtered = in_array('*', $cleanup_get, TRUE);
                            if ($keep_filtered) $get_array = [];
                            $link_url = clean_request(
                                $getname.'='.$child_id.(function_exists('check_get') && check_get('aid') ? '&aid='.get('aid') : ''),
                                $get_array,
                                $keep_filtered
                            );
                        } elseif ($link_url !== '') {
                            $link_url .= (str_contains($link_url, '?') ? '&' : '?').$getname.'='.$child_id;
                        }
                        $html .= '<a id="tab-'.$child_id.'" class="'.$child_class.'" href="'.tailwind_escape($link_url).'" role="menuitem" '.
                            ($child_selected ? 'aria-current="page" data-state="active"' : 'data-state="inactive"').'>' .
                            '<span data-fusion-tab-title>'.$child_title.'</span></a>';
                    } else {
                        $html .= '<a id="tab-'.$child_id.'" class="'.$child_class.'" href="#'.$child_id.'" role="menuitem" '.
                            'data-fusion-tab data-tailwind-tab data-fusion-tab-proxy="'.$proxy_id.'" '.
                            'data-state="'.($child_selected ? 'active' : 'inactive').'" aria-controls="'.$child_id.'">'.
                            '<span data-fusion-tab-title>'.$child_title.'</span></a>';
                    }
                }

                $html .= '</div></div>';
                continue;
            }

            $tab_id = tailwind_escape($tab_ids[$key] ?? 'tab-'.$key);
            $selected = $tab_id === $active_id;
            $item_class = tailwind_class('tw-tabs-trigger', $config['class'][$key] ?? '');
            $icon_value = (string)($config['icon'][$key] ?? '');
            $icon = '';
            if ($icon_value !== '') {
                $rendered_icon = str_contains($icon_value, '<')
                    ? $icon_value
                    : (function_exists('get_svg') ? (string)get_svg($icon_value) : '');
                if (str_contains($rendered_icon, '<')) {
                    $icon = '<span class="tw-tabs-trigger-icon" aria-hidden="true">'.$rendered_icon.'</span>';
                }
            }

            if ($link_mode) {
                $getname = (string)($options['getname'] ?? 'section');
                $cleanup_get = (array)($options['cleanup_get'] ?? []);
                $get_array = array_values(array_unique(array_merge([$getname], $cleanup_get)));
                $link_url = (string)$link;
                if ($link === TRUE && function_exists('clean_request')) {
                    $keep_filtered = in_array('*', $cleanup_get, TRUE);
                    if ($keep_filtered) $get_array = [];
                    $link_url = clean_request(
                        $getname.'='.$tab_id.(function_exists('check_get') && check_get('aid') ? '&aid='.get('aid') : ''),
                        $get_array,
                        $keep_filtered
                    );
                } elseif ($link_url !== '') {
                    $link_url .= (str_contains($link_url, '?') ? '&' : '?').$getname.'='.$tab_id;
                }
                $html .= '<a id="tab-'.$tab_id.'" class="'.$item_class.'" href="'.tailwind_escape($link_url).'" '.
                    ($selected ? 'aria-current="page" data-state="active"' : 'data-state="inactive"').'>'.
                    $icon.'<span>'.$title.'</span></a>';
            } else {
                $html .= '<button id="tab-'.$tab_id.'" type="button" class="'.$item_class.'" role="tab" '.
                    'data-fusion-tab data-tailwind-tab data-state="'.($selected ? 'active' : 'inactive').'" '.
                    'aria-controls="'.$tab_id.'" aria-selected="'.($selected ? 'true' : 'false').'" '.
                    'tabindex="'.($selected ? '0' : '-1').'">'.$icon.'<span>'.$title.'</span></button>';
            }
        }

        $html .= '</div></div>';
        if (!empty($options['header_action'])) {
            $html .= '<div class="tw-tabs-header-action">'.$options['header_action'].'</div>';
        }
        $html .= '</div>';

        if ($has_wrapper) {
            $html .= '<div class="'.$body_class.'"><div id="tab-content-'.$id.'" class="tw-tabs-content">';
        } else {
            $html .= '</div>';
        }
    } elseif ($part === 'openbody') {
        $group = tailwind_escape($options['group_id'] ?? 'tailwind-tabs');
        $active_id = (string)($options['active_id'] ?? ($groups[$group]['active_id'] ?? ''));
        $selected = array_key_exists('active', $options)
            ? !empty($options['active'])
            : $id === $active_id;
        $panel_class = tailwind_class('tw-tabs-panel', $options['class'] ?? '');
        $html .= '<div id="'.$id.'" class="'.$panel_class.'" role="tabpanel" data-fusion-tab-panel data-tailwind-tab-panel '.
            'data-fusion-tab-group="'.$group.'" data-tailwind-tab-group="'.$group.'" aria-labelledby="tab-'.$id.'" tabindex="0"'.
            ($selected ? '' : ' hidden').'>';
    } elseif ($part === 'openwrapper') {
        $group = tailwind_escape($options['group_id'] ?? 'tailwind-tabs');
        $html .= '<div class="'.tailwind_class('tw-tabs-body', $options['class'] ?? '').'">';
        $html .= '<div id="tab-content-'.$group.'" class="tw-tabs-content">';
    } elseif ($part === 'closebody' || $part === 'closewrapper') {
        $html .= '</div>';
        if ($part === 'closewrapper') $html .= '</div>';
    } elseif ($part === 'footer') {
        if (!empty($options['tab_nav'])) {
            $locale = (array)($options['locale'] ?? []);
            $html .= '<div class="tw-tabs-navigation">'.
                '<button type="button" class="tw-tabs-navigation-button" data-fusion-tab-previous data-tailwind-tab-previous>'.
                tailwind_escape($locale['previous'] ?? 'Previous').'</button>'.
                '<button type="button" class="tw-tabs-navigation-button" data-fusion-tab-next data-tailwind-tab-next>'.
                tailwind_escape($locale['next'] ?? 'Next').'</button></div>';
        }
        $has_wrapper = !array_key_exists('has_wrapper', $options) || !empty($options['has_wrapper']);
        if ($has_wrapper) $html .= '</div></div>';
        $html .= '</div>';
    }

    return $html;
}

function tailwind_render_modal(array $input): string
{
    static $footer_rendered = FALSE;

    $part = (string)($input['modal'] ?? '');
    $settings = array_replace($input, (array)($input['options'] ?? []));
    $id = tailwind_escape($input['id'] ?? $settings['id'] ?? 'tailwind');
    $dismiss = empty($settings['static']);
    $close = tailwind_escape($settings['close_label'] ?? 'Close');

    if ($part === 'open') {
        $footer_rendered = FALSE;
        $trigger = (string)($settings['trigger'] ?? '');
        $hidden = !empty($settings['hidden']) || $trigger !== '';
        $html = '<div id="'.$id.'_Modal" class="tw-fixed tw-inset-0 tw-z-50 tw-overflow-y-auto tw-bg-black/60 tw-p-4" data-tailwind-modal'.($trigger !== '' ? ' data-tailwind-modal-trigger="'.tailwind_escape($trigger).'"' : '').' role="dialog" aria-modal="true" aria-labelledby="'.$id.'_ModalTitle"'.($hidden ? ' hidden' : '').'><div class="tw-flex tw-min-h-full tw-items-center tw-justify-center"><div class="'.tailwind_class('tw-w-full tw-max-w-2xl tw-overflow-hidden tw-rounded-2xl tw-border tw-border-ui-border tw-bg-ui-card tw-shadow-dialog', $settings['class'] ?? '').'">';
        if (!empty($settings['header_content']) || $dismiss) {
            $html .= '<header class="tw-flex tw-items-center tw-justify-between tw-gap-4 tw-border-b tw-border-ui-border tw-px-5 tw-py-4">';
            if (!empty($settings['header_content'])) $html .= '<h2 id="'.$id.'_ModalTitle" class="tw-text-lg tw-font-semibold tw-text-ui-foreground">'.$settings['header_content'].'</h2>';
            if ($dismiss) $html .= '<button type="button" class="tw-inline-flex tw-min-h-11 tw-min-w-11 tw-cursor-pointer tw-items-center tw-justify-center tw-rounded-lg tw-text-ui-muted-foreground hover:tw-bg-ui-accent hover:tw-text-ui-foreground focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-ui-ring" data-tailwind-modal-close aria-label="'.$close.'">×</button>';
            $html .= '</header>';
        }
        return $html.'<div class="'.tailwind_class('tw-p-5 tw-text-ui-card-foreground', $settings['body_class'] ?? '').'">';
    }

    if ($part === 'footer') {
        $footer_rendered = TRUE;
        $html = '</div><footer class="tw-flex tw-flex-wrap tw-items-center tw-justify-end tw-gap-2 tw-border-t tw-border-ui-border tw-px-5 tw-py-4">';
        if ($dismiss) $html .= '<button type="button" class="tw-inline-flex tw-min-h-11 tw-cursor-pointer tw-items-center tw-rounded-lg tw-border tw-border-ui-border tw-bg-ui-card tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-ui-foreground hover:tw-bg-ui-accent focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-ui-ring" data-tailwind-modal-close>'.$close.'</button>';
        return $html.($settings['footer_content'] ?? '').'</footer>';
    }

    if ($part === 'close') {
        $html = $footer_rendered ? '</div></div></div>' : '</div></div></div></div>';
        $footer_rendered = FALSE;

        return $html;
    }

    return '';
}

function tailwind_render_notices(array $input): string
{
    $notices = (array)($input['notices'] ?? $input);
    $options = (array)($input['options'] ?? []);
    $styles = [
        'success' => 'tw-alert-success',
        'warning' => 'tw-alert-warning',
        'danger' => 'tw-alert-destructive',
        'info' => 'tw-alert-info',
    ];
    $html = '';

    foreach ($notices as $status => $notice) {
        $style = $styles[$status] ?? 'tw-alert-secondary';
        $html .= '<div class="tw-alert tw-mb-3 '.$style.'" role="'.($status === 'danger' ? 'alert' : 'status').'"><ul class="tw-m-0 tw-min-w-0 tw-flex-1 tw-list-none tw-space-y-1 tw-p-0">';
        foreach ((array)$notice as $message) $html .= '<li>'.$message.'</li>';
        $html .= '</ul><button type="button" class="tw-inline-flex tw-min-h-11 tw-min-w-11 tw-cursor-pointer tw-items-center tw-justify-center tw-rounded-lg hover:tw-bg-black/5 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-current" data-tailwind-notice-close aria-label="Close">×</button></div>';
    }

    if ($html !== '' && (!array_key_exists('container', $options) || !empty($options['container']))) {
        $html = '<div class="tw-notices-container">'.$html.'</div>';
    }

    return $html;
}

function tailwind_render_admin_component(array $info): string
{
    $component = (string)($info['_framework_component'] ?? '');
    $additional = $info['class'] ?? '';

    if ($component === 'openside') {
        $html = '<aside class="'.tailwind_class('tw-overflow-hidden tw-rounded-xl tw-border tw-border-ui-border tw-bg-ui-card tw-text-ui-card-foreground', $additional).'">';
        if (!empty($info['value']) || !empty($info['collapse'])) {
            $html .= '<header class="tw-flex tw-min-h-12 tw-items-center tw-justify-between tw-gap-3 tw-border-b tw-border-ui-border tw-px-4 tw-py-3"><h2 class="tw-text-base tw-font-semibold tw-text-ui-foreground">'.$info['value'].'</h2>';
            if (!empty($info['collapse'])) $html .= is_string($info['collapse']) ? $info['collapse'] : '';
            $html .= '</header>';
        }
        return $html.'<div class="tw-p-4">';
    }
    if ($component === 'closeside') return '</div></aside>';
    if ($component === 'opengrid') {
        $count = max(1, (int)($info['value'] ?? 1));
        return '<div class="'.tailwind_class('tw-framework-grid tw-grid tw-gap-4', $additional).'" style="--tw-framework-columns:'.$count.'">';
    }
    if ($component === 'closegrid') return '</div>';

    return '';
}
