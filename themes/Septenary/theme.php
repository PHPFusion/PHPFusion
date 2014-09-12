<?php
    /*-------------------------------------------------------+
    | PHP-Fusion Content Management System
    | Copyright (C) 2002 - 2014 Nick Jones
    | http://www.php-fusion.co.uk/
    +--------------------------------------------------------+
    | Name: Septenary Theme
    | Filename: theme.php
    | Version: 1.00
    | Author: PHP-Fusion Mods UK
    | Developer & Designer: Craig
    | Site: http://www.phpfusionmods.co.uk
    +--------------------------------------------------------+
    | This program is released as free software under the
    | Affero GPL license. You can redistribute it and/or
    | modify it under the terms of this license which you
    | can read by viewing the included agpl.txt or online
    | at www.gnu.org/licenses/agpl.html. Removal of this
    | copyright header is strictly prohibited without
    | written permission from the original author(s).
    +--------------------------------------------------------*/
    if ( ! defined("IN_FUSION") ) {
        die( "Access Denied" );
    }
    if ( file_exists(THEME."locale/".$settings['locale'].".php") ) {
        include THEME."locale/".$settings['locale'].".php";
    } else {
        include THEME."locale/English.php";
    }
    // Septenary Theme Functions
    require_once THEME."includes/functions.php";
    // Main Theme Functions
    require_once INCLUDES."theme_functions_include.php";
    // Header Scripts
    require_once THEME."includes/add_to_head.php";

    /* Theme Grid Controller */
    function open_grid($class, $box=false) {

        echo "<div class='".$class."'>\n";
        echo ($box) ? "<div class='container'>\n" : '';
    }

    function close_grid($box=false) {
        echo "</div>\n";
        echo ($box) ? "</div>\n" : '';
    }




    function render_page($license = false)
    {
        add_handler("theme_output");
        global $settings, $main_style, $locale, $userdata, $aidlink, $mysql_queries_time;
        require_once THEME."includes/header.php";
        // Header - something fancy for login page.
        if (FUSION_SELF !== 'login.php') {
            require_once THEME."includes/content.php";
        }
        // Footer
        require_once THEME."includes/footer.php";
    }

    // Render comments
    require_once THEME."includes/render_comments.php";
    // Render News And Articles
    require_once THEME."includes/render_news-articles.php";
    // Panel Functions
    require_once THEME."includes/panel_functions.php";
?>