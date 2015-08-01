<?php

/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Project File: Dynamic Form Builder formstack() i/o
| Filename: dynamics.inc.php
| Author: Frederick MC Chan (Hien)
| Version : 9.0
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

class dynamics {
	static function boot() {
		//Are these two include really necessary?
		include LOCALE.LOCALESET."admin/members.php";
		require_once INCLUDES."defender.inc.php";
		if (!defined('DYNAMICS')) {
			define('DYNAMICS', INCLUDES."dynamics/");
		}
		require_once DYNAMICS."includes/form_main.php";
		require_once DYNAMICS."includes/form_text.php";
		require_once DYNAMICS."includes/form_name.php";
		require_once DYNAMICS."includes/form_select.php";
		require_once DYNAMICS."includes/form_textarea.php";
		require_once DYNAMICS."includes/form_hidden.php";
		require_once DYNAMICS."includes/form_buttons.php";
		require_once DYNAMICS."includes/form_ordering.php";
		require_once DYNAMICS."includes/form_chain.php";
		require_once DYNAMICS."includes/form_datepicker.php";
		require_once DYNAMICS."includes/form_fileinput.php";
		require_once DYNAMICS."includes/form_colorpicker.php";
		require_once DYNAMICS."includes/form_geomap.php";
		require_once DYNAMICS."includes/form_modal.php";
		require_once DYNAMICS."includes/form_honeypot.php";
		require_once DYNAMICS."includes/form_checkbox.php";
		require_once DYNAMICS."includes/form_paragraph.php";
		require_once DYNAMICS."includes/form_document.php";
	}
}