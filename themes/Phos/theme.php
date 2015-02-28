<?php
/*.phos Theme for PHP-Fusion v7......*|
|*.Author: Max "Matonor" Toball......*|
|*.Released under the Affero GPLv3...*/

//Theme Settings
define("THEME_WIDTH", "95%"); //theme width. Make sure to adapt the margin-left in the first div (-width/2).
define("THEME_BULLET", "<span class='bullet'>&middot;</span>"); //bullet image
$enable_colour_switcher = true; //true=enable colour switcher | false=disable colour switcher
$enable_fontsize_switcher = true; //true=enable fontsize switcher | false=disable fontsize switcher
$enable_column_switcher = true; //true=enable column switcher | false=disable column switcher
//Theme Settings /

if (!defined("IN_FUSION")) { die("Access Denied"); }
require_once INCLUDES."theme_functions_include.php";
require_once THEMES."templates/switcher.php";

$colour_switcher = new Switcher("select", "colour", "gif", "blue", "switcherbutton");
if(!$enable_colour_switcher){
	$colour_switcher->disable();
}

$column_switcher = new Switcher("select", "columns", "gif", "both", "switcherbutton");
if(!$enable_column_switcher){
	$column_switcher->disable();
}

$fontsize_switcher = new Switcher("increment", "fontsize", "gif", 1, "switcherbutton", "", true, array("step" => 0.1, "max" => 1.5));
if(!$enable_fontsize_switcher){
	$fontsize_switcher->disable();
}

redirect_img_dir(THEME."forum", THEME."forum/".$colour_switcher->selected);
set_image("pollbar", THEME."images/panelcap_bg.jpg");

function get_head_tags(){
	global $colour_switcher, $fontsize_switcher, $column_switcher;
	echo $colour_switcher->makeHeadTag();
	echo $column_switcher->makeHeadTag();
	echo "<style type='text/css'>body{font-size: ".$fontsize_switcher->selected."em;}</style>";
	echo "<!--[if lte IE 7]><style type='text/css'>.clearfix {display:inline-block;} * html .clearfix{height: 1px;}#subheader ul {display:inline-block;}#subheader ul {display:inline;}#subheader ul li {float:left;} #main-bg, .panelbody{position: relative;}</style><![endif]-->";
}

function render_page($license=false) {

global $aidlink, $locale, $settings, $colour_switcher, $fontsize_switcher, $column_switcher, $main_style;


	echo "<div id='main' style='width: ".THEME_WIDTH."; left: 50%; margin-left: -47.5%;'>
		<div id='header_t'><div id='header_r'><div id='header_l'><div id='corner_tr'><div id='corner_tl'>
			<div id='header'>
			<div id='header_bg' class='clearfix'>
				<div id='userbar' class='floatfix'>
					<ul id='anchors' class='flleft'><li><a href='#content'>".$locale['global_210']."</a></li></ul>
					<ul id='links' class='clearfix flright'>\n";
					if(iMEMBER){
						echo "\t\t\t\t\t\t\t<li><a href='".BASEDIR."edit_profile.php'>".$locale['global_120']."</a> </li>
						<li> | <a href='".BASEDIR."messages.php'>".$locale['global_121']."</a></li>
						".(iADMIN ? "<li> | <a href='".ADMIN."index.php".$aidlink."' >".$locale['global_123']."</a></li>" : "")."
						<li> | <a href='".BASEDIR."setuser.php?logout=yes'>".$locale['global_124']."</a></li>\n";
					}else{
						echo "\t\t\t\t\t\t\t<li><a href='".BASEDIR."login.php'>".$locale['global_104']."</a></li>
						".($settings['enable_registration'] ? "<li> | <a href='".BASEDIR."register.php'>".$locale['global_107']."</a></li>\n" : "");
					}
					echo "\t\t\t\t\t\t</ul>
				</div>
				<div id='mainheader' class='clearfix'>".showbanners()."</div>
				<div id='subheader' class='flleft'>".
				preg_replace("^(li)( class='(first-link)')*(><a href='(\.\./)*".preg_quote(START_PAGE)."')^i", "\\1 class='active \\3'\\4", showsublinks(""))."</div>
				<div id='tab' class='flright'>
					<div id='tab_r' class='clearfix'>
						".$colour_switcher->makeForm("flright")." 
						".$column_switcher->makeForm("flright")."
						".$fontsize_switcher->makeForm("flright")."
					</div>
				</div>
			</div>
			</div>
		</div></div></div></div></div>
		<div id='cont_b'><div id='cont_r'><div id='cont_l'><div id='corner_br'><div id='corner_bl'>
		<div id='cont' class='clearfix $main_style'>
			".(LEFT ? "<div id='side-border-left'>".LEFT."</div>" : "")."
			".(RIGHT ? "<div id='side-border-right'>".RIGHT."</div>" : "")."
			<div id='main-bg'><div id='container'>".
				U_CENTER.
				CONTENT.
				L_CENTER."
			</div></div>
		</div>
		</div></div></div></div></div>
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
	".newsposter($info," &middot;").newsopts($info,"&middot;").itemoptions("N",$info['news_id']).
"</div>\n";
closetable();

}

function render_article($subject, $article, $info) {

global $locale;
	
opentable($subject);
echo "<div class='floatfix'>".($info['article_breaks'] == "y" ? nl2br($article) : $article)."</div>
<div class='news-footer'>
	".articleposter($info," &middot;").articleopts($info,"&middot;").itemoptions("A",$info['article_id']).
"</div>\n";
closetable();
}

function opentable($title) {

echo "\n<div class='panel'><h2 class='panelcap'><span><span>$title</span></span></h2>
	<div class='panelbody'><div class='panel_r'><div class='panel_l'>\n";

}

function closetable() {

echo "</div></div></div></div>\n";

}

$panel_collapse = true;
function openside($title, $collapse = false, $state = "on") {
	
static $box_id = 0; $box_id++;
global $panel_collapse, $p_data; $panel_collapse = $collapse;

if($p_data['panel_filename'] == "css_navigation_panel"){
	$panel_collapse = false;
	echo "<div class='panelbody navigation'><div class='navcap_r'><div class='navcap_l'><div class='panel_r'><div class='panel_l'>";
}else{
	echo "<h3 class='panelcap'><span><span>".($collapse ? panelbutton($state,$box_id) : "")."$title</span></span></h3>
<div class='panelbody'><div class='panel_r'><div class='panel_l'>\n\t".($collapse ? panelstate($state, $box_id) : "");
}
}

function closeside() {

global $panel_collapse, $p_data;

echo ($panel_collapse ? "\t</div>" : "")."\n</div></div></div>\n";

if($p_data['panel_filename'] == "css_navigation_panel"){
	echo "</div></div>";
}

}
?>
