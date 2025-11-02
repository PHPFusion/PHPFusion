<?php
/**
 * @param $link_id
 * @param $data
 * @param false $secondary_menu
 * @return string
 */
function render_nav_items($link_id, $data, $secondary_menu = false)
{
    $html = '';

    if (!empty($data[$link_id])) {
        foreach ($data[$link_id] as $rows) {

            if (!empty($rows['separator'])) {
                $html .= "<li class='dropdown-divider' role='separator'></li>";
                continue;
            }

            $li_class = isset($rows['li_class']) ? ' ' . $rows['li_class'] : '';
            $html .= "<li class='nav-item{$li_class}'>";
            if (!empty($rows['link_content'])) {
                $html .= $rows['link_content'];
            }

            if (!empty($rows['link_url'])) {
                $html .= '<a ' . ($rows['link_attr'] ?? '') . '>';
            }

            $html .= ($rows['link_icon'] ?? '') . ($rows['link_name'] ?? '') . ($rows['link_caret'] ?? '');

            if (!empty($rows['link_url'])) {
                $html .= '</a>';
            }

            // Handle submenu
            if (!empty($rows['link_child'])) {
                $secondaryMenuClass = " dropdown-menu-end dropdown-animation dropdown-menu-size-md shadow-lg border-0";
                $menu_class = "dropdown-menu" . ($secondary_menu ? $secondaryMenuClass : "");
                $html .= '<ul id="menu-' . $rows['link_id'] . '" aria-labelledby="ddlink' . $rows['link_id'] . '" class="' . $menu_class . '" data-bs-popper="static">';

                if (!empty($rows['link_url']) && $rows['link_url'] != '#') {
                    $liClass = !empty($rows['link_url']) ? 'nav-item' : 'no-link nav-item';
                    $html .= '<li class="' . $liClass . '" role="presentation">';
                    if (!empty($rows['link_content'])) {
                        $html .= $rows['link_content'];
                    } elseif (!empty($rows['link_child_attr'])) {
                        $html .= '<a ' . $rows['link_child_attr'] . ' role="menuitem">';
                        $html .= ($rows['link_icon'] ?? '') . ($rows['link_name'] ?? '');
                        $html .= '</a>';
                    }
                    $html .= '</li>';
                }

                $html .= render_nav_items($rows['link_id'], $data);
                $html .= '</ul>';
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
    $navbarClass = 'navbar navbar-expand-lg';
    if (!empty($info['navbar_class'])) {
        $navbarClass .= ' ' . $info['navbar_class'];
    }

    // Container
    $openContainer = $closeContainer = '';
    if (!empty($info['container'])) {
        $openContainer = '<div class="container">';
        $closeContainer = '</div>';
    } elseif (!empty($info['container_fluid'])) {
        $openContainer = '<div class="container-fluid">';
        $closeContainer = '</div>';
    }

    // Header and Banner
    $headerContent = '';
    $headerClass = 'navbar-brand d-lg-none';
    if (!empty($info['show_banner'])) {
        $headerContent = '<a class="' . $headerClass . '" href="' . ($info['navbar_link'] ?? '#') . '">' . ($info['banner'] ?? '') . '</a>';
        if (!empty($info['show_header'])) {
            $headerContent = is_string($info['show_header'])
                ? $info['show_header']
                : '<a class="' . $headerClass . '" href="' . ($info['navbar_link'] ?? '#') . '">' . ($info['sitename'] ?? '') . '</a>';
        }
    }

    // Responsive collapse
    $responsiveButton = $openNav = $closeNav = '';
    if (!empty($info['responsive'])) {
        $responsiveButton = '
        <button type="button" class="navbar-toggler ms-auto btn btn-light p-0" 
            data-bs-toggle="collapse" 
            data-bs-target="#' . $info['id'] . '_menu" 
            aria-expanded="false" 
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
    $html = '<nav id="' . $info['id'] . '" class="' . $navbarClass . '" role="navigation">';
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
    $html .= '</nav>';

    return $html;
}
