<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: form_textarea.php
| Author: Frederick MC Chan (Hien)
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
    global $locale, $defender, $userdata; // for editor

    $title = $label ? stripinput($label) : ucfirst(strtolower(str_replace("_", " ", $input_name)));

    $input_name = (isset($input_name) && (!empty($input_name))) ? stripinput($input_name) : "";

    require_once INCLUDES."bbcode_include.php";
    require_once INCLUDES."html_buttons_include.php";
    include_once LOCALE.LOCALESET."admin/html_buttons.php";
    include_once LOCALE.LOCALESET."error.php";
    if (!empty($options['bbcode'])) {
        $options['type'] = "bbcode";
    } elseif (!empty($options['html'])) {
        $options['type'] = "html";
    }

    $options = array(
        'input_id' => !empty($options['input_id']) ? $options['input_id'] : $input_name,
        "type" => !empty($options['type']) && in_array($options['type'],
                                                       array("html", "bbcode", "tinymce")) ? $options['type'] : "",
        'inline_editing' => !empty($options['inline_editing']) ? TRUE : FALSE,
        'required' => !empty($options['required']) && $options['required'] == 1 ? '1' : '0',
        'placeholder' => !empty($options['placeholder']) ? $options['placeholder'] : '',
        'deactivate' => !empty($options['deactivate']) && $options['deactivate'] == 1 ? '1' : '',
        'width' => !empty($options['width']) ? $options['width'] : '100%',
        'height' => !empty($options['height']) ? $options['height'] : '80px',
        'class' => !empty($options['class']) ? $options['class'] : '',
        'inline' => !empty($options['inline']) && $options['inline'] == 1 ? '1' : '0',
        'length' => !empty($options['length']) ? $options['length'] : '200',
        'error_text' => !empty($options['error_text']) ? $options['error_text'] : $locale['error_input_default'],
        'safemode' => !empty($options['safemode']) && $options['safemode'] == 1 ? '1' : '0',
        'form_name' => !empty($options['form_name']) ? $options['form_name'] : 'input_form',
        'tinymce' => !empty($options['tinymce']) && in_array($options['tinymce'], array(
            TRUE, 'simple', 'advanced'
        )) ? $options['tinymce'] : "simple",
        'no_resize' => !empty($options['no_resize']) && $options['no_resize'] == '1' ? '1' : '0',
        'autosize' => !empty($options['autosize']) && $options['autosize'] == 1 ? '1' : '0',
        'preview' => !empty($options['preview']) && $options['preview'] == TRUE ? TRUE : FALSE,
        'path' => !empty($options['path']) && $options['path'] ? $options['path'] : IMAGES,
        'maxlength' => !empty($options['maxlength']) && isnum($options['maxlength']) ? $options['maxlength'] : '',
        'tip' => !empty($options['tip']) ? $options['tip'] : '',
    );

    if ($options['type'] == "tinymce") {
        $tinymce_list = array();
        $image_list = makefilelist(IMAGES, ".|..|");
        $image_filter = array('png', 'PNG', 'bmp', 'BMP', 'jpg', 'JPG', 'jpeg', 'gif', 'GIF', 'tiff', 'TIFF');
        foreach ($image_list as $image_name) {
            $image_1 = explode('.', $image_name);
            $last_str = count($image_1) - 1;
            if (in_array($image_1[$last_str], $image_filter)) {
                $tinymce_list[] = array('title' => $image_name, 'value' => IMAGES.$image_name);
            }
        }
        $tinymce_list = json_encode($tinymce_list);
        $tinymce_smiley_vars = "";
        if (!defined('tinymce')) {
            add_to_head("<style type='text/css'>.mceIframeContainer iframe{width:100%!important; height:30px;}</style>");
            add_to_footer("<script type='text/javascript' src='".INCLUDES."jscripts/tinymce/tinymce.min.js'></script>");
            define('tinymce', TRUE);
            // PHP-Fusion Parse Cache Smileys
            $smileys = cache_smileys();
            $tinymce_smiley_vars = "";
            if (!empty($smileys)) {
                $tinymce_smiley_vars = "var shortcuts = {\n";
                foreach ($smileys as $params) {
                    $tinymce_smiley_vars .= "'".strtolower($params['smiley_code'])."' : '<img alt=\"".$params['smiley_text']."\" src=\"".IMAGES."smiley/".$params['smiley_image']."\"/>',\n";
                }
                $tinymce_smiley_vars .= "};\n";
                $tinymce_smiley_vars .= "
				ed.on('keyup load', function(e){
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
                theme: 'modern',
                entity_encoding : 'raw',
                width: '100%',
                height: 300,
                plugins: [
                    'advlist autolink autoresize link image lists charmap print preview hr anchor pagebreak spellchecker',
                    'searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking',
                    'save table contextmenu directionality template paste textcolor ".($options['inline_editing'] ? " save " : "")."'
                ],
                image_list: $tinymce_list,
                content_css: '".THEMES."admin_templates/".fusion_get_settings("admin_theme")."/acp_styles.css',
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
                theme: 'modern',
                menubar: false,
                statusbar: false,
                content_css: '".THEMES."/templates/tinymce.css',
                image_list: $tinymce_list,
                plugins: [
                    'advlist autolink autoresize link lists charmap print preview hr anchor pagebreak spellchecker',
                    'searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking',
                    'contextmenu directionality template paste bbcode autoresize ".($options['inline_editing'] ? " save " : "")."'
                ],
                height: 30,
                image_advtab: true,
                toolbar1: 'undo redo | bold italic underline | bullist numlist blockquote | hr media | fullscreen ".($options['inline_editing'] ? " save " : "")."',
                entity_encoding : 'raw',
                language: '".$locale['tinymce']."',
                object_resizing: false,
                resize: false,
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
				$('#inject').bind('click', function() {
					tinyMCE.activeEditor.execCommand(\"mceInsertContent\", true, '[b]I am injecting in stuff..[/b]');
					});
				");
                break;
            case 'default':
                add_to_jquery("
                tinymce.init({
                selector: '#".$options['input_id']."',
                inline: ".($options['inline_editing'] == TRUE ? "true" : "false").",
                theme: 'modern',
                entity_encoding : 'raw',
                language:'".$locale['tinymce']."',
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
            add_to_jquery("
		    $('#".$options['input_id']."').autosize();
		    ");
        }

    }


    if ($input_value !== '') {
        $input_value = html_entity_decode(stripslashes($input_value), ENT_QUOTES, $locale['charset']);
        $input_value = str_replace("<br />", "", $input_value);
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

    $html = "<div id='".$options['input_id']."-field' class='form-group ".$error_class.$options['class']."' ".($options['inline'] && $options['width'] && !$label ? "style='width: ".$options['width']." !important;'" : '').">\n";
    $html .= ($label) ? "<label class='control-label ".($options['inline'] ? "col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0" : '')."' for='".$options['input_id']."'>$label ".($options['required'] == 1 ? "<span class='required'>*</span>" : '')." ".($options['tip'] ? "<i class='pointer fa fa-question-circle' title='".$options['tip']."'></i>" : '')."</label>\n" : '';
    $html .= ($options['inline']) ? "<div class='col-xs-12 ".($label ? "col-sm-9 col-md-9 col-lg-9 p-r-0" : "col-sm-12 p-l-0")."'>\n" : "";
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
        $html .= opentab($tab_title, $tab_active, $options['input_id']."-link", "", "editor-wrapper");
        $html .= opentabbody($tab_title['title'][1], "txt-".$options['input_id'], $tab_active);
    }

    $html .= ($options['type'] == "html" || $options['type'] == "bbcode") ? "<div class='panel panel-default panel-txtarea m-b-0' ".($options['preview'] ? "style='border-top:0 !important; border-radius:0 !important;'" : '').">\n<div class='panel-heading clearfix' style='padding-bottom:0 !important;'>\n" : '';
    if ($options['type'] == "bbcode" && $options['form_name']) {
        $html .= display_bbcodes('90%', $input_name, $options['form_name']);
    } elseif ($options['type'] == "html" && $options['form_name']) {
        $html .= display_html($options['form_name'], $input_name, TRUE, TRUE, TRUE, $options['path']);
    }
    $html .= ($options['type'] == "html" || $options['type'] == "bbcode") ? "</div>\n<div class='panel-body p-0'>\n" : '';

    if ($options['inline_editing'] == TRUE) {

        $html .= "<div id='".$options['input_id']."'>".$input_value."</div>\n";

    } else {

       $html .= "<textarea name='$input_name' style='width:100%; height:".$options['height']."; ".($options['no_resize'] ? 'resize: none;' : '')."' class='form-control p-15 m-0 ".$options['class']." ".($options['autosize'] ? 'animated-height' : '')." ".(($options['type'] == "html" || $options['type'] == "bbcode") ? "no-shadow no-border" : '')." textbox ' placeholder='".$options['placeholder']."' id='".$options['input_id']."' ".($options['deactivate'] ? 'readonly' : '').($options['maxlength'] ? "maxlength='".$options['maxlength']."'" : '').">".$input_value."</textarea>\n";
    }


    if ($options['type'] == "html" || $options['type'] == "bbcode") {
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
        $html .= "</div>\n</div>\n";
    }

    if ($options['preview'] && ($options['type'] == "bbcode" || $options['type'] == "html")) {
        $html .= closetabbody();
        $html .= opentabbody($tab_title['title'][0], "prw-".$options['input_id']."", $tab_active);
        $html .= "No Result";
        $html .= closetabbody();
        $html .= closetab();
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
			url: '".INCLUDES."dynamics/assets/preview/preview.ajax.php',
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

    $html .= (($options['required'] == 1 && $defender->inputHasError($input_name)) || $defender->inputHasError($input_name)) ? "<div id='".$options['input_id']."-help' class='label label-danger p-5 display-inline-block'>".$options['error_text']."</div>" : "";
    $html .= $options['inline'] ? "</div>\n" : '';
    $html .= "</div>\n";
    $defender->add_field_session(array(
                                     'input_name' => $input_name,
                                     'type' => 'textarea',
                                     'title' => $label,
                                     'id' => $options['input_id'],
                                     'required' => $options['required'],
                                     'safemode' => $options['safemode'],
                                     'error_text' => $options['error_text']
                                 ));

    return $html;
}

