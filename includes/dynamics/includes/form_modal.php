<?php

function form_modal($modal_input, $title, $htmlcode = "", $array = FALSE) {
	$codes = (isset($htmlcode) && ($htmlcode !== "")) ? $htmlcode : "";
	if (!is_array($array)) {
		$array = array();
		$button_class = "btn-default";
		$button_img = "pictogram-popup pictogram-white";
		$button_text = "Show Detail";
		$hide_footer = "";
	} else {
		$button_class = (array_key_exists('button_class', $array)) ? $array['button_class'] : "btn-default";
		$button_img = (array_key_exists('button_img', $array)) ? $array['button_img'] : "pictogram-popup";
		$button_text = (array_key_exists('button_text', $array)) ? $array['button_text'] : "Show Detail";
		$htmlcode = (array_key_exists('htmlcode', $array)) ? $array['htmlcode'] : "";
		$hide_footer = (array_key_exists('hide_footer', $array)) ? $array['hide_footer'] : "";
	}
	$html = "";
	$html .= "<a href='#".$modal_input."-modal' role='button' class='btn $button_class' data-toggle='modal'><i class='$button_img'></i> $button_text</a>";
	$html .= "<div id='".$modal_input."-modal' class='modal fade'>";
	$html .= "<div class='modal-dialog'>";
	$html .= "<div class='modal-content'>";
	$html .= "<div class='modal-header'>";
	$html .= "<button type='button' class='close' data-dismiss='modal' aria-hidden='true'>&times;</button>";
	$html .= "<h4 id='".$modal_input."Label'>".$title."</h4>";
	$html .= "</div>";
	$html .= "<div class='modal-body' style='font-size:13px;'>".$htmlcode."</div>";
	if (isset($hide_footer) && ($hide_footer !== "1")) {
		$html .= "<div class='modal-footer'>";
		$html .= '<button class="btn btn-default" data-dismiss="modal" aria-hidden="true">Close</button>';
		$html .= '<button type="submit" class="btn btn-primary">Save</button>';
		$html .= "</div>";
	}
	$html .= "</div>";
	$html .= "</div></div>";
	return $html;
}

?>