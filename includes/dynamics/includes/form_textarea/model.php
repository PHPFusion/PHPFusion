<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
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

/**
 * @param string $input_name
 * @param string $label
 * @param string $input_value
 * @param array $options
 *
 * @return string
 */
function form_textarea($input_name, $label = '', $input_value = '', array $options = [])
{

	$locale = fusion_get_locale('',
		[
			LOCALE . LOCALESET . "admin/html_buttons.php",
			LOCALE . LOCALESET . "error.php",
		]);

	require_once INCLUDES . "bbcode_include.php";
	require_once INCLUDES . "html_buttons_include.php";

	$input_name = (isset($input_name) && (!empty($input_name))) ? stripinput($input_name) : "";

	$default_options = [
		'input_id'     => $input_name,
		'required'     => FALSE,
		'placeholder'  => '',
		'floating_label' => FALSE,
        'tip'           => '',
        'ext_tip'       => '',
		'width'        => '100%',
		'inner_width'  => '100%',
		'height'       => '200px',
		'class'        => '',
		'inner_class'  => '',
		'inline'       => FALSE,
		'error_text'   => $locale['error_input_default'],
		'no_resize'    => FALSE,
		'remember'     => FALSE,
		'safemode'     => FALSE,
		'censor_words' => FALSE,
		'descript'     => '',
		'autosize'     => FALSE,
		'ckeditor'     => FALSE,
		'tiptap'       => FALSE,
		'tiptap_format' => 'markdown',
		'mention'      => FALSE,
		'rows'         => 5,
	];

	$options += $default_options;
    if (!empty($options['ext_tip'])) {
        $options['ext_tip'] = dynamics_field_help($options['ext_tip'], TRUE);
    }
	$options['tiptap_format'] = $options['tiptap_format'] === 'html' ? 'html' : 'markdown';

	// --- RESTORED DEFENDER REGISTRATION ---
	\Defender::add_field_session([
		'input_name'   => clean_input_name($input_name),
		'type'         => 'textarea',
		'title'        => $label,
		'id'           => $options['input_id'],
		'required'     => $options['required'],
		'safemode'     => $options['safemode'],
		'error_text'   => $options['error_text'],
		'censor_words' => $options['censor_words'],
		'descript'     => $options['descript'],
		'remember'     => $options['remember'],
	]);

	$input_value = clean_input_value($input_value);

	// Persistence Check: POST > Session Remember > DB
	if (check_post($input_name)) {
		$input_value = post($input_name);
	}

	if ($options['remember'] == TRUE && empty($input_value)) {
		if (isset($_SESSION['form_drafts'][$options['input_id']])) {
			$input_value = $_SESSION['form_drafts'][$options['input_id']];
		}
	}

	// Validation Feedback Logic
	$error_class = "";
	if (\Defender::inputHasError($input_name)) {
		$error_class = " has-error";
		$defender_error = \Defender::getErrorText($input_name);
		if ($defender_error) {
			$options['error_text'] = $defender_error;
		}
		addnotice("danger", $options['error_text']);
	}

	// Prepare content for Editor/Textarea display
	if ($input_value) {
		$input_value = html_entity_decode(stripslashes($input_value), ENT_QUOTES, $locale['charset']);
		$input_value = htmlspecialchars_decode($input_value);
	}

	$safe_js_id = str_replace("-", "_", $options['input_id']);
	$floating_label = (bool)$options['floating_label'] && !$options['inline'];

	// --- HTML OUTPUT ---
	$html = "<div id='{$safe_js_id}_field' class='form-group " . ($floating_label ? 'form-floating ' : '') . ($options['inline'] && $label ? 'row' : '') . $error_class . $options['class'] . "'" . ($options['width'] ? " style='width: " . $options['width'] . " !important;'" : '') . ">";

	if ($label && !$floating_label) {
		$html .= "<label class='form-label " . ($options['inline'] ? "col-xs-12 col-sm-3" : '') . "' for='" . $safe_js_id . "'>" . $label . ($options['required'] ? "<span class='required'>&nbsp;*</span>" : '') . dynamics_field_help($options['tip']) . "</label>";
	}

	if ($options['inline']) {
		$html .= "<div class='col-xs-12 col-sm-9'>";
	}
	if (!$label && !empty($options['tip'])) {
		$html .= "<div class='dynamics-textarea-help'>" . dynamics_field_help($options['tip']) . "</div>";
	}


	$api_url = fusion_get_settings('siteurl') . "api/?api=textarea-sessions";

	// --- TIPTAP INTEGRATION ---
	if ($options['tiptap'] === TRUE) {
		fusion_load_script(BASEDIR . 'assets/libs/tiptap/dist/tiptap.css', 'css');
		fusion_load_script(BASEDIR . 'assets/libs/tiptap/dist/tiptap.min.js');
		$tiptap_config = json_encode(
			['output' => $options['tiptap_format']],
			JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
		) ?: '{"output":"markdown"}';

		// 3. Initialize via jQuery (Targeting ONLY this specific ID)
		add_to_jquery("
		;(function() {
			// By wrapping this in a function, 'currentTimer' is unique to this specific textarea
			var currentTimer = setInterval(function() {
				if (typeof window.initTiptapEditor === 'function') {
					window.initTiptapEditor('{$safe_js_id}', {$tiptap_config});
					clearInterval(currentTimer);
				}
			}, 50);
	
			" . ($options['remember'] ? "
			$('#{$safe_js_id}').on('change', function() {
				const val = $(this).val();
				$.post('{$api_url}', {
					field_id: '{$safe_js_id}',
					content: val,
					fusion_token: $('input[name=\"fusion_token\"]').val()
				});
			});
			" : "") . "
		})();
		");


	} // AJAX Session Backup script
	elseif ($options['remember'] && empty($options['ckeditor'])) {

		$remember_script = "
		var timer_{$safe_js_id};
		$(document).on('input', '#{$options['input_id']}', function() {
			clearTimeout(timer_{$safe_js_id});
			timer_{$safe_js_id} = setTimeout(() => {
				$.post('{$api_url}', { field_id: '{$options['input_id']}', content: $(this).val() });
			}, 1000);
		});
		";
		add_to_jquery($remember_script);
	}

	// CK editor integration
	if ($options['ckeditor'] === TRUE) {
		fusion_load_script(INCLUDES . 'ckeditor5/ckeditor5.js');
		fusion_load_script(INCLUDES . 'ckeditor5/ckeditor5.umd.js');
		fusion_load_script(INCLUDES . 'ckeditor5/ckeditor5.css', 'css');

		if (!defined('CKEDITOR_LOADED')) {

			// Load the CSS and the UMD JS
			add_to_head("
            <style>
                .ck-editor__editable_inline { 
                    min-height: {$options['height']}; 
                    color: #495057; /* Standard text color */
                }   
                .ck-hidden { display: none !important; }
            </style>");

			define('CKEDITOR_LOADED', TRUE);
		}

		add_to_jquery("
        {
        	const {
			ClassicEditor,
			Essentials,
			Paragraph,
			Bold,
			Italic,
			Markdown,    // Add this
			Heading,     // Recommended for Markdown # headers
			List,        // Recommended for Markdown * lists
			Link,         // Recommended for Markdown [links]()
			Autoformat,
			SourceEditing
			} = CKEDITOR;

		ClassicEditor.create( document.querySelector( '#{$safe_js_id}' ), {
				licenseKey: 'GPL', // Or 'GPL'.
				plugins: [ 
				 	Essentials,
                	Paragraph,
                	Bold,
                	Italic,
                	Markdown, // This activates the Markdown output
                	Heading,
                	List,
                	Link,
                	Autoformat, // <--- Add this
				 	SourceEditing
				 ],
				toolbar: [ 
					'sourceEditing', '|',
				 	'heading', '|',
                	'bold', 'italic', '|',
                	'bulletedList', 'numberedList', 'link', '|',
                	'undo', 'redo'
				 ]
			} ).then(editor => {
			
				// Store the editor instance in jQuery data for easy access
				$('#{$safe_js_id}').data('editor', editor);

				// If remember is enabled, listen to CKEditor changes
				" . ($options['remember'] ? "
				let timer_{$safe_js_id};
				editor.model.document.on('change:data', () => {
					clearTimeout(timer_{$safe_js_id});
					timer_{$safe_js_id} = setTimeout(() => {
						const data = editor.getData();
						// Update the hidden textarea so other scripts see the value
						$('#{$safe_js_id}').val(data);
						
						// Fire the AJAX backup
						$.post('{$api_url}', {
							field_id: '{$safe_js_id}',
							content: data,
							fusion_token: $('input[name=\"fusion_token\"]').val(),
							form_id: $('input[name=\"form_id\"]').val()
						});
					}, 1500); // 1.5s delay to save server hits
				});
				" : "") . "

			}).catch( error => {
				console.error( error );
			});
        }
        ");

		// $options['class'] .= " ck-hidden";
	} else {
		if ($options['autosize'] || defined('AUTOSIZE')) {
			fusion_load_script(DYNAMICS . 'assets/autosize/autosize.js');
			add_to_jquery("autosize($('#" . $options['input_id'] . "'));");
		}
	}

	// --- @-MENTION SDK INTEGRATION ---
	// Pass options['mention'] = ['dir' => INFUSIONS.'school/', 'endpoint' => 'student',
	//                            'id_col' => 'student_id', 'title_col' => 'student_name']
	// to attach a dynamic, AJAX-driven mention dropdown to this textarea.
	$mention_registry = '';
	$mention_data_attr = '';
	if (is_array($options['mention']) && !empty($options['mention']['endpoint'])) {
		$m = $options['mention'];

		// All infusion mention providers share the central API dispatcher.
		$m_url = BASEDIR . 'api/?api=' . rawurlencode($m['endpoint']);

		// Optional scoping params appended to every request (e.g. ['cal_id' => 12]).
		if (!empty($m['params']) && is_array($m['params'])) {
			foreach ($m['params'] as $pk => $pv) {
				$m_url .= '&' . rawurlencode($pk) . '=' . rawurlencode($pv);
			}
		}

		$m_config = json_encode([
			'url'      => $m_url,
			'idCol'    => $m['id_col'] ?? 'id',
			'titleCol' => $m['title_col'] ?? 'title',
			'trigger'  => $m['trigger'] ?? '@',
			'minChars' => isset($m['min_chars']) ? (int) $m['min_chars'] : 0,
			'qParam'   => $m['q_param'] ?? 'q',
		], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

		fusion_load_script(INCLUDES . 'jscripts/mention.js');
		fusion_load_script(INCLUDES . 'jscripts/mention.css', 'css');

		if ($options['tiptap'] === TRUE) {
			// Tiptap renders mentions as atomic chips via the Mention extension. The
			// config is delivered two ways for resilience: a JS registry (robust against
			// HTML output sanitisers that could drop data-* attributes) and a
			// data-mention attribute fallback. The plain-textarea SDK (initMentionField)
			// must NOT also bind to this field.
			add_to_jquery("window.PHPFusionMentionConfigs = window.PHPFusionMentionConfigs || {}; window.PHPFusionMentionConfigs['{$safe_js_id}'] = {$m_config};");
			$mention_data_attr = " data-mention='" . htmlspecialchars($m_config, ENT_QUOTES) . "'";
		} else {
			// Plain textarea: wait for the SDK to load (mirrors the tiptap bootstrap), then bind.
			add_to_jquery("
			(function() {
				var mTimer = setInterval(function() {
					if (typeof window.initMentionField === 'function') {
						window.initMentionField('{$safe_js_id}', {$m_config});
						clearInterval(mTimer);
					}
				}, 50);
			})();
			");
		}

		// Hidden registry: resolved {id,title} picks, submitted as {name}_mentions.
		$mention_registry = "<input type='hidden' name='" . clean_input_name($input_name) . "_mentions' id='{$safe_js_id}_mentions' value=''>";
	}

	// Standard Textarea (CKEditor will hide this and use it as data source)
	$placeholder_attr = $options['placeholder'] ? " placeholder='" . str_replace("'", "&#39;", $options['placeholder']) . "'" : '';
	$html .= "<textarea name='$input_name'" . ($options['required'] ? " aria-required='true'" : '') . "
              style='width:100%; height:{$options['height']};" . ($options['no_resize'] ? ' resize:none;' : '') . "'
              rows='{$options['rows']}'
              class='form-control'" . $placeholder_attr . $mention_data_attr . "
              id='{$safe_js_id}'>$input_value</textarea>";
	if ($floating_label && $label) {
		$html .= "<label for='{$safe_js_id}'>" . $label . ($options['required'] ? "<span class='required'>&nbsp;*</span>" : '') . dynamics_field_help($options['tip']) . '</label>';
	}
	$html .= $mention_registry;
    $html .= $options['ext_tip'] ? "<div class='form-text'>{$options['ext_tip']}</div>" : '';

	if ($options['inline']) {
		$html .= "</div>";
	}
	$html .= "</div>";

	return dynamics_render_component_template('form_textarea', $html);
}
