<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: eshop_jq_tree_panel.php
| Author: J.Falk (Domi)
+--------------------------------------------------------+
| PHP Example menu with PHP function and jQuery treeview,
| This exmaple is for customizations and do not currently 
| Follow the eshop_settings for menu options.
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined("IN_FUSION")) { die("Access Denied"); }

// Check if a locale file is available that match the selected locale.
if (file_exists(INFUSIONS."eshop_jq_tree_panel/locale/".LANGUAGE.".php")) {
	// Load the locale file matching selection.
	include INFUSIONS."eshop_jq_tree_panel/locale/".LANGUAGE.".php";
} else {
	// Load the default locale file.
	include INFUSIONS."eshop_jq_tree_panel/locale/English.php";
}

add_to_head("<link rel='stylesheet' type='text/css' href='".INFUSIONS."eshop_jq_tree_panel/jquery.treeview.css' />");
add_to_head("<script type='text/javascript' src='".INFUSIONS."eshop_jq_tree_panel/jquery.cookie.js'></script>");
add_to_head("<script type='text/javascript' src='".INFUSIONS."eshop_jq_tree_panel/jquery.treeview.pack.js'></script>");

function createTreeView($array, $currentParent, $currLevel = 0, $prevLevel = -1) {

foreach ($array as $categoryId => $category) {
if ($currentParent == $category['parentid']) {                       
    if ($currLevel > $prevLevel) echo "<ul id='tree'>"; 
    if ($currLevel == $prevLevel) echo "</li>";
    echo '<li><a href="'.BASEDIR.'eshop.php?category='.$categoryId.'">'.$category['name'].'</a>';
    if ($currLevel > $prevLevel) { $prevLevel = $currLevel; }
    $currLevel++; 
    createTreeView ($array, $categoryId, $currLevel, $prevLevel);
    $currLevel--;               
    }   
}
if ($currLevel == $prevLevel) echo "</li></ul>";
}
 
echo '<script type="text/javascript">
		$(function() {
			$("#tree").treeview({
				collapsed: true,
				animated: "medium",
				control:"#sidetreecontrol",
				persist: "cookie"
			});
		})
</script>';

openside($locale['ESHPJQT001']);

$result = dbquery("SELECT * FROM ".DB_ESHOP_CATS." WHERE ".groupaccess('access')." AND status='1' ORDER BY cat_order ASC");

$arrayCategories = array();

while($row = dbarray($result)){ 
	$arrayCategories[$row['cid']] = array("parentid" => $row['parentid'], "name" => $row['title']);   
}

echo "<div id='sidetree'>
<div class='treeheader'>&nbsp;</div>
<div id='sidetreecontrol'><a href='javascript:;'>".$locale['ESHPJQT002']."</a> | <a href='javascript:;'>".$locale['ESHPJQT003']."</a></div>";
createTreeView($arrayCategories, 0);
echo "</div>";

closeside();
