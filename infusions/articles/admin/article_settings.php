<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: admin/blog_settings.php
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
pageAccess("S8");
if (isset($_POST['savesettings'])) {
	$error = 0;
	$inputArray = array(
		"article_pagination" => form_sanitizer($_POST['article_pagination'], 0, "article_pagination"),
		"article_allow_submission" => form_sanitizer($_POST['article_allow_submission'], 0, "article_allow_submission"),
		"article_extended_required" => isset($_POST['article_extended_required']) ? 1 : 0,
	);

	if (defender::safe()) {
		foreach ($inputArray as $settings_name => $settings_value) {
			$inputSettings = array(
				"settings_name" => $settings_name, "settings_value" => $settings_value, "settings_inf" => "article",
			);
			dbquery_insert(DB_SETTINGS_INF, $inputSettings, "update", array("primary_key" => "settings_name"));
		}
		addNotice("success", $locale['900']);
		redirect(FUSION_REQUEST);
	} else {
		addNotice('danger', $locale['901']);
	}
}

echo "<div class='well'>".$locale['articles_0031']."</div>";
echo openform('settingsform', 'post', FUSION_REQUEST);
openside('');
echo form_text("article_pagination", $locale['articles_0032'], $article_settings['article_pagination'], array(
	"inline" => TRUE, "max_length" => 4, "width" => "150px", "type" => "number"
));
echo form_select("article_allow_submission", $locale['articles_0033'], $article_settings['article_allow_submission'], array(
	"inline" => TRUE, "options" => array($locale['disable'], $locale['enable'])
));
echo form_checkbox("article_extended_required", $locale['articles_0034'], $article_settings['article_extended_required'], array("inline" => TRUE));
closeside();
echo form_button('savesettings', $locale['750'], $locale['750'], array('class' => 'btn-primary'));
echo closeform();