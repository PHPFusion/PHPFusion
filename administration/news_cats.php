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
include LOCALE.LOCALESET."admin/news.php";
if (isset($_GET['status']) && !isset($message)) {
	if ($_GET['status'] == "sn") {
		$message = $locale['news_0150'];
	} elseif ($_GET['status'] == "su") {
		$message = $locale['news_0151'];
	} elseif ($_GET['status'] == "dn") {
		$message = $locale['news_0152']."<br />\n<span class='small'>".$locale['news_0153']."</span>";
	} elseif ($_GET['status'] == "dy") {
		$message = $locale['news_0154'];
	}
	if ($message) {
		echo "<div id='close-message'><div class='alert alert-info m-t-10 admin-message'>".$message."</div></div>\n";
	}
}
if ((isset($_GET['action']) && $_GET['action'] == "delete") && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) {
	$result = dbcount("(news_cat)", DB_NEWS, "news_cat='".$_GET['cat_id']."'") || dbcount("(news_cat_id)", DB_NEWS_CATS, "news_cat_parent='".$_GET['cat_id']."'");
	if (!empty($result)) {
		redirect(FUSION_SELF.$aidlink."&status=dn");
	} else {
		$result = dbquery("DELETE FROM ".DB_NEWS_CATS." WHERE news_cat_id='".$_GET['cat_id']."'");
		redirect(FUSION_SELF.$aidlink."&status=dy");
	}
} elseif (isset($_POST['save_cat'])) {
	$cat_name = form_sanitizer($_POST['cat_name'], '', 'cat_name');
	$cat_parent = isnum($_POST['cat_parent']) ? $_POST['cat_parent'] : "0";
	$cat_image = stripinput($_POST['cat_image']);
	$cat_language = stripinput($_POST['cat_language']);
	if ($cat_name && $cat_image && !defined('FUSION_NULL')) {
		if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) {
			$result = dbquery("UPDATE ".DB_NEWS_CATS." SET news_cat_name='$cat_name', news_cat_parent='$cat_parent', news_cat_image='$cat_image', news_cat_language='$cat_language' WHERE news_cat_id='".$_GET['cat_id']."'");
			redirect(FUSION_SELF.$aidlink."&status=su");
		} else {
			$checkCat = dbcount("(news_cat_id)", DB_NEWS_CATS, "news_cat_name='".$cat_name."'");
			if ($checkCat == 0) {
				$result = dbquery("INSERT INTO ".DB_NEWS_CATS." (news_cat_name, news_cat_parent, news_cat_image, news_cat_language) VALUES ('$cat_name', '$cat_parent', '$cat_image', '$cat_language')");
				redirect(FUSION_SELF.$aidlink."&status=sn");
			} else {
				$error = 2;
				$defender->stop();
				$defender->addNotice($locale['news_0352']);
				$formaction = FUSION_SELF.$aidlink;
				$openTable = $locale['news_0022'];
			}
		}
	} else {
		$error = 1;
		$defender->stop();
		$formaction = FUSION_SELF.$aidlink;
		$openTable = $locale['news_0022'];
	}
} elseif ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) {
	$result = dbquery("SELECT news_cat_id, news_cat_name, news_cat_parent, news_cat_image, news_cat_language FROM ".DB_NEWS_CATS." ".(multilang_table("NS") ? "WHERE news_cat_language='".LANGUAGE."' AND" : "WHERE")." news_cat_id='".$_GET['cat_id']."'");
	if (dbrows($result)) {
		$data = dbarray($result);
		$cat_name = $data['news_cat_name'];
		$cat_hidden = array($data['news_cat_id']);
		$cat_parent = $data['news_cat_parent'];
		$cat_image = $data['news_cat_image'];
		$cat_language = $data['news_cat_language'];
		$formaction = FUSION_SELF.$aidlink."&amp;action=edit&amp;cat_id=".$data['news_cat_id'];
		$openTable = $locale['news_0021'];
	} else {
		redirect(FUSION_SELF.$aidlink);
	}
} else {
	$cat_name = "";
	$cat_hidden = array();
	$cat_parent = 0;
	$cat_image = "";
	$cat_language = LANGUAGE;
	$formaction = FUSION_SELF.$aidlink;
	$openTable = $locale['news_0022'];
}
$image_files = makefilelist(IMAGES_NC, ".|..|index.php", TRUE);
$image_list = array();
foreach ($image_files as $image) {
	$image_list[$image] = $image;
}

opentable($openTable);
echo openform('addcat', 'post', $formaction, array('max_tokens' => 1));
echo "<table cellpadding='0' cellspacing='0' class='table table-responsive center'>\n<tr>\n";
echo "<td width='130' class='tbl'><label for='cat_name'>".$locale['news_0300']."</label></td>\n";
echo "<td class='tbl'>\n";
echo form_text('cat_name', '', $cat_name, array('required' => 1, 'error_text' => $locale['news_0351']));
echo "</td>\n</tr>\n";
echo "<tr><td width='130' class='tbl'><label for='cat_image'>".$locale['news_0305']."</label></td>\n";
echo "<td class='tbl'>\n";
echo form_select_tree("", "cat_parent", "cat_parent", $cat_parent, array("disable_opts" => $cat_hidden, "hide_disabled" => 1), DB_NEWS_CATS, "news_cat_name", "news_cat_id", "news_cat_parent");
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
echo "<tr><td width='130' class='tbl'><label for='cat_image'>".$locale['news_0301']."</label></td>\n";
echo "<td class='tbl'>\n";
echo form_select('', 'cat_image', 'cat_image', $image_list, $cat_image, array('placeholder' => $locale['choose']));
echo "</td>\n</tr>\n<tr>\n";
echo "<td align='center' colspan='2' class='tbl'><br />\n";
echo form_button('save_cat', $locale['news_0302'], $locale['news_0302'], array('class' => 'btn-primary'));
echo "</td>\n</tr>\n</table>\n</form>\n";
closetable();

opentable($locale['news_0020']);
$result = dbquery("SELECT news_cat_id, news_cat_name FROM ".DB_NEWS_CATS." ".(multilang_table("NS") ? "WHERE news_cat_language='".LANGUAGE."'" : "")." ORDER BY news_cat_name");
$rows = dbrows($result);
if ($rows != 0) {
	$counter = 0;
	$columns = 4;
	echo "<div class='row'>\n";
	while ($data = dbarray($result)) {
		if ($counter != 0 && ($counter%$columns == 0)) echo "</div>\n<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3 text-left'>\n";
		echo "<strong>".getNewsCatPath($data['news_cat_id'])."</strong>\n<br/>\n";
		echo "<img src='".get_image("nc_".$data['news_cat_name'])."' alt='".$data['news_cat_name']."' class='news-category img-thumbnail m-r-20' />\n<br /><br />\n";
		echo "<div class='block-inline' style='width:100%;'><span class='small'><a href='".FUSION_SELF.$aidlink."&amp;action=edit&amp;cat_id=".$data['news_cat_id']."'>".$locale['edit']."</a> -\n";
		echo "<a href='".FUSION_SELF.$aidlink."&amp;action=delete&amp;cat_id=".$data['news_cat_id']."' onclick=\"return confirm('".$locale['news_0350']."');\">".$locale['delete']."</a></span></div>\n";
		echo "</div>\n";
		$counter++;
	}
	echo "</div>\n";
} else {
	echo "<div style='text-align:center'><br />\n".$locale['news_0303']."<br /><br />\n</div>\n";
}
echo "<div style='text-align:center'><br />\n<a class='btn btn-primary' href='".ADMIN."images.php".$aidlink."&amp;ifolder=imagesnc'>".$locale['news_0304']."</a><br /><br />\n</div>\n";
closetable();

function getNewsCatPath($item_id) {
	$full_path = "";
	while ($item_id > 0) {
		$result = dbquery("SELECT news_cat_id, news_cat_name, news_cat_parent FROM ".DB_NEWS_CATS." WHERE news_cat_id='$item_id'".(multilang_table("NS") ? " AND news_cat_language='".LANGUAGE."'" : ""));
		if (dbrows($result)) {
			$data = dbarray($result);
			if ($full_path) { $full_path = " / ".$full_path; }
			$full_path = $data['news_cat_name'].$full_path;
			$item_id = $data['news_cat_parent'];
		}
	}
	return $full_path;
}

require_once THEMES."templates/footer.php";
?>
