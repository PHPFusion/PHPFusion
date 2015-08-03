<?php
/**
 * Debonair Widget Console.
 * There must be two tabs.
 * First tab is to control widget
 * Second tab is to control theme settings
 */

$locale['debonair_0200'] = "Banner";
$locale['debonair_0201'] = "Theme Settings";
$locale['debonair_0203'] = "Add New Slides";
$locale['debonair_0204'] = "Edit Slides";
$locale['debonair_0205'] = "Subject";
$locale['debonair_0206'] = "Slide Title/Subject";
$locale['debonair_0207'] = "Description";
$locale['debonair_0208'] = "Slide Descriptive Text";
$locale['debonair_0209'] = "Link URL";
$locale['debonair_0210'] = "Image";
$locale['debonair_0211'] = "Order";
$locale['debonair_0212'] = "Banner slides saved";
$locale['debonair_0213'] = "There are no slides defined";
$locale['debonair_0214'] = "Language";
$locale['debonair_0215'] = "Visibility";
$locale['debonair_0216'] = "Options";
$locale['debonair_0217'] = "Slides has been deleted";
$locale['debonair_0218'] = "Move Up";
$locale['debonair_0219'] = "Move Down";
$locale['debonair_0220'] = "Slides has been reordered";

$tab['title'] = array($locale['debonair_0200'], $locale['debonair_0201']);
$tab['id'] = array("banner", "tsettings");
$tab_active = tab_active($tab, 1);
echo opentab($tab, $tab_active, "debonair_widget");
echo opentabbody($tab['title'][0], $tab['id'][0], $tab_active);
debonair_banner_widget();
echo closetabbody();
echo opentabbody($tab['title'][1], $tab['id'][1], $tab_active);
debonair_theme_widget();
echo closetabbody();

echo closetab();


function debonair_banner_widget() {
	global $aidlink, $locale;

	echo "<a class='btn btn-default m-t-10 m-b-20' href='".clean_request("slides=new", array(), false)."'>".$locale['debonair_0203']."</a>\n";

	$acceptedMode = array("edit", "new", "del");
	if (isset($_GET['slides']) && (in_array($_GET['slides'], $acceptedMode) && isset($_GET['id']) && isnum($_GET['id']))) {
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

function debonair_theme_widget() {
	global $locale;
	$settings = get_theme_settings("debonair");
	print_p($settings);
	echo openform("debonair_theme_settings", "post", FUSION_REQUEST);
	// see now what we have.
	$list = array();
	$file_list = makefilelist(BASEDIR, ".|..|.htaccess|.DS_Store|config.php|config.temp.php|.gitignore|LICENSE|README.md|robots.txt|reactivate.php|rewrite.php|maintenance.php|maincore.php|lostpassword.php|index.php|error.php");
	foreach ($file_list as $files) {
		$list[] = $files;
	}
	openside("");
	echo form_select("main_banner_url", "Main Banner Include", $settings['main_banner_url'], array("options"=>$list, "tags"=>true, "multiple"=>true, "width"=>"100%", "inline"=>false));
	echo "<p>Select the page URL where the banner will be used. Non selected pages will instead use the shorter page header.</p>";
	closeside();
	/*
	$templateOpts = array(
		"articles" => "Select Article",
		"news" => "Select News",
		"blog" => "Select Blog",
		"custom_page" => "Select Page",
		"forum_thread" => "Select Thread"
	);*/
	/*
	"latest_blog" => "Latest Blog",
	"latest_articles" => "Latest Articles",
	"latest_news" => "Latest News",
	*/

	$templateOpts[0] = "None";
	/**
	 * Article Selector
	 */
	$articleOpts = array();
	if (db_exists(DB_ARTICLES)) {
		$article_result = dbquery("select article_id, article_subject, article_cat_language FROM ".DB_ARTICLES." a
	 				left join ".DB_ARTICLE_CATS." ac on a.article_cat = ac.article_cat_id
	 				WHERE article_cat_language='".LANGUAGE."'
	 				order by article_datestamp DESC
	 				");
		if (dbrows($article_result)>0) {
			while ($data = dbarray($article_result)) {
				$articleOpts[$data['article_cat_language']][$data['article_id']] = $data['article_subject'];
			}
		}
		if (!empty($articleOpts)) {
			$templateOpts['articles'] = "Display Article";
		}
	}
	/**
	 * News Selector
	 */
	$newsOpts = array();
	if (db_exists(DB_NEWS)) {
		$news_result = dbquery("select news_id, news_subject, news_cat_language FROM ".DB_NEWS." n
	 				left join ".DB_NEWS_CATS." nc on n.news_cat = nc.news_cat_id
	 				WHERE news_cat_language='".LANGUAGE."'
	 				order by news_datestamp DESC
	 				");
		if (dbrows($news_result)>0) {
			while ($data = dbarray($news_result)) {
				$newsOpts[$data['news_cat_language']][$data['news_id']] = $data['news_subject'];
			}
		}
		if (!empty($newsOpts)) {
			$templateOpts['news'] = "Display News";
		}
	}

	/**
	 * Blog Selector
	 */
	$blogOpts = array();
	if (db_exists(DB_BLOG)) {
		$blog_result = dbquery("select blog_id, blog_subject, blog_language FROM ".DB_BLOG."
	 				WHERE blog_language='".LANGUAGE."'
	 				order by blog_datestamp DESC
	 				");
		if (dbrows($blog_result)>0) {
			while ($data = dbarray($blog_result)) {
				$blogOpts[$data['blog_language']][$data['blog_id']] = $data['blog_subject'];
			}
		}
		if (!empty($blogOpts)) {
			$templateOpts['blog'] = "Display Blog";
		}
	}
	/**
	 * Custom Page Selector
	 */
	/**
	 * Forum Thread Selector
	 */
	openside("");
	echo "<div class='row'>\n";
	echo "<div class='col-xs-12 col-sm-4'>\n";
	echo form_select("ubanner_col_1", "Header Column 1 Presets", $settings['ubanner_col_1'], array("options"=>$templateOpts, "inline"=>false));

	if (!empty($articleOpts)) {
		echo "<div id='ubanner_col_1-article-choices' class='choices1' style='display:none;'>\n";
		foreach(fusion_get_enabled_languages() as $lang) {
			echo form_select("articles-".$lang, "Article in ".$lang, "", array("options"=>isset($articleOpts[$lang]) ? $articleOpts[$lang] : array()));
		}
		echo "</div>\n";
	}
	if (!empty($newsOpts)) {
		echo "<div id='ubanner_col_1-news-choices' class='choices1' style='display:none;'>\n";
		foreach(fusion_get_enabled_languages() as $lang) {
			echo form_select("news-".$lang, "News in ".$lang, "", array("options"=>isset($newsOpts[$lang]) ? $newsOpts[$lang] : array()));
		}
		echo "</div>\n";
	}
	if (!empty($blogOpts)) {
		echo "<div id='ubanner_col_1-blog-choices' class='choices1'  style='display:none;'>\n";
		foreach(fusion_get_enabled_languages() as $lang) {
			echo form_select("blog-".$lang, "Blog in ".$lang, "", array("options"=>isset($blogOpts[$lang]) ? $blogOpts[$lang] : array()));
		}
		echo "</div>\n";
	}
	// now use jquery to chain it.
	add_to_jquery("
	$('#ubanner_col_1').bind('change', function() {
		var val = $(this).val();
		if (val == '0') {
			$('.choices1').hide();
		} else {
			$('#ubanner_col_1-'+val+'-choices').show();
		}
	});
	");


	echo "</div><div class='col-xs-12 col-sm-4'>\n";
	echo form_select("ubanner_col_2", "Header Column 2 Presets", $settings['ubanner_col_2'], array("options"=>$templateOpts, "inline"=>false));

	echo "</div><div class='col-xs-12 col-sm-4'>\n";
	echo form_select("ubanner_col_3", "Header Column 3 Presets", $settings['ubanner_col_3'], array("options"=>$templateOpts, "inline"=>false));

	echo "</div>\n</div>\n";
	closeside();


	echo closeform();
}