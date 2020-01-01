<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: panel_editor.php
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
if (!checkrights("P") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) {redirect("../index.php");}

if (!isset($_GET['panel_side']) || !isNum($_GET['panel_side']) || $_GET['panel_side'] > 6) {
    $panel_side = 1;
} else {
    $panel_side = $_GET['panel_side'];
}

require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/panels.php";

$temp = opendir(INFUSIONS);
while ($folder = readdir($temp)) {
    if (!in_array($folder, [".", ".."]) && strstr($folder, "_panel")) {
        if (is_dir(INFUSIONS.$folder))
            $panel_list[] = $folder;
    }
}
closedir($temp);
sort($panel_list);
array_unshift($panel_list, "none");

if (isset($_POST['save'])) {
    $error = "";
    $panel_name = stripinput($_POST['panel_name']);
    $panel_url_list = stripinput($_POST['panel_url_list']);
    $panel_restriction = isset($_POST['panel_restriction']) && $_POST['panel_restriction'] == 1 ? 1 : 0;
    if ($panel_name == "")
        $error .= $locale['470']."<br />";
    if ($_POST['panel_filename'] == "none") {
        $panel_filename = "";
        $panel_content = (isset($_POST['panel_content']) ? addslash($_POST['panel_content']) : (($panel_side == 1 || $panel_side == 4) ? "openside(\"name\");\n"."echo \"Content\";\n"."closeside();" : "opentable(\"name\");\n"."echo \"Content\";\n"."closetable();"));
        $panel_type = "php";
    } else {
        $panel_filename = stripinput($_POST['panel_filename']);
        $panel_content = "";
        $panel_type = "file";
    }
    $panel_access = isnum($_POST['panel_access']) ? $_POST['panel_access'] : "0";
    $panel_langs = "";
    for ($pl = 0; $pl < sizeof($_POST['panel_languages']); $pl++) {
        $panel_langs .= $_POST['panel_languages'][$pl].($pl < (sizeof($_POST['panel_languages']) - 1) ? "." : "");
    }
    if ($panel_side == "1" || $panel_side == "4") {
        $panel_display = "0";
    } else {
        $panel_display = isset($_POST['panel_display']) ? "1" : "0";
    }

    if (isset($_GET['panel_id']) && isnum($_GET['panel_id'])) {
        if ($panel_name) {
            $data = dbarray(dbquery("SELECT panel_name FROM ".DB_PANELS." WHERE panel_id='".$_GET['panel_id']."'"));
            if ($panel_name != $data['panel_name']) {
                $result = dbcount("(panel_id)", DB_PANELS, "panel_name='".$panel_name."'");
                if (!empty($result)) {
                    $error .= $locale['471']."<br />";
                }
            }
        }
        if ($panel_type == "php" && $panel_content == "") {
            $error .= $locale['472']."<br />";
        }
        if (($panel_side == "2" || $panel_side == "3") && $panel_display == "0" && $panel_url_list !== "") {
            $error .= $locale['475']."<br />";
        }
        if (!check_admin_pass(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "")) {
            $error .= $locale['474']."<br />";
        }
        if (!$error) {
            $result = dbquery(
                "UPDATE ".DB_PANELS." SET
					panel_name='".$panel_name."', panel_url_list='".$panel_url_list."', panel_restriction='".$panel_restriction."',
					panel_type='".$panel_type."', panel_filename='".$panel_filename."', panel_content='".$panel_content."',
					panel_access='".$panel_access."', panel_display='".$panel_display."', panel_languages='".$panel_langs."'
				WHERE panel_id='".$_GET['panel_id']."'"
            );
        }
        opentable($locale['480']);
        echo "<div style='text-align:center'><br />\n";
        if ($error) {
            echo $locale['481']."<br /><br />\n".$error."<br />\n";
        } else {
            echo $locale['482']."<br /><br />\n";
        }
        echo "<a href='panels.php".$aidlink."'>".$locale['486']."</a><br /><br />\n";
        echo "<a href='index.php".$aidlink."'>".$locale['487']."</a><br /><br />\n";
        echo "</div>\n";
        closetable();
        set_admin_pass(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "");
    } else {
        if ($panel_name) {
            $result = dbcount("(panel_id)", DB_PANELS, "panel_name='".$panel_name."'");
            if (!empty($result)) {
                $error .= $locale['471']."<br />";
            }
        }
        if ($panel_type == "php" && $panel_content == "") {
            $error .= $locale['472']."<br />";
        }
        if ($panel_type == "file" && $panel_filename == "none") {
            $error .= $locale['473']."<br />";
        }
        if (($panel_side == "2" || $panel_side == "3") && $panel_display == "0" && $panel_url_list !== "") {
            $error .= $locale['475']."<br />";
        }
        if (!check_admin_pass(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "")) {
            $error .= $locale['474']."<br />";
        }
        if (!$error) {
            $result = dbquery("SELECT panel_order FROM ".DB_PANELS." WHERE panel_side='".$panel_side."' ORDER BY panel_order DESC LIMIT 1");
            if (dbrows($result) != 0) {
                $data = dbarray($result);
                $neworder = $data['panel_order'] + 1;
            } else {
                $neworder = 1;
            }
            $result = dbquery(
                "INSERT INTO ".DB_PANELS." (
					panel_name, panel_filename, panel_url_list, panel_restriction, panel_content,
					panel_side, panel_order, panel_type, panel_access, panel_display, panel_status, panel_languages
				) VALUES (
					'".$panel_name."', '".$panel_filename."', '".$panel_url_list."', '".$panel_restriction."',
					'".$panel_content."', '".$panel_side."', '".$neworder."', '".$panel_type."', '".$panel_access."',
					'".$panel_display."', '0', '".$panel_langs."'
				)"
            );
        }

        opentable($locale['483']);
        echo "<div style='text-align:center'><br />\n";
        if ($error) {
            echo $locale['484']."<br /><br />\n".$error."<br />\n";
        } else {
            echo $locale['485']."<br /><br />\n";
        }
        echo "<a href='panels.php".$aidlink."'>".$locale['486']."</a><br /><br />\n";
        echo "<a href='index.php".$aidlink."'>".$locale['487']."</a><br /><br />\n";
        echo "</div>\n";
        closetable();
        set_admin_pass(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "");
    }
} else {
    if (isset($_POST['preview'])) {
        $panel_name = stripinput($_POST['panel_name']);
        $panel_url_list = stripinput($_POST['panel_url_list']);
        $exclude_check = $_POST['panel_restriction'] == "1" ? " checked='checked'" : "";
        $include_check = $_POST['panel_restriction'] == "0" ? " checked='checked'" : "";
        $panel_filename = $_POST['panel_filename'];
        $panel_content = isset($_POST['panel_content']) ? stripslash($_POST['panel_content']) : "";
        $panel_access = $_POST['panel_access'];
        $panel_languages = $_POST['panel_languages'];
        $panelon = isset($_POST['panel_display']) ? " checked='checked'" : "";
        $panelopts = $panel_side == "1" || $panel_side == "4" ? " style='display:none'" : " style='display:block'";
        $panel_type = $panel_filename == "none" ? "php" : "file";
        if (check_admin_pass(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "")) {
            opentable($panel_name);
            if ($panel_type == "file") {
                @include INFUSIONS.$panel_filename."/".$panel_filename.".php";
            } else {
                eval($panel_content);
            }
            $panel_content = phpentities($panel_content);
            closetable();
            set_admin_pass(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "");
        } else {
            echo "<div id='close-message'><div class='admin-message'>".$locale['global_182']."</div></div>\n";
            $panel_content = phpentities($panel_content);
        }
    }
    if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['panel_id']) && isnum($_GET['panel_id']))) {
        $result = dbquery(
            "SELECT panel_name, panel_filename, panel_content, panel_type, panel_side,
				panel_access, panel_display, panel_url_list, panel_restriction, panel_languages
			FROM ".DB_PANELS." WHERE panel_id='".$_GET['panel_id']."'"
        );
        if (dbrows($result)) {
            $data = dbarray($result);
            $panel_name = $data['panel_name'];
            $panel_url_list = $data['panel_url_list'];
            $exclude_check = $data['panel_restriction'] == "1" ? " checked='checked'" : "";
            $include_check = $data['panel_restriction'] == "0" ? " checked='checked'" : "";
            $panel_filename = $data['panel_filename'];
            $panel_content = phpentities(stripslashes($data['panel_content']));
            $panel_type = $data['panel_type'];
            $panel_side = $data['panel_side'];
            $panel_access = $data['panel_access'];
            $panel_languages = $data['panel_languages'];
            $panelon = $data['panel_display'] == "1" ? " checked='checked'" : "";
            $panelopts = $panel_side == "1" || $panel_side == "4" ? " style='display:none'" : " style='display:block'";
        } else {
            redirect(FUSION_SELF.$aidlink);
        }
    }
    if (isset($_GET['panel_id']) && isnum($_GET['panel_id'])) {
        $action = FUSION_SELF.$aidlink."&amp;panel_id=".$_GET['panel_id']."&amp;panel_side=".$panel_side;
        opentable($locale['450']);
    } else {
        if (!isset($_POST['preview'])) {
            $panel_name = "";
            $panel_url_list = "";
            $exclude_check = " checked='checked'";
            $include_check = "";
            $panel_filename = "";
            $panel_content = (($panel_side == 1 || $panel_side == 4) ? "openside(\"name\");\n"."echo \"Content\";\n"."closeside();" : "opentable(\"name\");\n"."echo \"Content\";\n"."closetable();");
            $panel_type = "";
            $panel_access = "";
            $panel_languages = "";
            for ($x = 0; $x < sizeof($enabled_languages); $x++) {
                $panel_languages .= $enabled_languages[$x].(($x < sizeof($enabled_languages) - 1) ? "." : "");
            }
            $panelon = "";
            $panelopts = $panel_side == "1" || $panel_side == "4" ? " style='display:none'" : " style='display:block'";
        }
        $action = FUSION_SELF.$aidlink."&amp;panel_side=".$panel_side;
        opentable($locale['451']);
    }
    $user_groups = getusergroups();
    $access_opts = "";
    foreach ($user_groups as $user_group) {
        $sel = (isset($panel_access) && $panel_access == $user_group['0'] ? " selected='selected'" : "");
        $access_opts .= "<option value='".$user_group['0']."'$sel>".$user_group['1']."</option>\n";
    }
    echo "<form name='editform' method='post' action='$action'>\n";
    echo "<table cellpadding='0' cellspacing='0' class='center'>\n<tr>\n";
    echo "<td class='tbl'>".$locale['452']."</td>\n";
    echo "<td colspan='2' class='tbl'><input type='text' name='panel_name' value='$panel_name' class='textbox' style='width:200px;' /></td>\n";
    echo "</tr>\n";
    if (isset($_GET['panel_id']) && isnum($_GET['panel_id'])) {
        if ($panel_type == "file") {
            echo "<tr>\n<td class='tbl'>".$locale['453']."</td>\n";
            echo "<td colspan='2' class='tbl'><select name='panel_filename' class='textbox' style='width:200px;'>\n";
            for ($i = 0; $i < count($panel_list); $i++) {
                echo "<option".($panel_filename == $panel_list[$i] ? " selected='selected'" : "").">".$panel_list[$i]."</option>\n";
            }
            echo "</select></td>\n</tr>\n";
        }
    } else {
        echo "<tr>\n<td class='tbl'>".$locale['453']."</td>\n";
        echo "<td colspan='2' class='tbl'><select name='panel_filename' class='textbox' style='width:200px;'>\n";
        for ($i = 0; $i < count($panel_list); $i++) {
            echo "<option".($panel_filename == $panel_list[$i] ? " selected='selected'" : "").">".$panel_list[$i]."</option>\n";
        }
        echo "</select>&nbsp;&nbsp;<span class='small2'>".$locale['454']."</span></td>\n</tr>\n";
    }
    if (isset($_GET['panel_id']) && isnum($_GET['panel_id'])) {
        if ($panel_type == "php") {
            echo "<tr>\n<td valign='top' class='tbl'>".$locale['455']."</td>\n";
            echo "<td colspan='2' class='tbl'><textarea name='panel_content' cols='95' rows='15' class='textbox' style='width:98%'>".$panel_content."</textarea></td>\n";
            echo "</tr>\n";
        }
    } else {
        echo "<tr>\n<td valign='top' class='tbl'>".$locale['455']."</td>\n";
        echo "<td colspan='2' class='tbl'><textarea name='panel_content' cols='95' rows='15' class='textbox' style='width:98%'>".$panel_content."</textarea></td>\n";
        echo "</tr>\n";
    }
    echo "<tr>\n";
    echo "<td valign='top' class='tbl'>".$locale['462']."<br />\n";
    echo "<span class='small2'><em>".$locale['463']."<br />/news*<br />/news.php<br />/forum/index.php</em></span>";
    echo "</td>\n<td width='200' valign='top' class='tbl'>";
    echo "<textarea name='panel_url_list' cols='50' rows='5' class='textbox' style='width:300px;'>".$panel_url_list."</textarea>";
    echo "</td>\n<td valign='top' class='tbl'>";
    echo "<label><input type='radio' name='panel_restriction' value='1'".$exclude_check." /> ".$locale['464']."</label><br />\n";
    echo "<label><input type='radio' name='panel_restriction' value='0'".$include_check." /> ".$locale['465']."</label><br />\n";
    echo "</td>\n</tr>\n";

    if (!check_admin_pass(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "")) {
        echo "<tr>\n<td class='tbl'>".$locale['456']."</td>\n";
        echo "<td colspan='2' class='tbl'><input type='password' name='admin_password' value='".(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "")."' class='textbox' style='width:150px;' autocomplete='off' /></td>\n";
        echo "</tr>\n";
    }
	if (!isset($_GET['panel_id']) || !isnum($_GET['panel_id'])) {
		echo "<tr>\n<td class='tbl'>".$locale['457']."</td>\n";
		echo "<td class='tbl'><select name='panel_side' class='textbox' style='width:150px;' onchange=\"showopts(this.options[this.selectedIndex].value);\">\n";
		echo "<option value='1'".($panel_side == "1" ? " selected='selected'" : "").">".$locale['420']."</option>\n";
		echo "<option value='2'".($panel_side == "2" ? " selected='selected'" : "").">".$locale['421']."</option>\n";
		echo "<option value='3'".($panel_side == "3" ? " selected='selected'" : "").">".$locale['425']."</option>\n";
		echo "<option value='4'".($panel_side == "4" ? " selected='selected'" : "").">".$locale['422']."</option>\n";
		echo "<option value='4'".($panel_side == "5" ? " selected='selected'" : "").">".$locale['426']."</option>\n";
		echo "<option value='4'".($panel_side == "6" ? " selected='selected'" : "").">".$locale['427']."</option>\n";
		echo "</select></td>\n</tr>\n";
	}
    echo "<tr>\n<td class='tbl'>".$locale['458']."</td>\n";
    echo "<td colspan='2' class='tbl'><select name='panel_access' class='textbox' style='width:150px;'>\n".$access_opts."</select></td>\n";
    echo "</tr>\n";

    echo "<tr>\n";
    echo "<td class='tbl'>".$locale['466']."</td>";
    echo "<td colspan='2' class='tbl'>";

    if (isset($_POST['preview'])) {
        $panel_langs = $panel_languages;
    } else {
        $panel_langs = explode('.', $panel_languages);
    }
    $locale_files = makefilelist(LOCALE, ".|..", TRUE, "folders");
    for ($i = 0; $i < sizeof($locale_files); $i++) {
        if (in_array($locale_files[$i], $enabled_languages)) {
            echo "<input type='checkbox' value='".$locale_files[$i]."' name='panel_languages[]' class='textbox' ".(in_array($locale_files[$i], $panel_langs) ? "checked='checked'" : "")."> ".str_replace('_', ' ', $locale_files[$i])." ";
        }
        if ($i % 4 == 0 && $i != 0)
            echo "<br  />";
    }
    echo "</td></tr>";

    echo "<tr><td align='center' colspan='3' class='tbl'>\n";
    echo "<div id='panelopts'".$panelopts."><br /><input type='checkbox' id='panel_display' name='panel_display' value='1'".$panelon." /> ".$locale['459']."</div>\n";
    echo "<br />\n";
    if (isset($_GET['panel_id']) && isnum($_GET['panel_id'])) {
        if ($panel_type == "php") {
            echo "<input type='hidden' name='panel_filename' value='none' />\n";
        }
    }
    echo "<input type='submit' name='preview' value='".$locale['460']."' class='button' />\n";
    echo "<input type='submit' name='save' value='".$locale['461']."' class='button' /></td>\n";
    echo "</tr>\n</table>\n</form>\n";
    closetable();
}

echo "<script type='text/javascript'>
	function showopts(panelside) {
		var panelopts = document.getElementById('panelopts');
		var paneldisplay = document.getElementById('panel_display');
		if (panelside == 1 || panelside == 4) {
			panelopts.style.display = 'none';
			paneldisplay.checked = false;
		} else {
			panelopts.style.display = 'block';
		}
	}
</script>\n";

require_once THEMES."templates/footer.php";