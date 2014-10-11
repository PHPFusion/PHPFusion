<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: news_cats.php
| Author: Nick Jones (Digitanium)
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
if (!checkRights("NC") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) {
	redirect("../index.php");
}
require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/news-cats.php";
if (isset($_GET['status']) && !isset($message)) {
	if ($_GET['status'] == "sn") {
		$message = $locale['420'];
	} elseif ($_GET['status'] == "su") {
		$message = $locale['421'];
	} elseif ($_GET['status'] == "dn") {
		$message = $locale['422']."<br />\n<span class='small'>".$locale['423']."</span>";
	} elseif ($_GET['status'] == "dy") {
		$message = $locale['424'];
	}
	if ($message) {
		echo "<div id='close-message'><div class='alert alert-info m-t-10 admin-message'>".$message."</div></div>\n";
	}
}
if ((isset($_GET['action']) && $_GET['action'] == "delete") && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) {
	$result = dbcount("(news_cat)", DB_NEWS, "news_cat='".$_GET['cat_id']."'");
	if (!empty($result)) {
		redirect(FUSION_SELF.$aidlink."&status=dn");
	} else {
		$result = dbquery("DELETE FROM ".DB_NEWS_CATS." WHERE news_cat_id='".$_GET['cat_id']."'");
		redirect(FUSION_SELF.$aidlink."&status=dy");
	}
} elseif (isset($_POST['save_cat'])) {
	$cat_name = form_sanitizer($_POST['cat_name'], '', 'cat_name');
	$cat_image = stripinput($_POST['cat_image']);
	$cat_language = stripinput($_POST['cat_language']);
	if ($cat_name && $cat_image && !defined('FUSION_NULL')) {
		if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) {
			$result = dbquery("UPDATE ".DB_NEWS_CATS." SET news_cat_name='$cat_name', news_cat_image='$cat_image', news_cat_language='$cat_language' WHERE news_cat_id='".$_GET['cat_id']."'");
			redirect(FUSION_SELF.$aidlink."&status=su");
		} else {
			$checkCat = dbcount("(news_cat_id)", DB_NEWS_CATS, "news_cat_name='".$cat_name."'");
			if ($checkCat == 0) {
				$result = dbquery("INSERT INTO ".DB_NEWS_CATS." (news_cat_name, news_cat_image, news_cat_language) VALUES ('$cat_name', '$cat_image', '$cat_language')");
				redirect(FUSION_SELF.$aidlink."&status=sn");
			} else {
				$error = 2;
				$defender->stop();
				$defender->addNotice($locale['461']);
				$formaction = FUSION_SELF.$aidlink;
				$openTable = $locale['401'];
			}
		}
	} else {
		$error = 1;
		$defender->stop();
		$formaction = FUSION_SELF.$aidlink;
		$openTable = $locale['401'];
	}
} elseif ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) {
	$result = dbquery("SELECT news_cat_id, news_cat_name, news_cat_image, news_cat_language FROM ".DB_NEWS_CATS." ".(multilang_table("NS") ? "WHERE news_cat_language='".LANGUAGE."' AND" : "WHERE")." news_cat_id='".$_GET['cat_id']."'");
	if (dbrows($result)) {
		$data = dbarray($result);
		$cat_name = $data['news_cat_name'];
		$cat_image = $data['news_cat_image'];
		$cat_language = $data['news_cat_language'];
		$formaction = FUSION_SELF.$aidlink."&amp;action=edit&amp;cat_id=".$data['news_cat_id'];
		$openTable = $locale['400'];
	} else {
		redirect(FUSION_SELF.$aidlink);
	}
} else {
	$cat_name = "";
	$cat_image = "";
	$cat_language = LANGUAGE;
	$formaction = FUSION_SELF.$aidlink;
	$openTable = $locale['401'];
}
$image_files = makefilelist(IMAGES_NC, ".|..|index.php", TRUE);
$image_list = array();
foreach($image_files as $image) {
	$image_list[$image] = $image;
}

opentable($openTable);
echo openform('addcat', 'addcat', 'post', $formaction, array('downtime' => 0));
echo "<table cellpadding='0' cellspacing='0' class='table table-responsive center'>\n<tr>\n";
echo "<td width='130' class='tbl'><label for='cat_name'>".$locale['430']."</label></td>\n";
echo "<td class='tbl'>\n";
echo form_text('', 'cat_name', 'cat_name', $cat_name, array('required' => 1, 'error_text' => $locale['460']));
echo "</td>\n</tr>\n";
if (multilang_table("NS")) {
	echo "<tr><td class='tbl'><label for='cat_language'>".$locale['global_ML100']."</label></td>\n";
	$opts = get_available_languages_list($selected_language = "$cat_language");
	echo "<td class='tbl'>\n";
	echo form_select('', 'cat_language', 'cat_language', $language_opts, $cat_language, array('placeholder' => $locale['choose']));
	echo "</td>\n</tr>\n";
} else {
	echo form_hidden('', 'cat_language', 'cat_language', $cat_language);
}
echo "<tr><td width='130' class='tbl'><label for='cat_image'>".$locale['431']."</label></td>\n";
echo "<td class='tbl'>\n";
echo form_select('', 'cat_image', 'cat_image', $image_list, $cat_image, array('placeholder' => $locale['choose']));
echo "</td>\n</tr>\n<tr>\n";
echo "<td align='center' colspan='2' class='tbl'><br />\n";
echo form_button($locale['432'], 'save_cat', 'save_cat', $locale['432'], array('class' => 'btn-primary'));
echo "</td>\n</tr>\n</table>\n</form>\n";
closetable();
opentable($locale['402']);
$result = dbquery("SELECT news_cat_id, news_cat_name FROM ".DB_NEWS_CATS." ".(multilang_table("NS") ? "WHERE news_cat_language='".LANGUAGE."'" : "")." ORDER BY news_cat_name");
$rows = dbrows($result);
if ($rows != 0) {
	$counter = 0;
	$columns = 4;
	echo "<div class='row'>\n";
	while ($data = dbarray($result)) {
		if ($counter != 0 && ($counter%$columns == 0)) echo "</div>\n<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3 text-left'>\n";
		echo "<strong>".$data['news_cat_name']."</strong>\n<br/>\n";
		echo "<img src='".get_image("nc_".$data['news_cat_name'])."' alt='".$data['news_cat_name']."' class='news-category img-thumbnail m-r-20' />\n<br /><br />\n";
		echo "<div class='block-inline' style='width:100%;'><span class='small'><a href='".FUSION_SELF.$aidlink."&amp;action=edit&amp;cat_id=".$data['news_cat_id']."'>".$locale['433']."</a> -\n";
		echo "<a href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;cat_id=".$data['news_cat_id']."' onclick=\"return confirm('".$locale['450']."');\">".$locale['434']."</a></span></div>\n";
		echo "</div>\n";
		$counter++;
	}
	echo "</div>\n";
} else {
	echo "<div style='text-align:center'><br />\n".$locale['435']."<br /><br />\n</div>\n";
}
echo "<div style='text-align:center'><br />\n<a class='btn btn-primary' href='".ADMIN."images.php".$aidlink."&amp;ifolder=imagesnc'>".$locale['436']."</a><br /><br />\n</div>\n";
closetable();
require_once THEMES."templates/footer.php";
?>
