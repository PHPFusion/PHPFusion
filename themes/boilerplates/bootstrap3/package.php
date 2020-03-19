<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: package.php
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
function bootstrap3() {
    if (fusion_get_settings('bootstrap') || defined('BOOTSTRAP')) {
        $_themes = THEMES;
        if (!empty('CDN')) {
            $_themes = CDN.'themes/';
        }

        echo "<meta http-equiv='X-UA-Compatible' content='IE=edge'/>\n";
        echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'/>\n";
        echo "<link rel='stylesheet' href='".$_themes."boilerplates/bootstrap3/css/bootstrap.min.css'/>\n";
        echo "<link rel='stylesheet' href='".$_themes."boilerplates/bootstrap3/css/bootstrap-submenu.min.css'/>\n";

        if (fusion_get_locale('text-direction') == 'rtl') {
            echo "<link rel='stylesheet' href='".$_themes."boilerplates/bootstrap3/css/bootstrap-rtl.min.css'/>\n";
        }

        add_to_footer("<script src='".$_themes."boilerplates/bootstrap3/js/bootstrap.min.js'></script>");
        add_to_footer("<script src='".$_themes."boilerplates/bootstrap3/js/bootstrap-submenu.min.js'></script>");

        add_to_jquery("
            $('[data-submenu]').submenupicker();
            // Fix select2 on modal - http://stackoverflow.com/questions/13649459/twitter-bootstrap-multiple-modal-error/15856139#15856139
            $.fn.modal.Constructor.prototype.enforceFocus = function () {};
        ");
    }
}

function change_to_bs3_twig() {
    return [
        'nav'      => [
            'nav_path'        => BOILERPLATES.'bootstrap3/html/navbar/navbar.html',
            'nav_li_no_link'  => BOILERPLATES.'bootstrap3/html/navbar/nav_li_no_link.html',
            'nav_li_link'     => BOILERPLATES.'bootstrap3/html/navbar/nav_li_link.html',
            'nav_li_dropdown' => BOILERPLATES.'bootstrap3/html/navbar/nav_li_dropdown.html',
            'nav_divider'     => BOILERPLATES.'bootstrap3/html/navbar/nav_li_divider.html',
        ],
        'modal'    => BOILERPLATES.'bootstrap3/html/modal.html',
        'progress' => BOILERPLATES.'bootstrap3/html/progress.html'
    ];
}

// Add the script to head tag
fusion_add_hook('fusion_boiler_header', 'bootstrap3');

// Set the boilerpalte template files to system
fusion_add_hook('fusion_boiler_paths', 'change_to_bs3_twig');
