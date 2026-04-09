<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: application.php
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
require_once "../../maincore.php";
require_once THEMES."templates/header.php";

use PHPFusion\Panels;
use PHPFusion\Database\DatabaseFactory;

Panels::getInstance(TRUE)->hide_panel('LEFT');
Panels::getInstance(TRUE)->hide_panel('RIGHT');

\ThemeFactory\Core::setParam('left', FALSE);
\ThemeFactory\Core::setParam('right', FALSE);
\ThemeFactory\Core::setParam('body_container', FALSE);

$req = "<span style='color:#FF0000;'><strong>*</strong></span>\n";

add_to_title("Team Application");
?>
            <div class="container">
                <div class="stripes stripes-info" style="height: 880px; top: 0; grid: repeat(2, 200px)/repeat(4, 1fr)">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <div class="spacer-md">
                    <h1 class="text-white text-center strong m-b-0">PHPFusion Team Application</h1>
                </div>
                <div class="spacer-md"></div>
                    <div class="list-group-item">

<?php

$locale = fusion_get_locale('', STF_LOCALE);

add_to_title(" | ".$locale['stf_002']);
$error = "";
if (iMEMBER) {

    if (isset($_POST['staff_app'])) {

        $stf_real_name = stripinput($_POST['stf_real_name']);
        $stf_main_name = stripinput($_POST['stf_main_name']);
        $stf_main_name_id = stripinput($_POST['stf_main_name_id']);
        $stf_type = stripinput($_POST['stf_type']);
        $stf_text = stripinput($_POST['stf_text']);
        $post_description = "";

        if ($stf_real_name == "") {
            $error .= $locale['stf_012']."<br />\n";
        }
        if ($stf_main_name == "") {
            $error .= $locale['stf_051']."<br />\n";
        }
        //	if ($stf_main_name_id == $userdata['user_id']) { $error .= $locale['stf_052']."<br />\n"; }
        if ($stf_main_name_id == "") {
            $error .= $locale['stf_044']."<br />\n";
        }
        if ($stf_type == '0') {
            $error .= $locale['stf_055']."<br />\n";
        }

        if ($stf_text == "") {
            $error .= $locale['stf_075']."<br />\n";
        }

        if ($error) {
            opentable($locale['stf_012']);
            echo "<div style='text-align:center'><br />\n".$locale['stf_029']."<br /><br />\n$error<br /><br /><a href='".FUSION_SELF."'>".$locale['stf_054']."</a></div><br />\n";
            closetable();
        } else {

            $result = dbquery("INSERT INTO ".DB_STF_APPLICATIONS." (stf_id, stf_user_id, stf_real_name, stf_main_name, stf_main_name_id, stf_email, stf_type, stf_ip, stf_status, stf_admin, stf_text, stf_datestamp, stf_approver_comment) 
    VALUES ('', '".$userdata['user_id']."', '".$stf_real_name."', '".$stf_main_name."', '".$stf_main_name_id."', '".$userdata['user_email']."', '".$stf_type."', '".USER_IP."', '', '', '".$stf_text."', '".time()."', '')");

            $dummy_user = "15756";

            if ($stf_type == '35') {
                $forum_id = "127";
                $thread_subject = $locale['stf_089'];
                $line = $locale['stf_090'];
            } else if ($stf_type == '23') {
                $forum_id = "127";
                $thread_subject = $locale['stf_091'];
                $line = $locale['stf_090'];
            } else {
                $forum_id = "127";
                $thread_subject = $locale['stf_080'];
                $line = $locale['stf_081'];
            }

            $get_user = dbarray(dbquery("SELECT user_name, user_posts, user_joined FROM ".DB_USERS." WHERE user_id = '".$userdata['user_id']."'"));
            $get_group = dbarray(dbquery("SELECT group_name FROM ".DB_USER_GROUPS." WHERE group_id = '".$stf_type."'"));

            $post_description = $locale['stf_074']."[url=http://www.php-fusion.co.uk/profile.php?lookup=".$userdata['user_id']."]".$get_user['user_name']."[/url]".$line;
            $post_description .= "<br /><br />";
            $post_description .= $locale['stf_082']."[url=http://www.php-fusion.co.uk/profile.php?lookup=".$stf_main_name_id."]".$stf_main_name."[/url]";
            $post_description .= "<br /><br />";
            $post_description .= $get_user['user_name'].$locale['stf_083']."[url=http://www.php-fusion.co.uk/profile.php?group_id=".$stf_type."]".$get_group['group_name']."[/url]";
            $post_description .= "<br /><br />";
            $post_description .= $locale['stf_088'].$stf_real_name;
            $post_description .= "<br /><br />";
            $post_description .= $stf_text;
            $post_description .= "<br /><br />";
            $post_description .= $locale['stf_053']." ".$get_user['user_posts'];
            $post_description .= "<br />";
            $post_description .= $locale['stf_043']." ".showdate("shortdate", $get_user['user_joined']);


            $db = DatabaseFactory::getConnection();

            $result = dbquery("INSERT INTO ".DB_FORUM_THREADS." (forum_id, thread_subject, thread_author, thread_views, thread_lastpost, thread_lastpostid, thread_lastuser, thread_postcount, thread_poll) 
	VALUES('".$forum_id."', '".$thread_subject.$stf_real_name."', '".$dummy_user."', '0', '".time()."', '0', '".$dummy_user."', '1', '1')");
            $thread_id = $db->getLastId();
            $result = dbquery("INSERT INTO ".DB_FORUM_POSTS." (forum_id, thread_id, post_message, post_showsig, post_author, post_datestamp, post_ip, post_edituser, post_edittime) 
	VALUES ('".$forum_id."', '$thread_id', '".$post_description."', '0', '".$dummy_user."', '".time()."', '".USER_IP."', '0', '0')");
            $post_id = $db->getLastId();

            $result = dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='".time()."', forum_postcount=forum_postcount+1, forum_threadcount=forum_threadcount+1, forum_lastuser='1' WHERE forum_id='".$forum_id."'");
            $result = dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_lastpostid='".$post_id."' WHERE thread_id='".$thread_id."'");
            $result = dbquery("UPDATE ".DB_USERS." SET user_posts=user_posts+1, user_lastvisit='".time()."' WHERE user_id='".$dummy_user."'");

            $result = dbquery("INSERT INTO ".DB_FORUM_POLLS." (thread_id, forum_poll_title, forum_poll_start, forum_poll_length, forum_poll_votes) 
	VALUES('".$thread_id."', '".$locale['nss_091']."', '".time()."', '0', '0')");
            $result = dbquery("INSERT INTO ".DB_FORUM_POLL_OPTIONS." (thread_id, forum_poll_option_id, forum_poll_option_text, forum_poll_option_votes) 
	VALUES('".$thread_id."', '1', '".$locale['nss_092']."', '0')");
            $result = dbquery("INSERT INTO ".DB_FORUM_POLL_OPTIONS." (thread_id, forum_poll_option_id, forum_poll_option_text, forum_poll_option_votes) 
	VALUES('".$thread_id."', '2', '".$locale['nss_093']."', '0')");

            opentable($locale['stf_031']);
            echo "<div style='text-align:center'><br />\n".$locale['stf_030'].$stf_real_name."<br />\n".$locale['stf_025']."</div><br />\n";
            closetable();
        }

    } else {

        opentable($locale['stf_100']);
        echo "<div><br />\n".$locale['stf_101']."<br /><br /></div>\n";
        closetable();

        opentable($locale['stf_036']);

        echo openform('staff_app', 'post', FUSION_SELF);
        echo '<div style="margin:0 auto;max-width: 500px">';
        echo '<h5 class="text-center">'.$locale['stf_002'].'</h5>';

        echo form_text('stf_real_name', $locale['stf_003']);

        echo form_hidden('stf_main_name', $locale['stf_017'], fusion_get_userdata('user_name'));
        echo form_hidden('stf_main_name_id', '', fusion_get_userdata('user_id'));

        $resulta = dbquery("SELECT group_id, group_name 
            FROM ".DB_USER_GROUPS." 
            WHERE group_id='23' OR group_id='35'
            ORDER BY group_id ASC
        ");

        $opts = [];

        $opts[0] = $locale['stf_016'];

        if (dbrows($resulta)) {
            while ($datab = dbarray($resulta)) {
                if (!in_array($datab['group_id'], explode(".", $userdata['user_groups']))) {
                    $opts[$datab['group_id']] = $datab['group_name'];
               }
            }
        }

        echo form_select('stf_type', $locale['stf_059'], '', [
            'options' => $opts
        ]);

        echo form_textarea('stf_text', $locale['stf_006']);

        echo form_button('staff_app', $locale['stf_056'], $locale['stf_056']);

        /*echo "<table class='center' align='center' width='80%'>\n<tr>\n";
        echo "<th valign='top' colspan='2' class='tbl'>".$locale['stf_002']."</th>\n";
        echo "</tr>\n<tr>\n";
        echo "<td class='tbl' align='right'>".$locale['stf_003'].":</td>\n";
        echo "<td class='tbl'><input type='text' name='stf_real_name' maxlength='30' class='textbox' style='width:300px;' /></td>\n";
        echo "</tr>\n<tr>\n";
        echo "<td class='tbl' align='right'>".$locale['stf_017'].":</td>\n";
        echo "<td class='tbl'><input type='text' name='stf_main_name' maxlength='30' class='textbox' value='".fusion_get_userdata('user_name')."' style='width:210px;' />&nbsp;&nbsp;<input type='text' name='stf_main_name_id' maxlength='30' class='textbox' value='".fusion_get_userdata('user_id')."' style='width:80px;' /></td>\n";
        echo "</tr>\n<tr>\n";
        echo "<td class='tbl1' align='right'>".$locale['stf_059']."</td>\n";
        echo "<td class='tbl1' nowrap valign='top'>";

        $resulta = dbquery("SELECT
                                group_id,
                                group_name
                                FROM ".DB_USER_GROUPS."
                                WHERE group_id ='27' OR group_id ='32' OR group_id ='25' OR group_id = '23'
                                ORDER BY
                                group_id ASC
                                ");

        if (dbrows($resulta)) {
            echo "<select name='stf_type' class='textbox' style='width:300px;'>\n";
            echo "<option value='0'>".$locale['stf_016']."</option>\n";
            while ($datab = dbarray($resulta)) {
                if (!in_array($datab['group_id'], explode(".", $userdata['user_groups']))) {
                    echo "<option value='".$datab['group_id']."'>".$datab['group_name']."</option>\n";
                }
            }
        } else {
            echo "<div align='center'><br />".$locale['stf_063']."</div>\n";
        }

        echo "</select>\n</td>\n";
        echo "</tr>\n<tr>\n";
        echo "<td valign='top' class='tbl' align='right'>".$locale['stf_006'].":</td>\n";
        echo "<td class='tbl'><textarea name='stf_text' class='textbox' cols='60' rows='5' style='width:300px'></textarea>\n</td>\n";
        echo "</tr>\n<tr>\n";
        echo "<td class='tbl1' colspan='2' align='center'><input type='submit' name='staff_app' value='".$locale['stf_056']."' class='button' />\n";
        echo "</tr>\n</table>\n";*/


        echo closeform();

        closetable();

    }
} else {
    opentable($locale['stf_057']);
    echo "<div style='text-align:center'><br /><br />\n".$locale['stf_073']."<br /><br /><img src='".IMAGES."smiley/wink.png' alt='Smile' /><br /><br /></div>\n";
    closetable();
}
echo "</div>";
echo "</div>";
echo "<div class='spacer-md'></div>";
require_once THEMES."templates/footer.php";
