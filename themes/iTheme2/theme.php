<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: iTheme2/theme.php
| Author: Khalid545
| Author: RobiNN
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
defined('IN_FUSION') || exit;

require_once INCLUDES.'theme_functions_include.php';

define('THEME_BULLET', '&middot;');
define('BOOTSTRAP', TRUE);
define('FONTAWESOME', TRUE);

function render_page() {
    $settings = fusion_get_settings();

    echo '<div class="container">';
        echo '<div id="header">';
            echo '<div class="clearfix">';
                echo '<div class="logo pull-left"><a href="'.BASEDIR.$settings['opening_page'].'"><img src="'.BASEDIR.$settings['sitebanner'].'" alt="'.$settings['sitename'].'" class="img-responsive"/></a></div>';
            echo '</div>';

            echo showsublinks('', 'navbar-default', [
                'id'                => 'main-nav',
                'show_header'       => TRUE,
                'searchbar'         => TRUE,
                'language_switcher' => TRUE
            ]);
        echo '</div>';

        echo '<div id="main-box">';
            echo renderNotices(getNotices(['all', FUSION_SELF]));

            echo defined('AU_CENTER') && AU_CENTER ? AU_CENTER : '';
            echo showbanners(1);

            echo '<div class="row">';
                $content = ['sm' => 12, 'md' => 12, 'lg' => 12];
                $right = ['sm' => 3, 'md' => 3, 'lg' => 3];

                if (defined('RIGHT') && RIGHT) {
                    $content['sm'] = $content['sm'] - $right['sm'];
                    $content['md'] = $content['md'] - $right['md'];
                    $content['lg'] = $content['lg'] - $right['lg'];
                }

                $half_column = (defined('LEFT') && LEFT) || (defined('RIGHT') && RIGHT) ? '' : '-5';
                echo '<div class="col-xs-12 col-sm-'.$content['sm'].$half_column.' col-md-'.$content['md'].$half_column.' col-lg-'.$content['lg'].$half_column.'">';
                    echo '<div id="content">';
                        echo defined('U_CENTER') && U_CENTER ? U_CENTER : '';

                        echo CONTENT;

                        echo defined('L_CENTER') && L_CENTER ? L_CENTER : '';

                        echo showbanners(2);
                    echo '</div>';
                echo '</div>';

                if (defined('RIGHT') && RIGHT || defined('LEFT') && LEFT) {
                    echo '<div id="right-side" class="col-xs-12 col-sm-'.$right['sm'].' col-md-'.$right['md'].' col-lg-'.$right['lg'].'">';
                        echo defined('RIGHT') && RIGHT ? RIGHT : '';
                        echo defined('LEFT') && LEFT ? LEFT : '';
                    echo '</div>';
                }
            echo '</div>';

            echo defined('BL_CENTER') && BL_CENTER ? BL_CENTER : '';

            echo '<div class="row m-t-10">';
                echo defined('USER1') && USER1 ? '<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">'.USER1.'</div>' : '';
                echo defined('USER2') && USER2 ? '<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">'.USER2.'</div>' : '';
                echo defined('USER3') && USER3 ? '<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">'.USER3.'</div>' : '';
                echo defined('USER4') && USER4 ? '<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">'.USER4.'</div>' : '';
            echo '</div>';

            echo showFooterErrors();
        echo '</div>'; // #main-box

        echo '<footer id="main-footer" class="text-left">';
            echo '<div class="row copyright m-b-20">';
                echo '<div class="col-xs-12 col-sm-6">';
                    echo showcopyright();
                    echo '<br/>Theme by <a href="https://themify.me">Themify.me</a>. Converted to PHP-Fusion by: Khalid';
                    echo '<br/>Ported for v9 by <a href="https://github.com/RobiNN1" target="_blank">RobiNN</a>';
                echo '</div>';
                echo '<div class="col-xs-12 col-sm-6">';
                    echo nl2br(parse_textarea($settings['footer'], FALSE, TRUE)).'<br/>';
                    echo showprivacypolicy();
                    echo '<br/>';
                    echo showcounter();
                echo '</div>';
            echo '</div>';
        echo '</footer>';
    echo '</div>'; // .container

    if ($settings['rendertime_enabled'] == 1 || $settings['rendertime_enabled'] == 2) {
        echo '<div id="debug">';
            echo showrendertime();
            echo showMemoryUsage();
        echo '</div>';
    }
}

function opentable($title) {
    echo '<div class="post">';
    echo !empty($title) ? '<h2 class="ttitle">'.$title.'</h2>' : '';
}

function closetable() {
    echo '</div>';
}

function openside($title, $collapse = FALSE, $state = 'on') {
    global $panel_collapse;
    $boxname = '';
    $panel_collapse = $collapse;

    echo '<div class="widgetwrap widget">';
    echo !empty($title) ? '<h4 class="widgettitle">'.$title.'</h4>' : '';

    if ($collapse == TRUE) {
        $boxname = str_replace(' ', '', $title);
        echo '<div class="panel-button">'.panelbutton($state, $boxname).'</div>';
    }

    if ($collapse == TRUE) {
        echo panelstate($state, $boxname);
    }
}

function closeside() {
    global $panel_collapse;

    if ($panel_collapse == TRUE) {
        echo '</div>';
    }

    echo '</div>';
}
