<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: form_textarea.php
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
function form_textarea($input_name, $label = '', $input_value = '', array $options = array()) {
    $defender = defender::getInstance();
    $locale = fusion_get_locale('', [
        LOCALE.LOCALESET."admin/html_buttons.php",
        LOCALE.LOCALESET."error.php"
    ]);

    require_once INCLUDES."bbcode_include.php";
    require_once INCLUDES."html_buttons_include.php";

    $title = $label ? stripinput($label) : ucfirst(strtolower(str_replace("_", " ", $input_name)));

    $input_name = (isset($input_name) && (!empty($input_name))) ? stripinput($input_name) : "";

    if (!empty($options['bbcode'])) {
        $options['type'] = "bbcode";
    } elseif (!empty($options['html'])) {
        $options['type'] = "html";
    }

    $default_options = array(
        'input_id'            => $input_name,
        'type'                => '',
        'inline_editing'      => FALSE,
        'required'            => FALSE,
        'tinymce_forced_root' => TRUE,
        'placeholder'         => '',
        'deactivate'          => FALSE,
        'width'               => '',
        'inner_width'         => '100%',
        'height'              => '80px',
        'class'               => '',
        'inner_class'         => '',
        'inline'              => FALSE,
        'length'              => 200,
        'error_text'          => $locale['error_input_default'],
        'safemode'            => FALSE,
        'form_name'           => 'input_form',
        'tinymce'             => 'simple',
        'tinymce_css'         => '',
        'no_resize'          => FALSE,
        'autosize'           => FALSE,
        'preview'            => FALSE,
        'path'               => IMAGES,
        'maxlength'          => '',
        'tip'                => '',
        'ext_tip'            => '',
        'input_bbcode'       => '',
        'wordcount'          => FALSE,
        'file_filter'        => ['.png', '.PNG', '.svg', '.SVG', '.bmp', '.BMP', '.jpg', '.JPG', '.jpeg', '.gif', '.GIF', '.tiff', '.TIFF'],
        'tinymce_theme'      => 'modern',
        'tinymce_skin'       => 'lightgray',
        'tinymce_spellcheck' => TRUE,
    );

    $options += $default_options;

    if ($options['type'] == "tinymce") {

        $options['tinymce'] = !empty($options['tinymce']) && in_array($options['tinymce'], array(TRUE, 'simple', 'advanced')) ? $options['tinymce'] : "simple";

        $default_tinymce_css = (defined("ADMIN_PANEL") ? THEMES."admin_themes/".fusion_get_settings("admin_theme")."/acp_styles.css" : THEMES."templates/tinymce.css");

        $options['tinymce_css'] = (!empty($options['tinymce_css']) && file_exists($options['tinymce_css']) ? $options['tinymce_css'] : $default_tinymce_css);

        $options['tinymce_spellcheck'] = $options['tinymce_spellcheck'] == TRUE ? 'true' : 'false';

        $tinymce_list = array();
        if (!empty($options['path'])) {
            $image_list = [];
            if (is_array($options['path'])) {
                foreach ($options['path'] as $dir) {
                    if (file_exists($dir)) {
                        $image_list[$dir] = makefilelist($dir, ".|..|");
                    }
                }
            } else {
                $image_list[$options['path']] = makefilelist($options['path'], '.|..|');
            }
            foreach ($image_list as $key => $images) {
                foreach ($images as $keys => $image_name) {
                    $image_1 = explode('.', $image_name);
                    $last_str = count($image_1) - 1;
                    if (in_array(".".$image_1[$last_str], $options['file_filter'])) {
                        $tinymce_list[] = array('title' => $image_name, 'value' => $key.$image_name);
                    }
                }
            }
        }

        $tinymce_list = json_encode($tinymce_list);
        $tinymce_smiley_vars = "";
        if (!defined('tinymce')) {
            add_to_footer("<script type='text/javascript' src='".INCLUDES."jscripts/tinymce/tinymce.min.js'></script>");
            define('tinymce', TRUE);
            // PHP-Fusion Parse Cache Smileys
            $smileys = cache_smileys();
            $tinymce_smiley_vars = "";
            if (!empty($smileys)) {
                $tinymce_smiley_vars = "var shortcuts = {\n";
                foreach ($smileys as $params) {
                    $tinymce_smiley_vars .= "'".strtolower($params['smiley_code'])."' : '<img alt=\"".$params['smiley_text']."\" src=\"".fusion_get_settings('siteurl')."images/smiley/".$params['smiley_image']."\"/>',\n";
                }
                $tinymce_smiley_vars .= "};\n";
                $tinymce_smiley_vars .= "
                ed.on('keyup', function(e){
                    var marker = tinymce.activeEditor.selection.getBookmark();
                    // Store editor contents
                    var content = tinymce.activeEditor.getContent({'format':'raw'});
                    // Loop through all shortcuts
                    for(var key in shortcuts){
                        // Check if the editor html contains the looped shortcut
                        if(content.toLowerCase().indexOf(key) != -1) {
                            // Escaping special characters to be able to use the shortcuts in regular expression
                            var k = key.replace(/[<>*()?']/ig, \"\\$&\");
                            tinymce.activeEditor.setContent(content.replace(k, shortcuts[key]));
                        }
                    }
                    // Now put cursor back where it was
                    tinymce.activeEditor.selection.moveToBookmark(marker);
                });
                ";
            }
        }
        // Mode switching for TinyMCE
        switch ($options['tinymce']) {
            case 'advanced':
                add_to_jquery("
                tinymce.init({
                selector: '#".$options['input_id']."',
                inline: ".($options['inline_editing'] == TRUE ? "true" : "false").",
                theme: '".$options['tinymce_theme']."',
                skin: '".$options['tinymce_skin']."',
                browser_spellcheck: ".$options['tinymce_spellcheck'].",
                entity_encoding: 'raw',
                language:'".$locale['tinymce']."',
                ".($options['tinymce_forced_root'] ? "forced_root_block : ''," : '')."
                width: '100%',
                height: 300,
                plugins: [
                    'advlist autolink ".($options['autosize'] ? " autoresize " : "")." link image lists charmap print preview hr anchor pagebreak spellchecker',
                    'searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking',
                    'save table contextmenu directionality template paste textcolor ".($options['inline_editing'] ? " save " : "")."'
                ],
                image_list: $tinymce_list,
                content_css: '".$options['tinymce_css']."',
                toolbar1: '".($options['inline_editing'] ? " save " : "")." insertfile undo redo | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | newdocument fullscreen preview cut copy paste pastetext spellchecker searchreplace code',
                toolbar2: 'styleselect formatselect removeformat | fontselect fontsizeselect bold italic underline strikethrough subscript superscript blockquote | forecolor backcolor',
                toolbar3: 'hr pagebreak insertdatetime | link unlink anchor | image media | table charmap visualchars visualblocks emoticons',
                image_advtab: true,
                style_formats: [
                    {title: 'Bold text', inline: 'b'},
                    {title: 'Red text', inline: 'span', styles: {color: '#ff0000'}},
                    {title: 'Red header', block: 'h1', styles: {color: '#ff0000'}},
                    {title: 'Example 1', inline: 'span', classes: 'example1'},
                    {title: 'Example 2', inline: 'span', classes: 'example2'},
                    {title: 'Table styles'},
                    {title: 'Table row 1', selector: 'tr', classes: 'tablerow1'}
                ],
                setup: function(ed) {
                    // add tabkey listener
                    ed.on('keydown', function(event) {
                        if (event.keyCode == 9) { // tab pressed
                            if (event.shiftKey) { ed.execCommand('Outdent'); } else { ed.execCommand('Indent'); }
                            event.preventDefault();
                            return false;
                        }
                    });
                    // auto smileys parsing
                    ".$tinymce_smiley_vars."
                }
                });
                ");
                break;
            case 'simple':
                add_to_jquery("
                tinymce.init({
                selector: '#".$options['input_id']."',
                inline: ".($options['inline_editing'] == TRUE ? "true" : "false").",
                theme: '".$options['tinymce_theme']."',
                skin: '".$options['tinymce_skin']."',
                browser_spellcheck: ".$options['tinymce_spellcheck'].",
                entity_encoding: 'raw',
                menubar: false,
                statusbar: false,
                content_css: '".$options['tinymce_css']."',
                image_list: $tinymce_list,
                plugins: [
                    'advlist autolink ".($options['autosize'] ? " autoresize " : "")." link image lists charmap print preview hr anchor pagebreak spellchecker',
                    'searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking',
                    'contextmenu directionality template paste bbcode autoresize ".($options['inline_editing'] ? " save " : "")."'
                ],
                width: '100%',
                height: 100,
                image_advtab: true,
                toolbar1: 'undo redo | bold italic underline | emoticons | visualblocks | bullist numlist blockquote | hr media | fullscreen ".($options['inline_editing'] ? " save " : "")." | code',
                language: '".$locale['tinymce']."',
                ".($options['tinymce_forced_root'] ? "forced_root_block : ''," : '')."
                object_resizing: ".($options['autosize'] ? "false" : "true").",
                resize: ".($options['autosize'] ? "false" : "true").",
                relative_urls: false,
                setup: function(ed) {
                    // add tabkey listener
                    ed.on('keydown', function(event) {
                        if (event.keyCode == 9) { // tab pressed
                            if (event.shiftKey) { ed.execCommand('Outdent'); } else { ed.execCommand('Indent'); }
                            event.preventDefault();
                            return false;
                        }
                    });
                    // auto smileys parsing
                    ".$tinymce_smiley_vars."
                }
                });
                ");
                add_to_jquery("
                $('#inject').bind('click', function () {
                    tinyMCE.activeEditor.execCommand(\"mceInsertContent\", true, '[b]I am injecting in stuff..[/b]');
                    });
                ");
                break;
            case 'default':
                add_to_jquery("
                tinymce.init({
                selector: '#".$options['input_id']."',
                inline: ".($options['inline_editing'] == TRUE ? "true" : "false").",
                content_css: '".$options['tinymce_css']."',
                theme: '".$options['tinymce_theme']."',
                skin: '".$options['tinymce_skin']."',
                browser_spellcheck: ".$options['tinymce_spellcheck'].",
                entity_encoding: 'raw',
                language:'".$locale['tinymce']."',
                ".($options['tinymce_forced_root'] ? "forced_root_block : ''," : '')."
                setup: function(ed) {
                    // add tabkey listener
                    ed.on('keydown', function(event) {
                        if (event.keyCode == 9) { // tab pressed
                            if (event.shiftKey) { ed.execCommand('Outdent'); } else { ed.execCommand('Indent'); }
                            event.preventDefault();
                            return false;
                        }
                    });
                    // auto smileys parsing
                    ".$tinymce_smiley_vars."
                }
                });
                ");
                break;
        }
    } else {

        if (!defined('autogrow') && $options['autosize']) {
            define('autogrow', TRUE);
            add_to_footer("<script src='".DYNAMICS."assets/autosize/jquery.autosize.min.js'></script>");
        }

        if ($options['autosize']) {
            add_to_jquery("$('#".$options['input_id']."').autosize();");
        }
    }

    if ($input_value) {

        $input_value = html_entity_decode(stripslashes($input_value), ENT_QUOTES, $locale['charset']);
        $input_value = htmlspecialchars_decode($input_value);

        if ($options['type'] !== "tinymce") {
            $input_value = str_replace("<br />", "", $input_value);
        }
    }

    $error_class = "";
    if ($defender->inputHasError($input_name)) {
        $error_class = "has-error ";
        if (!empty($options['error_text'])) {
            $new_error_text = $defender->getErrorText($input_name);
            if (!empty($new_error_text)) {
                $options['error_text'] = $new_error_text;
            }
            addNotice("danger", "<strong>$title</strong> - ".$options['error_text']);
        }
    }

    $html = "<div id='".$options['input_id']."-field' class='form-group ".($options['inline'] ? 'display-block overflow-hide ' : '').$error_class.$options['class']."' ".($options['width'] ? "style='width: ".$options['width']." !important;'" : '').">\n";
    $html .= ($label) ? "<label class='control-label ".($options['inline'] ? "col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0 p-r-0" : '')."' for='".$options['input_id']."'>".$label.($options['required'] == 1 ? "<span class='required'>&nbsp;*</span>" : '')." ".($options['tip'] ? "<i class='pointer fa fa-question-circle' title='".$options['tip']."'></i>" : '')."</label>\n" : '';
    $html .= ($options['inline']) ? "<div class='clearfix".($label ? ' col-xs-12 col-sm-9 col-md-9 col-lg-9' : '')."'>\n" : '';
    $tab_active = 0;
    $tab_title = array();
    if ($options['preview'] && ($options['type'] == "html" || $options['type'] == "bbcode")) {
        $tab_title['title'][] = $locale['preview'];
        $tab_title['id'][] = "prw-".$options['input_id'];
        $tab_title['icon'][] = '';
        $tab_title['title'][] = $locale['texts'];
        $tab_title['id'][] = "txt-".$options['input_id'];
        $tab_title['icon'][] = '';
        $tab_active = tab_active($tab_title, 1);
    }

    $html .= ($options['type'] == "html" || $options['type'] == "bbcode") ? "<div class='panel panel-default panel-txtarea m-b-0' ".($options['preview'] ? "style='border-radius:0 !important;'" : '').">\n
    <div class='panel-heading clearfix'>\n" : '';

    if ($options['preview'] && ($options['type'] == "bbcode" || $options['type'] == "html")) {
        $html .= openeditortab($tab_title, $tab_active, $options['input_id']."-link", "", "editor-wrapper");
    }

    if ($options['type'] == "bbcode" && $options['form_name']) {
        $html .= "<div class='bbcode_input'>\n";
        $html .= display_bbcodes('100%', $options['input_id'], $options['form_name'], $options['input_bbcode']);
        $html .= $options['preview'] ? "</div>\n" : "";
    } elseif ($options['type'] == "html" && $options['form_name']) {
        $html .= "<div class='m-t-10 m-b-10'>\n";
        $html .= display_html($options['form_name'], $options['input_id'], TRUE, TRUE, TRUE, $options['path']);
        $html .= $options['preview'] ? "</div>\n" : "";
    }

    $html .= ($options['type'] == "html" || $options['type'] == "bbcode") ? "</div>\n</div>\n<div class='panel-body p-0'>\n" : '';

    if ($options['preview'] && ($options['type'] == "bbcode" || $options['type'] == "html")) {
        $html .= "<div id='tab-content-".$options['input_id']."-link' class='tab-content p-0'>\n";
        $html .= opentabbody($tab_title['title'][1], "txt-".$options['input_id'], $tab_active);
    }

    if ($options['inline_editing'] == TRUE) {
        $html .= "<div id='".$options['input_id']."' ".($options['width'] ? "style='display:block; width:".$options['width'].";'" : '').">".$input_value."</div>\n";
    } else {
        $html .= "<textarea name='$input_name' style='display:block; width:".$options['inner_width']."; height:".$options['height']."; ".($options['no_resize'] ? 'resize: none;' : '')."' class='form-control p-15 m-0 ".($options['inner_class'] ? " ".$options['inner_class']." " : '').($options['autosize'] ? 'animated-height' : '')." ".(($options['type'] == "html" || $options['type'] == "bbcode") ? "no-shadow no-border" : '')." textbox'".($options['placeholder'] ? " placeholder='".$options['placeholder']."' " : '')." id='".$options['input_id']."' ".($options['deactivate'] ? ' readonly' : '').($options['maxlength'] ? " maxlength='".$options['maxlength']."'" : '').">".$input_value."</textarea>\n";
    }

    if ($options['preview'] && ($options['type'] == "bbcode" || $options['type'] == "html")) {
        $html .= closetabbody();
        $html .= opentabbody($tab_title['title'][0], "prw-".$options['input_id']."", $tab_active);
        $html .= $locale['global_003'];
        $html .= closetabbody();
        $html .= "</div>\n";
        add_to_jquery("
        // preview syntax
        var form = $('#".$options['form_name']."');
        $('#tab-prw-".$options['input_id']."').bind('click',function(){
        var text = $('#".$options['input_id']."').val();
        var format = '".($options['type'] == "bbcode" ? 'bbcode' : 'html')."';
        var data = {
            ".(defined('ADMIN_PANEL') ? "'mode': 'admin', " : "")."
            'text' : text,
            'editor' : format,
            'url' : '".$_SERVER['REQUEST_URI']."',
        };
        var sendData = form.serialize() + '&' + $.param(data);
        $.ajax({
            url: '".FUSION_ROOT.INCLUDES."dynamics/assets/preview/preview.ajax.php',
            type: 'POST',
            dataType: 'html',
            data : sendData,
            success: function(result){
            //console.log(result);
            $('#prw-".$options['input_id']."').html(result);
            },
            error: function(result) {
                new PNotify({
                    title: '".$locale['error_preview']."',
                    text: '".$locale['error_preview_text']."',
                    icon: 'notify_icon n-attention',
                    animation: 'fade',
                    width: 'auto',
                    delay: '3000'
                });
            }
            });
        });
        ");
    }

    if (($options['type'] == "html" || $options['type'] == "bbcode") && $options['wordcount'] === TRUE) {
        $html .= "</div>\n<div class='panel-footer clearfix'>\n";
        $html .= "<div class='overflow-hide'><small>".$locale['word_count'].": <span id='".$options['input_id']."-wordcount'></span></small></div>";
        add_to_jquery("
        var init_str = $('#".$options['input_id']."').val().replace(/<[^>]+>/ig, '').replace(/\\n/g,'').replace(/ /g, '').length;
        $('#".$options['input_id']."-wordcount').text(init_str);
        $('#".$options['input_id']."').on('input propertychange paste', function() {
        var str = $(this).val().replace(/<[^>]+>/ig, '').replace(/\\n/g,'').replace(/ /g, '').length;
        $('#".$options['input_id']."-wordcount').text(str);
        });
        ");
        $html .= "</div>\n<!---panel-footer-->";
    }
    if ((!$options['type'] == "bbcode" && !$options['type'] == "html")) {
        $html .= $options['ext_tip'] ? "<span class='tip'><i>".$options['ext_tip']."</i></span>" : "";
    }
    $html .= $options['inline'] ? "</div>\n" : '';
    if (($options['type'] == "bbcode" || $options['type'] == "html")) {
        if ($options['wordcount']) {
            $html .= "</div>\n";
            $html .= $options['ext_tip'] ? "<br/>\n<span class='tip'><i>".$options['ext_tip']."</i></span>" : "";

        } else {
            $html .= "</div>\n";
            $html .= "</div>\n";
            $html .= $options['ext_tip'] ? "<br/>\n<span class='tip'><i>".$options['ext_tip']."</i></span>" : "";

        }
    }

    $html .= (($options['required'] == 1 && $defender->inputHasError($input_name)) || $defender->inputHasError($input_name)) ? "<div id='".$options['input_id']."-help' class='label label-danger text-white p-5 display-inline-block'>".$options['error_text']."</div>" : "";
    $html .= "</div>\n";

    $defender->add_field_session(array(
        'input_name' => $input_name,
        'type'       => 'textarea',
        'title'      => $label,
        'id'         => $options['input_id'],
        'required'   => $options['required'],
        'safemode'   => $options['safemode'],
        'error_text' => $options['error_text']
    ));

    return $html;
}

function openeditortab($tab_title, $link_active_arrkey, $id, $link = FALSE, $class = FALSE, $getname = "section") {
    $link_mode = $link ? $link : 0;
    $html = "<div class='nav-wrapper $class'>\n";
    $html .= "<ul class='nav' ".($id ? "id='".$id."'" : "")." >\n";
    if (!empty($tab_title['title'])) {
        foreach ($tab_title['title'] as $arr => $v) {
            $v_title = str_replace("-", " ", $v);
            $tab_id = $tab_title['id'][$arr];
            $icon = (isset($tab_title['icon'][$arr])) ? $tab_title['icon'][$arr] : "";
            $link_url = $link ? clean_request($getname.'='.$tab_id, array($getname), FALSE) : '#';
            if ($link_mode) {
                $html .= ($link_active_arrkey == $tab_id) ? "<li class='active m-r-10'>\n" : "<li class='m-r-10'>\n";
            } else {
                $html .= ($link_active_arrkey == "".$tab_id) ? "<li class='active m-r-10'>\n" : "<li  class='m-r-10'>\n";
            }
            $html .= "<a class='btn btn-default btn-xs m-l-10 pointer' ".(!$link_mode ? "id='tab-".$tab_id."' data-toggle='tab' data-target='#".$tab_id."'" : "href='$link_url'").">\n".($icon ? "<i class='".$icon."'></i>" : '')." ".$v_title." </a>\n";
            $html .= "</li>\n";
        }
    }
    $html .= "</ul>\n";

    return $html;
}
