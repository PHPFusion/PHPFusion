<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: testDefender.php
| Author: 
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

// ADD MORE INPUTS FOR TESTS

require_once "maincore.php";
require_once THEMES."templates/header.php";

opentable("Testing Inputs with Defender");

// These are the defaults, they are values
// pulled from DB most of the times, and we
// assume these values are already valid.
// If they aren't, the user will be prompted
// to enter valid values upon form submission.
$settings_test = array(
	'text_input_required'	=> '',
	'text_input_safe'		=> '',
	'password_input'		=> '',
	'text_input'			=> '',
	'number_input'			=> '321',
	'checkbox_input'		=> 0,
	'checkbox_input2'		=> 0,
	'checkbox_input_bs'		=> 0,
	'undefined_input'		=> 'foo', // this input is expected but not defined in code
	//'checkbox_input3'		=> 1,
	//'checkbox_input4'		=> 1,
	//'name_input'			=> '',
	//'address_input'			=> 'Some|Address',
	'email_input'			=> 'valid@email.com',
	'url_input'				=> '',
	'regex_input'			=> 'abc',
	'textarea'				=> '',
	'file_input'			=> ''
	);

if (isset($_POST['submit'])) {
	// Upon sumbission we check the posted inputs against 
	// default inputs and override their values.
	// If an input was not posted the default input's value
	// will be returned and also checked if valid.
	// If other inputs are posted they will simply be ingored.
	foreach ($settings_test as $key => $value) {
		// We process the inputs posted here
		if (isset($_POST[$key])) {
			// aditional input processing
			if ($key == 'some_input') {
				//$settings_test[$key] = 1;
			} else {
				$settings_test[$key] = form_sanitizer($_POST[$key], $settings_test[$key], $key);
			}
			//addNotice('info', $key." was posted, the user's input was used");
		// Here go the inputs that we expected but didn't make it
		// There can be more reasons and situations for this:
		// - INPUT NOT POSTED: if the input was defined in source code but not posted
		// the $value will checked and returned, but only if is not a checkbox in which
		// case we assume it was unchecked and int 0 is returned
		// - INPUT NOT DEFINED: if the input was not defined in source code then the $default
		// will be returned, this can be a valid value previously saved in the DB
		} else {
			$settings_test[$key] = form_sanitizer($settings_test[$key], $settings_test[$key], $key);
			//addNotice('info', $key." was NOT posted, the default value was used");
		}
	}

	if (!defined('FUSION_NULL')) {
		// Everything went as expected
		addNotice("success", "Posted successfully");

		//redirect(FUSION_SELF);
	}
}

echo openform('form', 'post', FUSION_SELF, array('max_tokens' => 5));

echo form_text('text_input_required', 'Required text input', $settings_test['text_input_required'], array('required' => 1, 'tip' => 'Information', 'error_text' => 'CUSTOM ERROR: This field cannot be left empty', 'inline' => 1));
echo form_text('text_input_safe', 'Required text input in SAFEMODE', $settings_test['text_input_safe'], array('required' => 1, 'safemode' => 1, 'inline' => 1));
echo form_text('password_input', 'Password input', $settings_test['password_input'], array('type' => 'password', 'autocomplete_off' => 1, 'required' => 1, 'error_text' => 'Ummm, please enter a valid password here', 'inline' => 1));
echo form_text('text_input', 'Text input', $settings_test['text_input'], array('required' => 1, 'inline' => 1));
echo form_text('text_input2', 'An extra text input<br /><small>This input is not accounted for and will be ignored</small>', 'something', array('required' => 1, 'inline' => 1));
echo form_text('email_input', 'Email', $settings_test['email_input'], array('required' => 0, 'type' => 'email', 'inline' => 1));
echo form_text('url_input', 'URL',  $settings_test['url_input'], array('type' => 'url', 'inline' => 1));
echo form_text('regex_input', 'Regex', $settings_test['regex_input'], array('tip' => 'Characters from A to Z only', 'regex' => '[a-z]+', 'inline' => 1));
echo form_text('number_input', 'Number', $settings_test['number_input'], array('required' => 1, 'type' => 'number', 'inline' => 1));
echo form_checkbox('checkbox_input', 'Checkbox', $settings_test['checkbox_input'], array('required' => 1, 'inline' => 1));
// Experimental 'child_of'
echo form_checkbox('checkbox_input2', 'Checkbox 2, child of Checkbox', $settings_test['checkbox_input2'], array('child_of' => 'checkbox_input', 'inline' => 1));
echo form_checkbox('checkbox_input_bs', 'Bootstrap switch checkbox', $settings_test['checkbox_input_bs'], array('toggle' => 1, 'toggle_text'=> array('OFF', 'ON'), 'disabled' => 0, 'inline' => 1));
//echo form_checkbox('Checkbox 3, child of Checkbox', 'checkbox_input3', 'checkbox_input3', $settings_test['checkbox_input3'], array('child_of' => 'checkbox_input', 'inline' => 1));
//echo form_checkbox('Checkbox 4, child of Checkbox 3', 'checkbox_input4', 'checkbox_input4', $settings_test['checkbox_input4'], array('child_of' => 'checkbox_input3', 'inline' => 1));
//echo form_name('Name', 'name_input', 'name_input', $settings_test['name_input'], array('required' => 1, 'inline' => 1));
//echo form_address('Address', 'address_input', 'address_input', explode('|', $settings_test['address_input']), array('inline' => 1));
echo form_textarea('textarea', 'Text area', $settings_test['textarea'], array('autosize'=>1, 'inline' => 1));

//var_dump($_SESSION['form_fields'][$_SERVER['PHP_SELF']]);
$file_options = array(
				'max_width' => $settings['download_screen_max_w'],
				'max_height' => $settings['download_screen_max_w'],
				'max_byte' => $settings['download_screen_max_b'],
				'type' => 'image',
				'required' => 0,
				'delete_original' => 0,
				'thumbnail_folder' => '',
				'thumbnail' => 1,
				'thumbnail_suffix'=> '_thumb',
				'thumbnail_w'=> $settings['download_thumb_max_w'],
				'thumbnail_h' => $settings['download_thumb_max_h'],
				'error_text' => 'Please select an image',
				'inline' => 1,
				'thumbnail2' => 0
			);
echo form_fileinput('File upload', 'file_input', 'file_input', DOWNLOADS."images/", '', $file_options); // all file types.

echo form_button('submit', 'Submit', 'value', array('class' => 'btn-success'));
echo closeform();

echo "<br>These are the default and posted settings merged, which would endup being inserted in the DB:";
var_dump($settings_test);

echo "<br>These are the tokens available for this form:";
var_dump($_SESSION['csrf_tokens']['form']);

closetable();
require_once THEMES."templates/footer.php";
?>