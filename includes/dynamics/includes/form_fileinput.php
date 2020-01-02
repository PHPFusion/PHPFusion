<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: form_fileinput.php
| Author: Frederick MC CHan (Chan)
| Credits: http://plugins.krajee.com/file-input
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

/**
 * @param            $input_name
 * @param string     $label
 * @param bool|FALSE $input_value
 * @param array      $options
 * if media is true, defender will check if any file uploaded. If no, select from media selection
 *
 * @return string
 */
function form_fileinput($input_name, $label = '', $input_value = FALSE, array $options = []) {
    $locale = fusion_get_locale();

    $title = $label ? stripinput($label) : ucfirst(strtolower(str_replace("_", " ", $input_name)));
    $input_name = (isset($input_name) && (!empty($input_name))) ? stripinput($input_name) : "";

    $template_choices = ['classic', 'modern', 'thumbnail'];

    $default_options = [
        'input_id'          => $input_name,
        'upload_path'       => IMAGES,
        'required'          => FALSE,
        'safemode'          => FALSE,
        'deactivate'        => FALSE,
        'preview_off'       => FALSE,
        'type'              => 'image', //// ['image', 'html', 'text', 'video', 'audio', 'flash', 'object', 'file']
        'width'             => '',
        'label'             => $locale['browse'],
        'inline'            => TRUE,
        'class'             => "",
        'tip'               => "",
        'ext_tip'           => "",
        'error_text'        => $locale['error_input_file'],
        'btn_class'         => 'btn-default',
        'icon'              => 'fa fa-upload',
        'jsonurl'           => FALSE,
        'dropzone'          => FALSE,
        'valid_ext'         => '.jpg,.png,.PNG,.JPG,.JPEG,.gif,.GIF,.bmp,.BMP',
        'thumbnail'         => FALSE,
        'thumbnail_w'       => 300,
        'thumbnail_h'       => 300,
        'thumbnail_folder'  => "",
        'thumbnail_ratio'   => 0,
        'thumbnail_suffix'  => '_t1',
        'thumbnail2'        => FALSE,
        'thumbnail2_w'      => 600,
        'thumbnail2_h'      => 400,
        'thumbnail2_suffix' => '_t2',
        'thumbnail2_ratio'  => 0,
        'delete_original'   => FALSE,
        'max_width'         => 1800,
        'max_height'        => 1600,
        'max_byte'          => 15728640,
        'max_count'         => 1,
        'multiple'          => FALSE,
        'template'          => 'classic',
        'media'             => FALSE,
        'placeholder'       => '',
        'form_id'           => '',
        'hide_upload'       => TRUE,
        'hide_remove'       => FALSE,
        'krajee_disabled'   => FALSE,
        'replace_upload'    => FALSE, // makes upload unique (i.e. overwrite instead of creating new)
    ];

    $options += $default_options;

    if (!is_dir($options['upload_path']) && !$options['jsonurl']) {
        $options['upload_path'] = IMAGES;
    }

    $options['thumbnail_folder'] = rtrim($options['thumbnail_folder'], "/");

    if (!in_array($options['template'], $template_choices)) {
        $options['template'] = "classic";
    }

    $options['input_id'] = trim(str_replace("[", "-", $options['input_id']), "]");

    $error_class = "";
    if (\defender::inputHasError($input_name)) {
        $error_class = "has-error ";
        if (!empty($options['error_text'])) {
            $new_error_text = \defender::getErrorText($input_name);
            if (!empty($new_error_text)) {
                $options['error_text'] = $new_error_text;
            }
            addNotice("danger", "<strong>$title</strong> - ".$options['error_text']);
        }
    }

    // default max file size
    $format = '';
    $browseLabel = $locale['df_300'];
    $type_for_js = NULL;
    if ($options['type']) {
        // file type if single filter, if not will accept as object if left empty.
        if (!stristr($options['type'], ',') && $options['type']) {
            if ($options['type'] == 'image') {
                $format = "image/*";
                $browseLabel = $locale['df_301'];
            } else if ($options['type'] == 'video') {
                $format = "video/*";
                $browseLabel = $locale['df_302'];
            } else if ($options['type'] == 'audio') {
                $format = "audio/*";
                $browseLabel = $locale['df_303'];
            }
        }
        $type_for_js = json_encode((array)$options['type']);
    }

    $html = "<div id='".$options['input_id']."-field' class='form-group ".($options['inline'] ? 'display-block overflow-hide ' : '').$error_class.$options['class']."' ".($options['width'] ? "style='width: ".$options['width']." !important;'" : '').">\n";
    $html .= ($label) ? "<label class='control-label ".($options['inline'] ? "col-xs-12 col-sm-3 col-md-3 col-lg-3" : '')."' for='".$options['input_id']."'>".$label.($options['required'] ? "<span class='required'>&nbsp;*</span>" : '')."
    ".($options['tip'] ? "<i class='pointer fa fa-question-circle' title='".$options['tip']."'></i>" : '')."
    </label>\n" : '';
    $html .= ($options['inline']) ? "<div class='col-xs-12 ".($label ? "col-sm-9 col-md-9 col-lg-9" : "col-sm-12")."'>\n" : "";
    $html .= "<input type='file'".($options['krajee_disabled'] == TRUE ? " class='form-control' " : "").($format ? " accept='".$format."'" : '')." name='".$input_name."' id='".$options['input_id']."' style='width:".$options['width']."' ".($options['deactivate'] ? 'readonly' : '')." ".($options['required'] ? 'required="required"' : '')." ".($options['multiple'] ? "multiple='1'" : '')." />\n";
    $html .= $options['ext_tip'] ? "<span class='tip'><i>".$options['ext_tip']."</i></span><br/>" : "";
    $html .= (\defender::inputHasError($input_name)) ? "<div id='".$options['input_id']."-help' class='label label-danger p-5 display-inline-block'>".$options['error_text']."</div>" : '';

    // Inserts Media Selector
    // Draw the framework first
    if ($options['media'] == TRUE) {
        $files_list = makefilelist($options['upload_path'], ".|..|index.php|", TRUE, 'files', 'psd|txt|md|php|exe|bat|pdf|js');
        $container_height = 300;
        $image_container_height = floor($container_height / 2.5);
        $html .= "<div id='".$options['input_id']."-media' class='panel panel-default spacer-sm'>";
        $html .= "<div class='panel-body'>\n";
        $html .= "<h5>".$locale['global_901']."</h5>";
        if (!empty($files_list)) {
            $html .= form_hidden($input_name."-mediaSelector", '', $input_value,
                ['input_id' => $options['input_id']."-mediaSelector"]);
            $html .= "<hr/>";
            $html .= "<div id='".$options['input_id']."-mediaContainer' class='row' style='max-height:".$container_height."px; overflow-y: scroll'>";
            foreach ($files_list as $files) {
                $html .= "<div class='col-xs-6 col-sm-3 clearfix text-center m-b-15'>\n";
                $html .= "<div class='media-container' title='$files' data-file='$files' style='height:".$image_container_height."px;'>\n";
                $html .= "<img class='center-y' style='margin: 0 auto;' src='".$options['upload_path'].$files."' alt='$files'/>";
                $html .= "</div>\n";
                $html .= "<small>$files</small>";
                $html .= "</div>\n";
            }
            $html .= "</div>\n";
            // single file selector only
            add_to_jquery("
                function mediaSelect() {
                    $('#".$options['input_id']."-media .media-container').bind('click', function(){
                        $('.media-container').removeClass('selected');
                        $(this).addClass('selected');
                        var current_folder = $('#".$options['input_id']."-mediaFolder').val();
                        var file_path = $(this).data('file');
                        $('#".$options['input_id']."-mediaSelector').val(file_path);
                    });
                }
                mediaSelect();
            ");
        }
        $html .= (\defender::inputHasError($input_name."-mediaSelector")) ? "<div id='".$options['input_id']."-mediaSelector' class='label label-danger p-5 display-inline-block'>".$options['error_text']."</div>" : "";

        $html .= "</div>\n";
        $html .= "</div>\n";
    }
    $html .= ($options['inline']) ? "</div>\n" : "";
    $html .= "</div>\n";

    \defender::getInstance()->add_field_session(
        [
            'input_name'        => trim($input_name, '[]'),
            'type'              => ((array)$options['type'] == ['image'] ? 'image' : 'file'),
            'title'             => $title,
            'id'                => $options['input_id'],
            'required'          => $options['required'],
            'safemode'          => $options['safemode'],
            'error_text'        => $options['error_text'],
            'path'              => $options['upload_path'],
            'thumbnail_folder'  => $options['thumbnail_folder'],
            'thumbnail'         => $options['thumbnail'],
            'thumbnail_suffix'  => $options['thumbnail_suffix'],
            'thumbnail_w'       => $options['thumbnail_w'],
            'thumbnail_h'       => $options['thumbnail_h'],
            'thumbnail_ratio'   => $options['thumbnail_ratio'],
            'thumbnail2'        => $options['thumbnail2'],
            'thumbnail2_w'      => $options['thumbnail2_w'],
            'thumbnail2_h'      => $options['thumbnail2_h'],
            'thumbnail2_suffix' => $options['thumbnail2_suffix'],
            'thumbnail2_ratio'  => $options['thumbnail2_ratio'],
            'delete_original'   => $options['delete_original'],
            'max_width'         => $options['max_width'],
            'max_height'        => $options['max_height'],
            'max_count'         => $options['max_count'],
            'max_byte'          => $options['max_byte'],
            'multiple'          => $options['multiple'],
            'valid_ext'         => $options['valid_ext'],
            'replace_upload'    => $options['replace_upload'],
        ]
    );


    if ($options['krajee_disabled'] === FALSE) {
        $browseLabel = $options['placeholder'] ?: $browseLabel;
        $value = "";
        if (!empty($input_value)) {
            if (is_array($input_value)) {
                $value = [];
                foreach ($input_value as $c_value) {
                    $value[] = (file_exists($options['upload_path'].$c_value) ? $options['upload_path'].$c_value : $c_value);
                }
            } else {
                $value = (file_exists($options['upload_path'].$input_value) ? $options['upload_path'].$input_value : $input_value);
            }
            $value = json_encode($value);
        }

        $lang = file_exists(DYNAMICS.'assets/fileinput/js/locales/'.$locale['short_lang_name'].'.js') ? 'language: "'.$locale['short_lang_name'].'",' : '';

        $extra_data_js = "";
        if ($options['form_id'] && $options['jsonurl']) {
            $extra_data_js = "
                uploadExtraData: function() {
                    var inputs = $('#".$options['form_id']." :input');
                    var obj = $.map(inputs, function(x, y) {
                        return {
                            Key: x.name,
                            Value: $(x).val()
                        };
                    });
                    return obj;
                },
            ";
        }
        if ($options['media']) {
            \defender::getInstance()->add_field_session(
                [
                    'input_name' => $input_name."-mediaSelector",
                    'title'      => trim($title, '[]'),
                    'id'         => $options['input_id']."-mediaSelector",
                    'type'       => 'mediaSelect',
                    'path'       => $options['upload_path'],
                    'required'   => $options['required'],
                    'safemode'   => $options['safemode'],
                ]
            );
        }

        switch ($options['template']) {
            case "classic":
                add_to_jquery("
                    $('#".$options['input_id']."').fileinput({
                        allowedFileTypes: ".$type_for_js.",
                        allowedPreviewTypes : ".$type_for_js.",
                        ".($value ? "initialPreview: ".$value.", " : '')."
                        ".($options['preview_off'] ? "showPreview: false, " : '')."
                        initialPreviewAsData: true,
                        browseClass: 'btn ".$options['btn_class']." button',
                        uploadClass: 'btn btn-default button',
                        captionClass : '',
                        maxFileCount: '".$options['max_count']."',
                        removeClass : 'btn ".$options['btn_class']." button',
                        browseLabel: '".$browseLabel."',
                        browseIcon: '<i class=\"".$options['icon']." m-r-10\"></i>',
                        ".($options['jsonurl'] ? "uploadUrl : '".$options['jsonurl']."'," : '')."
                        ".($options['hide_upload'] ? 'showUpload: false,' : '')."
                        ".($options['hide_remove'] ? 'showRemove: false,' : '')."
                        dropZoneEnabled: ".($options['dropzone'] ? "true" : "false").",
                        $extra_data_js
                        ".$lang."
                    });
                ");
                break;
            case "modern":
                add_to_jquery("
                    $('#".$options['input_id']."').fileinput({
                        allowedFileTypes: ".$type_for_js.",
                        allowedPreviewTypes : ".$type_for_js.",
                        ".($value ? "initialPreview: ".$value.", " : '')."
                        initialPreviewAsData: true,
                        ".($options['preview_off'] ? "showPreview: false, " : '')."
                        browseClass: 'btn btn-modal btn-lg',
                        uploadClass: 'btn btn-modal btn-lg',
                        captionClass : '',
                        maxFileCount: '".$options['max_count']."',
                        removeClass : 'btn button',
                        browseLabel: '".$browseLabel."',
                        browseIcon: '<i class=\"".$options['icon']."\"></i>',
                        showCaption: false,
                        showRemove: false,
                        ".($options['jsonurl'] ? "uploadUrl : '".$options['jsonurl']."'," : '')."
                        dropZoneEnabled: ".($options['dropzone'] ? "true" : "false").",
                        ".($options['hide_upload'] ? 'showUpload: false,' : '')."
                        ".($options['hide_remove'] ? 'showRemove: false,' : '')."
                        $extra_data_js
                        layoutTemplates: {
                            main2: '<div class=\"btn-photo-upload btn-link\">'+' {browse}'+' </div></span></div> {preview}',
                        },
                        ".$lang."
                    });
                ");
                break;
            case "thumbnail":
                add_to_jquery("
                    $('#".$options['input_id']."').fileinput({
                        allowedFileTypes: ".$type_for_js.",
                        allowedPreviewTypes : ".$type_for_js.",
                        ".($value ? "initialPreview: ".$value.", " : '')."
                        ".($options['preview_off'] ? "showPreview: false, " : '')."
                        initialPreviewAsData: true,
                        defaultPreviewContent: '<img class=\"img-responsive\" src=\"".IMAGES."no_photo.png\" alt=\"".$browseLabel."\" style=\"width:100%;\">',
                        browseClass: 'btn btn-block btn-default',
                        uploadClass: 'btn btn-modal',
                        captionClass : '',
                        maxFileCount: '".$options['max_count']."',
                        removeClass : 'btn button',
                        browseLabel: '".$browseLabel."',
                        browseIcon: '<i class=\"".$options['icon']."\"></i>',
                        showCaption: false,
                        showRemove: false,
                        ".($options['jsonurl'] ? "uploadUrl : '".$options['jsonurl']."'," : '')."
                        ".($options['hide_upload'] ? 'showUpload: false,' : '')."
                        ".($options['hide_remove'] ? 'showRemove: false,' : '')."
                        dropZoneEnabled: ".($options['dropzone'] ? "true" : "false").",
                        $extra_data_js
                        layoutTemplates: {
                            main2: '<div class=\"panel panel-default\">' + '{preview}' + '<div class=\"panel-body\">' + ' {browse}' + '</div></div>',
                        },
                        ".$lang."
                    });
                ");
                break;
        }

        if (!defined('form_fileinput')) {
            add_to_head("<link href='".DYNAMICS."assets/fileinput/css/fileinput.min.css' media='all' rel='stylesheet' type='text/css' />");
            if ($locale['text-direction'] == 'rtl') {
                add_to_head("<link href='".DYNAMICS."assets/fileinput/css/fileinput-rtl.min.css' media='all' rel='stylesheet' type='text/css' />");
            }
            add_to_footer("<script src='".DYNAMICS."assets/fileinput/js/fileinput.min.js' type='text/javascript'></script>");

            if (file_exists(DYNAMICS.'assets/fileinput/js/locales/'.$locale['short_lang_name'].'.js')) {
                add_to_footer("<script src='".DYNAMICS."assets/fileinput/js/locales/".$locale['short_lang_name'].".js' type='text/javascript'></script>");
                //$lang = 'language: "'.$locale['short_lang_name'].'",';
            }
            define('form_fileinput', TRUE);
        }

    }

    return (string)$html;
}
