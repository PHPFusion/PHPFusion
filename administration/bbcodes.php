<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: bbcodes.php
| Author: Wooya
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
pageAccess('BB');
require_once THEMES."templates/admin_header.php";
$locale = fusion_get_locale('', LOCALE.LOCALESET.'admin/bbcodes.php');
\PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb(['link'=> ADMIN.'bbcodes.php'.fusion_get_aidlink(), "title"=> $locale['400']]);
if (!isset($_GET['page']) || !isnum($_GET['page'])) {
    $_GET['page'] = 1;
}

global $p_data;

//prevent e_notice warning for included bbcode vars
$textarea_name = "";
$inputform_name = "";

$navigation = "<table width='100%' cellpadding='0' cellspacing='1' class='tbl-border'>\n<tr>\n";
$navigation .= "<td width='50%' align='center' class='".($_GET['page'] == 1 ? "tbl2" : "tbl1")."'>".($_GET['page'] == 1 ? "<strong>" : "")."<a href='".FUSION_SELF.$aidlink."&amp;page=1'>".$locale['400']."</a>".($_GET['page'] == 1 ? "</strong>" : "")."</td>\n";
$navigation .= "<td width='50%' align='center' class='".($_GET['page'] == 2 ? "tbl2" : "tbl1")."'>".($_GET['page'] == 2 ? "<strong>" : "")."<a href='".FUSION_SELF.$aidlink."&amp;page=2'>".$locale['401']."</a>".($_GET['page'] == 2 ? "</strong>" : "")."</td>\n";
$navigation .= "</tr>\n</table>\n";
$navigation .= "<div style='margin:15px'></div>\n";

if ($_GET['page'] == 1) {
    if ((isset($_GET['action']) && $_GET['action'] == "mup") && (isset($_GET['bbcode_id']) && isnum($_GET['bbcode_id']))) {
        $data = dbarray(dbquery("SELECT bbcode_id FROM ".DB_BBCODES." WHERE bbcode_order='".intval($_GET['order'])."'"));
        $result = dbquery("UPDATE ".DB_BBCODES." SET bbcode_order=bbcode_order+1 WHERE bbcode_id='".$data['bbcode_id']."'");
        $result = dbquery("UPDATE ".DB_BBCODES." SET bbcode_order=bbcode_order-1 WHERE bbcode_id='".$_GET['bbcode_id']."'");
        redirect(FUSION_SELF.$aidlink);
    } elseif ((isset($_GET['action']) && $_GET['action'] == "mdown") && (isset($_GET['bbcode_id']) && isnum($_GET['bbcode_id']))) {
        $data = dbarray(dbquery("SELECT bbcode_id FROM ".DB_BBCODES." WHERE bbcode_order='".intval($_GET['order'])."'"));
        $result = dbquery("UPDATE ".DB_BBCODES." SET bbcode_order=bbcode_order-1 WHERE bbcode_id='".$data['bbcode_id']."'");
        $result = dbquery("UPDATE ".DB_BBCODES." SET bbcode_order=bbcode_order+1 WHERE bbcode_id='".$_GET['bbcode_id']."'");
        redirect(FUSION_SELF.$aidlink);
    } elseif (isset($_GET['enable']) && preg_match("/^!?([a-z0-9_-]){1,50}$/i",
                                                   $_GET['enable']) && file_exists(INCLUDES."bbcodes/".$_GET['enable']."_bbcode_include_var.php") && file_exists(INCLUDES."bbcodes/".$_GET['enable']."_bbcode_include.php")
    ) {
        if (substr($_GET['enable'], 0, 1) != '!') {
            $data2 = dbarray(dbquery("SELECT MAX(bbcode_order) AS xorder FROM ".DB_BBCODES));
            $order = ($data2['xorder'] == 0 ? 1 : ($data2['xorder'] + 1));
            $result = dbquery("INSERT INTO ".DB_BBCODES." (bbcode_name, bbcode_order) VALUES ('".$_GET['enable']."', '".$order."')");
        } else {
            $result2 = dbcount("(bbcode_id)", DB_BBCODES);
            if (!empty($result2)) {
                $result3 = dbquery("UPDATE ".DB_BBCODES." SET bbcode_order=bbcode_order+1");
            }
            $result3 = dbquery("INSERT INTO ".DB_BBCODES." (bbcode_name, bbcode_order) VALUES ('".$_GET['enable']."', '1')");
        }
        redirect(FUSION_SELF.$aidlink);
    } elseif (isset($_GET['disable']) && isnum($_GET['disable'])) {
        $result = dbquery("DELETE FROM ".DB_BBCODES." WHERE bbcode_id='".$_GET['disable']."'");
        $result = dbquery("SELECT bbcode_order FROM ".DB_BBCODES." ORDER BY bbcode_order");
        $order = 1;
        while ($data = dbarray($result)) {
            $result2 = dbquery("UPDATE ".DB_BBCODES." SET bbcode_order='".$order."' WHERE bbcode_order='".$data['bbcode_order']."'");
            $order++;
        }
        redirect(FUSION_SELF.$aidlink);
    }
    $available_bbcodes = array();
    if ($handle_bbcodes = opendir(INCLUDES."bbcodes/")) {
        while (FALSE !== ($file_bbcodes = readdir($handle_bbcodes))) {
            if (!in_array($file_bbcodes, array("..", ".", "index.php")) && !is_dir(INCLUDES."bbcodes/".$file_bbcodes)) {
                if (preg_match("/_include.php/i", $file_bbcodes) && !preg_match("/_var.php/i", $file_bbcodes) && !preg_match("/_save.php/i", $file_bbcodes) && !preg_match("/.js/i", $file_bbcodes)
                ) {
                    $bbcode_name = explode("_", $file_bbcodes);
                    $available_bbcodes[] = $bbcode_name[0];
                    unset($bbcode_name);
                }
            }
        }
        closedir($handle_bbcodes);
    }
    sort($available_bbcodes);
    $enabled_bbcodes = array();
    opentable($locale['402']);
    echo $navigation;
    $result = dbquery("SELECT * FROM ".DB_BBCODES." ORDER BY bbcode_order");
    if (dbrows($result)) {
        echo "<div style='width:100%;'>\n";
        echo "<table cellpadding='0' cellspacing='1' class='table table-responsive tbl-border'>\n<thead>\n<tr>\n";
        echo "<th width='1%' class='tbl2' style='white-space:nowrap'><strong>".$locale['403']."</strong></th>\n";
        echo "<th width='1%' class='tbl2' style='white-space:nowrap'><strong>".$locale['404']."</strong></th>\n";
        echo "<th class='tbl2'><strong>".$locale['405']."</strong></th>\n";
        echo "<th class='tbl2'><strong>".$locale['406']."</strong></th>\n";
        echo "<th align='center' colspan='2' width='1%' class='tbl2' style='white-space:nowrap'><strong>".$locale['407']."</strong></th>\n";
        echo "<th width='1%' class='tbl2' style='white-space:nowrap'></th>\n";
        echo "</tr>\n</thead>\n<tbody>\n";
        $lp = 0;
        $ps = 1;
        $i = 1;
        $numrows = dbcount("(bbcode_id)", DB_BBCODES);
        while ($data = dbarray($result)) {
            if ($numrows != 1) {
                $up = $data['bbcode_order'] - 1;
                $down = $data['bbcode_order'] + 1;
                if ($i == 1) {
                    $up_down = " <a href='".FUSION_SELF.$aidlink."&amp;action=mdown&amp;bbcode_id=".$data['bbcode_id']."&amp;order=$down'><img src='".get_image("down")."' alt='".$locale['408']."' title='".$locale['408']."' style='border:0px;' /></a>\n";
                } else {
                    if ($i < $numrows) {
                        $up_down = " <a href='".FUSION_SELF.$aidlink."&amp;action=mup&amp;bbcode_id=".$data['bbcode_id']."&amp;order=$up'><img src='".get_image("up")."' alt='".$locale['409']."' title='".$locale['409']."' style='border:0px;' /></a>\n";
                        $up_down .= " <a href='".FUSION_SELF.$aidlink."&amp;action=mdown&amp;bbcode_id=".$data['bbcode_id']."&amp;order=$down'><img src='".get_image("down")."' alt='".$locale['408']."' title='".$locale['408']."' style='border:0px;' /></a>\n";
                    } else {
                        $up_down = " <a href='".FUSION_SELF.$aidlink."&amp;action=mup&amp;bbcode_id=".$data['bbcode_id']."&amp;order=$up'><img src='".get_image("up")."' alt='".$locale['409']."' title='".$locale['409']."' style='border:0px;' /></a>\n";
                    }
                }
            } else {
                $up_down = "";
            }
            $i++;
            $lp++;
            $enabled_bbcodes[] = $data['bbcode_name'];
            if (file_exists(INCLUDES."bbcodes/images/".$data['bbcode_name'].".png")) {
                $bbcode_image = "<img src='".INCLUDES."bbcodes/images/".$data['bbcode_name'].".png' alt='".$data['bbcode_name']."' style='border:1px solid black;' />\n";
            } else if (file_exists(INCLUDES."bbcodes/images/".$data['bbcode_name'].".gif")) {
                $bbcode_image = "<img src='".INCLUDES."bbcodes/images/".$data['bbcode_name'].".gif' alt='".$data['bbcode_name']."' style='border:1px solid black;' />\n";
            } else if (file_exists(INCLUDES."bbcodes/images/".$data['bbcode_name'].".jpg")) {
                $bbcode_image = "<img src='".INCLUDES."bbcodes/images/".$data['bbcode_name'].".jpg' alt='".$data['bbcode_name']."' style='border:1px solid black;' />\n";
            } else if (file_exists(INCLUDES."bbcodes/images/".$data['bbcode_name'].".svg")) {
                $bbcode_image = "<img src='".INCLUDES."bbcodes/images/".$data['bbcode_name'].".svg' alt='".$data['bbcode_name']."' style='border:1px solid black; width: 24px; height: 24px;' />\n";
            } else {
                $bbcode_image = "-";
            }
            $cls = ($lp % 2 == 0 ? "tbl2" : "tbl1");
            echo "<tr>\n";
            if (file_exists(LOCALE.LOCALESET."bbcodes/".$data['bbcode_name'].".php")) {
                $locale_file = LOCALE.LOCALESET."bbcodes/".$data['bbcode_name'].".php";
            } elseif (file_exists(LOCALE."English/bbcodes/".$data['bbcode_name'].".php")) {
                $locale_file = LOCALE."English/bbcodes/".$data['bbcode_name'].".php";
            }
            $locale = fusion_get_locale('', $locale_file);
            include INCLUDES."bbcodes/".$data['bbcode_name']."_bbcode_include_var.php";
            echo "<td width='1%' class='$cls' style='white-space:nowrap'>".ucwords($data['bbcode_name'])."</td>\n";
            echo "<td align='center' width='1%' class='$cls' style='white-space:nowrap'>".$bbcode_image."</td>\n";
            echo "<td class='$cls'>".$__BBCODE__[0]['description']."</td>\n";
            echo "<td class='$cls'>".$__BBCODE__[0]['usage']."</td>\n";
            unset ($__BBCODE__);
            echo "<td align='center' width='1%' class='$cls' style='white-space:nowrap'>".$data['bbcode_order']."</td>\n";
            echo "<td align='center' width='1%' class='$cls' style='white-space:nowrap'>".$up_down."</td>\n";
            echo "<td align='center' width='1%' class='$cls' style='white-space:nowrap'><a href='".FUSION_SELF.$aidlink."&amp;disable=".$data['bbcode_id']."'>".$locale['410']."</a></td>\n";
            echo "</tr>\n";
        }
        echo "</tbody>\n</table>\n";
        echo "</div>\n";
    } else {
        echo "<div style='text-align:center'>".$locale['411']."</div>\n";
    }
    closetable();

    $enabled = dbcount("(bbcode_id)", DB_BBCODES);
    opentable($locale['413']);
    if (count($available_bbcodes) != $enabled) {
        echo "<div style='width:100%;height:550px;overflow:auto'>\n";
        echo "<table cellpadding='0' cellspacing='1' class='table table-responsive tbl-border'>\n<thead>\n<tr>\n";
        echo "<td width='1%' class='tbl2' style='white-space:nowrap'><strong>".$locale['403']."</strong></td>\n";
        echo "<td width='1%' class='tbl2' style='white-space:nowrap'><strong>".$locale['404']."</strong></td>\n";
        echo "<td class='tbl2'><strong>".$locale['405']."</strong></td>\n";
        echo "<td class='tbl2'><strong>".$locale['406']."</strong></td>\n";
        echo "<td width='1%' class='tbl2' style='white-space:nowrap'></td>\n";
        echo "</tr>\n</thead>\n<tbody>\n";
        $xx = 0;
        foreach ($available_bbcodes as $available_bbcode) {
            $__BBCODE__ = array();
            if (!in_array($available_bbcode, $enabled_bbcodes)) {
            if (file_exists(INCLUDES."bbcodes/images/".$available_bbcode.".png")) {
                $bbcode_image = "<img src='".INCLUDES."bbcodes/images/".$available_bbcode.".png' alt='".$available_bbcode."' style='border:1px solid black;' />\n";
            } else if (file_exists(INCLUDES."bbcodes/images/".$available_bbcode.".gif")) {
                $bbcode_image = "<img src='".INCLUDES."bbcodes/images/".$available_bbcode.".gif' alt='".$available_bbcode."' style='border:1px solid black;' />\n";
            } else if (file_exists(INCLUDES."bbcodes/images/".$available_bbcode.".jpg")) {
                $bbcode_image = "<img src='".INCLUDES."bbcodes/images/".$available_bbcode.".jpg' alt='".$available_bbcode."' style='border:1px solid black;' />\n";
            } else if (file_exists(INCLUDES."bbcodes/images/".$available_bbcode.".svg")) {
                $bbcode_image = "<img src='".INCLUDES."bbcodes/images/".$available_bbcode.".svg' alt='".$available_bbcode."' style='border:1px solid black; width: 24px; height: 24px;' />\n";
            } else {
                $bbcode_image = "-";
            }

                if (file_exists(LOCALE.LOCALESET."bbcodes/".$available_bbcode.".php")) {
                    include(LOCALE.LOCALESET."bbcodes/".$available_bbcode.".php");
                } elseif (file_exists(LOCALE."English/bbcodes/".$available_bbcode.".php")) {
                    include(LOCALE."English/bbcodes/".$available_bbcode.".php");
                }

                include INCLUDES."bbcodes/".$available_bbcode."_bbcode_include_var.php";

                $cls = ($xx % 2 == 0 ? "tbl2" : "tbl1");
                echo "<tr>\n";
                echo "<td width='1%' class='$cls' style='white-space:nowrap'>".ucwords($available_bbcode)."</td>\n";
                echo "<td align='center' width='1%' class='$cls' style='white-space:nowrap'>".$bbcode_image."</td>\n";
                echo "<td class='$cls'>".$__BBCODE__[0]['description']."</td>\n";
                echo "<td class='$cls'>".$__BBCODE__[0]['usage']."</td>\n";
                echo "<td align='center' width='1%' class='$cls' style='white-space:nowrap'><a href='".FUSION_SELF.$aidlink."&amp;enable=".$available_bbcode."'>".$locale['414']."</a></td>\n";
                echo "</tr>\n";
                unset ($__BBCODE__);
                $xx++;
            }
        }
        echo "</tbody>\n</table>\n";
        echo "</div>\n";
    } else {
        echo "<div style='text-align:center'>".$locale['416']."</div>\n";
    }
    closetable();
} else {
    if ($_GET['page'] == 2) {
        if (isset($_POST['post_test'])) {
            $test_message = form_sanitizer($_POST['test_message'], '', 'test_message');
            $smileys_checked = isset($_POST['test_smileys']) || preg_match("#(\[code\](.*?)\[/code\]|\[geshi=(.*?)\](.*?)\[/geshi\]|\[php\](.*?)\[/php\])#si", $test_message) ? " checked='checked'" : "";
            if ($defender->safe()) {
                opentable($locale['417']);
                echo "<div class='well'>\n";
                if (!$smileys_checked) {
                    echo parseubb(parsesmileys($test_message));
                } else {
                    echo parseubb($test_message);
                }
                echo "</div>\n";
                closetable();
            }
        } else {
            $test_message = "";
            $smileys_checked = "";
        }
        include LOCALE.LOCALESET."comments.php";
        opentable($locale['401']);
        echo $navigation;
        echo openform('input_form', 'post', FUSION_SELF.$aidlink."&amp;page=2");
        echo "<table cellspacing='0' cellpadding='0' class='table table-responsive center'>\n<tr>\n";
        echo "<td class='tbl'>\n";
        echo form_textarea('test_message', $locale['418a'], $test_message, array('required' => 1, 'error_text' => $locale['418b'], 'bbcode' => 1));
        echo "</td>\n</tr>\n<tr>\n";
        echo "<td align='center' class='tbl'><label><input type='checkbox' name='test_smileys' value='1' ".$smileys_checked." />&nbsp;".$locale['418']."</label><br /><br />\n";
        echo form_button('post_test', $locale['401'], $locale['401'], array('class' => 'btn-primary'));
        echo "</td>\n</tr>\n</table>\n</form>\n";
        closetable();
    }
}
require_once THEMES."templates/footer.php";