<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: admin/download_settings.php
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
require_once INCLUDES."mimetypes_include.php";
$locale = fusion_get_locale();
if (isset($_POST['savesettings'])) {
    // redo this part
    $StoreArray = array(
        "download_max_b" => form_sanitizer($_POST['calc_b'], 1, "calc_b") * form_sanitizer($_POST['calc_c'], 1500000, "calc_c"),
        "download_types" => form_sanitizer($_POST['download_types'], "", "download_types"),
        "download_screen_max_w" => form_sanitizer($_POST['download_screen_max_w'], 500, "download_screen_max_w"),
        "download_screen_max_h" => form_sanitizer($_POST['download_screen_max_h'], 500, "download_screen_max_h"),
        "download_screen_max_b" => form_sanitizer($_POST['calc_bb'], 1, "calc_bb") * form_sanitizer($_POST['calc_cc'], 1500000, "calc_cc"),
        "download_thumb_max_h" => form_sanitizer($_POST['download_thumb_max_h'], 500, 'download_thumb_max_h'),
        "download_thumb_max_w" => form_sanitizer($_POST['download_thumb_max_w'], 500, 'download_thumb_max_w'),
        "download_screenshot" => form_sanitizer($_POST['download_screenshot'], 0, 'download_screenshot'),
        "download_stats" => form_sanitizer($_POST['download_stats'], 0, 'download_stats'),
        "download_pagination" => form_sanitizer($_POST['download_pagination'], 12, 'download_pagination'),
        "download_allow_submission" => form_sanitizer($_POST['download_allow_submission'], "", "download_allow_submission"),
        "download_screenshot_required" => isset($_POST['download_screenshot_required']) ? TRUE : FALSE,
        "download_extended_required" => isset($_POST['download_extended_required']) ? TRUE : FALSE,
    );
    if ($defender->safe()) {
        foreach ($StoreArray as $key => $value) {
            $result = NULL;
            if ($defender->safe()) {
                $Array = array("settings_name" => $key, "settings_value" => $value, "settings_inf" => "downloads");
                dbquery_insert(DB_SETTINGS_INF, $Array, 'update', array("primary_key" => "settings_name"));
            }
        }
        addNotice('success', $locale['900']);
    } else {
        // send message your settings was not safe.
        addNotice('danger', $locale['901']);
        addNotice('danger', $locale['696']);
        addNotice('danger', $locale['900']);
    }
    redirect(FUSION_SELF.$aidlink."&amp;section=download_settings");
}

$calc_opts = $locale['1020'];
$calc_c = calculate_byte($dl_settings['download_max_b']);
$calc_b = $dl_settings['download_max_b'] / $calc_c;
$calc_cc = calculate_byte($dl_settings['download_screen_max_b']);
$calc_bb = $dl_settings['download_screen_max_b'] / $calc_cc;
$choice_opts = ['1' => $locale['yes'], '0' => $locale['no']];
$mime = mimeTypes();
$mime_opts = array();
foreach ($mime as $m => $Mime) {
    $ext = ".$m";
    $mime_opts[$ext] = $ext;
}
echo "<div class='well m-t-10'>".$locale['download_description']."</div>";
echo openform('settingsform', 'post', FUSION_REQUEST);
echo "<div class='row'>\n<div class='col-xs-12 col-sm-8'>\n";
openside("");
echo form_text('download_pagination', $locale['939'], $dl_settings['download_pagination'], [
    'max_length'  => 4,
    'type'        => 'number',
    'inline'      => TRUE,
    'inner_width' => '150px',
    'width'       => '150px'
]);
closeside();
openside('');
echo "<div class='display-block overflow-hide'>
    <label class='control-label col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0' for='photo_w'>".$locale['934']."</label>
    <div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>
    ".form_text('download_screen_max_w', '', $dl_settings['download_screen_max_w'], [
        'class'      => 'pull-left',
        'max_length' => 4,
        'type'       => 'number',
        'width'      => '150px'
    ])."
        <i class='fa fa-close pull-left m-r-5 m-l-5 m-t-10'></i>
    ".form_text('download_screen_max_h', '', $dl_settings['download_screen_max_h'], [
        'class'      => 'pull-left',
        'max_length' => 4,
        'type'       => 'number',
        'width'      => '150px'
    ])."
        <small class='mid-opacity text-uppercase pull-left m-t-10 m-l-5'>(".$locale['604'].")</small>
    </div>\n</div>";
echo "<div class='display-block overflow-hide'>
    <label class='control-label col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0' for='photo_w'>".$locale['937']."</label>
    <div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>
    ".form_text('download_thumb_max_w', '', $dl_settings['download_thumb_max_w'], [
        'class'      => 'pull-left',
        'max_length' => 4,
        'type'       => 'number',
        'width'      => '150px'
    ])."
        <i class='fa fa-close pull-left m-r-5 m-l-5 m-t-10'></i>
    ".form_text('download_thumb_max_h', '', $dl_settings['download_thumb_max_h'], [
        'class'      => 'pull-left',
        'max_length' => 4,
        'type'       => 'number',
        'width'      => '150px'
    ])."
        <small class='mid-opacity text-uppercase pull-left m-t-10 m-l-5'>(".$locale['604'].")</small>
    </div>
</div>";

echo "<div class='display-block overflow-hide'>
    <label class='control-label col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0' for='calc_b'>".$locale['930']."</label>
    <div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>
    ".form_text('calc_b', '', $calc_b, [
        'required'   => TRUE,
        'type'       => 'number',
        'inline'     => TRUE,
        'error_text' => $locale['error_rate'],
        'width'      => '100px',
        'max_length' => 4,
        'class'      => 'pull-left m-r-10'
    ])."
    ".form_select('calc_c', '', $calc_c, [
        'options'     => $calc_opts,
        'placeholder' => $locale['choose'],
        'class'       => 'pull-left',
        'inner_width' => '100%',
        'width'       => '180px'
    ])."
    </div>
</div>
";

echo "<div class='display-block overflow-hide'>
    <label class='control-label col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0' for='calc_bb'>".$locale['936']."</label>
    <div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>
    ".form_text('calc_bb', '', $calc_bb, [
        'required'   => TRUE,
        'type'       => 'number',
        'error_text' => $locale['error_rate'],
        'width'      => '100px',
        'max_length' => 4,
        'class'      => 'pull-left m-r-10'
    ])."
    ".form_select('calc_cc', '', $calc_cc, [
        'options'     => $calc_opts,
        'placeholder' => $locale['choose'],
        'class'       => 'pull-left',
        'inner_width' => '100%',
        'width'       => '180px'
    ])."
    </div>
</div>";
closeside();
openside('');
echo form_select('download_allow_submission', $locale['download_0046'], $dl_settings['download_allow_submission'], [
    'inline' => TRUE, 'options' => [$locale['disable'], $locale['enable']]]);
echo form_checkbox('download_screenshot_required', $locale['download_0047'], $dl_settings['download_screenshot_required'], ['inline' => TRUE]);
echo form_checkbox('download_extended_required', $locale['download_0048'], $dl_settings['download_extended_required'], ['inline' => TRUE]);
closeside();


echo "</div><div class='col-xs-12 col-sm-4'>\n";
openside('');
echo form_select('download_screenshot', $locale['938'], $dl_settings['download_screenshot'], ['options' => $choice_opts]);
echo form_select('download_stats', $locale['940'], $dl_settings['download_stats'], ['options' => $choice_opts]);
closeside();
openside();
echo form_select('download_types[]', $locale['932'], $dl_settings['download_types'], [
    'options'     => $mime_opts,
    'input_id'    => 'dltype',
    'error_text'  => $locale['error_type'],
    'placeholder' => $locale['choose'],
    'multiple'    => TRUE,
    'tags'        => TRUE,
    'width'       => '100%',
    'delimiter'   => '|'
]);

closeside();
echo "</div>\n</div>\n";
echo form_button('savesettings', $locale['750'], $locale['750'], ['class' => 'btn-success', 'icon' => 'fa fa-hdd-o']);
echo closeform();

add_to_jquery("
$('#shortdesc_display').show();
$('#calc_upload').bind('click', function() {
    if ($('#calc_upload').attr('checked')) {
        $('#download_filesize').attr('readonly', 'readonly');
        $('#download_filesize').val('');
    } else {
       $('#download_filesize').removeAttr('readonly');
    }
});
");
