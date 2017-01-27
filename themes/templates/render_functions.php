<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: render_functions.php
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
use PHPFusion\BreadCrumbs;

if (!defined("IN_FUSION")) {
    die("Access Denied");
}

function open_table($title = '') {
    static $opentable = '';
    if (empty($opentable)) {
        ob_start();
        opentable($title);
        $opentable = ob_get_contents();
        ob_end_clean();
        if (empty($opentable)) {
            $opentable = opentable($title);
        }
    }
    return $opentable;
}

function close_table($title = '') {
    static $closetable = '';
    if (empty($closetable)) {
        ob_start();
        closetable($title);
        $closetable = ob_get_contents();
        ob_end_clean();
        if (empty($closetable)) {
            $closetable = opentable($title);
        }
    }
    return $closetable;
}

function open_side($title = '') {
    static $openside = '';
    if (empty($closetable)) {
        ob_start();
        openside($title);
        $openside = ob_get_contents();
        ob_end_clean();
        if (empty($openside)) {
            $openside = openside($title);
        }
    }
    return $openside;
}

function close_side($title = '') {
    static $closeside = '';
    if (empty($closeside)) {
        ob_start();
        closeside($title);
        $closeside = ob_get_contents();
        ob_end_clean();
        if (empty($closeside)) {
            $closeside = closeside($title);
        }
    }
    return $closeside;
}

// Render breadcrumbs template
if (!function_exists("render_breadcrumbs")) {
    function render_breadcrumbs() {
        $breadcrumbs = BreadCrumbs::getInstance();
        $html = "<ol class='".$breadcrumbs->getCssClasses()."'>\n";
        foreach ($breadcrumbs->toArray() as $crumb) {
            $html .= "<li class='".$crumb['class']."'>";
            $html .= ($crumb['link']) ? "<a title='".$crumb['title']."' href='".$crumb['link']."'>".$crumb['title']."</a>" : $crumb['title'];
            $html .= "</li>\n";
        }
        $html .= "</ol>\n";
        return $html;
    }
}

if (!function_exists('render_favicons')) {
    function render_favicons($folder = IMAGES) {
        $html = "";
        /* Src: http://realfavicongenerator.net/favicon_result?file_id=p1avd9jap61od55nq1l2e1e2q7q76#.WAbP6I995D8 */
		if (file_exists($folder)) {
            $html .= "<link rel='apple-touch-icon' sizes='144x144' href='".$folder."favicons/apple-touch-icon.png'>\n";
            $html .= "<link rel='icon' type='image/png' href='".$folder."favicons/favicon-32x32.png' sizes='32x32'>\n";
            $html .= "<link rel='icon' type='image/png' href='".$folder."favicons/favicon-16x16.png' sizes='16x16'>\n";
            $html .= "<link rel='manifest' href='".$folder."favicons/manifest.json'>\n";
            $html .= "<link rel='mask-icon' href='".$folder."favicons/safari-pinned-tab.svg' color='#ccc'>\n";
            $html .= "<meta name='theme-color' content='#ffffff'>\n";

        }
        return $html;
    }
}

if (!function_exists('render_user_tags')) {
    /**
     * The callback function for parseUser()
     * @global array $locale
     * @param string $m The message
     * @return string
     */
    function render_user_tags($m) {
        $locale = fusion_get_locale();
        add_to_jquery("$('[data-toggle=\"user-tooltip\"]').popover();");
        $user = str_replace('@', '', $m[0]);
        $result = dbquery("SELECT user_id, user_name, user_level, user_status, user_avatar FROM ".DB_USERS." WHERE user_name='".$user."' or user_name='".ucwords($user)."' or user_name='".strtolower($user)."' AND user_status='0' LIMIT 1");
        if (dbrows($result) > 0) {
            $data = dbarray($result);
            $src = ($data['user_avatar'] && file_exists(IMAGES."avatars/".$data['user_avatar'])) ? $src = IMAGES."avatars/".$data['user_avatar'] : IMAGES."avatars/no-avatar.jpg";
            $title = '<div class="user-tooltip"><div class="pull-left m-r-10"><img class="img-responsive" style="max-height:40px; max-width:40px;" src="'.$src.'"></div><div class="clearfix"><a title="'.sprintf($locale['go_profile'], $data['user_name']).'" class="strong profile-link strong m-b-10" href="'.BASEDIR.'profile.php?lookup='.$data['user_id'].'">'.$data['user_name'].'</a><br/><span class="user_level">'.getuserlevel($data['user_level']).'</span></div>';
            $content = '<a class="btn btn-block btn-primary" href="'.BASEDIR.'messages.php?msg_send='.$data['user_id'].'">'.$locale['send_message'].'</a>';
            $html = "<a class='strong pointer' tabindex='0' role='button' data-html='true' data-trigger='focus' data-placement='top' data-toggle='user-tooltip' title='".$title."' data-content='".$content."'>";
            $html .= "<span class='user-label'>".$m[0]."</span>";
            $html .= "</a>\n";
            return $html;
        }
        return $m[0];
    }
}