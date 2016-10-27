<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: go.php
| Author: Arda {SoulSmasher}
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once "../maincore.php";
pageAccess('SU');
include THEME."theme.php";
include THEMES.'templates/render_functions.php';

$urlprefix = "";
$url = BASEDIR."index.php";

if (isset($_GET['id']) && isnum($_GET['id'])) {
    $result = dbquery("SELECT submit_criteria FROM ".DB_SUBMISSIONS." WHERE submit_type='l' AND submit_id='".$_GET['id']."'");
    if (dbrows($result)) {
        $data = dbarray($result);
        $submit_criteria = unserialize($data['submit_criteria']);
        if (!strstr($submit_criteria['link_url'], "http://") && !strstr($submit_criteria['link_url'], "https://")) {
            $urlprefix = "http://";
        } else {
            $urlprefix = "";
        }
        $url = $submit_criteria['link_url'];
    }
}

echo '<!DOCTYPE html>';
echo '<html>';
    echo '<head>';
        echo '<meta charset="'.fusion_get_locale('charset').'"/>';
        echo '<title>'.fusion_get_settings('sitename').'</title>';
        echo '<link rel="stylesheet" type="text/css" href="'.THEME.'styles.css"/>';
        if (!defined('NO_DEFAULT_CSS')) {
            echo '<link rel="stylesheet" type="text/css" href="'.THEMES.'templates/default.css"/>';
        }
        echo '<meta http-equiv="refresh" content="2; url='.$urlprefix.$url.'" />';
        echo render_favicons(IMAGES);
        if (function_exists("get_head_tags")) {
            echo get_head_tags();
        }
    echo '</head>';
    echo '<body>';
        echo '<div class="align-center" style="margin-top: 15%;">';
            echo '<img src="'.BASEDIR.fusion_get_settings('sitebanner').'" alt="'.fusion_get_settings('sitename').'"/><br/>';
            echo '<a href="'.$urlprefix.$url.'" rel="nofollow">'.sprintf($locale['global_500'], $urlprefix.$url).'</a>';
        echo '</div>';
    echo '</body>';
echo '</html>';

if (ob_get_length() !== FALSE) {
    ob_end_flush();
}
