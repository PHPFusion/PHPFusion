<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
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
 * Generates a file upload input.
 *
 * @param string $input_name  Name of the input, by default it's also used as the ID for the input.
 * @param string $label       Input label.
 * @param bool   $input_value The value to be displayed.
 * @param array  $options
 *
 * @return string
 */
function form_fileinput($input_name, $label = '', $input_value = FALSE, array $options = []) {
    $locale = fusion_get_locale();

    $title = $label ? stripinput($label) : ucfirst(strtolower(str_replace("_", " ", $input_name)));

    $input_name = (isset($input_name) && (!empty($input_name))) ? stripinput($input_name) : "";

    $input_value = clean_input_value($input_value);

    $template_choices = ['classic', 'modern', 'thumbnail'];

    $default_options = [
        'input_id'          => $input_name,
        'upload_path'       => IMAGES, // The upload path for the file(s).
        'required'          => FALSE, // Whether this field is required during form submission.
        'safemode'          => FALSE, // Extra security settings such as strict type GD2 checks, and other validation during upload.
        'deactivate'        => FALSE, // Disable the input and set it as readonly.
        'preview_off'       => FALSE,
        'type'              => 'image', // Possible value: image, html, text, video, audio, flash, object, file
        'width'             => '', // Accepts px or % values.
        'label'             => $locale['browse'],
        'inline'            => TRUE,
        'class'             => "", // The input container wrapper class.
        'tip'               => "", // Displays a tip by the label.
        'ext_tip'           => "", // Displays a tip at the bottom of the input.
        'error_text'        => $locale['error_input_file'],
        'btn_class'         => 'btn-default',
        'icon'              => 'fa fa-upload',
        'jsonurl'           => FALSE,
        'dropzone'          => FALSE,
        'valid_ext'         => '.jpg,.png,.PNG,.JPG,.JPEG,.gif,.GIF,.bmp,.BMP',
        'thumbnail'         => FALSE, // Set to true to create primary thumbnail.
        'thumbnail_w'       => 300, // The width of the primary thumbnail.
        'thumbnail_h'       => 300, // The height of the primary thumbnail.
        'thumbnail_folder'  => "", // The path to the primary thumnail storage.
        'thumbnail_ratio'   => 0, // Keep original ratio or forced square dimension (0 - original, 1 - square). Possible value: 0, 1
        'thumbnail_suffix'  => '_t1', // Adds a suffix to primary thumbnail filename.
        'thumbnail2'        => FALSE, // Set to true to create secondary thumbnail.
        'thumbnail2_w'      => 600, // The width of the secondary thumbnail.
        'thumbnail2_h'      => 400, // The height of the secondary thumbnail.
        'thumbnail2_suffix' => '_t2', // Adds a suffix to secondary thumbnail filename.
        'thumbnail2_ratio'  => 0, // Keep original ratio or forced square dimension (0 - original, 1 - square). Possible value: 0, 1
        'delete_original'   => FALSE, // This is used to delete the uploaded file. It can be used along with thumbnail creation where you can set this parameter to true to keep only the thumbnail.
        'max_width'         => 1800, // Defines a maximum alloweable image width. Only takes effect if type is set to image.
        'max_height'        => 1600, // Defines a maximum alloweable image height. Only takes effect if type is set to image.
        'max_byte'          => 15728640, // Defines a maximum alloweable image size. Only takes effect if type is set to image.
        'max_count'         => 1, // Sets a minimum alloweable file selection count per instance. Declare a new max_count to 10 to allow user to select 10 files.
        'multiple'          => FALSE, // Whether the current fileinput allows multiple files selection per instance.
        'template'          => 'classic', // Customize HTML output of the widget. Possible value: classic, modern, thumbnail
        'media'             => FALSE, // Displays a file media browser selector to allow user to select files within the upload_path to pick on. If is true, defender will check if any file uploaded. If no, select from media selection.
        'placeholder'       => '', // A placeholder for the field.
        'form_id'           => '', // The current <form> element id that this widget is placed in.
        'hide_upload'       => TRUE, // Show or hide an upload file button when file has been selected.
        'hide_remove'       => FALSE, // Show or hide a remove file button when file has been selected.
        'krajee_disabled'   => FALSE, // Disables Kartik Bootstrap Jquery plugin and shows a normal browser fileinput instead.
        'replace_upload'    => FALSE, // Change the upload name to a new unique name upon successful upload.
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
    if (\Defender::inputHasError($input_name)) {
        $error_class = "has-error ";
        if (!empty($options['error_text'])) {
            $new_error_text = \Defender::getErrorText($input_name);
            if (!empty($new_error_text)) {
                $options['error_text'] = $new_error_text;
            }
            addnotice("danger", $options['error_text']);
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

    $html = "<div id='".$options['input_id']."-field' class='form-group ".($options['inline'] && $label ? 'row ' : '').$error_class.$options['class']."'".($options['width'] ? " style='width: ".$options['width']." !important;'" : '').">\n";
    $html .= ($label) ? "<label class='control-label ".($options['inline'] ? "col-xs-12 col-sm-3 col-md-3 col-lg-3" : '')."' for='".$options['input_id']."'>".$label.($options['required'] ? "<span class='required'>&nbsp;*</span>" : '')."
    ".($options['tip'] ? "<i class='pointer fa fa-question-circle' title='".$options['tip']."'></i>" : '')."
    </label>\n" : '';
    $html .= $options['inline'] && $label ? "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n" : "";
    $html .= "<input type='file'".($options['krajee_disabled'] == TRUE ? " class='form-control' " : "").($format ? " accept='".$format."'" : '')." name='".$input_name."' id='".$options['input_id']."'".($options['width'] ? " style='width: ".$options['width'].";' " : '')."".($options['deactivate'] ? 'readonly' : '')." ".($options['multiple'] ? "multiple='1'" : '')." />\n";
    $html .= $options['ext_tip'] ? "<span class='tip'><i>".$options['ext_tip']."</i></span><br/>" : "";
    $html .= (\Defender::inputHasError($input_name)) ? "<div id='".$options['input_id']."-help' class='label label-danger p-5 display-inline-block'>".$options['error_text']."</div>" : '';

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
                $html .= "<img class='center-y img-responsive' style='margin: 0 auto;' src='".$options['upload_path'].$files."' alt='$files'/>";
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
        $html .= (\Defender::inputHasError($input_name."-mediaSelector")) ? "<div id='".$options['input_id']."-mediaSelector' class='label label-danger p-5 display-inline-block'>".$options['error_text']."</div>" : "";

        $html .= "</div>\n";
        $html .= "</div>\n";
    }
    $html .= $options['inline'] && $label ? "</div>\n" : "";
    $html .= "</div>\n";

    \Defender::getInstance()->add_field_session(
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
            \Defender::getInstance()->add_field_session(
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

        $lang = file_exists(LOCALE.LOCALESET.'includes/dynamics/assets/fileinput/js/locales/'.$locale['short_lang_name'].'.js') ? 'language: "'.$locale['short_lang_name'].'",' : '';

        $icons = "
        browseIcon: '<i class=\"".$options['icon']."\"></i>&nbsp;',
        previewFileIcon: '<i class=\"fas fa-file\"></i>',
        removeIcon: '<i class=\"fas fa-trash\"></i>',
        cancelIcon: '<i class=\"fas fa-ban\"></i>',
        pauseIcon: '<i class=\"fas fa-pause\"></i>',
        uploadIcon: '<i class=\"fas fa-upload\"></i>',
        msgValidationErrorIcon: '<i class=\"fas fa-exclamation-circle\"></i> ',
        fileActionSettings: {
            removeIcon: '<i class=\"fas fa-trash\"></i>',
            uploadIcon: '<i class=\"fas fa-upload\"></i>',
            uploadRetryIcon: '<i class=\"fas fa-redo\"></i>',
            downloadIcon: '<i class=\"fas fa-download\"></i>',
            zoomIcon: '<i class=\"fas fa-search-plus\"></i>',
            dragIcon: '<i class=\"fas fa-arrows-alt\"></i>',
            indicatorNew: '<i class=\"fas fa-plus text-warning\"></i>',
            indicatorSuccess: '<i class=\"fas fa-check text-success\"></i>',
            indicatorError: '<i class=\"fas fa-exclamation text-danger\"></i>',
            indicatorLoading: '<i class=\"fas fa-hourglass-end text-muted\"></i>',
            indicatorPaused: '<i class=\"fas fa-pause text-primary\"></i>'
        },
        ";

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
                        ".$icons."
                        ".($options['jsonurl'] ? "uploadUrl : '".$options['jsonurl']."'," : '')."
                        ".($options['hide_upload'] ? 'showUpload: false,' : '')."
                        ".($options['hide_remove'] ? 'showRemove: false,' : '')."
                        dropZoneEnabled: ".($options['dropzone'] ? "true" : "false").",
                        ".($locale['text-direction'] == 'rtl' ? 'rtl: true,' : '')."
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
                        ".$icons."
                        showCaption: false,
                        showRemove: false,
                        ".($options['jsonurl'] ? "uploadUrl : '".$options['jsonurl']."'," : '')."
                        dropZoneEnabled: ".($options['dropzone'] ? "true" : "false").",
                        ".($options['hide_upload'] ? 'showUpload: false,' : '')."
                        ".($options['hide_remove'] ? 'showRemove: false,' : '')."
                        $extra_data_js
                        layoutTemplates: {
                            main2: '<div class=\"btn-photo-upload btn-link\">{preview}<div class=\"kv-upload-progress hide\"></div>{remove}{cancel}{upload}{browse}</div>'
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
                        ".$icons."
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

        if (!defined('FORM_FILEINPUT')) {
            define('FORM_FILEINPUT', TRUE);

            add_to_head("<link href='".DYNAMICS."assets/fileinput/css/fileinput.min.css' media='all' rel='stylesheet' type='text/css' />");
            if ($locale['text-direction'] == 'rtl') {
                add_to_head("<link href='".DYNAMICS."assets/fileinput/css/fileinput-rtl.min.css' media='all' rel='stylesheet' type='text/css' />");
            }
            add_to_footer("<script src='".DYNAMICS."assets/fileinput/js/fileinput.min.js' type='text/javascript'></script>");

            if (file_exists(LOCALE.LOCALESET.'includes/dynamics/assets/fileinput/js/locales/'.$locale['short_lang_name'].'.js')) {
                add_to_footer("<script src='".LOCALE.LOCALESET."includes/dynamics/assets/fileinput/js/locales/".$locale['short_lang_name'].".js' type='text/javascript'></script>");
            }
        }
    }

    return $html;
}
