<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: language_panel.php
| Author: Frederick MC Chan (Hien)
| Author: Joakim Falk (Domi)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined("IN_FUSION")) { die("Access Denied"); }
if (count($enabled_languages) > 1) {
	openside($locale['global_ML102']);
	include_once INCLUDES."translate_include.php";
	echo openform('lang_menu_form', 'post', FUSION_SELF, array('max_tokens' => 1));
	echo form_select('lang_menu', '', $language_opts, $settings['locale']);
	echo closeform();
	add_to_jquery("
	function showflag(item){
		return '<div class=\"clearfix\" style=\"width:100%; padding-left:10px;\"><img style=\"height:20px; margin-top:3px !important;\" class=\"img-responsive pull-left\" src=\"".LOCALE."' + item.text + '/'+item.text + '-s.png\"/><span class=\"p-l-10\">'+ item.text +'</span></div>';
	}
	
	$('#lang_menu').select2({
	placeholder: 'Switch Language',
	formatSelection: showflag,
	escapeMarkup: function(m) { return m; },
	formatResult: showflag,
	}).bind('change', function(item) {
		window.location.href = '".FUSION_REQUEST."?lang='+$(this).val();
	});
");
	closeside();
}
