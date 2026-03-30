<?php

function privacy() {
	
	$locale = fusion_get_locale(include_file: LOCALE.LOCALESET."privacy.php");
	
	return "<span class='".BASEDIR."?page=user'>{$locale['terms_0100']}</span>
	<span class='".BASEDIR."?page=privacy'>{$locale['terms_0101']}</span>
	<span class='".BASEDIR."?page=tou'>{$locale['terms_0102']}</span>
	";
	
}

function show_terms() {
	require_once INCLUDES.'parsedown_include.php';
	
	$accepted = ['user', 'privacy', 'tou'];
	
	$locale = fusion_get_locale(include_file: LOCALE.LOCALESET."privacy.php");
	
	
	
	$md = new Parsedown();
	
	
}

fusion_add_hook("fusion_footer", "privacy");