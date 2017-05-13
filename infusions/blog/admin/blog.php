<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: admin/blog.php
| Author: Frederick MC Chan (Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
$formaction = FUSION_REQUEST;
$locale = fusion_get_locale();
$userdata = fusion_get_userdata();

// use the select
$blog_cat_opts[0] = $locale['blog_0424'];
$cat_result = dbquery("SELECT blog_cat_id, blog_cat_name FROM ".DB_BLOG_CATS." ".(multilang_table("BL") ? "WHERE blog_cat_language='".LANGUAGE."'" : "")." ORDER BY blog_cat_name ASC");
if (dbrows($result) > 0) {
    while ($bcData = dbarray($cat_result)) {
        $blog_cat_opts[$bcData['blog_cat_id']] = $bcData['blog_cat_name'];
    }
}

$data = array(
    'blog_id'             => 0,
    'blog_draft'          => 0,
    'blog_sticky'         => 0,
    'blog_blog'           => '',
    'blog_datestamp'      => TIME,
    'blog_extended'       => '',
    'blog_keywords'       => '',
    'blog_breaks'         => 'y',
    'blog_allow_comments' => 1,
    'blog_allow_ratings'  => 1,
    'blog_language'       => LANGUAGE,
    'blog_visibility'     => 0,
    'blog_subject'        => '',
    'blog_start'          => '',
    'blog_end'            => '',
    'blog_cat'            => 0,
    'blog_image'          => '',
    'blog_ialign'         => 'pull-left',
);

if (fusion_get_settings('tinymce_enabled') != 1) {
    $data['blog_breaks'] = isset($_POST['line_breaks']) ? "y" : "n";
} else {
    $data['blog_breaks'] = "n";
}

if (isset($_POST['save']) or isset($_POST['preview'])) {

    $blog_blog = "";
    if ($_POST['blog_blog']) {
        $blog_blog = str_replace("src='".str_replace("../", "", IMAGES_B), "src='".IMAGES_B, (fusion_get_settings("allow_php_exe") ? htmlspecialchars($_POST['blog_blog']) : $_POST['blog_blog']));
    }

    $blog_extended = "";
    if ($_POST['blog_extended']) {
        $blog_extended = str_replace("src='".str_replace("../", "", IMAGES_B), "src='".IMAGES_B, (fusion_get_settings("allow_php_exe") ? htmlspecialchars($_POST['blog_extended']) : $_POST['blog_extended']));
    }

    $data = array(
        'blog_id'             => form_sanitizer($_POST['blog_id'], 0, 'blog_id'),
        'blog_subject'        => form_sanitizer($_POST['blog_subject'], '', 'blog_subject'),
        'blog_cat'            => isset($_POST['blog_cat']) ? form_sanitizer($_POST['blog_cat'], 0, 'blog_cat') : "",
        'blog_blog'           => form_sanitizer($blog_blog, '', 'blog_blog'),
        'blog_extended'       => form_sanitizer($blog_extended, '', 'blog_extended'),
        'blog_keywords'       => form_sanitizer($_POST['blog_keywords'], '', 'blog_keywords'),
        'blog_ialign'         => form_sanitizer($_POST['blog_ialign'], '', 'blog_ialign'),
        'blog_image'          => "",
        'blog_start'          => form_sanitizer($_POST['blog_start'], "", 'blog_start'),
        'blog_end'            => form_sanitizer($_POST['blog_end'], "", 'blog_end'),
        'blog_visibility'     => form_sanitizer($_POST['blog_visibility'], 0, 'blog_visibility'),
        'blog_draft'          => isset($_POST['blog_draft']) ? "1" : "0",
        'blog_sticky'         => isset($_POST['blog_sticky']) ? "1" : "0",
        "blog_breaks"         => isset($_POST['line_breaks']) ? 'y' : 'n',
        'blog_allow_comments' => isset($_POST['blog_allow_comments']) ? "1" : "0",
        'blog_allow_ratings'  => isset($_POST['blog_allow_ratings']) ? "1" : "0",
        'blog_language'       => form_sanitizer($_POST['blog_language'], '', 'blog_language'),
        'blog_datestamp'      => form_sanitizer($_POST['blog_datestamp'], '', 'blog_datestamp'),
    );

    if (isset($_POST['preview']) && \defender::safe()) {
        $modal = openmodal('blog_preview', $locale['blog_0141']." - ".$data['blog_subject']);
        $modal .= "<div class='m-b-20'>\n";
        $modal .= "";
        $modal .= "<div class='well'><p><strong>".$locale['blog_0425']."</strong></p>".$blog_blog."</div>";
        $modal .= $blog_extended;
        $modal .= "</div>\n";
        $modal .= closemodal();
        \PHPFusion\OutputHandler::addToFooter($modal);

    } else {

        if (isset($_FILES['blog_image'])) { // when files is uploaded.
            $upload = form_sanitizer($_FILES['blog_image'], '', 'blog_image');
            if (!empty($upload) && !$upload['error']) {
                $data['blog_image'] = $upload['image_name'];
                $data['blog_image_t1'] = $upload['thumb1_name'];
                $data['blog_image_t2'] = $upload['thumb2_name'];
                $data['blog_ialign'] = (isset($_POST['blog_ialign']) ? form_sanitizer($_POST['blog_ialign'], "pull-left", "blog_ialign") : "pull-left");
            }
        } else { // when files not uploaded. but there should be exist check.
            $data['blog_image'] = (isset($_POST['blog_image']) ? $_POST['blog_image'] : "");
            $data['blog_image_t1'] = (isset($_POST['blog_image_t1']) ? $_POST['blog_image_t1'] : "");
            $data['blog_image_t2'] = (isset($_POST['blog_image_t2']) ? $_POST['blog_image_t2'] : "");
            $data['blog_ialign'] = (isset($_POST['blog_ialign']) ? form_sanitizer($_POST['blog_ialign'], "pull-left", "blog_ialign") : "pull-left");
        }

        if ($data['blog_sticky'] == "1") {
            $result = dbquery("UPDATE ".DB_BLOG." SET blog_sticky='0' WHERE blog_sticky='1'");
        } // reset other sticky
        // delete image
        if (isset($_POST['del_image'])) {
            if (!empty($data['blog_image']) && file_exists(IMAGES_B.$data['blog_image'])) {
                unlink(IMAGES_B.$data['blog_image']);
            }
            if (!empty($data['blog_image_t1']) && file_exists(IMAGES_B_T.$data['blog_image_t1'])) {
                unlink(IMAGES_B_T.$data['blog_image_t1']);
            }
            if (!empty($data['blog_image_t2']) && file_exists(IMAGES_B_T.$data['blog_image_t2'])) {
                unlink(IMAGES_B_T.$data['blog_image_t2']);
            }
            $data['blog_image'] = "";
            $data['blog_image_t1'] = "";
            $data['blog_image_t2'] = "";
        }

        if (\defender::safe()) {
            if (dbcount("('blog_id')", DB_BLOG, "blog_id='".$data['blog_id']."'")) {
                dbquery_insert(DB_BLOG, $data, 'update');
                addNotice('success', $locale['blog_0411']);
                redirect(FUSION_SELF.$aidlink);
            } else {
                $data['blog_name'] = $userdata['user_id'];
                dbquery_insert(DB_BLOG, $data, 'save');
                addNotice('success', $locale['blog_0410']);
                redirect(FUSION_SELF.$aidlink);
            }
        }

    }

} elseif ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_POST['blog_id']) && isnum($_POST['blog_id'])) || (isset($_GET['blog_id']) && isnum($_GET['blog_id']))) {
    $result = dbquery("SELECT * FROM ".DB_BLOG." WHERE blog_id='".(isset($_POST['blog_id']) ? $_POST['blog_id'] : $_GET['blog_id'])."'");
    if (dbrows($result)) {
        $data = dbarray($result);
    } else {
        redirect(FUSION_SELF.$aidlink);
    }
}

echo "<div class='m-t-20'>\n";
echo openform('inputform', 'post', $formaction, array('enctype' => TRUE));
echo "<div class='row'>\n";
echo "<div class='col-xs-12 col-sm-12 col-md-7 col-lg-8'>\n";
echo form_hidden("blog_id", "", $data['blog_id']);
echo form_hidden("blog_datestamp", "", $data['blog_datestamp']);
echo form_text('blog_subject', '', $data['blog_subject'], array(
    'required'    => TRUE,
    'placeholder' => $locale['blog_0422'],
    'max_length'  => 200,
    'inner_class' => 'input-lg',
    'error_text'  => $locale['blog_0450']
));
// move keywords here because it's required
echo form_select('blog_keywords', $locale['blog_0443'], $data['blog_keywords'], array(
    "max_length"  => 320,
    "placeholder" => $locale['blog_0444'],
    "inner_width" => "100%",
    "width"       => '100%',
    "error_text"  => $locale['blog_0457'],
    "tags"        => TRUE,
    "multiple"    => TRUE
));
echo "<div class='pull-left m-r-10 display-inline-block'>\n";
echo form_datepicker('blog_start', $locale['blog_0427'], $data['blog_start'],
    array(
        "placeholder" => $locale['blog_0429'],
        "join_to_id"  => "blog_end",
        "width"       => "250px"
    )
);
echo "</div>\n<div class='pull-left m-r-10 display-inline-block'>\n";
echo form_datepicker('blog_end', $locale['blog_0428'], $data['blog_end'],
    array(
        "placeholder"  => $locale['blog_0429'],
        "join_from_id" => "blog_start",
        "width"        => "250px"
    )
);
echo "</div>\n";
echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-5 col-lg-4'>\n";
openside('');

echo form_select('blog_cat[]', $locale['blog_0423'], $data['blog_cat'], array(
        'options'     => $blog_cat_opts,
        "width"       => "100%",
        'inner_width' => '100%',
        "inline"      => TRUE,
        'multiple'    => TRUE,
    )
);

echo form_select('blog_visibility', $locale['blog_0430'], $data['blog_visibility'], array(
    'options'     => fusion_get_groups(),
    'placeholder' => $locale['choose'],
    'width'       => '100%',
    "inline"      => TRUE,
));

if (multilang_table("BL")) {
    echo form_select('blog_language', $locale['global_ML100'], $data['blog_language'], array(
        'options'     => fusion_get_enabled_languages(),
        'placeholder' => $locale['choose'],
        'width'       => '100%',
        "inline"      => TRUE,
    ));
} else {
    echo form_hidden('blog_language', '', $data['blog_language']);
}

echo form_button('cancel', $locale['cancel'], $locale['cancel'], array('class' => 'btn-default m-r-10', 'icon' => 'fa fa-times'));
echo form_button('save', $locale['blog_0437'], $locale['blog_0437'], array('class' => 'btn-success', 'icon' => 'fa fa-hdd-o'));
closeside();
echo "</div>\n</div>\n";
$snippetSettings = array(
    'required'    => TRUE,
    'preview'     => TRUE,
    'html'        => TRUE,
    'autosize'    => TRUE,
    'placeholder' => $locale['blog_0425a'],
    'form_name'   => 'inputform',
    'path'        => [IMAGES, IMAGES_B, IMAGES_BC]
);
if (fusion_get_settings('tinymce_enabled')) {
    $snippetSettings = array('required' => TRUE, 'type' => 'tinymce', 'tinymce' => 'advanced');
}
echo form_textarea('blog_blog', $locale['blog_0425'], $data['blog_blog'], $snippetSettings);

$extendedSettings = array();
if (!fusion_get_settings('tinymce_enabled')) {
    $extendedSettings = array(
        'preview'     => TRUE,
        'html'        => TRUE,
        'autosize'    => TRUE,
        'placeholder' => $locale['blog_0426b'],
        'form_name'   => 'inputform',
        'path'        => [IMAGES, IMAGES_B, IMAGES_BC]
    );
} else {
    $extendedSettings = array('type' => 'tinymce', 'tinymce' => 'advanced', 'path' => [IMAGES, IMAGES_B, IMAGES_BC]);
}
echo form_textarea('blog_extended', $locale['blog_0426'], $data['blog_extended'], $extendedSettings);
echo "<div class='row'>\n";
echo "<div class='col-xs-12 col-sm-12 col-md-7 col-lg-8'>\n";
openside('');
if ($data['blog_image'] != "" && $data['blog_image_t1'] != "") {
    echo "<div class='row'>\n";
    echo "<div class='col-xs-12 col-sm-6'>\n";
    $image_thumb = get_blog_image_path($data['blog_image'], $data['blog_image_t1'], $data['blog_image_t2']);
    echo "<label>".thumbnail($image_thumb, '100px');
    echo "<input type='checkbox' name='del_image' value='y' /> ".$locale['delete']."</label>\n";
    echo "</div>\n";
    echo "<div class='col-xs-12 col-sm-6'>\n";
    $alignOptions = array(
        'pull-left'       => $locale['left'],
        'blog-img-center' => $locale['center'],
        'pull-right'      => $locale['right']
    );
    echo form_select('blog_ialign', $locale['blog_0442'], $data['blog_ialign'], array(
        "options" => $alignOptions,
        "inline"  => FALSE
    ));
    echo "</div>\n</div>\n";
    echo "<input type='hidden' name='blog_image' value='".$data['blog_image']."' />\n";
    echo "<input type='hidden' name='blog_image_t1' value='".$data['blog_image_t1']."' />\n";
    echo "<input type='hidden' name='blog_image_t2' value='".$data['blog_image_t2']."' />\n";
} else {
    $file_input_options = array(
        'upload_path'      => IMAGES_B,
        'max_width'        => $blog_settings['blog_photo_max_w'],
        'max_height'       => $blog_settings['blog_photo_max_h'],
        'max_byte'         => $blog_settings['blog_photo_max_b'],
        // set thumbnail
        'thumbnail'        => 1,
        'thumbnail_w'      => $blog_settings['blog_thumb_w'],
        'thumbnail_h'      => $blog_settings['blog_thumb_h'],
        'thumbnail_folder' => 'thumbs',
        'delete_original'  => 0,
        // set thumbnail 2 settings
        'thumbnail2'       => 1,
        'thumbnail2_w'     => $blog_settings['blog_photo_w'],
        'thumbnail2_h'     => $blog_settings['blog_photo_h'],
        'valid_ext'        => $blog_settings['blog_file_types'],
        'type'             => 'image'
    );
    echo form_fileinput("blog_image", $locale['blog_0439'], "", $file_input_options);
    echo "<div class='small m-b-10'>".sprintf($locale['blog_0440'], parsebytesize($blog_settings['blog_photo_max_b']))."</div>\n";
    $alignOptions = array(
        'pull-left'       => $locale['left'],
        'news-img-center' => $locale['center'],
        'pull-right'      => $locale['right']
    );
    echo form_select('blog_ialign', $locale['blog_0442'], $data['blog_ialign'], array("options" => $alignOptions));
}
closeside();
openside('');
echo "<label><input type='checkbox' name='blog_draft' value='yes'".($data['blog_draft'] ? "checked='checked'" : "")." /> ".$locale['blog_0431']."</label><br />\n";
echo "<label><input type='checkbox' name='blog_sticky' value='yes'".($data['blog_sticky'] ? "checked='checked'" : "")."  /> ".$locale['blog_0432']."</label><br />\n";
if (fusion_get_settings("tinymce_enabled") != 1) {
    echo "<label><input type='checkbox' name='line_breaks' value='yes'".($data['blog_breaks'] ? "checked='checked'" : "")." /> ".$locale['blog_0433']."</label><br />\n";
}
closeside();
echo "</div>\n<div class='col-xs-12 col-sm-12 col-md-5 col-lg-4'>\n";
openside("");
if (!fusion_get_settings("comments_enabled") || !fusion_get_settings("ratings_enabled")) {
    $sys = "";
    if (!fusion_get_settings("comments_enabled") && !fusion_get_settings("ratings_enabled")) {
        $sys = $locale['comments_ratings'];
    } elseif (!fusion_get_settings("comments_enabled")) {
        $sys = $locale['comments'];
    } else {
        $sys = $locale['ratings'];
    }

    echo "<div class='well'>".sprintf($locale['blog_0149'], "<strong>$sys</strong>")."</div>\n";
}
echo "<label><input type='checkbox' name='blog_allow_comments' value='yes' onclick='SetRatings();'".($data['blog_allow_comments'] ? "checked='checked'" : "")." /> ".$locale['blog_0434']."</label><br/>";
echo "<label><input type='checkbox' name='blog_allow_ratings' value='yes'".($data['blog_allow_ratings'] ? "checked='checked'" : "")." /> ".$locale['blog_0435']."</label>";
closeside();
echo "</div>\n</div>\n";
echo form_button('save', $locale['blog_0437'], $locale['blog_0437'], array('class' => 'btn-success', 'icon' => 'fa fa-hdd-o'));
echo form_button('preview', $locale['blog_0141'], $locale['blog_0141'], array('class' => 'btn-primary', 'icon' => 'fa fa-eye'));
echo closeform();
echo "</div>\n";
