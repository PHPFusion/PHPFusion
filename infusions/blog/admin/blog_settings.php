<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: admin/blog_settings.php
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
pageAccess('BLOG');
if (isset($_POST['savesettings'])) {
    $error = 0;
    $inputArray = array(
        "blog_allow_submission" => form_sanitizer($_POST['blog_allow_submission'], 0, "blog_allow_submission"),
        "blog_allow_submission_files" => form_sanitizer($_POST['blog_allow_submission_files'], 0, "blog_allow_submission_files"),
        "blog_extended_required" => isset($_POST['blog_extended_required']) ? 1 : 0,
        "blog_pagination" => form_sanitizer($_POST['blog_pagination'], 0, "blog_pagination"),
        "blog_image_link" => form_sanitizer($_POST['blog_image_link'], 0, 'blog_image_link'),
        "blog_thumb_ratio" => form_sanitizer($_POST['blog_thumb_ratio'], 0, 'blog_thumb_ratio'),
        "blog_thumb_w" => form_sanitizer($_POST['blog_thumb_w'], 300, 'blog_thumb_w'),
        "blog_thumb_h" => form_sanitizer($_POST['blog_thumb_h'], 150, 'blog_thumb_h'),
        "blog_photo_w" => form_sanitizer($_POST['blog_photo_w'], 400, 'blog_photo_w'),
        "blog_photo_h" => form_sanitizer($_POST['blog_photo_h'], 300, 'blog_photo_h'),
        "blog_photo_max_w" => form_sanitizer($_POST['blog_photo_max_w'], 1800, 'blog_photo_max_w'),
        "blog_photo_max_h" => form_sanitizer($_POST['blog_photo_max_h'], 1600, 'blog_photo_max_h'),
        "blog_photo_max_b" => form_sanitizer($_POST['calc_b'], 150, 'calc_b') * form_sanitizer($_POST['calc_c'], 100000, 'calc_c'),
        "blog_file_types" => form_sanitizer($_POST['blog_file_types'], "", "blog_file_types"),
    );
    if (defender::safe()) {
        foreach ($inputArray as $settings_name => $settings_value) {
            $inputSettings = array(
                "settings_name" => $settings_name, "settings_value" => $settings_value, "settings_inf" => "blog",
            );
            dbquery_insert(DB_SETTINGS_INF, $inputSettings, "update", array("primary_key" => "settings_name"));
        }
        addNotice("success", $locale['900']);
        redirect(FUSION_REQUEST);
    } else {
        addNotice('danger', $locale['901']);
    }
}
$opts = array('0' => $locale['952'], '1' => $locale['953']);
$cat_opts = array('0' => $locale['959'], '1' => $locale['960']);
$thumb_opts = array('0' => $locale['955'], '1' => $locale['956']);
$calc_opts = $locale['1020'];
$calc_c = calculate_byte($blog_settings['blog_photo_max_b']);
$calc_b = $blog_settings['blog_photo_max_b'] / $calc_c;

echo '<div class="m-t-10">';
echo '<h2>'.$locale['blog_settings'].'</h2>';

echo "<div class='well'>".$locale['blog_description']."</div>";
echo openform('settingsform', 'post', FUSION_REQUEST);
echo "<div class='row'>\n<div class='col-xs-12 col-sm-8'>\n";
openside('');
echo form_text('blog_pagination', $locale['669b'], $blog_settings['blog_pagination'], array(
    'inline' => TRUE, 'max_length' => 4, 'inner_width' => '150px', 'width' => '150px', 'type' => 'number'
));

echo "<div class='display-block overflow-hide'>
    <label class='control-label col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0' for='blog_thumb_w'>".$locale['601']."</label>
    <div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>
        ".form_text('blog_thumb_w', '', $blog_settings['blog_thumb_w'], array(
            'class' => 'pull-left', 'max_length' => 4, 'type' => 'number', 'width' => '150px'
        ))."
        <i class='fa fa-close pull-left m-r-5 m-l-5 m-t-10'></i>
        ".form_text('blog_thumb_h', '', $blog_settings['blog_thumb_h'], array(
            'class' => 'pull-left', 'max_length' => 4, 'type' => 'number', 'width' => '150px'
        ))."
        <small class='mid-opacity text-uppercase pull-left m-t-10 m-l-5'>(".$locale['604'].")</small>
    </div>
</div>";

echo "<div class='display-block overflow-hide'>
    <label class='control-label col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0' for='blog_photo_w'>".$locale['602']."</label>
    <div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>
        ".form_text('blog_photo_w', '', $blog_settings['blog_photo_w'], array(
            'class' => 'pull-left', 'max_length' => 4, 'type' => 'number', 'width' => '150px'
        ))."
        <i class='fa fa-close pull-left m-r-5 m-l-5 m-t-10'></i>
        ".form_text('blog_photo_h', '', $blog_settings['blog_photo_h'], array(
            'class' => 'pull-left', 'max_length' => 4, 'type' => 'number', 'width' => '150px'
        ))."
        <small class='mid-opacity text-uppercase pull-left m-t-10 m-l-5'>(".$locale['604'].")</small>
    </div>
</div>";

echo "<div class='display-block overflow-hide'>
    <label class='control-label col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0' for='blog_photo_max_w'>".$locale['603']."</label>
    <div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>
        ".form_text('blog_photo_max_w', '', $blog_settings['blog_photo_max_w'], array(
            'class' => 'pull-left', 'max_length' => 4, 'type' => 'number', 'width' => '150px'
        ))."
        <i class='fa fa-close pull-left m-r-5 m-l-5 m-t-10'></i>
        ".form_text('blog_photo_max_h', '', $blog_settings['blog_photo_max_h'], array(
            'class' => 'pull-left', 'max_length' => 4, 'type' => 'number', 'width' => '150px'
        ))."
        <small class='mid-opacity text-uppercase pull-left m-t-10 m-l-5'>(".$locale['604'].")</small>
    </div>
</div>";

echo "<div class='display-block overflow-hide'>
    <label class='control-label col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0' for='calc_b'>".$locale['605']."</label>
    <div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>
        ".form_text('calc_b', '', $calc_b, array(
            'required' => TRUE, 'type' => 'number', 'error_text' => $locale['error_rate'], 'width' => '100px', 'max_length' => 4,
            'class' => 'pull-left m-r-10'
        ))."
        ".form_select('calc_c', '', $calc_c, array(
            'options' => $calc_opts, 'placeholder' => $locale['choose'], 'class' => 'pull-left', 'inner_width' => '100%', 'width' => '180px'
        ))."
    </div>
</div>";

closeside();
openside("");
echo form_select('blog_allow_submission', $locale['blog_0600'], $blog_settings['blog_allow_submission'], array(
    "inline" => TRUE, "options" => array($locale['disable'], $locale['enable'])
));
echo form_select('blog_allow_submission_files', $locale['blog_0601'], $blog_settings['blog_allow_submission_files'], array(
    "inline" => TRUE, "options" => array($locale['disable'], $locale['enable'])
));
echo form_checkbox('blog_extended_required', $locale['blog_0602'], $blog_settings['blog_extended_required'], array('inline' => TRUE));
closeside();
echo "</div>\n";
echo "<div class='col-xs-12 col-sm-4'>\n";
openside('');
echo form_select('blog_image_link', $locale['951'], $blog_settings['blog_image_link'], array("options" => $opts, "width" => "100%"));
echo form_select('blog_thumb_ratio', $locale['954'], $blog_settings['blog_thumb_ratio'], array("options" => $thumb_opts, "width" => "100%"));
require_once INCLUDES."mimetypes_include.php";
$mime = mimeTypes();
$mime_opts = array();
foreach ($mime as $m => $Mime) {
    $ext = ".$m";
    $mime_opts[$ext] = $ext;
}
sort($mime_opts);
echo form_select('blog_file_types', $locale['961'], $blog_settings['blog_file_types'],
                 array(
                'options' => $mime_opts,
                'error_text' => $locale['error_type'],
                'placeholder' => $locale['choose'],
                'multiple' => TRUE,
                'tags' => TRUE,
                'width' => '100%',
                'delimiter' => '|'
            ));
closeside();
echo "</div></div>\n";
echo form_button('savesettings', $locale['750'], $locale['750'], array('class' => 'btn-success', 'icon' => 'fa fa-hdd-o'));
echo closeform();

echo '</div>';
