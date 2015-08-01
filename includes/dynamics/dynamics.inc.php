<?php

/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Project File: Dynamic Form Builder formstack() i/o
| Filename: dynamics.inc.php
| Author: PHP-Fusion 8 Development Team
| Coded by : Frederick MC Chan (Hien)
| Version : 8.2.1 (please update every commit)
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
		require_once DYNAMICS."includes/form_alert.php";
		require_once DYNAMICS."includes/form_labelling.php";
		require_once DYNAMICS."includes/form_buttons.php";
		require_once DYNAMICS."includes/form_ordering.php";
		require_once DYNAMICS."includes/form_chain.php";
		require_once DYNAMICS."includes/form_datepicker.php";
		require_once DYNAMICS."includes/form_fileinput.php";
		require_once DYNAMICS."includes/form_colorpicker.php";
		require_once DYNAMICS."includes/form_geomap.php";
		require_once DYNAMICS."includes/form_modal.php";
		require_once DYNAMICS."includes/form_antibot.php";
		require_once DYNAMICS."includes/form_checkbox.php";
		require_once DYNAMICS."includes/form_paragraph.php";
		require_once DYNAMICS."includes/form_document.php";
	}
}

function load_tablesorter($id) {
	// implementation: use in table();
	// to add: sortlist:[[0,0],[1,0]]
	add_to_head("<script type='text/javascript' src='".DYNAMICS."assets/tablesorter/jquery.tablesorter.min.js'></script>");
	add_to_jquery("
        $('#".$id."').tablesorter();
        ");
	add_to_head("
        <style>
        /* tables */
        table.tablesorter {}
        table.tablesorter thead tr th, table.tablesorter tfoot tr th {}
        table.tablesorter thead tr .header {
        background-image: url(".DYNAMICS."assets/tablesorter/bg.gif);
        background-repeat: no-repeat;
        background-position: center right;
        cursor: pointer;
        }
        table.tablesorter tbody td {}
        table.tablesorter tbody tr.odd td {}
        table.tablesorter thead tr .headerSortUp { background-image: url(".DYNAMICS."assets/tablesorter/asc.gif);    }
        table.tablesorter thead tr .headerSortDown {	background-image: url(".DYNAMICS."assets/tablesorter/desc.gif);    }
        table.tablesorter thead tr .headerSortDown, table.tablesorter thead tr .headerSortUp {    }
        </style>
        ");
	return "tablesorter";
}

