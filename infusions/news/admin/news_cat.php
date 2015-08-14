<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: news_cats_admin.php
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
pageAccess("NC");

/**
 * Delete category images
 */
if ((isset($_GET['action']) && $_GET['action'] == "delete") && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) {
	$result = dbcount("(news_cat)", DB_NEWS, "news_cat='".$_GET['cat_id']."'") ||
			  dbcount("(news_cat_id)", DB_NEWS_CATS, "news_cat_parent='".$_GET['cat_id']."'");
	if (!empty($result)) {
		addNotice("success", $locale['news_0152'].$locale['news_0153']);
	} else {
		$result = dbquery("DELETE FROM ".DB_NEWS_CATS." WHERE news_cat_id='".$_GET['cat_id']."'");
		addNotice("success", $locale['news_0154']);
	}
	// FUSION_REQUEST without the "action" gets
	redirect(clean_request("", array("action"), false));
}

$data = array(
	"news_cat_id" => 0,
	"news_cat_name" => "",
	"news_cat_hidden" => array(),
	"news_cat_parent" => 0,
	"news_cat_image" => "",
	"news_cat_language" => LANGUAGE,
);

$formAction = FUSION_REQUEST;
$formTitle = $locale['news_0022'];

// if edit, override $data
if (isset($_POST['save_cat'])) {
	$inputArray = array(
		"news_cat_id" => form_sanitizer($_POST['news_cat_id'], "", "news_cat_id"),
		"news_cat_name" =>	form_sanitizer($_POST['news_cat_name'], "", "news_cat_name"),
		"news_cat_parent" =>	form_sanitizer($_POST['news_cat_parent'], 0, "news_cat_parent"),
		"news_cat_image" =>	form_sanitizer($_POST['news_cat_image'], "", "news_cat_image"),
		"news_cat_language" =>	form_sanitizer($_POST['news_cat_language'], LANGUAGE, "news_cat_language"),
	);

	$categoryNameCheck = array(
		"when_updating" => "news_cat_name='".$inputArray['news_cat_name']."' and news_cat_id !='".$inputArray['news_cat_id']."'",
		"when_saving" => "news_cat_name='".$inputArray['news_cat_name']."'",
	);

	if (defender::safe()) {
		// check category name is unique when updating
		if (dbcount("(news_cat_id)", DB_NEWS_CATS, "news_cat_id='".$inputArray['news_cat_id']."'")) {
			if (!dbcount("(news_cat_id)", DB_NEWS_CATS, $categoryNameCheck['when_updating'])) {
				dbquery_insert(DB_NEWS_CATS, $inputArray, "update");
				addNotice("success", $locale['news_0151']);
				// FUSION_REQUEST without the "action" gets
				redirect(clean_request("", array("action"), false));
			} else {
				addNotice('error', $locale['news_0352']);
			}
		} else {
			// check category name is unique when saving new
			if (!dbcount("(news_cat_id)", DB_NEWS_CATS, $categoryNameCheck['when_saving'])) {
				dbquery_insert(DB_NEWS_CATS, $inputArray, "save");
				addNotice("success", $locale['news_0150']);
				redirect(FUSION_REQUEST);
			} else {
				addNotice('error', $locale['news_0352']);
			}
		}
	}
}
elseif ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) {
	$result = dbquery("SELECT news_cat_id, news_cat_name, news_cat_parent, news_cat_image, news_cat_language FROM ".DB_NEWS_CATS." ".(multilang_table("NS") ? "WHERE news_cat_language='".LANGUAGE."' AND" : "WHERE")." news_cat_id='".$_GET['cat_id']."'");
	if (dbrows($result)) {
		$data = dbarray($result);
		$data['news_cat_hidden'] = array($data['news_cat_id']);
		$formTitle = $locale['news_0021'];
	} else {
		// FUSION_REQUEST without the "action" gets
		redirect(clean_request("", array("action"),false));
	}
}

add_breadcrumb(array('link'=>"", 'title'=>$formTitle));
opentable($formTitle);
echo openform("addcat", "post", $formAction);
openside("");
echo form_hidden("news_cat_id", "", $data['news_cat_id']);
echo form_text("news_cat_name", $locale['news_0300'], $data['news_cat_name'],
			   array(
				   "required" => true, "inline"=>true, "error_text" => $locale['news_0351']
			   )
);
echo form_select_tree("news_cat_parent", $locale['news_0305'], $data['news_cat_parent'],
					  array(
						  "inline"=>true,
						  "disable_opts" => $data['news_cat_hidden'],
						  "hide_disabled" => true,
						  "query" => (multilang_table("NS") ? "WHERE news_cat_language='".LANGUAGE."'" : "")
					  ),
					  DB_NEWS_CATS, "news_cat_name", "news_cat_id", "news_cat_parent");
if (multilang_table("NS")) {
	echo form_select("news_cat_language", $locale['global_ML100'], $data['news_cat_language'],
					 array(
						 "inline" => true,
						 "options" => fusion_get_enabled_languages(),
						 "placeholder" => $locale['choose']
					 )
	);
} else {
	echo form_hidden("news_cat_language", "", $data['news_cat_language']);
}
echo form_select("news_cat_image", $locale['news_0301'], $data['news_cat_image'],
				 array(
					 "inline"=>true,
					 "options" => newsCatImageOpts(),
				 )
);
echo form_button("save_cat", $locale['news_0302'], $locale['news_0302'], array("class" => "btn-success"));
closeside();


openside($locale['news_0020']);
$result = dbquery("SELECT news_cat_id, news_cat_name FROM ".DB_NEWS_CATS." ".(multilang_table("NS") ? "WHERE news_cat_language='".LANGUAGE."'" : "")." ORDER BY news_cat_name");
$rows = dbrows($result);
if ($rows != 0) {
	$counter = 0;
	$columns = 4;
	echo "<div class='row'>\n";
	while ($data = dbarray($result)) {
		if ($counter != 0 && ($counter%$columns == 0)) echo "</div>\n<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3 m-b-10 text-left'>\n";
		echo "<strong>".getNewsCatPath($data['news_cat_id'])."</strong>\n<br/>\n";
		echo "<img src='".get_image("nc_".$data['news_cat_name'])."' alt='".$data['news_cat_name']."' class='news-category img-thumbnail m-r-20' />\n";
		echo "<div class='display-block m-t-5'>\n";
		echo "<span class='small'><a href='".clean_request("action=edit&cat_id=".$data['news_cat_id'], array("aid", "section"), true)."'><i class='fa fa-edit'></i> ".$locale['edit']."</a> -\n";
		echo "<a href='".clean_request("action=delete&cat_id=".$data['news_cat_id'], array("aid", "section"), true)."' onclick=\"return confirm('".$locale['news_0350']."');\"><i class='fa fa-trash'></i> ".$locale['delete']."</a></span></div>\n";
		echo "</div>\n";
		$counter++;
	}
	echo "</div>\n";
} else {
	echo "<div class='well text-center'>".$locale['news_0303']."</div>\n";
}
echo "<div class='text-center'><a class='btn btn-primary' href='".ADMIN."images.php".$aidlink."&amp;ifolder=imagesnc'>".$locale['news_0304']."</a><br /><br />\n</div>\n";
closeside();
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
function newsCatImageOpts()
{
	$image_files = makefilelist(IMAGES_NC, ".|..|index.php", TRUE);
	$image_list = array();
	foreach ($image_files as $image) {
		$image_list[$image] = $image;
	}
	return $image_list;
}

require_once THEMES."templates/footer.php";
