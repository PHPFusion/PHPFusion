<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: gallery_settings.php
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
defined('IN_FUSION') || exit;

pageAccess("PH");
$locale = fusion_get_locale('', LOCALE.LOCALESET."admin/settings.php");

if (isset($_POST['delete_watermarks'])) {
    $result = dbquery("SELECT album_id,photo_filename FROM ".DB_PHOTOS." ORDER BY album_id, photo_id");
    $rows = dbrows($result);
    if ($rows) {
        $parts = [];
        $watermark1 = "";
        $watermark2 = "";
        $photodir = "";
        while ($data = dbarray($result)) {
            $parts = explode(".", $data['photo_filename']);
            $watermark1 = $parts[0]."_w1.".$parts[1];
            $watermark2 = $parts[0]."_w2.".$parts[1];
            $photodir = IMAGES_G;
            if (file_exists($photodir.$watermark1)) {
                unlink($photodir.$watermark1);
            }
            if (file_exists($photodir.$watermark2)) {
                unlink($photodir.$watermark2);
            }
            unset($parts);
        }
        redirect(FUSION_REQUEST);
    } else {
        redirect(FUSION_REQUEST);
    }
} else {
    if (isset($_POST['savesettings'])) {

        $inputArray = [
            'thumb_w'                     => form_sanitizer($_POST['thumb_w'], 200, 'thumb_w'),
            'thumb_h'                     => form_sanitizer($_POST['thumb_h'], 200, 'thumb_h'),
            'photo_w'                     => form_sanitizer($_POST['photo_w'], 800, 'photo_w'),
            'photo_h'                     => form_sanitizer($_POST['photo_h'], 800, 'photo_h'),
            'photo_max_w'                 => form_sanitizer($_POST['photo_max_w'], 2400, 'photo_max_w'),
            'photo_max_h'                 => form_sanitizer($_POST['photo_max_h'], 1800, 'photo_max_h'),
            'photo_max_b'                 => form_sanitizer($_POST['calc_b'], 2097152, 'calc_b') * form_sanitizer($_POST['calc_c'], 1, 'calc_c'),
            'gallery_pagination'          => form_sanitizer($_POST['gallery_pagination'], 24, 'gallery_pagination'),
            'photo_watermark'             => form_sanitizer($_POST['photo_watermark'], 0, 'photo_watermark'),
            'photo_watermark_save'        => isset($_POST['photo_watermark_save']) ? 1 : 0,
            'photo_watermark_image'       => isset($_POST['photo_watermark_image']) ? form_sanitizer($_POST['photo_watermark_image'], '', 'photo_watermark_image') : IMAGES_G.'watermark.png',
            'photo_watermark_text'        => isset($_POST['photo_watermark_text']) ? 1 : 0,
            'photo_watermark_text_color1' => isset($_POST['photo_watermark_text_color1']) ? form_sanitizer($_POST['photo_watermark_text_color1'], 'FF6600', 'photo_watermark_text_color1') : 'FF6600',
            'photo_watermark_text_color2' => isset($_POST['photo_watermark_text_color2']) ? form_sanitizer($_POST['photo_watermark_text_color2'], 'FFFF00', 'photo_watermark_text_color2') : 'FFFF00',
            'photo_watermark_text_color3' => isset($_POST['photo_watermark_text_color3']) ? form_sanitizer($_POST['photo_watermark_text_color3'], 'FFFFFF', 'photo_watermark_text_color3') : 'FFFFFF',
            'gallery_allow_submission'    => isset($_POST['gallery_allow_submission']) ? 1 : 0,
            'gallery_extended_required'   => isset($_POST['gallery_extended_required']) ? 1 : 0,
            'gallery_file_types'          => form_sanitizer($_POST['gallery_file_types'], '.pdf,.gif,.jpg,.png,.svg,.zip,.rar,.tar,.bz2,.7z', 'gallery_file_types'),
        ];
        if (\defender::safe()) {
            foreach ($inputArray as $settings_name => $settings_value) {
                $inputSettings = [
                    'settings_name'  => $settings_name,
                    'settings_value' => $settings_value,
                    'settings_inf'   => 'gallery',
                ];
                dbquery_insert(DB_SETTINGS_INF, $inputSettings, 'update', ['primary_key' => 'settings_name']);
            }
            addNotice('success', $locale['900']);
            redirect(FUSION_REQUEST);
        } else {
            addNotice('danger', $locale['901']);
        }
    }
}

$choice_opts = ['1' => $locale['yes'], '0' => $locale['no']];
$calc_opts = $locale['1020'];
$calc_c = calculate_byte($gll_settings['photo_max_b']);
$calc_b = $gll_settings['photo_max_b'] / $calc_c;

echo "<div class='m-t-20'>";
echo "<h2>".$locale['photo_settings']."</h2><hr/>";
echo "<div class='well'>".$locale['gallery_0022']."</div>";
echo openform('settingsform', 'post', FUSION_REQUEST, ['class' => 'spacer-sm']);
echo "<div class='row'>\n<div class='col-xs-12 col-sm-8'>\n";
echo "<div class='spacer-sm'>";
echo "<div class='row'>\n<div class='col-xs-12 col-sm-3'>\n";
echo "<h4 class='m-0'>".$locale['gallery_0220']."</h4><i>".$locale['gallery_0221']."</i>\n<br/><br/>";
echo "</div><div class='col-xs-12 col-sm-9'>\n";

echo "<div class='display-block overflow-hide'>
    <label class='control-label col-xs-12 col-sm-3 col-md-3 col-lg-3' for='news_thumb_w'>".$locale['gallery_0203']."</label>
    <div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>
    ".form_text('thumb_w', '', $gll_settings['thumb_w'], [
        'class'         => 'pull-left m-r-10',
        'max_length'    => 4,
        'type'          => 'number',
        'width'         => '170px',
        'prepend'       => TRUE,
        'prepend_value' => $locale['gallery_0222']
    ]).
    form_text('thumb_h', '', $gll_settings['thumb_h'], [
        'class'         => 'pull-left',
        'max_length'    => 4,
        'type'          => 'number',
        'width'         => '170px',
        'prepend'       => TRUE,
        'prepend_value' => $locale['gallery_0223']
    ])."
    </div>
</div>";

echo "<div class='display-block overflow-hide'>
    <label class='control-label col-xs-12 col-sm-3 col-md-3 col-lg-3' for='news_thumb_w'>".$locale['gallery_0205']."</label>
    <div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>
    ".form_text('photo_w', '', $gll_settings['photo_w'], [
        'class'         => 'pull-left m-r-10',
        'max_length'    => 4,
        'type'          => 'number',
        'width'         => '170px',
        'prepend'       => TRUE,
        'prepend_value' => $locale['gallery_0222']
    ]).
    form_text('photo_h', '', $gll_settings['photo_h'], [
        'class'         => 'pull-left',
        'max_length'    => 4,
        'type'          => 'number',
        'width'         => '170px',
        'prepend'       => TRUE,
        'prepend_value' => $locale['gallery_0223']
    ])."
    </div>
</div>";

echo "<div class='display-block overflow-hide'>
    <label class='control-label col-xs-12 col-sm-3 col-md-3 col-lg-3' for='news_thumb_w'>".$locale['gallery_0206']."</label>
    <div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>
    ".form_text('photo_max_w', '', $gll_settings['photo_max_w'], [
        'class'         => 'pull-left m-r-10',
        'max_length'    => 4,
        'type'          => 'number',
        'width'         => '170px',
        'prepend'       => TRUE,
        'prepend_value' => $locale['gallery_0222']
    ]).
    form_text('photo_max_h', '', $gll_settings['photo_max_h'], [
        'class'         => 'pull-left',
        'max_length'    => 4,
        'type'          => 'number',
        'width'         => '170px',
        'prepend'       => TRUE,
        'prepend_value' => $locale['gallery_0223']
    ])."
    </div>
</div>";

echo "<div class='display-block overflow-hide'>
    <label class='control-label col-xs-12 col-sm-3 col-md-3 col-lg-3' for='calc_b'>".$locale['gallery_0207']."</label>
    <div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>
    ".form_text('calc_b', '', $calc_b, [
        'required'   => TRUE,
        'type'       => 'number',
        'error_text' => $locale['error_rate'],
        'width'      => '100px',
        'max_length' => 4,
        'number_min' => 1,
        'class'      => 'pull-left m-r-10'
    ])."
    ".form_select('calc_c', '', $calc_c, [
        'options'     => $calc_opts,
        'placeholder' => $locale['choose'],
        'width'       => '180px',
        'inner_width' => '100%',
        'class'       => 'pull-left'
    ])."
    </div>
</div>";
echo "</div>\n</div>\n";
echo "</div>\n";
echo "<hr/>\n";

// default Settings
echo "<div class='spacer-sm'>\n";
echo "<div class='row'>\n<div class='col-xs-12 col-sm-3'>\n";
echo "<h4 class='m-0'>".$locale['gallery_0218']."</h4><i>".$locale['gallery_0219']."</i>\n<br/><br/>";
echo "</div><div class='col-xs-12 col-sm-9'>\n";
echo "<div class='display-block overflow-hide'>
    ".form_text('gallery_pagination', $locale['gallery_0202'], $gll_settings['gallery_pagination'], [
        'inline'      => TRUE,
        'max_length'  => 2,
        'width'       => '100px',
        'type'        => 'number',
        'inner_width' => '150px'
    ])."
</div>";
echo "<div class='display-block overflow-hide'>
    ".form_checkbox("gallery_allow_submission", $locale['gallery_0200'], $gll_settings['gallery_allow_submission'], ['inline' => TRUE])."
</div>";
echo "<div class='display-block overflow-hide'>
    ".form_checkbox("gallery_extended_required", $locale['gallery_0201'], $gll_settings['gallery_extended_required'], ['inline' => TRUE])."
</div>";

echo "</div>\n</div>\n";
echo "</div>\n";

echo "</div>\n<div class='col-xs-9 col-xs-offset-3 col-sm-9 col-sm-offset-3 col-md-4 col-md-offset-0 col-lg-4'>\n";

openside("");
echo form_select('photo_watermark', $locale['gallery_0214'], $gll_settings['photo_watermark'], [
    'options'     => ['0' => $locale['disable'], '1' => $locale['enable']],
    'width'       => '100%',
    'inner_width' => '100%'
]);
echo form_checkbox('photo_watermark_text', $locale['gallery_0213'], $gll_settings['photo_watermark_text']);
echo form_checkbox('photo_watermark_save', $locale['gallery_0215'], $gll_settings['photo_watermark_save']);
echo form_text('photo_watermark_image', $locale['gallery_0212'], $gll_settings['photo_watermark_image'], [
    'deactivate' => !$gll_settings['photo_watermark'] ? 1 : 0,
]);
echo form_colorpicker('photo_watermark_text_color1', $locale['gallery_0208'], $gll_settings['photo_watermark_text_color1'], [
    'deactivate' => !$gll_settings['photo_watermark'] ? 1 : 0,
]);
echo form_colorpicker('photo_watermark_text_color2', $locale['gallery_0209'], $gll_settings['photo_watermark_text_color2'], [
    'deactivate' => !$gll_settings['photo_watermark'] ? 1 : 0,
]);
echo form_colorpicker('photo_watermark_text_color3', $locale['gallery_0210'], $gll_settings['photo_watermark_text_color3'], [
    'deactivate' => !$gll_settings['photo_watermark'] ? 1 : 0,
]);
require_once INCLUDES."mimetypes_include.php";
$mime = mimeTypes();
$mime_opts = [];
foreach ($mime as $m => $Mime) {
    $ext = ".$m";
    $mime_opts[$ext] = $ext;
}
sort($mime_opts);
echo form_select('gallery_file_types', $locale['gallery_0217'], $gll_settings['gallery_file_types'],
    [
        'options'     => $mime_opts,
        'error_text'  => $locale['error_type'],
        'placeholder' => $locale['choose'],
        'multiple'    => TRUE,
        'tags'        => TRUE,
        'width'       => '100%',
        'inner_width' => '100%',
        'delimiter'   => '|'
    ]);
closeside();
echo "</div>\n</div>\n";
echo form_button('savesettings', $locale['gallery_0216'], $locale['gallery_0216'], ['class' => 'btn-success', 'icon' => 'fa fa-hdd-o']);
echo form_button('delete_watermarks', $locale['gallery_0211'], $locale['gallery_0211'], [
    'deactivate' => !$gll_settings['photo_watermark'] ? 1 : 0, 'class' => 'm-l-5 btn-danger', 'icon' => 'fa fa-trash'
]);
echo closeform();
        echo '</div>';
add_to_jquery("
    $('#photo_watermark').bind('change', function(){
    var vals = $(this).select2().val();
    if (vals == 1) {
        $('#photo_watermark_save').select2('enable');
        $('#delete_watermarks').removeAttr('disabled');
        $('#photo_watermark_image').removeAttr('disabled');
        $('#photo_watermark_text').select2('enable');
        $('#photo_watermark_text_color1').colorpicker('enable');
        $('#photo_watermark_text_color2').colorpicker('enable');
        $('#photo_watermark_text_color3').colorpicker('enable');
    } else {
        $('#photo_watermark_save').select2('disable');
        $('#delete_watermarks').attr('disabled', 'disabled');
        $('#photo_watermark_image').attr('disabled', 'disabled');
        $('#photo_watermark_text').select2('disable');
        $('#photo_watermark_text_color1').colorpicker('disable');
        $('#photo_watermark_text_color2').colorpicker('disable');
        $('#photo_watermark_text_color3').colorpicker('disable');
    }
    });
");

function color_mapper($field, $value) {
    global $gll_settings;
    $cvalue[] = "00";
    $cvalue[] = "33";
    $cvalue[] = "66";
    $cvalue[] = "99";
    $cvalue[] = "CC";
    $cvalue[] = "FF";

    $select = "<select name='".$field."' class='textbox' onchange=\"document.getElementById('preview_".$field."').style.background = '#' + this.options[this.selectedIndex].value;\" ".(!$gll_settings['photo_watermark'] ? "disabled='disabled'" : "").">\n";
    for ($ca = 0; $ca < count($cvalue); $ca++) {
        for ($cb = 0; $cb < count($cvalue); $cb++) {
            for ($cc = 0; $cc < count($cvalue); $cc++) {
                $hcolor = $cvalue[$ca].$cvalue[$cb].$cvalue[$cc];
                $select .= "<option value='".$hcolor."'".($value == $hcolor ? " selected='selected' " : " ")." style='background-color:#".$hcolor.";'>#".$hcolor."</option>\n";
            }
        }
    }
    $select .= "</select>\n";

    return $select;
}
