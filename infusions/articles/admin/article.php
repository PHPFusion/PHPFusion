<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Articles/admin/article.php
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

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
    $data['article_breaks'] = 'n';
} else {
    $fusion_mce = array('preview' => 1, 'html' => 1, 'autosize' => 1, 'form_name' => 'inputform');
    $data['article_breaks'] = 'y';
}

if (isset($_POST['save'])) {

    $data = array(
        "article_id" => form_sanitizer($_POST['article_id'], 0, "article_id"),
        "article_cat" => form_sanitizer($_POST['article_cat'], 0, "article_cat"),
        "article_subject" => form_sanitizer($_POST['article_subject'], "", "article_subject"),
        "article_snippet" => form_sanitizer($_POST['article_snippet'], "", "article_snippet"),
        "article_article" => form_sanitizer($_POST['article_article'], "", "article_article"),
        "article_language" => form_sanitizer($_POST['article_language'], "", "article_language"),
        "article_keywords" => form_sanitizer($_POST['article_keywords'], "", "article_keywords"),
        "article_visibility" => form_sanitizer($_POST['article_visibility'], "", "article_visibility"),
        "article_draft" => isset($_POST['article_draft']) ? "1" : "0",
        "article_allow_comments" => isset($_POST['article_allow_comments']) ? "1" : "0",
        "article_allow_ratings" => isset($_POST['article_allow_ratings']) ? "1" : "0",
        "article_datestamp" => form_sanitizer($_POST['article_datestamp'], "", "article_datestamp"),
    );

    if (fusion_get_settings("tinymce_enabled") != 1) {
        $data['article_breaks'] = isset($_POST['article_breaks']) ? "y" : "n";
    } else {
        $data['article_breaks'] = "n";
    }

    if (defender::safe()) {
        if (isset($_POST['article_id']) && dbcount("(article_id)", DB_ARTICLES, "article_id='".intval($data['article_id'])."'")) {

            dbquery_insert(DB_ARTICLES, $data, "update");

            addNotice("success", $locale['articles_0101']);

            redirect(FUSION_SELF.$aidlink);

        } else {

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
        $article_snippet = parse_textarea($article_snippet);
    }
    $article_article = "";
    if ($_POST['article_article']) {
        $article_article = str_replace("src='".str_replace("../", "", IMAGES_A), "src='".IMAGES_A, stripslashes($_POST['article_article']));
        $article_article = parse_textarea($article_article);
    }
    $data = array(
        "article_id" => form_sanitizer($_POST['article_id'], 0, "article_id"),
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
        "article_language" => form_sanitizer($_POST['article_language'], LANGUAGE, "article_language"),
    );
    $bodypreview = parse_textarea($data['article_snippet']);
    $body2preview = parse_textarea($data['article_article']);
    if (isset($_POST['article_breaks'])) {
        $bodypreview = nl2br($bodypreview);
        $body2preview = nl2br($body2preview);
    }
    if (\defender::safe()) {
        $preview_html = openmodal('article_preview', $locale['articles_0240']);
        $preview_html .= "<h4>".$data['article_subject']."</h4>\n";
        $preview_html .= "<p class='text-bigger'>".$bodypreview."\n</p>";
        $preview_html .= "<p>".$body2preview."</p>\n";
        $preview_html .= closemodal();
        add_to_footer($preview_html);
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
echo "<div class='container-fluid'>\n";
echo "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-8'>\n";
echo form_hidden("article_id", "", $data['article_id']);
echo form_hidden("article_datestamp", "", $data['article_datestamp']);
echo form_text("article_subject", $locale['articles_0200'], $data['article_subject'], array('required' => TRUE));
echo form_select("article_keywords", $locale['articles_0204'], $data['article_keywords'], array(
    'max_length' => 320,
    'width' => '100%',
    'error_text' => $locale['articles_0257'],
    'tags' => TRUE,
    'multiple' => TRUE
));

$textArea_opts = array(
    "required" => TRUE,
    "type" => fusion_get_settings("tinymce_enabled") ? "tinymce" : "html",
    "tinymce" => fusion_get_settings("tinymce_enabled") && iADMIN ? "advanced" : "simple",
    "autosize" => TRUE,
    "form_name" => "input_form",
);

echo form_textarea('article_snippet', $locale['articles_0202'], $data['article_snippet'], $textArea_opts);
$textArea_opts['required'] = FALSE;
echo form_textarea("article_article", $locale['articles_0203'], $data['article_article'], $textArea_opts);

echo "</div>\n";
echo "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-4'>\n";

openside("");
echo form_select_tree("article_cat", $locale['articles_0201'], $data['article_cat'], array(
    "no_root" => TRUE,
    "query" => (multilang_table("AR") ? "WHERE article_cat_language='".LANGUAGE."'" : "")
), DB_ARTICLE_CATS, "article_cat_name", "article_cat_id", "article_cat_parent");

if (multilang_table("AR")) {
    echo form_select('article_language', $locale['global_ML100'], $data['article_language'], array(
        'options' => fusion_get_enabled_languages(),
        'placeholder' => $locale['choose'],
        "inline" => FALSE,
    ));
} else {
    echo form_hidden('article_language', '', $data['article_language']);
}
echo form_select('article_visibility', $locale['articles_0211'], $data['article_visibility'], array(
    'options' => fusion_get_groups(),
    'placeholder' => $locale['choose']
));
closeside();

openside("");
echo "<label><input type='checkbox' name='article_draft' value='yes' ".($data['article_draft'] ? "checked='checked'" : "")." /> ".$locale['articles_0205']."</label><br />\n";

if (fusion_get_settings("tinymce_enabled") == FALSE) {
    echo "<label><input type='checkbox' name='article_breaks' value='yes' ".($data['article_breaks'] ? "checked='checked'" : "")."  /> ".$locale['articles_0206']."</label><br />\n";
}
closeside();

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

echo form_button('preview', $locale['articles_0240'], $locale['articles_0240'], array('class' => 'btn-default m-r-10'));
echo form_button('save', $locale['articles_0241'], $locale['articles_0241'], array('class' => 'btn-primary'));
echo closeform();
