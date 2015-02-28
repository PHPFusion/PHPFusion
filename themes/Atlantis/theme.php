<?php
/*.Atlantis Theme for PHP-Fusion v7..*|
|*.Author: Max "Matonor" Toball......*|
|*.Released under the Affero GPLv3...*/

if (!defined("IN_FUSION")) { die("Access Denied"); }
require_once INCLUDES."theme_functions_include.php";

//Theme Settings
define("THEME_WIDTH", "95%"); //theme width. Make sure to adapt the margin-left in the first div (-width/2).
define("THEME_BULLET", "<span class='bullet'>&middot;</span>"); //bullet image
//Theme Settings /

function get_head_tags(){
	echo "<!--[if lte IE 7]><style type='text/css'>.clearfix {display:inline-block;} * html .clearfix{height: 1px;}</style><![endif]-->";
}

function render_page($license=false) {

global $locale, $aidlink, $main_style, $settings;

echo "<div id='main' style='width: ".THEME_WIDTH."; left: 50%; margin-left: -47.5%;'>
	<div id='header'>
		<div id='userbar'>
			<ul id='anchors' class='flleft'><li><a href='#Content'>".$locale['global_210']."</a></li></ul>
			<ul id='links' class='clearfix flright'>\n";
			if(iMEMBER){
				echo "\t\t\t
				<li><img src='".THEME."images/profile.gif' alt='".$locale['global_120']."' /><a href='".BASEDIR."edit_profile.php'>".$locale['global_120']."</a></li>
				<li><img src='".THEME."images/pm.gif' alt='".$locale['global_121']."' /><a href='".BASEDIR."messages.php'>".$locale['global_121']."</a></li>
				".(iADMIN ? "<li><img src='".THEME."images/admin.gif' alt='".$locale['global_123']."' /><a href='".ADMIN."index.php".$aidlink."' >".$locale['global_123']."</a></li>" : "")."
				<li><img src='".THEME."images/logout.gif' alt='".$locale['global_124']."' /><a href='".BASEDIR."setuser.php?logout=yes'>".$locale['global_124']."</a></li>\n";
			}else{
				echo "\t\t\t<li><img src='".THEME."images/login.gif' alt='".$locale['global_104']."' /><a href='".BASEDIR."login.php'>".$locale['global_104']."</a></li>\n";
			}
			echo "\t\t</ul>
		</div>
		<div id='mainheader' class='clearfix'>
			<img src='".THEME."images/userbar_shadow.jpg' id='userbar_shadow' alt='---------' />
			".showbanners()."
		</div>
		<img src='".THEME."images/subheader_l.jpg' class='flleft' alt='' />
		<img src='".THEME."images/subheader_r.jpg' class='flright' alt='' />
		<div id='subheader' class='clearfix'>".showsublinks(" ")."</div>
	</div>
	<div id='cont' class='clearfix $main_style'>
		<img src='".THEME."images/side_tl.gif' id='side_tl' alt='' />
		<img src='".THEME."images/side_tr.gif' id='side_tr' alt='' />\n
		".(LEFT ? "<div id='side-border-left'>".LEFT."</div>" : "")."
		".(RIGHT ? "<div id='side-border-right'>".RIGHT."</div>" : "")."
		<div id='main-bg'><div id='container'>".
			U_CENTER.
			CONTENT.
			L_CENTER."
		</div></div>
	</div>
	<img src='".THEME."images/side_bl.jpg' class='flleft' alt='' />
	<img src='".THEME."images/side_br.jpg' class='flright' alt='' />
	<div id='cont_b'></div>
	<div id='footer' class='clearfix'>
		".(!$license ? "<div class='flleft' style='width: 50%'>".showcopyright()."<br />\n Theme designed by <a href='http://matonor.com'>Max Toball</a></div>" : "")."
		<div class='flright' style='width: 50%; text-align: right;'>".stripslashes($settings['footer'])."</div>\n";
echo "\t</div>
	<div id='subfooter' class='clearfix'>
		<div class='flleft' style='width: 50%'>".sprintf($locale['global_172'], substr((get_microtime() - START_TIME),0,4))."</div>
		<div class='flright' style='width: 50%; text-align: right;'>".showcounter()."</div>
	</div>
</div>";

}

/* New in v7.02 - render comments */
function render_comments($c_data, $c_info){
	global $locale, $settings;
	opentable($locale['c100']);
	if (!empty($c_data)){
		echo "<div class='comments floatfix'>\n";
			$c_makepagenav = '';
			if ($c_info['c_makepagenav'] !== FALSE) { 
			echo $c_makepagenav = "<div style='text-align:center;margin-bottom:5px;'>".$c_info['c_makepagenav']."</div>\n"; 
		}
			foreach($c_data as $data) {
	        $comm_count = "<a href='".FUSION_REQUEST."#c".$data['comment_id']."' id='c".$data['comment_id']."' name='c".$data['comment_id']."'>#".$data['i']."</a>";
			echo "<div class='tbl2 clearfix floatfix'>\n";
			if ($settings['comments_avatar'] == "1") { echo "<span class='comment-avatar'>".$data['user_avatar']."</span>\n"; }
	        echo "<span style='float:right' class='comment_actions'>".$comm_count."\n</span>\n";
			echo "<span class='comment-name'>".$data['comment_name']."</span>\n<br />\n";
			echo "<span class='small'>".$data['comment_datestamp']."</span>\n";
	if ($data['edit_dell'] !== false) { echo "<br />\n<span class='comment_actions'>".$data['edit_dell']."\n</span>\n"; }
			echo "</div>\n<div class='tbl1 comment_message'>".$data['comment_message']."</div>\n";
		}
		echo $c_makepagenav;
		if ($c_info['admin_link'] !== FALSE) {
			echo "<div style='float:right' class='comment_admin'>".$c_info['admin_link']."</div>\n";
		}
		echo "</div>\n";
	} else {
		echo $locale['c101']."\n";
	}
	closetable();   
}

function render_news($subject, $news, $info) {

global $locale;

opentable($subject);
echo "<div class='floatfix'>".$info['cat_image'].$news."</div>
<div class='news-footer'>
	".newsposter($info," &middot;").newsopts($info,"&middot;").itemoptions("N",$info['news_id'])."
</div>\n";
closetable();

}

function render_article($subject, $article, $info) {

global $locale;
	
opentable($subject);
echo "<div class='floatfix'>".($info['article_breaks'] == "y" ? nl2br($article) : $article)."</div>
<div class='news-footer'>
	".articleposter($info," &middot;").articleopts($info,"&middot;").itemoptions("A",$info['article_id'])."
</div>\n";
closetable();
}

function opentable($title) {

echo "\n<h2 class='panelcap'>$title</h2>\n\t<div class='panelbody'>\n";

}

function closetable() {

echo "</div>\n";

}

$panel_collapse = true;
function openside($title, $collapse = false, $state = "on") {
	
static $box_id = 0; $box_id++;
global $panel_collapse; $panel_collapse = $collapse;

echo "<h3 class='panelcap'>".($collapse ? panelbutton($state,$box_id) : "")."$title</h3>
<div class='panelbody'>\n\t".($collapse ? panelstate($state, $box_id) : "");

}

function closeside() {

global $panel_collapse;

echo ($panel_collapse ? "\t</div>" : "")."\n</div>\n";

}
?>
