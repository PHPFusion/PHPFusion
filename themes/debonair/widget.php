<?php
/**
 * Debonair Widget Console.
 * There must be two tabs.
 * First tab is to control widget
 * Second tab is to control theme settings
 */
//include THEMES."debonair/locale/".LOCALESET."/locale.php"; // this works
include "locale/".LOCALESET."locale.php"; // this works too (same as above)

$tab['title'] = array($locale['debonair_0200'], $locale['debonair_0201']);
$tab['id'] = array("banner", "tsettings");
$tab_active = tab_active($tab, 0);
echo opentab($tab, $tab_active, "debonair_widget");
echo opentabbody($tab['title'][0], $tab['id'][0], $tab_active);
debonair_banner_widget();
echo closetabbody();
echo opentabbody($tab['title'][1], $tab['id'][1], $tab_active);
debonair_theme_widget();
echo closetabbody();
echo closetab();


function debonair_banner_widget() {
	global $locale;
	$acceptedMode = array("edit", "new", "del");
	echo "<a class='btn btn-default m-t-10 m-b-20' href='".clean_request("slides=new", array(), false)."'>".$locale['debonair_0203']."</a>\n";
	echo "<div class='alert alert-info'>".$locale['debonair_0700']."</div>\n";
	if (isset($_GET['slides']) && (in_array($_GET['slides'], $acceptedMode))) {
		$_GET['id'] = isset($_GET['id']) && isnum($_GET['id']) ? $_GET['id'] : 0;
		$data = array();
		$db_keys = fieldgenerator(DB_DEBONAIR);
		foreach($db_keys as $keys) {
			$value = "";
			if ($keys == "banner_id") $value = 0;
			if ($keys == "banner_language") $value = LANGUAGE;
			if ($keys == "banner_visibility") $value = iGUEST;
			if ($keys == "banner_datestamp") $value = time();
			if ($keys == "banner_order") $value = dbcount("(banner_id)", DB_DEBONAIR, "banner_language='".LANGUAGE."'")+1;
			$data[$keys] = $value;
		}
		if ($_GET['slides'] == "edit" || $_GET['slides'] == "del") {
			$data = dbarray(dbquery("select * from ".DB_DEBONAIR." WHERE banner_id='".intval($_GET['id'])."'"));
			if ($_GET['slides'] == "del" && !empty($data)) {
				// process deletion
				if ($data['banner_image']) {
					unlink(THEME."upload/".$data['banner_image']);
					unlink(THEME."upload/".$data['banner_thumb']);
				}
				dbquery_insert(DB_DEBONAIR, $data, "delete");
				addNotice("success", $locale['debonair_0217']);
				redirect(clean_request("", array("slides"), false));
			}
		}

		if (isset($_POST['save_slide']))
		{
			$data = array(
				"banner_id" => form_sanitizer($_POST['banner_id'], 0, "banner_id"),
				"banner_subject" => form_sanitizer($_POST['banner_subject'], "", "banner_subject"),
				"banner_description" => form_sanitizer($_POST['banner_description'], "", "banner_description"),
				"banner_link" => form_sanitizer($_POST['banner_link'], "", "banner_link"),
				"banner_language" => form_sanitizer($_POST['banner_language'], LANGUAGE, "banner_language"),
				"banner_visibility" => form_sanitizer($_POST['banner_visibility'], iGUEST, "banner_visibility"),
				"banner_datestamp" => time(),
			);
			if (defender::safe()) {
				$upload = form_sanitizer($_FILES['banner_image'], "", "banner_image");
				if (isset($upload['error']) && !$upload['error']) {
					$data['banner_image'] = $upload['image_name'];
					$data['banner_thumb'] = $upload['thumb1'];
				}
				if ($data['banner_id'] > 0  && dbcount("(banner_id)", DB_DEBONAIR, "banner_id='".$data['banner_id']."'")) {
					// get old data. do ordering
					$old_data = dbquery("select banner_image, banner_thumb, banner_order from ".DB_DEBONAIR." where banner_id='".$data['banner_id']."'");
					if ($old_data['banner_image']) {
						unlink(THEME."upload/".$old_data['banner_image']);
						unlink(THEME."upload/".$old_data['banner_thumb']);
					}
					dbquery_insert(DB_DEBONAIR, $data, "update");
				} else {
					dbquery_insert(DB_DEBONAIR, $data, "save");
				}
				addNotice("success", $locale['debonair_0212']);
				redirect(clean_request("", array("slides"), false));
			}
		}
		echo "<h3>".$locale['debonair_0203']."</h3>\n";
		echo openform("debonair_banner", "post", FUSION_REQUEST, array("enctype"=>true));
		echo "<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-8'>\n";
		openside("");
		echo form_hidden("banner_id", "", $data['banner_id']);
		echo form_text("banner_subject", $locale['debonair_0205'], $data['banner_subject'], array("required"=>true, "inline"=>true, "placeholder"=>$locale['debonair_0206']));
		echo form_textarea("banner_description", $locale['debonair_0207'], $data['banner_subject'],
						   array("required"=>true, "inline"=>true, "placeholder"=>$locale['debonair_0208'], "bbcode"=>true,
						   ));
		echo form_text("banner_link", $locale['debonair_0209'], $data['banner_subject'], array("required"=>true, "inline"=>true, "placeholder"=>"ie. news.php?readmore=1"));
		closeside();
		openside("");
		echo form_fileinput("banner_image", $locale['debonair_0210'], $data['banner_image'] ? THEME."upload/".$data['banner_image'] : "", array("upload_path"=>THEME."upload/", "inline"=>true, "placeholder"=>$locale['debonair_0210'], "template"=>"modern"));
		closeside();
		echo "</div>\n<div class='col-xs-12 col-sm-4'>\n";
		openside("");
		echo form_select("banner_language", $locale['debonair_0214'], $data['banner_language'], array("options"=>fusion_get_enabled_languages(), "inline"=>true, "placeholder"=>$locale['debonair_0206']));
		echo form_select("banner_visibility", $locale['debonair_0215'], $data['banner_visibility'], array("options"=>fusion_get_groups(), "inline"=>true, "placeholder"=>$locale['debonair_0206']));
		closeside();
		echo form_button("save_slide", $locale['save_changes'], "save_slide", array("class"=>"btn-primary"));
		echo "<a class='btn btn-default m-l-10' href='".clean_request("", array("slides"), false)."'>".$locale['cancel']."</a>\n";
		echo "</div>\n</div>\n";
		echo closeform();
	} else {

		if (isset($_GET['move']) && isset($_GET['id']) && isnum($_GET['id'])) {
			$data = dbarray(dbquery("select banner_id, banner_order FROM ".DB_DEBONAIR." where banner_id='".intval($_GET['id'])."' AND banner_language='".LANGUAGE."'"));
			if ($_GET['move'] == "md") {
				dbquery("UPDATE ".DB_DEBONAIR." SET banner_order=banner_order-1 WHERE banner_order= '".($data['banner_order']+1)."' AND banner_language='".LANGUAGE."'");
				dbquery("UPDATE ".DB_DEBONAIR." SET banner_order=banner_order+1 WHERE banner_id='".$data['banner_id']."' AND banner_language='".LANGUAGE."'");
			}
			if ($_GET['move'] == "mup") {
				dbquery("UPDATE ".DB_DEBONAIR." SET banner_order=banner_order+1 WHERE banner_order= '".($data['banner_order']-1)."' AND banner_language='".LANGUAGE."'");
				dbquery("UPDATE ".DB_DEBONAIR." SET banner_order=banner_order-1 WHERE banner_id='".$data['banner_id']."' AND banner_language='".LANGUAGE."'");
			}
			addNotice("success", $locale['debonair_0220']);
			redirect(clean_request("", array("move", "id"), false));
		}

		$result = dbquery("SELECT * FROM ".DB_DEBONAIR." WHERE banner_language='".LANGUAGE."' order by banner_order ASC");
		if (dbrows($result)>0) {
			echo "<table class='table table-striped'>\n";
			echo "<tr>\n
		<th>".$locale['debonair_0205']."</th>
		<th>".$locale['debonair_0210']."</th>
		<th>".$locale['debonair_0214']."</th>\n<th>".$locale['debonair_0215']."</th>
		<th>".$locale['debonair_0211']."</th>
		<th>".$locale['debonair_0216']."</th></tr>";
			while ($data = dbarray($result)) {
				echo "
			<tr>\n
			<td><a href='".(clean_request("slides=edit&id=".$data['banner_id'], array(), false))."'>".$data['banner_subject']."</a></td>\n
			<td>".($data['banner_image'] ? $locale['yes'] : $locale['no'])."</td>\n
			<td>".$data['banner_language']."</td>\n
			<td>".getgroupname($data['banner_visibility'])."</td>\n";
				echo "<td>\n";
				if ($data['banner_order'] == 1) {
					echo "<a href='".(clean_request("move=md&id=".$data['banner_id'], array(), false))."'>".$locale['debonair_0219']."</a>";
				} elseif ($data['banner_order'] == dbrows($result)) {
					echo "<a href='".(clean_request("move=mup&id=".$data['banner_id'], array(), false))."'>".$locale['debonair_0218']."</a>";
				} else {
					echo "<a href='".(clean_request("move=mup&id=".$data['banner_id'], array(), false))."'>".$locale['debonair_0218']."</a> - ";
					echo "<a href='".(clean_request("move=md&id=".$data['banner_id'], array(), false))."'>".$locale['debonair_0219']."</a>";
				}
				echo "</td>\n
			<td>
			<a href='".(clean_request("slides=edit&id=".$data['banner_id'], array(), false))."'>".$locale['edit']."</a>
			- <a href='".(clean_request("slides=del&id=".$data['banner_id'], array(), false))."'>".$locale['delete']."</a>
			</td>
			</tr>\n
			";
			}
			echo "</table>\n";
		} else {
			echo "<div class='well text-center'>".$locale['debonair_0213']."</div>\n";
		}
	}
}

function debonair_theme_widget()
{
	global $locale;
	require_once "functions.php";
	$settings = get_theme_settings("debonair");
	/**
	 * data parsing
	 */
	$ubanner_col_1_data = uncomposeSelection($settings['ubanner_col_1']);
	$ubanner_col_2_data = uncomposeSelection($settings['ubanner_col_2']);
	$ubanner_col_3_data = uncomposeSelection($settings['ubanner_col_3']);

	$settings = array(
		"main_banner_url" => $settings['main_banner_url'],
		"ubanner_col_1" => !empty($ubanner_col_1_data['selected']) ? $ubanner_col_1_data['selected'] : 0,
		"ubanner_col_2" => !empty($ubanner_col_2_data['selected']) ? $ubanner_col_2_data['selected'] : 0,
		"ubanner_col_3" => !empty($ubanner_col_3_data['selected']) ? $ubanner_col_3_data['selected'] : 0,
		"lbanner_col_1" => $settings['lbanner_col_1'],
		"lbanner_col_2" => $settings['lbanner_col_2'],
		"lbanner_col_3" => $settings['lbanner_col_3'],
		"lbanner_col_4" => $settings['lbanner_col_4'],
		"facebook_url" => $settings['facebook_url'],
		"twitter_url" => $settings['twitter_url'],
	);

	if (isset($_POST['save_settings'])) {
		$inputArray = array(
			"main_banner_url" => form_sanitizer($_POST['main_banner_url'], "", "main_banner_url"),
			"ubanner_col_1" => composeSelection(form_sanitizer($_POST['ubanner_col_1'], "", "ubanner_col_1")),
			"ubanner_col_2" => composeSelection(form_sanitizer($_POST['ubanner_col_2'], "", "ubanner_col_2")),
			"ubanner_col_3" => composeSelection(form_sanitizer($_POST['ubanner_col_3'], "", "ubanner_col_3")),
			"lbanner_col_1" => form_sanitizer($_POST['lbanner_col_1'], "", "lbanner_col_1"),
			"lbanner_col_2" => form_sanitizer($_POST['lbanner_col_2'], "", "lbanner_col_2"),
			"lbanner_col_3" => form_sanitizer($_POST['lbanner_col_3'], "", "lbanner_col_3"),
			"lbanner_col_4" => form_sanitizer($_POST['lbanner_col_4'], "", "lbanner_col_4"),
			"facebook_url" => form_sanitizer($_POST['facebook_url'], "", "facebook_url"),
			"twitter_url" => form_sanitizer($_POST['twitter_url'], "", "twitter_url"),
		);
		foreach($inputArray as $settings_name => $settings_value) {
			$sqlArray = array(
				"settings_name" => $settings_name,
				"settings_value" => $settings_value,
				"settings_theme" => "debonair",
			);
			dbquery_insert(DB_SETTINGS_THEME, $sqlArray, "update", array("primary_key"=>"settings_name"));
		}
		if (defender::safe()) {
			redirect(FUSION_REQUEST);
		}
	}

	echo openform("debonair_theme_settings", "post", FUSION_REQUEST);
	$exclude_list = ".|..|.htaccess|.DS_Store|config.php|config.temp.php|.gitignore|LICENSE|README.md|robots.txt|reactivate.php|rewrite.php|maintenance.php|maincore.php|lostpassword.php|index.php|error.php";
	$list = array();
	$file_list = makefilelist(BASEDIR, $exclude_list);
	foreach ($file_list as $files) {
		$list[] = $files;
	}

	$include_list = array();
	$file_list = makefilelist(THEMES."/debonair/include/", $exclude_list);
	foreach ($file_list as $files) {
		$include_list[$files] = str_replace(".php", "", str_replace("_", " ", ucwords($files)));
	}

	openside("");
	echo form_select("main_banner_url", $locale['debonair_0300'], $settings['main_banner_url'], array("options"=>$list, "tags"=>true, "multiple"=>true, "width"=>"100%", "inline"=>false));
	echo "<p>".$locale['debonair_0301']."</p>";
	closeside();

	openside("");
	echo form_text("facebook_url", $locale['debonair_0321'], $settings['facebook_url'], array("type"=>"url", "inline"=>true, "placeholder"=>"http://www.facebook.com/your-page-id"));
	echo form_text("twitter_url", $locale['debonair_0322'], $settings['twitter_url'], array("type"=>"url", "inline"=>true, "placeholder"=>"http://www.twitter.com/your-page-id"));
	closeside();

	$templateOpts[0] = $locale['debonair_0302'];
	/**
	 * Article Selector
	 */
	$articleOpts = array();
	if (db_exists(DB_ARTICLES)) {
		$article_result = dbquery("select article_id, article_subject, article_cat_language FROM ".DB_ARTICLES." a
	 				left join ".DB_ARTICLE_CATS." ac on a.article_cat = ac.article_cat_id
	 				order by article_datestamp DESC
	 				");
		if (dbrows($article_result)>0) {
			while ($data = dbarray($article_result)) {
				$articleOpts[$data['article_cat_language']][$data['article_id']] = $data['article_subject'];
			}
		}
		if (!empty($articleOpts)) {
			$templateOpts['articles'] = $locale['debonair_0303'];
		}
	}
	/**
	 * News Selector
	 */
	$newsOpts = array();
	if (db_exists(DB_NEWS)) {
		$news_result = dbquery("select news_id, news_subject, news_language FROM ".DB_NEWS." order by news_datestamp DESC");
		if (dbrows($news_result)>0) {
			while ($data = dbarray($news_result)) {
				$newsOpts[$data['news_language']][$data['news_id']] = $data['news_subject'];
			}
		}
		if (!empty($newsOpts)) {
			$templateOpts['news'] = $locale['debonair_0304'];
		}
	}
	/**
	 * Blog Selector
	 */
	$blogOpts = array();
	if (db_exists(DB_BLOG)) {
		$blog_result = dbquery("select blog_id, blog_subject, blog_language FROM ".DB_BLOG."
	 				order by blog_datestamp DESC
	 				");
		if (dbrows($blog_result)>0) {
			while ($data = dbarray($blog_result)) {
				$blogOpts[$data['blog_language']][$data['blog_id']] = $data['blog_subject'];
			}
		}
		if (!empty($blogOpts)) {
			$templateOpts['blog'] = $locale['debonair_0305'];
		}
	}
	/**
	 * Custom Page Selector
	 * Note: custom page has a different multilanguage setup.
	 */
	$cpOpts = array();
	if (db_exists(DB_CUSTOM_PAGES)) {
		$cp_result = dbquery("select page_id, page_title, page_language FROM ".DB_CUSTOM_PAGES." order by page_id ASC");
		if (dbrows($cp_result)>0) {
			while ($data = dbarray($cp_result)) {
				$acceptedLang = stristr($data['page_language'], ".") ? explode(".", $data['page_language']) : array(0=> $data['page_language']);
				foreach(fusion_get_enabled_languages() as $lang) {
					if (in_array($lang, $acceptedLang)) {
						$cpOpts[$lang][$data['page_id']] = $data['page_title'];
					}
				}
			}
		}
		if (!empty($cpOpts)) {
			$templateOpts['cp'] = $locale['debonair_0306'];
		}
	}

	openside("");
	echo "<div class='row'>\n";
	echo "<div class='col-xs-12 col-sm-4'>\n";
	echo form_select("ubanner_col_1", $locale['debonair_0307'], $settings['ubanner_col_1'], array("options"=>$templateOpts, "inline"=>false));
	if (!empty($articleOpts)) {
		echo "<div id='ubanner_col_1-articles-choices' class='choices1' ".($settings['ubanner_col_1'] === "articles" ? "" : "style='display:none;'")."'>\n";
		foreach(fusion_get_enabled_languages() as $lang) {
			$callback_value = $settings['ubanner_col_1'] === "articles" && !empty($ubanner_col_1_data['options'][$lang]) ? $ubanner_col_1_data['options'][$lang] : "";
			echo form_select("articles-".$lang, sprintf($locale['debonair_0310'], $lang),  $callback_value, array("options"=>isset($articleOpts[$lang]) ? $articleOpts[$lang] : array()));
		}
		echo "</div>\n";
	}
	if (!empty($newsOpts)) {
		echo "<div id='ubanner_col_1-news-choices' class='choices1' ".($settings['ubanner_col_1'] === "news" ? "" : "style='display:none;'").">\n";
		foreach(fusion_get_enabled_languages() as $lang) {
			$callback_value = $settings['ubanner_col_1'] === "news" && !empty($ubanner_col_1_data['options'][$lang]) ? $ubanner_col_1_data['options'][$lang] : "";
			echo form_select("news-".$lang, sprintf($locale['debonair_0311'], $lang), $callback_value, array("options"=>isset($newsOpts[$lang]) ? $newsOpts[$lang] : array()));
		}
		echo "</div>\n";
	}
	if (!empty($blogOpts)) {
		echo "<div id='ubanner_col_1-blog-choices' class='choices1' ".($settings['ubanner_col_1'] === "blog" ? "" : "style='display:none;'").">\n";
		foreach(fusion_get_enabled_languages() as $lang) {
			$callback_value = $settings['ubanner_col_1'] === "blog" && !empty($ubanner_col_1_data['options'][$lang]) ? $ubanner_col_1_data['options'][$lang] : "";
			echo form_select("blog-".$lang, sprintf($locale['debonair_0312'], $lang), $callback_value, array("options"=>isset($blogOpts[$lang]) ? $blogOpts[$lang] : array()));
		}
		echo "</div>\n";
	}
	if (!empty($cpOpts)) {
		echo "<div id='ubanner_col_1-cp-choices' class='choices1' ".($settings['ubanner_col_1'] === "cp" ? "" : "style='display:none;'").">\n";
		foreach(fusion_get_enabled_languages() as $lang) {
			$callback_value = $settings['ubanner_col_1'] === "cp" && !empty($ubanner_col_1_data['options'][$lang]) ? $ubanner_col_1_data['options'][$lang] : "";
			echo form_select("cp-".$lang, sprintf($locale['debonair_0313'], $lang), $callback_value, array("options"=>isset($cpOpts[$lang]) ? $cpOpts[$lang] : array()));
		}
		echo "</div>\n";
	}

	echo "</div><div class='col-xs-12 col-sm-4'>\n";
	echo form_select("ubanner_col_2", $locale['debonair_0308'], $settings['ubanner_col_2'], array("options"=>$templateOpts, "inline"=>false));
	if (!empty($articleOpts)) {
		echo "<div id='ubanner_col_2-articles-choices' class='choices2' ".($settings['ubanner_col_2'] === "articles" ? "" : "style='display:none;'").">\n";
		foreach(fusion_get_enabled_languages() as $lang) {
			$callback_value = $settings['ubanner_col_2'] === "articles" && !empty($ubanner_col_2_data['options'][$lang]) ? $ubanner_col_2_data['options'][$lang] : "";
			echo form_select("articles2-".$lang, sprintf($locale['debonair_0310'], $lang), $callback_value, array("options"=>isset($articleOpts[$lang]) ? $articleOpts[$lang] : array()));
		}
		echo "</div>\n";
	}
	if (!empty($newsOpts)) {
		echo "<div id='ubanner_col_2-news-choices' class='choices2' ".($settings['ubanner_col_2'] === "news" ? "" : "style='display:none;'").">\n";
		foreach(fusion_get_enabled_languages() as $lang) {
			$callback_value = $settings['ubanner_col_2'] === "news" && !empty($ubanner_col_2_data['options'][$lang]) ? $ubanner_col_2_data['options'][$lang] : "";
			echo form_select("news2-".$lang, sprintf($locale['debonair_0311'], $lang), $callback_value, array("options"=>isset($newsOpts[$lang]) ? $newsOpts[$lang] : array()));
		}
		echo "</div>\n";
	}
	if (!empty($blogOpts)) {
		echo "<div id='ubanner_col_2-blog-choices' class='choices2' ".($settings['ubanner_col_2'] === "blog" ? "" : "style='display:none;'").">\n";
		foreach(fusion_get_enabled_languages() as $lang) {
			$callback_value = $settings['ubanner_col_2'] === "blog" && !empty($ubanner_col_2_data['options'][$lang]) ? $ubanner_col_2_data['options'][$lang] : "";
			echo form_select("blog2-".$lang, sprintf($locale['debonair_0312'], $lang), $callback_value, array("options"=>isset($blogOpts[$lang]) ? $blogOpts[$lang] : array()));
		}
		echo "</div>\n";
	}
	if (!empty($cpOpts)) {
		echo "<div id='ubanner_col_2-cp-choices' class='choices2' ".($settings['ubanner_col_2'] === "cp" ? "" : "style='display:none;'").">\n";
		foreach(fusion_get_enabled_languages() as $lang) {
			$callback_value = $settings['ubanner_col_2'] === "cp" && !empty($ubanner_col_2_data['options'][$lang]) ? $ubanner_col_2_data['options'][$lang] : "";
			echo form_select("cp2-".$lang, sprintf($locale['debonair_0313'], $lang), $callback_value, array("options"=>isset($cpOpts[$lang]) ? $cpOpts[$lang] : array()));
		}
		echo "</div>\n";
	}
	echo "</div><div class='col-xs-12 col-sm-4'>\n";
	// 3rd
	echo form_select("ubanner_col_3", $locale['debonair_0309'], $settings['ubanner_col_3'], array("options"=>$templateOpts, "inline"=>false));
	if (!empty($articleOpts)) {
		echo "<div id='ubanner_col_3-articles-choices' class='choices3' ".($settings['ubanner_col_3'] == "articles" ? "" : "style='display:none;'").">\n";
		foreach(fusion_get_enabled_languages() as $lang) {
			$callback_value = $settings['ubanner_col_3'] === "articles" && !empty($ubanner_col_3_data['options'][$lang]) ? $ubanner_col_3_data['options'][$lang] : "";
			echo form_select("articles3-".$lang, sprintf($locale['debonair_0310'], $lang), $callback_value, array("options"=>isset($articleOpts[$lang]) ? $articleOpts[$lang] : array()));
		}
		echo "</div>\n";
	}
	if (!empty($newsOpts)) {
		echo "<div id='ubanner_col_3-news-choices' class='choices3' ".($settings['ubanner_col_3'] === "news" ? "" : "style='display:none;'").">\n";
		foreach(fusion_get_enabled_languages() as $lang) {
			$callback_value = $settings['ubanner_col_3'] === "news" && !empty($ubanner_col_3_data['options'][$lang]) ? $ubanner_col_3_data['options'][$lang] : "";
			echo form_select("news3-".$lang, sprintf($locale['debonair_0311'], $lang), $callback_value, array("options"=>isset($newsOpts[$lang]) ? $newsOpts[$lang] : array()));
		}
		echo "</div>\n";
	}
	if (!empty($blogOpts)) {
		echo "<div id='ubanner_col_3-blog-choices' class='choices3' ".($settings['ubanner_col_3'] === "blog" ? "" : "style='display:none;'").">\n";
		foreach(fusion_get_enabled_languages() as $lang) {
			$callback_value = $settings['ubanner_col_3'] === "blog" && !empty($ubanner_col_3_data['options'][$lang]) ? $ubanner_col_3_data['options'][$lang] : "";
			echo form_select("blog3-".$lang, sprintf($locale['debonair_0312'], $lang), $callback_value, array("options"=>isset($blogOpts[$lang]) ? $blogOpts[$lang] : array()));
		}
		echo "</div>\n";
	}
	if (!empty($cpOpts)) {
		echo "<div id='ubanner_col_3-cp-choices' class='choices3' ".($settings['ubanner_col_3'] === "cp" ? "" : "style='display:none;'").">\n";
		foreach(fusion_get_enabled_languages() as $lang) {
			$callback_value = $settings['ubanner_col_3'] === "cp" && !empty($ubanner_col_3_data['options'][$lang]) ? $ubanner_col_3_data['options'][$lang] : "";
			echo form_select("cp3-".$lang, sprintf($locale['debonair_0313'], $lang), $callback_value, array("options"=>isset($cpOpts[$lang]) ? $cpOpts[$lang] : array()));
		}
		echo "</div>\n";
	}
	echo "</div>\n</div>\n";
	echo $locale['debonair_0315'];
	closeside();

	openside("");
	echo form_select("lbanner_col_1", $locale['debonair_0317'], $settings['lbanner_col_1'], array("options"=>$include_list, "inline"=>true));
	echo form_select("lbanner_col_2", $locale['debonair_0318'], $settings['lbanner_col_2'], array("options"=>$include_list, "inline"=>true));
	echo form_select("lbanner_col_3", $locale['debonair_0319'], $settings['lbanner_col_3'], array("options"=>$include_list, "inline"=>true));
	echo form_select("lbanner_col_4", $locale['debonair_0320'], $settings['lbanner_col_4'], array("options"=>$include_list, "inline"=>true));
	echo $locale['debonair_0316'];
	closeside();

	echo form_button("save_settings", $locale['save_changes'], "save", array("class"=>"btn-success"));
	echo closeform();

	// Now use Jquery to chain the selectors - add_to_jquery combines, include into a single min. document ready script
	add_to_jquery("
	function switchSelection(selector, value) {
		$('.choices'+selector).hide();
		if (value == '0') {
			$('.choices'+selector).hide();
		} else {
			$('#ubanner_col_'+selector+'-'+value+'-choices').show();
		}
	}
	$('#ubanner_col_1').bind('change', function() { switchSelection(1, $(this).val()); });
	$('#ubanner_col_2').bind('change', function() { switchSelection(2, $(this).val()); });
	$('#ubanner_col_3').bind('change', function() { switchSelection(3, $(this).val()); });
	");
}