<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| http://phpfusion.com
+--------------------------------------------------------+
| Filename: crv_report.php
| Author: PHPFusion Addons Team
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
include INFUSIONS."crv_report/inc/includes.php";

use PHPFusion\Panels;
use PHPFusion\Database\DatabaseFactory;

Panels::getInstance(TRUE)->hide_panel('LEFT');
Panels::getInstance(TRUE)->hide_panel('RIGHT');

\ThemeFactory\Core::setParam('left', FALSE);
\ThemeFactory\Core::setParam('right', FALSE);
\ThemeFactory\Core::setParam('body_container', FALSE);


?>
<div class="legal">
    <div class="header">
        <div class="container">
            <div class="stripes stripes-info" style="height: 880px; top: 0px; grid: repeat(2, 200px)/repeat(4, 1fr)">
                <span></span>
                <span></span>
                <span></span>
                <span></span>
            </div>
            <div class="spacer-md">
                <h1 class="text-white text-center strong m-b-0">PHPFusion Copyright Violation Report Tool</h1>
            </div>
            <div class="spacer-md">
                <div class="list-group-item">
                    <br/>
<div class="theme-content ">

<?php

add_to_title($locale['crv_001']);
$error = "";

if (iMEMBER) {

if (isset($_POST['crv_rep'])) {

	$crv_url = stripinput(trim(preg_replace("/ +/i", " ", $_POST['crv_url'])));
	$crv_text = stripinput(trim($_POST['crv_text']));
	$post_description = "";

	

if (!preg_match('#^http(s)?://#', $crv_url)) {
   $crv_url = 'http://' . $crv_url;
}
$urlParts = parse_url($crv_url);
$search = preg_replace('/^www\./', '', $urlParts['host']);
$result = dbquery("SELECT * FROM ".DB_CRL." WHERE crl_domain = '".$search."' LIMIT 0,1");	

if (!dbrows($result)) {

                                                    
	
	if ($_POST['crv_url'] == "") {
		$error .= " <span class='alt'>".$locale['crv_012']."<br />".$locale['crv_020']."</span><br />\n";
	}
	
	if (!iMEMBER) {
	$_CAPTCHA_IS_VALID = false;
	include INCLUDES."captchas/".$settings['captcha']."/captcha_check.php";
	if ($_CAPTCHA_IS_VALID == false) {
		$error .= " <span class='alt'>".$locale['crv_016']."</span><br />\n";
	   }
	}
	
	 if ($error != "") {
  opentable($locale['crv_012']);
		echo "<div style='text-align:center'><br />\n".$locale['crv_018']."<br /><br />\n$error<br />
		<center><a href='javascript:history.back(1)'>".$locale['crv_004']."</a></center></div><br />\n";
		closetable();
	} else {
	
	if (!strstr($crv_url, "http://") && !strstr($crv_url, "https://")) {
			$urlprefix = "http://";
		} else {
			$urlprefix = "";
		}
              
	$post_description .= ("".$locale['crv_008']."<br />");
	$post_description .= ("[url=".$urlprefix.$crv_url."]".$crv_url."[/url]</b><br /><br />");
	$post_description .= $locale['crv_006']."<br /><br />".$crv_text."<br />";
	
	$db = DatabaseFactory::getConnection();
	
	$result = dbquery("INSERT INTO ".DB_FORUM_THREADS." (forum_id, thread_subject, thread_author, thread_views, thread_lastpost, thread_lastpostid, thread_lastuser, thread_postcount) 
	VALUES('".$forum_id."', '".$locale['crv_009']."', '".$autobot."', '0', '".time()."', '0', '".$autobot."', '1')");
	$thread_id = $db->getLastId();
	$result = dbquery("INSERT INTO ".DB_FORUM_POSTS." (forum_id, thread_id, post_message, post_showsig, post_author, post_datestamp, post_ip, post_edituser, post_edittime) 
	VALUES ('".$forum_id."', '$thread_id', '".$post_description."', '0', '".$autobot."', '".time()."', '".USER_IP."', '0', '0')");
	$post_id = $db->getLastId();
	$result = dbquery("UPDATE ".DB_FORUMS." SET forum_lastpost='".time()."', forum_postcount=forum_postcount+1, forum_threadcount=forum_threadcount+1, forum_lastuser='1' WHERE forum_id='".$forum_id."'");
	$result = dbquery("UPDATE ".DB_FORUM_THREADS." SET thread_lastpostid='".$post_id."' WHERE thread_id='".$thread_id."'");
	$result = dbquery("UPDATE ".DB_USERS." SET user_posts=user_posts+1 WHERE user_id='11'"); 
	
	  opentable($locale['crv_010']);
	  echo "<div style='text-align:center'><br />\n".$locale['crv_013']."</div><br />\n";
	  closetable();
  }
} else {
opentable("Valid Domain");
echo "<div class='well text-center'><br />\n The Domain you reported own a valid Copyright Removal License and will not be registered as a license violator. <br />Thank you kindly for taking your time and reporting Copyright violations to us. <br /></div>\n";
closetable();
}
 }  



if (!isset($_POST['crv_rep'])) {

opentable($locale['crv_001']);
echo "<div><br />\n".$locale['crv_021']."<br />\n<br /></div>\n";
closetable();

opentable($locale['crv_002']);

	echo openform('crv_rep', 'post', FUSION_SELF);
	echo "<table class='tbl-border' align='center' width='80%'>\n<tr>\n";
	echo "<td class='tbl'>".$locale['crv_003']."</td>\n";
	echo "<td class='tbl'><input type='text' name='crv_url' maxlength='100' class='textbox' style='width:300px;' /></td>\n";
	echo "<td width='1%' class='tbl1'>".$req."</td>\n";
	echo "</tr>\n<tr>\n";
  
	echo "<td valign='top' class='tbl'>".$locale['crv_006']."<br /><span class='small'><i>".$locale['crv_007']."</i></span></td>\n";
	echo "<td class='tbl'><textarea name='crv_text' cols='60' rows='5' style='width:300px' class='textbox'></textarea>\n</td>\n";
	echo "<td width='1%' class='tbl1'>&nbsp;</td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td class='tbl1 p-20' colspan='2' align='center'><input type='submit' name='crv_rep' value='".$locale['crv_014']."' class='btn btn-primary' />\n";
	echo "</tr>\n</table>\n";
	closeform();

closetable();

   }

} else {
	echo "<div class='spacer-md'></div>";
	echo "<div class='well text-center'>Please Login before submitting Reports</div>";
	echo "<div class='spacer-lg'></div>";
}
echo "</div></div></div>";

require_once THEMES."templates/footer.php";