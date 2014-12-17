<?php
/*--------------------------------------------------------------+
| PHP-Fusion Content Management System 				|
| Copyright © 2002 - 2008 Nick Jones 				|
| http://www.php-fusion.co.uk/ 					|
+---------------------------------------------------------------+
| Author: Joakim Falk (Domi) 					|
+--------------------------------------------------------------*/
if (!defined("IN_FUSION")) { die("Access Denied"); }

// Check if a locale file is available that match the selected locale.
if (file_exists(INFUSIONS."eshop_menu_panel/locale/".LANGUAGE.".php")) {
	// Load the locale file matching selection.
	include INFUSIONS."eshop_menu_panel/locale/".LANGUAGE.".php";
} else {
	// Load the default locale file.
	include INFUSIONS."eshop_menu_panel/locale/English.php";
}

add_to_head("<link rel='stylesheet' type='text/css' href='".THEMES."templates/global/css/eshop.css' />");

openside($locale['ESHPP001']);

if ($settings['eshop_cats'] == "1") {

// Node object
echo "<script type='text/javascript'>
function Node(id, pid, name, url, title, target, icon, iconOpen, open) {
	this.id = id;
	this.pid = pid;
	this.name = name;
	this.url = url;
	this.title = title;
	this.target = target;
	this.icon = icon;
	this.iconOpen = iconOpen;
	this._io = open || false;
	this._is = false;
	this._ls = false;
	this._hc = false;
	this._ai = 0;
	this._p;
}; 
// Tree object

function menu(objName) { 	
this.config = {
}

this.icon = {
		root			: '".INFUSIONS."eshop_menu_panel/menu-images/base.gif',
		folder			: '".INFUSIONS."eshop_menu_panel/menu-images/folder.gif',
		folderOpen	    : '".INFUSIONS."eshop_menu_panel/menu-images/folderopen.gif',
		node			: '".INFUSIONS."eshop_menu_panel/menu-images/page.gif',
		empty			: '".INFUSIONS."eshop_menu_panel/menu-images/empty.gif',
		line			: '".INFUSIONS."eshop_menu_panel/menu-images/line.gif',
		join			: '".INFUSIONS."eshop_menu_panel/menu-images/join.gif',
		joinBottom	    : '".INFUSIONS."eshop_menu_panel/menu-images/joinbottom.gif',
		plus			: '".INFUSIONS."eshop_menu_panel/menu-images/plus.gif',
		plusBottom		: '".INFUSIONS."eshop_menu_panel/menu-images/plusbottom.gif',
		minus			: '".INFUSIONS."eshop_menu_panel/menu-images/minus.gif',
		minusBottom	    : '".INFUSIONS."eshop_menu_panel/menu-images/minusbottom.gif',
		nlPlus			: '".INFUSIONS."eshop_menu_panel/menu-images/nolines_plus.gif',
		nlMinus			: '".INFUSIONS."eshop_menu_panel/menu-images/nolines_minus.gif'

	}; 	

	this.obj = objName;
	this.aNodes = [];
	this.aIndent = [];
	this.root = new Node(-1);
	this.selectedNode = null;
	this.selectedFound = false;
	this.completed = false;
};
</script>";
echo "<table width='100%' cellspacing='1' cellpadding='1' border='0'>";
echo "<tr><td>";

add_to_head("<link rel='stylesheet' type='text/css' href='".INFUSIONS."eshop_menu_panel/eshop_menu_panel.css' />");
echo '<script type="text/javascript" src="'.INFUSIONS.'eshop_menu_panel/menu.js"></script>';

$result = dbquery("SELECT * FROM ".DB_ESHOP_CATS." WHERE ".groupaccess('access')." AND status='1' ORDER BY cat_order ASC");
if (dbrows($result) != 0) {

if ($settings['eshop_icons'] == "1") { $icons = "true"; } else { $icons = "false"; }
if ($settings['eshop_folderlink'] == "1") { $folderlink = "true"; } else { $folderlink = "false"; }
if ($settings['eshop_selection'] == "1") { $selection = "true"; } else { $selection = "false"; }
if ($settings['eshop_cookies'] == "1") { $cookies = "true"; } else { $cookies = "false"; }
if ($settings['eshop_bclines'] == "1") { $bclines = "true"; } else { $bclines = "false"; }
if ($settings['eshop_statustext'] == "1") { $statustext = "true"; } else { $statustext = "false"; }
if ($settings['eshop_closesamelevel'] == "1") { $closesamelevel = "true"; } else { $closesamelevel = "false"; }
if ($settings['eshop_inorder'] == "1") { $inorder = "true"; } else { $inorder = "false"; }

echo '<script type="text/javascript"> 
d = new menu("d");
d.config.target="'.$settings['eshop_target'].'"; 
d.config.folderLinks = '.$folderlink.'; 
d.config.useSelection = '.$selection.'; 
d.config.useCookies = '.$cookies.'; 
d.config.useLines='.$bclines.'; 
d.config.useIcons='.$icons.'; 
d.config.useStatusText='.$statustext.'; 
d.config.closeSameLevel='.$closesamelevel.'; 
d.config.inOrder ='.$inorder.';
d.add(0,-1,"'.$locale['ESHPP003'].'");';

if (multilang_table("ES")) {
		while ($data = dbarray($result)) {
			$ec_langs = explode('.', $data['cat_languages']);
			if (in_array(LANGUAGE, $ec_langs)) {
				$title = trimlink($data['title'],20);
				echo 'd.add('.$data['cid'].','.$data['parentid'].',"'.$title.'","'.($settings['site_seo'] == '1' ? FUSION_ROOT : '').BASEDIR.'eshop.php?category='.$data['cid'].'","'.$data['title'].'","_self");'; 
			}
		}
} else {
while ($data=dbarray($result)) {
	$title = trimlink($data['title'],20);
	echo 'd.add('.$data['cid'].','.$data['parentid'].',"'.$title.'","'.($settings['site_seo'] == '1' ? FUSION_ROOT : '').BASEDIR.'eshop.php?category='.$data['cid'].'","'.$data['title'].'","_self");'; 
 }
}
echo 'document.write(d);
</script>';
} else {
	echo $locale['ESHPF005'];
}

echo "</td></tr></table>";
} else {
add_to_head("<link rel='stylesheet' type='text/css' href='".INFUSIONS."eshop_menu_panel/navigation_panel.css' />");
echo "<script type='text/javascript' src='".INFUSIONS."eshop_menu_panel/jquery.easing.1.3.js'></script>";

echo '<script type="text/javascript">

$(document).ready(function() {
	$(".navigeringv1 .navigeringv1_liste li a").mouseover(function () {
	$(this).css("background-color","#FFFFFF");
	$(this).stop().animate({ paddingLeft: "15px" }, 500 );
});

$(".navigeringv1 .navigeringv1_liste li a").mouseout(function () {
	$(this).css("background-color","#ECEFF5");
	$(this).stop().animate({ paddingLeft: "4px" }, 500 );
 });
});
</script>';

$result = dbquery("SELECT * FROM ".DB_ESHOP." WHERE active = '1' AND ".groupaccess('access')." ORDER BY iorder");
echo "<div class='navigation-header'>".$locale['ESHPP002']."</div>";
while ($data = dbarray($result)) {
echo "<div class='navigeringv1'>
	<ul class='navigeringv1_liste'>
	<li><a href='".BASEDIR."eshop.php?product=".$data['id']."'>".$data['title']."</a></li>
</ul></div>";
 }
}

echo "<br /><center><a class='eshpbutton red' href='".SHOP."campaigns.php' title='".$locale['ESHPF004']."'>".$locale['ESHPF004']."</a></center>";

closeside();
?>