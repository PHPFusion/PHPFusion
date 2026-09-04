<?php
/**
 * @param $link_id
 * @param $data
 * @param false $secondary_menu
 * @return string
 */
function bootstrap_nav_item_content(array $row, bool $show_description = false, bool $show_caret = true): string
{
    $html = '';
    if (!empty($row['link_icon'])) {
        $html .= '<span class="nav-link-icon d-md-none d-lg-inline-block">' . $row['link_icon'] . '</span>';
    }
    $html .= '<span class="nav-link-title">' . ($row['link_name'] ?? '') . '</span>';
    if ($show_description && !empty($row['link_description'])) {
        $html .= '<span class="nav-link-desc text-break">' . htmlspecialchars((string)$row['link_description'], ENT_QUOTES, 'UTF-8') . '</span>';
    }
    if ($show_caret) {
        $html .= $row['link_caret'] ?? '';
    }

    return $html;
}

function bootstrap_nav_has_destination(array $row): bool
{
    if (array_key_exists('link_has_destination', $row)) {
        return !empty($row['link_has_destination']);
    }

    $url = trim((string)($row['link_url'] ?? ''));
    return $url !== '' && $url !== '#';
}

function bootstrap_render_mega_menu(array $parent, array $data): string
{
    $children = array_values(array_filter(
        (array)($data[(int)$parent['link_id']] ?? []),
        static fn($row): bool => is_array($row) && empty($row['separator'])
    ));
    $column_count = min(3, max(1, count($children)));
    $menu_id = 'menu-' . (int)$parent['link_id'];
    $html = '<ul id="' . $menu_id . '" aria-labelledby="ddlink' . (int)$parent['link_id'] .
        '" class="dropdown-menu dropdown-menu-mega mega-columns-' . $column_count .
        '" data-fusion-mega-menu>';

    foreach ($children as $child) {
        $html .= '<li class="dropdown-menu-column" role="none">';
        $html .= '<div class="dropdown-header">' . bootstrap_nav_item_content($child, false, false) . '</div>';

        if (bootstrap_nav_has_destination($child)) {
            $child_class = trim((string)($child['link_child_class'] ?? 'dropdown-item'));
            $html .= '<a class="' . htmlspecialchars($child_class . ' dropdown-menu-parent-link', ENT_QUOTES, 'UTF-8') .
                '" ' . ($child['link_child_attr'] ?? '') . '>' .
                bootstrap_nav_item_content($child, true, false) . '</a>';
        }

        if (!empty($child['link_child'])) {
            $html .= '<ul class="dropdown-menu-list">';
            $html .= render_nav_items((int)$child['link_id'], $data, false, 1);
            $html .= '</ul>';
        }
        $html .= '</li>';
    }

    return $html . '</ul>';
}

function render_nav_items($link_id, $data, $secondary_menu = false, int $depth = 0)
{
    $html = '';

    if (!empty($data[$link_id])) {
        foreach ($data[$link_id] as $rows) {

            if (!empty($rows['separator'])) {
                $html .= "<li class='dropdown-divider' role='separator'></li>";
                continue;
            }

            $has_children = !empty($rows['link_child']);
            $li_classes = ['nav-item'];
            if (!empty($rows['li_class'])) {
                $li_classes[] = $rows['li_class'];
            }
            if ($has_children && $depth > 0) {
                $li_classes[] = 'dropend';
            }
            $html .= '<li class="' . htmlspecialchars(implode(' ', $li_classes), ENT_QUOTES, 'UTF-8') . '">';
            if (!empty($rows['link_content'])) {
                $html .= $rows['link_content'];
            }

            if (!empty($rows['link_url'])) {
                $linkClass = trim((string)($rows['link_class'] ?? ''));
                $linkAttributes = (string)($rows['link_attr'] ?? '');
                if ($has_children && $depth > 0) {
                    $linkAttributes = (string)preg_replace(
                        '/\s*data-bs-toggle="dropdown"/',
                        ' data-fusion-dropdown-trigger',
                        $linkAttributes
                    );
                }
                $html .= '<a' . ($linkClass !== '' ? ' class="' . htmlspecialchars($linkClass, ENT_QUOTES, 'UTF-8') . '"' : '') .
                    ' ' . trim($linkAttributes) . '>';
            }

            $html .= bootstrap_nav_item_content($rows, false, true);

            if (!empty($rows['link_url'])) {
                $html .= '</a>';
            }

            // Handle submenu
            if ($has_children) {
                $drop_style = strtolower(trim((string)($rows['link_drop_style'] ?? 'default')));
                $style_class = match ($drop_style) {
                    'arrow' => ' dropdown-menu-arrow',
                    'card' => ' dropdown-menu-card',
                    'dark' => ' dropdown-menu-dark',
                    default => '',
                };
                $is_mega = $drop_style === 'mega';
                if ($is_mega) {
                    $html .= bootstrap_render_mega_menu($rows, $data);
                } else {
                    $secondary_menu_class = $secondary_menu
                        ? ' dropdown-menu-end dropdown-animation dropdown-menu-size-md shadow-lg border-0'
                        : '';
                    $menu_class = 'dropdown-menu' . $style_class . $secondary_menu_class;
                    $html .= '<ul id="menu-' . (int)$rows['link_id'] . '" aria-labelledby="ddlink' .
                        (int)$rows['link_id'] . '" class="' . $menu_class . '">';

                    if (bootstrap_nav_has_destination($rows)) {
                        $child_class = trim((string)($rows['link_child_class'] ?? 'dropdown-item'));
                        $html .= '<li class="nav-item dropdown-menu-parent" role="none"><a class="' .
                            htmlspecialchars($child_class, ENT_QUOTES, 'UTF-8') . '" ' .
                            ($rows['link_child_attr'] ?? '') . '>' .
                            bootstrap_nav_item_content($rows, true, false) . '</a></li>';
                    }

                    $html .= render_nav_items((int)$rows['link_id'], $data, false, $depth + 1);
                    $html .= '</ul>';
                }
            }

            $html .= '</li>';
        }
    }

    return $html;
}

/**
 * @param $info
 * @return string
 */
function render_navbar($info)
{
    $navbarClass = 'navbar navbar-expand-md fusion-navbar';
    if (!empty($info['navbar_class'])) {
        $navbarClass .= ' ' . $info['navbar_class'];
    }

    // Container
    $openContainer = $closeContainer = '';
    if (!empty($info['container'])) {
        $openContainer = '<div class="container-xl">';
        $closeContainer = '</div>';
    } elseif (!empty($info['container_fluid'])) {
        $openContainer = '<div class="container-fluid">';
        $closeContainer = '</div>';
    }

    // Header and banner
    $headerContent = '';
    $headerClass = 'navbar-brand';
    if (!empty($info['show_header'])) {
        $headerBody = !empty($info['show_banner'])
            ? ($info['banner'] ?? '')
            : ($info['sitename'] ?? '');
        $headerContent = '<a class="' . $headerClass . '" href="' . ($info['navbar_link'] ?? '#') . '">' . $headerBody . '</a>';
    }

    // Responsive collapse
    $responsiveButton = $openNav = $closeNav = '';
    if (!empty($info['responsive'])) {
        $responsiveButton = '
        <button type="button" class="navbar-toggler ms-auto btn btn-light p-0" 
            data-bs-toggle="collapse" 
            data-bs-target="#' . $info['id'] . '_menu" 
            aria-expanded="false" 
            aria-label="Toggle navigation"
            aria-controls="' . $info['id'] . '_menu">
            <span class="navbar-toggler-icon"></span>
        </button>';
        $openNav = '<div class="collapse navbar-collapse" id="' . $info['id'] . '_menu">';
        $closeNav = '</div>';
    }

    // Nav classes
    $primaryNavClass = !empty($info['nav_class'])
        ? 'class="' . $info['nav_class'] . '"'
        : 'class="navbar-nav me-auto mb-2 mb-lg-0"';

    $secondaryNavClass = !empty($info['additional_nav_class'])
        ? 'class="' . $info['additional_nav_class'] . '"'
        : 'class="navbar-nav ms-auto"';

    // Render start
    $html = '<header id="' . $info['id'] . '" class="' . $navbarClass . '" role="navigation" aria-label="Primary navigation">';
    $html .= $openContainer;
    $html .= ($headerContent ?? '');
    $html .= ($info['custom_header'] ?? '');
    $html .= $responsiveButton;
    $html .= $openNav;
    $html .= ($info['html_pre_content'] ?? '');

    // Primary nav
    $html .= '<ul ' . $primaryNavClass . '>';
    $html .= render_nav_items(0, $info['primary_callback_nav']);
    $html .= '</ul>';

    $html .= ($info['html_content'] ?? '');

    // Secondary menu
    if (!empty($info['language_switcher']) || !empty($info['secondary_callback_nav'])) {
        $html .= '<ul ' . $secondaryNavClass . '>';
        $html .= render_nav_items(0, $info['secondary_callback_nav'], true);

        // Language switcher (optional)
        if (!empty($info['language_switcher']) && function_exists('fusion_get_language_switch')) {
            $langSwitch = fusion_get_language_switch();
            if (count($langSwitch) > 1) {
                $currentLang = defined('LANGUAGE') ? LANGUAGE : array_key_first($langSwitch);
                $selectedLang = $langSwitch[$currentLang];
                $html .= '<li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <img src="' . $selectedLang['language_icon_s'] . '" alt="' . $selectedLang['language_name'] . '">
                    </a>
                    <ul id="ddLanguage" class="dropdown-menu dropdown-menu-end">';
                foreach ($langSwitch as $lang) {
                    $html .= '<li><a class="dropdown-item" href="' . $lang['language_link'] . '">
                        <div class="d-flex align-items-center">
                            <img class="me-2" src="' . $lang['language_icon_s'] . '" alt="' . $lang['language_name'] . '">
                            ' . $lang['language_name'] . '
                        </div></a></li>';
                }
                $html .= '</ul></li>';
            }
        }

        $html .= '</ul>';
    }

    if (!empty($info['searchbar'])) {
        $html .= '<form class="d-flex ms-auto" role="search" action="' . ($info['search_uri'] ?? '') . '">'
            . ($info['search_input'] ?? '') . '</form>';
    }

    $html .= ($info['html_post_content'] ?? '');
    $html .= $closeNav;
    $html .= $closeContainer;
    $html .= '</header>';

    return $html;
}
