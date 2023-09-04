<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: legaldocs.tpl.php
| Author: meangczac (Chan)
| PHPFusion Lead Developer, PHPFusion Core Developer
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

if (!function_exists( 'display_legal_docs' )) {
    /**
     * Template for legal document view
     *
     * @param $title
     * @param $date
     * @param $content
     */
    function display_legal_docs( $title, $date, $content ) {
        opentable( $title );
        echo '<p><i>' . $date . '</i></p>';
        echo $content;
        closetable();
    }
}