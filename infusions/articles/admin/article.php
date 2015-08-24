<?php
// because there are no uncategorized.
// should have an uncategorized.
$data = array(
	"article_id" => 0,
	"article_cat" => 0,
	"article_subject" => "",
	"article_snippet" => "",
	"article_article" => "",
	"article_keywords" => "",
	"article_draft" => FALSE,
	"article_name" => $userdata['user_id'],
	"article_language" => LANGUAGE,
	"article_datestamp" => time(),
	"article_visibility" => iGUEST,
	"article_reads" => 0,
	"article_allow_comments" => TRUE,
	"article_allow_ratings" => TRUE,
);
if (fusion_get_settings("tinymce_enabled")) {
	echo "<script language='javascript' type='text/javascript'>advanced();</script>\n";
	$data['article_breaks'] = 'n';
} else {
	$fusion_mce = array('preview' => 1, 'html' => 1, 'autosize' => 1, 'form_name' => 'inputform');
	$data['article_breaks'] = 'y';
}
if (isset($_POST['save'])) {
	$article_snippet = "";
	if ($_POST['article_snippet']) {
		$article_snippet = str_replace("src='".str_replace("../", "", IMAGES_A), "src='".IMAGES_A, stripslashes($_POST['article_snippet']));
		$article_snippet = html_entity_decode($article_snippet);
	}
	$article_article = "";
	if ($_POST['article_article']) {
		$article_article = str_replace("src='".str_replace("../", "", IMAGES_A), "src='".IMAGES_A, stripslashes($_POST['article_article']));
		$article_article = html_entity_decode($article_article);
	}
	$data = array(
		"article_id" => form_sanitizer($_POST['article_id'], 0, "article_id"),
		"article_cat" => form_sanitizer($_POST['article_cat'], 0, "article_cat"),
		"article_subject" => form_sanitizer($_POST['article_subject'], "", "article_subject"),
		"article_snippet" => form_sanitizer($article_snippet, "", "article_snippet"),
		"article_article" => form_sanitizer($article_article, "", "article_article"),
		"article_language" => form_sanitizer($_POST['article_language'], "", "article_language"),
		"article_keywords" => form_sanitizer($_POST['article_keywords'], "", "article_keywords"),
		"article_visibility" => form_sanitizer($_POST['article_visibility'], "", "article_visibility"),
		"article_draft" => isset($_POST['article_draft']) ? "1" : "0",
		"article_allow_comments" => isset($_POST['article_allow_comments']) ? "1" : "0",
		"article_allow_ratings" => isset($_POST['article_allow_ratings']) ? "1" : "0",
		"article_datestamp" => form_sanitizer($_POST['article_datestamp'], "", "article_datestamp"),
	);
	if (fusion_get_settings("tinymce_enabled") != 1) {
		$data['article_breaks'] = isset($_POST['line_breaks']) ? "y" : "n";
	} else {
		$data['article_breaks'] = "n";
	}
	if (defender::safe()) {
		if (isset($_POST['article_id']) && dbcount("(article_id)", DB_ARTICLES, "article_id='".intval($data['article_id'])."'")) {
			dbquery_insert(DB_ARTICLES, $data, "update");
			addNotice("success", $locale['articles_0101']);
			redirect(FUSION_SELF.$aidlink);
		} else {
			// only add time and name here.
			$data['article_name'] = $userdata['user_id'];
			dbquery_insert(DB_ARTICLES, $data, "save");
			addNotice("success", $locale['articles_0100']);
			redirect(FUSION_SELF.$aidlink);
		}
	}
}
if (isset($_POST['preview'])) {
	$article_snippet = "";
	if ($_POST['article_snippet']) {
		$article_snippet = str_replace("src='".str_replace("../", "", IMAGES_A), "src='".IMAGES_A, stripslashes($_POST['article_snippet']));
		$article_snippet = html_entity_decode($article_snippet);
	}
	$article_article = "";
	if ($_POST['article_article']) {
		$article_article = str_replace("src='".str_replace("../", "", IMAGES_A), "src='".IMAGES_A, stripslashes($_POST['article_article']));
		$article_article = html_entity_decode($article_article);
	}
	$data = array(
		"article_cat" => form_sanitizer($_POST['article_cat'], 0, "article_cat"),
		"article_subject" => form_sanitizer($_POST['article_subject'], "", "article_subject"),
		"article_snippet" => form_sanitizer($article_snippet, "", "article_snippet"),
		"article_article" => form_sanitizer($article_article, "", "article_article"),
		"article_keywords" => form_sanitizer($_POST['article_keywords'], "", "article_keywords"),
		"article_visibility" => form_sanitizer($_POST['article_visibility'], "", "article_visibility"),
		"article_draft" => isset($_POST['article_draft']) ? TRUE : FALSE,
		"article_breaks" => isset($_POST['article_breaks']) ? TRUE : FALSE,
		"article_allow_comments" => isset($_POST['article_allow_comments']) ? TRUE : FALSE,
		"article_allow_ratings" => isset($_POST['article_allow_ratings']) ? TRUE : FALSE,
		"article_datestamp" => form_sanitizer($_POST['article_datestamp'], "", "article_datestamp"),
	);
	$bodypreview = html_entity_decode(stripslashes($data['article_snippet']));
	$body2preview = html_entity_decode(stripslashes($data['article_article']));
	if (isset($_POST['article_breaks'])) {
		$bodypreview = nl2br(html_entity_decode($bodypreview));
		$body2preview = nl2br(html_entity_decode($body2preview));
	}
	if (defender::safe()) {
		echo openmodal('article_preview', $locale['articles_0240']);
		echo "<h4>".$data['article_subject']."</h4>\n";
		echo "<p class='text-bigger'>".$bodypreview."\n</p>";
		echo "<p>".$body2preview."</p>\n";
		echo closemodal();
	}
}
if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_POST['article_id']) && isnum($_POST['article_id'])) || (isset($_GET['article_id']) && isnum($_GET['article_id']))) {
	$id = "";
	if (isset($_POST['article_id']) && isnum($_POST['article_id'])) {
		$id = $_POST['article_id'];
	} elseif (isset($_GET['article_id']) && isnum($_GET['article_id'])) {
		$id = $_GET['article_id'];
	}
	$result = dbquery("SELECT * FROM ".DB_ARTICLES." WHERE article_id='".intval($id)."'");
	if (dbrows($result) > 0) {
		$data = dbarray($result);
		$data['article_snippet'] = phpentities(stripslashes($data['article_snippet']));
		$data['article_article'] = phpentities(stripslashes($data['article_article']));
	} else {
		redirect(FUSION_SELF.$aidlink);
	}
}
echo openform('input_form', 'post', FUSION_REQUEST, array("class" => "m-t-20"));
echo "<div class='row'>\n";
echo "<div class='col-xs-12 col-sm-8'>\n";
echo form_hidden("article_id", "", $data['article_id']);
echo form_hidden("article_datestamp", "", $data['article_datestamp']);
echo form_text("article_subject", $locale['articles_0200'], $data['article_subject'], array('required' => TRUE));
echo form_select("article_keywords", $locale['articles_0204'], $data['article_keywords'], array(
	'max_length' => 320,
	'width' => '100%',
	'error_text' => $locale['articles_0257'],
	'tags' => 1,
	'multiple' => 1
));
openside("");
echo "<label><input type='checkbox' name='article_draft' value='yes' ".($data['article_draft'] ? "checked='checked'" : "")." /> ".$locale['articles_0205']."</label><br />\n";
if (fusion_get_settings("tinymce_enabled") == FALSE) {
	echo "<label><input type='checkbox' name='article_breaks' value='yes' ".($data['article_breaks'] ? "checked='checked'" : "")."  /> ".$locale['articles_0206']."</label><br />\n";
}
closeside();
echo "</div>\n";
echo "<div class='col-xs-12 col-sm-4'>\n";
echo form_select_tree("article_cat", $locale['articles_0201'], $data['article_cat'], array(
	"no_root" => TRUE,
	"width" => "100%",
	"query" => (multilang_table("AR") ? "WHERE article_cat_language='".LANGUAGE."'" : "")
), DB_ARTICLE_CATS, "article_cat_name", "article_cat_id", "article_cat_parent");

if (multilang_table("AR")) {
	echo form_select('article_language', $locale['global_ML100'], $data['article_language'], array(
		'options' => fusion_get_enabled_languages(),
		'placeholder' => $locale['choose'],
		'width' => '100%',
		"inline" => false,
	));
} else {
	echo form_hidden('article_language', '', $data['article_language']);
}
echo form_select('article_visibility', $locale['articles_0211'], $data['article_visibility'], array(
	"width" => "100%",
	'options' => fusion_get_groups(),
	'placeholder' => $locale['choose']
));
openside("");
if (!fusion_get_settings("comments_enabled") || !fusion_get_settings("ratings_enabled")) {
	$sys = "";
	if (!fusion_get_settings("comments_enabled") || !fusion_get_settings("ratings_enabled")) {
		$sys = $locale['comments_ratings'];
	} elseif (!fusion_get_settings("comments_enabled")) {
		$sys = $locale['comments'];
	} else {
		$sys = $locale['ratings'];
	}
	echo "<div class='alert alert-warning'>".sprintf($locale['articles_0256'], $sys)."</div>\n";
}
echo "<label><input type='checkbox' name='article_allow_comments' value='yes' ".($data['article_allow_comments'] ? "checked='checked'" : "")."/> ".$locale['articles_0207']."</label><br/>";
echo "<label><input type='checkbox' name='article_allow_ratings' value='yes' ".($data['article_allow_ratings'] ? "checked='checked'" : "")." /> ".$locale['articles_0208']."</label>";
closeside();
echo "</div>\n";
echo "</div>\n";
$snippet_settings = array(
	"autosize" => TRUE,
	"html" => TRUE,
	"preview" => TRUE,
	"form_name" => "input_form",
	"required" => TRUE,
);
if (fusion_get_settings("tinymce_enabled") == TRUE) {
	$snippet_settings = array(
		"required" => TRUE,
	);
}
echo form_textarea('article_snippet', $locale['articles_0202'], $data['article_snippet'], $snippet_settings);
$snippet_settings['required'] = false;
echo form_textarea("article_article", $locale['articles_0203'], $data['article_article'], $snippet_settings);
echo form_button('preview', $locale['articles_0240'], $locale['articles_0240'], array('class' => 'btn-default m-r-10'));
echo form_button('save', $locale['articles_0241'], $locale['articles_0241'], array('class' => 'btn-primary'));
echo closeform();