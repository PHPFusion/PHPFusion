<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: bbcodes.php
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
require_once __DIR__.'/../maincore.php';
pageAccess('BB');
require_once THEMES.'templates/admin_header.php';
$locale = fusion_get_locale('', LOCALE.LOCALESET."admin/bbcodes.php");

use \PHPFusion\BreadCrumbs;

global $p_data;

BreadCrumbs::getInstance()->addBreadCrumb(['link' => ADMIN.'bbcodes.php'.fusion_get_aidlink(), 'title' => $locale['BBCA_400']]);
$allowed_section = ['bbcode_form', 'bbcode_list'];
$_GET['section'] = isset($_GET['section']) && in_array($_GET['section'], $allowed_section) ? $_GET['section'] : 'bbcode_form';

$tab_title['title'][] = $locale['BBCA_400a'];
$tab_title['id'][] = 'bbcode_form';
$tab_title['icon'][] = '';

$tab_title['title'][] = $locale['BBCA_401'];
$tab_title['id'][] = 'bbcode_list';
$tab_title['icon'][] = '';

opentable($locale['BBCA_400']);
echo opentab($tab_title, $_GET['section'], 'bbcode_list', TRUE, 'nav-tabs m-b-15');
switch ($_GET['section']) {
    case "bbcode_form":
        bbcode_form();
        break;
    default:
        bbcode_list();
        break;
}
echo closetab();
closetable();

function bbcode_list() {
    $locale = fusion_get_locale('', LOCALE.LOCALESET.'comments.php');
    $test_message = '';
    $smileys_checked = 0;

    if (isset($_POST['post_test'])) {
        $test_message = form_sanitizer($_POST['test_message'], '', 'test_message');
        $smileys_checked = isset($_POST['test_smileys']) || preg_match("#(\[code\](.*?)\[/code\]|\[geshi=(.*?)\](.*?)\[/geshi\]|\[php\](.*?)\[/php\])#si",
            $test_message) ? 1 : 0;
        if (\defender::safe()) {
            opentable($locale['BBCA_417']);
            echo "<div class='well'>\n";
            if (!$smileys_checked) {
                echo parseubb(parsesmileys($test_message));
            } else {
                echo parseubb($test_message);
            }
            echo "</div>\n";
            closetable();
        }
    }

    opentable($locale['BBCA_401']);
    echo openform('input_form', 'post', FUSION_SELF.fusion_get_aidlink()."&amp;section=bbcode_list");
    echo form_textarea('test_message', $locale['BBCA_418a'], $test_message, [
        'required'   => TRUE,
        'error_text' => $locale['BBCA_418b'],
        'type'       => 'bbcode'
    ]);

    echo '<div class="row">';
    echo "<div class='col-xs-6 col-md-6 text-right'>\n";
    echo form_checkbox('test_smileys', $locale['BBCA_418'], $smileys_checked, [
        'type'          => 'checkbox',
        'reverse_label' => TRUE
    ]);
    echo "</div>\n";
    echo "<div class='col-xs-6 col-md-6 text-left'>\n";
    echo form_button('post_test', $locale['BBCA_401'], $locale['BBCA_401'], ['class' => 'btn-primary']);
    echo "</div>\n";
    echo "</div>\n";
    closeform();
    closetable();

}

function bbcode_form() {
    $locale = fusion_get_locale('', LOCALE.LOCALESET."comments.php");
    $aidlink = fusion_get_aidlink();
    $available_bbcodes = [];
    $enabled_bbcodes = [];
    $textarea_name = "";
    $inputform_name = "";
    $__BBCODE__ = [];

    if ((isset($_GET['action']) && $_GET['action'] == "mup") && (isset($_GET['bbcode_id']) && isnum($_GET['bbcode_id']))) {
        $data = dbarray(dbquery("SELECT bbcode_id FROM ".DB_BBCODES." WHERE bbcode_order=:bbcodeorder", [':bbcodeorder' => intval($_GET['order'])]));
        dbquery("UPDATE ".DB_BBCODES." SET bbcode_order=bbcode_order+1 WHERE bbcode_id=:bbcodeid", [':bbcodeid' => $data['bbcode_id']]);
        dbquery("UPDATE ".DB_BBCODES." SET bbcode_order=bbcode_order-1 WHERE bbcode_id=:bbcode", [':bbcode' => $_GET['bbcode_id']]);
        addNotice('info', $locale['BBCA_430']);
        redirect(clean_request('', ['section', 'action', 'bbcode_id', 'order'], FALSE));

    } else if ((isset($_GET['action']) && $_GET['action'] == "mdown") && (isset($_GET['bbcode_id']) && isnum($_GET['bbcode_id']))) {
        $data = dbarray(dbquery("SELECT bbcode_id FROM ".DB_BBCODES." WHERE bbcode_order=:bbcodeorder", [':bbcodeorder' => intval($_GET['order'])]));
        dbquery("UPDATE ".DB_BBCODES." SET bbcode_order=bbcode_order-1 WHERE bbcode_id=:bbcodeid", [':bbcodeid' => $data['bbcode_id']]);
        dbquery("UPDATE ".DB_BBCODES." SET bbcode_order=bbcode_order+1 WHERE bbcode_id=:bbcode", [':bbcode' => $_GET['bbcode_id']]);
        addNotice('info', $locale['BBCA_431']);
        redirect(clean_request('', ['section', 'action', 'bbcode_id', 'order'], FALSE));

    } else if (isset($_GET['enable']) && preg_match("/^!?([a-z0-9_-]){1,50}$/i",
            $_GET['enable']) && file_exists(INCLUDES."bbcodes/".$_GET['enable']."_bbcode_include_var.php") && file_exists(INCLUDES."bbcodes/".$_GET['enable']."_bbcode_include.php")
    ) {
        if (substr($_GET['enable'], 0, 1) != '!') {
            $data2 = dbarray(dbquery("SELECT MAX(bbcode_order) AS xorder FROM ".DB_BBCODES));
            $order = ($data2['xorder'] == 0 ? 1 : ($data2['xorder'] + 1));
            dbquery("INSERT INTO ".DB_BBCODES." (bbcode_name, bbcode_order) VALUES ('".$_GET['enable']."', '".$order."')");
        } else {
            $result2 = dbcount("(bbcode_id)", DB_BBCODES);
            if (!empty($result2)) {
                dbquery("UPDATE ".DB_BBCODES." SET bbcode_order=bbcode_order+1");
            }
            dbquery("INSERT INTO ".DB_BBCODES." (bbcode_name, bbcode_order) VALUES ('".$_GET['enable']."', '1')");
        }
        addNotice('info', $locale['BBCA_432']);
        redirect(clean_request('', ['section', 'enable'], FALSE));

    } else if (isset($_GET['disable']) && isnum($_GET['disable'])) {
        dbquery("DELETE FROM ".DB_BBCODES." WHERE bbcode_id=:bbcodeid", [':bbcodeid' => $_GET['disable']]);
        $result = dbquery("SELECT bbcode_order FROM ".DB_BBCODES." ORDER BY bbcode_order");
        $order = 1;
        while ($data = dbarray($result)) {
            dbquery("UPDATE ".DB_BBCODES." SET bbcode_order=:norder WHERE bbcode_order=:bbcodeorder", [':norder' => $order, ':bbcodeorder' => $data['bbcode_order']]);
            $order++;
        }
        addNotice('warning', $locale['BBCA_433']);
        redirect(clean_request('', ['section', 'disable'], FALSE));
    }

    $bbcode_folder = makefilelist(INCLUDES."bbcodes/", '.|..|index.php|.js', TRUE, 'files');
    if (!empty($bbcode_folder)) {
        foreach ($bbcode_folder as $bbcode_folders) {
            if (preg_match("/_include.php/i", $bbcode_folders)) {
                $bbcode_name = explode("_", $bbcode_folders);
                $available_bbcodes[] = $bbcode_name[0];
            }
        }
    }

    $result = dbquery("SELECT * FROM ".DB_BBCODES." ORDER BY bbcode_order");
    sort($available_bbcodes);
    if (dbrows($result)) {
        opentable($locale['BBCA_402']);
        echo "<div class='table-responsive'><table class='table table-hover table-striped'>\n<thead>\n<tr>\n";
        echo "<th><strong>".$locale['BBCA_403']."</strong></th>\n";
        echo "<th><strong>".$locale['BBCA_404']."</strong></th>\n";
        echo "<th><strong>".$locale['BBCA_405']."</strong></th>\n";
        echo "<th><strong>".$locale['BBCA_406']."</strong></th>\n";
        echo "<th class='text-center' colspan='2'><strong>".$locale['BBCA_407']."</strong></th>\n";
        echo "<th></th>\n";
        echo "</tr>\n</thead>\n<tbody>\n";
        $i = 1;
        $numrows = dbcount("(bbcode_id)", DB_BBCODES);
        while ($data = dbarray($result)) {
            if ($numrows != 1) {
                $up = $data['bbcode_order'] - 1;
                $down = $data['bbcode_order'] + 1;
                if ($i == 1) {
                    $up_down = " <a href='".FUSION_SELF.$aidlink."&amp;action=mdown&amp;bbcode_id=".$data['bbcode_id']."&amp;order=$down'><img src='".get_image("down")."' alt='".$locale['BBCA_408']."' title='".$locale['BBCA_408']."' style='border:none;' /></a>\n";
                } else {
                    if ($i < $numrows) {
                        $up_down = " <a href='".FUSION_SELF.$aidlink."&amp;action=mup&amp;bbcode_id=".$data['bbcode_id']."&amp;order=$up'><img src='".get_image("up")."' alt='".$locale['BBCA_409']."' title='".$locale['BBCA_409']."' style='border:none;' /></a>\n";
                        $up_down .= " <a href='".FUSION_SELF.$aidlink."&amp;action=mdown&amp;bbcode_id=".$data['bbcode_id']."&amp;order=$down'><img src='".get_image("down")."' alt='".$locale['BBCA_408']."' title='".$locale['BBCA_408']."' style='border:none;' /></a>\n";
                    } else {
                        $up_down = " <a href='".FUSION_SELF.$aidlink."&amp;action=mup&amp;bbcode_id=".$data['bbcode_id']."&amp;order=$up'><img src='".get_image("up")."' alt='".$locale['BBCA_409']."' title='".$locale['BBCA_409']."' style='border:none;' /></a>\n";
                    }
                }
            } else {
                $up_down = "";
            }
            $i++;

            $enabled_bbcodes[] = $data['bbcode_name'];
            $check_path = __DIR__.'/../includes/bbcodes/images/';
            $img_path = FUSION_ROOT.fusion_get_settings('site_path').'includes/bbcodes/images/';
            $bbcode_attr = ['.svg', '.png', '.gif', '.jpg'];
            $bbcode_image = '-';
            foreach ($bbcode_attr as $attr) {
                if (file_exists($check_path.$data['bbcode_name'].$attr)) {
                    $bbcode_image = "<img src='".$img_path.$data['bbcode_name'].$attr."' alt='".$data['bbcode_name']."' title='".$data['bbcode_name']."' style='border:1px solid black; ".($attr == '.svg' ? 'width: 24px; height: 24px;' : '')."' />\n";
                    break;
                }
            }

            if (file_exists(LOCALE.LOCALESET."bbcodes/".$data['bbcode_name'].".php")) {
                $locale = fusion_get_locale('', LOCALE.LOCALESET."bbcodes/".$data['bbcode_name'].".php");
            } else if (file_exists(LOCALE."English/bbcodes/".$data['bbcode_name'].".php")) {
                $locale = fusion_get_locale('', LOCALE."English/bbcodes/".$data['bbcode_name'].".php");
            }

            if (file_exists(INCLUDES."bbcodes/".$data['bbcode_name']."_bbcode_include_var.php")) {
                include INCLUDES."bbcodes/".$data['bbcode_name']."_bbcode_include_var.php";

                echo "<tr>\n";
                echo "<td>".ucwords($data['bbcode_name'])."</td>\n";
                echo "<td class='text-center'>".$bbcode_image."</td>\n";
                echo "<td>".$__BBCODE__[0]['description']."</td>\n";
                echo "<td>".$__BBCODE__[0]['usage']."</td>\n";
                unset ($__BBCODE__);
                echo "<td class='text-center'>".$data['bbcode_order']."</td>\n";
                echo "<td class='text-center'>".$up_down."</td>\n";
                echo "<td class='text-center'><a href='".FUSION_SELF.$aidlink."&amp;disable=".$data['bbcode_id']."'>".$locale['BBCA_410']."</a></td>\n";
                echo "</tr>\n";
            }
        }
        echo "</tbody>\n</table>\n";
        echo "</div>\n";
    } else {
        echo "<div class='text-center'>".$locale['BBCA_411']."</div>\n";
    }
    closetable();
    $enabled = dbcount("(bbcode_id)", DB_BBCODES);
    opentable($locale['BBCA_413']);
    if (count($available_bbcodes) != $enabled) {
        echo "<div class='table-responsive'><table class='table table-hover table-striped'>\n<thead>\n<tr>\n";
        echo "<th><strong>".$locale['BBCA_403']."</strong></th>\n";
        echo "<th><strong>".$locale['BBCA_404']."</strong></th>\n";
        echo "<th><strong>".$locale['BBCA_405']."</strong></th>\n";
        echo "<th><strong>".$locale['BBCA_406']."</strong></th>\n";
        echo "<th></th>\n";
        echo "</tr>\n</thead>\n<tbody>\n";

        foreach ($available_bbcodes as $available_bbcode) {
            $__BBCODE__ = [];
            $check_path = __DIR__.'/../includes/bbcodes/images/';
            $img_path = FUSION_ROOT.fusion_get_settings('site_path').'includes/bbcodes/images/';
            $bbcode_attr = ['.svg', '.png', '.gif', '.jpg'];
            $bbcode_image = '-';

            if (!in_array($available_bbcode, $enabled_bbcodes)) {
                foreach ($bbcode_attr as $attr) {
                    if (file_exists($check_path.$available_bbcode.$attr)) {
                        $bbcode_image = "<img src='".$img_path.$available_bbcode.$attr."' alt='".$available_bbcode."' style='border:1px solid black;".($attr == '.svg' ? 'width: 24px; height: 24px;' : '')."' />\n";
                        break;
                    }
                }

                if (file_exists(LOCALE.LOCALESET."bbcodes/".$available_bbcode.".php")) {
                    $locale = fusion_get_locale('', LOCALE.LOCALESET."bbcodes/".$available_bbcode.".php");
                } else if (file_exists(LOCALE."English/bbcodes/".$available_bbcode.".php")) {
                    $locale = fusion_get_locale('', LOCALE."English/bbcodes/".$available_bbcode.".php");
                }

                include INCLUDES."bbcodes/".$available_bbcode."_bbcode_include_var.php";
                echo "<tr>\n";
                echo "<td>".ucwords($available_bbcode)."</td>\n";
                echo "<td class='text-center'>".$bbcode_image."</td>\n";
                echo "<td>".$__BBCODE__[0]['description']."</td>\n";
                echo "<td>".$__BBCODE__[0]['usage']."</td>\n";
                echo "<td class='text-center'><a href='".FUSION_SELF.$aidlink."&amp;enable=".$available_bbcode."'>".$locale['BBCA_414']."</a></td>\n";
                echo "</tr>\n";
                unset ($__BBCODE__);
            }
        }
        echo "</tbody>\n</table>\n";
        echo "</div>\n";
    } else {
        echo "<div class='text-center'>".$locale['BBCA_416']."</div>\n";
    }
    closetable();

}

require_once THEMES.'templates/footer.php';
