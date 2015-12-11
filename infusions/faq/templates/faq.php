<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: templates/faq.php
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
// Main View
if (!function_exists("render_faq")) {
	function render_faq($info) {
		opentable($info['faq_title']);
		echo "<!--pre_faq_idx-->\n";
		if (!empty($info['items']) && count($info['items'])) {
			echo "<div class='list-group'>\n";
			foreach ($info['items'] as $data) {
				echo "<div class='list-group-item'>\n";
				echo "<h4 style='width:100%'><a href='".$data['faq_link']."'>".$data['faq_cat_name']."</a><span class='badge pull-right'>".$data['faq_count']."</span></h4>\n";
				if ($data['faq_cat_description']) {
					echo $data['faq_cat_description'];
				}
				echo "</div>\n";
			}
			echo "</div>\n";
		} else {
			echo "<div class='well text-center'>".$info['nofaqs']."</div>\n";
		}
		closetable();
	}
}
// Category View
if (!function_exists("render_faq_item")) {
	function render_faq_item($info) {
		global $locale;
		echo "<span id='content'></span>\n";
		opentable($locale['401'].": ".$info['faq_cat_name']);
		echo "<a href='".INFUSIONS."faq/faq.php'>".$locale['400']."</a> &gt; <a href='".$info['faq_link']."'>".$info['faq_cat_name']."</a>\n";
		if (!empty($info['nofaq_items'])) {
			echo "<div class='well text-center m-t-20'>".$info['nofaq_items']."</div>\n";
		} else {
			echo "<div class='row m-t-20'>\n";
			echo "<div class='col-xs-12 col-sm-3'>\n";
			if (!empty($info['items'])) {
				echo "<ul>\n";
				foreach ($info['items'] as $data) {
					echo "<li><a href='".FUSION_REQUEST."#faq_".$data['faq_id']."'>".$data['faq_question']."</a></li>\n";
				}
				echo "</ul>\n";
			}
			echo "</div>\n";
			echo "<div class='col-xs-12 col-sm-9'>\n";
			foreach ($info['items'] as $data) {
				echo "<a class='pull-right btn btn-xs btn-default' href='".FUSION_REQUEST."#content'><i class='fa fa-arrow-up'></i> ".$locale['402']."</a>\n";
				echo "<h4 id='faq_".$data['faq_id']."'>".$data['faq_question']."</h4>\n";
				echo nl2br(parse_textarea($data['faq_answer']));
				echo "<hr/>\n";
			}
			echo "</div>\n";
			echo "</div>\n";
		}
		closetable();
	}
}