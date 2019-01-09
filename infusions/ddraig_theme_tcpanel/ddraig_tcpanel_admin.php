<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: ddraig_tcpanel_admin.php
| Author: JoiNNN
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once "../../maincore.php";
if (!checkrights("DDCP") || !defined("iAUTH") || $_GET['aid'] != iAUTH) {
    redirect("../../index.php");
}

require_once THEMES."templates/admin_header.php";

if (file_exists(INFUSIONS."ddraig_theme_tcpanel/locale/".LANGUAGE.".php")) {
    include INFUSIONS."ddraig_theme_tcpanel/locale/".LANGUAGE.".php";
} else {
    include INFUSIONS."ddraig_theme_tcpanel/locale/English.php";
}

include INFUSIONS."ddraig_theme_tcpanel/infusion_db.php";

//Select options
$select_opt = [
    1 => ["desc"  => $locale['enabled'],
          "color" => "green"],
    0 => ["desc"  => $locale['disabled'],
          "color" => "red"]
];

/////////////////////////
// Saving settings
/////////////////////////
if (isset($_POST['save_settings'])) {
    //Check and get all inputs values
    function check_input($name, $values = "", $default = "") {
        $res = "";
        //For inputs with predefined values
        if (isset($name) && $values != "" && $default != "") {
            if (isset($_POST[$name]) && isnum($_POST[$name]) && array_key_exists($_POST[$name], $values)) {
                $res = stripinput($_POST[$name]);
            } else {
                $res = $default;
            }
        } else if (isset($name)) {
            //For inputs with no predefined values
            if (isset($_POST[$name]) && isnum($_POST[$name])) {
                $res = stripinput($_POST[$name]);
            } else {
                $res = "0";
            }
        }
        return $res;
    }

    $theme_minwidth = check_input('theme_minwidth');
    $theme_maxwidth = check_input('theme_maxwidth');
    $home_icon = check_input('home_icon', $select_opt, '1');
    $winter_mode = check_input('winter_mode', $select_opt, '0');
    $theme_maxwidth_forum = "0";
    //If the checkbox is checked get width from input field
    if (isset($_POST['cbox_theme_maxwidth_forum']) && isnum($_POST['theme_maxwidth_forum'])) {
        $theme_maxwidth_forum = stripinput($_POST['theme_maxwidth_forum']);
    }
    //theme_maxwidth_forum should not be lower than MinWidth
    if (isset($_POST['cbox_theme_maxwidth_forum']) && $theme_maxwidth_forum < $theme_minwidth) {
        $theme_maxwidth_forum = $theme_minwidth;
    }

    $theme_maxwidth_admin = "0";
    //If the checkbox is checked get width from input field
    if (isset($_POST['cbox_theme_maxwidth_admin']) && isnum($_POST['theme_maxwidth_admin'])) {
        $theme_maxwidth_admin = stripinput($_POST['theme_maxwidth_admin']);
    }
    //theme_maxwidth_admin should not be lower than MinWidth
    if (isset($_POST['cbox_theme_maxwidth_admin']) && $theme_maxwidth_admin < $theme_minwidth) {
        $theme_maxwidth_admin = $theme_minwidth;
    }

    //Check if any width field is empty
    if (empty($theme_maxwidth) || empty($theme_minwidth)) {
        $err_mess = $locale['invalid'];
        //MaxWidth should not be lower than MinWidth
    } else if ($theme_maxwidth < $theme_minwidth) {
        $err_mess = $locale['maxwidth_low'];
        //If all is good, update settings
    } else {
        $result = dbquery("UPDATE ".DB_DDRAIGTCP." SET 
                        theme_maxwidth			= '$theme_maxwidth',
                        theme_minwidth			= '$theme_minwidth',
                        theme_maxwidth_admin	= '$theme_maxwidth_admin',
                        theme_maxwidth_forum	= '$theme_maxwidth_forum',
                        home_icon 				= '$home_icon',
                        winter_mode				= '$winter_mode'
                        ");
        redirect(FUSION_SELF.$aidlink."&amp;status=su"); //Settings updated, redirect
    }
}
/////////////////////////
// Get settings from DB
/////////////////////////
$theme_settings = dbquery("SELECT * FROM ".DB_DDRAIGTCP);
$setting = dbarray($theme_settings);

//Render input function
function render_input($val = "", $type = "", $values = "", $maxlen = "2", $default = "") {
    global $setting, $locale;
    $res = "";
    //Text inputs
    if ($type == "input") {
        $res = "<input name='$val' id='$val' value='".$setting[$val]."' size='10' type='text' maxlength='$maxlen' class='textbox input' />";
        //Text inputs with checkbox
    } else if ($type == "cboxinput") {
        $checked = "checked='checked'";
        $disabled = "";
        if ($setting[$val] == 0) {
            $checked = "";
            $disabled = "disabled='disabled'";
            $setting[$val] = $default;
        }
        $res = "<input type='checkbox' name='cbox_".$val."' id='cbox_".$val."' ".$checked." value='0' /> ";
        $res .= "<input name='$val' id='$val' value='".$setting[$val]."' ".$disabled." size='10' type='text' maxlength='$maxlen' class='textbox cboxinput' />";
        //Select inputs
    } else if ($type == "select") {
        $res = "<select class='textbox select' name='$val' id='$val'>";
        foreach ($values as $key => $value) {
            $selected = "";
            if ($setting[$val] == $key) {
                $selected = "selected='selected'";
            }
            $res .= "<option style='color:".$value['color']."' value='$key' ".$selected.">".$value['desc']."</option>";
        }
        $res .= "</select>";
        if ($setting[$val] == 0) {
            $res .= " <img src='".IMAGES."no.png' width='16' height='16' alt='".$locale['disabled']."' />";
        } else {
            $res .= " <img src='".IMAGES."yes.png' width='16' height='16' alt='".$locale['enabled']."' />";
        }
    }
    return $res;
}

////////////////////////
// Theme settings
////////////////////////
opentable($locale['tcp_title']);
echo "<form name='save_settings' method='post' action='".FUSION_SELF.$aidlink."'>
            <table class='settings center' width='100%' cellspacing='0'> 
            <tbody>
            <tr><th class='tbl2 forum-caption' colspan='4'><h3>".$locale['g_sets']."</h3></th></tr>";

//Theme Max Width
echo "<tr>
            <td class='desc'><label for='theme_maxwidth'><b>".$locale['max_w']."</b></label>
                <p class='small'>".$locale['max_w_des']."</p>
            </td>
            <td class='inputs'>
            ".render_input('theme_maxwidth', 'input', '', '4')." px
            </td>
      </tr>";
echo "<tr><td colspan='2'><hr /></td></tr>";

//Theme Min Width
echo "<tr>
            <td class='desc'><label for='theme_minwidth'><b>".$locale['min_w']."</b></label>
                <p class='small'>".$locale['min_w_des']."</p>
            </td>
            <td class='inputs'>
            ".render_input('theme_minwidth', 'input', '', '4')." px
            </td>
      </tr>";
echo "<tr><td colspan='2'><hr /></td></tr>";

//Theme Max Width in Forum
echo "<tr>
        <td class='desc'><label for='theme_maxwidth_forum'><b>".$locale['max_wf']."</b></label>
            <p class='small'>".$locale['max_wf_des']."</p>
        </td>
        <td class='inputs'>
        ".render_input('theme_maxwidth_forum', 'cboxinput', '', '4', $setting['theme_maxwidth'])." px
        </td>
      </tr>";
echo "<tr><td colspan='2'><hr /></td></tr>";

//Theme Max Width in Administration
echo "<tr>
        <td class='desc'><label for='theme_maxwidth_admin'><b>".$locale['max_wa']."</b></label>
            <p class='small'>".$locale['max_wa_des']."</p>
        </td>
        <td class='inputs'>
        ".render_input('theme_maxwidth_admin', 'cboxinput', '', '4', $setting['theme_maxwidth'])." px
        </td>
      </tr>";
echo "<tr><td colspan='2'><hr /></td></tr>";

//Home Icon
echo "<tr>
        <td class='desc'><label for='home_icon'><b>".$locale['home_icon']."</b></label>
            <p class='small'>".$locale['home_icon_des']."</p>
        </td>
        <td class='inputs'>
        ".render_input('home_icon', 'select', $select_opt)."
        </td>				
      </tr>";
echo "<tr><td colspan='2'><hr /></td></tr>";

//Winter Mode
echo "<tr>
        <td class='desc'><label for='winter_mode'><b>".$locale['winter']."</b></label>
            <p class='small'>".$locale['winter_des']."</p>
        </td>
        <td class='inputs'>
        ".render_input('winter_mode', 'select', $select_opt)."
        </td>				
      </tr>";
echo "<tr><td colspan='2'><hr /></td></tr>";

//Save Button
echo "<tr>";
echo "<td colspan='3' align='center'><br /><input type='submit' name='save_settings' value='".$locale['save_sets']."' class='button' /></td>";
echo "</tr>";

echo "</tbody>";
echo "</table>";
echo "</form>";

add_to_footer("<script type='text/javascript'>
/* <![CDATA[ */
jQuery(document).ready(function() {
    $('.inputs select').change(function () {
        var color = $('option:selected', this).attr('style');
        $(this).attr('style', color);
    });

    $('.inputs select').each(function () {
        var color = $('option[selected=selected]', this).attr('style');
        $(this).attr('style', color);
    });
    
    $('input[type=checkbox]').click(function() {
    if (this.checked) {
        $(this).next('input').removeAttr('disabled');
      } else {
        $(this).next('input').attr('disabled', 'disabled');
      }
    });
});
/* ]]>*/
</script>");

//Status messages
if (isset($_GET['status']) && $_GET['status'] == "su") {
    $message = $locale['sets_up'];
    replace_in_output("<!--error_handler-->", "<!--error_handler--><div id=\'close-message\'><div class=\'admin-message\'>".$message."</div></div>");
}

//If any error message is set show it
if (isset($err_mess)) {
    replace_in_output("<!--error_handler-->", "<!--error_handler--><div class=\'admin-message\'>".$err_mess."</div>");
};

closeside();

require_once THEMES."templates/footer.php";
