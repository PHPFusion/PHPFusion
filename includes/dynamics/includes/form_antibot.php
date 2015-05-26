<?php

function form_antibot($title = FALSE, $input_name, $input_id, $input_value = FALSE, $array = FALSE) {
	/* To do
	* To do: label off
	* Note: DO NOT USE DISABLED, it will not pass information to server. Use "readonly" instead.
	*/
	global $_POST;
	if (isset($title) && ($title !== "")) {
		$title = stripinput($title);
	} else {
		$title = "";
	}
	if (isset($input_name) && ($input_name !== "")) {
		$input_name = stripinput($input_name);
	} else {
		$input_name = "";
	}
	if (isset($input_id) && ($input_id !== "")) {
		$input_id = stripinput($input_id);
	} else {
		$input_id = "";
	}
	if (isset($input_value) && ($input_value !== "")) {
		$input_value = stripinput($input_value);
	} else {
		$input_value = "";
	}
	// 4 choices to sub-array
	// a. icon, b. button, c.dropdown list d.dropdown with modal
	if (!is_array($array)) {
		$array = array();
		$state_validation = "";
		$before = "";
		$after = "";
		$required = "";
		$placeholder = "";
		$deactivate = "";
		$width = "";
		$class = "input-sm";
		$well = "";
		$type = "";
		$stacking = "";
	} else {
		$before = (array_key_exists('before', $array)) ? $array['before'] : "";
		$after = (array_key_exists('after', $array)) ? $array['after'] : "";
		$placeholder = (array_key_exists('placeholder', $array)) ? $array['placeholder'] : "";
		$deactivate = (array_key_exists('deactivate', $array)) ? $array['deactivate'] : "";
		$class = (array_key_exists('class', $array)) ? $array['class'] : "input-sm";
		$required = (array_key_exists('required', $array)) ? $array['required'] : "";
		$width = (array_key_exists('width', $array)) ? "style='width: ".$array['width']."'" : "";
		$well = (array_key_exists('well', $array)) ? "style='margin-top:-10px;'" : "";
		$type = (array_key_exists('password', $array) && ($array['password'] == "1")) ? "password" : "text";
		$stacking = (array_key_exists("stacking", $array)) ? 1 : "";
	}
	if (($required == "1") && (isset($_POST[$input_name]) && (empty($_POST[$input_name])))) {
		$state_validation = "has-error";
	} else {
		$state_validation = "";
	}
	if ($stacking == "1") {
		$col = "col-sm-12 col-md-12 col-lg-12";
		$col2 = "col-sm-12 col-md-12 col-lg-12";
	} else {
		$col = "col-sm-12 col-md-3 col-lg-3 control-label";
		$col2 = "col-sm-12 col-md-9 col-lg-9";
	}
	// Append/Prepend Plugin API
	if ((!empty($before)) || (!empty($after))) {
		$init_bs3 = "<div class='input-group'>";
		$end_bs3 = "</div>";
	} else {
		// cancel plugin
		$init_bs3 = "";
		$end_bs3 = "";
	}
	$html = "";
	if (!empty($title)) {
		$html .= "<div class='form-group ".$state_validation."'>";
		$html .= "<label for='$input_id-0' class='$col'>$title</label>";
		$html .= "<div class='$col2' $well>";
	}
	// Are you a human
	require_once(INCLUDES."captchas/ayah/ayah.php");
	$ayah = new AYAH();
	if (array_key_exists('ayah_submit', $_POST)) {
		$score = $ayah->scoreResult();
		if ($score) {
			$html = "Congratulations: you are a human!";
		} else {
			$html = "Sorry, but we were not able to verify you as human. Please try again.";
		}
	}
	$html .= "<div class='row'>";
	$html .= "<div class='col-sm-12 col-md-12 col-lg-12'>";
	$html .= $ayah->getPublisherHTML();
	$html .= "</div>";
	$html .= "</div>";
	if (!empty($title)) {
		$html .= "</div></div>";
	}
	return $html;
}

