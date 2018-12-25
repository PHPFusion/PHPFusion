<?php
/*---------------------------------------------------------+
| PHP-Fusion Content Management System                     |
| Copyright (C) 2002 - 2010 Nick Jones                     |
| http://www.php-fusion.co.uk/                             |
+----------------------------------------------------------+
| Arise Theme Copyright (C) 2011 Joakim Falk (Falk)        |
+----------------------------------------------------------+
|                               /T /I/ /  /   /            |
|                              / |/ | .-~/.-~/		   |
|                          T\ Y  I  |/  /  _/		   |
|         /T               | \I  |  I  Y.-~/ 	           |
|        I l   /I       T\ |  |  l  |  T  /		   |
|     T\ |  \ Y l  /T   | \I  l   \ `  l Y		   |
| __  | \l   \l  \I l __l  l   \   `  _. |		   |
| \ ~-l  `\   `\  \  \ ~\  \   `. .-~   < 		   |
|  \   ~-. "-.  `  \  ^._ ^. "-.  /  \   |                 |
|.--~-._  ~-  `  _  ~-_.-"-." ._ /._ ." ./		   |
| >--.  ~-.   ._  ~>-"    "\   7   7   ]                   | 
|^.___~"--._    ~-{  .-~ .  `\ Y . /    |		   |
| <__ ~"-.  ~       /_/   \   \I  Y   : |		   |
|   ^-.__           ~(_/   \   >._:   | l______		   |
|       ^--.,___.-~"  /_/   !  `-.~"--l_ /     ~"-.	   |
|              (_/ .  ~(   /'     "~"--,Y    -=O- _)	   |
|               (_/ .  \  :           / l       "   \	   |
|                \ /    `.    .     .^   \_.-~"~--.  )     |
|                 (_/ .   `  /     /       !       )/      |
|                  / / _.   '.   .':      /        '	   |
|                  ~(_/ .   /    _  `  .-<_		   |
|                    /_/ . ' .-~" `.  / \  \          ,´=. |
|                    ~( /   '  :   | X   "-.~-.______//    |
|                      "-,.    l   I/ \_    __{--->._(==.  |
|                       //(     \  <    ~"~"     //        |
|                      /' /\     \  \     ,~=.  ((         |
|                    .^. / /\     "  }__ //===-  `	   |
|                   / / ' '  "-.,__ {---(==-		   |
|                 .^ '       :  ;  ~"   ll       	   |
|                / .  .  . : | :!        \		   |
|               (_/  /   | | j-"          ~^		   |
|                 ~-<_(_.^-~"				   |
+----------------------------------------------------------+
| Arise Theme Copyright (C) 2011 Joakim Falk (Domi)        |
+----------------------------------------------------------+
| This program is released as free software under the 	   |
| Affero GPL license. You can redistribute it and/or 	   |
| modify it under the terms of this license which you      |
| can read by viewing the included agpl.txt or online      |
| at www.gnu.org/licenses/agpl.html. Removal of this       |
| copyright header is strictly prohibited without  	   |
| written permission from the original author(s).	   |
+---------------------------------------------------------*/
if (!defined("IN_FUSION")) { die("Access Denied"); }
require_once INCLUDES."theme_functions_include.php";
require_once THEME."functions.php";

define("THEME_BULLET", "");
define("THEME_WIDTH", "1000px;");


define('BOOTSTRAP', TRUE);
define('ENTYPO', TRUE);

// Uncomment to enable/disable styles

// Disable Load Default CCS
// define('NO_DEFAULT_CSS', TRUE);

// Disable Load Global CCS
// define('NO_GLOBAL_CSS', TRUE);

// Enable Fontawesome
// define('FONTAWESOME', TRUE);


function render_page($license = false) {
	global $settings, $main_style, $locale;

	//Wrapper
	echo "<div style='width:".THEME_WIDTH."; margin: 0px auto;'>";
	echo "<div style='width:".THEME_WIDTH."; margin: 0px auto;' id='header' class='header'>";

	//Header
	echo "<table cellpadding='0' cellspacing='0' width='100%'><tr>\n";
	echo "<td style='width:80%;' align='center'>\n";

	//Site Banner
	echo "<div style='float:left;padding-bottom:10px;'>".showbanners()."</div>";


	//Header links
	if (HEADERLINKS) {
	echo "<div style='float:left;margin: 0px auto;margin-left:2px;padding:8px;'>";
	echo "<a href='".BASEDIR."downloads.php'><img src='".THEME."headerimgs/downloads.png' height='60' width='60' alt='Downloads' title='Downloads' style='vertical-align:middle;' /><br /> Downloads </a>";
	echo "</div>";

	echo "<div style='float:left;margin: 0px auto;padding:8px;'>";
	echo "<a href='".BASEDIR."articles.php'><img src='".THEME."headerimgs/articles.png' height='60' width='60' alt='Articles' title='Articles' style='vertical-align:middle;' /><br /> Articles </a>";
	echo "</div>";

	echo "<div style='float:left;margin: 0px auto;padding:8px;'>";
	echo "<a href='".BASEDIR."photogallery.php'><img src='".THEME."headerimgs/photogallery.png' height='60' width='60' alt='Photogallery' title='Photogallery' style='vertical-align:middle;' /><br /> Photogallery </a>";
	echo "</div>";

	echo "<div style='float:left;margin: 0px auto;padding:8px;'>";
	echo "<a href='".BASEDIR."faq.php'><img src='".THEME."headerimgs/faq.png' height='60' width='60' alt='FaQ' title='FaQ' style='vertical-align:middle;' /><br /> FaQ </a>";
	echo "</div>";

	echo "<div style='float:left;margin: 0px auto;padding:8px;'>";
	echo "<a href='".BASEDIR."/forum/index.php'><img src='".THEME."headerimgs/forum.png' height='60' width='60' alt='Forum' title='Forum' style='vertical-align:middle;' /><br /> Forum </a>";
	echo "</div>";

	echo "<div style='float:left;margin: 0px auto;padding:8px;'>";
	echo "<a href='".BASEDIR."contact.php'><img src='".THEME."headerimgs/contact.png' height='60' width='60' alt='Contact' title='Contact' style='vertical-align:middle;' /><br /> Contact </a>";
	echo "</div>";
	echo "<div class='clear'></div>";
	echo "</td>\n<td style='width:20%;'>\n";
	}

	if (HSDESCRIPTION) {
	//Right header section with sitename and site description
	echo "<div style='float:right;margin-top:2px;'>";
	echo "<br /><h4>".$settings['sitename']."</h4>
	<span class='dtext1'>".$settings['description']."</span></div>";
	}
   
	echo "</td>\n</tr>\n</table>\n";
	echo "<div class='clear'></div>";
	
	//Search bar (Courtesy iTheme II)
	$locale['search'] = str_replace($locale['global_200'], "", $locale['global_202']);
	echo "<form action='".BASEDIR."search.php' id='searchform' method='get'><input type='text' class='textbox' onblur='if (this.value == \"\") {this.value = \"".$locale['search']."...\";}' onfocus='if (this.value == \"".$locale['search']."...\") {this.value = \"\";}' id='stext' name='stext' value='".$locale['search']."...' /></form>\n";

	echo "<div id='nav-bar'>";
	echo navigation();
	echo "</div>\n";
	echo "</div><div class='clear'></div>";

	if ($main_style == "") {
		$colspan = "";
	} elseif ($main_style == "side-both") {
		$colspan = "colspan='3'";
	} else {
		$colspan = "colspan='2'";
	}
	
	//Content
	echo "<table cellpadding='0' cellspacing='0' width='".THEME_WIDTH."' class='$main_style'>\n";
	echo "AU_CENTER." ? "<tr><td class='main-bg' ".$colspan." valign='top'>".AU_CENTER."</td>\n</tr>\n<tr>\n" : "<tr>\n";
	if (LEFT) { echo "<td class='side-border-left' valign='top'>".LEFT."</td>"; }
	echo "<td class='main-bg' valign='top'>".U_CENTER.CONTENT.L_CENTER."</td>";
	if (RIGHT) { echo "<td class='side-border-right' valign='top'>".RIGHT."</td>"; }
	echo "BL_CENTER." ? "</tr>\n<tr><td class='main-bg' ".$colspan." valign='top'>".BL_CENTER."</td>\n</tr>\n<tr>\n" : "";
	echo "</tr>\n</table>\n";
	
	//Footer
	echo "<table cellpadding='0' cellspacing='0' width='100%' class='main-footer'>\n<tr>\n";
	echo "<td align='left' valign='top' width='20%'>&nbsp;Arise Theme by <a href='http://www.venue.nu' target='_blank' title='Venue'>Domi</a> 2011</td>\n";
	if ($settings['rendertime_enabled'] == 1 || ($settings['rendertime_enabled'] == 2 && iADMIN)) {
	echo "<td align='center' valign='top' width='65%'><center>".showrendertime()." - ".showMemoryUsage()."</center></td>\n";
	}
	echo "<td align='right' valign='top' width='15%'>".showcounter()."&nbsp;</td>\n";
	echo "</tr><tr>";
        echo "<td align='center' colspan='3' valign='top' width='100%'><br /><br />".showcopyright()."</td>\n";
	echo "</tr>\n</table></div>\n";
	}


function render_comments($c_data, $c_info){
	global $locale, $settings;

	echo "<table cellpadding='0' cellspacing='0' width='100%'>\n<tr>\n";
	echo "<td class='box box-caption'>".$locale['c100']."</td>\n";
	echo "</tr>\n</table>\n";
	echo "<table width='100%' cellpadding='0' cellspacing='0' class='spacer'>\n<tr>\n";
	echo "<td class='main-body'>";
	if (!empty($c_data)){
		echo "<div class='comments floatfix'>\n";
			$c_makepagenav = '';
			if ($c_info['c_makepagenav'] !== FALSE) { 
			echo $c_makepagenav = "<div style='text-align:center;margin-bottom:5px;'>".$c_info['c_makepagenav']."</div>\n"; 
		}
			foreach($c_data as $data) {
		        $comm_count = "<a href='".PERMALINK_CURRENT_PATH."#c".$data['comment_id']."' id='c".$data['comment_id']."' name='c".$data['comment_id']."'>#".$data['i']."</a>";
			echo "<div class='comment-tbl clearfix floatfix'>\n";
			if ($settings['comments_avatar'] == "1") { echo "<span class='comment-avatar'>".$data['user_avatar']."</span>\n"; }
		    echo "<span class='comment-actions'>".$comm_count."\n</span>\n";
			echo "<span class='comment-name'>".$data['comment_name']."</span>\n<br /><br />\n";
			echo "<span class='small'>".$data['comment_datestamp']."</span>\n";
	if ($data['edit_dell'] !== false) { echo "<br />\n<span class='comment-admin-top'>".$data['edit_dell']."\n</span>\n"; }
			echo "</div>\n<div class='comment-message'>".$data['comment_message']."</div>\n";
		}
		echo $c_makepagenav;
		if ($c_info['admin_link'] !== FALSE) {
			echo "<div style='float:right' class='comment-admin'>".$c_info['admin_link']."</div>\n";
		}
		echo "</div>\n";
	} else {
		echo $locale['c101']."\n";
	}
		echo "</td>\n</tr>\n";
		echo "<tr><td style='height:2px;background-color:#e87a0b;'></td>\n";
		echo "</tr>\n</table>\n";
}


	

function render_news($subject, $news, $info) {
global $settings;

	echo "<table cellpadding='0' cellspacing='0' width='100%'>\n<tr>\n";
	echo "<td class='box box-caption' onclick=\"location='".BASEDIR."news.php?readmore=".$info['news_id']."'\" style='cursor:pointer;'>".$subject."</td>\n";
	echo "</tr>\n</table>\n";
	echo "<table width='100%' cellpadding='0' cellspacing='0' class='spacer'>\n<tr>\n";
	echo "<td class='main-body'><div style='float:left;width:100%;'>".$info['cat_image'].$news."</div><div class='clear'></div><div style='float:right;font-size:9px;'>".newsposter($info)."</div></td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td align='center' class='news-footer'>\n";
	echo "<div style='float:left;margin-top:3px;'>";
	echo newsopts($info," &middot;" ).itemoptions("N",$info['news_id']);
	echo " &middot; </div>";
	echo "</td>\n";
	echo "</tr>\n";
	echo "<tr><td style='height:2px;background-color:#ff0024;'></td>\n";
	echo "</tr>\n</table>\n";

}

function render_article($subject, $article, $info) {
global $settings;
	
	echo "<table width='100%' cellpadding='0' cellspacing='0'>\n<tr>\n";
	echo "<td class='box box-caption'>".$subject."</td>\n";
	echo "</tr>\n</table>\n";
	echo "<table width='100%' cellpadding='0' cellspacing='0' class='spacer'>\n<tr>\n";
	echo "<td class='main-body'><div style='float:left;width:100%;'>".($info['article_breaks'] == "y" ? nl2br($article) : $article)."</div><div class='clear'></div><div style='float:right;font-size:9px;'>".articleposter($info)."</div></td>\n";
	echo "</tr>\n<tr>\n";
	echo "<td align='center' class='news-footer'>\n";
	echo "<div style='float:left;margin-top:3px;'>";
	echo articleopts($info," &middot; ").itemoptions("A",$info['article_id']);
	echo " &middot; </div>";
	echo "</td>\n</tr>\n";
	echo "<tr><td style='height:2px;background-color:#00bbc3;'></td>\n";
	echo "</tr>\n</table>\n";
}


function opentable($title) {
	echo "<table cellpadding='0' cellspacing='0' width='100%'>\n<tr>\n";
	echo "<td class='box box-caption'>".$title."</td>\n";
	echo "</tr>\n</table>\n";
	echo "<table cellpadding='0' cellspacing='0' width='100%'>\n<tr>\n";
	echo "<td class='main-body'>\n";
}

function closetable() {
	echo "</td>\n";
	echo "</tr>\n";
	echo "</table>\n";
	echo "<table cellpadding='0' cellspacing='0' width='100%'>\n<tr>\n";
	echo "<td>\n";
	echo "<div class='box-footer'></div>\n";
	echo "</td>\n";
	echo "</tr>\n";
	echo "</table>\n";
}

function openside($title, $collapse = false, $state = "on") {
	global $panel_collapse; $panel_collapse = $collapse;
	echo "<table cellpadding='0' cellspacing='0' width='100%'>\n<tr>\n";
	echo "<td class='box box-caption'><center>".$title."</center></td>\n";
	if ($collapse == true) {
	$boxname = str_replace(" ", "", $title);
	echo "<td class='box box-caption' align='right'>".panelbutton($state, $boxname)."</td>\n";
	}
	echo "</tr>\n</table>\n";
	echo "<table cellpadding='0' cellspacing='0' width='100%'>\n<tr>\n";
	echo "<td class='side-body'>\n";	
	if ($collapse == true) { echo panelstate($state, $boxname); }
}

function closeside() {
	global $panel_collapse;
	if ($panel_collapse == true) { echo "</div>\n"; }	
	echo "</td>\n</tr>\n</table>";
	echo "<div class='box-footer'></div>\n";
}
?>