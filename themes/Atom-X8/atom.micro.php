<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: atom.micro.php
| Author: Chan (Frederick MC Chan)
| Author: Falk (Joakim Falk)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

// Atom Render Engine
function atom_micro($iLeft, $iRight) {
	global $settings, $userdata;
	
		// This is the PHP-Fusion 8 & < standard render
		function phpfusion_default_render() {
			$_left = 0; $_right = 0;

			// Exclude side panels on these pages.
			$page = array('profile.php', 'register.php', 'home.php', 'login.php');

			// You can define PANELS_OFF in individual positions as well, see opentable.switch.php
			if (!in_array(FUSION_SELF, $page) && !defined('PANELS_OFF')) {
				if (!defined('PANEL_LEFT_OFF')) {  $_left = (defined('LEFT')  && strlen(LEFT)>0) ? '1': '0'; }
				if (!defined('PANEL_RIGHT_OFF')) { $_right = (defined('RIGHT') &&  strlen(RIGHT)>0) ? '1': '0'; }
			}

			$html  = "<section id='mainbody' role='grid'>\n";
			$html .= "<div class='row'>\n";
			$html .= "AU_CENTER." ? "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12'>".AU_CENTER."\n</div>\n" : "";
			$html .= ($_left == '1') ? "<div class='col-xs-12 col-sm-2 col-md-2 col-lg-2'>\n".LEFT."</div>\n" : '';

			if ($_left == '0') {
				$html .= ($_right == '1') ? "<div class='col-xs-12 col-sm-10 col-md-10 col-lg-10'>".U_CENTER.CONTENT.L_CENTER."\n</div>
											<div class='col-xs-12 col-sm-2 col-md-2 col-lg-2'>".RIGHT." " :
											"<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12'>".U_CENTER.CONTENT.L_CENTER."\n";
			} else {
				$html .= ($_right == '1') ? "<div class='col-xs-12 col-sm-8 col-md-8 col-lg-8'>".U_CENTER.CONTENT.L_CENTER."\n</div>
											 <div class='col-xs-12 col-sm-2 col-md-2 col-lg-2'>".RIGHT." " : 
											 "<div class='col-xs-12 col-sm-10 col-md-10 col-lg-10'>".U_CENTER.CONTENT.L_CENTER."\n";
			}
			$html .= "</div>\n";
			$html .= "BL_CENTER." ? "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12'>".BL_CENTER."\n</div>\n" : "";
			$html .= "</section>";
			return $html;
		}

	$total_span = 12; // Bootstrap max 12 cols set
	$left_width = 3; // Configure the left width here. (no more than 12)
	$right_width = 3; // Configure the right width here. (no more than 12)

	// Load ready to use templates
	
	$atom_templates = array(
		'profile' => array(
		'file'=> TEMPLATE."profile.tpl.php",
		'body' => "atom_profile"
		),
	);

	// Render output replacement
	foreach($atom_templates as $file => $template) {
		if (preg_match("/".$file."/i", $_SERVER['PHP_SELF']) && (!defined('ADMIN_PANEL'))) {
			if (file_exists($template['file'])) { include $template['file']; $atom = true; } else {
				echo "<div class='well text-center'> ".$template['file']." - Template file not found </div>\n";
			}
			$content = (function_exists($template['body'])) ? $template['body']() : '';
		}
	}

	if (!isset($atom) && (!defined('ADMIN_PANEL'))) {
		$content = phpfusion_default_render();
	}

  // Control layout
	if ($_SERVER['PHP_SELF'] == $settings['opening_page'] && (!defined('ADMIN_PANEL'))) {
		return CONTENT;
	} else {
		return $content;
	}
}

// Cache it
function atom_get_content($file_path) {
	ob_start();
	if (file_exists($file_path)) {
		require_once $file_path;
	}
	$atom_content = ob_get_contents();
	ob_end_clean();
	return $atom_content;
}