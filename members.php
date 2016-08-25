<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: members.php
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
require_once file_exists('maincore.php') ? 'maincore.php' : __DIR__."maincore.php";
if (!db_exists(DB_USERS)) {
    redirect(BASEDIR."error.php?code=404");
}
require_once THEMES."templates/header.php";
$locale = fusion_get_locale('', LOCALE.LOCALESET."members.php");

add_to_title($locale['global_200'].$locale['400'].\PHPFusion\SiteLinks::get_current_SiteLinks("", "link_name"));

opentable("<i class='fa fa-fw fa-user m-r-10'></i>".$locale['400']);
if (iMEMBER) {
    if (!isset($_GET['sortby']) || !ctype_alnum($_GET['sortby'])) {
        $_GET['sortby'] = "all";
    }
    $orderby = ($_GET['sortby'] == "all" ? "" : " AND user_name LIKE '".stripinput($_GET['sortby'])."%'");
    $search_text = ((isset($_GET['search_text']) && preg_check("/^[-0-9A-Z_@\s]+$/i",
                                                               $_GET['search_text'])) ? $orderby = ' AND user_name LIKE "'.stripinput($_GET['search_text']).'%"' : $_GET['sortby']);

    $rows = dbcount("(user_id)", DB_USERS, (iADMIN ? "user_status>='0'" : "user_status='0'").$orderby);

    $_GET['rowstart'] = (!isset($_GET['rowstart']) || !isnum($_GET['rowstart'])) ? 0 : $_GET['rowstart'];

    $search = array_merge(range("A", "Z"), range(0, 9));

    echo "<table class='table table-responsive table-striped center'>\n<tr>\n";
    echo "<td rowspan='2' class='tbl2'><a class='strong' href='".FUSION_SELF."?sortby=all'>".$locale['404']."</a></td>";
    for ($i = 0; $i < count($search) != ""; $i++) {
        echo "<td align='center' class='tbl1'><div class='small'><a href='".FUSION_SELF."?sortby=".$search[$i]."'>".$search[$i]."</a></div></td>";
        echo($i == 17 ? "<td rowspan='2' class='tbl2'><a class='strong' href='".FUSION_SELF."?sortby=all'>".$locale['404']."</a></td>\n</tr>\n<tr>\n" : "\n");
    }
    echo "</tr>\n</table>\n";

    echo "<hr />\n";
    echo "<div class='text-center m-b-20'>\n";
    echo openform('searchform', 'get', FUSION_SELF, array('max_tokens' => 1, 'notice' => 0));
    echo form_text('search_text', $locale['408'], '', array(
        'inline' => TRUE,
        'placeholder' => $locale['401'],
        'append_button' => TRUE,
        'append_type' => "submit",
        "append_form_value" => $locale['409'],
        "append_value" => "<i class='fa fa-search'></i> ".$locale['409'],
        "append_button_name" => $locale['409'],
        'class' => 'no-border m-b-0',
    ));
    echo closeform();

    echo "</div>\n";
    if (!empty($rows)) {
        echo "<table id='unread_tbl' class='table table-responsive table-hover'>\n";
        echo "<tr>\n";
        echo "<td class='col-xs-1'>".$locale['411']."</td>\n";
        echo "<td class='col-xs-2'>".$locale['401']."</td>\n";
        echo "<td class='col-xs-3'>".$locale['405']."</td>\n";
        echo "<td class='col-xs-2'>".$locale['402']."</td>\n";
        echo "<td class='col-xs-2'>".$locale['410']."</td>\n";
        echo "<td class='col-xs-1'>".$locale['status']."</td>\n";
        echo "</tr>\n";

        $result = dbquery("SELECT user_id, user_name, user_status, user_level, user_groups, user_language, user_joined, user_avatar
        FROM ".DB_USERS."
        WHERE ".(iADMIN ? "user_status>='0'" : "user_status='0'").$orderby."
        ORDER BY user_level DESC, user_language, user_name ASC
        LIMIT ".intval($_GET['rowstart']).",20"
        );

        while ($data = dbarray($result)) {

            echo "<td class='col-xs-1'>".display_avatar($data, '50px')."</td>\n";
            echo "<td class='col-xs-2'><span class='side'>".profile_link($data['user_id'], $data['user_name'], $data['user_status'])."</span></td>\n";
            $groups = "";
            $user_groups = explode(".", $data['user_groups']);
            foreach ($user_groups as $key => $value) {
                if ($value) {
                    $groups .= "<a class='btn btn-default btn-sm' href='profile.php?group_id=".$value."'>".getgroupname($value)."</a>\n";

                }
            }
            echo "<td class='col-xs-3'>\n".($groups ? $groups : ($data['user_level'] == USER_LEVEL_SUPER_ADMIN ? $locale['407'] : $locale['406']))."</td>\n";
            echo "<td class='col-xs-2'>".getuserlevel($data['user_level'])."</td>\n";
            echo "<td class='col-xs-2'>".$data['user_language']."</td>\n";
            echo "<td class='col-xs-1'>".getuserstatus($data['user_status'])."</td>\n</tr>\n";
        }
        echo "</table>\n";
    } else {
        echo "<div class='well text-center'>".$locale['403'].(isset($_GET['search_text']) ? $_GET['search_text'] : $_GET['sortby'])."</div>\n";
    }

} else {
    redirect("index.php");
}

echo $rows > 20 ? "<div class='pull-right m-r-10'>".makepagenav($_GET['rowstart'], 20, $rows, 3,
                                                                FUSION_SELF."?sortby=".$_GET['sortby']."&amp;")."</div>\n" : "";

closetable();
require_once THEMES."templates/footer.php";